<?php

namespace V9\Outbox\Module\Channel;

use Closure;
use V9\Outbox\Module\Channel\DTO\Channel;
use V9\Outbox\Module\Channel\DTO\ChannelCollectionInterface;

interface FactoryInterface
{
    /**
     * Create new channel instance
     * @param string  $key
     * @param Closure $action
     * @return Channel
     */
    public function create(string $key, Closure $action): Channel;

    /**
     * @param array $channels [ ['key' => 'channelKey', 'action' => Closure] ]
     * @return ChannelCollectionInterface
     */
    public function createCollection(array $channels): ChannelCollectionInterface;
}
