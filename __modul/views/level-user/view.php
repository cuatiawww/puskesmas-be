<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\level_user\LevelUser $model */

$this->title = $model->nama_level;
$this->params['breadcrumbs'][] = ['label' => 'Akses', 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => 'Level User', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="level-user-view">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center justify-content-between">
                <div class="col-sm-auto">
                    <div class="page-header-title">
                        <h5 class="mb-0 font-weight-600 fw-bold">DETAIL LEVEL USER</h5>
                    </div>
                    <p><?= Html::encode($this->title) ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Informasi Level User</h5>
                    <div>
                        <?= Html::a('<i class="ti ti-edit me-1"></i> Edit', ['update', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>
                        <?= Html::a('<i class="ti ti-trash me-1"></i> Hapus', ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-danger',
                            'data' => [
                                'confirm' => 'Apakah Anda yakin ingin menghapus level user ini?',
                                'method' => 'post',
                            ],
                        ]) ?>
                    </div>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            'nama_level',
                            'deskripsi:ntext',
                            [
                                'attribute' => 'is_active',
                                'format' => 'raw',
                                'value' => function($model) {
                                    return $model->is_active
                                        ? '<span class="badge bg-light-success">Aktif</span>'
                                        : '<span class="badge bg-light-danger">Nonaktif</span>';
                                }
                            ],
                            'created_at:datetime',
                            'updated_at:datetime',
                        ],
                    ]) ?>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="d-flex gap-2 justify-content-end">
                <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Kembali ke Daftar', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
            </div>
        </div>
    </div>
</div>
