<?php

namespace V9\Tests\Outbox\Unit\Models;

use V9\Outbox\Contracts\OutboxInstance;
use V9\Outbox\Models\Outbox;
use V9\Tests\Outbox\Stub\OutboxInstanceStub;
use V9\Tests\Outbox\Unit\TestCase;

class OutboxTest extends TestCase
{
    private Outbox $fixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixture = new Outbox();
    }

    /** @test */
    public function outbox_model_should_have_table_set(): void
    {
        self::assertEquals('v9_outbox', $this->fixture->getTable());
    }

    /** @test */
    public function outbox_model_should_have_fillable_values_set(): void
    {
        self::assertEquals([
            'channel',
            'content',
            'send_at',
            'sent_at',
            'status',
            'subject_id',
            'subject_type',
            'receiver_id',
            'receiver_type',
            'try',
            'error',
        ], $this->fixture->getFillable());
    }

    /** @test */
    public function outbox_model_should_mutate_the_value_and_make_outbox_instance_to_string(): void
    {
        $instance = new OutboxInstanceStub;
        $this->fixture->content = $instance;

        self::assertTrue(is_string($this->fixture->getAttributes()['content']));
    }

    /** @test */
    public function outbox_model_should_mutate_the_value_and_make_string_from_outbox_instance(): void
    {
        $this->fixture->setRawAttributes(['content' => $this->instanceString()]);

        self::assertInstanceOf(OutboxInstance::class, $this->fixture->content);
    }

    private function instanceString(): string
    {
        return 'TzozOToiVjlcVGVzdHNcT3V0Ym94XFN0dWJcT3V0Ym94SW5zdGFuY2VTdHViIjoxOntzOjUwOiIAVjlcVGVzdHNcT3V0Ym94XFN0dWJcT3V0Ym94SW5zdGFuY2VTdHViAGF0dHJpYnV0ZSI7czo0OiJ0ZXN0Ijt9';
    }
}
