<?php

namespace Tests\Unit;

use App\Misc\PhpMailTransport;
use Symfony\Component\Mime\Email;
use Tests\TestCase;

/**
 * PHP mail() driver. Runs in a separate PHP process with sendmail_path pointing to a script
 * which saves the message, as sendmail_path can not be changed at runtime.
 */
class PhpMailTransportTest extends TestCase
{
    public function testMessageIsPassedToMail()
    {
        $dir = sys_get_temp_dir().'/fs_mail_test_'.getmypid();
        @mkdir($dir);
        // sendmail_path is run by shell: use a path without spaces.
        $sendmail = $dir.'/sendmail.sh';
        copy(base_path('tests/Fixtures/capture-sendmail.sh'), $sendmail);
        chmod($sendmail, 0755);
        $code = 'require "vendor/autoload.php";'
            .'(new '.PhpMailTransport::class.'())->send((new '.Email::class.'())'
            .'->from("support@example.com")->to("Customer <customer@example.org>")->cc("cc@example.org")->bcc("bcc@example.org")'
            .'->subject("Problème test")->text("Body text"));';

        exec('CAPTURE_DIR='.escapeshellarg($dir).' '.escapeshellarg(PHP_BINARY)
            .' -d sendmail_path='.escapeshellarg($sendmail)
            .' -r '.escapeshellarg($code).' 2>&1', $output, $status);

        $this->assertSame(0, $status, implode("\n", $output));
        $message = file_get_contents($dir.'/message');
        $this->assertStringContainsString('To: Customer <customer@example.org>', $message);
        $this->assertStringContainsString('Subject: =?utf-8?', $message);
        $this->assertStringContainsString('Cc: cc@example.org', $message);
        $this->assertStringContainsString('Bcc: bcc@example.org', $message);
        $this->assertStringContainsString('Body text', $message);
        $this->assertStringContainsString('-fsupport@example.com', file_get_contents($dir.'/args'));
        // To and Subject are not duplicated.
        $this->assertSame(1, substr_count($message, "\nTo:") + (strpos($message, 'To:') === 0 ? 1 : 0));
    }
}
