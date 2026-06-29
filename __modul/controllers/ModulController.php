<?php

namespace app\controllers;

use Yii;
use app\models\SubModul;
use app\models\SubModulSearch;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;

class ModulController extends BaseController
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
        $searchModel = new SubModulSearch();
        $params = Yii::$app->request->queryParams;

        // Override search to only get parent_id = NULL (moduls, not sub-moduls)
        $query = SubModul::find()->where(['parent_id' => null]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['urutan' => SORT_ASC]
            ],
        ]);

        // Apply search filters if any
        if ($searchModel->load($params) && $searchModel->validate()) {
            $query->andFilterWhere(['like', 'nama_sub_modul', $searchModel->nama_sub_modul])
                  ->andFilterWhere(['like', 'label', $searchModel->label])
                  ->andFilterWhere(['modul_id' => $searchModel->modul_id])
                  ->andFilterWhere(['is_active' => $searchModel->is_active]);
        }

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
        $model->scenario = SubModul::SCENARIO_MODUL;
        $model->parent_id = null; // Ensure it's a modul, not sub-modul

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                $model->parent_id = null; // Force parent_id to NULL for moduls
                if ($model->save()) {
                    Yii::$app->session->setFlash('swal', [
                        'icon' => 'success',
                        'title' => 'Berhasil',
                        'text' => 'Data modul berhasil disimpan.',
                    ]);
                    return $this->redirect(['index']);
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', ['model' => $model]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = SubModul::SCENARIO_MODUL;

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                $model->parent_id = null; // Force parent_id to NULL for moduls
                if ($model->save()) {
                    Yii::$app->session->setFlash('swal', [
                        'icon' => 'success',
                        'title' => 'Berhasil',
                        'text' => 'Data modul berhasil diupdate.',
                    ]);
                    return $this->redirect(['index']);
                }
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
            'text' => 'Data modul berhasil dihapus',
        ]);
        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = SubModul::findOne(['id' => $id, 'parent_id' => null])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}

