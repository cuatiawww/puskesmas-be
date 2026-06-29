<?php

namespace app\controllers;

use app\services\LocalFaskesMasterService;
use app\services\WilayahService;
use Yii;
use yii\data\Pagination;

abstract class BaseFaskesMasterController extends BaseController
{
    abstract protected function facilityConfig(): array;

    protected function faskesMaster(): LocalFaskesMasterService
    {
        return new LocalFaskesMasterService();
    }

    public function actionIndex()
    {
        $config = $this->facilityConfig();
        $request = Yii::$app->request;

        $uiPage = max(0, (int) $request->get('page', 0));
        $page = $uiPage + 1;
        $perPage = max(1, (int) $request->get('per-page', 10));
        $search = trim((string)$request->get('search', ''));
        $kodeProvinsi = trim((string)$request->get('kode_provinsi', ''));
        $kodeKabkota = trim((string)$request->get('kode_kabkota', ''));

        // Apply logged-in user regional scope
        $scope = $this->currentUserWilayahScope();
        $codeScope = $this->currentUserWilayahCodeScope();
        if ($scope['mode'] === 'provinsi') {
            $kodeProvinsi = (string)$codeScope['prov_code'];
        } elseif ($scope['mode'] === 'kabupaten') {
            $kodeProvinsi = (string)$codeScope['prov_code'];
            $kodeKabkota = (string)$codeScope['kab_code'];
        }

        $rows = [];
        $errorMessage = null;

        $wilayahService = new WilayahService();
        $provinsiOptions = $wilayahService->getProvinsiOptions();
        if ($scope['mode'] === 'provinsi' || $scope['mode'] === 'kabupaten') {
            $provinsiOptions = array_filter($provinsiOptions, static function ($opt) use ($codeScope) {
                return (string)$opt['code'] === (string)$codeScope['prov_code'];
            });
        }

        $kabupatenOptions = [];
        if ($kodeProvinsi !== '') {
            $kabupatenOptions = $wilayahService->getKabupatenOptions($kodeProvinsi);
            if ($scope['mode'] === 'kabupaten') {
                $kabupatenOptions = array_filter($kabupatenOptions, static function ($opt) use ($codeScope) {
                    return (string)$opt['code'] === (string)$codeScope['kab_code'];
                });
            }
        }

        $dataDebug = [];
        $paginationMeta = [
            'total' => 0,
            'page' => $page,
            'per_page' => $perPage,
        ];

        try {
            $service = $this->faskesMaster();
            $result = $service->getFacilityTable([
                'jenis' => (string) ($config['jenis'] ?? 'rumah_sakit'),
                'page' => $page,
                'per_page' => $perPage,
                'search' => $search,
                'kode_provinsi' => $kodeProvinsi,
                'kode_kabkota' => $kodeKabkota,
            ]);

            $rows = $result['rows'];
            $paginationMeta = $result['pagination'];
            $dataDebug = $result['debug'] ?? [];
        } catch (\Throwable $e) {
            Yii::error('Faskes master index error: ' . $e->getMessage(), __METHOD__);
            $errorMessage = $e->getMessage();
        }

        $pagination = new Pagination([
            'totalCount' => (int) ($paginationMeta['total'] ?? count($rows)),
            'pageSize' => (int) ($paginationMeta['per_page'] ?? $perPage),
            'page' => max(0, ((int) ($paginationMeta['page'] ?? $page)) - 1),
            'pageParam' => 'page',
            'pageSizeParam' => 'per-page',
            'forcePageParam' => false,
        ]);

        $scopeSummary = 'Data ditampilkan dari master faskes lokal pada database aplikasi.';
        if ($scope['mode'] === 'provinsi') {
            $provName = (new WilayahService())->findProvinsiName($codeScope['prov_code']);
            $scopeSummary = 'Data dibatasi untuk provinsi ' . ($provName ?: '-');
        } elseif ($scope['mode'] === 'kabupaten') {
            $kabName = (new WilayahService())->findKabupatenName($codeScope['kab_code']);
            $scopeSummary = 'Data dibatasi untuk kabupaten/kota ' . ($kabName ?: '-');
        } elseif ($scope['mode'] === 'all') {
            $scopeSummary = 'Super admin dapat melihat seluruh data dan melakukan CRUD.';
        }

        return $this->render('@app/views/faskes-master/index', [
            'pageTitle' => (string) ($config['label'] ?? 'Master Data Faskes'),
            'activeMenu' => (string) ($config['active_menu'] ?? 'faskes-master'),
            'jenisLabel' => (string) ($config['jenis_label'] ?? $config['label'] ?? 'Faskes'),
            'rows' => $rows,
            'pagination' => $pagination,
            'filters' => [
                'kode_provinsi' => $kodeProvinsi,
                'kode_kabkota' => $kodeKabkota,
                'search' => $search,
                'per_page' => $perPage,
            ],
            'provinsiOptions' => $provinsiOptions,
            'kabupatenOptions' => $kabupatenOptions,
            'errorMessage' => $errorMessage,
            'dataDebug' => $dataDebug,
            'scope' => $scope,
            'scopeSummary' => $scopeSummary,
        ]);
    }

