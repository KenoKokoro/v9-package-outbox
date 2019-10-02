<?php

namespace V9\Outbox\Providers;

use V9\Outbox\Module\OutboxScheduler;
use V9\Outbox\Module\V9Schedule;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OutboxScheduler::class, V9Schedule::class);
    }
}
