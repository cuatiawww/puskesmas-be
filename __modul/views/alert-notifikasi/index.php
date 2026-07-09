<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var array $notifications */
/** @var array $stats */

$this->title = 'Pusat Notifikasi';
$this->params['breadcrumbs'][] = $this->title;
?>

<!-- Page Header (Flat Able style) -->
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center justify-content-between">
            <div class="col-sm-auto">
                <div class="page-header-title">
                    <h5 class="mb-0 font-weight-600 fw-bold">PUSAT NOTIFIKASI</h5>
                </div>
                <p>Pusat persetujuan akun pengguna dan verifikasi pendaftaran sistem</p>
            </div>
            <div class="col-sm-auto">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= Url::to(['/site/index']) ?>"><i class="ti ti-home"></i></a></li>
                    <li class="breadcrumb-item">PUSAT NOTIFIKASI</li>
                </ul>
            </div>
            <div style="margin-top: 10px;" class="d-flex col-12">
                <a class="btn btn-sm btn-primary rounded-pill px-2" role="button" href="javascript:void(0);">
                    <i class="ti ti-external-link me-1"></i> Info Selengkapnya
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Summary Status Cards -->
<div class="row mb-4">
    <!-- Total Notifications -->
    <div class="col-md-4">
        <div class="card bg-primary text-white mb-3">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-white text-uppercase mb-1 small opacity-75">Total Notifikasi</p>
                        <h3 class="mb-0 text-white fw-bold"><?= number_format($stats['total']) ?></h3>
                    </div>
                    <i class="ti ti-bell text-white" style="font-size: 2.5rem; opacity: 0.6;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Unread Notifications -->
    <div class="col-md-4">
        <div class="card bg-danger text-white mb-3">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-white text-uppercase mb-1 small opacity-75">Belum Ditinjau</p>
                        <h3 class="mb-0 text-white fw-bold"><?= number_format($stats['unread']) ?></h3>
                    </div>
                    <i class="ti ti-mail text-white" style="font-size: 2.5rem; opacity: 0.6;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Read Notifications -->
    <div class="col-md-4">
        <div class="card bg-success text-white mb-3">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-white text-uppercase mb-1 small opacity-75">Sudah Ditinjau</p>
                        <h3 class="mb-0 text-white fw-bold"><?= number_format($stats['read']) ?></h3>
                    </div>
                    <i class="ti ti-mail-opened text-white" style="font-size: 2.5rem; opacity: 0.6;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Full-Width Notifications Feed -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5>Pusat Notifikasi Persetujuan Akun</h5>
                    <p class="mb-0 text-muted">Daftar permohonan persetujuan pendaftaran akun baru oleh masyarakat.</p>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php if (empty($notifications)): ?>
                        <div class="list-group-item p-5 text-center text-muted">
                            <i class="ti ti-bell-off mb-2" style="font-size: 3rem; display: block; opacity: 0.5;"></i>
                            Belum ada notifikasi persetujuan pendaftaran akun saat ini.
                        </div>
                    <?php else: ?>
                        <?php foreach ($notifications as $notif): ?>
                            <div class="list-group-item p-4 <?= $notif['status'] === 'Belum Dibaca' ? 'bg-light-primary bg-opacity-10' : '' ?>">
                                <div class="d-flex align-items-start">
                                    <div class="avtar avtar-lg rounded-circle <?= $notif['status'] === 'Belum Dibaca' ? 'bg-primary text-white' : 'bg-light-secondary text-muted' ?> flex-shrink-0">
                                        <i class="<?= $notif['icon'] ?>" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <h5 class="mb-0 fw-bold text-dark">
                                                <?php if (!empty($notif['url']) && $notif['url'] !== '#'): ?>
                                                    <a href="<?= Url::to(['/alert-notifikasi/read', 'id' => $notif['id'], 'url' => $notif['url']]) ?>" class="text-dark hover-primary fw-bold text-decoration-none"><?= Html::encode($notif['title']) ?></a>
                                                <?php else: ?>
                                                    <a href="<?= Url::to(['/alert-notifikasi/read', 'id' => $notif['id']]) ?>" class="text-dark hover-primary fw-bold text-decoration-none"><?= Html::encode($notif['title']) ?></a>
                                                <?php endif; ?>
                                                <?php if ($notif['status'] === 'Belum Dibaca'): ?>
                                                    <span class="badge bg-danger ms-2" style="font-size: 0.7rem; vertical-align: middle;">Baru</span>
                                                <?php endif; ?>
                                            </h5>
                                            <small class="text-muted"><i class="ti ti-clock me-1"></i><?= Html::encode($notif['time']) ?></small>
                                        </div>
                                        <p class="mb-3" style="font-size: 0.95rem; line-height: 1.6; color: #1e293b !important; font-weight: 500;">
                                            <?= Html::encode($notif['description']) ?>
                                        </p>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge <?= $notif['badge_class'] ?> px-3 py-2 fw-semibold">
                                                <?= Html::encode($notif['badge']) ?>
                                            </span>
                                            <span class="badge bg-secondary text-white px-3 py-2 fw-semibold">
                                                <?= Html::encode($notif['category']) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
