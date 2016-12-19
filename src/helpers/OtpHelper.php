<?php
/**
 * Author: Semen Dubina
 * Date: 03.02.16
 * Time: 23:06
 */

namespace sam002\otp\helpers;

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
    static public function generateSecret($length = 20)
    {
        $security = new Security();
        $full = Base32::encode($security->generateRandomString($length));
        return substr($full, 0, $length);
    }

    /**
     * @param string $label
     * @param int $digits
     * @param string $digest
     * @param int $interval
     * @return TOTP
     */
    static public function getTotp($label = '', $digits = 6, $digest = 'sha1', $interval = 30)
    {
        $totp = new TOTP();
        $totp->setLabel($label)
            ->setDigits($digits)
            ->setDigest($digest)
            ->setInterval($interval);

        return $totp;
    }

    /**
     * @param string $label
     * @param int $digits
     * @param string $digest
     * @param int $counter
     * @return HOTP
     */
    static public function getHotp($label = '', $digits = 6, $digest = 'sha1', $counter = 0)
    {
        $totp = new HOTP();
        $totp->setLabel($label)
            ->setDigits($digits)
            ->setDigest($digest)
            ->setCounter($counter);

        return $totp;
    }
}
