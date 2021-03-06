<?php

namespace V9\Outbox\Models;

use Illuminate\Database\Eloquent\Model;
use V9\DAL\Contracts\BaseModelInterface;
use V9\Outbox\Contracts\OutboxInstance;

/**
 * @property string         channel
 * @property OutboxInstance content
 * @property int            try
 * @property string         subject_id
 * @property string         subject_type
 * @property string         receiver_id
 * @property string         receiver_type
 */
class Outbox extends Model implements BaseModelInterface
{
    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_ERROR = 'error';
    const STATUS_DONE = 'done';

    protected $table = 'v9_outbox';

    protected $fillable = [
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
}
