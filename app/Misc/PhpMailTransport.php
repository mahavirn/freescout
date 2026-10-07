<?php
/**
 * "PHP mail()" outgoing method: sends messages using PHP mail() function.
 * Symfony Mailer has no such transport (its "native" transport needs proc_open,
 * which is often disabled on shared hostings while mail() is available).
 */

namespace App\Misc;

use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Header\MailboxListHeader;

class PhpMailTransport extends AbstractTransport
{
    protected function doSend(SentMessage $message): void
    {
        [$headers, $body] = explode("\r\n\r\n", $message->toString(), 2) + [1 => ''];

        // mail() adds To and Subject headers itself.
        $to = '';
        $subject = '';
        $headers = preg_replace_callback('/^(To|Subject):[ \t]*([^\r\n]*(?:\r\n[ \t][^\r\n]*)*)(\r\n|$)/mi', function ($m) use (&$to, &$subject) {
            $value = preg_replace('/\r\n[ \t]+/', ' ', $m[2]);
            if (strtolower($m[1]) == 'to') {
                $to = $value;
            } else {
                $subject = $value;
            }

            return '';
        }, $headers);

        // Bcc is not included into the message, but mail() reads recipients from headers.
        $original = $message->getOriginalMessage();
        if (method_exists($original, 'getBcc') && $original->getBcc()) {
            $headers .= "\r\n".(new MailboxListHeader('Bcc', $original->getBcc()))->toString();
        }

        $params = '';
        $sender = $message->getEnvelope()->getSender()->getAddress();
        if (filter_var($sender, FILTER_VALIDATE_EMAIL)) {
            $params = '-f'.$sender;
        }

        if (!mail($to, $subject, $body, trim($headers), $params)) {
            throw new TransportException('Could not send email using PHP mail() function.');
        }
    }

    public function __toString(): string
    {
        return 'mail://default';
    }
}
