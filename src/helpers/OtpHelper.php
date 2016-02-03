<?php
/**
 * Author: Semen Dubina
 * Date: 03.02.16
 * Time: 23:06
 */

namespace sam002\helpers;

use Base32\Base32;
use yii\base\Security;
use OTPHP\HOTP;
use OTPHP\TOTP;

class OtpHelper
{

    /**
     * @param int $length
     * @return string
     */
    static public function generateSecret($length = 16)
    {
        $security = new Security();
        return Base32::encode($security->generateRandomString($length));
    }

    /**
     * @param string $lable
     * @param int $digits
     * @param string $digets
     * @param int $interval
     * @return TOTP
     */
    static public function getTotp($lable = '', $digits = 6, $digets = 'sha1', $interval = 30)
    {
        $totp = new TOTP();
        $totp->setLabel($lable)
            ->setDigits($digits)
            ->setDigest($digets)
            ->setInterval($interval);

        return $totp;
    }

    /**
     * @param string $lable
     * @param int $digits
     * @param string $digets
     * @param int $counter
     * @return HOTP
     */
    static public function getHotp($lable = '', $digits = 6, $digets = 'sha1', $counter = 0)
    {
        $totp = new HOTP();
        $totp->setLabel($lable)
            ->setDigits($digits)
            ->setDigest($digets)
            ->setCounter($counter);

        return $totp;
    }
}