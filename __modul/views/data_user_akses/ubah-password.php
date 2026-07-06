<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $user_id int */

$this->title = 'Ubah Password';
$this->params['active_menu'] = 'ubah-password';
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center justify-content-between">
      <div class="col-sm-auto">
        <div class="page-header-title">
              <h5 class="mb-0 font-weight-600 fw-bold">UBAH PASSWORD</h5>
                <p>Form untuk mengubah password akun Anda</p>
            </div>
          </div>
          <div class="col-sm-auto">
            <ul class="breadcrumb">
              <li class="breadcrumb-item"><?= Html::a('<i class="ph-duotone ph-house"></i>', ['site/index']) ?></li>
              <li class="breadcrumb-item" aria-current="page">UBAH PASSWORD</li>
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
            <h5>Ganti Password</h5>
          </div>
          <div class="card-body">
            <div id="alert-container"></div>

            <form id="formUbahPassword" method="post" action="<?= Url::to(['user-model/ubah-password']) ?>">
              <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
              <input type="hidden" name="user_id" value="<?= (int)($user_id ?? Yii::$app->user->id) ?>" />
              <div class="mb-3">
                <label class="form-label">Password Lama <span class="text-danger">*</span></label>
                <div class="input-group">
                  <input type="password" class="form-control" id="old_password" name="old_password" required />
                  <button class="btn btn-outline-secondary" type="button" id="toggleOldPassword">
                    <i class="ph-duotone ph-eye"></i>
                  </button>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Password Baru <span class="text-danger">*</span></label>
                <div class="input-group">
                  <input type="password" class="form-control" id="new_password" name="new_password" required />
                  <button class="btn btn-outline-secondary" type="button" id="toggleNewPassword">
                    <i class="ph-duotone ph-eye"></i>
                  </button>
                </div>
                <!-- Password Strength Checklist -->
                <ul class="list-unstyled mt-2" id="password-rules-list" style="font-size: 0.825rem; line-height: 1.6;">
                  <li id="rule-length" class="text-danger fw-semibold">
                    <i class="ti ti-x me-1"></i> Minimal 8 Karakter
                  </li>
                  <li id="rule-upper" class="text-danger fw-semibold">
                    <i class="ti ti-x me-1"></i> Mengandung Huruf Besar (A-Z)
                  </li>
                  <li id="rule-number" class="text-danger fw-semibold">
                    <i class="ti ti-x me-1"></i> Mengandung Angka (0-9)
                  </li>
                  <li id="rule-special" class="text-danger fw-semibold">
                    <i class="ti ti-x me-1"></i> Mengandung Karakter Khusus (@$!%*?&)
                  </li>
                </ul>
              </div>

              <div class="mb-3">
                <label class="form-label">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                <div class="input-group">
                  <input type="password" class="form-control" id="confirm_password" name="confirm_password" required />
                  <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                    <i class="ph-duotone ph-eye"></i>
                  </button>
                </div>
              </div>

              <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary" id="btnUbahPassword">
                  <i class="ph-duotone ph-lock-key me-2"></i>Ubah Password
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php $this->registerJs(<<<'JS'
$(document).ready(function() {
  // Toggle password visibility
  $('#toggleOldPassword').on('click', function() { togglePasswordVisibility('old_password', $(this)); });
  $('#toggleNewPassword').on('click', function() { togglePasswordVisibility('new_password', $(this)); });
  $('#toggleConfirmPassword').on('click', function() { togglePasswordVisibility('confirm_password', $(this)); });

  var passwordInput = $('#new_password');
  var submitBtn = $('#btnUbahPassword');
  
  var ruleLength = $('#rule-length');
  var ruleUpper = $('#rule-upper');
  var ruleNumber = $('#rule-number');
  var ruleSpecial = $('#rule-special');
  
  // Disable button on load
  submitBtn.prop('disabled', true);
  
  passwordInput.on('input', function() {
      var val = $(this).val();
      
      var hasLength = val.length >= 8;
      var hasUpper = /[A-Z]/.test(val);
      var hasNumber = /\d/.test(val);
      var hasSpecial = /[@$!%*?&]/.test(val);
      
      // Rule Length
      if (hasLength) {
          ruleLength.removeClass('text-danger').addClass('text-success')
                    .find('i').removeClass('ti-x').addClass('ti-check');
      } else {
          ruleLength.removeClass('text-success').addClass('text-danger')
                    .find('i').removeClass('ti-check').addClass('ti-x');
      }
      
      // Rule Upper
      if (hasUpper) {
          ruleUpper.removeClass('text-danger').addClass('text-success')
                   .find('i').removeClass('ti-x').addClass('ti-check');
      } else {
          ruleUpper.removeClass('text-success').addClass('text-danger')
                   .find('i').removeClass('ti-check').addClass('ti-x');
      }
      
      // Rule Number
      if (hasNumber) {
          ruleNumber.removeClass('text-danger').addClass('text-success')
                    .find('i').removeClass('ti-x').addClass('ti-check');
      } else {
          ruleNumber.removeClass('text-success').addClass('text-danger')
                    .find('i').removeClass('ti-check').addClass('ti-x');
      }
      
      // Rule Special
      if (hasSpecial) {
          ruleSpecial.removeClass('text-danger').addClass('text-success')
                     .find('i').removeClass('ti-x').addClass('ti-check');
      } else {
          ruleSpecial.removeClass('text-success').addClass('text-danger')
                     .find('i').removeClass('ti-check').addClass('ti-x');
      }
      
      // Enable/Disable submit button
      if (hasLength && hasUpper && hasNumber && hasSpecial) {
          submitBtn.prop('disabled', false);
      } else {
          submitBtn.prop('disabled', true);
      }
  });

  $('#formUbahPassword').on('submit', function(e) {
    var oldPassword = $('#old_password').val();
    var newPassword = $('#new_password').val();
    var confirmPassword = $('#confirm_password').val();

    var hasLength = newPassword.length >= 8;
    var hasUpper = /[A-Z]/.test(newPassword);
    var hasNumber = /\d/.test(newPassword);
    var hasSpecial = /[@$!%*?&]/.test(newPassword);

    if (!hasLength || !hasUpper || !hasNumber || !hasSpecial) {
      showAlert('Password belum memenuhi syarat kriteria password kuat', 'danger');
      e.preventDefault();
      return false;
    }
    if (newPassword !== confirmPassword) {
      showAlert('Konfirmasi password tidak cocok', 'danger');
      e.preventDefault();
      return false;
    }
    if (oldPassword === newPassword) {
      showAlert('Password baru harus berbeda dengan password lama', 'danger');
      e.preventDefault();
      return false;
    }
  });
});

function togglePasswordVisibility(inputId, button) {
  var input = $('#' + inputId);
  var icon = button.find('i');

  var isPassword = input.attr('type') === 'password' || input.prop('type') === 'password';

  if (isPassword) {
    input.attr('type', 'text');
    // set accessible attributes
    button.attr('aria-pressed', 'true').attr('title', 'Sembunyikan kata sandi');
    // try multiple icon families (phosphor / font-awesome)
    if (icon.length) {
      if (icon.hasClass('ph-eye')) icon.removeClass('ph-eye').addClass('ph-eye-slash');
      else if (icon.hasClass('fa-eye')) icon.removeClass('fa-eye').addClass('fa-eye-slash');
      else icon.toggleClass('visible');
    }
  } else {
    input.attr('type', 'password');
    button.attr('aria-pressed', 'false').attr('title', 'Tampilkan kata sandi');
    if (icon.length) {
      if (icon.hasClass('ph-eye-slash')) icon.removeClass('ph-eye-slash').addClass('ph-eye');
      else if (icon.hasClass('fa-eye-slash')) icon.removeClass('fa-eye-slash').addClass('fa-eye');
      else icon.toggleClass('visible');
    }
  }
}

  // form will submit to server; keep UI simple for non-AJAX flow

function showAlert(message, type) {
  var alertHtml = `
    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  `;
  $('#alert-container').html(alertHtml);
}

// Show SweetAlert modal and redirect on confirm when password changed
JS
); ?>

<?php $swalPwd = Yii::$app->session->getFlash('swal_password_changed', null);
if (!empty($swalPwd)) {
    $icon = htmlspecialchars($swalPwd['icon'] ?? 'success', ENT_QUOTES);
    $title = htmlspecialchars($swalPwd['title'] ?? 'Berhasil', ENT_QUOTES);
    $text = htmlspecialchars($swalPwd['text'] ?? 'Operasi berhasil', ENT_QUOTES);
    $loginUrl = Url::to(['site/login']);
    $this->registerJs(<<<'JS'
        Swal.fire({
            icon: '$icon',
            title: '$title',
            text: '$text',
            confirmButtonText: 'OK'
        }).then(function() {
            window.location.href = '$loginUrl';
        });
JS
    );
}
?>