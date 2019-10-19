<?php

namespace V9\Tests\Outbox\Unit\Models;

use V9\Outbox\Contracts\OutboxInstance;
use V9\Outbox\Models\Outbox;
use V9\Tests\Outbox\Stub\OutboxInstanceStub;
use V9\Tests\Outbox\Unit\TestCase;

class OutboxTest extends TestCase
{
    private Outbox $fixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixture = new Outbox();
    }

    /** @test */
    public function outbox_model_should_have_table_set(): void
    {
        self::assertEquals('v9_outbox', $this->fixture->getTable());
    }

    /** @test */
    public function outbox_model_should_have_fillable_values_set(): void
    {
        self::assertEquals([
            'channel',
            'content',
            'send_at',
            'sent_at',
            'status',
            'subject_id',
            'subject_type',
            'receiver_id',
            'receiver_type',
            'try',
            'error',
        ], $this->fixture->getFillable());
    }
}
