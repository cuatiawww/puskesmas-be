<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;

use kartik\password\PasswordInput;
use yii\base\Model;
use yii\helpers\Url;

$this->title = 'Pengelolaan Website';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss(<<<CSS
.form-control {
  border-radius: 6px;
}
.register-link {
  font-weight: 700;
  color: #1f9f99;
  text-decoration: none;
}
.register-link:hover {
  color: #17827d;
}
CSS);

?>

<?php
$error = \Yii::$app->session['error'];
$error_title = \Yii::$app->session['error_title'];
$error_message = \Yii::$app->session['error_message'];

?>
<?php if ($error != NULL) {

	if ($error == 1) {
		$title = 'Error!!';
	} elseif ($error == 2) {
		$title = $error_title ? $error_title : 'Sukses!!';
	}
?>
	<script>
		const showLoadingx = function(e) {
			Swal.fire({
				title: "<?= $title ?>",
				text: "<?= $error_message ?>",
				allowEscapeKey: false,
				allowOutsideClick: false,
				showConfirmButton: true,
				confirmButtonColor: '#000287',
				showLoading: false,
				closeOnConfirm: true,
				showConfirmButton: true,
				onOpen: () => {
					//swal.showLoading();
				}
			}, function() {

			});
		};

		$(document).ready(function() {
			showLoadingx();
		})
	</script>
	<?php
	unset(\Yii::$app->session['error']);
	unset(\Yii::$app->session['error_message']);
	?>
<?php } ?>


<?php
$error = \Yii::$app->session['error_login'];
$error_message = \Yii::$app->session['error_login_message'];

?>
<?php if ($error != NULL) {

	if ($error == 1) {
		$title = 'Login Tidak Berhasil!!';
	} elseif ($error == 2) {
		$title = 'Sukses!!';
	}
?>
	<script>
		const showLoadingx2 = function(e) {
			Swal.fire({
				title: "<?= $title ?>",
				text: "<?= $error_message ?>",
				allowEscapeKey: false,
				allowOutsideClick: false,
				showConfirmButton: true,
				confirmButtonColor: '#870000',
				showLoading: false,
				closeOnConfirm: true,
				showConfirmButton: true,
				onOpen: () => {
					//swal.showLoading();
				}
			}, function() {

			});
		};

		$(document).ready(function() {
			showLoadingx2();
		})
	</script>
	<?php
	unset(\Yii::$app->session['error_login']);
	unset(\Yii::$app->session['error_login_message']);
	?>
<?php } ?>


