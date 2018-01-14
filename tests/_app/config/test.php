<?php

use sam002\otp\Otp;

return [
    'id' => 'yii2-otp-test-web',
    'basePath' => dirname(__DIR__),
    'language' => 'en-US',
    'aliases' => [
        '@tests' => dirname(dirname(__DIR__)),
        '@vendor' => VENDOR_DIR,
        '@bower' => VENDOR_DIR . '/bower-asset',
    ],
    'components' => [
        'redis' => [
            'class' => yii\redis\Connection::className(),
            'hostname' => '127.0.0.1',
            'port' => 6379,
            'database' => 0,
        ],
        'otpTotpBase' => [
            'class' => Otp::className(),
            // 'totp' only now
            'algorithm' => sam002\otp\Otp::ALGORITHM_TOTP,
            // length of code
            'digits' => 6,
            //  Algorithm for hashing
            'digest' => 'sha1',
            // Label of application
            'label' => 'yii2-otp',
            // Uri to image (application icon)
//            'imgLabelUrl' => Yii::to('/icon.png'),
            // Betwen 8 and 1024
            'secretLength' => 64,
            // Time interval in seconds, must be at least 1
            'interval' => 30
        ],
        'assetManager' => [
            'basePath' => '@app/assets',
            'baseUrl' => '/',
        ],
        'mailer' => [
            'useFileTransport' => true,
        ],
        'urlManager' => [
            'showScriptName' => true,
        ],
        'request' => [
            'cookieValidationKey' => 'test',
            'enableCsrfValidation' => false,
        ]
    ],
    'params' => [],
];
