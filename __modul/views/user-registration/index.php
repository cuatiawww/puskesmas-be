<?php

use app\models\UserRegistration;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $status string|null */

$this->title = 'Approval Masyarakat';
$this->params['active_menu'] = 'approval-masyarakat';

$statusItems = ['' => 'Semua'] + UserRegistration::statusLabels();

$this->registerCss(<<<CSS
.approval-filter {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}
.approval-filter .btn {
  border-radius: 999px;
}
.approval-table thead th {
  background-color: #219b98;
  color: #fff;
  border-color: #219b98;
  font-weight: 600;
}
.approval-table thead th a {
  color: #fff;
}
.approval-table thead th a:hover,
.approval-table thead th a:focus {
  color: #fff;
  text-decoration: underline;
}
CSS);
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center justify-content-between">
      <div class="col-sm-auto">
        <div class="page-header-title">
          <h5 class="mb-0 font-weight-600 fw-bold">APPROVAL MASYARAKAT</h5>
        </div>
        <p>Review pendaftaran akun masyarakat</p>
      </div>
      <div class="col-sm-auto">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= Url::to(['site/index']) ?>"><i class="ph-duotone ph-house"></i></a></li>
          <li class="breadcrumb-item" aria-current="page">Approval Masyarakat</li>
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

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Daftar Pengajuan</h5>
    <div class="approval-filter">
      <?php foreach ($statusItems as $key => $label): ?>
        <?= Html::a(Html::encode($label), ['index', 'status' => $key ?: null], [
            'class' => 'btn btn-sm ' . ((string) $status === (string) $key ? 'btn-primary' : 'btn-outline-secondary'),
        ]) ?>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="card-body">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-striped table-hover approval-table'],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'username',
            'nama_lengkap',
            'email',
            [
                'attribute' => 'kategori_akses',
                'value' => function ($model) {
                    return \app\models\RegisterMasyarakatForm::kategoriAksesOptions()[$model->kategori_akses] ?? $model->kategori_akses;
                },
            ],
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function ($model) {
                    $class = 'bg-light-secondary';
                    if ($model->status === UserRegistration::STATUS_PENDING_APPROVAL) $class = 'bg-light-warning';
                    if ($model->status === UserRegistration::STATUS_APPROVED) $class = 'bg-light-success';
                    if ($model->status === UserRegistration::STATUS_REJECTED) $class = 'bg-light-danger';
                    return '<span class="badge ' . $class . '">' . Html::encode($model->getStatusLabel()) . '</span>';
                },
            ],
            [
                'attribute' => 'created_at',
                'format' => ['datetime', 'php:d/m/Y H:i'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {delete}',
                'buttons' => [
                    'view' => function ($url, $model) {
                        return Html::a('<i class="ti ti-eye"></i>', ['view', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-primary',
                            'title' => 'Review',
                        ]);
                    },
                    'delete' => function ($url, $model) {
                        return Html::a('<i class="ti ti-trash"></i>', ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-danger',
                            'title' => 'Hapus',
                            'data-method' => 'post',
                            'data-confirm' => 'Apakah Anda yakin ingin menghapus data pendaftaran dan user ini? Tindakan ini tidak dapat dibatalkan.',
                        ]);
                    },
                ],
            ],
        ],
    ]) ?>
  </div>
</div>
