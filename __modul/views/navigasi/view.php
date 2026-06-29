<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = $model->label;
$this->params['active_menu'] = 'navigasi';
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center justify-content-between">
      <div class="col-sm-auto">
        <div class="page-header-title">
          <h5 class="mb-0 fw-bold"><?= Html::encode($this->title) ?></h5>
          <p>Detail modul/navigasi</p>
        </div>
      </div>
      <div class="col-sm-auto">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['/site/index']) ?>"><i class="ph-duotone ph-house"></i></a></li>
          <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['index']) ?>">NAVIGASI</a></li>
          <li class="breadcrumb-item" aria-current="page">View</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Detail Navigasi</h5>
        <div>
          <?= Html::a('Edit', ['update', 'id' => $model->id], ['class' => 'btn btn-sm btn-warning']) ?>
          <?= Html::a('Delete', ['delete', 'id' => $model->id], ['class' => 'btn btn-sm btn-danger', 'data-confirm' => 'Are you sure?', 'data-method' => 'post']) ?>
          <?= Html::a('Kembali', ['index'], ['class' => 'btn btn-sm btn-secondary']) ?>
        </div>
      </div>
      <div class="card-body">
        <?= DetailView::widget([
          'model' => $model,
          'attributes' => [
            'id',
            'nama_modul',
            'label',
            'deskripsi',
            'urutan',
            'is_active:boolean',
            'created_at',
            'updated_at',
          ],
        ]) ?>
      </div>
    </div>
  </div>
</div>
