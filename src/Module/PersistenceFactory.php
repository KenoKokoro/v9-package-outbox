<?php

namespace V9\Outbox\Module;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use V9\Outbox\DAL\Outbox\OutboxRepository;

class PersistenceFactory
{
    /**
     * @var Container
     */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @return OutboxRepository
     * @throws BindingResolutionException
     */
    public function repository(): OutboxRepository
    {
        return $this->container->make(OutboxRepository::class);
    }
}
