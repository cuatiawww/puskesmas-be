<?php

use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\models\level_user\LevelUser;

$this->title = $model->isNewRecord ? 'Tambah User' : 'Edit User';
$this->params['active_menu'] = 'data-user';

$this->registerCss(<<<CSS
.sipkk-password-strength .progress {
  height: 8px;
}
CSS);

$isNew = $model->isNewRecord;
$selectedLevel = $model->id_user_level ?? $model->level_user_id ?? null;

$levelName    = '';
$isSuperAdmin = false;
$isProvinsi   = false;
$isKabKota    = false;
$isMasyarakat = false;

if ($selectedLevel) {
    try {
        $levelModel = LevelUser::findOne((int) $selectedLevel);
        if ($levelModel) {
            $levelName    = strtolower($levelModel->nama_level);
            $isSuperAdmin = ((int) $selectedLevel === 1);
            $isProvinsi   = strpos($levelName, 'provinsi') !== false;
            $isKabKota    = strpos($levelName, 'kab') !== false || strpos($levelName, 'kota') !== false;
            $isMasyarakat = strpos($levelName, 'masyarakat') !== false;
        }
    } catch (\Throwable $e) {
        // Fallback
    }
}
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center justify-content-between">
      <div class="col-sm-auto">
        <div class="page-header-title">
          <h5 class="mb-0 font-weight-600 fw-bold"><?= $isNew ? 'TAMBAH' : 'EDIT' ?> USER</h5>
        </div>
        <p><?= $isNew ? 'Buat akun pengguna berdasarkan level dan wilayah.' : 'Perbarui akun pengguna berdasarkan level dan wilayah.' ?></p>
      </div>
      <div class="col-sm-auto">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= Url::to(['site/index']) ?>"><i class="ph-duotone ph-house"></i></a></li>
          <li class="breadcrumb-item"><a href="#">AKSES</a></li>
          <li class="breadcrumb-item"><a href="<?= Url::to(['user-model/index']) ?>">DATA USER</a></li>
          <li class="breadcrumb-item" aria-current="page"><?= $isNew ? 'TAMBAH' : 'EDIT' ?></li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Form Data User</h5>
      </div>
      <div class="card-body">
        <?php $form = ActiveForm::begin(['options' => ['class' => 'form-horizontal']]); ?>

        <!-- Username -->
        <div class="mb-3">
          <?= $form->field($model, 'username')
            ->textInput([
              'class'        => 'form-control',
              'placeholder'  => 'Masukkan username',
              'autocomplete' => 'off',
            ])
            ->label('Username <span class="text-danger">*</span>', ['encode' => false]) ?>
        </div>

        <!-- Nama Lengkap -->
        <div class="mb-3">
          <?= $form->field($model, 'nama_lengkap')
            ->textInput([
              'class'        => 'form-control',
              'placeholder'  => 'Masukkan nama lengkap',
              'autocomplete' => 'off',
              'value'        => Html::encode($model->nama_lengkap ?? ''),
            ])
            ->label('Nama Lengkap <span class="text-danger">*</span>', ['encode' => false]) ?>
        </div>

        <!-- Email -->
        <div class="mb-3">
          <?= $form->field($model, 'email')
            ->textInput([
              'type'         => 'email',
              'class'        => 'form-control',
              'placeholder'  => 'Masukkan email',
              'autocomplete' => 'off',
            ])
            ->label('Email <span class="text-danger">*</span>', ['encode' => false]) ?>
        </div>

        <!-- Jenis Kelamin -->
        <div class="mb-3">
          <?= $form->field($model, 'jenis_kelamin')
            ->dropDownList([
              'Laki-laki' => 'Laki-laki',
              'Perempuan' => 'Perempuan',
            ], [
              'class'  => 'form-control',
              'prompt' => '-- Pilih Jenis Kelamin --',
            ])
            ->label('Jenis Kelamin') ?>
        </div>

        <!-- Alamat -->
        <div class="mb-3">
          <?= $form->field($model, 'alamat')
            ->textarea([
              'class'       => 'form-control',
              'placeholder' => 'Masukkan alamat lengkap',
              'rows'        => 2,
            ])
            ->label('Alamat') ?>
        </div>

        <!-- Password -->
        <div class="row">
          <div class="col-md-6">
            <div class="mb-3">
              <label for="password" class="form-label">
                <?= $isNew ? 'Password' : 'Password Baru' ?>
                <?= $isNew ? ' <span class="text-danger">*</span>' : '' ?>
              </label>
              <input
                type="password"
                class="form-control"
                id="password"
                name="password"
                autocomplete="new-password"
                placeholder="<?= $isNew ? 'Masukkan password' : 'Kosongkan jika tidak diubah' ?>"
              >
              <div class="form-text">Minimal 8 karakter dengan huruf besar, huruf kecil, angka, dan karakter khusus.</div>
              <div id="password-strength" class="sipkk-password-strength mt-2" aria-live="polite">
                <div class="progress">
                  <div
                    id="password-strength-bar"
                    class="progress-bar bg-secondary"
                    role="progressbar"
                    style="width: 0%"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    aria-valuenow="0"
                  ></div>
                </div>
                <div class="d-flex justify-content-between gap-2 mt-2 small">
                  <span id="password-strength-label" class="badge bg-secondary">Belum diisi</span>
                  <span id="password-strength-hint" class="text-muted">
                    <?= $isNew ? 'Masukkan password.' : 'Isi password baru jika ingin mengganti.' ?>
                  </span>
                </div>
              </div>
              <?php if ($model->hasErrors('password')): ?>
                <div class="invalid-feedback d-block"><?= Html::encode($model->getFirstError('password')) ?></div>
              <?php endif; ?>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label for="password_confirm" class="form-label">
                <?= $isNew ? 'Konfirmasi Password' : 'Konfirmasi Password Baru' ?>
                <?= $isNew ? ' <span class="text-danger">*</span>' : '' ?>
              </label>
              <input
                type="password"
                class="form-control"
                id="password_confirm"
                name="password_confirm"
                autocomplete="new-password"
                placeholder="Ulangi password"
              >
            </div>
          </div>
        </div>

        <!-- Level User -->
        <div class="mb-3">
          <?= $form->field($model, 'id_user_level')
            ->dropDownList($levelOptions ?? [], [
              'class'  => 'form-control',
              'prompt' => 'Pilih level user...',
              'id'     => 'user-level',
            ])
            ->label('Level User <span class="text-danger">*</span>', ['encode' => false]) ?>
        </div>

        <?= Html::activeHiddenInput($model, 'master_wilayah_id', ['id' => 'user-master-wilayah-id']) ?>

        <!-- Provinsi -->
        <div class="mb-3" id="provinsi-group" style="display: <?= ($isProvinsi || $isKabKota || $isMasyarakat) ? 'block' : 'none' ?>;">
          <label for="user-provinsi" class="form-label">
            Provinsi
            <?= ($isProvinsi || ($isKabKota && !$isMasyarakat)) ? '<span class="text-danger">*</span>' : '' ?>
          </label>
          <select class="form-control" id="user-provinsi" name="kd_prop">
            <option value="">Pilih Provinsi...</option>
            <?php
            $provinsiOptions = $provinsiList ?? [];
            foreach ($provinsiOptions as $id => $nama): ?>
              <option value="<?= Html::encode($id) ?>" <?= ($model->kd_prop == $id) ? 'selected' : '' ?>>
                <?= Html::encode($nama) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <small class="form-text text-muted" id="provinsi-help"></small>
        </div>

        <!-- Kabupaten/Kota -->
        <div class="mb-3" id="kabupaten-group" style="display: <?= ($isKabKota || $isMasyarakat) ? 'block' : 'none' ?>;">
          <label for="user-kabupaten" class="form-label">
            Kabupaten/Kota
          </label>
          <select class="form-control" id="user-kabupaten" name="kd_kab">
            <option value="">Pilih Kabupaten/Kota...</option>
            <?php
            $kabupatenOptions = $kabupatenList ?? [];
            foreach ($kabupatenOptions as $id => $nama): ?>
              <option value="<?= Html::encode($id) ?>" <?= ($model->kd_kab == $id) ? 'selected' : '' ?>>
                <?= Html::encode($nama) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <small class="form-text text-muted" id="kabupaten-help"></small>
        </div>

        <!-- Status Aktif -->
        <div class="mb-4">
          <?= $form->field($model, 'is_active', ['options' => ['class' => 'mb-0']])
            ->checkbox(['class' => 'form-check-input'], false)
            ->label('Status Aktif', ['class' => 'form-check-label']) ?>
        </div>

        <!-- Tombol -->
        <div class="d-flex gap-2 justify-content-end">
          <a href="<?= Url::to(['user-model/index']) ?>" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i> Kembali
          </a>
          <?= Html::submitButton(
            '<i class="ti ti-device-floppy me-1"></i> ' . ($isNew ? 'Simpan' : 'Update'),
            ['class' => 'btn btn-primary']
          ) ?>
        </div>

        <?php ActiveForm::end(); ?>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Panduan Singkat</h5>
      </div>
      <div class="card-body">
        <p class="text-muted mb-3">Pemilihan wilayah berdasarkan level user:</p>
        <div class="small text-muted">
          <p class="mb-2"><strong>Super Admin</strong>: Tidak perlu memilih wilayah</p>
          <p class="mb-2"><strong>Provinsi</strong>: Wajib pilih provinsi, kab/kota bebas dipilih</p>
          <p class="mb-2"><strong>Kab/Kota</strong>: Hanya pilih kab/kota (provinsi otomatis)</p>
          <p class="mb-0"><strong>Masyarakat</strong>: Bebas pilih provinsi &amp; kab/kota</p>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
