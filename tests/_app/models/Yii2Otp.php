<?php
/**
 * Author: Semen Dubina
 * Date: 03.01.18
 * Time: 14:23
 */

namespace app\models;

use sam002\otp\behaviors\OtpBehavior;
use yii\redis\ActiveRecord;

/**
 * Yii2Otp is the model behind example page.
 * @property string $name
 * @property string $secret
 * @property string $code
 * @property string $counter
 */
class Yii2Otp extends ActiveRecord
{
//    public $name;
//    public $secret;
    public $code;


    /**
     * @return array the list of attributes for this record
     */
    public function attributes()
    {
        return ['name', 'secret', 'counter'];
    }

    public static function primaryKey()
    {
        return ['name'];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'otp' => [
                'class' => OtpBehavior::className(),
                'component' => 'otpTotpBase',
//                'secretAttribute' => 'secret',
//                'codeAttribute' => 'code'
            ]
        ];
    }

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            ['name', 'filter', 'filter' => 'trim'],
            [['name', 'code', 'secret'], 'required'],
            ['name', 'unique',
                'message' => 'This name has already been taken.',
            ],
            ['name', 'string', 'min' => 1, 'max' => 255],
            [['name', 'code'], 'string'],
//            ['counter', 'integer', 'min' => 1],
//            ['counter', 'default', 'value' => 1],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Name',
            'secret' => 'Secret',
            'code' => 'Confirm code',
            'verifyCode' => 'Verification Code'
        ];
    }
}