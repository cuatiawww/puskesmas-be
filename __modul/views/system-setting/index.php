<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\SystemSettingHelper;

$this->title = 'Konfigurasi Tampilan Sistem';
$this->params['active_menu'] = 'system-setting';

// Register Summernote Lite rich text editor stylesheet and script
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css');
$this->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);

// CSS to style and rotate the chevron arrow smoothly when open/closed
$this->registerCss(<<<CSS
.accordion-button::after {
  display: none !important;
}
.accordion-button .accordion-arrow {
  transition: transform 0.2s ease-in-out;
}
.accordion-button:not(.collapsed) .accordion-arrow {
  transform: rotate(180deg);
}
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

// Initialize Summernote WYSIWYG editor
$this->registerJs(<<<JS
\$('.wysiwyg-editor').summernote({
    placeholder: 'Tulis konten HTML disini...',
    height: 300,
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

// Sinkronisasi color picker ↔ hex input untuk warna aksen email
(function() {
    var colorPicker = document.querySelector('input[name="Setting[email_header_color]"]');
    var hexInput    = document.getElementById('emailColorHex');
    if (!colorPicker || !hexInput) return;

    colorPicker.addEventListener('input', function() {
        hexInput.value = this.value;
    });
    hexInput.addEventListener('input', function() {
        var val = this.value.trim();
        if (/^#[0-9a-fA-F]{6}$/.test(val)) {
            colorPicker.value = val;
            // juga update name agar ikut di-POST
            colorPicker.dispatchEvent(new Event('change'));
        }
    });
    // Pastikan hex input ikut di-POST (nama Setting[email_header_color] ada di colorPicker)
    hexInput.addEventListener('change', function() {
        var val = this.value.trim();
        if (/^#[0-9a-fA-F]{6}$/.test(val)) {
            colorPicker.value = val;
        }
    });
})();
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

<?= Html::beginForm('', 'post', ['enctype' => 'multipart/form-data']) ?>

<div class="accordion" id="systemSettingAccordion">
  
  <!-- Accordion Item 1: Halaman Login (Yii Backend) -->
  <div class="accordion-item card mb-2">
    <h2 class="accordion-header" id="headingLogin">
      <button class="accordion-button collapsed font-weight-600" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLogin" aria-expanded="false" aria-controls="collapseLogin">
        <i class="ph-duotone ph-sign-in me-2 fs-5"></i> 
        <span>Pengaturan Halaman Login Admin</span>
        <i class="ti ti-chevron-down ms-auto accordion-arrow fs-5"></i>
      </button>
    </h2>
    <div id="collapseLogin" class="accordion-collapse collapse" aria-labelledby="headingLogin" data-bs-parent="#systemSettingAccordion">
      <div class="accordion-body card-body">
        
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
          <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="badge bg-light-primary text-primary">Rekomendasi Ukuran: 1920 x 1080 px</span>
            <span class="badge bg-light-secondary text-secondary">Rasio: 16:9 (Landscape)</span>
            <span class="badge bg-light-success text-success">Format: JPG, PNG, WebP</span>
          </div>
          <small class="form-text text-muted d-block mt-1">Background ini akan memenuhi layar (cover) pada halaman login admin.</small>
        </div>

        <!-- Login Logo -->
        <div class="form-group mb-3">
          <label class="form-label font-weight-bold">Logo Halaman Login</label>
          <div class="mb-3">
            <div class="p-3 border rounded bg-light text-center">
              <img src="<?= SystemSettingHelper::getAssetUrl('login_logo', '/app_asset/images/logo-kemenkes-warna.png') ?>" 
                   style="max-height: 70px; max-width: 100%; object-fit: contain;" alt="Login Logo Preview">
            </div>
          </div>
          <input type="file" name="SettingFile[login_logo]" class="form-control mb-2" accept="image/*">
          <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="badge bg-light-primary text-primary">Rekomendasi Lebar: Maksimal 300 px</span>
            <span class="badge bg-light-secondary text-secondary">Tinggi: Otomatis (Auto)</span>
            <span class="badge bg-light-success text-success">Format: PNG Transparan</span>
          </div>
          <small class="form-text text-muted d-block mt-1">Logo kementerian/instansi berwarna utama yang tampil di atas form login admin.</small>
        </div>
        
        <!-- Petunjuk Teknis Admin (WYSIWYG) -->
        <div class="form-group mb-3">
          <label class="form-label font-weight-bold">Petunjuk Teknis Penggunaan Admin</label>
          <textarea name="Setting[frontend_technical_guidelines]" class="form-control wysiwyg-editor" rows="6"><?= Html::encode(SystemSettingHelper::get('frontend_technical_guidelines')) ?></textarea>
          <small class="form-text text-muted">Petunjuk teknis masuk sistem yang akan ditampilkan dalam modal di halaman login admin.</small>
        </div>
        
      </div>
    </div>
  </div>

  <!-- Accordion Item 2: Tampilan Dashboard & Umum -->
  <div class="accordion-item card mb-2">
    <h2 class="accordion-header" id="headingDashboard">
      <button class="accordion-button collapsed font-weight-600" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDashboard" aria-expanded="false" aria-controls="collapseDashboard">
        <i class="ph-duotone ph-desktop me-2 fs-5"></i> 
        <span>Tampilan Dashboard Admin & Umum</span>
        <i class="ti ti-chevron-down ms-auto accordion-arrow fs-5"></i>
      </button>
    </h2>
    <div id="collapseDashboard" class="accordion-collapse collapse" aria-labelledby="headingDashboard" data-bs-parent="#systemSettingAccordion">
      <div class="accordion-body card-body">
        
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
        <div class="form-group mb-3">
          <label class="form-label font-weight-bold">Logo Sidebar (Halaman Dalam)</label>
          <div class="mb-3">
            <div class="p-3 border rounded bg-dark text-center">
              <img src="<?= SystemSettingHelper::getAssetUrl('inner_logo', '/app_asset/images/logo-haji.png') ?>" 
                   style="max-height: 50px; max-width: 100%; object-fit: contain; filter: drop-shadow(0 0 1px rgba(255,255,255,0.3));" alt="Inner Logo Preview">
            </div>
          </div>
          <input type="file" name="SettingFile[inner_logo]" class="form-control mb-2" accept="image/*">
          <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="badge bg-light-primary text-primary">Rekomendasi Lebar: Maksimal 240 px</span>
            <span class="badge bg-light-secondary text-secondary">Tinggi: Otomatis (Auto)</span>
            <span class="badge bg-light-success text-success">Format: PNG Transparan</span>
          </div>
          <small class="form-text text-muted d-block mt-1">Logo instansi dengan kontras warna terang (putih/emas) untuk ditempatkan pada sidebar navigasi berlatar gelap.</small>
        </div>
        
      </div>
    </div>
  </div>

  <!-- Accordion Item 3: Tampilan Dashboard & Aplikasi Utama -->
  <div class="accordion-item card mb-2">
    <h2 class="accordion-header" id="headingFrontend">
      <button class="accordion-button font-weight-600" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFrontend" aria-expanded="true" aria-controls="collapseFrontend">
        <i class="ph-duotone ph-desktop me-2 fs-5"></i> 
        <span>Tampilan Dashboard & Aplikasi Utama</span>
        <i class="ti ti-chevron-down ms-auto accordion-arrow fs-5"></i>
      </button>
    </h2>
    <div id="collapseFrontend" class="accordion-collapse collapse show" aria-labelledby="headingFrontend" data-bs-parent="#systemSettingAccordion">
      <div class="accordion-body card-body">

        <!-- App Title (Indikator Penilaian Kinerja Puskesmas) -->
        <div class="form-group mb-4">
          <label class="form-label font-weight-bold">Judul Utama Halaman Login</label>
          <input type="text" name="Setting[frontend_app_title]" class="form-control" 
                 value="<?= Html::encode(SystemSettingHelper::get('frontend_app_title', 'Indikator Penilaian Kinerja Puskesmas')) ?>" required>
          <small class="form-text text-muted">Judul utama teks besar di halaman login (Sisi Kiri/Hero).</small>
        </div>

        <!-- App Subtitle (Sistem pemantauan terpadu...) -->
        <div class="form-group mb-4">
          <label class="form-label font-weight-bold">Deskripsi Halaman Login</label>
          <textarea name="Setting[frontend_app_subtitle]" class="form-control" rows="3" required><?= Html::encode(SystemSettingHelper::get('frontend_app_subtitle', 'Sistem pemantauan terpadu untuk melihat capaian, sebaran, dan perkembangan Puskesmas di seluruh wilayah Indonesia.')) ?></textarea>
          <small class="form-text text-muted">Paragraf penjelasan di bawah judul utama hero halaman login.</small>
        </div>

        <!-- Login Card Title (Asistensi Kinerja Puskesmas) -->
        <div class="form-group mb-4">
          <label class="form-label font-weight-bold">Judul Card Form Login</label>
          <input type="text" name="Setting[frontend_login_card_title]" class="form-control" 
                 value="<?= Html::encode(SystemSettingHelper::get('frontend_login_card_title', 'Asistensi Kinerja Puskesmas')) ?>" required>
          <small class="form-text text-muted">Judul di atas kolom input form login (Sisi Kanan).</small>
        </div>

        <!-- Login Card Subtitle (Silakan masuk untuk mengakses...) -->
        <div class="form-group mb-4">
          <label class="form-label font-weight-bold">Sub-judul Card Form Login</label>
          <input type="text" name="Setting[frontend_login_card_subtitle]" class="form-control" 
                 value="<?= Html::encode(SystemSettingHelper::get('frontend_login_card_subtitle', 'Silakan masuk untuk mengakses data kinerja Puskesmas.')) ?>" required>
          <small class="form-text text-muted">Teks petunjuk kecil di bawah judul card form login.</small>
        </div>

        <!-- Security / Login Note (Akses terbatas untuk...) -->
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
          <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="badge bg-light-primary text-primary">Rekomendasi Lebar: Maksimal 300 px</span>
            <span class="badge bg-light-secondary text-secondary">Tinggi: Otomatis (Auto)</span>
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
          <div class="d-flex flex-wrap gap-2 align-items-center">
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
          <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="badge bg-light-primary text-primary">Rekomendasi Ukuran: 1920 x 1080 px</span>
            <span class="badge bg-light-secondary text-secondary">Rasio: 16:9 (Landscape)</span>
            <span class="badge bg-light-success text-success">Format: JPG, PNG, WebP</span>
          </div>
        </div>



        <!-- Terms & Conditions (WYSIWYG) -->
        <div class="form-group mb-3">
          <label class="form-label font-weight-bold">Syarat & Ketentuan Pengguna (Dashboard)</label>
          <textarea name="Setting[frontend_terms_conditions]" class="form-control wysiwyg-editor" rows="6"><?= Html::encode(SystemSettingHelper::get('frontend_terms_conditions')) ?></textarea>
          <small class="form-text text-muted">Syarat dan ketentuan pendaftaran akun yang akan ditampilkan saat pengguna mengklik Syarat & Ketentuan di halaman register.</small>
        </div>

      </div>
    </div>
  </div>
  <!-- Accordion Item 4: Email Notifikasi -->
  <div class="accordion-item card mb-2">
    <h2 class="accordion-header" id="headingEmail">
      <button class="accordion-button collapsed font-weight-600" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEmail" aria-expanded="false" aria-controls="collapseEmail">
        <i class="ph-duotone ph-envelope me-2 fs-5"></i>
        <span>Pengaturan Email Notifikasi</span>
        <i class="ti ti-chevron-down ms-auto accordion-arrow fs-5"></i>
      </button>
    </h2>
    <div id="collapseEmail" class="accordion-collapse collapse" aria-labelledby="headingEmail" data-bs-parent="#systemSettingAccordion">
      <div class="accordion-body card-body">

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

        <div class="row g-3">

          <!-- 1. Logo Email -->
          <div class="col-12">
            <div class="card border shadow-none">
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

          <!-- 2. Sapaan + Nama Sistem -->
          <div class="col-md-6">
            <div class="card border shadow-none h-100">
              <div class="card-body">
                <label class="form-label font-weight-bold">2. Sapaan Pembuka</label>
                <input type="text" name="Setting[email_greeting_prefix]" class="form-control"
                       value="<?= Html::encode(SystemSettingHelper::get('email_greeting_prefix', 'Yth.')) ?>"
                       placeholder="Contoh: Yth. / Kepada Yth. / Dear">
                <small class="text-muted">Muncul sebelum nama pengguna. Contoh: <em>"Yth. BUDI SANTOSO,"</em></small>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="card border shadow-none h-100">
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
            <div class="card border shadow-none">
              <div class="card-body">
                <label class="form-label font-weight-bold">3. Label Pengirim (tanda tangan bawah isi email)</label>
                <input type="text" name="Setting[email_sender_label]" class="form-control"
                       value="<?= Html::encode(SystemSettingHelper::get('email_sender_label', 'Asistensi Kinerja Puskesmas (KEMKES RI)')) ?>"
                       placeholder="Contoh: Puskesmas Kota Bandung (Dinkes Jabar)">
                <small class="text-muted">Teks yang muncul di bagian <em>"Dikirim oleh,"</em> di bawah konten email.</small>
              </div>
            </div>
          </div>

          <!-- 4. Footer Org + 5. Label Link + URL Link + Warna -->
          <div class="col-md-4">
            <div class="card border shadow-none h-100">
              <div class="card-body">
                <label class="form-label font-weight-bold">4. Teks Organisasi Footer</label>
                <input type="text" name="Setting[email_footer_org]" class="form-control"
                       value="<?= Html::encode(SystemSettingHelper::get('email_footer_org', 'Kementerian Kesehatan Republik Indonesia')) ?>"
                       placeholder="Contoh: Dinas Kesehatan Kota Bandung">
                <small class="text-muted">Nama instansi di kiri footer email.</small>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="card border shadow-none h-100">
              <div class="card-body">
                <label class="form-label font-weight-bold">5. Label Link Footer</label>
                <input type="text" name="Setting[email_footer_link_label]" class="form-control"
                       value="<?= Html::encode(SystemSettingHelper::get('email_footer_link_label', 'Kunjungi Website')) ?>"
                       placeholder="Contoh: Buka Aplikasi">
                <small class="text-muted">Teks tombol link di kanan footer.</small>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="card border shadow-none h-100">
              <div class="card-body">
                <label class="form-label font-weight-bold">URL Link Footer</label>
                <input type="text" name="Setting[email_footer_link_url]" class="form-control"
                       value="<?= Html::encode(SystemSettingHelper::get('email_footer_link_url')) ?>"
                       placeholder="Contoh: https://puskesmas.go.id">
                <small class="text-muted">Target link tombol footer. Kosongkan untuk default URL web.</small>
              </div>
            </div>
          </div>

          <div class="col-md-2">
            <div class="card border shadow-none h-100">
              <div class="card-body">
                <label class="form-label font-weight-bold">Warna Aksen</label>
                <?php $emailColor = SystemSettingHelper::get('email_header_color', '#0f766e'); ?>
                <div class="d-flex align-items-center gap-1">
                  <input type="color" name="Setting[email_header_color]" class="form-control form-control-color flex-shrink-0"
                         value="<?= Html::encode($emailColor) ?>" style="width:40px;height:38px;padding:2px;">
                  <input type="text" id="emailColorHex" class="form-control px-2"
                         value="<?= Html::encode($emailColor) ?>"
                         placeholder="#0f766e" pattern="^#[0-9a-fA-F]{6}$" style="font-size:12px;">
                </div>
                <small class="text-muted">Warna atas & footer.</small>
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
    </div>
  </div>
  
</div>

<div class="row mt-3 mb-4">
  <div class="col-12 text-end">
    <?= Html::submitButton('Simpan Perubahan', ['class' => 'btn btn-primary px-4']) ?>
  </div>
</div>

<?= Html::endForm() ?>
