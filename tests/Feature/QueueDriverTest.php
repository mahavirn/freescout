<?php

namespace Tests\Feature;

use App\Jobs\RestartQueueWorker;
use App\Jobs\SendReplyToCustomer;
use App\Thread;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Queue features which must work with any queue driver (database, Redis, etc).
 */
class QueueDriverTest extends TestCase
{
    use DatabaseTransactions;

    public function testUndoneAndResentReplyJobIsOutdated()
    {
        $mailbox = \Database\Factories\MailboxFactory::new()->create();
        $customer = \Database\Factories\CustomerFactory::new()->create();
        $user = \Database\Factories\UserFactory::new()->create();
        $conversation = \Database\Factories\ConversationFactory::new()->create([
            'mailbox_id'         => $mailbox->id,
            'customer_id'        => $customer->id,
            'created_by_user_id' => $user->id,
        ]);
        $thread = \Database\Factories\ThreadFactory::new()->create([
            'conversation_id' => $conversation->id,
            'customer_id'     => $customer->id,
            'type'            => Thread::TYPE_MESSAGE,
            'created_at'      => now()->subMinute(),
        ]);

        $job = new SendReplyToCustomer($conversation, Thread::where('id', $thread->id)->get(), $customer);
        $payload = serialize($job);

        // Models are reloaded from DB when the job is processed.
        $this->assertFalse(unserialize($payload)->isOutdated());

        // Reply is undone and sent again.
        $thread->created_at = now();
        $thread->save();

        $this->assertTrue(unserialize($payload)->isOutdated());

        // Jobs queued before the check was added are processed as before.
        $old_job = unserialize($payload);
        $old_job->last_thread_created_at = null;
        $this->assertFalse($old_job->isOutdated());
    }

    public function testOnlyOneRestartJobIsQueued()
    {
        \Queue::fake();
        \Option::remove(RestartQueueWorker::QUEUED_AT_OPTION);

        \Helper::queueWorkerRestart();
        \Helper::queueWorkerRestart();
        \Queue::assertPushed(RestartQueueWorker::class, 1);

        // Job has been processed.
        \Option::remove(RestartQueueWorker::QUEUED_AT_OPTION);
        \Helper::queueWorkerRestart();
        \Queue::assertPushed(RestartQueueWorker::class, 2);
    }

    public function testSendMonitorDetectsStuckEmailsQueue()
    {
        config(['queue.default' => 'database']);

        \Queue::pushOn('emails', new RestartQueueWorker());
        $this->artisan('freescout:send-monitor');
        $this->assertFalse(\Option::get('send_emails_problem', false, true, false));

        \DB::table('jobs')->where('queue', 'emails')->update(['available_at' => time() - 13 * 3600]);
        $this->artisan('freescout:send-monitor');
        $this->assertEquals('1', \Option::get('send_emails_problem', false, true, false));
    }
}
