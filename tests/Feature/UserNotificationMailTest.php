<?php

namespace Tests\Feature;

use App\Mail\UserNotification;
use App\Thread;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserNotificationMailTest extends TestCase
{
    use DatabaseTransactions;

    public function testNotificationIsSent()
    {
        $user = \Database\Factories\UserFactory::new()->create();
        $mailbox = \Database\Factories\MailboxFactory::new()->create();
        $customer = \Database\Factories\CustomerFactory::new()->create();
        $customer->syncEmails(['customer@example.org']);
        $conversation = \Database\Factories\ConversationFactory::new()->create([
            'mailbox_id'         => $mailbox->id,
            'customer_id'        => $customer->id,
            'created_by_user_id' => $user->id,
            'status'             => \App\Conversation::STATUS_ACTIVE,
        ]);
        \Database\Factories\ThreadFactory::new()->create([
            'conversation_id' => $conversation->id,
            'customer_id'     => $customer->id,
            'type'            => Thread::TYPE_CUSTOMER,
        ]);

        config(['mail.driver' => 'array']);
        \MailHelper::reapplyMailConfig();

        $from = ['address' => 'notifications@example.com', 'name' => 'Support'];
        \Mail::to($user->email)->send(new UserNotification($user, $conversation, $conversation->threads()->get(), ['Message-ID' => 'test-notification@example.com'], $from, $mailbox));

        $message = app('mail.manager')->mailer()->getSymfonyTransport()->messages()->last()->getOriginalMessage();
        $this->assertEquals('notifications@example.com', $message->getFrom()[0]->getAddress());
        $this->assertEquals('Support', $message->getFrom()[0]->getName());
        $this->assertEquals('test-notification@example.com', $message->getHeaders()->get('Message-ID')->getId());
    }
}
