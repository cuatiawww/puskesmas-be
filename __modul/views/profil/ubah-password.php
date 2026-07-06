<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Ubah Password';
$this->params['active_menu'] = 'profil';
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center justify-content-between">
      <div class="col-sm-auto">
        <div class="page-header-title">
          <h5 class="mb-0 fw-bold text-uppercase">UBAH PASSWORD</h5>
          <p>Perbarui kata sandi keamanan Anda.</p>
        </div>
      </div>
      <div class="col-sm-auto">
        <ul class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= Url::to(['/site/index']) ?>"><i class="ph-duotone ph-house"></i></a></li>
          <li class="breadcrumb-item">BERANDA</li>
          <li class="breadcrumb-item"><a href="<?= Url::to(['profil/index']) ?>">AKUN SAYA</a></li>
          <li class="breadcrumb-item">UBAH PASSWORD</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0 fw-bold">Ubah Kata Sandi Keamanan</h5>
      </div>
      <div class="card-body">
        
        <!-- Warning Box -->
        <div class="alert alert-warning d-flex align-items-center mb-4 p-3 rounded-2" style="background-color: #fff9e6; border-left: 4px solid #ffc107; color: #856404;">
          <i class="ti ti-info-circle fs-4 me-2"></i>
          <div>
            Setelah berhasil mengganti password, Anda akan otomatis keluar secara aman dan harus login kembali dengan kata sandi baru.
          </div>
        </div>

        <?php if ($model->hasErrors()): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= Html::errorSummary($model) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>

        <?= Html::beginForm(['profil/ubah-password'], 'post', ['id' => 'change-password-form']) ?>

          <div class="mb-3">
            <label class="form-label fw-semibold text-dark">KATA SANDI LAMA</label>
            <div class="input-group">
              <span class="input-group-text bg-light text-muted"><i class="ph-duotone ph-lock"></i></span>
              <?= Html::passwordInput('password_lama', '', [
                  'class' => 'form-control', 
                  'required' => true,
                  'placeholder' => 'Masukkan kata sandi saat ini'
              ]) ?>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold text-dark">KATA SANDI BARU</label>
              <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i class="ph-duotone ph-key"></i></span>
                <?= Html::passwordInput('password_baru', '', [
                    'class' => 'form-control', 
                    'id' => 'password_baru',
                    'required' => true,
                    'placeholder' => 'Masukkan kata sandi baru'
                ]) ?>
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

            <div class="col-md-6">
              <label class="form-label fw-semibold text-dark">KONFIRMASI SANDI BARU</label>
              <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i class="ph-duotone ph-key"></i></span>
                <?= Html::passwordInput('password_konfirmasi', '', [
                    'class' => 'form-control', 
                    'required' => true,
                    'placeholder' => 'Ulangi kata sandi baru'
                ]) ?>
              </div>
            </div>
          </div>

          <div class="text-end gap-2 mt-4">
            <a href="<?= Url::to(['profil/index']) ?>" class="btn btn-outline-secondary px-4 me-2">
              Batal
            </a>
            <button type="submit" class="btn text-white px-4" id="submit-password-btn" style="background-color: #1abc9c; border-color: #1abc9c;">
              <i class="ti ti-key me-1"></i> UBAH KATA SANDI
            </button>
          </div>

        <?= Html::endForm() ?>

      </div>
    </div>
  </div>
</div>

<?php
$this->registerJs(<<<JS
$(document).ready(function() {
    var passwordInput = $('#password_baru');
    var submitBtn = $('#submit-password-btn');
    
    var ruleLength = $('#rule-length');
    var ruleUpper = $('#rule-upper');
    var ruleNumber = $('#rule-number');
    var ruleSpecial = $('#rule-special');
    
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
    
    // Initialize button state as disabled
    submitBtn.prop('disabled', true);
});
JS
);
?>
