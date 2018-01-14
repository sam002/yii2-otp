<?php

return [
    'id' => 'yii2-otp-test-console',
    'basePath' => dirname(__DIR__),
    'aliases' => [
        '@tests' => dirname(dirname(__DIR__)),
    ],
    'components' => [
        'log' => null,
        'cache' => null,
    ],
];
