<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\PuskesmasProfile */
/* @var $form yii\widgets\ActiveForm */
/* @var $wilayahService app\services\WilayahService */

$provinces = $wilayahService->getProvinsiOptions();
$provinceList = ArrayHelper::map($provinces, 'code', 'name');

$kabupatenList = [];
if (!empty($model->provinsi_id)) {
    $kabupatenList = ArrayHelper::map($wilayahService->getKabupatenOptions((string)$model->provinsi_id), 'code', 'name');
}

$kecamatanList = [];
if (!empty($model->kabupaten_id)) {
    $kecamatanList = ArrayHelper::map($wilayahService->getKecamatanOptions((string)$model->kabupaten_id), 'code', 'name');
}

$desaList = [];
if (!empty($model->kecamatan_id)) {
    $desaList = ArrayHelper::map($wilayahService->getDesaOptions((string)$model->kecamatan_id), 'code', 'name');
}
?>

<div class="puskesmas-profile-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="fw-bold">Informasi Utama</h5>
                </div>
                <div class="card-body">
                    <?= $form->field($model, 'kode_faskes')->textInput(['maxlength' => true, 'placeholder' => 'Contoh: P3201010101']) ?>

                    <?= $form->field($model, 'nama_puskesmas')->textInput(['maxlength' => true, 'placeholder' => 'Nama Puskesmas']) ?>

                    <?= $form->field($model, 'level_wilayah')->dropDownList([
                        'kabupaten' => 'Kabupaten/Kota',
                        'provinsi'  => 'Provinsi',
                    ], ['prompt' => '-- Pilih Level Wilayah --']) ?>

                    <?= $form->field($model, 'jumlah_penduduk')->textInput(['type' => 'number', 'min' => 0]) ?>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'kategori_wilayah')->dropDownList([
                                'Tidak Terpencil' => 'Tidak Terpencil',
                                'Terpencil' => 'Terpencil',
                                'Sangat Terpencil' => 'Sangat Terpencil',
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'kategori_jenis')->dropDownList([
                                'Perkotaan' => 'Perkotaan',
                                'Pedesaan' => 'Pedesaan',
                            ]) ?>
                        </div>
                    </div>

                     <?= $form->field($model, 'status_pelayanan')->dropDownList([
                        'Non Rawat Inap' => 'Non Rawat Inap',
                        'Rawat Inap' => 'Rawat Inap',
                    ]) ?>

                    <?= $form->field($model, 'status_akreditasi')->dropDownList([
                        'Belum Akreditasi' => 'Belum Akreditasi',
                        'Dasar' => 'Dasar',
                        'Madya' => 'Madya',
                        'Utama' => 'Utama',
                        'Paripurna' => 'Paripurna',
                    ]) ?>

                    <hr>
                    <h6 class="fw-bold text-teal mb-3">Persyaratan Bangunan & Lab</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <?= $form->field($model, 'bangunan_permanen')->checkbox() ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'bangunan_terpisah')->checkbox() ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'lab_tingkat1')->checkbox() ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="fw-bold">Wilayah & Lokasi</h5>
                </div>
                <div class="card-body">
                    <?= $form->field($model, 'provinsi_id')->dropDownList($provinceList, [
                        'prompt' => '-- Pilih Provinsi --',
                        'id' => 'provinsi-select',
                    ]) ?>

                    <?= $form->field($model, 'kabupaten_id')->dropDownList($kabupatenList, [
                        'prompt' => '-- Pilih Kabupaten/Kota --',
                        'id' => 'kabupaten-select',
                    ]) ?>

                    <?= $form->field($model, 'kecamatan_id')->dropDownList($kecamatanList, [
                        'prompt' => '-- Pilih Kecamatan --',
                        'id' => 'kecamatan-select',
                    ]) ?>

                    <?= $form->field($model, 'kelurahan_id')->dropDownList($desaList, [
                        'prompt' => '-- Pilih Desa/Kelurahan --',
                        'id' => 'desa-select',
                    ]) ?>
                </div>
            </div>
        </div>

        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="fw-bold">Persyaratan SDM Kesiapan Awal</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <?= $form->field($model, 'jumlah_nakes_medis')->textInput(['type' => 'number', 'min' => 0]) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'jumlah_nakes_paramedis')->textInput(['type' => 'number', 'min' => 0]) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'jumlah_nakes_penunjang')->textInput(['type' => 'number', 'min' => 0]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="fw-bold">Perizinan & Registrasi</h5>
                </div>
                <div class="card-body">
                    <?= $form->field($model, 'nomor_izin')->textInput(['maxlength' => true, 'placeholder' => 'Nomor SK/Izin Operasional']) ?>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'izin_berlaku_sampai')->textInput(['type' => 'date']) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'tanggal_registrasi')->textInput(['type' => 'date']) ?>
                        </div>
                    </div>

                    <?= $form->field($model, 'status_aktif')->checkbox() ?>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group text-end mt-3">
        <?= Html::a('Batal', ['index'], ['class' => 'btn btn-secondary me-2']) ?>
        <?= Html::submitButton('Simpan Profil', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
// Simple dynamic dynamic cascading dropdown logic without complex AJAX plugins
$provUrl = Url::to(['api/regions']);
$js = <<<JS
$('#provinsi-select').change(function(){
    var provId = $(this).val();
    $('#kabupaten-select').html('<option value="">Loading...</option>');
    $('#kecamatan-select').html('<option value="">-- Pilih Kecamatan --</option>');
    $('#desa-select').html('<option value="">-- Pilih Desa/Kelurahan --</option>');
    if(provId){
        $.get('$provUrl', {province_id: provId}, function(res){
            if(res.success){
                var options = '<option value="">-- Pilih Kabupaten/Kota --</option>';
                res.data.forEach(function(item){
                    options += '<option value="'+item.code+'">'+item.name+'</option>';
                });
                $('#kabupaten-select').html(options);
            }
        });
    } else {
        $('#kabupaten-select').html('<option value="">-- Pilih Kabupaten/Kota --</option>');
    }
});

$('#kabupaten-select').change(function(){
    var kabId = $(this).val();
    $('#kecamatan-select').html('<option value="">Loading...</option>');
    $('#desa-select').html('<option value="">-- Pilih Desa/Kelurahan --</option>');
    if(kabId){
        $.get('$provUrl', {kabupaten_id: kabId}, function(res){
            if(res.success){
                var options = '<option value="">-- Pilih Kecamatan --</option>';
                res.data.forEach(function(item){
                    options += '<option value="'+item.code+'">'+item.name+'</option>';
                });
                $('#kecamatan-select').html(options);
            }
        });
    } else {
        $('#kecamatan-select').html('<option value="">-- Pilih Kecamatan --</option>');
    }
});

$('#kecamatan-select').change(function(){
    var kecId = $(this).val();
    $('#desa-select').html('<option value="">Loading...</option>');
    if(kecId){
        $.get('$provUrl', {kecamatan_id: kecId}, function(res){
            if(res.success){
                var options = '<option value="">-- Pilih Desa/Kelurahan --</option>';
                res.data.forEach(function(item){
                    options += '<option value="'+item.code+'">'+item.name+'</option>';
                });
                $('#desa-select').html(options);
            }
        });
    } else {
        $('#desa-select').html('<option value="">-- Pilih Desa/Kelurahan --</option>');
    }
});
JS;
$this->registerJs($js);
?>
