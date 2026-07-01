<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\PuskesmasKinerja */
/* @var $form yii\widgets\ActiveForm */
/* @var $puskesmas app\models\PuskesmasProfile */

$this->registerCss("
.tab-siskohat-nav {
  margin-bottom: 0 !important;
  border-bottom: 1px solid #C4C4C4FF;
  flex-wrap: nowrap;
  overflow-x: auto;
  overflow-y: hidden;
}

.tab-siskohat-nav .nav-item {
  flex: 0 0 auto;
}

.tab-siskohat-nav .tab-pertama.active {
  border-left: 6px solid #2AB2A8;
  padding-left: 10px;
  border-bottom-color: #fff;
}

.tab-siskohat-nav .tab-pertama:not(.active) {
  border-left: 1px solid transparent;
  padding-left: 14px;
}

.tab-siskohat-nav .nav-link {
  border: 1px solid transparent;
  border-top-left-radius: 10px;
  border-top-right-radius: 10px;
  color: #555;
  padding: 10px 14px;
  margin-right: 6px;
  background: #f7f7f7;
  white-space: nowrap;
}

.tab-siskohat-nav .nav-link.active {
  background: #fff;
  border-color: #C4C4C4FF #C4C4C4FF #fff;
  color: #111;
  font-weight: 600;
}

.tab-siskohat-nav .step-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 28px;
  height: 28px;
  padding: 0 8px;
  border-radius: 999px;
  background: #e9edf2;
  color: #1f2937;
  font-size: 12px;
  font-weight: 700;
}

.tab-siskohat-nav .nav-link.active .step-badge {
  background: #2AB2A8;
  color: #fff;
}

.tab-siskohat-card {
  border-radius: 0 0 10px 10px !important;
  border: 1px solid #C4C4C4FF !important;
  border-top: 0 !important;
  border-left: 6px solid #2AB2A8 !important;
  padding: 24px;
  background: #fff;
  box-shadow: 0 4px 6px rgba(0,0,0,0.05);
}

.detail-section-card {
  border: 1px solid #dfe7ea;
  border-top: 3px solid #7fd6d1;
  border-radius: 10px;
  overflow: hidden;
  background: #fff;
  margin-bottom: 20px;
}

.detail-section-header {
  padding: 14px 18px;
  border-bottom: 1px solid #eef2f4;
  background: #f8fafb;
}

.detail-section-title {
  margin: 0;
  font-size: 14px;
  font-weight: 700;
  color: #425466;
  text-transform: uppercase;
}

.detail-section-body {
  padding: 18px;
}

.text-teal {
  color: #2AB2A8 !important;
}

.btn-next-tab, .btn-prev-tab {
  border-radius: 20px;
}
");
?>

