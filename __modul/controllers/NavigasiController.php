<?php

namespace app\controllers;

use Yii;
use app\models\Modul;
use app\models\ModulSearch;
use app\models\SubModul;
use app\models\SubModulSearch;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

class NavigasiController extends BaseController
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete-navigasi' => ['POST'],
                    'delete-modul' => ['POST'],
                    'delete-submodul' => ['POST'],
                ],
            ],
        ]);
    }

    /**
     * Unified index displaying all three tabs
     */
    public function actionIndex()
    {
        // 1. Navigasi
        $searchNavigasi = new ModulSearch();
        $providerNavigasi = $searchNavigasi->search(Yii::$app->request->queryParams);

        // 2. Modul (parent_id = null)
        $searchModul = new SubModulSearch();
        $providerModul = $searchModul->search(Yii::$app->request->queryParams);
        $providerModul->query->andWhere(['parent_id' => null]);

        // 3. Sub Modul (parent_id != null)
        $searchSubModul = new SubModulSearch();
        $providerSubModul = $searchSubModul->search(Yii::$app->request->queryParams);
        $providerSubModul->query->andWhere(['not', ['parent_id' => null]]);

        return $this->render('index', [
            'searchNavigasi' => $searchNavigasi,
            'providerNavigasi' => $providerNavigasi,
            'searchModul' => $searchModul,
            'providerModul' => $providerModul,
            'searchSubModul' => $searchSubModul,
            'providerSubModul' => $providerSubModul,
            'activeTab' => Yii::$app->request->get('tab', 'navigasi'),
        ]);
    }

    // ==========================================
    // CRUD: NAVIGASI (modul table)
    // ==========================================
    public function actionCreateNavigasi()
    {
        $model = new Modul();

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                Yii::$app->session->setFlash('swal', [
                    'icon' => 'success',
                    'title' => 'Berhasil',
                    'text' => 'Data navigasi berhasil disimpan.',
                ]);
                return $this->redirect(['index', 'tab' => 'navigasi']);
            }
        } else {
            $model->loadDefaultValues();
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('_form_navigasi', ['model' => $model]);
        }
        return $this->render('create_navigasi', ['model' => $model]);
    }

    public function actionUpdateNavigasi($id)
    {
        $model = $this->findNavigasi($id);

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                Yii::$app->session->setFlash('swal', [
                    'icon' => 'success',
                    'title' => 'Berhasil',
                    'text' => 'Data navigasi berhasil diupdate.',
                ]);
                return $this->redirect(['index', 'tab' => 'navigasi']);
            }
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('_form_navigasi', ['model' => $model]);
        }
        return $this->render('update_navigasi', ['model' => $model]);
    }

    public function actionDeleteNavigasi($id)
    {
        $this->findNavigasi($id)->delete();
        Yii::$app->session->setFlash('swal', [
            'icon' => 'success',
            'title' => 'Berhasil',
            'text' => 'Data navigasi berhasil dihapus',
        ]);
        return $this->redirect(['index', 'tab' => 'navigasi']);
    }

    public function actionAjaxCreateNavigasi()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Modul();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return [
                'success' => true,
                'id' => $model->id,
                'label' => $model->nama_modul,
            ];
        }
        return [
            'success' => false,
            'errors' => $model->errors,
        ];
    }

    // ==========================================
    // CRUD: MODUL (sub_modul table, parent_id is null)
    // ==========================================
    public function actionCreateModul()
    {
        $model = new SubModul();
        $model->scenario = SubModul::SCENARIO_MODUL;
        $model->parent_id = null;

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                $model->parent_id = null;
                if ($model->save()) {
                    Yii::$app->session->setFlash('swal', [
                        'icon' => 'success',
                        'title' => 'Berhasil',
                        'text' => 'Data modul berhasil disimpan.',
                    ]);
                    return $this->redirect(['index', 'tab' => 'modul']);
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('_form_modul', ['model' => $model]);
        }
        return $this->render('create_modul', ['model' => $model]);
    }

    public function actionUpdateModul($id)
    {
        $model = $this->findSubModul($id);
        $model->scenario = SubModul::SCENARIO_MODUL;

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                $model->parent_id = null;
                if ($model->save()) {
                    Yii::$app->session->setFlash('swal', [
                        'icon' => 'success',
                        'title' => 'Berhasil',
                        'text' => 'Data modul berhasil diupdate.',
                    ]);
                    return $this->redirect(['index', 'tab' => 'modul']);
                }
            }
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('_form_modul', ['model' => $model]);
        }
        return $this->render('update_modul', ['model' => $model]);
    }

    public function actionDeleteModul($id)
    {
        $this->findSubModul($id)->delete();
        Yii::$app->session->setFlash('swal', [
            'icon' => 'success',
            'title' => 'Berhasil',
            'text' => 'Data modul berhasil dihapus.',
        ]);
        return $this->redirect(['index', 'tab' => 'modul']);
    }

    public function actionAjaxCreateModul()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new SubModul();
        $model->scenario = SubModul::SCENARIO_MODUL;
        $model->parent_id = null;
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return [
                'success' => true,
                'id' => $model->id,
                'label' => $model->label,
            ];
        }
        return [
            'success' => false,
            'errors' => $model->errors,
        ];
    }

    // ==========================================
    // CRUD: SUB MODUL (sub_modul table, parent_id not null)
    // ==========================================
    public function actionCreateSubmodul()
    {
        $model = new SubModul();
        $model->scenario = SubModul::SCENARIO_SUB_MODUL;

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                Yii::$app->session->setFlash('swal', [
                    'icon' => 'success',
                    'title' => 'Berhasil',
                    'text' => 'Data sub modul berhasil disimpan.',
                ]);
                return $this->redirect(['index', 'tab' => 'submodul']);
            }
        } else {
            $model->loadDefaultValues();
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('_form_submodul', ['model' => $model]);
        }
        return $this->render('create_submodul', ['model' => $model]);
    }

    public function actionUpdateSubmodul($id)
    {
        $model = $this->findSubModul($id);
        $model->scenario = SubModul::SCENARIO_SUB_MODUL;

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                Yii::$app->session->setFlash('swal', [
                    'icon' => 'success',
                    'title' => 'Berhasil',
                    'text' => 'Data sub modul berhasil diupdate.',
                ]);
                return $this->redirect(['index', 'tab' => 'submodul']);
            }
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('_form_submodul', ['model' => $model]);
        }
        return $this->render('update_submodul', ['model' => $model]);
    }

    public function actionDeleteSubmodul($id)
    {
        $this->findSubModul($id)->delete();
        Yii::$app->session->setFlash('swal', [
            'icon' => 'success',
            'title' => 'Berhasil',
            'text' => 'Data sub modul berhasil dihapus',
        ]);
        return $this->redirect(['index', 'tab' => 'submodul']);
    }

    public function actionGetSubModulByModul($modul_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
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

    // ==========================================
    // FINDERS
    // ==========================================
    protected function findNavigasi($id)
    {
        if (($model = Modul::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('Data navigasi tidak ditemukan.');
    }

    protected function findSubModul($id)
    {
        if (($model = SubModul::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('Data modul/submodul tidak ditemukan.');
    }
}
