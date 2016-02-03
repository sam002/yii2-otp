<?php
/**
 * Author: Semen Dubina
 * Date: 19.01.16
 * Time: 15:34
 */

namespace sam002\otp\behaviors;

use sam002\otp\Otp;
use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\base\Security;


/**
 * Behavior for yii2-otp extension.
 *
 * For example:
 *
 * ```php
 * public function behaviors()
 * {
 *  return [
 *       'otp' => [
 *           'class' => OtpBehavior::className(),
 *           'component' => 'componentName'
 *       ],
 *  ];
 * }
 * ```
 *
 * @see https://en.wikipedia.org/wiki/Two-factor_authentication
 * @author sam002
 * @package sam002\otp
 */
class OtpBehavior extends Behavior
{

    public $type;
    public $column;
    public $counter = null;

    private $_otpSecret;
    private $_otpCode;


    public function setOtpUrl($value)
    {
        $this->otp->getProvisioningUri();
    }

    public function getOtpUrl()
    {
        return $this->_otpUrl;
        //if (!$this->owner->getIsNewRecord()
    }

    public function validate($code, $window = 0)
    {
        ;
    }

    //TODO validate
    //TODO generate
    //TODO save secrete
    //TODO increment counter

}