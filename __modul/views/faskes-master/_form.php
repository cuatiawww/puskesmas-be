<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

$isTypeA = in_array($jenis, ['rs', 'puskesmas', 'klinik'], true);
?>

<div class="faskes-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form-faskes-master',
        'enableClientValidation' => true,
    ]); ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'nama')->textInput(['maxlength' => true, 'placeholder' => 'Nama Fasilitas Kesehatan']) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'kode_satusehat')->textInput(['maxlength' => true, 'placeholder' => 'Kode Satusehat']) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'kode_sarana')->textInput(['maxlength' => true, 'placeholder' => 'Kode Sarana / BPJS']) ?>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-md-6">
            <?= $form->field($model, 'alamat')->textarea(['rows' => 3, 'placeholder' => 'Alamat Lengkap']) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'telp')->textInput(['maxlength' => true, 'placeholder' => 'Nomor Telepon']) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'email')->textInput(['maxlength' => true, 'type' => 'email', 'placeholder' => 'Alamat Email']) ?>
        </div>
    </div>

    <h5 class="mt-4 mb-3 border-bottom pb-2 fw-semibold text-secondary">WILAYAH ADMINISTRASI</h5>

    <div class="row">
        <div class="col-md-3">
            <?php if ($isTypeA): ?>
                <?= $form->field($model, 'kode_prop')->dropDownList(
                    ArrayHelper::map($provinsiOptions, 'code', 'name'),
                    [
                        'prompt' => '-- Pilih Provinsi --',
                        'class' => 'form-select',
                        'disabled' => (!empty($scope['mode']) && $scope['mode'] !== 'all')
                    ]
                ) ?>
            <?php else: ?>
                <?= $form->field($model, 'kode_provinsi')->dropDownList(
                    ArrayHelper::map($provinsiOptions, 'code', 'name'),
                    [
                        'prompt' => '-- Pilih Provinsi --',
                        'class' => 'form-select',
                        'disabled' => (!empty($scope['mode']) && $scope['mode'] !== 'all')
                    ]
                ) ?>
            <?php endif; ?>
        </div>
        <div class="col-md-3">
            <?php if ($isTypeA): ?>
                <?= $form->field($model, 'kode_kab')->dropDownList(
                    ArrayHelper::map($kabupatenOptions, 'code', 'name'),
                    [
                        'prompt' => '-- Pilih Kabupaten/Kota --',
                        'class' => 'form-select',
                        'disabled' => (empty($kabupatenOptions) || (!empty($scope['mode']) && $scope['mode'] === 'kabupaten'))
                    ]
                ) ?>
            <?php else: ?>
                <?= $form->field($model, 'kode_kabkota')->dropDownList(
                    ArrayHelper::map($kabupatenOptions, 'code', 'name'),
                    [
                        'prompt' => '-- Pilih Kabupaten/Kota --',
                        'class' => 'form-select',
                        'disabled' => (empty($kabupatenOptions) || (!empty($scope['mode']) && $scope['mode'] === 'kabupaten'))
                    ]
                ) ?>
            <?php endif; ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'kode_kecamatan')->dropDownList(
                ArrayHelper::map($kecamatanOptions, 'code', 'name'),
                [
                    'prompt' => '-- Pilih Kecamatan --',
                    'class' => 'form-select',
                    'disabled' => empty($kecamatanOptions)
                ]
            ) ?>
        </div>
        <div class="col-md-3">
            <?php if (!$isTypeA): ?>
                <?= $form->field($model, 'kode_kelurahan')->dropDownList(
                    ArrayHelper::map($desaOptions, 'code', 'name'),
                    [
                        'prompt' => '-- Pilih Kelurahan/Desa --',
                        'class' => 'form-select',
                        'disabled' => empty($desaOptions)
                    ]
                ) ?>
            <?php endif; ?>
        </div>
    </div>

    <h5 class="mt-4 mb-3 border-bottom pb-2 fw-semibold text-secondary">STATUS & OPERASIONAL</h5>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'status_sarana')->dropDownList(
                [
                    'verified' => 'Verified',
                    'valid' => 'Valid',
                    'active' => 'Active',
                    'non-active' => 'Non-Active'
                ],
                ['prompt' => '-- Pilih Status --', 'class' => 'form-select']
            ) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'operasional')->dropDownList(
                [
                    1 => 'Operasional',
                    0 => 'Tidak Operasional'
                ],
                ['prompt' => '-- Pilih Status Operasional --', 'class' => 'form-select']
            ) ?>
        </div>
    </div>

    <div class="form-group mt-4 text-end">
        <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Batal', ['index'], ['class' => 'btn btn-outline-secondary rounded-pill px-3']) ?>
        <?= Html::submitButton('<i class="ti ti-device-floppy me-1"></i> Simpan Data', ['class' => 'btn btn-success rounded-pill px-4']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$getKabUrl = Url::to(['get-kabupaten']);
$getKecUrl = Url::to(['get-kecamatan']);
$getDesaUrl = Url::to(['get-desa']);

$js = <<<JS
(function($){
    var jenis = "$jenis";
    var isTypeA = (jenis === 'rs' || jenis === 'puskesmas' || jenis === 'klinik');
    
    var provSelectId = isTypeA ? '#faskesform-kode_prop' : '#faskesform-kode_provinsi';
    var kabSelectId = isTypeA ? '#faskesform-kode_kab' : '#faskesform-kode_kabkota';
    var kecSelectId = '#faskesform-kode_kecamatan';
    var desaSelectId = '#faskesform-kode_kelurahan';

    $(provSelectId).on('change', function() {
        var provCode = $(this).val();
        $(kabSelectId).html('<option value="">-- Pilih Kabupaten/Kota --</option>').prop('disabled', true).trigger('change');
        if (provCode) {
            $.ajax({
                url: '{$getKabUrl}',
                type: 'GET',
                data: { kode_provinsi: provCode },
                dataType: 'json',
                success: function(data) {
                    if (data && data.length > 0) {
                        $(kabSelectId).prop('disabled', false);
                        $.each(data, function(i, item) {
                            $(kabSelectId).append($('<option>', {
                                value: item.code,
                                text: item.name
                            }));
                        });
                    }
                }
            });
        }
    });

    $(kabSelectId).on('change', function() {
        var kabCode = $(this).val();
        $(kecSelectId).html('<option value="">-- Pilih Kecamatan --</option>').prop('disabled', true).trigger('change');
        if (kabCode) {
            $.ajax({
                url: '{$getKecUrl}',
                type: 'GET',
                data: { kode_kabkota: kabCode },
                dataType: 'json',
                success: function(data) {
                    if (data && data.length > 0) {
                        $(kecSelectId).prop('disabled', false);
                        $.each(data, function(i, item) {
                            $(kecSelectId).append($('<option>', {
                                value: item.code,
                                text: item.name
                            }));
                        });
                    }
                }
            });
        }
    });

    $(kecSelectId).on('change', function() {
        if (!isTypeA) {
            var kecCode = $(this).val();
            $(desaSelectId).html('<option value="">-- Pilih Kelurahan/Desa --</option>').prop('disabled', true);
            if (kecCode) {
                $.ajax({
                    url: '{$getDesaUrl}',
                    type: 'GET',
                    data: { kode_kecamatan: kecCode },
                    dataType: 'json',
                    success: function(data) {
                        if (data && data.length > 0) {
                            $(desaSelectId).prop('disabled', false);
                            $.each(data, function(i, item) {
                                $(desaSelectId).append($('<option>', {
                                    value: item.code,
                                    text: item.name
                                }));
                            });
                        }
                    }
                });
            }
        }
    });
})(jQuery);
JS;
$this->registerJs($js);
?>
