<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Detail Provinsi';
$this->params['active_menu'] = $activeMenu;
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-1">DETAIL PROVINSI</h5>
            <p class="mb-0 text-muted">Informasi lengkap data provinsi.</p>
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
            <tr><th>ID Tbl Wilayah</th><td><?= Html::encode((string) $model->tbl_wilayah_id) ?></td></tr>
            <tr><th>Nama Provinsi</th><td><?= Html::encode((string) $model->nama_wilayah) ?></td></tr>
            <tr><th>Status</th><td><?= (int) $model->status_aktif === 1 ? 'Aktif' : 'Nonaktif' ?></td></tr>
            <tr><th>Keterangan</th><td><?= Html::encode((string) $model->keterangan) ?></td></tr>
        </table>
    </div>
</div>
