<?php

namespace V9\Outbox\Module;

use Closure;
use V9\Outbox\Module\Channel\DTO\Channel;
use V9\Outbox\Module\Channel\DTO\ChannelCollection;
use V9\Outbox\Module\Channel\DTO\ChannelCollectionInterface;
use V9\Outbox\Module\Channel\FactoryInterface;

class ChannelFactory implements FactoryInterface
{
    public function create(string $key, Closure $action): Channel
    {
        return new Channel($key, $action);
    }

    public function createCollection(array $channels): ChannelCollectionInterface
    {
        $instances = [];
        foreach ($channels as $channel) {
            $instances[] = $this->create($channel['key'], $channel['action']);
        }

        return new ChannelCollection(...$instances);
    }
}
