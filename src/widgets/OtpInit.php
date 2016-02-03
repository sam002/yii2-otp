<?php
/**
 * Author: Semen Dubina
 * Date: 20.01.16
 * Time: 4:22
 */

namespace widgets;


use dosamigos\qrcode\lib\Enum;
use dosamigos\qrcode\QrCode;
use sam002\otp\Otp;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\validators\UrlValidator;

class OtpInit extends Widget
{
    /**
     * @var array
     */
    public $QrParams = [
        'outfile' => false,
        'level' => Enum::QR_ECLEVEL_L,
        'size' => 3,
        'margin' => 4,
        'saveAndPrint' => false,
        'type' => Enum::QR_FORMAT_PNG
    ];

    /**
     * @var \OTPHP\OTP
     */
    private $otp = null;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        parent::run();
        $this->renderWidget();
    }

    /**
     * @return string
     * @throws InvalidConfigException
     */
    private function renderWidget()
    {
        return $this->render('/init', [
            'qr' => $this->generateQr($this->otp->getProvisioningUri()),
            'code' => $this->otp->getSecret()
        ]);
    }

    /**
     * @param string $text
     * @return array|int
     * @throws InvalidConfigException
     */
    private function generateQr($text = '')
    {
        $outfile = false;
        $level = Enum::QR_ECLEVEL_L;
        $size = 3;
        $margin = 4;
        $saveAndPrint = false;
        $type = Enum::QR_FORMAT_PNG;

        foreach ($this->QrParams as $name => $param) {
            switch ($name) {
                case 'outfile':
                    $outfile = $this->checkParamOutfile($param);
                    break;
                case 'level':
                    $level = $this->checkParamLevel($param);
                    break;
                case 'size':
                    $size = $this->checkParamSize($param);
                    break;
                case 'margin':
                    $margin = $this->checkParamMargin($param);
                    break;
                case 'saveAndPrint':
                    $saveAndPrint = $this->checkParamSaveAndPrint($param);
                    break;
                case 'type':
                    $type = $this->checkParamType($param);
                    break;
            }
        }

        return QrCode::encode($text, $outfile, $level, $size, $margin, $saveAndPrint, $type);
    }

    private function checkParamOutfile($outfile)
    {
        if (!is_bool($outfile)) {
            throw new InvalidConfigException('OtpInit::$QrParams[\'outfile\'] is not boolean');
        }
        return $outfile;
    }

    private function checkParamLevel($level)
    {
        $qrLevels = [
            Enum::QR_ECLEVEL_L,
            Enum::QR_ECLEVEL_M,
            Enum::QR_ECLEVEL_Q,
            Enum::QR_ECLEVEL_H,
        ];
        if (in_array($level, $qrLevels, true)) {
            throw new InvalidConfigException('OtpInit::$QrParams[\'level\'] is not QR_ECLEVEL_*');
        }
        return $level;
    }

    private function checkParamSize($size)
    {
        if (!is_integer($size)) {
            throw new InvalidConfigException('OtpInit::$QrParams[\'size\'] is not integer');
        }
        return $size;
    }

    private function checkParamMargin($margin)
    {
        if (!is_integer($margin)) {
            throw new InvalidConfigException('OtpInit::$QrParams[\'margin\'] is not integer');
        }
        return $margin;
    }


    private function checkParamSaveAndPrint($saveAndPrint)
    {
        if (!is_bool($saveAndPrint)) {
            throw new InvalidConfigException('OtpInit::$QrParams[\'saveAndPrint\'] is not boolean');
        }
        return $saveAndPrint;
    }

    private function checkParamType($type)
    {
        $qrTypes = [
            Enum::QR_FORMAT_PNG,
            Enum::QR_FORMAT_JPG,
            Enum::QR_FORMAT_RAW,
            Enum::QR_FORMAT_TEXT
        ];
        if (in_array($type, $qrTypes, true)) {
            throw new InvalidConfigException('OtpInit::$QrParams[\'type\'] is not Enum::QR_FORMAT_*');
        }
        return $type;
    }
}