<?php

namespace app\controllers;

use Yii;
use app\models\SubModul;
use app\models\SubModulSearch;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class SubModulController extends BaseController
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
        $searchModel = new SubModulSearch();
        $dataProvider = $searchModel->search($request);
        $dataProvider->query->andWhere(['not', ['parent_id' => null]]);

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
        $model = new SubModul();
        $model->scenario = SubModul::SCENARIO_SUB_MODUL;

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                Yii::$app->session->setFlash('swal', [
                    'icon' => 'success',
                    'title' => 'Berhasil',
                    'text' => 'Data berhasil disimpan.',
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
        $model->scenario = SubModul::SCENARIO_SUB_MODUL;

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                Yii::$app->session->setFlash('swal', [
                    'icon' => 'success',
                    'title' => 'Berhasil',
                    'text' => 'Data berhasil diupdate.',
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
            'text' => 'Data berhasil dihapus',
        ]);
        return $this->redirect(['index']);
    }

    /**
     * AJAX: Get sub-modules by parent modul
     */
    public function actionGetSubModulByModul($modul_id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $subModuls = SubModul::find()
            ->select(['id', 'label'])
            ->where([
                'modul_id' => $modul_id,
                'is_active' => true,
                'parent_id' => null
            ])
            ->orderBy('urutan ASC')
            ->asArray()
            ->all();

        $data = [];
        foreach ($subModuls as $subModul) {
            $data[$subModul['id']] = $subModul['label'];
        }

        return $data;
    }

    protected function findModel($id)
    {
        if (($model = SubModul::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
