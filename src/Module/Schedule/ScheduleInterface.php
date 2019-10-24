<?php

namespace V9\Outbox\Module\Schedule;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Throwable;
use V9\Outbox\Contracts\OutboxInstance;
use V9\Outbox\Contracts\ReceiverInstance;
use V9\Outbox\Contracts\SubjectInstance;
use V9\Outbox\Models\Outbox;
use V9\Outbox\Module\Exceptions\UnsupportedChannelException;

interface ScheduleInterface
{
    /**
     * Schedule the given instance to be sent to the subject
     * @param OutboxInstance   $instance
     * @param string           $channel
     * @param SubjectInstance  $subject
     * @param ReceiverInstance $receiver
     * @param Carbon           $sendAt
     * @return Outbox
     * @throws UnsupportedChannelException
     */
    public function put(
        OutboxInstance $instance,
        string $channel,
        SubjectInstance $subject,
        ReceiverInstance $receiver,
        Carbon $sendAt = null
    ): Outbox;

    /**
     * Mark the given outbox record as done
     * @param Outbox $outbox
     */
    public function done(Outbox $outbox): void;

    /**
     * Mark the given outbox record as processing
     * @param Outbox $outbox
     */
    public function processing(Outbox $outbox): void;

    /**
     * Mark the given outbox record with error
     * @param Outbox    $outbox
     * @param Exception $exception
     */
    public function error(Outbox $outbox, Exception $exception): void;

    /**
     * Get the outbox records that fit the time frame with the tries constraint that should be sent
     * @param int         $tries
     * @param Carbon      $endDate
     * @param Carbon|null $startDate
     * @return Collection
     */
    public function getScheduled(int $tries, Carbon $endDate, ?Carbon $startDate = null): Collection;

    /**
     * Send the given outbox record
     * @param Outbox $outbox
     * @throws Throwable
     */
    public function send(Outbox $outbox): void;
}
