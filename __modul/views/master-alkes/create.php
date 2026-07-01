<?php
use yii\helpers\Url;
$this->title = 'Tambah Alkes';
?>
<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center justify-content-between">
      <div class="col-sm-auto">
        <div class="page-header-title">
          <h5 class="mb-0 fw-bold">TAMBAH MASTER ALKES</h5>
          <p>Daftarkan alkes/sarpras baru.</p>
        </div>
      </div>
      <div class="col-sm-auto">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= Url::to(['/site/index']) ?>"><i class="ph-duotone ph-house"></i></a></li>
          <li class="breadcrumb-item"><a href="<?= Url::to(['master-alkes/index']) ?>">MASTER ALKES</a></li>
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
