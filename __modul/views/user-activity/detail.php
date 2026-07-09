<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\UserActivityLog $model */

$this->title = 'Detail Log #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Log Aktivitas Pengguna', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->username, 'url' => ['view', 'username' => $model->username]];
$this->params['breadcrumbs'][] = $this->title;

// Parse details / changes JSON
$changes = null;
if (!empty($model->changes)) {
    $changes = json_decode($model->changes, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $changes = null;
    }
}
?>

<!-- Page Header (Flat Able style) -->
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center justify-content-between">
            <div class="col-sm-auto">
                <div class="page-header-title">
                    <h5 class="mb-0 font-weight-600 fw-bold">DETAIL LOG AKTIVITAS</h5>
                </div>
                <p>Detail audit trail data perubahan dan metadata request</p>
            </div>
            <div class="col-sm-auto">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= Url::to(['/site/index']) ?>"><i class="ti ti-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= Url::to(['index']) ?>">LOG AKTIVITAS PENGGUNA</a></li>
                    <li class="breadcrumb-item"><a href="<?= Url::to(['view', 'username' => $model->username]) ?>"><?= Html::encode($model->username) ?></a></li>
                    <li class="breadcrumb-item">DETAIL LOG #<?= $model->id ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="mb-3">
    <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Kembali ke Riwayat User', ['view', 'username' => $model->username], ['class' => 'btn btn-outline-secondary rounded-2 px-3']) ?>
</div>