<div class="auth-main v1" style="background-image: url('<?= \app\components\SystemSettingHelper::getAssetUrl('login_background', '/app_asset/images/background-sipkk.png') ?>'); background-size: cover; background-position: center; background-attachment: fixed;">
	<div class="auth-wrapper">
		<div class="auth-form">
			<a href="<?= Yii::$app->params['base_url'] ?>" class="d-block mt-5"><img src="<?= \app\components\SystemSettingHelper::getAssetUrl('login_logo', '/app_asset/images/logo-kemenkes-warna.png') ?>" style="max-width:300px; height:auto;" alt="img" /></a>
			<div class="card mb-5 mt-3">
				<div class="card-header" style="background-color: #2AB2A8;">
					<!-- <h4 class="text-center text-white f-w-500 mb-0">Sistem Komputerisasi Haji Terpadu Bidang Kesehatan <br>SISKOHATKES ARAB SAUDI</h4> -->
					 <h4 class="text-center text-white f-w-500 mb-0"><?= \yii\helpers\Html::encode(\app\components\SystemSettingHelper::get('login_title', 'AKSES SISTEM')) ?></h4>
				</div>
				<div class="card-body">

					<?php $form = ActiveForm::begin([
						'id' => 'login-form',
						// 'class' => 'md-float-material',	
						// 'layout' => 'horizontal',
						// 'options' => [
						// 	'class' => 'md-float-material'
						//  ],	

					]); ?>



					<?= $form->field($model, 'username', [
						'template' => "<div class=\"mb-3\">{input}\n{error}</div>",
						'errorOptions' => ['class' => 'invalid-feedback d-block'],
					])->textInput([
						'class' => 'form-control',
						'placeholder' => 'Username',
						'required' => true,
					])->label(false) ?>

					<div class="mb-3 position-relative">
						<?= $form->field($model, 'password', [
							'template' => "{input}\n{error}",
							'errorOptions' => ['class' => 'invalid-feedback d-block'],
						])->passwordInput([
							'id' => 'password',
							'class' => 'form-control pe-5',   // kasih ruang kanan untuk icon
							'placeholder' => 'Password',
							'required' => true,
						])->label(false) ?>

						<button type="button"
							class="btn btn-link position-absolute top-50 end-0 translate-middle-y me-2 p-0"
							id="togglePassword"
							aria-label="Toggle password">
							<i class="ti ti-eye"></i> <!-- kalau tabler-icons -->
						</button>
					</div>



					<?php
					$this->registerJs(<<<JS
			(function(){
			const input = document.getElementById('password');
			const btn   = document.getElementById('togglePassword');
			if(!input || !btn) return;

			btn.addEventListener('click', function(){
				const isPwd = input.getAttribute('type') === 'password';
				input.setAttribute('type', isPwd ? 'text' : 'password');

				// toggle icon (tabler-icons)
				const icon = btn.querySelector('i');
				if(icon){
				icon.classList.toggle('ti-eye', !isPwd);
				icon.classList.toggle('ti-eye-off', isPwd);
				}
			});
			})();
			JS);
					?>



					<?= $form->field($model, 'rememberMe', [
						'template' => '<div class="d-flex mt-1 justify-content-between align-items-center">
									<div class="form-check">
									{input}
									{label}
									{error}
									</div>
									<div>
										<a href="' . Url::to(['/forgot-password/request']) . '" class="text-primary text-decoration-underline small font-weight-bold">Lupa Password?</a>
									</div>
								</div>',
					])->checkbox([
						'class' => 'form-check-input input-primary',
						'id' => 'customCheckc1',
					], false) // <-- ini penting: false supaya label default Yii tidak "dibungkus" lagi
						->label('Remember me?', [
							'class' => 'form-check-label text-muted',
							'for' => 'customCheckc1',
						]) ?>


					<div class="mb-3">
						<div class="form-floating  position-relative" style="margin-bottom:8px;">
							<div style="width: 100%;">
								<img id="capimg"
									src="<?= Url::to(['site/captcha', 't' => time()]) ?>"
									alt="captcha"
									style="border-radius:6px;box-shadow:0 0 0 1px #ddd;width:70%;">
							</div>



						</div>


						<?php $isInvalid = ($model instanceof Model) && $model->hasErrors('verifyCode'); ?>
						<?= $form->field($model, 'verifyCode', [
							'options' => ['class' => 'form-floating mb-3'],     // wrapper = form-floating
							'template' => "{input}\n{label}\n{error}",          // urutan: input > label > error
							'inputOptions' => [
								'class' => 'form-control' . ($isInvalid ? ' is-invalid' : ''),
								'placeholder' => 'Kode Verifikasi',                         // wajib untuk form-floating
								// optional: samakan dengan label for
								'required' => true,
								'oninvalid' => "this.setCustomValidity('Captcha Harus Diisi')",
								'oninput' => "this.setCustomValidity('')",
								//'maxlength' => 16,
								// 'pattern' => '\d{16}',
								'inputmode' => 'text',
								'autocomplete' => 'off',
							],
							'labelOptions' => ['class' => 'form-label', 'for' => 'verifyCode'],
							'errorOptions' => ['class' => 'help-block'],  // agar nyambung dengan BS5
						])->textInput()->label('Masukkan kode di atas') ?>
					</div>






					<div class="form-group" style="text-align: left;">

						<div class="col-sm-12 col-xs-12">
							<?= Html::submitButton("Login", ['class' => 'btn btn-primary btn-login w-100', 'name' => 'login-button']) ?>
						</div>

					</div>

					<div class="form-group mt-3" style="text-align: center;">
						<a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#modal_panduan_teknis" class="text-primary font-weight-bold text-decoration-underline" style="font-size: 0.85rem;">
							<i class="ti ti-book me-1"></i>Panduan Teknis Penggunaan
						</a>
						<?php 
						$dashboardLink = \app\components\SystemSettingHelper::get('login_dashboard_link');
						if (!empty($dashboardLink)): 
						?>
							<span class="text-muted mx-2">|</span>
							<a href="<?= \yii\helpers\Html::encode($dashboardLink) ?>" class="text-primary font-weight-bold text-decoration-underline" style="font-size: 0.85rem;">
								<i class="ti ti-external-link me-1"></i>Buka Dashboard Web
							</a>
						<?php endif; ?>
					</div>

					<?php ActiveForm::end(); ?>



				</div>

				<div class="card-footer border-top">
					<div class="text-center">
						<p class="text-muted mb-0" style="font-size: 0.875rem; font-weight: 300;"><?= \yii\helpers\Html::encode(\app\components\SystemSettingHelper::get('footer_text', 'SIPKK 2026')) ?></p>
					</div>
				</div>

			</div>
		</div>
	</div>
</div>



<!-- Modal Lupa Pass -->
<div id="modal_lupa_pass" class="modal" role="dialog">
	<div class="modal-dialog">

		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">

				<h4 class="modal-title">Lupa Password Anda</h4>
			</div>
			<div class="modal-body">
				<p>Sedang Dalam pengembangan </p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>

	</div>
</div>

<!-- Modal Panduan Teknis -->
<div id="modal_panduan_teknis" class="modal fade" role="dialog" tabindex="-1" aria-labelledby="modalPanduanLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title font-weight-bold" id="modalPanduanLabel">
					<i class="ti ti-help me-1 text-primary"></i>Petunjuk Teknis Penggunaan
				</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
				<?= \app\components\SystemSettingHelper::get('frontend_technical_guidelines') ?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
			</div>
		</div>
	</div>
</div>
