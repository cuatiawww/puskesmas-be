<?php

use yii\helpers\Url;

$this->title = 'Edit Log Versi';
?>
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center justify-content-between">
      <div class="col-sm-auto">
        <div class="page-header-title">
          <h5 class="mb-0 fw-bold">EDIT LOG VERSI</h5>
          <p>Ubah catatan rilis atau log pembaruan versi sistem.</p>
        </div>
      </div>
      <div class="col-sm-auto">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= Url::to(['/site/index']) ?>"><i class="ph-duotone ph-house"></i></a></li>
          <li class="breadcrumb-item"><a href="<?= Url::to(['versi-sistem/index']) ?>">VERSI SISTEM</a></li>
          <li class="breadcrumb-item">EDIT</li>
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
