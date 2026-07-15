<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Detail ' . $jenisLabel;
$this->params['active_menu'] = $activeMenu;

$renderValue = static function (array $row, array $keys): string {
    foreach ($keys as $key) {
        if (array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== '') {
            return (string) $row[$key];
        }
    }

    return '-';
};
?>

<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center justify-content-between">
            <div class="col-sm-auto">
                <div class="page-header-title">
                    <h5 class="mb-0 fw-bold">DETAIL <?= Html::encode(strtoupper($jenisLabel)) ?></h5>
                    <p>Detail memakai snapshot baris dari master data lokal.</p>
                </div>
            </div>
            <div class="col-sm-auto">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= Url::to(['/site/index']) ?>">
                            <i class="ph-duotone ph-house"></i>
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="<?= Url::to(['index']) ?>"><?= Html::encode(strtoupper($jenisLabel)) ?></a>
                    </li>
                    <li class="breadcrumb-item">DETAIL</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5>INFORMASI <?= Html::encode(strtoupper($jenisLabel)) ?></h5>
                    <p class="mb-0">Kode Satusehat: <?= Html::encode((string) ($kodeSatusehat ?: '-')) ?></p>
                </div>
                <div>
                    <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($row)): ?>
                    <div class="alert alert-warning mb-0">
                        Data detail belum tersedia dari snapshot master lokal.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle mb-0">
                            <tbody>
                                <tr><th style="width: 260px;">Kode Satusehat</th><td><?= Html::encode($renderValue($row, ['kode_satusehat'])) ?></td></tr>
                                <tr><th>Kode Sarana</th><td><?= Html::encode($renderValue($row, ['kode_sarana'])) ?></td></tr>
                                <tr><th>Nama Faskes</th><td><?= Html::encode($renderValue($row, ['nama'])) ?></td></tr>
                                <tr><th>Jenis Faskes</th><td><?= Html::encode($renderValue($row, ['jenis_sarana_nama'])) ?></td></tr>
                                <tr><th>Alamat</th><td><?= Html::encode($renderValue($row, ['alamat'])) ?></td></tr>
                                <tr><th>Kode Provinsi</th><td><?= Html::encode($renderValue($row, ['kode_provinsi', 'kode_prop'])) ?></td></tr>
                                <tr><th>Provinsi</th><td><?= Html::encode($renderValue($row, ['nama_provinsi', 'nama_prop'])) ?></td></tr>
                                <tr><th>Kode Kabupaten/Kota</th><td><?= Html::encode($renderValue($row, ['kode_kabkota', 'kode_kab'])) ?></td></tr>
                                <tr><th>Kabupaten/Kota</th><td><?= Html::encode($renderValue($row, ['nama_kabkota', 'nama_kab'])) ?></td></tr>
                                <tr><th>Kode Kecamatan</th><td><?= Html::encode($renderValue($row, ['kode_kecamatan'])) ?></td></tr>
                                <tr><th>Kecamatan</th><td><?= Html::encode($renderValue($row, ['nama_kecamatan'])) ?></td></tr>
                                <tr><th>Kode Kelurahan</th><td><?= Html::encode($renderValue($row, ['kode_kelurahan'])) ?></td></tr>
                                <tr><th>Kelurahan</th><td><?= Html::encode($renderValue($row, ['nama_kelurahan'])) ?></td></tr>
                                <tr><th>Status</th><td><?= Html::encode($renderValue($row, ['status_sarana'])) ?></td></tr>
                                <tr><th>Update Terakhir</th><td><?= Html::encode($renderValue($row, ['update_date'])) ?></td></tr>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