$getProvinsiUrl  = Url::to(['user-model/get-provinsi']);
$getKabupatenUrl = Url::to(['user-model/get-kabupaten']);
$isNewRecordJs   = Json::htmlEncode((bool) $isNew);

$this->registerJs(<<<JS
(function() {
  var isNewRecord         = {$isNewRecordJs};
  var levelSelect         = document.getElementById('user-level');
  var provinsiSelect      = document.getElementById('user-provinsi');
  var kabupatenSelect     = document.getElementById('user-kabupaten');
  var masterWilayahInput  = document.getElementById('user-master-wilayah-id');
  var provinsiGroup       = document.getElementById('provinsi-group');
  var kabupatenGroup      = document.getElementById('kabupaten-group');
  var passwordInput       = document.getElementById('password');
  var passwordStrength    = document.getElementById('password-strength');
  var passwordStrengthBar = document.getElementById('password-strength-bar');
  var passwordStrengthLabel = document.getElementById('password-strength-label');
  var passwordStrengthHint  = document.getElementById('password-strength-hint');
  var initialProvinsiValue  = provinsiSelect  ? provinsiSelect.value  : '';
  var initialKabupatenValue = kabupatenSelect ? kabupatenSelect.value : '';

  if (!levelSelect || !provinsiSelect || !kabupatenSelect || !masterWilayahInput) {
    return;
  }

  function getLevelText() {
    var option = levelSelect.options[levelSelect.selectedIndex];
    return option ? String(option.text || '').toLowerCase() : '';
  }

  function isSuperAdminLevel() {
    return levelSelect.value === '1' || getLevelText().indexOf('super admin') !== -1;
  }

  function isProvinsiLevel() {
    return getLevelText().indexOf('provinsi') !== -1;
  }

  function isKabKotaLevel() {
    var text = getLevelText();
    return text.indexOf('kab') !== -1 || text.indexOf('kota') !== -1;
  }

  function isMasyarakatLevel() {
    return getLevelText().indexOf('masyarakat') !== -1;
  }

  function syncMasterWilayah() {
    if (isSuperAdminLevel()) {
      masterWilayahInput.value = '';
      return;
    }

    if (isKabKotaLevel() || isMasyarakatLevel()) {
      masterWilayahInput.value = kabupatenSelect.value || provinsiSelect.value || '';
      return;
    }

    if (isProvinsiLevel()) {
      masterWilayahInput.value = provinsiSelect.value || '';
      return;
    }

    masterWilayahInput.value = provinsiSelect.value || '';
  }

  function renderOptions(select, data, placeholder, selectedValue) {
    var html = '<option value="">' + placeholder + '</option>';
    Object.keys(data || {}).forEach(function(id) {
      var selected = String(selectedValue) === String(id) ? ' selected' : '';
      html += '<option value="' + id + '"' + selected + '>' + data[id] + '</option>';
    });
    select.innerHTML = html;
  }

  function getPasswordScore(value) {
    if (!value) return 0;

    var score = 0;
    if (value.length >= 1)  score++;
    if (value.length >= 8)  score++;
    if (/[a-z]/.test(value) && /[A-Z]/.test(value)) score++;
    if (/\d/.test(value))   score++;
    if (/[^A-Za-z0-9]/.test(value) && value.length >= 10) score++;

    return Math.min(score, 5);
  }

  function getStrengthState(score) {
    var map = {
      0: { width: 0,   bar: 'bg-secondary', badge: 'bg-secondary' },
      1: { width: 20,  bar: 'bg-danger',    badge: 'bg-danger' },
      2: { width: 40,  bar: 'bg-danger',    badge: 'bg-danger' },
      3: { width: 60,  bar: 'bg-warning',   badge: 'bg-warning text-dark' },
      4: { width: 80,  bar: 'bg-success',   badge: 'bg-success' },
      5: { width: 100, bar: 'bg-success',   badge: 'bg-success' }
    };
    return map[score] || map[0];
  }

  function updatePasswordStrength() {
    if (!passwordInput || !passwordStrength || !passwordStrengthBar || !passwordStrengthLabel || !passwordStrengthHint) {
      return;
    }

    var value  = passwordInput.value || '';
    var score  = getPasswordScore(value);
    var labels = ['', 'Sangat Lemah', 'Lemah', 'Cukup Kuat', 'Kuat', 'Sangat Kuat'];
    var hints  = [
      'Masukkan password.',
      'Tambahkan minimal 8 karakter.',
      'Tambahkan huruf besar dan huruf kecil.',
      'Tambahkan angka agar memenuhi syarat.',
      'Tambahkan karakter khusus agar memenuhi syarat.',
      'Password sangat kuat.'
    ];

    if (!value && !isNewRecord) {
      passwordStrengthBar.style.width = '0%';
      passwordStrengthBar.setAttribute('aria-valuenow', '0');
      passwordStrengthBar.className     = 'progress-bar bg-secondary';
      passwordStrengthLabel.className   = 'badge bg-secondary';
      passwordStrengthLabel.textContent = 'Belum diisi';
      passwordStrengthHint.textContent  = 'Isi password baru jika ingin mengganti.';
      return;
    }

    var state = getStrengthState(score);
    passwordStrengthBar.style.width = state.width + '%';
    passwordStrengthBar.setAttribute('aria-valuenow', String(state.width));
    passwordStrengthBar.className     = 'progress-bar ' + state.bar;
    passwordStrengthLabel.className   = 'badge ' + state.badge;
    passwordStrengthLabel.textContent = labels[score] || 'Belum diisi';
    passwordStrengthHint.textContent  = hints[score]  || hints[0];
  }

  function loadProvinsiList(selectedValue) {
    fetch('{$getProvinsiUrl}', {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    })
    .then(function(response) {
      if (!response.ok) throw new Error('HTTP error, status: ' + response.status);
      return response.json();
    })
    .then(function(data) {
      if (!data || Object.keys(data).length === 0) {
        provinsiSelect.innerHTML = '<option value="">Tidak ada data provinsi</option>';
        syncMasterWilayah();
        return;
      }
      renderOptions(provinsiSelect, data, 'Pilih Provinsi...', selectedValue || initialProvinsiValue);
      syncMasterWilayah();
    })
    .catch(function(err) {
      provinsiSelect.innerHTML = '<option value="">Gagal memuat provinsi</option>';
    });
  }

  function loadKabupaten(selectedValue) {
    var provinsiId = provinsiSelect.value;

    if (!provinsiId) {
      kabupatenSelect.innerHTML = '<option value="">Pilih Provinsi dahulu...</option>';
      syncMasterWilayah();
      return;
    }

    kabupatenSelect.innerHTML = '<option value="">Memuat...</option>';

    fetch('{$getKabupatenUrl}?provinsi_id=' + encodeURIComponent(provinsiId), {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    })
    .then(function(response) {
      if (!response.ok) throw new Error('HTTP error, status: ' + response.status);
      return response.json();
    })
    .then(function(data) {
      renderOptions(kabupatenSelect, data || {}, 'Pilih Kabupaten/Kota...', selectedValue || initialKabupatenValue);
      syncMasterWilayah();
    })
    .catch(function(err) {
      kabupatenSelect.innerHTML = '<option value="">Gagal memuat kabupaten</option>';
    });
  }

  function updateWilayahFields() {
    if (!levelSelect.value || isSuperAdminLevel()) {
      provinsiGroup.style.display  = 'none';
      kabupatenGroup.style.display = 'none';
      provinsiSelect.value  = '';
      kabupatenSelect.value = '';
      syncMasterWilayah();
      return;
    }

    provinsiGroup.style.display  = 'block';
    kabupatenGroup.style.display = 'block';

    if (provinsiSelect.options.length <= 1) {
      loadProvinsiList(provinsiSelect.value);
    }

    if (provinsiSelect.value) {
      loadKabupaten(kabupatenSelect.value);
    }

    syncMasterWilayah();
  }

  levelSelect.addEventListener('change', function() {
    if (!isKabKotaLevel() && !isMasyarakatLevel()) {
      kabupatenSelect.value = '';
    }
    updateWilayahFields();
  });

  provinsiSelect.addEventListener('change', function() {
    loadKabupaten();
  });

  kabupatenSelect.addEventListener('change', syncMasterWilayah);

  if (passwordInput) {
    passwordInput.addEventListener('input',  updatePasswordStrength);
    passwordInput.addEventListener('change', updatePasswordStrength);
  }

  if (provinsiSelect.options.length <= 1) {
    loadProvinsiList(initialProvinsiValue);
  }
  updateWilayahFields();
  updatePasswordStrength();

  // Show SweetAlert loader on form submit after validation passes
  if (typeof jQuery !== 'undefined') {
    jQuery('.form-horizontal').on('beforeSubmit', function() {
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          title: 'Memproses...',
          text: 'Sedang menyimpan data dan mengirim email notifikasi, mohon tunggu...',
          allowOutsideClick: false,
          showConfirmButton: false,
          didOpen: function() {
            Swal.showLoading();
          }
        });
      }
      return true;
    });
  }
})();
JS);
?>