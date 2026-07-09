<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\SystemSettingHelper;

$this->title = 'Konfigurasi Tampilan Sistem';
$this->params['active_menu'] = 'system-setting';

// Register Summernote Lite rich text editor stylesheet and script
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css');
$this->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);

$this->registerCss(<<<CSS
.note-editor {
  border-radius: 8px !important;
  border-color: #dee2e6 !important;
}
.note-toolbar {
  border-top-left-radius: 8px !important;
  border-top-right-radius: 8px !important;
  background-color: #f8f9fa !important;
}
CSS
);

$this->registerJs(<<<JS
$('.wysiwyg-editor').summernote({
    placeholder: 'Tulis konten HTML disini...',
    height: 250,
    tabsize: 2,
    toolbar: [
        ['style', ['style']],
        ['font', ['bold', 'underline', 'clear']],
        ['color', ['color']],
        ['para', ['ul', 'ol', 'paragraph']],
        ['table', ['table']],
        ['insert', ['link']],
        ['view', ['fullscreen', 'codeview', 'help']]
    ]
});

$('#emailTemplateSelector').on('change', function() {
    var val = $(this).val();
    $('.email-greeting-group').addClass('d-none');
    $('.email-greeting-group[data-template="' + val + '"]').removeClass('d-none');

    $('.email-color-group').addClass('d-none');
    $('.email-color-group[data-template="' + val + '"]').removeClass('d-none');
});

$('#dashboardLevelSelector').on('change', function() {
    var val = $(this).val();
    $('.dashboard-level-group').addClass('d-none');
    $('.dashboard-level-group[data-level="' + val + '"]').removeClass('d-none');
});

$('.email-color-group').each(function() {
    var container = $(this);
    var colorPicker = container.find('input[type="color"]');
    var hexInput    = container.find('input.email-color-hex');
    
    if (colorPicker.length && hexInput.length) {
        var pickerEl = colorPicker[0];
        var hexEl    = hexInput[0];

        pickerEl.addEventListener('input', function() {
            hexEl.value = this.value;
        });

        hexEl.addEventListener('input', function() {
            var val = this.value.trim();
            if (/^#[0-9a-fA-F]{6}$/.test(val)) {
                pickerEl.value = val;
                pickerEl.dispatchEvent(new Event('change'));
            }
        });

        hexEl.addEventListener('change', function() {
            var val = this.value.trim();
            if (/^#[0-9a-fA-F]{6}$/.test(val)) {
                pickerEl.value = val;
            }
        });
    }
});
JS
);
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center justify-content-between">
      <div class="col-sm-auto">
        <div class="page-header-title">
          <h5 class="mb-0 font-weight-600 fw-bold">KONFIGURASI TAMPILAN SISTEM</h5>
          <p>Sesuaikan logo, background, dan teks utama sistem secara real-time</p>
        </div>
      </div>
      <div class="col-sm-auto">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= Url::to(['/site/index']) ?>"><i class="ph-duotone ph-house"></i></a></li>
          <li class="breadcrumb-item">Konfigurasi Tampilan</li>
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

<?= Html::beginForm('', 'post', ['enctype' => 'multipart/form-data']) ?>

