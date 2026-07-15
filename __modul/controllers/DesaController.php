<?php

namespace app\controllers;

use app\models\WilayahDesa;
use app\models\WilayahKecamatan;
use app\services\WilayahService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class DesaController extends BaseWilayahCrudController
{
    protected function wilayahLevel(): int
    {
        return 5;
    }

    protected function pageTitle(): string
    {
        return 'Master Data Desa/Kelurahan';
    }

    protected function activeMenu(): string
    {
        return 'desa';
    }

    protected function parentLevel(): ?int
    {
        return 4;
    }

    protected function parentLabel(): string
    {
        return 'Kecamatan';
    }

    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => $this->buildScopedQuery(),
            'pagination' => ['pageSize' => 20],
            'sort' => [
                'defaultOrder' => ['name' => SORT_ASC],
            ],
        ]);

        return $this->render($this->viewBasePath() . '/index', [
            'dataProvider' => $dataProvider,
            'scopeSummary' => $this->buildScopeSummary(),
            'canCrud' => $this->canCrud(),
            'pageTitle' => $this->pageTitle(),
            'activeMenu' => $this->activeMenu(),
            'parentLabel' => $this->parentLabel(),
        ]);
    }

    public function actionView($id)
    {
        return $this->render($this->viewBasePath() . '/view', [
            'model' => $this->findModel($id),
            'pageTitle' => $this->pageTitle(),
            'activeMenu' => $this->activeMenu(),
            'parentLabel' => $this->parentLabel(),
        ]);
    }

    public function actionCreate()
    {
        $this->ensureCrudAllowed();

        $model = new WilayahDesa();

        if ($model->load(Yii::$app->request->post())) {
            $kec = WilayahKecamatan::findOne(['code' => $model->parent_code]);
            if ($kec) {
                $model->province_code = substr($kec->code, 0, 2);
                $model->district_code = substr($kec->code, 0, 4);
            }
            if ($model->save()) {
                Yii::$app->session->setFlash('swal', [
                    'icon' => 'success',
                    'title' => 'Berhasil',
                    'text' => $this->pageTitle() . ' berhasil ditambahkan.',
                ]);
                return $this->redirect(['index']);
            }
        }

        return $this->render($this->viewBasePath() . '/create', [
            'model' => $model,
            'pageTitle' => $this->pageTitle(),
            'activeMenu' => $this->activeMenu(),
            'parentOptions' => $this->getParentOptions(),
            'parentLabel' => $this->parentLabel(),
        ]);
    }

    public function actionUpdate($id)
    {
        $this->ensureCrudAllowed();

        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            $kec = WilayahKecamatan::findOne(['code' => $model->parent_code]);
            if ($kec) {
                $model->province_code = substr($kec->code, 0, 2);
                $model->district_code = substr($kec->code, 0, 4);
            }
            if ($model->save()) {
                Yii::$app->session->setFlash('swal', [
                    'icon' => 'success',
                    'title' => 'Berhasil',
                    'text' => $this->pageTitle() . ' berhasil diperbarui.',
                ]);
                return $this->redirect(['index']);
            }
        }

        return $this->render($this->viewBasePath() . '/update', [
            'model' => $model,
            'pageTitle' => $this->pageTitle(),
            'activeMenu' => $this->activeMenu(),
            'parentOptions' => $this->getParentOptions(),
            'parentLabel' => $this->parentLabel(),
        ]);
    }

    public function actionDelete($id)
    {
        $this->ensureCrudAllowed();

        $model = $this->findModel($id);

        try {
            $model->delete();
            Yii::$app->session->setFlash('swal', [
                'icon' => 'success',
                'title' => 'Berhasil',
                'text' => $this->pageTitle() . ' berhasil dihapus.',
            ]);
        } catch (\Throwable $e) {
            Yii::error('Gagal menghapus data desa: ' . $e->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('swal', [
                'icon' => 'error',
                'title' => 'Gagal',
                'text' => 'Data tidak dapat dihapus. Kemungkinan masih dipakai oleh data lain.',
            ]);
        }

        return $this->redirect(['index']);
    }

    protected function buildScopedQuery()
    {
        $query = WilayahDesa::find()->alias('w');

        $scope = $this->currentUserWilayahScope();
        $codeScope = $this->currentUserWilayahCodeScope();

        if (($scope['mode'] ?? 'all') === 'all') {
            return $query;
        }

        if ($scope['mode'] === 'provinsi' && !empty($codeScope['prov_code'])) {
            $query->andWhere(['like', 'w.code', $codeScope['prov_code'] . '%', false]);
        } elseif ($scope['mode'] === 'kabupaten' && !empty($codeScope['kab_code'])) {
            $query->andWhere(['like', 'w.code', $codeScope['kab_code'] . '%', false]);
        }

        return $query;
    }

    protected function findModel($id)
    {
        $query = $this->buildScopedQuery()->andWhere(['w.id' => (int) $id]);
        $model = $query->one();

        if (!$model instanceof WilayahDesa) {
            throw new NotFoundHttpException('Data desa/kelurahan tidak ditemukan.');
        }

        return $model;
    }

    protected function getParentOptions(): array
    {
        $query = WilayahKecamatan::find()->orderBy(['name' => SORT_ASC]);

        $scope = $this->currentUserWilayahScope();
        $codeScope = $this->currentUserWilayahCodeScope();

        if ($scope['mode'] === 'provinsi' && !empty($codeScope['prov_code'])) {
            $query->andWhere(['like', 'code', $codeScope['prov_code'] . '%', false]);
        } elseif ($scope['mode'] === 'kabupaten' && !empty($codeScope['kab_code'])) {
            $query->andWhere(['like', 'code', $codeScope['kab_code'] . '%', false]);
        }

        $rows = $query->asArray()->all();

        $options = [];
        foreach ($rows as $row) {
            $options[$row['code']] = $row['name'] . ' (' . $row['code'] . ')';
        }

        return $options;
    }

    protected function buildScopeSummary(): string
    {
        $scope = $this->currentUserWilayahScope();
        $codeScope = $this->currentUserWilayahCodeScope();

        if (($scope['mode'] ?? 'all') === 'all') {
            return 'Super admin dapat melihat seluruh data dan melakukan CRUD.';
        }

        if ($scope['mode'] === 'provinsi' && !empty($codeScope['prov_code'])) {
            $provName = (new WilayahService())->findProvinsiName($codeScope['prov_code']);
            return 'Data dibatasi untuk provinsi ' . ($provName ?: '-');
        }

        if ($scope['mode'] === 'kabupaten' && !empty($codeScope['kab_code'])) {
            $kabName = (new WilayahService())->findKabupatenName($codeScope['prov_code'], $codeScope['kab_code']);
            return 'Data dibatasi untuk kabupaten/kota ' . ($kabName ?: '-');
        }

        return 'Data dibatasi sesuai hak akses wilayah pengguna.';
    }
}
