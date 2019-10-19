<?php

namespace V9\Tests\Outbox\Unit\Module\Schedule;

use Exception;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Support\Carbon;
use Mockery as m;
use Mockery\MockInterface;
use V9\Outbox\Contracts\ReceiverInstance;
use V9\Outbox\Contracts\SubjectInstance;
use V9\Outbox\DAL\Outbox\OutboxRepository;
use V9\Outbox\Models\Outbox;
use V9\Outbox\Module\Channel\DTO\Channel;
use V9\Outbox\Module\Channel\DTO\ChannelCollectionInterface;
use V9\Outbox\Module\Exceptions\UnsupportedChannelException;
use V9\Outbox\Module\Schedule\ScheduleInterface;
use V9\Outbox\Module\Schedule\V9Schedule;
use V9\Tests\Outbox\Stub\OutboxInstanceStub;
use V9\Tests\Outbox\Unit\TestCase;

class V9ScheduleTest extends TestCase
{
    /**
     * @var MockInterface|OutboxRepository
     */
    private MockInterface $repository;

    /**
     * @var MockInterface|ChannelCollectionInterface
     */
    private MockInterface $supportedChannels;

    /**
     * @var MockInterface|Encrypter
     */
    private MockInterface $encrypter;

    /**
     * @var MockInterface|Container
     */
    private MockInterface $container;

    /**
     * @var MockInterface|Outbox
     */
    private MockInterface $outbox;

    private V9Schedule $fixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = m::mock(OutboxRepository::class);
        $this->supportedChannels = m::mock(ChannelCollectionInterface::class);
        $this->encrypter = m::mock(Encrypter::class);
        $this->container = m::mock(Container::class);
        $this->outbox = m::mock(Outbox::class);
        $this->fixture = new V9Schedule(
            $this->repository,
            $this->supportedChannels,
            $this->encrypter,
            $this->container
        );
    }

    /** @test */
    public function v9_schedule_should_be_instance_of_schedule_interface(): void
    {
        self::assertInstanceOf(ScheduleInterface::class, $this->fixture);
    }

    /** @test */
    public function v9_schedule_should_put_new_outbox_record_to_database_with_default_send_at(): void
    {
        $channel = m::mock(Channel::class);
        $outboxInstance = new OutboxInstanceStub;
        $subject = m::mock(SubjectInstance::class);
        $receiver = m::mock(ReceiverInstance::class);
        $channel
            ->shouldReceive('getKey')
            ->once()->andReturn('channel1');
        $this->supportedChannels
            ->shouldReceive('has')
            ->once()->with('channel1')
            ->andReturnTrue();
        $this->supportedChannels
            ->shouldReceive('get')
            ->once()->with('channel1')
            ->andReturn($channel);
        $this->encrypter
            ->shouldReceive('encrypt')
            ->once()->andReturn('encrypt-content');

        $subject
            ->shouldReceive('getKey')
            ->once()->andReturn('subject-key');
        $subject
            ->shouldReceive('getMorphClass')
            ->once()->andReturn('subject-type');
        $receiver
            ->shouldReceive('getKey')
            ->once()->andReturn('receiver-key');
        $receiver
            ->shouldReceive('getMorphClass')
            ->once()->andReturn('receiver-type');

        $this->repository
            ->shouldReceive('create')
            ->once()->with($this->putAttributesStub('2019-03-20 08:00:00'))
            ->andReturn($this->outbox);

        $actual = $this->fixture->put($outboxInstance, 'channel1', $subject, $receiver);
        self::assertInstanceOf(Outbox::class, $actual);
    }

    /** @test */
    public function v9_schedule_should_put_new_outbox_record_to_database_with_default_given_send_at(): void
    {
        $channel = m::mock(Channel::class);
        $outboxInstance = new OutboxInstanceStub;
        $subject = m::mock(SubjectInstance::class);
        $receiver = m::mock(ReceiverInstance::class);
        $channel
            ->shouldReceive('getKey')
            ->once()->andReturn('channel1');
        $this->supportedChannels
            ->shouldReceive('has')
            ->once()->with('channel1')
            ->andReturnTrue();
        $this->supportedChannels
            ->shouldReceive('get')
            ->once()->with('channel1')
            ->andReturn($channel);
        $this->encrypter
            ->shouldReceive('encrypt')
            ->once()->andReturn('encrypt-content');

        $subject
            ->shouldReceive('getKey')
            ->once()->andReturn('subject-key');
        $subject
            ->shouldReceive('getMorphClass')
            ->once()->andReturn('subject-type');
        $receiver
            ->shouldReceive('getKey')
            ->once()->andReturn('receiver-key');
        $receiver
            ->shouldReceive('getMorphClass')
            ->once()->andReturn('receiver-type');

        $this->repository
            ->shouldReceive('create')
            ->once()->with($this->putAttributesStub('2019-03-22 08:00:00'))
            ->andReturn($this->outbox);

        $sendAt = Carbon::now()->addDays(2);
        $actual = $this->fixture->put($outboxInstance, 'channel1', $subject, $receiver, $sendAt);
        self::assertInstanceOf(Outbox::class, $actual);
    }

    /** @test */
    public function v9_schedule_should_throw_exception_for_unsupported_channel(): void
    {
        self::expectException(UnsupportedChannelException::class);

        $outboxInstance = new OutboxInstanceStub;
        $subject = m::mock(SubjectInstance::class);
        $receiver = m::mock(ReceiverInstance::class);
        $this->supportedChannels
            ->shouldReceive('has')
            ->once()->with('channel1')
            ->andReturnFalse();

        $this->fixture->put($outboxInstance, 'channel1', $subject, $receiver);
    }

    /** @test */
    public function v9_schedule_should_mark_outbox_record_as_done(): void
    {
        $this->repository
            ->shouldReceive('update')
            ->once()->with(
                $this->outbox,
                [
                    'sent_at' => '2019-03-20 08:00:00',
                    'status' => 'done',
                    'error' => null,
                ]
            );

        $this->fixture->done($this->outbox);
        self::assertTrue(true);
    }

    /** @test */
    public function v9_schedule_should_mark_outbox_record_as_processing(): void
    {
        $this->outbox
            ->shouldReceive('getAttribute')
            ->once()->with('try')
            ->andReturn(2);
        $this->repository
            ->shouldReceive('update')
            ->once()->with(
                $this->outbox,
                [
                    'status' => 'running',
                    'error' => null,
                    'try' => 3,
                ]
            );

        $this->fixture->processing($this->outbox);
        self::assertTrue(true);
    }

    /** @test */
    public function v9_schedule_should_mark_outbox_record_as_error(): void
    {
        $exception = new Exception('There is some error.');
        $this->repository
            ->shouldReceive('update')
            ->once()->with(
                $this->outbox,
                [
                    'status' => 'error',
                    'error' => 'There is some error.',
                ]
            );

        $this->fixture->error($this->outbox, $exception);
        self::assertTrue(true);
    }

    private function putAttributesStub(string $sendAtString): array
    {
        return [
            'channel' => 'channel1',
            'content' => 'encrypt-content',
            'send_at' => $sendAtString,
            'sent_at' => null,
            'status' => 'pending',
            'subject_id' => 'subject-key',
            'subject_type' => 'subject-type',
            'receiver_id' => 'receiver-key',
            'receiver_type' => 'receiver-type',
            'try' => 0,
            'error' => null,
        ];
    }
}
