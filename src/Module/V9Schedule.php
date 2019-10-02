<?php

namespace V9\Outbox\Module;

use Illuminate\Support\Carbon;
use V9\Outbox\Contracts\OutboxInstance;
use V9\Outbox\Contracts\ReceiverInstance;
use V9\Outbox\Contracts\SubjectInstance;
use V9\Outbox\Models\Outbox;

class V9Schedule implements OutboxScheduler
{
    /**
     * @var PersistenceFactory
     */
    private $factory;

    public function __construct(PersistenceFactory $factory)
    {
        $this->factory = $factory;
    }

    public function put(
        Carbon $sendAt,
        OutboxInstance $instance,
        string $channel,
        SubjectInstance $subject,
        ReceiverInstance $receiver
    ): Outbox {
        return $this->factory
            ->repository()
            ->schedule($sendAt, $instance, $channel, $subject, $receiver);
    }
}
