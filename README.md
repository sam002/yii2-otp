# yii2-otp
[![Code Climate](https://codeclimate.com/github/sam002/yii2-otp/badges/gpa.svg)](https://codeclimate.com/github/sam002/yii2-otp)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/cb3e45a9-f9a3-4843-bfe5-56a1cc7883d2/small.png)](https://insight.sensiolabs.com/projects/cb3e45a9-f9a3-4843-bfe5-56a1cc7883d2)

[![Latest Version](https://img.shields.io/github/tag/sam002/yii2-otp.svg?style=flat-square&label=releas)](https://github.com/sam002/yii2-otp/tags)
[![Software License](https://img.shields.io/badge/license-LGPL3-brightgreen.svg?style=flat-square)](LICENSE.md)

[![Build Status](https://travis-ci.org/sam002/yii2-otp.svg?branch=master)](https://travis-ci.org/sam002/yii2-otp)

YII2 extension  for generating one time passwords according to RFC 4226 (HOTP Algorithm) and the RFC 6238 (TOTP Algorithm)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require sam002/yii2-otp:~2.0.0
```
or add

```json
"sam002/yii2-otp" : "~2.0.0"
```

to the require section of your application's `composer.json` file.


Usage
-----

After extension is installed you need to setup auth client collection application component:

**Configure**

```php
<?php
use sam002\otp\Otp;

...

'components' => [
    'otp' => [
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
        'imgLabelUrl' => Yii::to('/icon.png'),
        
        // Betwen 8 and 1024
        'secretLength' => 64,
        // Time interval in seconds, must be at least 1
        'interval'
    ],
...
]
```

**Add behavior**
Add any model column for storing secure code. //My case: the use of two-factor authentication 

```php
<?php
use sam002\otp\behaviors\OtpBehavior;

...

'behavior' => [
    'otp' => [
        'class' => OtpBehavior::className(),
        // Component name
        'component' => 'otp',
        
        // column|property name for get and set secure phrase
        //'secretAttribute' => 'secret'
        // column|property name for get code and confirm secret
        //'codeAttribute' => 'secret'
        
        //Window in time for check authorithation (current +/- window*interval) 
        //'window' => 0
    ],
...
]
```

**Widget use**
Widget for generate init QR-code.
Read more about QrParams in the [qrcode-library](https://github.com/2amigos/qrcode-library).

```php
use sam002\otp\widgets\OtpInit;

<?php echo $form->field($model, 'secret')->widget(
                    OtpInit::className() ,[
                        'component'=>'otp',
                        
                        // link text
                        'link' => 'ADD OTP BY LINK',
                        
                        'QrParams' => [
                            // pixels width
                            'size' => 300,
                            
                            // margin around QR-code
                            'margin' => 10,
                            
                            // Path to logo on image
                            'logo' => '/icon.png',
                            
                            // Width logo on image
                            'logoWidth' => 50,
                            
                            // RGB color
                            'foregroundColor' => [0,0,0],
                            
                            // RGB color
                            'backgroundColor' => [255,255,255],
                            
                            // Qulity of QR: LOW, MEDIUM, HIGHT, QUARTILE
                            'level' => ErrorCorrectionLevelInterface::HIGH,
                            
                            // Image format: PNG, JPG, SVG, EPS
                            'type' => PngWriter::class,
                            
                            // Locale
                            'encoding' => 'UTF-8',
                            
                            // Text on image under QR code
                            'label' => 'QR code',
                            
                            // by default image create and save at Yii::$app->runtimePath . '/temporaryQR/'
                            'outfile' => '/tmp/'.uniqid(),
                            
                            // save or delete after generate
                            'save' => false,
                        ]
                ]); ?>
```

Further Information
-------------------
- [About HOTP](https://en.wikipedia.org/wiki/HMAC-based_One-time_Password_Algorithm)
- [About TOTP](https://en.wikipedia.org/wiki/Time-based_One-time_Password_Algorithm)
- [otphp docs](https://github.com/Spomky-Labs/otphp/tree/v8.3/doc)
- [qrcode-library](https://github.com/2amigos/qrcode-library)


Credits
-------

- [sam002](https://github.com/sam002)
- [All Contributors](../../contributors)


License
-------

The LGPLv3 License. Please see [License File](LICENSE) for more information.

