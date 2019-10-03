<?php

namespace V9\Outbox\DAL;

use V9\Outbox\DAL\Outbox\EloquentOutbox;
use V9\Outbox\DAL\Outbox\OutboxRepository;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OutboxRepository::class, EloquentOutbox::class);
    }
}
