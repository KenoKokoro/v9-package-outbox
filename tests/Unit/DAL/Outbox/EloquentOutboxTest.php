<?php

namespace V9\Tests\Outbox\Unit\DAL\Outbox;

use Mockery as m;
use Mockery\MockInterface;
use V9\Outbox\DAL\Outbox\EloquentOutbox;
use V9\Outbox\DAL\Outbox\OutboxRepository;
use V9\Outbox\Models\Outbox;
use V9\Tests\Outbox\Unit\TestCase;

class EloquentOutboxTest extends TestCase
{
    private EloquentOutbox $fixture;

    private MockInterface $outbox;

    protected function setUp(): void
    {
        parent::setUp();
        $this->outbox = m::mock(Outbox::class);
        $this->fixture = new EloquentOutbox($this->outbox);
    }

    /** @test */
    public function eloquent_outbox_is_instance_of_outbox_repository(): void
    {
        self::assertInstanceOf(OutboxRepository::class, $this->fixture);
    }
}
