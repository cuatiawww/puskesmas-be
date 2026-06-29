<?php

namespace app\controllers;

use Yii;
use app\components\Helper;
use app\models\DataDukung;
use app\models\DataDukungSearch;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\web\HttpException;

class DataDukungController extends BaseController
{
    public $enableCsrfValidation = false;

    protected function getFallbackSample(): array
    {
        return [
            [
                'id' => 1,
                'id_dukung' => 'DOK-001',
                'id_jemaah' => 'JMH-001',
                'nama_jemaah' => 'Ahmad Fauzi',
                'jenis_dokumen' => 'paspor',
                'nama_dokumen' => 'Paspor Indonesia',
                'status_verifikasi' => 'menunggu',
            ],
            [
                'id' => 2,
                'id_dukung' => 'DOK-002',
                'id_jemaah' => 'JMH-002',
                'nama_jemaah' => 'Siti Aminah',
                'jenis_dokumen' => 'vaksin',
                'nama_dokumen' => 'Sertifikat Vaksin',
                'status_verifikasi' => 'terverifikasi',
            ],
        ];
    }

    /* =============================
     * INDEX
     * ============================= */
    public function actionIndex()
    {
        $searchModel = new DataDukungSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /* =============================
     * GET LIST (JSON)
     * ============================= */
    public function actionGetList()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $rows = DataDukung::find()->orderBy(['id' => SORT_DESC])->all();
            $result = [];

            foreach ($rows as $item) {
                $result[] = [
                    'id' => $item->id,
                    'id_dukung' => $item->id_dukung,
                    'id_jemaah' => $item->id_jemaah,
                    'nama_jemaah' => $item->nama_jemaah,
                    'jenis_dokumen' => $item->jenis_dokumen,
                    'nama_dokumen' => $item->nama_dokumen,
                    'status_verifikasi' => $item->status_verifikasi,
                ];
            }

            return ['success' => true, 'data' => $result];
        } catch (\Throwable $e) {
            Yii::error('DataDukung::actionGetList ' . $e->getMessage(), __METHOD__);
            return [
                'success' => true,
                'fallback' => true,
                'message' => 'Menampilkan data contoh karena koneksi database bermasalah.',
                'data' => $this->getFallbackSample()
            ];
        }
    }

    /* =============================
     * VIEW
     * ============================= */
    public function actionView($id)
    {
        if (Yii::$app->request->isGet && !Yii::$app->request->isAjax) {
            $model = DataDukung::findOne($id);
            if (!$model) throw new NotFoundHttpException('Data tidak ditemukan');
            return $this->render('view', ['model' => $model]);
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = DataDukung::findOne($id);
        if (!$model) return ['success' => false, 'message' => 'Data tidak ditemukan'];

        return ['success' => true, 'data' => $model->attributes];
    }

    /* =============================
     * CREATE
     * ============================= */
    public function actionCreate()
    {
        $model = new DataDukung();

        if ($model->load(Yii::$app->request->post())) {

            // Auto generate ID
            $model->id_dukung ??= 'DOK-' . date('YmdHis');
            $model->id_jemaah ??= 'JMH-' . date('YmdHis');
            $model->status_verifikasi ??= 'menunggu';

            // Upload file
            $file = UploadedFile::getInstance($model, 'file_path');
            if ($file) {
                $dir = Yii::getAlias('@app/../uploads/data-dukung/');
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                $name = time() . '-' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->name);
                $file->saveAs($dir . $name);
                $model->file_path = 'uploads/data-dukung/' . $name;
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('swal', [
                    'icon' => 'success',
                    'title' => 'Berhasil',
                    'text' => 'Data dukung berhasil disimpan',
                ]);
                return $this->redirect(['index']);
            }

            Yii::$app->session->setFlash('swal', [
                'icon' => 'error',
                'title' => 'Gagal',
                'text' => 'Data gagal disimpan',
            ]);
        }

        return $this->render('create', ['model' => $model]);
    }

    /* =============================
     * UPDATE
     * ============================= */
    public function actionUpdate($id)
    {
        $model = DataDukung::findOne($id);
        if (!$model) throw new NotFoundHttpException('Data tidak ditemukan');

        if ($model->load(Yii::$app->request->post())) {

            $file = UploadedFile::getInstance($model, 'file_path');
            if ($file) {
                $dir = Yii::getAlias('@app/../uploads/data-dukung/');
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                $name = time() . '-' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->name);
                $file->saveAs($dir . $name);
                $model->file_path = 'uploads/data-dukung/' . $name;
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('swal', [
                    'icon' => 'success',
                    'title' => 'Berhasil',
                    'text' => 'Data dukung berhasil diupdate',
                ]);
                return $this->redirect(['index']);
            }

            Yii::$app->session->setFlash('swal', [
                'icon' => 'error',
                'title' => 'Gagal',
                'text' => 'Data gagal diupdate',
            ]);
        }

        return $this->render('update', ['model' => $model]);
    }

    /* =============================
     * DELETE
     * ============================= */
    public function actionDelete($id = null)
    {
        if ($id === null) $id = Yii::$app->request->post('id');
        $model = DataDukung::findOne($id);
        if (!$model) throw new HttpException(404, 'Data tidak ditemukan');

        try {
            $model->delete();
            Yii::$app->session->setFlash('swal', [
                'icon' => 'success',
                'title' => 'Berhasil',
                'text' => 'Data berhasil dihapus',
            ]);
        } catch (\Throwable $e) {
            Yii::$app->session->setFlash('swal', [
                'icon' => 'error',
                'title' => 'Gagal',
                'text' => 'Data gagal dihapus',
            ]);
        }

        return $this->redirect(['index']);
    }

    public function actionPing()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['success' => true, 'message' => 'pong'];
    }
}
