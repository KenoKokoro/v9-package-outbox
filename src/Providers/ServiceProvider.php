<?php

namespace V9\Outbox\Providers;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use V9\Outbox\DAL\Outbox\OutboxRepository;
use V9\Outbox\DAL\ServiceProvider as DalServiceProvider;
use V9\Outbox\Module\Channel\FactoryInterface;
use V9\Outbox\Module\ChannelFactory;
use V9\Outbox\Module\Schedule\ScheduleInterface;
use V9\Outbox\Module\Schedule\V9Schedule;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/v9-outbox.php', 'v9-outbox');

        $this->app->register(DalServiceProvider::class);
        $this->app->bind(FactoryInterface::class, ChannelFactory::class);
        $this->app->bind(ScheduleInterface::class, function() {
            /** @var FactoryInterface $factory */
            $factory = $this->app->make(FactoryInterface::class);
            /** @var ConfigRepository $config */
            $config = $this->app->make(ConfigRepository::class);

            return new V9Schedule(
                $this->app->make(OutboxRepository::class),
                $factory->createCollection($config->get('v9-outbox.map', []))
            );
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom([
            __DIR__ . '/../../database/migrations/',
        ]);

        $this->publishes([
            __DIR__ . '/../../config/v9-outbox.php' => $this->app->basePath('config/v9-outbox.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../../database/migrations/2019_10_03_00000_create_outbox_table.php' => $this->app->databasePath('migrations'),
        ], 'migrations');
    }
}
