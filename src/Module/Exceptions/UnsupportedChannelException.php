<?php

namespace V9\Outbox\Module\Exceptions;

use Throwable;

class UnsupportedChannelException extends \DomainException
{
    public function __construct(string $channel, Throwable $previous = null)
    {
        $message = "Channel [{$channel}] is not added to the supported list.";

        parent::__construct($message, 0, $previous);
    }
}
