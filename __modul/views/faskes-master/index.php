<?php

use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\widgets\LinkPager;
use yii\widgets\Pjax;

$this->title = $pageTitle;
$this->params['active_menu'] = $activeMenu;

$swal = Yii::$app->session->getFlash('swal', null);
if ($swal) {
    $swalJson = Json::encode($swal);
    $js = <<<JS
(function(){
  var opt = $swalJson;
  if(!opt.toast && opt.position === undefined) {
    opt.position = (opt.icon === 'success') ? 'center' : 'top-end';
  }
  if(!opt.toast && opt.timer === undefined) opt.timer = 2500;
  if(!opt.toast && opt.showConfirmButton === undefined) opt.showConfirmButton = false;
  if (typeof Swal !== 'undefined') {
    Swal.fire(opt);
  }
})();
JS;
    $this->registerJs($js);
}

$renderValue = static function (array $row, array $keys, string $default = '-'): string {
    foreach ($keys as $key) {
        if (array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== '') {
            return (string) $row[$key];
        }
    }

    return $default;
};

$resolveProvinsi = static function (array $row) use ($renderValue): string {
    return $renderValue($row, ['nama_provinsi', 'nama_prop'], '-');
};

$resolveKabupaten = static function (array $row, string $provinsiValue) use ($renderValue): string {
    $kabupaten = $renderValue($row, ['nama_kabkota', 'nama_kabupaten', 'nama_kab'], '');
    if ($kabupaten !== '') {
        return $kabupaten;
    }

    $genericName = $renderValue($row, ['nama'], '');
    if ($genericName !== '' && $genericName !== $provinsiValue) {
        return $genericName;
    }

    return '-';
};

$resolveKecamatan = static function (array $row, string $provinsiValue, string $kabupatenValue) use ($renderValue): string {
    $kecamatan = $renderValue($row, ['nama_kecamatan', 'kecamatan'], '');
    if ($kecamatan !== '') {
        return $kecamatan;
    }

    $genericName = $renderValue($row, ['nama'], '');
    if ($genericName !== '' && $genericName !== $provinsiValue && $genericName !== $kabupatenValue) {
        return $genericName;
    }

    return '-';
};
?>

