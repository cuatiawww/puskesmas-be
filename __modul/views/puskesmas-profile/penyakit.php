<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

$this->title = 'Data Kasus Penyakit - ' . $puskesmas->nama_puskesmas;
$this->params['active_menu'] = 'puskesmas';

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
          <h5 class="mb-0 fw-bold">DATA KASUS PENYAKIT: <?= Html::encode($puskesmas->nama_puskesmas) ?></h5>
          <p>Kelola data epidemiologi penyakit di wilayah kerja Puskesmas.</p>
        </div>
      </div>
      <div class="col-sm-auto">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= Url::to(['/site/index']) ?>"><i class="ph-duotone ph-house"></i></a></li>
          <li class="breadcrumb-item"><a href="<?= Url::to(['puskesmas-profile/index']) ?>">KINERJA PUSKESMAS</a></li>
          <li class="breadcrumb-item">DATA KASUS PENYAKIT</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <!-- Daftar Penyakit (Kiri) -->
  <div class="col-md-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h5>Daftar Kasus Penyakit</h5>
          <p>Data epidemiologi penyakit yang dilaporkan oleh faskes.</p>
        </div>
        <?= Html::a('Kembali', ['index'], ['class' => 'btn btn-sm btn-secondary']) ?>
      </div>
      <div class="card-body table-border-style">
        <div class="table-responsive">

          <?php Pjax::begin(); ?>

          <?= GridView::widget([
            'summary' => 'Menampilkan {begin} - {end} dari {totalCount} data penyakit',
            'tableOptions' => ['class' => 'table table-striped table-hover table-bordered align-middle'],
            'dataProvider' => $dataProvider,
            'columns' => [
              ['class' => 'yii\grid\SerialColumn', 'header' => 'NO'],
              [
                'attribute' => 'tahun',
                'header' => 'Tahun',
              ],
              [
                'attribute' => 'bulan',
                'header' => 'Bulan',
                'value' => function($model) {
                  return date("F", mktime(0, 0, 0, $model->bulan, 1));
                }
              ],
              [
                'attribute' => 'nama_penyakit',
                'header' => 'Nama Penyakit',
                'format' => 'raw',
                'value' => function($model) {
                  return '<strong>' . Html::encode($model->nama_penyakit) . '</strong>';
                }
              ],
              [
                'attribute' => 'jumlah_kasus',
                'header' => 'Jumlah Kasus',
                'value' => function($model) {
                  return number_format($model->jumlah_kasus ?? 0) . ' kasus';
                }
              ],
              [
                'class' => 'yii\grid\ActionColumn',
                'header' => 'Aksi',
                'options' => ['style' => 'width: 80px; text-align: center;'],
                'template' => '{delete}',
                'buttons' => [
                  'delete' => function ($url, $model) use ($puskesmas) {
                    return Html::a('<i class="ti ti-trash"></i>', ['penyakit-delete', 'id' => $puskesmas->id, 'penyakit_id' => $model->id], [
                      'class' => 'btn btn-xs btn-danger',
                      'title' => 'Hapus Data',
                      'data' => [
                        'confirm' => 'Apakah Anda yakin ingin menghapus data penyakit ini?',
                        'method' => 'post',
                      ]
                    ]);
                  },
                ],
              ],
            ],
          ]); ?>

          <?php Pjax::end(); ?>

        </div>
      </div>
    </div>
  </div>

  <!-- Form Tambah (Kanan) -->
  <div class="col-md-4">
    <div class="card">
      <div class="card-header">
        <h5 class="fw-bold">Tambah Data Penyakit</h5>
      </div>
      <div class="card-body">
        <?php $form = ActiveForm::begin(); ?>

        <div class="row">
          <div class="col-6">
            <?= $form->field($model, 'tahun')->textInput(['type' => 'number', 'min' => 2020]) ?>
          </div>
          <div class="col-6">
            <?= $form->field($model, 'bulan')->dropDownList([
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ]) ?>
          </div>
        </div>

        <?= $form->field($model, 'nama_penyakit')->textInput(['maxlength' => true, 'placeholder' => 'Contoh: Influenza, Diare, ISPA']) ?>

        <?= $form->field($model, 'jumlah_kasus')->textInput(['type' => 'number', 'min' => 0]) ?>

        <div class="form-group text-end mt-3">
          <?= Html::submitButton('<i class="ti ti-plus me-1"></i> Tambah Data', ['class' => 'btn btn-primary w-100']) ?>
        </div>

        <?php ActiveForm::end(); ?>
      </div>
    </div>
  </div>
</div>