<div class="row">
  <div class="col-md-12">
    <div class="card">
      
      <!-- Card Tab Headers -->
      <div class="card-header p-0">
        <ul class="nav nav-tabs" id="systemSettingTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active py-3 px-4" id="login-tab" data-bs-toggle="tab" data-bs-target="#tab-login" type="button" role="tab" aria-controls="tab-login" aria-selected="true">
              <i class="ph-duotone ph-sign-in me-2 align-middle fs-5"></i> Login Admin
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link py-3 px-4" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#tab-dashboard" type="button" role="tab" aria-controls="tab-dashboard" aria-selected="false">
              <i class="ph-duotone ph-desktop me-2 align-middle fs-5"></i> Dashboard Admin
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link py-3 px-4" id="frontend-tab" data-bs-toggle="tab" data-bs-target="#tab-frontend" type="button" role="tab" aria-controls="tab-frontend" aria-selected="false">
              <i class="ph-duotone ph-browser me-2 align-middle fs-5"></i> Aplikasi Utama
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link py-3 px-4" id="email-tab" data-bs-toggle="tab" data-bs-target="#tab-email" type="button" role="tab" aria-controls="tab-email" aria-selected="false">
              <i class="ph-duotone ph-envelope me-2 align-middle fs-5"></i> Email Notifikasi
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link py-3 px-4" id="dashboard-setting-tab" data-bs-toggle="tab" data-bs-target="#tab-dashboard-setting" type="button" role="tab" aria-controls="tab-dashboard-setting" aria-selected="false">
              <i class="ph-duotone ph-layout me-2 align-middle fs-5"></i> Pengaturan Beranda
            </button>
          </li>
        </ul>
      </div>

      <!-- Card Tab Content -->
      <div class="card-body">
        <div class="tab-content" id="systemSettingTabContent">
          
          <!-- Tab 1: Login Admin -->
          <div class="tab-pane fade show active" id="tab-login" role="tabpanel" aria-labelledby="login-tab">
            <h5 class="mb-4 fw-bold text-dark border-bottom pb-2">Pengaturan Halaman Login Admin</h5>
            
            <!-- Login Title -->
            <div class="form-group mb-4">
              <label class="form-label font-weight-bold">Judul Form Login</label>
              <input type="text" name="Setting[login_title]" class="form-control" 
                     value="<?= Html::encode(SystemSettingHelper::get('login_title', 'AKSES SISTEM')) ?>" required>
              <small class="form-text text-muted">Judul utama form login yang ditampilkan di atas kolom input username dan password.</small>
            </div>
            
            <!-- Login Background -->
            <div class="form-group mb-4">
              <label class="form-label font-weight-bold">Background Halaman Login</label>
              <div class="mb-3">
                <div class="p-2 border rounded bg-light text-center">
                  <img src="<?= SystemSettingHelper::getAssetUrl('login_background', '/app_asset/images/background-sipkk.png') ?>" 
                       class="img-fluid rounded" style="max-height: 150px; object-fit: contain;" alt="Login BG Preview">
                </div>
              </div>
              <input type="file" name="SettingFile[login_background]" class="form-control mb-2" accept="image/*">
              <div class="d-flex flex-wrap gap-2 align-items-center mb-1">
                <span class="badge bg-light-primary text-primary">Rekomendasi Ukuran: 1920 x 1080 px</span>
                <span class="badge bg-light-secondary text-secondary">Rasio: 16:9 (Landscape)</span>
                <span class="badge bg-light-success text-success">Format: JPG, PNG, WebP</span>
              </div>
              <small class="form-text text-muted d-block">Background ini akan memenuhi layar (cover) pada halaman login admin.</small>
            </div>

            <!-- Login Logo -->
            <div class="form-group mb-4">
              <label class="form-label font-weight-bold">Logo Halaman Login</label>
              <div class="mb-3">
                <div class="p-3 border rounded bg-light text-center">
                  <img src="<?= SystemSettingHelper::getAssetUrl('login_logo', '/app_asset/images/logo-kemenkes-warna.png') ?>" 
                       style="max-height: 70px; max-width: 100%; object-fit: contain;" alt="Login Logo Preview">
                </div>
              </div>
              <input type="file" name="SettingFile[login_logo]" class="form-control mb-2" accept="image/*">
              <div class="d-flex flex-wrap gap-2 align-items-center mb-1">
                <span class="badge bg-light-primary text-primary">Rekomendasi Lebar: Maksimal 300 px</span>
                <span class="badge bg-light-success text-success">Format: PNG Transparan</span>
              </div>
              <small class="form-text text-muted d-block">Logo kementerian/instansi berwarna utama yang tampil di atas form login admin.</small>
            </div>
            
            <!-- Petunjuk Teknis Admin (WYSIWYG) -->
            <div class="form-group mb-4">
              <label class="form-label font-weight-bold">Petunjuk Teknis Penggunaan Admin</label>
              <textarea name="Setting[frontend_technical_guidelines]" class="form-control wysiwyg-editor" rows="6"><?= Html::encode(SystemSettingHelper::get('frontend_technical_guidelines')) ?></textarea>
              <small class="form-text text-muted">Petunjuk teknis masuk sistem yang akan ditampilkan dalam modal di halaman login admin.</small>
            </div>

            <!-- Link ke Dashboard Web -->
            <div class="form-group mb-0">
              <label class="form-label font-weight-bold">Link ke Dashboard Web</label>
              <input type="text" name="Setting[login_dashboard_link]" class="form-control" 
                     value="<?= Html::encode(SystemSettingHelper::get('login_dashboard_link', 'https://puskes-kappa.vercel.app/login')) ?>">
              <small class="form-text text-muted">Tautan teks link menuju dashboard Next.js (ditampilkan di samping Panduan Teknis Penggunaan pada halaman login admin).</small>
            </div>
          </div>

          <!-- Tab 2: Dashboard Admin -->
          <div class="tab-pane fade" id="tab-dashboard" role="tabpanel" aria-labelledby="dashboard-tab">
            <h5 class="mb-4 fw-bold text-dark border-bottom pb-2">Tampilan Dashboard Admin & Umum</h5>
            
            <!-- System Title -->
            <div class="form-group mb-4">
              <label class="form-label font-weight-bold">Nama Sistem (Singkatan)</label>
              <input type="text" name="Setting[system_title]" class="form-control" 
                     value="<?= Html::encode(SystemSettingHelper::get('system_title', 'SIPKK')) ?>" required>
              <small class="form-text text-muted">Nama singkatan sistem untuk label hak akses, notifikasi email, dan halaman registrasi.</small>
            </div>

            <!-- Footer Text -->
            <div class="form-group mb-4">
              <label class="form-label font-weight-bold">Teks Footer / Copyright</label>
              <input type="text" name="Setting[footer_text]" class="form-control" 
                     value="<?= Html::encode(SystemSettingHelper::get('footer_text', 'SIPKK 2026')) ?>" required>
              <small class="form-text text-muted">Copyright teks yang tampil di bawah form auth/login admin.</small>
            </div>

            <!-- Inner Logo -->
            <div class="form-group mb-0">
              <label class="form-label font-weight-bold">Logo Sidebar (Halaman Dalam)</label>
              <div class="mb-3">
                <div class="p-3 border rounded bg-dark text-center">
                  <img src="<?= SystemSettingHelper::getAssetUrl('inner_logo', '/app_asset/images/logo-haji.png') ?>" 
                       style="max-height: 50px; max-width: 100%; object-fit: contain; filter: drop-shadow(0 0 1px rgba(255,255,255,0.3));" alt="Inner Logo Preview">
                </div>
              </div>
              <input type="file" name="SettingFile[inner_logo]" class="form-control mb-2" accept="image/*">
              <div class="d-flex flex-wrap gap-2 align-items-center mb-1">
                <span class="badge bg-light-primary text-primary">Rekomendasi Lebar: Maksimal 240 px</span>
                <span class="badge bg-light-success text-success">Format: PNG Transparan</span>
              </div>
              <small class="form-text text-muted d-block">Logo instansi dengan kontras warna terang (putih/emas) untuk ditempatkan pada sidebar navigasi berlatar gelap.</small>
            </div>
          </div>

          <!-- Tab 3: Aplikasi Utama -->
          <div class="tab-pane fade" id="tab-frontend" role="tabpanel" aria-labelledby="frontend-tab">
            <h5 class="mb-4 fw-bold text-dark border-bottom pb-2">Pengaturan Aplikasi & Halaman Login Utama (Web)</h5>

            <!-- App Title -->
            <div class="form-group mb-4">
              <label class="form-label font-weight-bold">Judul Utama Halaman Login</label>
              <input type="text" name="Setting[frontend_app_title]" class="form-control" 
                     value="<?= Html::encode(SystemSettingHelper::get('frontend_app_title', 'Indikator Penilaian Kinerja Puskesmas')) ?>" required>
              <small class="form-text text-muted">Judul utama teks besar di halaman login (Sisi Kiri/Hero).</small>
            </div>

            <!-- App Subtitle -->
            <div class="form-group mb-4">
              <label class="form-label font-weight-bold">Deskripsi Halaman Login</label>
              <textarea name="Setting[frontend_app_subtitle]" class="form-control" rows="3" required><?= Html::encode(SystemSettingHelper::get('frontend_app_subtitle', 'Sistem pemantauan terpadu untuk melihat capaian, sebaran, dan perkembangan Puskesmas di seluruh wilayah Indonesia.')) ?></textarea>
              <small class="form-text text-muted">Paragraf penjelasan di bawah judul utama hero halaman login.</small>
            </div>

            <!-- Login Card Title -->
            <div class="form-group mb-4">
              <label class="form-label font-weight-bold">Judul Card Form Login</label>
              <input type="text" name="Setting[frontend_login_card_title]" class="form-control" 
                     value="<?= Html::encode(SystemSettingHelper::get('frontend_login_card_title', 'Asistensi Kinerja Puskesmas')) ?>" required>
              <small class="form-text text-muted">Judul di atas kolom input form login (Sisi Kanan).</small>
            </div>

            <!-- Login Card Subtitle -->
            <div class="form-group mb-4">
              <label class="form-label font-weight-bold">Sub-judul Card Form Login</label>
              <input type="text" name="Setting[frontend_login_card_subtitle]" class="form-control" 
                     value="<?= Html::encode(SystemSettingHelper::get('frontend_login_card_subtitle', 'Silakan masuk untuk mengakses data kinerja Puskesmas.')) ?>" required>
              <small class="form-text text-muted">Teks petunjuk kecil di bawah judul card form login.</small>
            </div>

            <!-- Security / Login Note -->
            <div class="form-group mb-4">
              <label class="form-label font-weight-bold">Catatan Keamanan / Petunjuk Login</label>
              <textarea name="Setting[frontend_login_note]" class="form-control" rows="2" required><?= Html::encode(SystemSettingHelper::get('frontend_login_note', 'Akses terbatas untuk pengguna yang berwenang. Hubungi admin jika mengalami kendala masuk.')) ?></textarea>
              <small class="form-text text-muted">Catatan keamanan yang tampil di bagian paling bawah form login.</small>
            </div>

            <!-- Footer Copyright Text -->
            <div class="form-group mb-4">
              <label class="form-label font-weight-bold">Teks Footer / Copyright (Dashboard)</label>
              <input type="text" name="Setting[frontend_footer_text]" class="form-control" 
                     value="<?= Html::encode(SystemSettingHelper::get('frontend_footer_text', '© 2026 Kementerian Kesehatan Republik Indonesia')) ?>" required>
              <small class="form-text text-muted">Hak cipta di bagian bawah halaman login.</small>
            </div>

            <!-- Logo (Dashboard) -->
            <div class="form-group mb-4">
              <label class="form-label font-weight-bold">Logo Instansi (Dashboard)</label>
              <div class="mb-3">
                <div class="p-3 border rounded bg-dark text-center">
                  <img src="<?= SystemSettingHelper::getAssetUrl('frontend_login_logo', '/Logo-Kemenkes.png') ?>" 
                       style="max-height: 50px; max-width: 100%; object-fit: contain;" alt="Frontend Logo Preview">
                </div>
              </div>
              <input type="file" name="SettingFile[frontend_login_logo]" class="form-control mb-2" accept="image/*">
              <div class="d-flex flex-wrap gap-2 align-items-center mb-1">
                <span class="badge bg-light-primary text-primary">Rekomendasi Lebar: Maksimal 300 px</span>
                <span class="badge bg-light-success text-success">Format: PNG Transparan</span>
              </div>
            </div>

            <!-- Background Login (Dashboard) -->
            <div class="form-group mb-4">
              <label class="form-label font-weight-bold">Background Halaman Login (Dashboard)</label>
              <div class="mb-3">
                <div class="p-2 border rounded bg-light text-center">
                  <img src="<?= SystemSettingHelper::getAssetUrl('frontend_login_background', '/pkk.png') ?>" 
                       class="img-fluid rounded" style="max-height: 150px; object-fit: contain;" alt="Frontend Login BG Preview">
                </div>
              </div>
              <input type="file" name="SettingFile[frontend_login_background]" class="form-control mb-2" accept="image/*">
              <div class="d-flex flex-wrap gap-2 align-items-center mb-1">
                <span class="badge bg-light-primary text-primary">Rekomendasi Ukuran: 1920 x 1080 px</span>
                <span class="badge bg-light-secondary text-secondary">Rasio: 16:9 (Landscape)</span>
                <span class="badge bg-light-success text-success">Format: JPG, PNG, WebP</span>
              </div>
            </div>

            <!-- Background Register (Dashboard) -->
            <div class="form-group mb-4">
              <label class="form-label font-weight-bold">Background Halaman Register (Dashboard)</label>
              <div class="mb-3">
                <div class="p-2 border rounded bg-light text-center">
                  <img src="<?= SystemSettingHelper::getAssetUrl('frontend_register_background', '/pkk.png') ?>" 
                       class="img-fluid rounded" style="max-height: 150px; object-fit: contain;" alt="Frontend Register BG Preview">
                </div>
              </div>
              <input type="file" name="SettingFile[frontend_register_background]" class="form-control mb-2" accept="image/*">
              <div class="d-flex flex-wrap gap-2 align-items-center mb-1">
                <span class="badge bg-light-primary text-primary">Rekomendasi Ukuran: 1920 x 1080 px</span>
                <span class="badge bg-light-secondary text-secondary">Rasio: 16:9 (Landscape)</span>
                <span class="badge bg-light-success text-success">Format: JPG, PNG, WebP</span>
              </div>
            </div>

            <!-- Terms & Conditions (WYSIWYG) -->
            <div class="form-group mb-0">
              <label class="form-label font-weight-bold">Syarat & Ketentuan Pengguna (Dashboard)</label>
              <textarea name="Setting[frontend_terms_conditions]" class="form-control wysiwyg-editor" rows="6"><?= Html::encode(SystemSettingHelper::get('frontend_terms_conditions')) ?></textarea>
              <small class="form-text text-muted">Syarat dan ketentuan pendaftaran akun yang akan ditampilkan saat pengguna mengklik Syarat & Ketentuan di halaman register.</small>
            </div>
          </div>

          <!-- Tab 4: Email Notifikasi -->
          <div class="tab-pane fade" id="tab-email" role="tabpanel" aria-labelledby="email-tab">
            <h5 class="mb-4 fw-bold text-dark border-bottom pb-2">Pengaturan Email Notifikasi</h5>
            
            <!-- Diagram email anatomy -->
            <div class="alert mb-4 p-0" style="border:1px dashed #94a3b8;border-radius:10px;overflow:hidden;background:#f8fafc;">
              <div style="background:#0f766e;color:#fff;font-size:11px;font-weight:bold;text-align:center;padding:7px;letter-spacing:1px;">1. HEADER — Logo Email</div>
              <div style="background:#fff;padding:12px 18px;font-size:12px;color:#475569;border-bottom:1px solid #e2e8f0;">
                <span style="color:#0f766e;font-weight:bold;">2. Sapaan:</span> "Yth. / Kepada Yth." + NAMA USER,<br>
                <span style="color:#0f766e;font-weight:bold;">Isi konten email</span> (otomatis dari sistem)<br>
                <span style="color:#64748b;font-size:11px;font-style:italic;">Dikirim oleh, <strong>3. Label Pengirim</strong></span>
              </div>
              <div style="background:#0f766e;color:#fff;font-size:11px;padding:7px 14px;display:flex;justify-content:space-between;">
                <span><strong>4. Teks Organisasi Footer</strong></span>
                <span><strong>5. Label Link → </strong></span>
              </div>
            </div>

            <!-- Selector Tipe Email -->
            <div class="form-group mb-4">
              <label class="form-label font-weight-bold text-primary">Pilih Tipe Email yang Ingin Dikonfigurasi:</label>
              <select id="emailTemplateSelector" class="form-select border-primary font-weight-bold" style="max-width: 450px;">
                <option value="all">Semua Tipe Email (Pengaturan Umum)</option>
                <option value="otp">1. Kode OTP Verifikasi Email</option>
                <option value="approved">2. Pendaftaran Akun Disetujui</option>
                <option value="rejected">3. Pendaftaran Akun Ditolak</option>
                <option value="created">4. Akun Dibuat oleh Admin</option>
              </select>
              <small class="form-text text-muted">Secara default, semua email menggunakan pengaturan umum. Pilih tipe khusus jika ingin membedakan sapaan/warna aksen per tipe email.</small>
            </div>

            <div class="row g-3">
              <!-- 1. Logo Email -->
              <div class="col-12">
                <div class="card border shadow-none mb-0">
                  <div class="card-body">
                    <label class="form-label font-weight-bold mb-2">1. Logo Email Notifikasi</label>
                    <div class="row g-3 align-items-center">
                      <div class="col-auto">
                        <div class="p-2 border rounded bg-white text-center" style="min-width:100px;">
                          <img src="<?= SystemSettingHelper::getAssetUrl('email_logo', SystemSettingHelper::getAssetUrl('login_logo', '/app_asset/images/logo-kemenkes-warna.png')) ?>"
                               style="max-height:50px;max-width:120px;object-fit:contain;" alt="Email Logo Preview">
                        </div>
                      </div>
                      <div class="col">
                        <input type="file" name="SettingFile[email_logo]" class="form-control" accept="image/*">
                        <small class="text-muted d-block mt-1">Muncul di bagian atas semua email. Fallback ke Logo Login jika kosong.</small>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- 2. Sapaan -->
              <div class="col-md-6">
                <div class="card border shadow-none h-100 mb-0">
                  <div class="card-body">
                    <label class="form-label font-weight-bold">2. Sapaan Pembuka</label>
                    <div class="email-greeting-group" data-template="all">
                      <input type="text" name="Setting[email_greeting_prefix]" class="form-control"
                             value="<?= Html::encode(SystemSettingHelper::get('email_greeting_prefix', 'Yth.')) ?>" placeholder="Contoh: Yth. / Kepada Yth.">
                      <small class="text-muted">Sapaan umum untuk semua email. Contoh: <em>"Yth. BUDI SANTOSO"</em></small>
                    </div>
                    <div class="email-greeting-group d-none" data-template="otp">
                      <input type="text" name="Setting[email_otp_greeting]" class="form-control"
                             value="<?= Html::encode(SystemSettingHelper::get('email_otp_greeting', 'Yth.')) ?>" placeholder="Contoh: Yth.">
                      <small class="text-muted">Khusus email Kode OTP Verifikasi.</small>
                    </div>
                    <div class="email-greeting-group d-none" data-template="approved">
                      <input type="text" name="Setting[email_approved_greeting]" class="form-control"
                             value="<?= Html::encode(SystemSettingHelper::get('email_approved_greeting', 'Yth.')) ?>" placeholder="Contoh: Yth.">
                      <small class="text-muted">Khusus email Akun Disetujui.</small>
                    </div>
                    <div class="email-greeting-group d-none" data-template="rejected">
                      <input type="text" name="Setting[email_rejected_greeting]" class="form-control"
                             value="<?= Html::encode(SystemSettingHelper::get('email_rejected_greeting', 'Yth.')) ?>" placeholder="Contoh: Yth.">
                      <small class="text-muted">Khusus email Akun Ditolak.</small>
                    </div>
                    <div class="email-greeting-group d-none" data-template="created">
                      <input type="text" name="Setting[email_created_greeting]" class="form-control"
                             value="<?= Html::encode(SystemSettingHelper::get('email_created_greeting', 'Yth.')) ?>" placeholder="Contoh: Yth.">
                      <small class="text-muted">Khusus email Akun Dibuat Admin.</small>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Nama Sistem -->
              <div class="col-md-6">
                <div class="card border shadow-none h-100 mb-0">
                  <div class="card-body">
                    <label class="form-label font-weight-bold">Nama Sistem (dalam email)</label>
                    <input type="text" name="Setting[email_system_name]" class="form-control"
                           value="<?= Html::encode(SystemSettingHelper::get('email_system_name', 'Asistensi Kinerja Puskesmas')) ?>"
                           placeholder="Contoh: Asistensi Kinerja Puskesmas">
                    <small class="text-muted">Nama aplikasi yang muncul di isi email. Contoh: <em>"...pada aplikasi <strong>Asistensi Kinerja Puskesmas</strong>..."</em></small>
                  </div>
                </div>
              </div>

              <!-- 3. Label Pengirim -->
              <div class="col-12">
                <div class="card border shadow-none mb-0">
                  <div class="card-body">
                    <label class="form-label font-weight-bold">3. Label Pengirim (tanda tangan bawah isi email)</label>
                    <input type="text" name="Setting[email_sender_label]" class="form-control"
                           value="<?= Html::encode(SystemSettingHelper::get('email_sender_label', 'Asistensi Kinerja Puskesmas (KEMKES RI)')) ?>"
                           placeholder="Contoh: Puskesmas Kota Bandung (Dinkes Jabar)">
                    <small class="text-muted">Teks yang muncul di bagian <em>"Dikirim oleh,"</em> di bawah konten email.</small>
                  </div>
                </div>
              </div>

              <!-- 4. Teks Organisasi -->
              <div class="col-md-4">
                <div class="card border shadow-none h-100 mb-0">
                  <div class="card-body">
                    <label class="form-label font-weight-bold">4. Teks Organisasi Footer</label>
                    <input type="text" name="Setting[email_footer_org]" class="form-control"
                           value="<?= Html::encode(SystemSettingHelper::get('email_footer_org', 'Kementerian Kesehatan Republik Indonesia')) ?>"
                           placeholder="Contoh: Dinas Kesehatan Kota Bandung">
                    <small class="text-muted">Nama instansi di kiri footer email.</small>
                  </div>
                </div>
              </div>

              <!-- 5. Label Link -->
              <div class="col-md-3">
                <div class="card border shadow-none h-100 mb-0">
                  <div class="card-body">
                    <label class="form-label font-weight-bold">5. Label Link Footer</label>
                    <input type="text" name="Setting[email_footer_link_label]" class="form-control"
                           value="<?= Html::encode(SystemSettingHelper::get('email_footer_link_label', 'Kunjungi Website')) ?>"
                           placeholder="Contoh: Buka Aplikasi">
                    <small class="text-muted">Teks tombol link di kanan footer.</small>
                  </div>
                </div>
              </div>

              <!-- URL Link -->
              <div class="col-md-3">
                <div class="card border shadow-none h-100 mb-0">
                  <div class="card-body">
                    <label class="form-label font-weight-bold">URL Link Footer</label>
                    <input type="text" name="Setting[email_footer_link_url]" class="form-control"
                           value="<?= Html::encode(SystemSettingHelper::get('email_footer_link_url')) ?>"
                           placeholder="Contoh: https://puskesmas.go.id">
                    <small class="text-muted">Target link tombol footer. Kosongkan untuk default URL web.</small>
                  </div>
                </div>
              </div>

              <!-- Warna Aksen -->
              <div class="col-md-2">
                <div class="card border shadow-none h-100 mb-0">
                  <div class="card-body">
                    <label class="form-label font-weight-bold">Warna Aksen</label>
                    <div class="email-color-group" data-template="all">
                      <?php $emailColor = SystemSettingHelper::get('email_header_color', '#0f766e'); ?>
                      <div class="d-flex align-items-center gap-1">
                        <input type="color" name="Setting[email_header_color]" class="form-control form-control-color flex-shrink-0"
                               value="<?= Html::encode($emailColor) ?>" style="width:40px;height:38px;padding:2px;">
                        <input type="text" class="form-control px-2 email-color-hex" value="<?= Html::encode($emailColor) ?>" placeholder="#0f766e" pattern="^#[0-9a-fA-F]{6}$" style="font-size:12px;">
                      </div>
                      <small class="text-muted">Warna umum.</small>
                    </div>

                    <div class="email-color-group d-none" data-template="otp">
                      <?php $otpColor = SystemSettingHelper::get('email_otp_color', '#0284c7'); ?>
                      <div class="d-flex align-items-center gap-1">
                        <input type="color" name="Setting[email_otp_color]" class="form-control form-control-color flex-shrink-0"
                               value="<?= Html::encode($otpColor) ?>" style="width:40px;height:38px;padding:2px;">
                        <input type="text" class="form-control px-2 email-color-hex" value="<?= Html::encode($otpColor) ?>" placeholder="#0284c7" pattern="^#[0-9a-fA-F]{6}$" style="font-size:12px;">
                      </div>
                      <small class="text-muted">Warna OTP.</small>
                    </div>

                    <div class="email-color-group d-none" data-template="approved">
                      <?php $appColor = SystemSettingHelper::get('email_approved_color', '#0f766e'); ?>
                      <div class="d-flex align-items-center gap-1">
                        <input type="color" name="Setting[email_approved_color]" class="form-control form-control-color flex-shrink-0"
                               value="<?= Html::encode($appColor) ?>" style="width:40px;height:38px;padding:2px;">
                        <input type="text" class="form-control px-2 email-color-hex" value="<?= Html::encode($appColor) ?>" placeholder="#0f766e" pattern="^#[0-9a-fA-F]{6}$" style="font-size:12px;">
                      </div>
                      <small class="text-muted">Warna disetujui.</small>
                    </div>

                    <div class="email-color-group d-none" data-template="rejected">
                      <?php $rejColor = SystemSettingHelper::get('email_rejected_color', '#e11d48'); ?>
                      <div class="d-flex align-items-center gap-1">
                        <input type="color" name="Setting[email_rejected_color]" class="form-control form-control-color flex-shrink-0"
                               value="<?= Html::encode($rejColor) ?>" style="width:40px;height:38px;padding:2px;">
                        <input type="text" class="form-control px-2 email-color-hex" value="<?= Html::encode($rejColor) ?>" placeholder="#e11d48" pattern="^#[0-9a-fA-F]{6}$" style="font-size:12px;">
                      </div>
                      <small class="text-muted">Warna ditolak.</small>
                    </div>

                    <div class="email-color-group d-none" data-template="created">
                      <?php $creColor = SystemSettingHelper::get('email_created_color', '#0f766e'); ?>
                      <div class="d-flex align-items-center gap-1">
                        <input type="color" name="Setting[email_created_color]" class="form-control form-control-color flex-shrink-0"
                               value="<?= Html::encode($creColor) ?>" style="width:40px;height:38px;padding:2px;">
                        <input type="text" class="form-control px-2 email-color-hex" value="<?= Html::encode($creColor) ?>" placeholder="#0f766e" pattern="^#[0-9a-fA-F]{6}$" style="font-size:12px;">
                      </div>
                      <small class="text-muted">Warna dibuat admin.</small>
                    </div>
                  </div>
                </div>
              </div>
            </div><!-- end .row -->

            <!-- Info box -->
            <div class="alert alert-light border rounded p-3 mt-3 mb-0">
              <p class="mb-2 font-weight-bold"><i class="ph-duotone ph-info me-1 text-primary"></i> Jenis email yang dikirimkan sistem:</p>
              <ul class="mb-0 ps-3" style="font-size: 13px; color: #475569;">
                <li><span class="badge" style="background:#e0f2fe;color:#0284c7;">Biru</span> <strong>Kode OTP Verifikasi Email</strong> — dikirim saat pendaftaran akun baru.</li>
                <li><span class="badge" style="background:#ccfbf1;color:#0f766e;">Teal</span> <strong>Pendaftaran Akun Disetujui</strong> — dikirim beserta username &amp; kata sandi baru.</li>
                <li><span class="badge" style="background:#ffe4e6;color:#e11d48;">Merah</span> <strong>Pendaftaran Akun Ditolak</strong> — dikirim beserta alasan penolakan.</li>
                <li><span class="badge" style="background:#ccfbf1;color:#0f766e;">Teal</span> <strong>Akun Dibuat oleh Admin</strong> — dikirim beserta username &amp; kata sandi awal.</li>
              </ul>
            </div>
          </div>

          <!-- Tab 5: Pengaturan Beranda -->
          <div class="tab-pane fade" id="tab-dashboard-setting" role="tabpanel" aria-labelledby="dashboard-setting-tab">
            <h5 class="mb-4 fw-bold text-dark border-bottom pb-2">Pengaturan Visibilitas Halaman Beranda</h5>
            
            <div class="form-group mb-4">
              <label class="form-label font-weight-bold text-primary">Pilih Level / Peran User yang Ingin Diatur:</label>
              <select id="dashboardLevelSelector" class="form-select border-primary font-weight-bold" style="max-width: 450px;">
                <option value="1">Super Admin</option>
                <option value="2">PROVINSI</option>
                <option value="3">KAB/KOTA</option>
                <option value="4">KECAMATAN/DESA</option>
                <option value="7">Masyarakat</option>
              </select>
              <small class="form-text text-muted">Pilih peran user dari dropdown di atas untuk memunculkan checkbox pengaturan tampilan beranda.</small>
                   <?php
            $levels = [
                1 => 'Super Admin',
                2 => 'PROVINSI',
                3 => 'KAB/KOTA',
                4 => 'KECAMATAN/DESA',
                7 => 'Masyarakat'
            ];
            $sections = [
                'welcome_profile'        => 'Kartu Selamat Datang & Profil',
                'user_detail'            => 'Box Detail Akun Pengguna',
                'system_stats'           => 'Widget Statistik Sistem (User & Pending Approval)',
                'quick_config'           => 'Menu Akses Cepat',
                'user_activities_stats'  => 'Statistik Wilayah & Aktivitas Pengguna (Provinsi, Kab, Kec, Logs)',
            ];
            $allSubModules = \app\models\SubModul::find()
                ->where(['is_active' => true])
                ->andWhere(['not', ['route' => null]])
                ->andWhere(['not', ['route' => '']])
                ->andWhere(['not', ['route' => '#']])
                ->andWhere(['not', ['route' => '#!']])
                ->orderBy(['label' => SORT_ASC])
                ->all();
            ?>

            <?php foreach ($levels as $levelId => $levelName): ?>
              <?php 
              $currentVisible = \app\models\DashboardSetting::getVisibleSections($levelId);
              $currentQuickAccess = \app\models\DashboardSetting::getQuickAccessModules($levelId);
              ?>
              <div class="dashboard-level-group card border shadow-none mb-3 <?= $levelId === 1 ? '' : 'd-none' ?>" data-level="<?= $levelId ?>">
                <div class="card-header bg-light">
                  <h6 class="mb-0 fw-bold"><i class="ph-duotone ph-shield-check me-2 text-primary"></i> Pengaturan Dashboard: <?= Html::encode($levelName) ?></h6>
                </div>
                <div class="card-body">
                  <!-- Section 1: Visibility Sections -->
                  <div class="mb-4">
                    <label class="form-label font-weight-bold text-secondary mb-2">1. VISIBILITAS KARTU / SECTION BERANDA:</label>
                    <div class="row g-3">
                      <?php foreach ($sections as $secKey => $secLabel): ?>
                        <div class="col-12 col-md-6">
                          <div class="form-check">
                            <?= Html::checkbox("Setting[dashboard_setting_level_{$levelId}][]", in_array($secKey, $currentVisible), [
                                'value' => $secKey,
                                'class' => 'form-check-input',
                                'id' => "chk-beranda-{$levelId}-{$secKey}"
                            ]) ?>
                            <label class="form-check-label text-dark fw-semibold ms-1" for="chk-beranda-{$levelId}-{$secKey}">
                              <?= Html::encode($secLabel) ?>
                            </label>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>

                  <!-- Section 2: Quick Access -->
                  <div class="border-top pt-4">
                    <label class="form-label font-weight-bold text-secondary mb-2">2. MENU AKSES CEPAT (PILIH MODUL YANG DIAPLIKASIKAN):</label>
                    <div class="row g-3">
                      <?php foreach ($allSubModules as $subMod): ?>
                        <div class="col-12 col-md-6">
                          <div class="form-check">
                            <?= Html::checkbox("Setting[quick_access_level_{$levelId}][]", in_array($subMod->id, $currentQuickAccess), [
                                'value' => $subMod->id,
                                'class' => 'form-check-input',
                                'id' => "chk-quick-{$levelId}-{$subMod->id}"
                            ]) ?>
                            <label class="form-check-label text-dark fw-semibold ms-1" for="chk-quick-{$levelId}-{$subMod->id}">
                              <?= Html::encode($subMod->label) ?> <small class="text-muted">(<?= Html::encode($subMod->route) ?>)</small>
                            </label>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<div class="row mt-3 mb-4">
  <div class="col-12 text-end">
    <?= Html::submitButton('<i class="ti ti-device-floppy me-1"></i> Simpan Perubahan', ['class' => 'btn btn-primary px-4']) ?>
  </div>
</div>

<?= Html::endForm() ?>