    private const TABLES_MAP = [
        'rs' => 'tbl_rs_sarana',
        'puskesmas' => 'tbl_puskesmas_sarana',
        'klinik' => 'tbl_klinik_sarana',
        'posyandu' => 'tbl_posyandu_sarana',
        'pustu' => 'tbl_pustu_sarana',
    ];

    public function actionView($kode_satusehat = null, $snapshot = null)
    {
        $config = $this->facilityConfig();
        $row = [];

        if (is_string($snapshot) && $snapshot !== '') {
            $decoded = json_decode(base64_decode($snapshot, true) ?: '', true);
            if (is_array($decoded)) {
                $row = $decoded;
            }
        }

        return $this->render('@app/views/faskes-master/view', [
            'pageTitle' => (string) ($config['label'] ?? 'Master Data Faskes'),
            'activeMenu' => (string) ($config['active_menu'] ?? 'faskes-master'),
            'jenisLabel' => (string) ($config['jenis_label'] ?? $config['label'] ?? 'Faskes'),
            'kodeSatusehat' => $kode_satusehat,
            'row' => $row,
        ]);
    }

    public function actionCreate()
    {
        $config = $this->facilityConfig();
        $jenis = (string) ($config['jenis'] ?? 'rs');
        $table = self::TABLES_MAP[$jenis] ?? null;

        if ($table === null) {
            throw new \yii\web\NotFoundHttpException('Tabel faskes tidak ditemukan.');
        }

        $model = new \app\models\FaskesForm();
        
        $scope = $this->currentUserWilayahScope();
        $codeScope = $this->currentUserWilayahCodeScope();
        if ($scope['mode'] === 'provinsi') {
            $model->kode_prop = (string)$codeScope['prov_code'];
            $model->kode_provinsi = (string)$codeScope['prov_code'];
        } elseif ($scope['mode'] === 'kabupaten') {
            $model->kode_prop = (string)$codeScope['prov_code'];
            $model->kode_provinsi = (string)$codeScope['prov_code'];
            $model->kode_kab = (string)$codeScope['kab_code'];
            $model->kode_kabkota = (string)$codeScope['kab_code'];
        }

        if ($model->load(Yii::$app->request->post())) {
            if ($scope['mode'] === 'provinsi') {
                $model->kode_prop = (string)$codeScope['prov_code'];
                $model->kode_provinsi = (string)$codeScope['prov_code'];
            } elseif ($scope['mode'] === 'kabupaten') {
                $model->kode_prop = (string)$codeScope['prov_code'];
                $model->kode_provinsi = (string)$codeScope['prov_code'];
                $model->kode_kab = (string)$codeScope['kab_code'];
                $model->kode_kabkota = (string)$codeScope['kab_code'];
            }

            if ($model->save($table, $jenis)) {
                Yii::$app->session->setFlash('swal', [
                    'icon' => 'success',
                    'title' => 'Berhasil',
                    'text' => 'Data faskes berhasil ditambahkan.',
                ]);
                return $this->redirect(['index']);
            }
        }

        $wilayahService = new WilayahService();
        $provinsiOptions = $wilayahService->getProvinsiOptions();
        if ($scope['mode'] === 'provinsi' || $scope['mode'] === 'kabupaten') {
            $provinsiOptions = array_filter($provinsiOptions, static function ($opt) use ($codeScope) {
                return (string)$opt['code'] === (string)$codeScope['prov_code'];
            });
        }

        $provCodeSelected = in_array($jenis, ['rs', 'puskesmas', 'klinik'], true) ? $model->kode_prop : $model->kode_provinsi;
        $kabCodeSelected = in_array($jenis, ['rs', 'puskesmas', 'klinik'], true) ? $model->kode_kab : $model->kode_kabkota;

        $kabupatenOptions = [];
        if (!empty($provCodeSelected)) {
            $kabupatenOptions = $wilayahService->getKabupatenOptions($provCodeSelected);
            if ($scope['mode'] === 'kabupaten') {
                $kabupatenOptions = array_filter($kabupatenOptions, static function ($opt) use ($codeScope) {
                    return (string)$opt['code'] === (string)$codeScope['kab_code'];
                });
            }
        }

        $kecamatanOptions = [];
        if (!empty($kabCodeSelected)) {
            $kecamatanOptions = $wilayahService->getKecamatanOptions($kabCodeSelected);
        }

        $desaOptions = [];
        if (!empty($model->kode_kecamatan) && !in_array($jenis, ['rs', 'puskesmas', 'klinik'], true)) {
            $desaOptions = $wilayahService->getDesaOptions($model->kode_kecamatan);
        }

        return $this->render('@app/views/faskes-master/create', [
            'pageTitle' => (string) ($config['label'] ?? 'Tambah Data Faskes'),
            'activeMenu' => (string) ($config['active_menu'] ?? 'faskes-master'),
            'jenisLabel' => (string) ($config['jenis_label'] ?? $config['label'] ?? 'Faskes'),
            'jenis' => $jenis,
            'model' => $model,
            'scope' => $scope,
            'provinsiOptions' => $provinsiOptions,
            'kabupatenOptions' => $kabupatenOptions,
            'kecamatanOptions' => $kecamatanOptions,
            'desaOptions' => $desaOptions,
        ]);
    }

