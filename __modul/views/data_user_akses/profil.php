<?php
use yii\helpers\Html;
use yii\helpers\Url;

/*
 * View: akses-level-user (server-side, non-AJAX)
 * Expected variables from controller:
 * - $user (array or object) OR use $user_id if provided
 */

$user = isset($user) ? (array)$user : [];
$userId = $user['id'] ?? ($user_id ?? null);
$username = Html::encode($user['username'] ?? '');
$nama = Html::encode($user['nama_lengkap'] ?? '');
$email = Html::encode($user['email'] ?? '');
$no_telpon = Html::encode($user['no_telpon'] ?? '');
$level_name = Html::encode($user['level_user_nama'] ?? '');
$kode_kloter = Html::encode($user['kode_kloter'] ?? '');
$embarkasi = Html::encode($user['embarkasi'] ?? '');
$defaultAvatar = Url::to('@web/app_asset/images/user/avatar-1.jpg');
$foto_src = $defaultAvatar;
// Try multiple common fields that may contain a photo path
$candidates = [];
if (!empty($user['foto_profil'])) $candidates[] = $user['foto_profil'];
if (!empty($user['photo'])) $candidates[] = $user['photo'];
if (!empty($user['photo_thumb'])) $candidates[] = $user['photo_thumb'];
if (!empty($user['foto'])) $candidates[] = $user['foto'];

foreach ($candidates as $foto) {
    if (empty($foto)) continue;
    // Absolute URL -> use directly
    if (preg_match('#^https?://#i', $foto)) {
        $foto_src = $foto;
        break;
    }

    // Normalize path and check file existence on disk
    $relative = ltrim(str_replace('\\', '/', $foto), '/');
    $diskPath = Yii::getAlias('@app/../') . $relative;
    if (file_exists($diskPath)) {
        $foto_src = Url::to('@web/' . $relative) . '?t=' . filemtime($diskPath);
        break;
    }

    // Try common uploads folder with basename
    $alt = 'uploads/profile-photos/' . basename($relative);
    if (file_exists(Yii::getAlias('@app/../') . $alt)) {
        $foto_src = Url::to('@web/' . $alt) . '?t=' . filemtime(Yii::getAlias('@app/../') . $alt);
        break;
    }

    // Fallback to a web-relative candidate (may be 404 but better than nothing)
    $foto_src_candidate = Url::to('@web/' . $relative);
    if ($foto_src_candidate) {
        $foto_src = $foto_src_candidate;
        break;
    }
}

?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center justify-content-between">
      <div class="col-sm-auto">
        <div class="page-header-title">
              <h5 class="mb-0 font-weight-600 fw-bold">PROFIL PETUGAS</h5>
              <p>Informasi akun petugas sistem</p>
            </div>
          </div>
          <div class="col-sm-auto">
            <ul class="breadcrumb">
              <li class="breadcrumb-item"><?= Html::a('<i class="ph-duotone ph-house"></i>', ['site/index']) ?></li>
              <li class="breadcrumb-item" aria-current="page">PROFIL</li>
            </ul>
          </div>
          <div style="margin-top: -5px;">
       <a class="btn btn-sm btn-light-primary rounded-pill px-2"
   role="button"
   href="javascript:void(0);">
  <i class="ti ti-external-link me-1"></i>
  Info Selengkapnya
