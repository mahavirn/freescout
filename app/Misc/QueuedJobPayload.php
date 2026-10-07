<?php

namespace App\Misc;

use App\Thread;

/**
 * Shared by App\Job (jobs table) and App\FailedJob (failed_jobs table).
 */
trait QueuedJobPayload
{
    public $payload_decoded = null;

    /**
     * Jobs of the given class. Matches "displayName" anywhere in the JSON payload,
     * as key order depends on the Laravel version.
     */
    public function scopeOfJob($query, $class)
    {
        $display_name = substr(json_encode($class), 1, -1);

        return $query->where('payload', 'like', '%"displayName":"'.addcslashes($display_name, '\\%_').'"%');
    }

    public function getPayloadDecoded()
    {
        if ($this->payload_decoded === null) {
            $this->payload_decoded = json_decode($this->payload, true);
        }

        return $this->payload_decoded;
    }

    public function getCommand($allowed_classes = [])
    {
        return self::getPayloadCommand($this->getPayloadDecoded(), $allowed_classes);
    }

    public function getCommandLastThread()
    {
        $command = $this->getCommand();
        if ($command && !empty($command->threads)) {
            return Thread::getLastThread($command->threads);
        }

        return null;
    }

    public static function getPayloadCommand($payload, $allowed_classes = [])
    {
        if (empty($payload['data']) || empty($payload['data']['command'])) {
            return null;
        }
        if (!$allowed_classes) {
            $allowed_classes = [
                'App\Jobs\SendReplyToCustomer',
                'App\Jobs\SendNotificationToUsers',
                'App\Jobs\SendAutoReply',
                'App\Jobs\SendAlert',
                'App\Jobs\SendEmailReplyError',
                'Illuminate\Contracts\Database\ModelIdentifier',
            ];
        }
        try {
            // If some record has been deleted from DB, there will be an error:
            // No query results for model [App\Conversation].
            return unserialize($payload['data']['command'], ['allowed_classes' => $allowed_classes]);
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function getTriggerActionName($payload)
    {
        return preg_replace('/^.*?action";s:\d+:"([^"]+)".*$/s', '$1', $payload['data']['command'] ?? '');
    }
}
