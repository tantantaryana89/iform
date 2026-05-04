<?php

use yii\web\User;
use yii\web\JsonParser;

return [

    // 🔐 Matikan total session & login untuk API
    'components' => [

        'user' => [
            'class' => User::class,

            // ⛔ API TIDAK pakai identity
            'identityClass' => null,

            // ⛔ API TIDAK pakai session
            'enableSession' => false,

            // ⛔ Jangan pernah redirect ke login
            'loginUrl' => null,
        ],

        // ✅ Pastikan JSON selalu diparse
        'request' => [
            'parsers' => [
                'application/json' => JsonParser::class,
            ],
        ],
    ],
];
