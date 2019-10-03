<?php

namespace V9\Outbox\Module\Schedule;

use Illuminate\Support\Carbon;
use V9\Outbox\Contracts\OutboxInstance;
use V9\Outbox\Contracts\ReceiverInstance;
use V9\Outbox\Contracts\SubjectInstance;
use V9\Outbox\Models\Outbox;
use V9\Outbox\Module\Exceptions\UnsupportedChannelException;

interface ScheduleInterface
{
    /**
     * Schedule the given instance to be sent to the subject
     * @param Carbon           $sendAt
     * @param OutboxInstance   $instance
     * @param string           $channel
     * @param SubjectInstance  $subject
     * @param ReceiverInstance $receiver
     * @return Outbox
     * @throws UnsupportedChannelException
     */
    public function put(
        Carbon $sendAt,
        OutboxInstance $instance,
        string $channel,
        SubjectInstance $subject,
        ReceiverInstance $receiver
    ): Outbox;
}
