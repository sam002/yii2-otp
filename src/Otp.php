<?php
/**
 * Author: Semen Dubina
 * Date: 19.01.16
 * Time: 15:24
 */

namespace sam002\otp;

use yii\base\Component;
use yii\base\InvalidConfigException;
use dosamigos\qrcode\lib\Enum;
use yii\validators\UrlValidator;
use sam002\helpers\OtpHelper;

/**
 * Class Collection is a single otp module with initialization and code-validation
 *
 * Example application configuration:
 *
 * ~~~
 *  'components' => [
 *      'otp' => [
 *          'class' => 'sam002\otp\Otp',
 *          'algorithm' => sam002\otp\Collection::ALGORITHM_HOTP
 *          'digits' => 6,
 *          'digets' => 'sha1',
 *          'lable' => 'yii2-otp',
 *          'imgLabelUrl' => Yii
 *     ]
 *     ...
 * ]
 * ~~~
 *
 * @author Semen Dubina <sam@sam002.net>
 * @package sam002\otp
 */
class Otp extends Component
{

    const ALGORITHM_TOTP = 'totp';
    const ALGORITHM_HOTP = 'hotp';

    /**
     * @var string
     */
    public $algorithm = self::ALGORITHM_HOTP;

    /**
     * @var int
     */
    public $digits = 6;

    /**
     * @var string
     */
    public $digets = 'sha1';

    /**
     * @var int
     */
    public $interval = 30;

    /**
     * @var int
     */
    public $counter = 0;

    /**
     * @var string
     */
    public $lable = 'yii2-otp';

    /**
     * @var null
     */
    public $imgLabelUrl = null;

    /**
     * @var array
     */
    public $qrParams = [
        'outfile' => false,
        'level' => Enum::QR_ECLEVEL_L,
        'size' => 3,
        'margin' => 4,
        'saveAndPrint' => false,
        'type' => Enum::QR_FORMAT_PNG
    ];

    private $otp = null;

    public function init()
    {
        parent::init();
        if ($this->algorithm === self::ALGORITHM_HOTP) {
            $this->otp = OtpHelper::getTotp($this->lable, $this->digits, $this->digets, $this->interval);
        } elseif ($this->algorithm === self::ALGORITHM_TOTP) {
            $this->otp = OtpHelper::getHotp($this->lable, $this->digits, $this->digets, $this->counter);
        } else {
            throw new InvalidConfigException('otp::$algorithm = \"' . $this->algorithm . '\" not allowed, only Otp::ALGORITHM_TOTP or Otp::ALGORITHM_HOTP');
        }

        if (!empty($this->imgLabelUrl) && is_string($this->imgLabelUrl)) {
            $validator = new UrlValidator();
            if ($validator->validate($this->imgLabelUrl)) {
                $this->otp->setImage($this->imgLabelUrl);
            } else {
                throw new InvalidConfigException($validator->message);
            }
        }
    }

    private function checkQrParams()
    {
        if(!is_array($this->qrParams) || empty($this->qrParams)) {

        }
        foreach ($this->qrParams as $name => $param) {
            switch ($name) {
                case 'outfile':
                    $this->checkParamOutfile($param);
                    break;
                case 'level':
                    $this->checkParamLevel($param);
                    break;
                case 'size':
                    $this->checkParamSize($param);
                    break;
                case 'margin':
                    $this->checkParamMargin($param);
                    break;
                case 'saveAndPrint':
                    $this->checkParamSaveAndPrint($param);
                    break;
                case 'type':
                    $this->checkParamType($param);
                    break;
            }
        }
    }
}