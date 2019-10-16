<?php

namespace V9\Tests\Outbox\Unit\Providers;

use Illuminate\Contracts\Foundation\Application;
use Mockery as m;
use Mockery\MockInterface;
use V9\Outbox\Providers\ServiceProvider;
use V9\Tests\Outbox\Unit\TestCase;

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
}
