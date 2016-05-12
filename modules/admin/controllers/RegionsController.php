<?php

namespace app\modules\admin\controllers;

use Yii;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use app\components\BaseController;
use app\models\Region;
use app\modules\admin\models\search\RegionSearch;

class RegionsController extends BaseController
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'operations' => ['post'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'operations' => [
                'class' => 'app\modules\admin\controllers\common\OperationsAction',
                'modelName' => 'app\models\Region',
            ],
            'delete' => [
                'class' => 'app\modules\admin\controllers\common\DeleteAction',
                'modelName' => 'app\models\Region',
            ],
        ];
    }

    public function actionIndex()
    {
        $regionSearch = new RegionSearch();
        $dataProvider = $regionSearch->search(Yii::$app->request->get());

        return $this->render('index', [
            'regionSearch' => $regionSearch,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionEdit($id = null)
    {
        $model = new Region();

        if ($id) {
            $model = $this->loadModel($model, $id);
        }

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Saved successfully'));
                if (Yii::$app->request->isAjax) {
                    return $this->response(['redirect' => Url::toRoute(['edit', 'id' => $model->region_id])]);
                }
            } else {
                if (Yii::$app->request->isAjax) {
                    return $this->response(\app\helpers\Util::getValidationErrors($model));
                }
            }
        }

        return $this->render('edit', ['model' => $model]);
    }
}
