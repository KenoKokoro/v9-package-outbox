<?php

namespace V9\Outbox\Models;

use Illuminate\Database\Eloquent\Model;
use V9\Outbox\Contracts\OutboxInstance;

class Outbox extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_ERROR = 'error';
    const STATUS_DONE = 'done';

    protected string $table = 'outbox';

    protected array $fillable = [
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
    ];

    public function setContentAttribute(OutboxInstance $instance): void
    {
        $this->attributes['content'] = encrypt(base64_encode(serialize($instance)));
    }

    public function getContentAttribute(string $content): OutboxInstance
    {
        return unserialize(base64_decode(decrypt($content)));
    }
}
