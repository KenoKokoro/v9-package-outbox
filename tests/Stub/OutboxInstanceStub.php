<?php

namespace V9\Tests\Outbox\Stub;

use V9\Outbox\Contracts\OutboxInstance;

class OutboxInstanceStub implements OutboxInstance
{
    private string $attribute = 'test';
}
