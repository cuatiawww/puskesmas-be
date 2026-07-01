<?php

namespace app\controllers;

use Yii;
use app\models\MasterObat;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class MasterObatController extends BaseController
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
        $query = MasterObat::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_ASC],
            ]
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreate()
    {
        $model = new MasterObat();

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                Yii::$app->session->setFlash('swal', [
                    'icon' => 'success',
                    'title' => 'Berhasil',
                    'text' => 'Master obat berhasil disimpan.',
                ]);
                return $this->redirect(['index']);
            }
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
                    'text' => 'Master obat berhasil diupdate.',
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
            'text' => 'Master obat berhasil dihapus.',
        ]);
        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = MasterObat::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('Data tidak ditemukan.');
    }
}