</a>
      </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h5>Informasi Akun</h5>
          </div>
          <div class="card-body">

            <?php if (Yii::$app->session->hasFlash('success')): ?>
              <div class="alert alert-success"><?= Yii::$app->session->getFlash('success') ?></div>
            <?php endif; ?>
            <?php if (Yii::$app->session->hasFlash('error')): ?>
              <div class="alert alert-danger"><?= Yii::$app->session->getFlash('error') ?></div>
            <?php endif; ?>

            <?php $errors = isset($errors) ? $errors : [];?>
            <?php if (!empty($errors)): ?>
              <div class="alert alert-danger">
                <strong>Validasi gagal:</strong>
                <ul class="mb-0">
                  <?php foreach ($errors as $attr => $msgs): ?>
                    <?php foreach ($msgs as $msg): ?>
                      <li><?= Html::encode($msg) ?></li>
                    <?php endforeach; ?>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <?= Html::beginForm(['user-model/update'], 'post', ['enctype' => 'multipart/form-data', 'id' => 'formProfil']) ?>

              <?= Html::hiddenInput('id', $userId) ?>

              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Username <span class="text-danger">*</span></label>
                    <?= Html::textInput('username', $username, ['class' => 'form-control', 'required' => true]) ?>
                    <small class="text-muted">Username untuk login</small>
                    <?php if (!empty($errors['username'])): ?>
                      <div class="text-danger small mt-1"><?= Html::encode(implode(', ', $errors['username'])) ?></div>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Level User</label>
                    <?= Html::textInput('level_user_nama', $level_name, ['class' => 'form-control', 'readonly' => true]) ?>
                    <?php if (!empty($errors['level_user_id'])): ?>
                      <div class="text-danger small mt-1"><?= Html::encode(implode(', ', $errors['level_user_id'])) ?></div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <?= Html::textInput('nama_lengkap', $nama, ['class' => 'form-control', 'required' => true]) ?>
                    <?php if (!empty($errors['nama_lengkap'])): ?>
                      <div class="text-danger small mt-1"><?= Html::encode(implode(', ', $errors['nama_lengkap'])) ?></div>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <?= Html::input('email', 'email', $email, ['class' => 'form-control', 'required' => true]) ?>
                    <?php if (!empty($errors['email'])): ?>
                      <div class="text-danger small mt-1"><?= Html::encode(implode(', ', $errors['email'])) ?></div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">No Telepon</label>
                    <?= Html::textInput('no_telpon', $no_telpon, ['class' => 'form-control']) ?>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Foto Profil</label>
                    <?= Html::fileInput('foto_profil_file', null, ['class' => 'form-control', 'accept'=>'image/*', 'id'=>'foto_profil_file']) ?>
                    <small class="text-muted">Maksimal 2MB. Format: JPG, PNG, GIF</small>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Kode Kloter</label>
                    <?= Html::textInput('kode_kloter', $kode_kloter, ['class' => 'form-control', 'placeholder' => 'Contoh: KL001']) ?>
                    <small class="text-muted">Kode identifikasi kloter/keberangkatan</small>
                    <?php if (!empty($errors['kode_kloter'])): ?>
                      <div class="text-danger small mt-1"><?= Html::encode(implode(', ', $errors['kode_kloter'])) ?></div>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Embarkasi</label>
                    <?= Html::textInput('embarkasi', $embarkasi, ['class' => 'form-control', 'placeholder' => 'Lokasi embarkasi/pemberangkatan']) ?>
                    <small class="text-muted">Lokasi pemberangkatan awal</small>
                    <?php if (!empty($errors['embarkasi'])): ?>
                      <div class="text-danger small mt-1"><?= Html::encode(implode(', ', $errors['embarkasi'])) ?></div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>

              <div class="row" id="preview-container" style="display: none;">
                <div class="col-md-12">
                  <div class="mb-3">
                    <label class="form-label">Preview Foto Profil</label>
                    <div>
                      <img id="foto_preview" src="" alt="Preview" style="max-width:200px; max-height:200px; border-radius:8px; border:2px solid #ddd;">
                    </div>
                  </div>
                </div>
              </div>

              <div class="row" id="current-photo-container">
                <div class="col-md-12">
                  <div class="mb-3">
                    <label class="form-label">Foto Profil Saat Ini</label>
                    <div>
                      <img id="current_foto" src="<?= Html::encode($foto_src) ?>" alt="Foto Profil" style="max-width:200px; max-height:200px; border-radius:8px; border:2px solid #ddd;">
                    </div>
                  </div>
                </div>
              </div>

              <div class="text-end">
                <?= Html::submitButton('<i class="ph-duotone ph-floppy-disk me-2"></i>Simpan Perubahan', ['class' => 'btn btn-primary', 'id' => 'btnSimpan']) ?>
              </div>

            <?= Html::endForm() ?>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php $this->registerJs(<<<JS
// Preview foto saat dipilih (client-side only, tidak mengirim via AJAX)
(function(){
  var fileInput = document.getElementById('foto_profil_file');
  if (!fileInput) return;
  fileInput.addEventListener('change', function(e){
    var file = e.target.files && e.target.files[0];
    if (file) {
      var reader = new FileReader();
      reader.onload = function(evt){
        var img = document.getElementById('foto_preview');
        if (img) img.src = evt.target.result;
        var pc = document.getElementById('preview-container');
        if (pc) pc.style.display = '';
      };
      reader.readAsDataURL(file);
    } else {
      var pc = document.getElementById('preview-container');
      if (pc) pc.style.display = 'none';
    }
  });
})();

// Also update menu/header avatars immediately when profile changes
(function(){
  try {
    var newSrc = <?= \yii\helpers\Json::encode($foto_src) ?>;
    var selectors = '.user-avtar, .wid-50, .nav-user-image img, img.user-avtar, #current_foto';
    document.querySelectorAll(selectors).forEach(function(el){
      if (el) el.src = newSrc;
    });
  } catch (e) {
    console && console.warn('avatar update failed', e);
  }
})();

JS
); ?>

<?php
// Register swal only on this profil page if flash exists
$swalProfile = Yii::$app->session->getFlash('swal_profile', null);
if ($swalProfile) {
    $swalJson = \yii\helpers\Json::encode($swalProfile);
    $jsSwal = "(function(){ var opt = $swalJson; if(!opt.toast && opt.position === undefined) opt.position = 'center'; if(!opt.toast && opt.timer === undefined) opt.timer = 2500; if(!opt.toast && opt.showConfirmButton === undefined) opt.showConfirmButton = false; if (typeof Swal !== 'undefined') { Swal.fire(opt); } else { console && console.warn('SweetAlert2 (Swal) belum ter-load'); } })();";
    $this->registerJs($jsSwal);
}
?>
