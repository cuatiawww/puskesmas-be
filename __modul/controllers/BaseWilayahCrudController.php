<?php

namespace app\controllers;

use app\models\MasterWilayah;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

abstract class BaseWilayahCrudController extends BaseController
{
    abstract protected function wilayahLevel(): int;

    abstract protected function pageTitle(): string;

    abstract protected function activeMenu(): string;

    protected function parentLevel(): ?int
    {
        return null;
    }

    protected function parentLabel(): string
    {
        return 'Parent Wilayah';
    }

    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => $this->buildScopedQuery(),
            'pagination' => ['pageSize' => 20],
            'sort' => [
                'defaultOrder' => ['nama_wilayah' => SORT_ASC],
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

        $model = new MasterWilayah();
        $model->level_wilayah = $this->wilayahLevel();
        $model->status_aktif = 1;

        if ($model->load(Yii::$app->request->post())) {
            $this->prepareModelBeforeSave($model);
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
            $this->prepareModelBeforeSave($model);
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
            Yii::error('Gagal menghapus data wilayah: ' . $e->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('swal', [
                'icon' => 'error',
                'title' => 'Gagal',
                'text' => 'Data tidak dapat dihapus. Kemungkinan masih dipakai oleh data lain.',
            ]);
        }

        return $this->redirect(['index']);
    }

    protected function canCrud(): bool
    {
        return $this->isCurrentUserSuperAdmin();
    }

    protected function ensureCrudAllowed(): void
    {
        if (!$this->canCrud()) {
            throw new ForbiddenHttpException('Hanya super admin yang dapat mengubah master wilayah.');
        }
    }

    protected function buildScopedQuery()
    {
        $query = MasterWilayah::find()
            ->alias('w')
            ->where(['w.level_wilayah' => $this->wilayahLevel()]);

        $scope = $this->currentUserWilayahScope();
        $level = $this->wilayahLevel();

        if (($scope['mode'] ?? 'all') === 'all') {
            return $query;
        }

        if ($level === 2 && !empty($scope['prov_tbl_id'])) {
            $query->andWhere(['w.id' => (int) $scope['prov_tbl_id']]);
            return $query;
        }

        if ($level === 3) {
            if (($scope['mode'] ?? null) === 'provinsi' && !empty($scope['prov_tbl_id'])) {
                $query->andWhere(['w.parent_master_wilayah_id' => (int) $scope['prov_tbl_id']]);
            } elseif (($scope['mode'] ?? null) === 'kabupaten' && !empty($scope['kab_tbl_id'])) {
                $query->andWhere(['w.id' => (int) $scope['kab_tbl_id']]);
            }

            return $query;
        }

        if ($level === 4) {
            if (($scope['mode'] ?? null) === 'provinsi' && !empty($scope['prov_tbl_id'])) {
                $kabupatenIds = MasterWilayah::find()
                    ->select(['id'])
                    ->where([
                        'level_wilayah' => 3,
                        'parent_master_wilayah_id' => (int) $scope['prov_tbl_id'],
                    ]);
                $query->andWhere(['w.parent_master_wilayah_id' => $kabupatenIds]);
            } elseif (($scope['mode'] ?? null) === 'kabupaten' && !empty($scope['kab_tbl_id'])) {
                $query->andWhere(['w.parent_master_wilayah_id' => (int) $scope['kab_tbl_id']]);
            }
        }

        if ($level === 5) {
            if (($scope['mode'] ?? null) === 'provinsi' && !empty($scope['prov_tbl_id'])) {
                $kabupatenIds = MasterWilayah::find()
                    ->select(['id'])
                    ->where([
                        'level_wilayah' => 3,
                        'parent_master_wilayah_id' => (int) $scope['prov_tbl_id'],
                    ]);
                $kecamatanIds = MasterWilayah::find()
                    ->select(['id'])
                    ->where([
                        'level_wilayah' => 4,
                        'parent_master_wilayah_id' => $kabupatenIds,
                    ]);
                $query->andWhere(['w.parent_master_wilayah_id' => $kecamatanIds]);
            } elseif (($scope['mode'] ?? null) === 'kabupaten' && !empty($scope['kab_tbl_id'])) {
                $kecamatanIds = MasterWilayah::find()
                    ->select(['id'])
                    ->where([
                        'level_wilayah' => 4,
                        'parent_master_wilayah_id' => (int) $scope['kab_tbl_id'],
                    ]);
                $query->andWhere(['w.parent_master_wilayah_id' => $kecamatanIds]);
            }
        }

        return $query;
    }

    protected function findModel($id)
    {
        $query = $this->buildScopedQuery()->andWhere(['w.id' => (int) $id]);
        $model = $query->one();

        if (!$model instanceof MasterWilayah) {
            throw new NotFoundHttpException('Data wilayah tidak ditemukan.');
        }

        return $model;
    }

    protected function prepareModelBeforeSave(MasterWilayah $model): void
    {
        $model->level_wilayah = $this->wilayahLevel();
        $model->tbl_wilayah_id = $model->tbl_wilayah_id !== null && $model->tbl_wilayah_id !== '' ? (int) $model->tbl_wilayah_id : null;
        $model->status_aktif = (int) ($model->status_aktif ?? 1);

        if ($this->parentLevel() === null) {
            $model->parent_tbl_wilayah_id = null;
            $model->parent_master_wilayah_id = null;
            return;
        }

        $parentId = $model->parent_master_wilayah_id !== null && $model->parent_master_wilayah_id !== ''
            ? (int) $model->parent_master_wilayah_id
            : null;

        if ($parentId === null) {
            $model->addError('parent_master_wilayah_id', $this->parentLabel() . ' wajib dipilih.');
            return;
        }

        $parent = MasterWilayah::findOne([
            'id' => $parentId,
            'level_wilayah' => $this->parentLevel(),
        ]);

        if (!$parent) {
            $model->addError('parent_master_wilayah_id', $this->parentLabel() . ' tidak valid.');
            return;
        }

        $model->parent_master_wilayah_id = (int) $parent->id;
        $model->parent_tbl_wilayah_id = $parent->tbl_wilayah_id !== null ? (int) $parent->tbl_wilayah_id : null;
    }

    protected function getParentOptions(): array
    {
        $level = $this->parentLevel();
        if ($level === null) {
            return [];
        }

        $rows = MasterWilayah::find()
            ->select(['id', 'nama_wilayah'])
            ->where(['level_wilayah' => $level])
            ->orderBy(['nama_wilayah' => SORT_ASC])
            ->asArray()
            ->all();

        $options = [];
        foreach ($rows as $row) {
            $options[(int) $row['id']] = $row['nama_wilayah'];
        }

        return $options;
    }

    protected function buildScopeSummary(): string
    {
        $scope = $this->currentUserWilayahScope();

        if (($scope['mode'] ?? 'all') === 'all') {
            return 'Super admin dapat melihat seluruh data dan melakukan CRUD.';
        }

        if (($scope['mode'] ?? null) === 'provinsi' && !empty($scope['prov_tbl_id'])) {
            $provinsi = MasterWilayah::find()
                ->select(['nama_wilayah'])
                ->where(['level_wilayah' => 2, 'tbl_wilayah_id' => (int) $scope['prov_tbl_id']])
                ->scalar();

            return 'Data dibatasi untuk provinsi ' . ($provinsi ?: '-');
        }

        if (($scope['mode'] ?? null) === 'kabupaten' && !empty($scope['kab_tbl_id'])) {
            $kabupaten = MasterWilayah::find()
                ->select(['nama_wilayah'])
                ->where(['level_wilayah' => 3, 'tbl_wilayah_id' => (int) $scope['kab_tbl_id']])
                ->scalar();

            return 'Data dibatasi untuk kabupaten/kota ' . ($kabupaten ?: '-');
        }

        return 'Data dibatasi sesuai hak akses wilayah pengguna.';
    }

    protected function viewBasePath(): string
    {
        return '@app/views/' . $this->routeBase();
    }

    protected function routeBase(): string
    {
        return $this->id;
    }
}