    public function actionUpdate($id)
    {
        $config = $this->facilityConfig();
        $jenis = (string) ($config['jenis'] ?? 'rs');
        $table = self::TABLES_MAP[$jenis] ?? null;

        if ($table === null) {
            throw new \yii\web\NotFoundHttpException('Tabel faskes tidak ditemukan.');
        }

        $row = (new \yii\db\Query())
            ->from($table)
            ->where(['id' => (int)$id])
            ->one();

        if (!$row) {
            throw new \yii\web\NotFoundHttpException('Data faskes tidak ditemukan.');
        }

        $model = new \app\models\FaskesForm();
        $model->loadFromRow($row, $jenis);

        $scope = $this->currentUserWilayahScope();
        $codeScope = $this->currentUserWilayahCodeScope();
        if ($scope['mode'] === 'provinsi') {
            $faskesProv = in_array($jenis, ['rs', 'puskesmas', 'klinik'], true) ? $model->kode_prop : $model->kode_provinsi;
            if ((string)$faskesProv !== (string)$codeScope['prov_code']) {
                throw new \yii\web\ForbiddenHttpException('Anda tidak memiliki wewenang untuk mengubah data faskes di luar wilayah Anda.');
            }
        } elseif ($scope['mode'] === 'kabupaten') {
            $faskesKab = in_array($jenis, ['rs', 'puskesmas', 'klinik'], true) ? $model->kode_kab : $model->kode_kabkota;
            if ((string)$faskesKab !== (string)$codeScope['kab_code']) {
                throw new \yii\web\ForbiddenHttpException('Anda tidak memiliki wewenang untuk mengubah data faskes di luar wilayah Anda.');
            }
        }

        if ($model->load(Yii::$app->request->post())) {
            if ($scope['mode'] === 'provinsi') {
                $model->kode_prop = (string)$codeScope['prov_code'];
                $model->kode_provinsi = (string)$codeScope['prov_code'];
            } elseif ($scope['mode'] === 'kabupaten') {
                $model->kode_prop = (string)$codeScope['prov_code'];
                $model->kode_provinsi = (string)$codeScope['prov_code'];
                $model->kode_kab = (string)$codeScope['kab_code'];
                $model->kode_kabkota = (string)$codeScope['kab_code'];
            }

            if ($model->save($table, $jenis)) {
                Yii::$app->session->setFlash('swal', [
                    'icon' => 'success',
                    'title' => 'Berhasil',
                    'text' => 'Data faskes berhasil diperbarui.',
                ]);
                return $this->redirect(['index']);
            }
        }

        $wilayahService = new WilayahService();
        $provinsiOptions = $wilayahService->getProvinsiOptions();
        if ($scope['mode'] === 'provinsi' || $scope['mode'] === 'kabupaten') {
            $provinsiOptions = array_filter($provinsiOptions, static function ($opt) use ($codeScope) {
                return (string)$opt['code'] === (string)$codeScope['prov_code'];
            });
        }

        $provCodeSelected = in_array($jenis, ['rs', 'puskesmas', 'klinik'], true) ? $model->kode_prop : $model->kode_provinsi;
        $kabCodeSelected = in_array($jenis, ['rs', 'puskesmas', 'klinik'], true) ? $model->kode_kab : $model->kode_kabkota;

        $kabupatenOptions = [];
        if (!empty($provCodeSelected)) {
            $kabupatenOptions = $wilayahService->getKabupatenOptions($provCodeSelected);
            if ($scope['mode'] === 'kabupaten') {
                $kabupatenOptions = array_filter($kabupatenOptions, static function ($opt) use ($codeScope) {
                    return (string)$opt['code'] === (string)$codeScope['kab_code'];
                });
            }
        }

        $kecamatanOptions = [];
        if (!empty($kabCodeSelected)) {
            $kecamatanOptions = $wilayahService->getKecamatanOptions($kabCodeSelected);
        }

        $desaOptions = [];
        if (!empty($model->kode_kecamatan) && !in_array($jenis, ['rs', 'puskesmas', 'klinik'], true)) {
            $desaOptions = $wilayahService->getDesaOptions($model->kode_kecamatan);
        }

        return $this->render('@app/views/faskes-master/update', [
            'pageTitle' => (string) ($config['label'] ?? 'Edit Data Faskes'),
            'activeMenu' => (string) ($config['active_menu'] ?? 'faskes-master'),
            'jenisLabel' => (string) ($config['jenis_label'] ?? $config['label'] ?? 'Faskes'),
            'jenis' => $jenis,
            'model' => $model,
            'scope' => $scope,
            'provinsiOptions' => $provinsiOptions,
            'kabupatenOptions' => $kabupatenOptions,
            'kecamatanOptions' => $kecamatanOptions,
            'desaOptions' => $desaOptions,
        ]);
    }

