<?php
/**
 * Author: Semen Dubina
 * Date: 19.01.16
 * Time: 15:34
 */

namespace sam002\otp\behaviors;

use Yii;
use sam002\otp\Otp;
use yii\base\Behavior;


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
 *           'component' => 'componentName',
 *           'window' => 0
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
    /**
     * @var string
     */
    public $component = 'otp';

    /**
     * @var string
     */
    public $secretAttribute = 'secret';

    /**
     * @var string
     */
    public $countAttribute = 'count';

    /**
     * @var int
     */
    public $window = 0;

    /**
     * @var Otp
     */
    private $otp = null;

    public function init()
    {
        parent::init();
        $this->otp = Yii::$app->get($this->component);

    }


    public function setOtpSecret($value)
    {
        $this->otp->setSecret($value);
    }

    public function getOtpSecret()
    {
        if (isset($this->owner->{$this->secretAttribute})) {
            $this->otp->setSecret($this->owner->{$this->secretAttribute});
        }
        return $this->otp->getSecret();
    }

    public function validateOtpSecret($code)
    {
        if ($this->getOtpSecret()) {
            return $this->otp->valideteCode($code, $this->window);
        }
        return false;
    }

}
