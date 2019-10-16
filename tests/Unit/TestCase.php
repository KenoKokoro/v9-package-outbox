<?php

namespace V9\Tests\Outbox\Unit;

use Illuminate\Support\Carbon;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        Carbon::setTestNow(Carbon::createFromFormat('Y-m-d H:i:s', '2019-03-20 08:00:00'));
        parent::setUp();
    }
}