<div class="row">
    <!-- General Request Metadata (Left Panel) -->
    <div class="col-lg-5 mb-4">
        <div class="card h-100">
            <div class="card-header py-3">
                <h5 class="mb-0"><i class="ti ti-info-circle me-2 text-primary"></i>Metadata Request</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless align-middle mb-0" style="font-size: 0.95rem;">
                    <tbody>
                        <tr>
                            <td class="text-muted py-2" style="width: 150px;">ID Log</td>
                            <td class="fw-bold py-2">#<?= $model->id ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted py-2">Pengguna</td>
                            <td class="py-2">
                                <span class="badge bg-light text-dark border px-2 py-1"><i class="ti ti-user me-1"></i><?= Html::encode($model->username) ?></span>
                                <?php if ($model->user_id): ?>
                                    <span class="small text-muted">(ID: <?= $model->user_id ?>)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted py-2">Tindakan</td>
                            <td class="py-2">
                                <?php
                                switch ($model->action) {
                                    case 'create':
                                        echo '<span class="badge bg-light-success fw-bold px-3 py-1">CREATE</span>';
                                        break;
                                    case 'update':
                                        echo '<span class="badge bg-light-warning text-dark fw-bold px-3 py-1">UPDATE</span>';
                                        break;
                                    case 'delete':
                                        echo '<span class="badge bg-light-danger fw-bold px-3 py-1">DELETE</span>';
                                        break;
                                    case 'view':
                                    default:
                                        echo '<span class="badge bg-light text-dark border px-3 py-1">VIEW</span>';
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted py-2">Waktu</td>
                            <td class="py-2 fw-bold text-dark"><?= Yii::$app->formatter->asDatetime($model->created_at, 'long') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted py-2">Alamat IP</td>
                            <td class="py-2 text-primary fw-bold"><?= Html::encode($model->ip_address) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted py-2">Browser / OS</td>
                            <td class="py-2 small"><?= Html::encode($model->browser) ?> (<?= Html::encode($model->platform) ?>)</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-2">Modul / Rute</td>
                            <td class="py-2">
                                <?php if ($model->module): ?>
                                    <span class="badge bg-secondary me-1"><?= Html::encode($model->module) ?></span>
                                <?php endif; ?>
                                <code><?= Html::encode($model->route) ?></code>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted py-2">URL Lengkap</td>
                            <td class="py-2">
                                <div style="max-height: 80px; overflow-y: auto; overflow-wrap: break-word; font-size: 0.85rem;" class="text-muted font-monospace bg-light p-2 border rounded-2">
                                    <?= Html::encode($model->url) ?>
                                </div>
                            </td>
                        </tr>
                        <?php if ($model->target_model): ?>
                            <tr>
                                <td class="text-muted py-2">Model Target</td>
                                <td class="py-2">
                                    <code class="text-dark bg-light px-2 py-1 border rounded-1"><?= Html::encode($model->target_model) ?></code>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted py-2">ID Record Target</td>
                                <td class="py-2 fw-bold">
                                    <span class="badge bg-dark text-white"><?= Html::encode($model->target_id) ?></span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Data Modification Detail / Changes (Right Panel) -->
    <div class="col-lg-7 mb-4">
        <div class="card h-100">
            <div class="card-header py-3">
                <h5 class="mb-0"><i class="ti ti-edit-circle me-2 text-primary"></i>Detail Data & Perubahan</h5>
            </div>
            <div class="card-body">
                <?php if ($model->action === 'update' && is_array($changes) && isset($changes['old'], $changes['new'])): ?>
                    <!-- UPDATE Diff Table -->
                    <div class="alert alert-primary py-2 px-3 mb-3">
                        <i class="ti ti-info-circle me-2"></i>Berikut adalah daftar kolom yang nilainya diubah oleh pengguna.
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-2" style="width: 200px;">Nama Kolom</th>
                                    <th class="py-2 text-danger bg-light-red">Nilai Lama</th>
                                    <th class="py-2 text-success bg-light-green">Nilai Baru</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($changes['new'] as $field => $newValue): 
                                    $oldValue = $changes['old'][$field] ?? null;
                                ?>
                                    <tr>
                                        <td class="fw-bold py-2"><code><?= Html::encode($field) ?></code></td>
                                        <td class="py-2 text-danger text-decoration-line-through small" style="background-color: #ffebee;">
                                            <?= Html::encode(is_array($oldValue) ? json_encode($oldValue) : (string)$oldValue) ?>
                                        </td>
                                        <td class="py-2 text-success fw-bold small" style="background-color: #e8f5e9;">
                                            <?= Html::encode(is_array($newValue) ? json_encode($newValue) : (string)$newValue) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                <?php elseif (($model->action === 'create' || $model->action === 'delete') && is_array($changes)): ?>
                    <!-- CREATE / DELETE Full Attributes List -->
                    <div class="alert alert-secondary py-2 px-3 mb-3 small text-muted">
                        <i class="ti ti-list me-2"></i>Seluruh isi properti data yang <?= $model->action === 'create' ? 'dibuat' : 'dihapus' ?>.
                    </div>
                    <div class="table-responsive" style="max-height: 450px; overflow-y: auto;">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-2">Nama Kolom</th>
                                    <th class="py-2">Nilai Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($changes as $field => $val): ?>
                                    <tr>
                                        <td class="fw-bold py-2" style="width: 200px;"><code><?= Html::encode($field) ?></code></td>
                                        <td class="py-2 small">
                                            <?= Html::encode(is_array($val) ? json_encode($val) : (string)$val) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                <?php elseif ($model->action === 'view' && is_array($changes)): ?>
                    <!-- VIEW Route Parameters -->
                    <h6 class="fw-bold mb-2">Parameter URL Request:</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-2">Parameter</th>
                                    <th class="py-2">Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($changes as $param => $val): ?>
                                    <tr>
                                        <td class="fw-bold py-2"><code><?= Html::encode($param) ?></code></td>
                                        <td class="py-2 small"><?= Html::encode(is_array($val) ? json_encode($val) : (string)$val) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <!-- Empty Changes -->
                    <div class="text-center py-5 text-muted">
                        <i class="ti ti-message-dots d-block mb-3 text-primary" style="font-size: 3rem;"></i>
                        <p class="mb-0">Tidak ada detail perubahan data yang terekam pada aktivitas ini.</p>
                        <span class="small text-muted">(Biasanya untuk akses halaman murni/view tanpa input)</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
