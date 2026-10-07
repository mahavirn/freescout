<?php

namespace Tests\Feature;

use App\FailedJob;
use App\Jobs\SendReplyToCustomer;
use App\Thread;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Failed jobs are looked up by class in failed_jobs table (retry sending).
 * Uses real payloads created by the database queue driver.
 */
class QueuedJobLookupTest extends TestCase
{
    use DatabaseTransactions;

    const QUEUE = 'test_job_lookup';

    public function testFailedReplyJobIsFoundByThread()
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
        ]);
        $other_thread = \Database\Factories\ThreadFactory::new()->create([
            'conversation_id' => $conversation->id,
            'customer_id'     => $customer->id,
            'type'            => Thread::TYPE_MESSAGE,
        ]);

        // Payload exactly as the database queue driver stores it.
        $job_id = \Queue::connection('database')->pushOn(self::QUEUE, new SendReplyToCustomer($conversation, Thread::where('id', $thread->id)->get(), $customer));
        $payload = \DB::table('jobs')->where('id', $job_id)->value('payload');

        $failed_job_id = \DB::table('failed_jobs')->insertGetId([
            'connection' => 'database',
            'queue'      => 'emails',
            'payload'    => $payload,
            'exception'  => 'Test',
            'failed_at'  => now(),
        ]);

        $this->assertTrue(FailedJob::ofJob(SendReplyToCustomer::class)->where('id', $failed_job_id)->exists());
        $this->assertEquals($failed_job_id, $thread->getFailedJobId());
        $this->assertNull($other_thread->getFailedJobId());
    }
}
