<?php

namespace V9\Outbox\DAL\Outbox;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use V9\Outbox\Contracts\OutboxInstance;
use V9\Outbox\Contracts\ReceiverInstance;
use V9\Outbox\Contracts\SubjectInstance;
use V9\Outbox\Models\Outbox;

class EloquentOutbox implements OutboxRepository
{
    /**
     * @var Outbox
     */
    private $model;

    public function __construct(Outbox $outbox)
    {
        $this->model = $outbox;
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
        $outbox->update([
            'sent_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'status' => Outbox::STATUS_DONE,
            'error' => null,
            'try' => ($outbox->try + 1),
        ]);
    }

    public function pending(Outbox $outbox): void
    {
        $outbox->update([
            'status' => Outbox::STATUS_RUNNING,
            'error' => null,
        ]);
    }

    public function error(Outbox $outbox, string $error): void
    {
        $outbox->update([
            'status' => Outbox::STATUS_ERROR,
            'error' => $error,
            'try' => ($outbox->try + 1),
        ]);
    }

    /**
     * New query builder
     * @return Builder
     */
    private function newQuery(): Builder
    {
        return $this->model->newQuery();
    }
}
