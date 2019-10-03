<?php

namespace V9\Outbox\Module\Channel\DTO;

interface ChannelCollectionInterface
{
    /**
     * Get the channel if exists in the collection
     * @param string $key
     * @return Channel|null
     */
    public function get(string $key): ?Channel;

    /**
     * Check if the channel key exists in the collection
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;
}
