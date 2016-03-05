# yii2-otp


[![Latest Version](https://img.shields.io/github/tag/sam002/yii2-otp.svg?style=flat-square&label=releas)](https://github.com/sam002/yii2-otp/tags)
[![Software License](https://img.shields.io/badge/license-LGPL3-brightgreen.svg?style=flat-square)](LICENSE.md)

YII2 extension  for generating one time passwords according to RFC 4226 (HOTP Algorithm) and the RFC 6238 (TOTP Algorithm)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require sam002/yii2-otp:~0.1
```
or add

```json
"sam002/yii2-otp" : "~0.1"
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
        'class' => 'Otp',
        // 'totp' only now
        'algorithm' => sam002\otp\Collection::ALGORITHM_TOTP
        
        // length of code
        'digits' => 6,
        
        //  Algorithm for hashing
        'digets' => 'sha1',
        
        // Lable of application
        'lable' => 'yii2-otp',
        
        // Uri to image (application icon)
        'imgLabelUrl' => Yii::to('/icon.png'),
        
        // Betwen 8 and 1024
        'secretLength' => 64
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
        
        //Window in time for check authorithation (current +/- window*interval) 
        //'window' => 0
    ],
...
]
```

**Widget use**
Widget for generate init QR-code

```php
use sam002\otp\widgets\OtpInit;

<?php echo $form->field($model, 'otpSecret')->widget(
                    OtpInit::className() ,[
                        'component'=>'otp',
                        
                        // link text
                        'link' => 'ADD OTP BY LINK',
                        
                        'QrParams' => [
                            // pixels per cell
                            'size' => 3,
                            
                            // margin around QR-code
                            'margin' => 5,
                            
                            // by default image create and save at Yii::$app->runtimePath . '/temporaryQR/'
                            'outfile' => '/tmp/'.uniqid(),
                            
                            // save or delete after generate
                            'save' => false,
                        ]
                ]); ?>
```

**Validation. Additional examples**

```php
// login view
<?php
            ...
            <?php echo $form->field($model, 'username') ?>
            <?php echo $form->field($model, 'otp')->passwordInput() ?>
            ...

// login form model
<?php
     /**
     * Validates the OTP.
     */
    public function validateOtp()
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validateOtpSecret($this->otp)) {
                $this->addError('otp', Yii::t('common', 'Incorrect code.'));
            }
        }
    }
```


Further Information
-------------------
- [About HOTP](https://en.wikipedia.org/wiki/HMAC-based_One-time_Password_Algorithm)
- [About TOTP](https://en.wikipedia.org/wiki/Time-based_One-time_Password_Algorithm)
- [otphp docs](https://github.com/Spomky-Labs/otphp/tree/master/doc)
- [yii2-qrcode-helper](https://github.com/2amigos/yii2-qrcode-helper)


Credits
-------

- [sam002](https://github.com/sam002)
- [All Contributors](../../contributors)


License
-------

The LGPLv3 License. Please see [License File](LICENSE.md) for more information.