<div class="puskesmas-kinerja-form">

    <?php $form = ActiveForm::begin(); ?>

    <!-- RHA Custom Tab Navigation -->
    <ul class="nav nav-tabs tab-siskohat-nav" id="kinerjaTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active tab-pertama" id="periode-tab" data-bs-toggle="tab" data-bs-target="#pane-periode" type="button" role="tab">
                <span class="step-badge me-2">01</span> Periode & SDM
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="alkes-tab" data-bs-toggle="tab" data-bs-target="#pane-alkes" type="button" role="tab">
                <span class="step-badge me-2">02</span> Sarpras & Alkes
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="obat-tab" data-bs-toggle="tab" data-bs-target="#pane-obat" type="button" role="tab">
                <span class="step-badge me-2">03</span> Obat & Farmasi
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="blud-tab" data-bs-toggle="tab" data-bs-target="#pane-blud" type="button" role="tab">
                <span class="step-badge me-2">04</span> Keuangan & BLUD
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pkp-tab" data-bs-toggle="tab" data-bs-target="#pane-pkp" type="button" role="tab">
                <span class="step-badge me-2">05</span> Pelayanan & PKP
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="mutu-tab" data-bs-toggle="tab" data-bs-target="#pane-mutu" type="button" role="tab">
                <span class="step-badge me-2">06</span> Penjaminan Mutu
            </button>
        </li>
    </ul>

    <!-- Tab Contents Container -->
    <div class="tab-content tab-siskohat-card">
        
        <!-- Tab 1: Periode & SDM -->
        <div class="tab-pane fade show active" id="pane-periode" role="tabpanel">
            <div class="detail-section-card">
                <div class="detail-section-header">
                    <h6 class="detail-section-title">Informasi Periode Pelaporan</h6>
                </div>
                <div class="detail-section-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <?= $form->field($model, 'tahun')->textInput(['type' => 'number', 'min' => 2020, 'max' => 2035]) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'periode_tipe')->dropDownList([
                                'Tahunan' => 'Tahunan',
                                'Kuartal' => 'Kuartal',
                                'Bulanan' => 'Bulanan',
                            ], ['class' => 'form-select']) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'periode_nilai')->textInput(['type' => 'number', 'min' => 1])
                                ->hint('Isi 1-4 untuk Kuartal, atau 1-12 untuk Bulan, atau Tahun untuk Tahunan.') ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="detail-section-card">
                <div class="detail-section-header">
                    <h6 class="detail-section-title">Kesiapan & Ketenagaan SDM (DLI 6.1 & Permenkes 19/2024)</h6>
                </div>
                <div class="detail-section-body">
                    <div class="row">
                        <div class="col-md-4">
                            <?= $form->field($model, 'dokter_tersedia')->checkbox() ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'nakes_9_jenis')->checkbox() ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'nakes_11_jenis')->checkbox() ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end mt-3">
                <button type="button" class="btn btn-primary btn-next-tab" data-next="#pane-alkes">Simpan & Lanjutkan <i class="ti ti-arrow-right"></i></button>
            </div>
        </div>

        <!-- Tab 2: Sarpras & Alkes -->
        <div class="tab-pane fade" id="pane-alkes" role="tabpanel">
            <div class="detail-section-card">
                <div class="detail-section-header">
                    <h6 class="detail-section-title">Pemenuhan Sarana Prasarana & Alat Kesehatan (ASPAK)</h6>
                </div>
                <div class="detail-section-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <?= $form->field($model, 'persen_alkes')->textInput(['type' => 'number', 'step' => '0.1', 'min' => 0, 'max' => 100])
                                ->hint('Target minimal 60% pemenuhan Alkes.') ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'persen_spa')->textInput(['type' => 'number', 'step' => '0.1', 'min' => 0, 'max' => 100])
                                ->hint('Target minimal 60% pemenuhan SPA.') ?>
                        </div>
                        <div class="col-md-12">
                            <?= $form->field($model, 'prasarana_terpelihara')->checkbox() ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-3">
                <button type="button" class="btn btn-secondary btn-prev-tab" data-prev="#pane-periode"><i class="ti ti-arrow-left"></i> Sebelumnya</button>
                <button type="button" class="btn btn-primary btn-next-tab" data-next="#pane-obat">Simpan & Lanjutkan <i class="ti ti-arrow-right"></i></button>
            </div>
        </div>

        <!-- Tab 3: Obat & Farmasi -->
        <div class="tab-pane fade" id="pane-obat" role="tabpanel">
            <div class="detail-section-card">
                <div class="detail-section-header">
                    <h6 class="detail-section-title">Ketersediaan Obat & Bahan Medis Habis Pakai (SMILE)</h6>
                </div>
                <div class="detail-section-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <?= $form->field($model, 'jumlah_obat_esensial')->textInput(['type' => 'number', 'min' => 0])
                                ->hint('Target minimal 40 jenis obat esensial.') ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'persen_obat_esensial')->textInput(['type' => 'number', 'step' => '0.1', 'min' => 0, 'max' => 100]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'bmhp_tersedia')->checkbox() ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'farmasi_bmhp_terkendali')->checkbox() ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-3">
                <button type="button" class="btn btn-secondary btn-prev-tab" data-prev="#pane-alkes"><i class="ti ti-arrow-left"></i> Sebelumnya</button>
                <button type="button" class="btn btn-primary btn-next-tab" data-next="#pane-blud">Simpan & Lanjutkan <i class="ti ti-arrow-right"></i></button>
            </div>
        </div>

        <!-- Tab 4: Keuangan & BLUD -->
        <div class="tab-pane fade" id="pane-blud" role="tabpanel">
            <div class="detail-section-card">
                <div class="detail-section-header">
                    <h6 class="detail-section-title">Status Penerapan BLUD & Tata Kelola</h6>
                </div>
                <div class="detail-section-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <?= $form->field($model, 'status_blud')->checkbox() ?>
                            <?= $form->field($model, 'sk_blud_tersedia')->checkbox() ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'tata_kelola_remunerasi')->checkbox() ?>
                            <?= $form->field($model, 'tata_kelola_barang_jasa')->checkbox() ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="detail-section-card">
                <div class="detail-section-header">
                    <h6 class="detail-section-title">Sumber Pendanaan Anggaran (Pendapatan/Belanja BLUD)</h6>
                </div>
                <div class="detail-section-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <?= $form->field($model, 'alokasi_bok')->textInput(['type' => 'number', 'step' => '0.01'])->label('Alokasi BOK') ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'realisasi_bok')->textInput(['type' => 'number', 'step' => '0.01'])->label('Realisasi BOK') ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'realisasi_insentif_ukm')->textInput(['type' => 'number', 'step' => '0.01'])->label('Realisasi Insentif UKM') ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'realisasi_insentif_fktp')->textInput(['type' => 'number', 'step' => '0.01'])->label('Realisasi Insentif FKTP') ?>
                        </div>
                    </div>
                    <hr>
                    <h6 class="fw-bold text-teal mb-3">Rincian Sumber Penerimaan Pendapatan (Rp)</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <?= $form->field($model, 'sumber_apbd_dau')->textInput(['type' => 'number', 'step' => '0.01'])->label('APBD: DAU') ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'sumber_apbd_bok')->textInput(['type' => 'number', 'step' => '0.01'])->label('APBD: BOK') ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'sumber_kapitasi')->textInput(['type' => 'number', 'step' => '0.01'])->label('Jasa: Kapitasi') ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'sumber_tarif')->textInput(['type' => 'number', 'step' => '0.01'])->label('Jasa: Tarif Non-Kapitasi') ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'sumber_hibah')->textInput(['type' => 'number', 'step' => '0.01'])->label('Dana Hibah') ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'sumber_kerjasama')->textInput(['type' => 'number', 'step' => '0.01'])->label('Kerja Sama') ?>
                        </div>
                        <div class="col-md-12">
                            <?= $form->field($model, 'sumber_lainnya')->textInput(['type' => 'number', 'step' => '0.01'])->label('Pendapatan Lain-lain Sah') ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-3">
                <button type="button" class="btn btn-secondary btn-prev-tab" data-prev="#pane-obat"><i class="ti ti-arrow-left"></i> Sebelumnya</button>
                <button type="button" class="btn btn-primary btn-next-tab" data-next="#pane-pkp">Simpan & Lanjutkan <i class="ti ti-arrow-right"></i></button>
            </div>
        </div>

        <!-- Tab 5: Pelayanan & PKP -->
        <div class="tab-pane fade" id="pane-pkp" role="tabpanel">
            <div class="detail-section-card">
                <div class="detail-section-header">
                    <h6 class="detail-section-title">Integrasi Layanan Primer (ILP) & Pustu Aktif</h6>
                </div>
                <div class="detail-section-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <?= $form->field($model, 'status_ilp')->checkbox() ?>
                            <?= $form->field($model, 'sk_ilp_tersedia')->checkbox() ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'jumlah_pustu_aktif')->textInput(['type' => 'number', 'min' => 0])
                                ->hint('Pustu aktif minimal memiliki 2 nakes & 2 kader.') ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="detail-section-card">
                <div class="detail-section-header">
                    <h6 class="detail-section-title">Skor Penilaian Kinerja Puskesmas (PKP) per Klaster</h6>
                </div>
                <div class="detail-section-body">
                    <p class="text-muted small">Input nilai evaluasi kinerja dalam skala 0 - 100. Target rata-rata total PKP Baik adalah &ge; 80%.</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <?= $form->field($model, 'skor_pkp_klaster1')->textInput(['type' => 'number', 'step' => '0.1', 'min' => 0, 'max' => 100])->label('Klaster 1 (Manajemen)') ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'skor_pkp_klaster2')->textInput(['type' => 'number', 'step' => '0.1', 'min' => 0, 'max' => 100])->label('Klaster 2 (Ibu & Anak)') ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'skor_pkp_klaster3')->textInput(['type' => 'number', 'step' => '0.1', 'min' => 0, 'max' => 100])->label('Klaster 3 (Dewasa & Lansia)') ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'skor_pkp_klaster4')->textInput(['type' => 'number', 'step' => '0.1', 'min' => 0, 'max' => 100])->label('Klaster 4 (Surveilans & Kesling)') ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'skor_pkp_lintas_klaster')->textInput(['type' => 'number', 'step' => '0.1', 'min' => 0, 'max' => 100])->label('Lintas Klaster') ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'skor_pkp_total')->textInput(['type' => 'number', 'step' => '0.1', 'min' => 0, 'max' => 100])->label('Rata-rata Total PKP') ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-3">
                <button type="button" class="btn btn-secondary btn-prev-tab" data-prev="#pane-blud"><i class="ti ti-arrow-left"></i> Sebelumnya</button>
                <button type="button" class="btn btn-primary btn-next-tab" data-next="#pane-mutu">Simpan & Lanjutkan <i class="ti ti-arrow-right"></i></button>
            </div>
        </div>

        <!-- Tab 6: Penjaminan Mutu -->
        <div class="tab-pane fade" id="pane-mutu" role="tabpanel">
            <div class="detail-section-card">
                <div class="detail-section-header">
                    <h6 class="detail-section-title">Peningkatan Mutu Internal Puskesmas</h6>
                </div>
                <div class="detail-section-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <?= $form->field($model, 'indikator_mutu_dilaporkan')->checkbox() ?>
                        </div>
                        <div class="col-md-12">
                            <?= $form->field($model, 'insiden_keselamatan_dilaporkan')->checkbox() ?>
                        </div>
                        <div class="col-md-12">
                            <?= $form->field($model, 'manajemen_risiko_diterapkan')->checkbox() ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <button type="button" class="btn btn-secondary btn-prev-tab" data-prev="#pane-pkp"><i class="ti ti-arrow-left"></i> Sebelumnya</button>
                <div>
                    <?= Html::a('Batal', ['kinerja', 'id' => $puskesmas->id], ['class' => 'btn btn-outline-secondary me-2']) ?>
                    <?= Html::submitButton('Simpan Laporan Lengkap <i class="ti ti-circle-check"></i>', ['class' => 'btn btn-success']) ?>
                </div>
            </div>
        </div>

    </div>

    <?php ActiveForm::end(); ?>

</div>

<!-- JavaScript to control tab switching -->
<?php
$js = <<<JS
document.addEventListener('DOMContentLoaded', function() {
    // Next tab triggers
    document.querySelectorAll('.btn-next-tab').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var target = this.getAttribute('data-next');
            var nextTabBtn = document.querySelector('button[data-bs-target="' + target + '"]');
            if (nextTabBtn) {
                bootstrap.Tab.getOrCreateInstance(nextTabBtn).show();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    });

    // Previous tab triggers
    document.querySelectorAll('.btn-prev-tab').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var target = this.getAttribute('data-prev');
            var prevTabBtn = document.querySelector('button[data-bs-target="' + target + '"]');
            if (prevTabBtn) {
                bootstrap.Tab.getOrCreateInstance(prevTabBtn).show();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    });
});
JS;
$this->registerJs($js);
?>
