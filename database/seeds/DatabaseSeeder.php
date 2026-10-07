<?php

use App\Conversation;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create users
        \Database\Factories\UserFactory::new()->count(3)->create();

        // Create mailboxes, conversations, etc
        \Database\Factories\MailboxFactory::new()->count(3)->create()->each(function ($m) {
            $user = \Database\Factories\UserFactory::new()->create();
            $m->users()->save($user);

            for ($i = 0; $i < 7; $i++) {
                $customer = \Database\Factories\CustomerFactory::new()->create();

                $email = \Database\Factories\EmailFactory::new()->make();
                $customer->emails()->save($email);

                $conversation = \Database\Factories\ConversationFactory::new()->create([
                    'created_by_user_id' => $user->id,
                    'mailbox_id'         => $m->id,
                    'customer_id'        => $customer->id,
                    'customer_email'     => $email->email,
                    'user_id'            => $user->id,
                    'status'             => array_rand([Conversation::STATUS_ACTIVE => 1, Conversation::STATUS_PENDING => 1]),
                ]);

                $thread = \Database\Factories\ThreadFactory::new()->make([
                    'customer_id'     => $customer->id,
                    'to'              => $email->email,
                    'conversation_id' => $conversation->id,
                ]);
                $conversation->threads()->save($thread);
            }
        });
    }
}
