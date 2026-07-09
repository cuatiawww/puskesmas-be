<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\level_user\LevelUser $model */
?>

<div class="level-user-form">
    <div class="row">
        <!-- Level User Info -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informasi Level User</h5>
                </div>
                <div class="card-body">
                    <form id="formLevelUser">
                        <input type="hidden" id="level_user_id" name="id" value="<?= $model->id ?>">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nama_level" class="form-label">Nama Level <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nama_level" name="nama_level" value="<?= Html::encode($model->nama_level) ?>" required>
                                    <small class="form-text text-muted">Contoh: Super Admin, Admin, User</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?= $model->isNewRecord || $model->is_active ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="is_active">Aktif</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="deskripsi" class="form-label">Deskripsi</label>
                                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?= Html::encode($model->deskripsi) ?></textarea>
                                    <small class="form-text text-muted">Deskripsi singkat tentang level user ini</small>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 justify-content-end mt-3">
                            <?= Html::a('Kembali', ['index'], ['class' => 'btn btn-secondary']) ?>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i> Simpan Level User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$createUrl = Url::to(['level-user/create']);
$updateUrl = Url::to(['level-user/update']);
$indexUrl = Url::to(['level-user/index']);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->getCsrfToken();

$this->registerJs("
var csrfParam = " . json_encode($csrfParam) . ";
var csrfToken = " . json_encode($csrfToken) . ";

function withCsrf(data) {
  data = data || {};
  data[csrfParam] = csrfToken;
  return data;
}

$('#formLevelUser').on('submit', function(e) {
  e.preventDefault();
  
  var formData = {
    id: $('#level_user_id').val(),
    nama_level: $('#nama_level').val(),
    deskripsi: $('#deskripsi').val(),
    is_active: $('#is_active').is(':checked') ? 1 : 0
  };

  var url = formData.id
    ? '$updateUrl?id=' + formData.id
    : '$createUrl';

  $.ajax({
    url: url,
    method: 'POST',
    data: withCsrf(formData),
    headers: {
      'X-CSRF-Token': csrfToken
    },
    dataType: 'json',
    success: function(response) {
      if (response.success) {
        Swal.fire({
          icon: 'success',
          title: 'Berhasil',
          text: response.message || 'Data level user berhasil disimpan'
        }).then(function() {
          window.location.href = '$indexUrl';
        });
      } else {
        Swal.fire('Error', response.message || 'Gagal menyimpan data', 'error');
      }
    },
    error: function(xhr) {
      Swal.fire('Error', 'Gagal menyimpan data', 'error');
      console.error(xhr.responseText);
    }
  });
});
");
?>
