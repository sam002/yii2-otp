<?php
/**
 * @var $this \yii\web\View
 * @var $model \app\models\Yii2Otp
 */

use sam002\otp\Otp;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$modelTotp = clone $model;
$modelHotp = clone $model;

$formCheck = ActiveForm::begin([
    'id' => 'check',
    'action' => ['check']
]);
echo $formCheck->field($model, 'name')->textInput();
echo $formCheck->field($model, 'code')->passwordInput();
echo Html::submitButton('Check');
ActiveForm::end();


$formTotp = ActiveForm::begin([
    'id' => Otp::ALGORITHM_TOTP,
    'action' => ['init']
]);
echo $formTotp->field($modelTotp, 'name')->textInput();
echo $formTotp->field($modelTotp, 'code')->textInput();
echo $formTotp->field($modelTotp, 'secret')->widget(
    \sam002\otp\widgets\OtpInit::className(), [
    'component' => 'otpTotpBase',
    'link'      => 'Add by totp link',
    'QrParams'  => [
        'size' => 2,
    ]
]);
echo Html::submitButton('Add');
ActiveForm::end();


$formHotp = ActiveForm::begin([
    'id' => Otp::ALGORITHM_HOTP,
    'action' => ['init']
]);
echo $formHotp->field($modelHotp, 'secret')->widget(
    \sam002\otp\widgets\OtpInit::className(), [
    'component' => 'otpTotpBase',
    'link'      => "Add by hotp link",
    'QrParams'  => [
        'size' => 3
    ]
]);
echo $formHotp->field($modelHotp, 'name')->textInput();
echo $formHotp->field($modelHotp, 'code')->passwordInput();
echo Html::submitButton('Add');
$formHotp::end();