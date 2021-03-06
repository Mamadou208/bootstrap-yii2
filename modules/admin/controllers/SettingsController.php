<?php

namespace app\modules\admin\controllers;

use Yii;
use app\helpers\Model;
use app\components\BaseController;
use app\modules\admin\models\forms\SettingsForm;

class SettingsController extends BaseController
{
    public function actionIndex()
    {
        $model = new SettingsForm();

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                Yii::$app->settings->load($model->getAttributes());
                Yii::$app->session->setFlash('success', Yii::t('app.messages', 'Saved successfully'));
                return $this->refresh();
            }
            if (Yii::$app->request->isAjax) {
                return $this->response(Model::collectErrors($model));
            }
        }

        $model->setAttributes(Yii::$app->settings->all());

        return $this->render('index', ['model' => $model]);
    }
}
