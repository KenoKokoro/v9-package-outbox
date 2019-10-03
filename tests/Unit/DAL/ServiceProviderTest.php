<?php

namespace V9\Tests\Outbox\Unit\DAL;

use Illuminate\Contracts\Foundation\Application;
use Mockery\MockInterface;
use V9\Outbox\DAL\Outbox\EloquentOutbox;
use V9\Outbox\DAL\Outbox\OutboxRepository;
use V9\Outbox\DAL\ServiceProvider;
use V9\Response\Http\HttpFactory;
use V9\Response\Http\HttpFactoryInterface;
use V9\Response\Http\Json\Factory as JsonFactory;
use V9\Response\Http\Json\JsonResponseInterface;
use V9\Tests\Outbox\Unit\TestCase;
use Mockery as m;

class ServiceProviderTest extends TestCase
{
    /**
     * @var MockInterface|Application
     */
    private MockInterface $application;

    private ServiceProvider $fixture;

    protected function setUp(): void
    {
        $this->application = m::mock(Application::class);
        $this->fixture = new ServiceProvider($this->application);
        parent::setUp();
    }

    /** @test */
    public function should_register_response_modules(): void
    {
        $this->application
            ->shouldReceive('bind')
            ->once()
            ->with(OutboxRepository::class, EloquentOutbox::class);

        $this->fixture->register();
        self::assertTrue(true);
    }
}
