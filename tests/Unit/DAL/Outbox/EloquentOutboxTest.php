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

    /** @test */
    public function eloquent_outbox_should_schedule_an_entity(): void
    {
        $outboxInstance = m::mock(OutboxInstance::class);
        $subject = m::mock(SubjectInstance::class);
        $subject
            ->shouldReceive('getKey')
            ->once()
            ->andReturn('subject-key');
        $subject
            ->shouldReceive('getMorphClass')
            ->once()
            ->andReturn('subject-morph-class');
        $receiver = m::mock(ReceiverInstance::class);
        $receiver
            ->shouldReceive('getKey')
            ->once()
            ->andReturn('receiver-key');
        $receiver
            ->shouldReceive('getMorphClass')
            ->once()
            ->andReturn('receiver-morph-class');
        $sendAt = Carbon::now();
        $this->builder
            ->shouldReceive('create')
            ->once()
            ->with([
                'channel' => 'mail',
                'content' => $outboxInstance,
                'send_at' => $sendAt,
                'sent_at' => null,
                'status' => 'pending',
                'subject_id' => 'subject-key',
                'subject_type' => 'subject-morph-class',
                'receiver_id' => 'receiver-key',
                'receiver_type' => 'receiver-morph-class',
                'try' => 0,
                'error' => null,
            ])
            ->andReturn($this->outbox);
        $this->outbox
            ->shouldReceive('newQuery')
            ->once()
            ->andReturn($this->builder);

        $actual = $this->fixture->schedule($sendAt, $outboxInstance, 'mail', $subject, $receiver);
        self::assertInstanceOf(Outbox::class, $actual);
    }

    /** @test */
    public function eloquent_outbox_should_mark_outbox_record_as_done(): void
    {
        $this->outbox
            ->shouldReceive('getAttribute')
            ->once()
            ->with('try')
            ->andReturn(1);
        $this->outbox
            ->shouldReceive('setAttribute')
            ->once()
            ->with('sent_at', '2019-03-20 08:00:00')
            ->andReturnSelf();
        $this->outbox
            ->shouldReceive('setAttribute')
            ->once()
            ->with('status', 'done')
            ->andReturnSelf();
        $this->outbox
            ->shouldReceive('setAttribute')
            ->once()
            ->with('error', null)
            ->andReturnSelf();
        $this->outbox
            ->shouldReceive('setAttribute')
            ->once()
            ->with('try', 2)
            ->andReturnSelf();
        $this->outbox
            ->shouldReceive('save')
            ->once();

        $this->fixture->success($this->outbox);
        self::assertTrue(true);
    }

    /** @test */
    public function eloquent_outbox_should_mark_outbox_record_as_pending(): void
    {
        $this->outbox
            ->shouldReceive('setAttribute')
            ->once()
            ->with('status', 'running')
            ->andReturnSelf();
        $this->outbox
            ->shouldReceive('setAttribute')
            ->once()
            ->with('error', null)
            ->andReturnSelf();
        $this->outbox
            ->shouldReceive('save')
            ->once();

        $this->fixture->pending($this->outbox);
        self::assertTrue(true);
    }

    /** @test */
    public function eloquent_outbox_should_mark_outbox_record_as_error(): void
    {
        $this->outbox
            ->shouldReceive('getAttribute')
            ->once()
            ->with('try')
            ->andReturn(1);
        $this->outbox
            ->shouldReceive('setAttribute')
            ->once()
            ->with('status', 'error')
            ->andReturnSelf();
        $this->outbox
            ->shouldReceive('setAttribute')
            ->once()
            ->with('error', 'error message')
            ->andReturnSelf();
        $this->outbox
            ->shouldReceive('setAttribute')
            ->once()
            ->with('try', 2)
            ->andReturnSelf();
        $this->outbox
            ->shouldReceive('save')
            ->once();

        $this->fixture->error($this->outbox, 'error message');
        self::assertTrue(true);
    }
}
