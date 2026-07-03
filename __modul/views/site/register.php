<?php

use app\models\RegisterMasyarakatForm;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\RegisterMasyarakatForm */
/* @var $provinsiList array */
/* @var $kabupatenList array */

$this->title = 'Pendaftaran Masyarakat';

$kategoriOptions = RegisterMasyarakatForm::kategoriAksesOptions();
$tujuanOptions = RegisterMasyarakatForm::tujuanAksesOptions();
$kabupatenUrl = Url::to(['site/register-kabupaten']);

$this->registerCss(<<<CSS
.register-page {
  min-height: 100vh;
  background-size: cover;
  background-position: center;
  background-attachment: fixed;
  padding: 36px 16px;
}
.register-shell {
  width: min(1120px, 100%);
  margin: 0 auto;
}
.register-topbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  margin-bottom: 22px;
}
.register-brand {
  display: flex;
  align-items: center;
  gap: 14px;
}
.register-brand img {
  width: 210px;
  max-width: 58vw;
}
.register-title {
  color: #102a43;
  font-weight: 700;
  margin: 0;
}
.register-subtitle {
  color: #627d98;
  margin: 4px 0 0;
}
.register-card {
  background: #fff;
  border: 1px solid #e6edf5;
  overflow: hidden;
}
.register-card-header {
  background: #1f9f99;
  color: #fff;
  padding: 18px 24px;
}
.register-card-header h4 {
  color: #fff;
  margin: 0;
  font-weight: 700;
}
.register-card-body {
  padding: 24px;
}
.section-title {
  color: #102a43;
  font-weight: 700;
  margin: 0 0 12px;
}
.category-grid .radio {
  margin: 0;
}
.category-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 12px;
  margin-bottom: 22px;
}
.category-grid label {
  width: 100%;
  min-height: 78px;
  display: flex;
  align-items: center;
  gap: 10px;
  border: 1px solid #d9e2ec;
  border-radius: 8px;
  padding: 14px;
  color: #243b53;
  background: #fff;
  font-weight: 600;
  cursor: pointer;
  transition: .18s ease;
}
.category-grid input {
  accent-color: #1f9f99;
}
.category-grid label:has(input:checked) {
  border-color: #1f9f99;
  background: #effcf9;
  box-shadow: inset 0 0 0 1px #1f9f99;
}
.register-form-area {
  border-top: 1px solid #e6edf5;
  padding-top: 22px;
}
.form-control {
  border-radius: 6px;
}
.help-block {
  color: #dc3545;
  margin-bottom: 0;
}
.password-hint {
  color: #829ab1;
  font-size: 12px;
  margin-top: 6px;
}
.password-strength {
  margin-top: 8px;
}
.password-strength-track {
  height: 7px;
  border-radius: 999px;
  overflow: hidden;
  background: #e6edf5;
}
.password-strength-bar {
  width: 0;
  height: 100%;
  border-radius: 999px;
  transition: width .18s ease, background-color .18s ease;
}
.password-strength-text {
  display: flex;
  justify-content: space-between;
  gap: 10px;
  margin-top: 5px;
  color: #829ab1;
  font-size: 12px;
}
.password-strength-label {
  font-weight: 700;
}
.password-strength[data-level="1"] .password-strength-bar { width: 20%; background: #d92d20; }
.password-strength[data-level="2"] .password-strength-bar { width: 40%; background: #f04438; }
.password-strength[data-level="3"] .password-strength-bar { width: 60%; background: #f79009; }
.password-strength[data-level="4"] .password-strength-bar { width: 80%; background: #12b76a; }
.password-strength[data-level="5"] .password-strength-bar { width: 100%; background: #067647; }
.password-strength[data-level="1"] .password-strength-label { color: #d92d20; }
.password-strength[data-level="2"] .password-strength-label { color: #f04438; }
.password-strength[data-level="3"] .password-strength-label { color: #f79009; }
.password-strength[data-level="4"] .password-strength-label { color: #12b76a; }
.password-strength[data-level="5"] .password-strength-label { color: #067647; }
.password-rules {
  border-left: 4px solid #1f9f99;
  background: #effcf9;
  color: #315b58;
  border-radius: 6px;
  margin-top: 12px;
}
.password-rules ul {
  margin: 8px 0 0;
  padding-left: 18px;
  font-size: 0.9rem;
}
.password-rules li {
  color: #dc3545;
  list-style: disc;
  margin-bottom: 4px;
}
.password-rules li.is-valid {
  color: #198754;
}
.captcha-row {
  display: flex;
  align-items: center;
  gap: 14px;
}
.captcha-row img {
  border-radius: 6px;
  border: 1px solid #d9e2ec;
  max-width: 180px;
}
.register-actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-top: 10px;
}
.btn-register {
  background: #1f9f99;
  border-color: #1f9f99;
  color: #fff;
  min-width: 180px;
  font-weight: 700;
}
.btn-register:hover,
.btn-register:focus {
  background: #17827d;
  border-color: #17827d;
  color: #fff;
}
.register-link {
  font-weight: 700;
  color: #1f9f99;
  text-decoration: none;
}
.register-link:hover {
  color: #17827d;
}
@media (max-width: 991px) {
  .category-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
  .register-topbar {
    align-items: flex-start;
    flex-direction: column;
  }
}
@media (max-width: 575px) {
  .register-page {
    padding: 20px 10px;
  }
  .register-card-body {
    padding: 18px;
  }
  .category-grid {
    grid-template-columns: 1fr;
  }
  .register-actions,
  .captcha-row {
    align-items: stretch;
    flex-direction: column;
  }
  .btn-register {
    width: 100%;
  }
}
CSS);

$this->registerJs(<<<JS
(function () {
  const provinsi = document.getElementById('registermasyarakatform-provinsi_id');
  const kabupaten = document.getElementById('registermasyarakatform-kabupaten_id');
  const formArea = document.getElementById('register-form-area');
  const kategoriInputs = document.querySelectorAll('input[name="RegisterMasyarakatForm[kategori_akses]"]');
  const tujuan = document.getElementById('registermasyarakatform-tujuan_akses');
  const tujuanLainnyaWrap = document.getElementById('tujuan-lainnya-wrap');
  const password = document.getElementById('registermasyarakatform-password');
  const strength = document.getElementById('password-strength');
  const strengthLabel = document.getElementById('password-strength-label');
  const strengthHint = document.getElementById('password-strength-hint');

  function updateFormVisibility() {
    const selected = document.querySelector('input[name="RegisterMasyarakatForm[kategori_akses]"]:checked');
    if (formArea) {
      formArea.style.display = selected ? 'block' : 'none';
    }
  }

  function updateTujuanLainnya() {
    if (!tujuan || !tujuanLainnyaWrap) return;
    tujuanLainnyaWrap.style.display = tujuan.value === 'lainnya' ? 'block' : 'none';
  }

  function getPasswordScore(value) {
    if (!value) return 0;

    let score = 0;
    if (value.length >= 1) score++;
    if (value.length >= 8) score++;
    if (/[a-z]/.test(value) && /[A-Z]/.test(value)) score++;
    if (/\\d/.test(value)) score++;
    if (/[^A-Za-z0-9]/.test(value) && value.length >= 10) score++;

    return Math.min(score, 5);
  }

  function validatePassword() {
    if (!password) return;

    const passwordValue = password.value;
    const checks = {
      'length-check': passwordValue.length >= 8,
      'uppercase-check': /[A-Z]/.test(passwordValue),
      'lowercase-check': /[a-z]/.test(passwordValue),
      'number-check': /\\d/.test(passwordValue),
      'special-check': /[@$!%*?&]/.test(passwordValue),
    };

    Object.keys(checks).forEach(function(id) {
      const element = document.getElementById(id);
      if (!element) return;
      element.classList.toggle('is-valid', checks[id]);
    });
  }

  function updatePasswordStrength() {
    if (!password || !strength || !strengthLabel || !strengthHint) return;

    const value = password.value || '';
    const score = getPasswordScore(value);
    const labels = ['', 'Sangat Lemah', 'Lemah', 'Cukup Kuat', 'Kuat', 'Sangat Kuat'];
    const hints = [
      'Masukkan password.',
      'Tambahkan minimal 8 karakter.',
      'Tambahkan huruf besar dan huruf kecil.',
      'Tambahkan angka agar memenuhi syarat.',
      'Tambahkan karakter khusus agar memenuhi syarat.',
      'Password sangat kuat.'
    ];

    strength.setAttribute('data-level', score);
    strengthLabel.textContent = labels[score] || 'Belum diisi';
    strengthHint.textContent = hints[score] || hints[0];
  }

  kategoriInputs.forEach(function (input) {
    input.addEventListener('change', updateFormVisibility);
  });

  if (tujuan) {
    tujuan.addEventListener('change', updateTujuanLainnya);
  }

  if (password) {
    password.addEventListener('input', function() {
      updatePasswordStrength();
      validatePassword();
    });
    password.addEventListener('change', function() {
      updatePasswordStrength();
      validatePassword();
    });
  }

  if (provinsi && kabupaten) {
    provinsi.addEventListener('change', function () {
      const value = provinsi.value;
      kabupaten.innerHTML = '<option value="">Memuat Kab/Kota...</option>';
      kabupaten.disabled = true;

      if (!value) {
        kabupaten.innerHTML = '<option value="">Pilih Provinsi Dahulu</option>';
        return;
      }

      fetch('$kabupatenUrl?province_id=' + encodeURIComponent(value), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
        .then(function (response) { return response.json(); })
        .then(function (result) {
          let html = '<option value="">Pilih Kab/Kota</option>';
          if (result.success && Array.isArray(result.data)) {
            result.data.forEach(function (item) {
              html += '<option value="' + String(item.id).replace(/"/g, '&quot;') + '">' + item.name + '</option>';
            });
          }
          kabupaten.innerHTML = html;
          kabupaten.disabled = false;
        })
        .catch(function () {
          kabupaten.innerHTML = '<option value="">Gagal memuat Kab/Kota</option>';
          kabupaten.disabled = false;
        });
    });
  }

  updateFormVisibility();
  updateTujuanLainnya();
  updatePasswordStrength();
})();
JS);
?>

<div class="register-page" style="background-image: url('<?= \app\components\SystemSettingHelper::getAssetUrl('login_background', '/app_asset/images/background-sipkk.png') ?>'); background-size: cover; background-position: center;">
  <div class="register-shell">
    <div class="register-topbar">
      <div class="register-brand">
        <a href="<?= Url::to(['site/login']) ?>">
          <img src="<?= \app\components\SystemSettingHelper::getAssetUrl('login_logo', '/app_asset/images/logo-kemenkes-warna.png') ?>" style="max-width:300px; height:auto;" alt="SIPKK">
        </a>
      </div>
      <div>
        <h2 class="register-title">Pendaftaran Akses Masyarakat</h2>
        <p class="register-subtitle">Lengkapi data berikut untuk mengajukan akses <?= \yii\helpers\Html::encode(\app\components\SystemSettingHelper::get('system_title', 'SIPKK')) ?>.</p>
      </div>
    </div>

    <div class="register-card">
      <div class="register-card-header">
        <h4>Form Pendaftaran</h4>
      </div>
      <div class="register-card-body">
        <?php $form = ActiveForm::begin([
            'id' => 'register-masyarakat-form',
            'enableClientValidation' => true,
        ]); ?>

        <h5 class="section-title">Pilih Kategori Akses</h5>
        <?= $form->field($model, 'kategori_akses', [
            'template' => "{input}\n{error}",
            'errorOptions' => ['class' => 'help-block'],
        ])->radioList($kategoriOptions, [
            'class' => 'category-grid',
            'item' => function ($index, $label, $name, $checked, $value) {
                $checkedAttr = $checked ? ' checked' : '';
                return '<label><input type="radio" name="' . Html::encode($name) . '" value="' . Html::encode($value) . '"' . $checkedAttr . '> <span>' . Html::encode($label) . '</span></label>';
            },
        ])->label(false) ?>

        <div id="register-form-area" class="register-form-area">
          <div class="row">
            <div class="col-md-6">
              <?= $form->field($model, 'nama_lengkap')->textInput(['class' => 'form-control', 'maxlength' => true]) ?>
            </div>
            <div class="col-md-6">
              <?= $form->field($model, 'username')->textInput(['class' => 'form-control', 'maxlength' => true, 'autocomplete' => 'username']) ?>
            </div>
            <div class="col-md-6">
              <?= $form->field($model, 'password')->passwordInput(['class' => 'form-control', 'autocomplete' => 'new-password'])
                  ->hint('Minimal 8 karakter dengan huruf besar, huruf kecil, angka, dan karakter khusus.', ['class' => 'password-hint']) ?>
              <div id="password-strength" class="password-strength" data-level="0" aria-live="polite">
                <div class="password-strength-track">
                  <div class="password-strength-bar"></div>
                </div>
                <div class="password-strength-text">
                  <span id="password-strength-label" class="password-strength-label">Belum diisi</span>
                  <span id="password-strength-hint">Masukkan password.</span>
                </div>
              </div>
              
            </div>
            <div class="col-md-6">
              <?= $form->field($model, 're_password')->passwordInput(['class' => 'form-control', 'autocomplete' => 'new-password']) ?>
            </div>
            <div class="col-md-6">
              <?= $form->field($model, 'email')->input('email', ['class' => 'form-control', 'maxlength' => true, 'autocomplete' => 'email']) ?>
            </div>
            <div class="col-md-6">
              <?= $form->field($model, 'telp')->textInput(['class' => 'form-control', 'maxlength' => true, 'inputmode' => 'tel']) ?>
            </div>
            <div class="col-md-6">
              <?= $form->field($model, 'nama_institusi')->textInput(['class' => 'form-control', 'maxlength' => true]) ?>
            </div>
            <div class="col-md-6">
              <?= $form->field($model, 'pekerjaan_posisi')->textInput(['class' => 'form-control', 'maxlength' => true]) ?>
            </div>
            <div class="col-md-12">
              <?= $form->field($model, 'alamat_user')->textarea(['class' => 'form-control', 'rows' => 3]) ?>
            </div>
            <div class="col-md-6">
              <?= $form->field($model, 'provinsi_id')->dropDownList($provinsiList, ['class' => 'form-control']) ?>
            </div>
            <div class="col-md-6">
              <?= $form->field($model, 'kabupaten_id')->dropDownList($kabupatenList, ['class' => 'form-control']) ?>
            </div>
            <div class="col-md-6">
              <?= $form->field($model, 'tujuan_akses')->dropDownList(['' => 'Pilih Tujuan Akses'] + $tujuanOptions, ['class' => 'form-control']) ?>
            </div>
            <div class="col-md-6" id="tujuan-lainnya-wrap">
              <?= $form->field($model, 'tujuan_akses_lainnya')->textInput(['class' => 'form-control']) ?>
            </div>
            <div class="col-md-12">
              <div class="captcha-row">
                <img id="capimg" src="<?= Url::to(['site/captcha', 't' => time()]) ?>" alt="captcha">
                <div style="flex: 1;">
                  <?= $form->field($model, 'verifyCode')->textInput([
                      'class' => 'form-control',
                      'autocomplete' => 'off',
                      'placeholder' => 'Masukkan kode captcha',
                  ]) ?>
                </div>
              </div>
            </div>
          </div>

          <div class="register-actions">
            <?= Html::a('Sudah punya akun? Login', ['site/login'], ['class' => 'register-link']) ?>
            <?= Html::submitButton('Kirim Pendaftaran', ['class' => 'btn btn-register']) ?>
          </div>
        </div>

        <?php ActiveForm::end(); ?>
      </div>
    </div>
  </div>
</div>
