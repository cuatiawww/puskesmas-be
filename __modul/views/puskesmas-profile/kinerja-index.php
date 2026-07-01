<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

$this->title = 'Laporan Kinerja - ' . $puskesmas->nama_puskesmas;
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
          <h5 class="mb-0 fw-bold">RIWAYAT LAPORAN KINERJA: <?= Html::encode($puskesmas->nama_puskesmas) ?></h5>
          <p>Kategori: <?= Html::encode($puskesmas->kategori_wilayah) ?> | Kode Faskes: <?= Html::encode($puskesmas->kode_faskes) ?></p>
        </div>
      </div>
      <div class="col-sm-auto">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= Url::to(['/site/index']) ?>"><i class="ph-duotone ph-house"></i></a></li>
          <li class="breadcrumb-item"><a href="<?= Url::to(['puskesmas-profile/index']) ?>">KINERJA PUSKESMAS</a></li>
          <li class="breadcrumb-item">LAPORAN KINERJA</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
          <h5>Daftar Laporan Periodik</h5>
          <p>Isi dan kelola data kinerja per tahun atau per kuartal.</p>
        </div>
        <div class="d-flex gap-2">
          <?= Html::a('Kembali', ['index'], ['class' => 'btn btn-sm btn-secondary']) ?>
          <?= Html::a('<i class="ti ti-plus me-1"></i> Tambah Laporan Periodik', ['kinerja-create', 'id' => $puskesmas->id], ['class' => 'btn btn-sm btn-primary']) ?>
        </div>
      </div>
      <div class="card-body table-border-style">
        <div class="table-responsive">

          <?php Pjax::begin(); ?>

          <?= GridView::widget([
            'summary' => 'Menampilkan {begin} - {end} dari {totalCount} Periode Laporan',
            'tableOptions' => ['class' => 'table table-striped table-hover table-bordered align-middle'],
            'dataProvider' => $dataProvider,
            'columns' => [
              ['class' => 'yii\grid\SerialColumn', 'header' => 'NO'],
              [
                'attribute' => 'tahun',
                'header' => 'Tahun',
              ],
              [
                'attribute' => 'periode_tipe',
                'header' => 'Tipe Periode',
              ],
              [
                'attribute' => 'periode_nilai',
                'header' => 'Kuartal / Bulan',
                'value' => function($model) {
                  if ($model->periode_tipe === 'Kuartal') {
                    return 'Kuartal ' . $model->periode_nilai;
                  } elseif ($model->periode_tipe === 'Bulanan') {
                    return date("F", mktime(0, 0, 0, $model->periode_nilai, 1));
                  }
                  return 'Tahunan';
                }
              ],
              [
                'header' => 'DLI 6.1 Kesiapan',
                'format' => 'raw',
                'value' => function($model) {
                  $dokter = $model->dokter_tersedia ? '<span class="badge bg-success">Dokter Lengkap</span>' : '<span class="badge bg-danger">Dokter Kosong</span>';
                  $alkes = $model->persen_alkes >= 60 ? '<span class="badge bg-success">Alkes: ' . $model->persen_alkes . '%</span>' : '<span class="badge bg-danger">Alkes: ' . $model->persen_alkes . '%</span>';
                  $nakes = $model->nakes_9_jenis ? '<span class="badge bg-success">9 Nakes</span>' : '<span class="badge bg-danger">Nakes Kurang</span>';
                  return '<div class="d-flex flex-wrap gap-1">' . $dokter . $alkes . $nakes . '</div>';
                }
              ],
              [
                'header' => 'Tata Kelola (BLUD/ILP)',
                'format' => 'raw',
                'value' => function($model) {
                  $blud = $model->status_blud ? '<span class="badge bg-info">BLUD</span>' : '<span class="badge bg-light text-dark">Non BLUD</span>';
                  $ilp = $model->status_ilp ? '<span class="badge bg-info">ILP</span>' : '<span class="badge bg-light text-dark">Non ILP</span>';
                  return '<div class="d-flex flex-wrap gap-1">' . $blud . $ilp . '</div>';
                }
              ],
              [
                'attribute' => 'skor_pkp_total',
                'header' => 'Skor PKP Rata-rata',
                'format' => 'raw',
                'value' => function($model) {
                  $skor = number_format($model->skor_pkp_total ?? 0, 1);
                  if ($model->skor_pkp_total >= 80) {
                    $badge = '<span class="badge bg-success">Baik (' . $skor . ')</span>';
                  } elseif ($model->skor_pkp_total >= 60) {
                    $badge = '<span class="badge bg-warning text-dark">Cukup (' . $skor . ')</span>';
                  } else {
                    $badge = '<span class="badge bg-danger">Kurang (' . $skor . ')</span>';
                  }
                  return $badge;
                }
              ],
              [
                'header' => 'Checklist Detail',
                'format' => 'raw',
                'options' => ['style' => 'width: 200px; text-align: center;'],
                'value' => function($model) use ($puskesmas) {
                  $nakesBtn = Html::a('<i class="ti ti-users"></i> SDM', ['nakes', 'id' => $puskesmas->id, 'kinerja_id' => $model->id], ['class' => 'btn btn-xs btn-outline-success me-1', 'title' => 'Rincian SDM']);
                  $obatBtn = Html::a('<i class="ti ti-pill"></i> Obat', ['obat', 'id' => $puskesmas->id, 'kinerja_id' => $model->id], ['class' => 'btn btn-xs btn-outline-info me-1', 'title' => 'Checklist Obat']);
                  $alkesBtn = Html::a('<i class="ti ti-heart"></i> Alkes', ['alkes', 'id' => $puskesmas->id, 'kinerja_id' => $model->id], ['class' => 'btn btn-xs btn-outline-primary', 'title' => 'Checklist Alkes']);
                  return '<div class="d-flex justify-content-center">' . $nakesBtn . $obatBtn . $alkesBtn . '</div>';
                }
              ],
              [
                'class' => 'yii\grid\ActionColumn',
                'header' => 'Aksi',
                'options' => ['style' => 'width: 120px; text-align: center;'],
                'template' => '{update} {delete}',
                'buttons' => [
                  'update' => function ($url, $model) use ($puskesmas) {
                    return Html::a('<i class="ti ti-edit"></i>', ['kinerja-update', 'id' => $puskesmas->id, 'kinerja_id' => $model->id], ['class' => 'btn btn-sm btn-warning me-1', 'title' => 'Edit Laporan']);
                  },
                  'delete' => function ($url, $model) use ($puskesmas) {
                    return Html::a('<i class="ti ti-trash"></i>', ['kinerja-delete', 'id' => $puskesmas->id, 'kinerja_id' => $model->id], [
                      'class' => 'btn btn-sm btn-danger',
                      'title' => 'Hapus Laporan',
                      'data' => [
                        'confirm' => 'Apakah Anda yakin ingin menghapus laporan periodik ini?',
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
</div>
