<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'defaultRoute' => 'dashboard/index',
    'layout' => 'main',
    'timeZone' => 'Asia/Jakarta',

    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],

    // ⬇️ MODULES LETAK YANG BENAR
    'modules' => [
        'qrcode' => [
            'class' => 'app\modules\qrcode\Module',
        ],
        'api' => [
            'class' => 'app\modules\api\Module',
        ],
        'admin' => [
            'class' => 'mdm\admin\Module',
        ],
    ],

    'components' => [

        'request' => [
            'class' => yii\web\Request::class,
            'cookieValidationKey' => 'boZ08XolcY5cBh8J70Sa9f_Lh3JbWKDT',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],

        'assetManager' => [
            'bundles' => [
                'yii\bootstrap\BootstrapAsset' => false,
                'yii\bootstrap\BootstrapPluginAsset' => false,
                'yii\bootstrap4\BootstrapAsset' => false,
                'yii\bootstrap4\BootstrapPluginAsset' => false,
                'yii\web\JqueryAsset' => false,
            ],
        ],

        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],

        'user' => [
            'class' => yii\web\User::class,
            'identityClass' => 'app\models\User',
            'enableSession' => true,
            'enableAutoLogin' => false,
            'loginUrl' => ['site/login'],
        ],

        'errorHandler' => [
            'errorAction' => 'site/error',
        ],

        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            'useFileTransport' => true,
        ],

        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                // LOG ERROR UMUM
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],

                // 🔥 LOG DEBUG CHECKSHEET
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'info'],
                    'categories' => ['debug.checksheet'],
                    'logFile' => '@runtime/logs/debug-checksheet.log',
                ],
            ],
        ],

        'formatter' => [
            'class' => yii\i18n\Formatter::class,
            'timeZone' => 'Asia/Jakarta',
            'defaultTimeZone' => 'Asia/Jakarta',
        ],

        'db' => $db,
        
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            'itemTable' => '{{%auth_item}}',
            'itemChildTable' => '{{%auth_item_child}}',
            'assignmentTable' => '{{%auth_assignment}}',
            'ruleTable' => '{{%auth_rule}}',
        ],

        'urlManager' => [
            'enablePrettyUrl' => false,
            'showScriptName' => false,
        ],
    ],
    // 🔒 FIREWALL: Semua halaman wajib login (kecuali login & error)
    'as access' => [
        'class' => \yii\filters\AccessControl::class,
        'denyCallback' => function ($rule, $action) {
            return \Yii::$app->response->redirect(['site/login']);
        },
        'rules' => [
            // ✅ IZINKAN SEMUA ROUTE API TANPA LOGIN WEB
            [
                'allow' => true,
                'controllers' => ['api/*'],
            ],

            // login & error tetap bebas
            [
                'allow' => true,
                'actions' => ['login', 'error'],
                'controllers' => ['site'],
            ],

            // selain itu wajib login
            [
                'allow' => true,
                'roles' => ['@'],
            ],

            [
                'allow' => false,
            ],
        ],
    ],

    'params' => $params,
];



if (YII_ENV_DEV) {

    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
