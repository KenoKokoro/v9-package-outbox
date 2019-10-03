<?php

namespace V9\Outbox\Module\Schedule;

use Illuminate\Support\Carbon;
use V9\Outbox\Contracts\OutboxInstance;
use V9\Outbox\Contracts\ReceiverInstance;
use V9\Outbox\Contracts\SubjectInstance;
use V9\Outbox\DAL\Outbox\OutboxRepository;
use V9\Outbox\Models\Outbox;
use V9\Outbox\Module\Channel\DTO\ChannelCollectionInterface;
use V9\Outbox\Module\Exceptions\UnsupportedChannelException;

class V9Schedule implements ScheduleInterface
{
    private OutboxRepository $repository;

    private ChannelCollectionInterface $supportedChannels;

    public function __construct(OutboxRepository $repository, ChannelCollectionInterface $supportedChannels)
    {
        $this->repository = $repository;
        $this->supportedChannels = $supportedChannels;
    }

    public function put(
        Carbon $sendAt,
        OutboxInstance $instance,
        string $channel,
        SubjectInstance $subject,
        ReceiverInstance $receiver
    ): Outbox {
        if (!$this->supportedChannels->has($channel)) {
            throw new UnsupportedChannelException($channel);
        }

        return $this->repository->schedule($sendAt, $instance, $channel, $subject, $receiver);
    }
}
