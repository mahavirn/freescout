<?php

namespace App\Mail;

use Illuminate\Container\Container;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Illuminate\Mail\Mailable;

// https://medium.com/@guysmilez/queuing-mailables-with-custom-headers-in-laravel-5-4-ab615f022f17
//abstract class AbstractMessage extends Mailable
class ReplyToCustomer extends Mailable
{
    /**
     * Conversation to send.
     *
     * @var [type]
     */
    public $conversation;

    /**
     * Threads to send.
     *
     * @var [type]
     */
    public $threads;

    /**
     * Custom headers.
     *
     * @var array
     */
    public $headers = [];

    /**
     * Mailbox.
     *
     * @var array
     */
    public $mailbox;

    /**
     * Subject.
     */
    public $subject;

    /**
     * Number of threads.
     */
    public $threads_count;

    /**
     * Needed to show proper signature when conversation is being moved between mailboxes.
     */
    public $mailbox_change_history;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($conversation, $threads, $headers, $mailbox, $subject, $threads_count = 1, $mailbox_change_history = [])
    {
        $this->conversation = $conversation;
        $this->threads = $threads;
        $this->headers = $headers;
        $this->mailbox = $mailbox;
        $this->subject = $subject;
        $this->threads_count = $threads_count;
        $this->mailbox_change_history = $mailbox_change_history;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        \MailHelper::prepareMailable($this);

        $thread = $this->threads->first();
        $from_alias = trim($thread->from ?? '');

        // Set Message-ID
        // Settings via $this->addCustomHeaders does not work
        $new_headers = $this->headers;
        if (!empty($new_headers) || $from_alias) {
            $mailbox = $this->mailbox;
            $this->withSymfonyMessage(function ($message) use ($new_headers, $from_alias, $mailbox, $thread) {
                if (!empty($new_headers)) {
                    \MailHelper::setMessageHeaders($message, $new_headers);
                }
                if (!empty($from_alias)) {
                    // Make sure that the From contains a mailbox alias,
                    // as user thread may have From specified when a user
                    // replies to an email notification.
                    \MailHelper::setFromAlias($message, $mailbox, $from_alias, $thread->created_by_user, $thread->conversation);
                }

                \Eventy::action('email.reply_to_customer.swiftmessage', $message, $from_alias, $thread, $mailbox);
            });
        }

        $template_html = \Eventy::filter('email.reply_to_customer.template_name_html', 'emails/customer/reply_fancy');
        $template_text = \Eventy::filter('email.reply_to_customer.template_name_text', 'emails/customer/reply_fancy_text');

        // from($this->from) Sets only email, name stays empty.
        // So we set from in Mail::setMailDriver
        $message = $this->subject($this->subject)
                    ->view($template_html)
                    ->text($template_text);

        if ($thread->has_attachments) {
            foreach ($thread->attachments as $attachment) {
                if (\Helper::isLocalStorage()) {
                    if ($attachment->fileExists()) {
                        $message->attach($attachment->getLocalFilePath());
                    } else {
                        \Log::error('[ReplyToCustomer] Thread: '.$thread->id.'. Attachment file not find on disk: '.$attachment->getLocalFilePath());
                    }
                } else {
                    \Eventy::filter('email.reply_to_customer.attach', false, $message, $attachment, $thread->id);
                }
            }
        }

        return $message;
    }

    /*
     * Send the message using the given mailer.
     *
     * @param  \Illuminate\Contracts\Mail\Mailer  $mailer
     * @return void
     */
    // public function send(MailerContract $mailer)
    // {
    //     Container::getInstance()->call([$this, 'build']);

    //     $mailer->send($this->buildView(), $this->buildViewData(), function ($message) {
    //         $this->buildFrom($message)
    //              ->buildRecipients($message)
    //              ->buildSubject($message)
    //              ->buildAttachments($message)
    //              ->addCustomHeaders($message) // This is new!
    //              ->runCallbacks($message);
    //     });
    // }
}
