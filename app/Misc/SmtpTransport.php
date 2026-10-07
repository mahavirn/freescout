<?php
/**
 * SMTP transport with FreeScout specific behaviour (ported from Swift Mailer overrides):
 * - XOAUTH2 authentication.
 * - SSL certificates are not verified (https://github.com/freescout-helpdesk/freescout/issues/2714).
 * - No automatic STARTTLS when encryption is not set.
 * - Remembers if message data has been passed to the server (\MailHelper::$smtp_data_sent).
 * - Adds the last SMTP command to error messages (without sensitive data).
 * - Remembers SMTP queue ID returned by the server (https://github.com/freescout-helpdesk/freescout/issues/3330).
 */

namespace App\Misc;

use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\Smtp\Auth\XOAuth2Authenticator;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

class SmtpTransport extends EsmtpTransport
{
    /**
     * SMTP queue ID of the last sent message.
     */
    public static $last_smtp_queue_id = null;

    protected $last_command = '';

    /**
     * Create transport from Laravel mailer config.
     *
     * @param array $config host, port, encryption (ssl|tls|''), username, password, auth_mode, timeout
     */
    public static function fromConfig(array $config)
    {
        $encryption = strtolower($config['encryption'] ?? '');

        $transport = new self($config['host'] ?? 'localhost', (int)($config['port'] ?? 25), $encryption == 'ssl');

        if ($encryption == 'tls') {
            $transport->setRequireTls(true);
        } elseif ($encryption != 'ssl') {
            $transport->setAutoTls(false);
        }

        if (strtoupper($config['auth_mode'] ?? '') == 'XOAUTH2') {
            $transport->setAuthenticators([new XOAuth2Authenticator()]);
        }
        if (!empty($config['username'])) {
            $transport->setUsername($config['username']);
            $transport->setPassword($config['password'] ?? '');
        }

        $stream = $transport->getStream();
        $stream->setStreamOptions(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]]);
        if (!empty($config['timeout'])) {
            $stream->setTimeout((float)$config['timeout']);
        }

        return $transport;
    }

    protected function doSend(SentMessage $message): void
    {
        \MailHelper::$smtp_data_sent = false;
        self::$last_smtp_queue_id = null;

        parent::doSend($message);
    }

    public function executeCommand(string $command, array $codes): string
    {
        // Message data has been streamed to the server.
        if ($command === "\r\n.\r\n") {
            \MailHelper::$smtp_data_sent = true;
            $this->last_command = 'STREAMMESSAGE';
        } else {
            $this->last_command = $command;
        }

        try {
            return parent::executeCommand($command, $codes);
        } catch (TransportExceptionInterface $e) {
            throw $this->addLastCommandToException($e);
        }
    }

    protected function parseMessageId(string $mtaResult): string
    {
        if (strpos($mtaResult, 'queued') !== false
            && preg_match("#queued as ([^\$\r\n ]+)#", $mtaResult, $m)
            && trim($m[1])
        ) {
            self::$last_smtp_queue_id = trim($m[1]);
        }

        return parent::parseMessageId($mtaResult);
    }

    /**
     * Add last SMTP command to the error message, removing sensitive data (AUTH params).
     */
    protected function addLastCommandToException(TransportExceptionInterface $e)
    {
        $last_command = trim($this->last_command);
        if (!$last_command) {
            return $e;
        }
        if (strstr($last_command, ':')) {
            list($last_command) = explode(':', $last_command);
        } else {
            list($last_command) = explode(' ', $last_command);
        }

        $class = get_class($e);
        $message = 'Last Command: '.$last_command.'; '.$e->getMessage();
        $new_exception = $e instanceof TransportException ? new $class($message, $e->getCode(), $e) : $e;
        if ($new_exception !== $e) {
            $new_exception->appendDebug($e->getDebug());
        }

        return $new_exception;
    }
}
