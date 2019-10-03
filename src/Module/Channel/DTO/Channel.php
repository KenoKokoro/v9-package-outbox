<?php

namespace V9\Outbox\Module\Channel\DTO;

use Closure;

class Channel
{
    private string $key;

    private Closure $action;

    public function __construct(string $key, Closure $action)
    {
        $this->key = $key;
        $this->action = $action;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getAction(): Closure
    {
        return $this->action;
    }
}
