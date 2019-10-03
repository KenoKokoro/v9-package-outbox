<?php

namespace V9\Outbox\Module\Channel\DTO;

class ChannelCollection implements ChannelCollectionInterface
{
    private array $channels;

    public function __construct(Channel ...$channels)
    {
        foreach ($channels as $channel) {
            $this->channels[$channel->getKey()] = $channel;
        }
    }

    public function get(string $key): ?Channel
    {
        if ($this->has($key) === false) {
            return null;
        }

        return $this->channels[$key];
    }

    public function has(string $key): bool
    {
        return !!($this->channels[$key] ?? null);
    }
}
