<?php

use yii\helpers\Url;

$this->title = 'Tambah Unduhan';
?>
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center justify-content-between">
      <div class="col-sm-auto">
        <div class="page-header-title">
          <h5 class="mb-0 fw-bold">TAMBAH UNDUHAN</h5>
          <p>Tambahkan file atau aplikasi pendukung sistem yang baru.</p>
        </div>
      </div>
      <div class="col-sm-auto">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= Url::to(['/site/index']) ?>"><i class="ph-duotone ph-house"></i></a></li>
          <li class="breadcrumb-item"><a href="<?= Url::to(['download/index']) ?>">DOWNLOAD</a></li>
          <li class="breadcrumb-item">TAMBAH</li>
        </ul>
      </div>
    </div>
  </div>
</div>
<div class="row">
    <div class="col-md-12">
        <?= $this->render('_form', ['model' => $model]) ?>
    </div>
</div>
