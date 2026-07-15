<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Detail Desa/Kelurahan';
$this->params['active_menu'] = $activeMenu;
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-1">DETAIL DESA/KELURAHAN</h5>
            <p class="mb-0 text-muted">Informasi lengkap data desa/kelurahan.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary">Kembali</a>
            <?php if (Yii::$app->user->identity && (int) (Yii::$app->user->identity->id_user_level ?? Yii::$app->user->identity->level_user_id ?? 0) === 1): ?>
                <a href="<?= Url::to(['update', 'id' => $model->id]) ?>" class="btn btn-warning">Edit</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <tr><th style="width: 240px;">ID Master</th><td><?= Html::encode((string) $model->id) ?></td></tr>
            <tr><th>Kode Wilayah</th><td><?= Html::encode((string) $model->code) ?></td></tr>
            <tr><th>Kode BPS</th><td><?= Html::encode((string) $model->bps_code) ?></td></tr>
            <tr><th>Nama Desa/Kelurahan</th><td><?= Html::encode((string) $model->name) ?></td></tr>
            <tr><th>Kecamatan</th><td><?= Html::encode((string) ($model->kecamatan->name ?? '-')) ?></td></tr>
            <tr><th>Latitude</th><td><?= Html::encode((string) $model->latitude) ?></td></tr>
            <tr><th>Longitude</th><td><?= Html::encode((string) $model->longitude) ?></td></tr>
            <tr><th>Jumlah Penduduk</th><td><?= Html::encode((string) $model->jumlah_penduduk) ?></td></tr>
            <tr><th>Luas Wilayah</th><td><?= Html::encode((string) $model->luas_wilayah) ?></td></tr>
        </table>
    </div>
</div>
