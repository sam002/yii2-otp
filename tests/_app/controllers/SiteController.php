<?php

namespace app\controllers;

use app\models\Yii2Otp;
use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\widgets\ActiveForm;

class SiteController extends Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionOtp()
    {
        $model = new Yii2Otp();

        return $this->render('otp', [
            'model' => $model
        ]);
    }

    public function actionInit()
    {
        $model = new Yii2Otp();

        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$model->load(Yii::$app->request->post())) {
            return 'error';
        }
        if ($model->getIsNewRecord() && $model->save()) {
            return ['name' => $model->name];
        } else {
            if ($model->hasErrors()) {
                Yii::$app->response->setStatusCode(500);
            }
            return ['validation' => ActiveForm::validate($model)];
        }
        return 'empty';
    }

    public function actionCheck()
    {
        $model = new Yii2Otp();

        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$model->load(Yii::$app->request->post())) {
            return 'error';
        }

        $model = Yii2Otp::findOne($model->primaryKey);
        if (!$model) {
            return 'error';
        }
        $model->load(Yii::$app->request->post());

        return ActiveForm::validate($model);
    }
}
