<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Download';
$this->params['active_menu'] = 'download';

$canCreate = Yii::$app->controller->canCreate();
$canUpdate = Yii::$app->controller->canUpdate();
$canDelete = Yii::$app->controller->canDelete();
$hasCrudAction = $canUpdate || $canDelete;

$swal = Yii::$app->session->getFlash('swal', null);
if ($swal) {
    $swalJson = \yii\helpers\Json::encode($swal);
    $js = <<<JS
(function(){
  var opt = $swalJson;
  if (typeof Swal !== 'undefined') Swal.fire(opt);
})();
JS;
    $this->registerJs($js);
}
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center justify-content-between">
      <div class="col-sm-auto">
        <div class="page-header-title">
          <h5 class="mb-0 fw-bold">DOWNLOAD</h5>
          <p>Perangkat lunak pendukung penggunaan Sistem Informasi Kesehatan</p>
        </div>
      </div>
      <div class="col-sm-auto">
        <ul class="breadcrumb">
          <li class="breadcrumb-item">
            <a href="<?= Url::to(['/site/index']) ?>">
              <i class="ph-duotone ph-house"></i>
            </a>
          </li>
          <li class="breadcrumb-item">Download</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="card shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
          <h5 class="mb-0 fw-bold">DAFTAR DOWNLOAD</h5>
          <p class="mb-0 text-muted">Daftar aplikasi yang direkomendasikan untuk mengakses sistem</p>
        </div>
        <?php if ($canCreate): ?>
          <a href="<?= Url::to(['download/create']) ?>" class="btn btn-sm btn-primary">
            <i class="ti ti-plus me-1"></i> Tambah Unduhan
          </a>
        <?php endif; ?>
      </div>

      <div class="card-body table-border-style">
        <div class="table-responsive">
          <table class="table table-bordered align-middle">
            <thead class="text-white text-center" style="background-color: #1abc9c; border-color: #1abc9c;">
              <tr>
                <th width="5%">NO</th>
                <th width="35%">NAMA DOWNLOAD</th>
                <th width="30%">KATEGORI / FUNGSI</th>
                <th width="15%">DOWNLOAD</th>
                <?php if ($hasCrudAction): ?>
                  <th width="15%">AKSI</th>
                <?php endif; ?>
              </tr>
            </thead>
            <tbody>
              <?php 
              $models = $dataProvider->getModels();
              if (empty($models)): 
              ?>
                <tr>
                   <td colspan="<?= $hasCrudAction ? 5 : 4 ?>" class="text-center text-muted py-4">Data unduhan belum tersedia.</td>
                </tr>
              <?php 
              else: 
                $no = $dataProvider->pagination->offset + 1;
                foreach ($models as $model): 
              ?>
                <tr>
                  <td class="text-center"><?= $no++ ?></td>
                  <td class="fw-semibold"><?= Html::encode($model->nama_download) ?></td>
                  <td class="text-muted"><?= Html::encode($model->kategori ?: '-') ?></td>
                  <td class="text-center">
                    <?php if (!empty($model->link_download)): ?>
                      <?= Html::a(
                        '<i class="ti ti-download me-1"></i> Download',
                        $model->link_download,
                        ['class' => 'btn btn-sm btn-primary rounded-1', 'target' => '_blank']
                      ) ?>
                    <?php else: ?>
                      <span class="text-muted small">-</span>
                    <?php endif; ?>
                  </td>
                  <?php if ($hasCrudAction): ?>
                    <td class="text-center">
                      <div class="d-flex justify-content-center gap-1">
                        <?php if ($canUpdate): ?>
                          <?= Html::a('<i class="ti ti-edit"></i>', ['update', 'id' => $model->id], ['class' => 'btn btn-xs btn-warning']) ?>
                        <?php endif; ?>
                        <?php if ($canDelete): ?>
                          <?= Html::a('<i class="ti ti-trash"></i>', ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-xs btn-danger',
                            'data' => [
                              'confirm' => 'Apakah Anda yakin ingin menghapus data unduhan ini?',
                              'method' => 'post',
                            ]
                          ]) ?>
                        <?php endif; ?>
                      </div>
                    </td>
                  <?php endif; ?>
                </tr>
              <?php 
                endforeach; 
              endif; 
              ?>
            </tbody>
          </table>
          
          <div class="d-flex justify-content-end mt-3">
            <?= \yii\widgets\LinkPager::widget([
              'pagination' => $dataProvider->pagination,
              'options' => ['class' => 'pagination pagination-sm mb-0'],
              'linkContainerOptions' => ['class' => 'page-item'],
              'linkOptions' => ['class' => 'page-link'],
              'disabledPageCssClass' => 'disabled',
              'activePageCssClass' => 'active',
            ]) ?>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>