    public function actionDelete($id)
    {
        $config = $this->facilityConfig();
        $jenis = (string) ($config['jenis'] ?? 'rs');
        $table = self::TABLES_MAP[$jenis] ?? null;

        if ($table === null) {
            throw new \yii\web\NotFoundHttpException('Tabel faskes tidak ditemukan.');
        }

        $row = (new \yii\db\Query())
            ->from($table)
            ->where(['id' => (int)$id])
            ->one();

        if (!$row) {
            throw new \yii\web\NotFoundHttpException('Data faskes tidak ditemukan.');
        }

        $scope = $this->currentUserWilayahScope();
        $codeScope = $this->currentUserWilayahCodeScope();
        if ($scope['mode'] === 'provinsi') {
            $faskesProv = in_array($jenis, ['rs', 'puskesmas', 'klinik'], true) ? ($row['kode_prop'] ?? '') : ($row['kode_provinsi'] ?? '');
            if ((string)$faskesProv !== (string)$codeScope['prov_code']) {
                throw new \yii\web\ForbiddenHttpException('Anda tidak memiliki wewenang untuk menghapus data faskes di luar wilayah Anda.');
            }
        } elseif ($scope['mode'] === 'kabupaten') {
            $faskesKab = in_array($jenis, ['rs', 'puskesmas', 'klinik'], true) ? ($row['kode_kab'] ?? '') : ($row['kode_kabkota'] ?? '');
            if ((string)$faskesKab !== (string)$codeScope['kab_code']) {
                throw new \yii\web\ForbiddenHttpException('Anda tidak memiliki wewenang untuk menghapus data faskes di luar wilayah Anda.');
            }
        }

        try {
            Yii::$app->db->createCommand()->delete($table, ['id' => (int)$id])->execute();
            Yii::$app->session->setFlash('swal', [
                'icon' => 'success',
                'title' => 'Berhasil',
                'text' => 'Data faskes berhasil dihapus.',
            ]);
        } catch (\Throwable $e) {
            Yii::error('Faskes master delete error: ' . $e->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('swal', [
                'icon' => 'error',
                'title' => 'Gagal',
                'text' => 'Gagal menghapus data faskes.',
            ]);
        }

        return $this->redirect(['index']);
    }

    public function actionGetKabupaten($kode_provinsi)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $scope = $this->currentUserWilayahScope();
        $codeScope = $this->currentUserWilayahCodeScope();
        $options = (new WilayahService())->getKabupatenOptions($kode_provinsi);
        
        if ($scope['mode'] === 'kabupaten') {
            $options = array_filter($options, static function ($opt) use ($codeScope) {
                return (string)$opt['code'] === (string)$codeScope['kab_code'];
            });
            $options = array_values($options);
        }
        
        return $options;
    }

    public function actionGetKecamatan($kode_kabkota)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return (new WilayahService())->getKecamatanOptions($kode_kabkota);
    }

    public function actionGetDesa($kode_kecamatan)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return (new WilayahService())->getDesaOptions($kode_kecamatan);
    }
}
