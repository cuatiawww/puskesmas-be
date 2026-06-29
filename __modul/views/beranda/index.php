<?php
use yii\helpers\Url;

$this->title = 'Dashboard - Puskesmas';
$this->params['breadcrumbs'][] = ['label' => 'Beranda', 'url' => ['beranda/index']];

$user = Yii::$app->user->identity;
$user_name = $user->username ?? 'User';
$user_fullname = $user->nama_lengkap ?? 'Administrator';
$user_email = $user->email ?? '-';

// Fetch level user name dynamically
$level_name = 'Administrator';
if ($user && method_exists($user, 'getIdUserLevel')) {
    $db = Yii::$app->db;
    $role = $db->createCommand("SELECT nama_level FROM public.level_user WHERE id = :id", [
        ':id' => $user->level_user_id
    ])->queryScalar();
    if ($role) {
        $level_name = $role;
    }
}
?>

<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center justify-content-between">
            <div class="col-sm-auto">
                <div class="page-header-title">
                    <h5 class="mb-0 font-weight-600 fw-bold">BERANDA UTAMA</h5>
                </div>
                <p class="text-muted">Selamat datang kembali di Sistem Informasi Manajemen Akun Puskesmas.</p>
            </div>
            <div class="col-sm-auto">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= Url::to(['beranda/index']) ?>"><i class="ph-duotone ph-house"></i></a></li>
                    <li class="breadcrumb-item" aria-current="page">BERANDA PUSKESMAS</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Selamat Datang & Profil Card -->
    <div class="col-lg-8 col-md-12">
        <div class="card">
            <div class="card-body">
                <h4 class="mb-2 fw-bold text-dark">Selamat Datang, <?= htmlspecialchars(strtoupper($user_fullname)) ?>!</h4>
                <p class="text-muted mb-4">
                    Anda masuk sebagai <strong><?= htmlspecialchars($level_name) ?></strong>. Kelola hak akses, persetujuan akun pendaftar baru, dan konfigurasi navigasi sistem melalui panel kendali ini.
                </p>
                <div class="d-flex gap-2">
                    <a class="btn btn-primary" href="<?= Url::to(['/user-registration/index']) ?>">
                        <i class="ph-duotone ph-user-plus me-1"></i> Registrasi Pending
                    </a>
                    <a class="btn btn-light-primary ms-2" href="<?= Url::to(['/user-model/index']) ?>">
                        <i class="ph-duotone ph-users me-1"></i> Data Pengguna
                    </a>
                </div>
            </div>
        </div>

        <!-- Profil Details Card -->
        <div class="card mt-4">
            <div class="card-header">
                <h5><i class="ph-duotone ph-user-circle me-1 text-primary"></i> Detail Akun Pengguna</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block">Username</small>
                        <span class="fw-semibold text-dark"><?= htmlspecialchars($user_name) ?></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block">Nama Lengkap</small>
                        <span class="fw-semibold text-dark"><?= htmlspecialchars($user_fullname) ?></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block">Email</small>
                        <span class="fw-semibold text-dark"><?= htmlspecialchars($user_email) ?></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block">Level Akses</small>
                        <span class="badge bg-light-primary text-primary fw-semibold"><?= htmlspecialchars($level_name) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik Ringkas & Quick Links -->
    <div class="col-lg-4 col-md-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="ph-duotone ph-chart-pie-slice me-1 text-primary"></i> Statistik Sistem</h5>
            </div>
            <div class="card-body">
                <!-- Stat Item 1 -->
                <div class="d-flex align-items-center mb-4">
                    <span class="badge bg-light-primary text-primary p-2 me-3">
                        <i class="ph-duotone ph-users-three" style="font-size: 1.5rem;"></i>
                    </span>
                    <div>
                        <h4 class="mb-0 fw-bold text-dark"><?= isset($stats['total_users']) ? $stats['total_users'] : 0 ?></h4>
                        <span class="text-muted small">Pengguna Aktif</span>
                    </div>
                </div>

                <!-- Stat Item 2 -->
                <div class="d-flex align-items-center">
                    <span class="badge bg-light-warning text-warning p-2 me-3">
                        <i class="ph-duotone ph-user-plus" style="font-size: 1.5rem;"></i>
                    </span>
                    <div>
                        <h4 class="mb-0 fw-bold text-dark"><?= isset($stats['total_pending_registrations']) ? $stats['total_pending_registrations'] : 0 ?></h4>
                        <span class="text-muted small">Persetujuan Akun Pending</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Links Panel -->
        <div class="card">
            <div class="card-header">
                <h5><i class="ph-duotone ph-gear me-1 text-primary"></i> Konfigurasi Cepat</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <a href="<?= Url::to(['/user-registration/index']) ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center border-0 px-4 py-3">
                        <span class="text-dark"><i class="ph-duotone ph-identification-card me-2 text-primary"></i> Persetujuan Akun</span>
                        <i class="ph-duotone ph-caret-right text-muted"></i>
                    </a>
                    <a href="<?= Url::to(['/level-user/index']) ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center border-0 px-4 py-3">
                        <span class="text-dark"><i class="ph-duotone ph-shield-check me-2 text-primary"></i> Atur Level / Peran</span>
                        <i class="ph-duotone ph-caret-right text-muted"></i>
                    </a>
                    <a href="<?= Url::to(['/navigasi/index']) ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center border-0 px-4 py-3">
                        <span class="text-dark"><i class="ph-duotone ph-navigation-arrow me-2 text-primary"></i> Konfigurasi Navigasi</span>
                        <i class="ph-duotone ph-caret-right text-muted"></i>
                    </a>
                    <a href="<?= Url::to(['/modul/index']) ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center border-0 px-4 py-3">
                        <span class="text-dark"><i class="ph-duotone ph-squares-four me-2 text-primary"></i> Pengaturan Modul</span>
                        <i class="ph-duotone ph-caret-right text-muted"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
