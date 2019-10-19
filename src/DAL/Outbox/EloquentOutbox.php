<?php

namespace V9\Outbox\DAL\Outbox;

use V9\DAL\EloquentRepository;
use V9\Outbox\Models\Outbox;

class EloquentOutbox extends EloquentRepository implements OutboxRepository
{
    public function __construct(Outbox $model)
    {
        parent::__construct($model);
    }
}
