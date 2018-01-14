<?php


use sam002\otp\Otp;

class OtpCest
{
    public function _before(FunctionalTester $I)
    {
        $I->wantTo('Ensure that QrCodeAction and QrComponent works.');
        $I->amGoingTo('Call the configured action "otp".');
        $I->amOnRoute('/site/otp');
    }

    /**
     * @param FunctionalTester $I
     * @param $formId
     * @param array $data
     * @param null $at
     * @return \OTPHP\HOTPInterface|\OTPHP\TOTPInterface
     * @throws \yii\base\Exception
     */
    private function initOtp(FunctionalTester $I, $formId, $data=[], $at = null)
    {
        $I->expect('img element and otp link');
        $I->seeElement("#{$formId} div img[src]");
        $I->see("Add by {$formId} link");
        $linkOtp = $I->grabAttributeFrom("#{$formId} a", 'href');

        $I->wantTo('Ensure otp\'s link correct.');

        $otp = OTPHP\Factory::loadFromProvisioningUri($linkOtp);

        if (!isset($data['code'])) {
            $data['code'] = $otp->at(time());
        }
        $I->fillField("form#{$formId} #yii2otp-code", $data['code']);

        if (!isset($data['name'])) {
            $data['name'] = Yii::$app->getSecurity()->generateRandomString(15);
        }
        $I->fillField("form#{$formId} #yii2otp-name", $data['name']);

        $I->submitForm("form#{$formId}", $data, 'Add');

        return $otp;
    }

    /**
     * @param FunctionalTester $I
     * @param string $name
     * @param bool $needCorrect
     */
    private function checkInRedis(FunctionalTester $I, $name, $needCorrect = true)
    {
        $I->comment('Check by name in DB');
        $key = 'yii2_otp:a:'.\yii\redis\ActiveRecord::buildKey($name);
        if (!$needCorrect) {
            $I->dontSeeInRedis($key);
        } else {
            $I->seeRedisKeyContains($key, 'name', $name);
        }
    }

    /**
     * @param FunctionalTester $I
     * @param string $name
     * @param string $code
     */
    private function checkCode(FunctionalTester $I, $name, $code)
    {
        $I->comment('Check otp by code');
        $I->amOnRoute('/site/otp');
        $I->submitForm("form#check", [
            'Yii2Otp[name]' => $name,
            'Yii2Otp[code]' => $code
        ]);
        $I->seeResponseCodeIs(200);
    }

    public function testNotInitTotp(FunctionalTester $I)
    {
        $I->wantTo('Ensure that TOTP\'s init not accept wrong code.');
        $this->initOtp($I, Otp::ALGORITHM_TOTP, ['code'=>0]);

        $I->see('The code is incorrect.');
    }

    private function checkLimits(FunctionalTester $I, $name, $otp)
    {
        $currentIniTime = function() {
            $interval = Yii::$app->otpTotpBase->interval;
            return time() - (time() % $interval);
        };

        $magicLatency = 1;

        $conditions = [
            '[]' => [
                'Ensure code is correct at now' => function() {
                    return time();
                },
                'Ensure code is correct at begin of interval windows'=> function() use ($currentIniTime, $magicLatency) {
                    return $currentIniTime() + $magicLatency;
                },
                'Ensure code is correct at end of interval windows'=> function() use ($currentIniTime, $magicLatency) {
                    return $currentIniTime() + Yii::$app->otpTotpBase->interval - $magicLatency;
                },
            ],
            'The code is incorrect.' => [
                'Ensure code is not correct before interval' => function() use ($currentIniTime, $magicLatency) {
                    return $currentIniTime() - $magicLatency;
                },
                'Ensure code is not correct after interval' => function() use ($currentIniTime, $magicLatency) {
                    return $currentIniTime() + Yii::$app->otpTotpBase->interval + $magicLatency;
                },
            ]
        ];

        foreach ($conditions as $see => $condition) {
            foreach ($condition as $comment => $time) {
                $I->expect($comment);
                //Repit, if latency has shifted the window.
                $count = 0;
                do {
                    $at = $time();
                    $this->checkCode($I, $name, $otp->at($at));
                    if ($count > 3) {
                        break;
                    }
                    ++$count;
                } while ($at !== $time());
                $I->see($see);
            }
        }
    }

    public function testCorrectTotp(FunctionalTester $I)
    {
        $I->wantTo('Ensure that TOTP\'s init works.');
        $name = Yii::$app->getSecurity()->generateRandomString(15);

        $I->comment('Ensure name is new.');
        $this->checkInRedis($I, $name, false);

        /** @var \OTPHP\TOTP $otp */
        $otp = $this->initOtp($I, Otp::ALGORITHM_TOTP, ['name' => $name]);
        $I->see('"name":"'.$name.'"');

        $this->checkInRedis($I, $name);

        $this->checkLimits($I, $name, $otp);
    }
}
