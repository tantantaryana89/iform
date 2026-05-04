<?php
namespace app\assets;

use yii\web\AssetBundle;

class AdminKitAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl  = '@web';

    public $css = [
        'adminkit/css/app.css',
        'fontawesome/css/all.min.css',
    ];

    public $js = [
        'vendor/bootstrap/js/bootstrap.bundle.min.js',
        'adminkit/js/app.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',   // ini otomatis include jQuery + yii.js
    ];
}
