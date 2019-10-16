<?php

namespace V9\Outbox\DAL\Outbox;

use Illuminate\Support\Carbon;
use V9\DAL\EloquentRepository;
use V9\Outbox\Contracts\OutboxInstance;
use V9\Outbox\Contracts\ReceiverInstance;
use V9\Outbox\Contracts\SubjectInstance;
use V9\Outbox\Models\Outbox;

class EloquentOutbox extends EloquentRepository implements OutboxRepository
{
    public function __construct(Outbox $model)
    {
        parent::__construct($model);
    }

    public function schedule(
        Carbon $sendAt,
        OutboxInstance $instance,
        string $channel,
        SubjectInstance $subject,
        ReceiverInstance $receiver
    ): Outbox {
        /** @var Outbox $instance */
        $instance = $this->newQuery()->create([
            'channel' => $channel,
            'content' => $instance,
            'send_at' => $sendAt,
            'sent_at' => null,
            'status' => Outbox::STATUS_PENDING,
            'subject_id' => $subject->getKey(),
            'subject_type' => $subject->getMorphClass(),
            'receiver_id' => $receiver->getKey(),
            'receiver_type' => $receiver->getMorphClass(),
            'try' => 0,
            'error' => null,
        ]);

        return $instance;
    }

    public function success(Outbox $outbox): void
    {
        $this->update($outbox, [
            'sent_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'status' => Outbox::STATUS_DONE,
            'error' => null,
            'try' => ($outbox->try + 1),
        ]);
    }

    public function pending(Outbox $outbox): void
    {
        $this->update($outbox, [
            'status' => Outbox::STATUS_RUNNING,
            'error' => null,
        ]);
    }

    public function error(Outbox $outbox, string $error): void
    {
        $this->update($outbox, [
            'status' => Outbox::STATUS_ERROR,
            'error' => $error,
            'try' => ($outbox->try + 1),
        ]);
    }
}
