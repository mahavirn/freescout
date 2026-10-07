<?php

namespace Tests\Unit;

use App\Misc\SmtpTransport;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mime\Email;
use Tests\TestCase;

/**
 * Needs a local test SMTP server on 127.0.0.1:1025 which rejects
 * recipients containing "reject" and replies "250 2.0.0 Ok: queued as <ID>".
 */
class SmtpTransportTest extends TestCase
{
    private function transport()
    {
        $socket = @fsockopen('127.0.0.1', 1025, $errno, $errstr, 1);
        if (!$socket) {
            $this->markTestSkipped('Test SMTP server is not running on 127.0.0.1:1025');
        }
        fclose($socket);

        return SmtpTransport::fromConfig(['host' => '127.0.0.1', 'port' => 1025, 'encryption' => '', 'username' => 'user', 'password' => 'pass']);
    }

    private function email(array $to, array $cc = [])
    {
        return (new Email())->from('support@example.com')->to(...$to)->cc(...$cc)->subject('Test')->text('Test');
    }

    public function testRejectedRecipientDoesNotStopSending()
    {
        $transport = $this->transport();

        $sent = $transport->send($this->email(['customer@example.org'], ['reject@example.org']));

        $this->assertEquals(['reject@example.org'], $transport->getFailedRecipients());
        // SMTP queue ID returned by the server.
        $this->assertStringStartsWith('SINK', $sent->getMessageId());
        $this->assertTrue(\MailHelper::$smtp_data_sent);
    }

    public function testAllRecipientsRejected()
    {
        $transport = $this->transport();

        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('No valid recipients');

        $transport->send($this->email(['reject1@example.org'], ['reject2@example.org']));
    }

    public function testSmtpLogHidesCredentials()
    {
        // Symfony transport debug format.
        $debug = "[2026-10-07T10:00:00.000000+00:00] > AUTH LOGIN\r\n"
            ."[2026-10-07T10:00:00.000000+00:00] < 334 VXNlcm5hbWU6\r\n"
            ."[2026-10-07T10:00:00.000000+00:00] > dXNlcm5hbWU=\r\n"
            ."[2026-10-07T10:00:00.000000+00:00] < 334 UGFzc3dvcmQ6\r\n"
            ."[2026-10-07T10:00:00.000000+00:00] > cGFzc3dvcmQ=\r\n"
            ."[2026-10-07T10:00:00.000000+00:00] < 535 5.7.8 Authentication failed\r\n"
            ."[2026-10-07T10:00:00.000000+00:00] > AUTH PLAIN AHVzZXIAcGFzcw==\r\n";
        $e = new TransportException('Failed to authenticate');
        $e->appendDebug($debug);

        $log = \MailHelper::getSmtpLog($e);

        $this->assertStringNotContainsString('dXNlcm5hbWU=', $log);
        $this->assertStringNotContainsString('cGFzc3dvcmQ=', $log);
        $this->assertStringNotContainsString('AHVzZXIAcGFzcw==', $log);
        $this->assertStringContainsString('535 5.7.8 Authentication failed', $log);
    }
}
