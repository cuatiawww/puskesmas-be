<?php

namespace app\controllers;

use Yii;
use app\models\Modul;
use app\models\ModulSearch;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * NavigasiController handles module/navigation management
 * This is the same as ModulController but with alias for "Navigasi" terminology
 */
class NavigasiController extends BaseController
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ]);
    }

    public function actionIndex()
    {
        $request = Yii::$app->request->queryParams;
        $searchModel = new ModulSearch();
        $dataProvider = $searchModel->search($request);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->render('view', ['model' => $model]);
    }

    public function actionCreate()
    {
        $model = new Modul();

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                Yii::$app->session->setFlash('swal', [
                    'icon' => 'success',
                    'title' => 'Berhasil',
                    'text' => 'Data navigasi berhasil disimpan.',
                ]);
                return $this->redirect(['index']);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', ['model' => $model]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                Yii::$app->session->setFlash('swal', [
                    'icon' => 'success',
                    'title' => 'Berhasil',
                    'text' => 'Data navigasi berhasil diupdate.',
                ]);
                return $this->redirect(['index']);
            }
        }

        return $this->render('update', ['model' => $model]);
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('swal', [
            'icon' => 'success',
            'title' => 'Berhasil',
            'text' => 'Data navigasi berhasil dihapus',
        ]);
        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = Modul::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
