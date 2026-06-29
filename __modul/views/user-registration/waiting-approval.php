<?php

use yii\helpers\Html;

$this->title = 'Menunggu Persetujuan - SIPKK';
?>

<div class="user-registration-waiting-approval">
    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5 text-center">
                        <div class="mb-4">
                            <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>

                        <h2 class="mb-3 text-primary">Menunggu Persetujuan</h2>
                        <p class="text-muted mb-4">
                            Email Anda berhasil terverifikasi. Pengajuan akses Anda sedang diproses oleh admin pusat.
                        </p>

                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle"></i>
                            <strong>Status Pendaftaran:</strong><br>
                            Menunggu Persetujuan Admin Pusat
                        </div>

                        <!-- Informasi Verifikasi -->
                        <div class="card bg-light border-0 mb-4">
                            <div class="card-body">
                                <p class="text-muted small mb-2">
                                    <strong>Email Terverifikasi:</strong><br>
                                    <?= htmlspecialchars($registration->email, ENT_QUOTES, 'UTF-8') ?>
                                </p>
                                <p class="text-muted small mb-0">
                                    <strong>Tanggal Verifikasi:</strong><br>
                                    <?= Yii::$app->formatter->asDatetime($registration->email_verified_at, 'php:d M Y H:i') ?>
                                </p>
                            </div>
                        </div>

                        <!-- Informasi Proses -->
                        <div class="steps mb-4">
                            <div class="step active">
                                <div class="step-number bg-success text-white">
                                    <i class="fas fa-check"></i>
                                </div>
                                <p class="step-label">Pendaftaran Selesai</p>
                            </div>

                            <div class="step-divider"></div>

                            <div class="step active">
                                <div class="step-number bg-success text-white">
                                    <i class="fas fa-check"></i>
                                </div>
                                <p class="step-label">Email Terverifikasi</p>
                            </div>

                            <div class="step-divider"></div>

                            <div class="step">
                                <div class="step-number bg-primary text-white">
                                    <i class="fas fa-hourglass-half"></i>
                                </div>
                                <p class="step-label">Menunggu Approval</p>
                            </div>

                            <div class="step-divider"></div>

                            <div class="step">
                                <div class="step-number bg-secondary text-white">4</div>
                                <p class="step-label">Akun Aktif</p>
                            </div>
                        </div>

                        <!-- Timeline -->
                        <div class="timeline mb-4">
                            <div class="timeline-item">
                                <div class="timeline-date">
                                    <?= Yii::$app->formatter->asDatetime($registration->created_at, 'php:d M Y') ?>
                                </div>
                                <div class="timeline-content">
                                    <strong>Pendaftaran Dibuat</strong>
                                    <p class="text-muted small mb-0">Data Anda berhasil disimpan</p>
                                </div>
                            </div>

                            <div class="timeline-item">
                                <div class="timeline-date">
                                    <?= Yii::$app->formatter->asDatetime($registration->email_verified_at, 'php:d M Y') ?>
                                </div>
                                <div class="timeline-content">
                                    <strong>Email Terverifikasi</strong>
                                    <p class="text-muted small mb-0">Email Anda berhasil diverifikasi</p>
                                </div>
                            </div>

                            <div class="timeline-item">
                                <div class="timeline-date">
                                    <i class="fas fa-hourglass-half text-primary"></i>
                                </div>
                                <div class="timeline-content">
                                    <strong>Sedang Diproses</strong>
                                    <p class="text-muted small mb-0">Admin pusat melakukan review data Anda</p>
                                </div>
                            </div>
                        </div>

                        <!-- Info Box -->
                        <div class="alert alert-light border text-start mb-4">
                            <h6 class="mb-3">
                                <i class="fas fa-clock"></i> Berapa lama proses persetujuan?
                            </h6>
                            <p class="mb-2">
                                Proses persetujuan biasanya memakan waktu <strong>1-3 hari kerja</strong>. 
                                Kami akan mengirimkan email kepada Anda segera setelah ada keputusan.
                            </p>
                        </div>

                        <!-- Informasi Kontak -->
                        <div class="alert alert-warning text-start">
                            <strong>Pertanyaan atau Bantuan?</strong>
                            <p class="mb-1">Hubungi admin SIPKK:</p>
                            <p class="mb-0">
                                <i class="fas fa-envelope"></i> 
                                <a href="mailto:admin@sipkk.kemkes.go.id">admin@sipkk.kemkes.go.id</a>
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2">
                            <?= Html::a('Kembali ke Beranda', ['/site/index'], [
                                'class' => 'btn btn-outline-primary',
                            ]) ?>
                            <?= Html::a('Cek Status Lagi', ['waiting-approval', 'registration_id' => $registration->id], [
                                'class' => 'btn btn-primary mt-2',
                            ]) ?>
                        </div>

                        <p class="text-muted small mt-4 mb-0">
                            ID Pendaftaran: <code><?= $registration->id ?></code>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Auto-refresh halaman setiap 5 menit untuk cek status terbaru
$this->registerJs(<<<JS
    // Auto-refresh setiap 5 menit
    setInterval(function() {
        location.reload();
    }, 5 * 60 * 1000);
JS
, \yii\web\View::POS_END);
?>

<style>
    .card {
        border-radius: 12px;
    }
    
    .text-primary {
        color: #0f766e !important;
    }
    
    .btn-primary {
        background-color: #0f766e;
        border-color: #0f766e;
    }
    
    .btn-primary:hover {
        background-color: #0d5f59;
        border-color: #0d5f59;
    }
    
    /* Steps styling */
    .steps {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0;
        margin: 20px 0;
    }
    
    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: 1;
    }
    
    .step-number {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-bottom: 8px;
        background-color: #e5e7eb;
        color: #6b7280;
    }
    
    .step.active .step-number {
        background-color: #0f766e;
        color: white;
    }
    
    .step-label {
        font-size: 12px;
        text-align: center;
        margin: 0;
        max-width: 60px;
    }
    
    .step-divider {
        flex: 0 0 20px;
        height: 2px;
        background-color: #e5e7eb;
        margin-bottom: 40px;
    }
    
    /* Timeline styling */
    .timeline {
        text-align: left;
        padding: 20px 0;
    }
    
    .timeline-item {
        display: flex;
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .timeline-item:last-child {
        border-bottom: none;
    }
    
    .timeline-date {
        flex-shrink: 0;
        width: 80px;
        font-size: 12px;
        font-weight: bold;
        color: #0f766e;
    }
    
    .timeline-content {
        flex: 1;
        margin-left: 20px;
    }
    
    .timeline-content strong {
        display: block;
        margin-bottom: 4px;
    }
    
    /* Responsive */
    @media (max-width: 576px) {
        .steps {
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .step-divider {
            flex: 0 0 100%;
            height: 20px;
            margin: 0;
            background: transparent;
        }
    }
</style>
