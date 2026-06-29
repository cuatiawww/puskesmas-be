<?php

use app\models\RegisterMasyarakatForm;
use app\models\UserRegistration;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\UserRegistration */

$this->title = 'Review Pendaftaran';
$this->params['active_menu'] = 'approval-masyarakat';

$kategoriOptions = RegisterMasyarakatForm::kategoriAksesOptions();
$tujuanOptions = RegisterMasyarakatForm::tujuanAksesOptions();
$canReview = $model->status === UserRegistration::STATUS_PENDING_APPROVAL;

$statusClass = 'bg-light-secondary';
if ($model->status === UserRegistration::STATUS_PENDING_APPROVAL) {
    $statusClass = 'bg-light-warning';
} elseif ($model->status === UserRegistration::STATUS_APPROVED) {
    $statusClass = 'bg-light-success';
} elseif ($model->status === UserRegistration::STATUS_REJECTED) {
    $statusClass = 'bg-light-danger';
}
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center justify-content-between">
      <div class="col-sm-auto">
        <div class="page-header-title">
          <h5 class="mb-0 font-weight-600 fw-bold">REVIEW PENDAFTARAN</h5>
        </div>
        <p>Detail pengajuan akun masyarakat</p>
      </div>
      <div class="col-sm-auto">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= Url::to(['site/index']) ?>"><i class="ph-duotone ph-house"></i></a></li>
          <li class="breadcrumb-item"><a href="<?= Url::to(['index']) ?>">Approval Masyarakat</a></li>
          <li class="breadcrumb-item" aria-current="page">Review</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<?php foreach (['success', 'warning', 'error'] as $type): ?>
  <?php if (Yii::$app->session->hasFlash($type)): ?>
    <div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?>">
      <?= Html::encode(Yii::$app->session->getFlash($type)) ?>
    </div>
  <?php endif; ?>
<?php endforeach; ?>

<div class="row">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Data Pendaftar</h5>
        <span class="badge <?= $statusClass ?>"><?= Html::encode($model->getStatusLabel()) ?></span>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered mb-0">
            <tbody>
              <tr><th style="width:220px">Nama Lengkap</th><td><?= Html::encode($model->nama_lengkap) ?></td></tr>
              <tr><th>Username</th><td><?= Html::encode($model->username) ?></td></tr>
              <tr><th>Email</th><td><?= Html::encode($model->email) ?></td></tr>
              <tr><th>Telepon</th><td><?= Html::encode($model->telp) ?></td></tr>
              <tr><th>Kategori Akses</th><td><?= Html::encode($kategoriOptions[$model->kategori_akses] ?? $model->kategori_akses) ?></td></tr>
              <tr><th>Nama Institusi</th><td><?= Html::encode($model->nama_institusi ?: '-') ?></td></tr>
              <tr><th>Pekerjaan/Posisi</th><td><?= Html::encode($model->pekerjaan_posisi ?: '-') ?></td></tr>
              <tr><th>Alamat</th><td><?= nl2br(Html::encode($model->alamat_user)) ?></td></tr>
              <tr><th>Provinsi ID</th><td><?= Html::encode($model->provinsi_id) ?></td></tr>
              <tr><th>Kab/Kota ID</th><td><?= Html::encode($model->kabupaten_id) ?></td></tr>
              <tr><th>Tujuan Akses</th><td><?= Html::encode($tujuanOptions[$model->tujuan_akses] ?? $model->tujuan_akses) ?></td></tr>
              <tr><th>Tujuan Lainnya</th><td><?= Html::encode($model->tujuan_akses_lainnya ?: '-') ?></td></tr>
              <tr><th>Email Diverifikasi</th><td><?= $model->email_verified_at ? Yii::$app->formatter->asDatetime($model->email_verified_at, 'php:d/m/Y H:i') : '-' ?></td></tr>
              <tr><th>Tanggal Daftar</th><td><?= Yii::$app->formatter->asDatetime($model->created_at, 'php:d/m/Y H:i') ?></td></tr>
              <?php if ($model->rejection_reason): ?>
                <tr><th>Alasan Penolakan</th><td><?= nl2br(Html::encode($model->rejection_reason)) ?></td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Keputusan Admin Pusat</h5>
      </div>
      <div class="card-body">
        <?php if ($canReview): ?>
          <?= Html::beginForm(['approve', 'id' => $model->id], 'post', ['class' => 'mb-3']) ?>
            <?= Html::submitButton('Approve', [
                'class' => 'btn btn-success w-100',
                'data-confirm' => 'Approve pendaftaran ini?',
            ]) ?>
          <?= Html::endForm() ?>

          <?= Html::beginForm(['reject', 'id' => $model->id], 'post') ?>
            <div class="mb-3">
              <label class="form-label" for="reason">Alasan Penolakan</label>
              <?= Html::textarea('reason', '', [
                  'id' => 'reason',
                  'class' => 'form-control',
                  'rows' => 4,
                  'placeholder' => 'Opsional',
              ]) ?>
            </div>
            <?= Html::submitButton('Reject', [
                'class' => 'btn btn-danger w-100',
                'data-confirm' => 'Tolak pendaftaran ini?',
            ]) ?>
          <?= Html::endForm() ?>
        <?php else: ?>
          <p class="mb-3">Pengajuan ini sudah memiliki keputusan akhir atau belum melewati verifikasi email.</p>
          <?= Html::a('Kembali ke daftar', ['index'], ['class' => 'btn btn-outline-secondary w-100']) ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
