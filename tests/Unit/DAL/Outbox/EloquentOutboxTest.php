<?php

namespace V9\Tests\Outbox\Unit\DAL\Outbox;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Mockery as m;
use Mockery\MockInterface;
use V9\DAL\Contracts\RepositoryInterface;
use V9\DAL\EloquentRepository;
use V9\Outbox\Contracts\OutboxInstance;
use V9\Outbox\Contracts\ReceiverInstance;
use V9\Outbox\Contracts\SubjectInstance;
use V9\Outbox\DAL\Outbox\EloquentOutbox;
use V9\Outbox\DAL\Outbox\OutboxRepository;
use V9\Outbox\Models\Outbox;
use V9\Tests\Outbox\Unit\TestCase;

class EloquentOutboxTest extends TestCase
{
    private EloquentOutbox $fixture;

    private MockInterface $builder;

    private MockInterface $outbox;

    protected function setUp(): void
    {
        parent::setUp();
        $this->outbox = m::mock(Outbox::class);
        $this->builder = m::mock(Builder::class);
        $this->fixture = new EloquentOutbox($this->outbox);
    }

    /** @test */
    public function eloquent_outbox_is_instance_of_outbox_repository(): void
    {
        self::assertInstanceOf(OutboxRepository::class, $this->fixture);
        self::assertInstanceOf(RepositoryInterface::class, $this->fixture);
        self::assertInstanceOf(EloquentRepository::class, $this->fixture);
    }
}
