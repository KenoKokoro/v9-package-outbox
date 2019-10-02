<?php

namespace V9\Outbox\DAL\Outbox;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use V9\Outbox\Contracts\OutboxInstance;
use V9\Outbox\Contracts\ReceiverInstance;
use V9\Outbox\Contracts\SubjectInstance;
use V9\Outbox\Models\Outbox;

interface OutboxRepository
{
    /**
     * Schedule the notification to outbox
     * @param Carbon           $sendAt
     * @param OutboxInstance   $instance
     * @param string           $channel
     * @param SubjectInstance  $subject
     * @param ReceiverInstance $receiver
     * @return Outbox|Model
     */
    public function schedule(
        Carbon $sendAt,
        OutboxInstance $instance,
        string $channel,
        SubjectInstance $subject,
        ReceiverInstance $receiver
    ): Outbox;

    /**
     * Mark the outbox as sent with success
     * @param Outbox $notification
     */
    public function success(Outbox $notification): void;

    /**
     * Mark the outbox as pending to be sent
     * @param Outbox $outbox
     */
    public function pending(Outbox $outbox): void;

    /**
     * Mark the outbox with an error
     * @param Outbox $outbox
     * @param string $error
     */
    public function error(Outbox $outbox, string $error): void;
}
