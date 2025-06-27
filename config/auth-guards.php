<?php

return [
    'guards' => [
        'admin' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        'tenant' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],
];
