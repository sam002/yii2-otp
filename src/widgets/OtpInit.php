<?php
/**
 * Author: Semen Dubina
 * Date: 20.01.16
 * Time: 4:22
 */

namespace sam002\otp\widgets;


use dosamigos\qrcode\lib\Enum;
use dosamigos\qrcode\QrCode;
use sam002\otp\Otp;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\widgets\InputWidget;

class OtpInit extends InputWidget
{

    /**
     * @var string
     */
    public $component = 'otp';

    /**
     * @var bool|string
     */
    public $link = true;

    /**
     * @var array
     */
    public $QrParams;

    /**
     * @var array
     */
    private $defaultQrParams = [
        'outfile' => false,
        'level' => Enum::QR_ECLEVEL_Q,
        'size' => 3,
        'margin' => 4,
        'save' => false,
        'type' => Enum::QR_FORMAT_PNG
    ];

    /**
     * @var \OTPHP\OTP
     */
    private $otp = null;

    public function init()
    {
        /** @var Otp $component */
        $component = Yii::$app->get($this->component);
        parent::init();

        $secret = $this->model->{$this->attribute};
        if (!empty($secret)) {
            $component->setSecret($secret);
        }

        $this->otp = $component->getOtp();
        $this->QrParams = array_merge($this->defaultQrParams, $this->QrParams);
    }

    public function run()
    {
        parent::run();
        return $this->renderWidget();
    }


    /**
     * @return string
     * @throws InvalidConfigException
     */
    private function renderWidget()
    {
        $imgSrc = "data:image/jpeg;base64,";
        if($this->QrParams['type'] === Enum::QR_FORMAT_PNG) {
            $imgSrc = "data:image/png;base64,";
        }

        echo '<div><img src=' . $imgSrc . base64_encode($this->generateQr($this->otp->getProvisioningUri())) . ' /></div>';

        if ($this->link || is_string($this->link)) {
            echo Html::a($this->link, $this->otp->getProvisioningUri());
        }

        if ($this->hasModel()
            && $this->model->hasProperty($this->attribute)
            && empty($this->model->{$this->attribute})
        ) {
            $this->model->setAttributes([$this->attribute => $this->otp->getSecret()]);
        }
        echo Html::activeHiddenInput($this->model, $this->attribute, $this->options);
    }

    /**
     * @param string $text
     * @return array|int
     * @throws InvalidConfigException
     */
    private function generateQr($text = '')
    {
        $image = null;

        $outfile = false;
        $level = Enum::QR_ECLEVEL_L;
        $size = 3;
        $margin = 4;
        $save = false;
        $type = Enum::QR_FORMAT_JPG;

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
                case 'save':
                    $save = $this->checkParamSave($param);
                    break;
                case 'type':
                    $type = $this->checkParamType($param);
                    break;
            }
        }

        QrCode::encode($text, $outfile, $level, $size, $margin, false, $type);
        if(is_file($outfile)) {
            $image = file_get_contents($outfile);
        }
        if(!$save) {
            unlink($outfile);
        }
        return $image;
    }

    private function checkParamOutfile($outfile)
    {
        if(is_string($outfile) && !is_dir(dirname($outfile))) {
            throw new InvalidConfigException('OtpInit::$QrParams[\'outfile\'] error ' . dirname($outfile) . ' is not dir');
        } elseif (!is_string($outfile) && $outfile !== false) {
            throw new InvalidConfigException('OtpInit::$QrParams[\'outfile\'] is not false or file path');
        }
        if($outfile === false) {
            $outfile = Yii::$app->runtimePath . '/temporaryQR/' . uniqid();
            $dir = dirname($outfile);
            if (!is_dir($dir)) {
                FileHelper::createDirectory($dir);
            }
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
        if (!in_array($level, $qrLevels, true)) {
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


    private function checkParamSave($saveAndPrint)
    {
        if (!is_bool($saveAndPrint)) {
            throw new InvalidConfigException('OtpInit::$QrParams[\'save\'] is not boolean');
        }
        return $saveAndPrint;
    }

    private function checkParamType($type)
    {
        $qrTypes = [
            Enum::QR_FORMAT_PNG,
            Enum::QR_FORMAT_JPG,
        ];
        if (!in_array($type, $qrTypes, true)) {
            throw new InvalidConfigException('OtpInit::$QrParams[\'type\'] is not Enum::QR_FORMAT_PNG or Enum::QR_FORMAT_JPG');
        }
        return $type;
    }
}
