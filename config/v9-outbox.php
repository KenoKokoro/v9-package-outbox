<?php

use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Mail\MailQueue;

return [
    'map' => [
        [
            'key' => 'mail',
            'action' => function(Mailable $mailable, Mailer $mailer) {
                $mailer->send($mailable);
            },
        ],
        [
            'key' => 'mail-queue',
            'action' => function(Mailable $mailable, MailQueue $mailer) {
                $mailer->queue($mailable);
            },
        ],
    ],
];
