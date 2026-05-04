<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=127.0.0.1;port=8889;dbname=iform',
    'username' => 'root',
    'password' => 'root',
    'charset' => 'utf8',
    'on afterOpen' => static function ($event) {
        $event->sender->createCommand("SET time_zone = '+07:00'")->execute();
    },

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