<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center justify-content-between">
            <div class="col-sm-auto">
                <div class="page-header-title">
                    <h5 class="mb-0 fw-bold"><?= Html::encode(strtoupper($pageTitle)) ?></h5>
                    <p><?= Html::encode($scopeSummary ?? 'Data ditampilkan dari master faskes lokal pada database aplikasi.') ?></p>
                </div>
            </div>
            <div class="col-sm-auto">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= Url::to(['/site/index']) ?>">
                            <i class="ph-duotone ph-house"></i>
                        </a>
                    </li>
                    <li class="breadcrumb-item"><?= Html::encode(strtoupper($jenisLabel)) ?></li>
                </ul>
            </div>
            <div style="margin-top: 10px;" class="d-flex">
                <a class="btn btn-sm btn-primary rounded-pill px-2" role="button" href="javascript:void(0);">
                    <i class="ti ti-external-link me-1"></i> Info Selengkapnya
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5>DAFTAR <?= Html::encode(strtoupper($jenisLabel)) ?></h5>
                    <p class="mb-0">Daftar <?= Html::encode(strtolower($jenisLabel)) ?> menyesuaikan level akses user yang sedang login.</p>
                </div>
                <div>
                    <a href="<?= Url::to(['create']) ?>" class="btn btn-primary">
                        <i class="ph-duotone ph-plus-square"></i> TAMBAH
                    </a>
                </div>
            </div>
            <div class="card-body table-border-style">
                <?php if ($errorMessage): ?>
                    <div class="alert alert-danger mb-3">
                        <strong>Master data faskes belum bisa ditampilkan.</strong><br>
                        <?= Html::encode($errorMessage) ?>
                    </div>
                <?php endif; ?>

                <?php
                $firstRow = $rows[0] ?? [];
                $showsExpectedFacilityFields = is_array($firstRow) && (
                    array_key_exists('kode_satusehat', $firstRow)
                    || array_key_exists('nama', $firstRow)
                    || array_key_exists('nama_provinsi', $firstRow)
                    || array_key_exists('jenis_sarana_nama', $firstRow)
                );
                ?>

                <?php if (!$errorMessage && !empty($rows) && !$showsExpectedFacilityFields): ?>
                    <div class="alert alert-warning">
                        Data lokal sudah terbaca, tetapi baris data belum mengandung field fasilitas yang lengkap untuk seluruh kolom tabel.
                    </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <form method="get" action="<?= Url::to(['index']) ?>" class="d-flex align-items-center gap-2 flex-wrap">
                        <?php
                            $isProvScope = !empty($scope['mode']) && $scope['mode'] === 'provinsi';
                            $isKabScope  = !empty($scope['mode']) && $scope['mode'] === 'kabupaten';
                            $isAllScope  = empty($scope['mode']) || $scope['mode'] === 'all';
                        ?>

                        <input
                            type="text"
                            name="search"
                            value="<?= Html::encode((string)($filters['search'] ?? '')) ?>"
                            class="form-control form-control-sm"
                            style="width: 220px;"
                            placeholder="Cari nama/kode faskes"
                        >

                        <?php if ($isProvScope || $isKabScope): ?>
                            <!-- Provinsi LOCKED: tampilkan nama, kirim via hidden input -->
                            <div class="d-flex align-items-center gap-1">
                                <select class="form-select form-select-sm" style="width: 220px;" disabled>
                                    <?php foreach ($provinsiOptions as $provinsi): ?>
                                        <option value="<?= Html::encode((string)$provinsi['code']) ?>" selected>
                                            <?= Html::encode((string)$provinsi['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="kode_provinsi" value="<?= Html::encode((string)($filters['kode_provinsi'] ?? '')) ?>">
                                <i class="ph-duotone ph-lock text-muted" title="Provinsi dikunci sesuai akses user" style="font-size:16px;"></i>
                            </div>
                        <?php else: ?>
                            <!-- Provinsi BEBAS: bisa dipilih -->
                            <select name="kode_provinsi" class="form-select form-select-sm" style="width: 220px;" onchange="this.form.submit()">
                                <option value="">Semua Provinsi</option>
                                <?php foreach ($provinsiOptions as $provinsi): ?>
                                    <option value="<?= Html::encode((string)$provinsi['code']) ?>" <?= (string)($filters['kode_provinsi'] ?? '') === (string)$provinsi['code'] ? 'selected' : '' ?>>
                                        <?= Html::encode((string)$provinsi['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>

                        <?php if ($isKabScope): ?>
                            <!-- Kabupaten LOCKED untuk level kabupaten -->
                            <div class="d-flex align-items-center gap-1">
                                <select class="form-select form-select-sm" style="width: 240px;" disabled>
                                    <?php foreach ($kabupatenOptions as $kabupaten): ?>
                                        <option value="<?= Html::encode((string)$kabupaten['code']) ?>" selected>
                                            <?= Html::encode((string)$kabupaten['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="kode_kabkota" value="<?= Html::encode((string)($filters['kode_kabkota'] ?? '')) ?>">
                                <i class="ph-duotone ph-lock text-muted" title="Kabupaten dikunci sesuai akses user" style="font-size:16px;"></i>
                            </div>
                        <?php else: ?>
                            <!-- Kabupaten AKTIF: bisa dipilih (disabled jika provinsi belum dipilih dan bukan scope provinsi) -->
                            <select name="kode_kabkota" class="form-select form-select-sm" style="width: 240px;" onchange="this.form.submit()"
                                <?= (!$isProvScope && empty($filters['kode_provinsi'])) ? 'disabled' : '' ?>>
                                <option value="">Semua Kab/Kota</option>
                                <?php foreach ($kabupatenOptions as $kabupaten): ?>
                                    <option value="<?= Html::encode((string)$kabupaten['code']) ?>" <?= (string)($filters['kode_kabkota'] ?? '') === (string)$kabupaten['code'] ? 'selected' : '' ?>>
                                        <?= Html::encode((string)$kabupaten['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>

                        <button type="submit" class="btn btn-sm btn-primary">Terapkan</button>
                    </form>
                    <div>
                        <form method="get" action="<?= Url::to(['index']) ?>" class="d-flex align-items-center gap-2">
                            <input type="hidden" name="search" value="<?= Html::encode((string)($filters['search'] ?? '')) ?>">
                            <input type="hidden" name="kode_provinsi" value="<?= Html::encode((string)($filters['kode_provinsi'] ?? '')) ?>">
                            <input type="hidden" name="kode_kabkota" value="<?= Html::encode((string)($filters['kode_kabkota'] ?? '')) ?>">
                            <label class="form-label mb-0 small text-muted">Per Page</label>
                            <select name="per-page" class="form-select form-select-sm" onchange="this.form.submit()">
                                <?php foreach ([10, 20, 50, 100] as $size): ?>
                                    <option value="<?= $size ?>" <?= (int) ($filters['per_page'] ?? 10) === $size ? 'selected' : '' ?>><?= $size ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>
                </div>


                <div class="table-responsive">
                    <?php Pjax::begin(['id' => 'pjax-faskes-master']); ?>
                    <table id="table-faskes-master" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th style="width: 60px;">NO</th>
                                <th style="min-width: 140px;">Kode Satusehat</th>
                                <th style="min-width: 220px;">Nama Faskes</th>
                                <th style="min-width: 160px;">Jenis Faskes</th>
                                <th style="min-width: 260px;">Alamat</th>
                                <th style="min-width: 160px;">Provinsi</th>
                                <th style="min-width: 180px;">Kabupaten/Kota</th>
                                <th style="min-width: 160px;">Kecamatan</th>
                                <th style="min-width: 120px;">Status</th>
                                <th style="min-width: 160px;">Update Terakhir</th>
                                <th style="min-width: 100px; text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($rows)): ?>
                                <?php foreach ($rows as $index => $row): ?>
                                    <?php
                                    $provinsiValue = $resolveProvinsi($row);
                                    $kabupatenValue = $resolveKabupaten($row, $provinsiValue);
                                    $kecamatanValue = $resolveKecamatan($row, $provinsiValue, $kabupatenValue);
                                    ?>
                                    <tr>
                                        <td><?= Html::encode((string) ($pagination->offset + $index + 1)) ?></td>
                                        <td><?= Html::encode($renderValue($row, ['kode_satusehat'])) ?></td>
                                        <td><?= Html::encode($renderValue($row, ['nama'])) ?></td>
                                        <td><?= Html::encode($renderValue($row, ['jenis_sarana_nama'], $jenisLabel)) ?></td>
                                        <td><?= Html::encode($renderValue($row, ['alamat'])) ?></td>
                                        <td><?= Html::encode($provinsiValue) ?></td>
                                        <td><?= Html::encode($kabupatenValue) ?></td>
                                        <td><?= Html::encode($kecamatanValue) ?></td>
                                        <td><?= Html::encode($renderValue($row, ['status_sarana'])) ?></td>
                                        <td><?= Html::encode($renderValue($row, ['update_date'])) ?></td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <?php if (!empty($row['kode_satusehat'])): ?>
                                                    <a href="<?= Url::to(['view', 'kode_satusehat' => (string) $row['kode_satusehat'], 'snapshot' => base64_encode(Json::encode($row))]) ?>" class="btn btn-sm btn-info text-white" title="Lihat Detail" data-pjax="0">
                                                        <i class="ti ti-eye"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="<?= Url::to(['update', 'id' => $row['id']]) ?>" class="btn btn-sm btn-warning text-white" title="Edit Data" data-pjax="0">
                                                    <i class="ti ti-edit"></i>
                                                </a>
                                                <a href="<?= Url::to(['delete', 'id' => $row['id']]) ?>" class="btn btn-sm btn-danger text-white" title="Hapus Data" data-pjax="0" data-confirm="Apakah Anda yakin ingin menghapus data faskes ini?" data-method="post">
                                                    <i class="ti ti-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="11" class="text-center text-muted py-4">
                                        <?= $errorMessage ? 'Data belum bisa dimuat dari master lokal.' : 'Tidak ada data yang ditampilkan.' ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-end mt-3">
                        <?= LinkPager::widget([
                            'pagination' => $pagination,
                            'options' => ['class' => 'pagination mb-0'],
                            'linkOptions' => ['class' => 'page-link'],
                            'disabledPageCssClass' => 'page-item disabled',
                            'activePageCssClass' => 'page-item active',
                            'prevPageCssClass' => 'page-item',
                            'nextPageCssClass' => 'page-item',
                            'pageCssClass' => 'page-item',
                        ]) ?>
                    </div>
                    <?php Pjax::end(); ?>
                </div>

                <?php if (YII_DEBUG && !empty($dataDebug)): ?>
                    <details class="mt-4">
                        <summary class="fw-semibold">Debug Data Lokal</summary>
                        <pre class="mt-3 mb-0 bg-light p-3 rounded" style="max-height: 360px; overflow:auto;"><?= Html::encode(Json::encode($dataDebug, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></pre>
                    </details>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
