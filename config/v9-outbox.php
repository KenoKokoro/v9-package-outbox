<?php

use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Contracts\Mail\Mailer;

return [
    'map' => [
        [
            'key' => 'mail',
            'action' => function(Mailable $mailable, Mailer $mailer) {
                $mailer->send($mailable);
            },
        ],
    ],
];
