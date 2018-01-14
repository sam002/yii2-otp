<?php
/**
 * Author: Semen Dubina
 * Date: 20.01.16
 * Time: 4:22
 */

namespace sam002\otp\widgets;

use BaconQrCode\Common\CharacterSetEci;
use Da\QrCode\Contracts\ErrorCorrectionLevelInterface;
use Da\QrCode\Contracts\WriterInterface;
use Da\QrCode\QrCode;
use Da\QrCode\Writer\EpsWriter;
use Da\QrCode\Writer\JpgWriter;
use Da\QrCode\Writer\PngWriter;
use Da\QrCode\Writer\SvgWriter;
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
        'logo' => null,
        'logoWidth' => null,
        'foregroundColor' => [0,0,0],
        'backgroundColor' => [255,255,255],
        'encoding' => 'UTF-8',
        'label' => null,
        'outfile' => false,
        'level' => ErrorCorrectionLevelInterface::HIGH,
        'size' => 300,
        'margin' => 10,
        'save' => false,
        'type' => PngWriter::class
    ];

    /**
     * @var \OTPHP\OTP
     */
    private $otp;

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
        switch ($this->QrParams['type']) {
            case JpgWriter::class :
                $imgSrc = "data:image/jpeg;base64,";
                $img = '<img src=' . $imgSrc . base64_encode($this->generateQr($this->otp->getProvisioningUri())) . ' />';
                break;
            case PngWriter::class :
                $imgSrc = "data:image/png;base64,";
                $img = '<img src=' . $imgSrc . base64_encode($this->generateQr($this->otp->getProvisioningUri())) . ' />';
                break;
            case SvgWriter::class :
            case EpsWriter::class :
            default:
                $img = $this->generateQr($this->otp->getProvisioningUri());
        }

        echo "<div>$img</div>";

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

        $qr = $this->initQr($text);
        $qr = $this->decorateQr($qr);
        $qr = $this->initLogoQr($qr);


        $image = $qr->writeString();

        if(
            isset($this->QrParams['save'])
            && $this->checkParamSave($this->QrParams['save'])
        ) {
            $outfile = $this->checkParamOutfile($this->QrParams['outfile']);
            file_put_contents($outfile, $image);
        }

        return $image;
    }

    private function initQr($text)
    {

        $level = ErrorCorrectionLevelInterface::QUARTILE;
        $type = PngWriter::class;
        $encoding = 'UTF-8';
        $label = null;

        foreach ($this->QrParams as $name => $param) {
            switch ($name) {
                case 'level':
                    $level = $this->checkParamLevel($param);
                    break;
                case 'type':
                    $type = $this->checkParamType($param);
                    break;
                case 'encoding':
                    $encoding = $this->checkParamEncoding($param);
                    break;
                case 'label':
                    $label = is_string($param) ? $param : null;
                    break;
            }
        }

        $writer = new $type();
        /** @var QrCode $qr */
        return (new QrCode($text, $level, $writer))
            ->useEncoding($encoding)
            ->setLabel($label);
    }

    private function initLogoQr($qr)
    {
        if (empty($this->QrParams['logo'])) {
            return $qr;
        }
        $with = isset($this->QrParams['logoWith']) ? $this->QrParams['logoWith'] : null;
        $logo = $this->checkParamLogo($this->QrParams['logo']);
        return $qr->useLogo($logo)
            ->setLogoWidth($with);
    }

    /**
     * @param QrCode $qr
     * @return QrCode
     * @throws InvalidConfigException
     */
    private function decorateQr(QrCode $qr)
    {

        $foreColor = [0,0,0];
        $backColor = [255,255,255];
        $size = 300;
        $margin = 10;
        $label = null;

        foreach ($this->QrParams as $name => $param) {
            switch ($name) {
                case 'foregroundColor':
                    $foreColor = $this->checkParamColor($param);
                    break;
                case 'backgroundColor':
                    $backColor = $this->checkParamColor($param);
                    break;
                case 'margin':
                    $margin = $this->checkParamMargin($param);
                    break;
                case 'size':
                    $size = $this->checkParamSize($param);
                    break;
            }
        }
        return $qr->useForegroundColor($foreColor[0], $foreColor[1], $foreColor[2])
            ->useBackgroundColor($backColor[0], $backColor[1], $backColor[2])
            ->setMargin($margin)
            ->setSize($size);
    }

    private function checkParamOutfile($outfile)
    {
        if(is_string($outfile) && !is_dir(dirname($outfile))) {
            throw new InvalidConfigException('OtpInit::$QrParams[\'outfile\'] error ' . dirname($outfile) . ' is not dir');
        } elseif (!is_string($outfile) && $outfile !== false) {
            throw new InvalidConfigException('OtpInit::$QrParams[\'outfile\'] is not false or file path');
        }
        if($outfile === false) {
            $outfile = Yii::$app->runtimePath . DIRECTORY_SEPARATOR .
                'yii2otp' . DIRECTORY_SEPARATOR . 'savedQr' . uniqid('qr_', true);
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
            ErrorCorrectionLevelInterface::LOW,
            ErrorCorrectionLevelInterface::MEDIUM,
            ErrorCorrectionLevelInterface::QUARTILE,
            ErrorCorrectionLevelInterface::HIGH
        ];
        if (!in_array($level, $qrLevels, true)) {
            throw new InvalidConfigException('OtpInit::$QrParams[\'level\'] is not ErrorCorrectionLevelInterface*');
        }
        return $level;
    }

    private function checkParamSize($size)
    {
        if (!is_int($size)) {
            throw new InvalidConfigException('OtpInit::$QrParams[\'size\'] is not integer');
        }
        return $size;
    }

    private function checkParamMargin($margin)
    {
        if (!is_int($margin)) {
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
        if(!is_subclass_of($type, WriterInterface::class)) {
            throw new InvalidConfigException('OtpInit::$QrParams[\'type\'] is not extend ' . WriterInterface::class);
        }
        return $type;
    }

    private function checkParamLogo($logo)
    {
        if (empty($logo)) {
            return null;
        }

        //Try process URL
        if (!is_file($logo)) {
            $img = file_get_contents($logo);

            $tmpFile = Yii::$app->runtimePath . DIRECTORY_SEPARATOR .
                    'yii2otp' . DIRECTORY_SEPARATOR . 'proxylogo'
                    . md5($logo);

            $dir = dirname($tmpFile);
            if (!is_dir($dir)) {
                FileHelper::createDirectory($dir);
            }
            $logo = file_put_contents($tmpFile, $img);
        }

        if (!is_file($logo)) {
            throw new InvalidConfigException('OtpInit::$QrParams[\'logo\'] '.$logo.' is not exist');
        }
        return $logo;
    }

    private function checkParamEncoding($encoding)
    {
        if (CharacterSetEci::getCharacterSetECIByName($encoding) === null) {
            throw new InvalidConfigException('OtpInit::$QrParams[\'encoding\'] Unknown '.$encoding.' encoding');
        }
        return $encoding;
    }

    private function checkParamColor($color)
    {

        if (!is_array($color)
            || count($color) != 3
            || 255 < $color[0]
            || 255 < $color[1]
            || 255 < $color[2]
            || 0 > $color[0]
            || 0 > $color[1]
            || 0 > $color[2]
        ) {
            throw new InvalidConfigException('OtpInit::$QrParams[\'encoding\'] Not correct color');
        }
        return $color;
    }
}
