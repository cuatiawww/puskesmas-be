<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\level_user\LevelUser $model */

$this->title = 'Pengaturan Hak Akses: ' . $model->nama_level;
$this->params['breadcrumbs'][] = ['label' => 'Akses', 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => 'Level User', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Hak Akses';
?>

<div class="level-user-hak-akses">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center justify-content-between">
                <div class="col-sm-auto">
                    <div class="page-header-title">
                        <h5 class="mb-0 font-weight-600 fw-bold">PENGATURAN HAK AKSES</h5>
                        <p>Kelola Otorisasi Fitur untuk Level: <span class="badge bg-light-primary text-primary fs-6" id="nama_level_display"><?= Html::encode($model->nama_level) ?></span></p>
                    </div>
                </div>
                <div class="col-sm-auto mt-sm-0 mt-3">
                    <?= Html::a('<i class="ti ti-arrow-left me-1"></i> Kembali', ['index'], ['class' => 'btn btn-secondary']) ?>
                    <button type="button" class="btn btn-success ms-2" id="btnSimpanHakAkses">
                        <i class="ti ti-device-floppy me-1"></i> Simpan Hak Akses
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <input type="hidden" id="level_user_id" value="<?= $model->id ?>">

        <div class="col-md-12">
            <div class="card border shadow-none">
                <div class="card-header bg-light">
                    <h5 class="mb-0 text-dark"><i class="ph-duotone ph-lock-key me-2 text-primary fs-5 align-middle"></i> Matriks Hak Akses Modul &amp; Sub-Modul</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                        <i class="ph-duotone ph-info me-3 fs-3 text-info"></i>
                        <div>
                            <strong>Panduan Pengaturan:</strong> Atur perizinan untuk setiap modul/sub-modul. 
                            Anda dapat mencentang kolom **Semua** untuk langsung mengaktifkan semua perizinan pada baris tersebut.
                        </div>
                    </div>

                    <div id="superAdminNotice" class="alert alert-warning d-none mb-4">
                        <i class="ph-duotone ph-warning me-3 fs-3 text-warning"></i>
                        <div>
                            <strong>Super Admin:</strong> Peran ini memiliki akses penuh ke seluruh sistem secara otomatis. Seluruh kontrol perizinan di bawah ini ter-centang &amp; terkunci.
                        </div>
                    </div>

                    <!-- Modul List Container -->
                    <div id="modulList">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Memuat data struktur modul &amp; hak akses...</p>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <?= Html::a('Batal', ['index'], ['class' => 'btn btn-secondary']) ?>
                        <button type="button" class="btn btn-success px-4" id="btnSimpanHakAksesBawah">
                            <i class="ti ti-device-floppy me-1"></i> Simpan Hak Akses
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$getModulStructureUrl = Url::to(['level-user/get-modul-structure']);
$getHakAksesUrl = Url::to(['level-user/get-hak-akses']);
$saveHakAksesUrl = Url::to(['level-user/save-hak-akses']);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->getCsrfToken();

$this->registerJs(<<<JS
var modulStructure = [];
var currentPermissions = {};
var dataLoadStatus = {
  modulStructure: false,
  hakAkses: false
};
var csrfParam = '{$csrfParam}';
var csrfToken = '{$csrfToken}';

function withCsrf(data) {
  data = data || {};
  data[csrfParam] = csrfToken;
  return data;
}

function checkAndRender() {
  if (dataLoadStatus.modulStructure && dataLoadStatus.hakAkses) {
    renderModulList();
  }
}

function loadModulStructure() {
  $.ajax({
    url: '{$getModulStructureUrl}',
    method: 'GET',
    dataType: 'json',
    success: function(response) {
      if (response.success) {
        modulStructure = response.data;
        dataLoadStatus.modulStructure = true;
        checkAndRender();
      }
    },
    error: function() {
      $('#modulList').html('<p class="text-danger p-4 text-center">Gagal memuat struktur modul.</p>');
    }
  });
}

function loadHakAkses(levelUserId) {
  $.ajax({
    url: '{$getHakAksesUrl}?id=' + levelUserId,
    method: 'GET',
    dataType: 'json',
    success: function(response) {
      if (response.success) {
        currentPermissions = {};
        response.data.forEach(function(perm) {
          var key = perm.sub_modul_id;
          currentPermissions[key] = {
            can_view: Boolean(perm.can_view),
            can_create: Boolean(perm.can_create),
            can_update: Boolean(perm.can_update),
            can_delete: Boolean(perm.can_delete)
          };
        });
        dataLoadStatus.hakAkses = true;
        checkAndRender();
      }
    },
    error: function() {
      dataLoadStatus.hakAkses = true;
      checkAndRender();
    }
  });
}

function renderModulList() {
  if (modulStructure.length === 0) return;

  var isSuperAdmin = $('#nama_level_display').text().toLowerCase().includes('super admin');
  if (isSuperAdmin) {
    $('#superAdminNotice').removeClass('d-none');
  }

  var html = '<div class="mb-4">';
  html += '  <div class="input-group input-group-lg border rounded">';
  html += '    <span class="input-group-text bg-white border-0"><i class="ti ti-search text-muted"></i></span>';
  html += '    <input type="text" class="form-control form-control-lg border-0 ps-0" id="searchModul" placeholder="Cari navigasi, modul, atau sub-modul...">';
  html += '  </div>';
  html += '</div>';

  html += '<div class="row g-4">';

  modulStructure.forEach(function(navigasi) {
    var searchText = [
      navigasi.label || '',
      navigasi.nama_modul || '',
      collectNavigasiKeywords(navigasi)
    ].join(' ').toLowerCase();

    html += '<div class="col-md-12 modul-card" data-search="' + escapeHtmlAttr(searchText) + '">';
    html += '  <div class="card border shadow-none mb-0">';
    html += '    <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">';
    html += '      <h6 class="mb-0 fw-bold text-dark"><i class="' + (navigasi.icon || 'ph-duotone ph-navigation') + ' me-2 text-primary fs-5 align-middle"></i>' + escapeHtml(navigasi.label) + '</h6>';
    html += '      <span class="badge bg-light-primary text-primary">' + ((navigasi.modules || []).length) + ' modul</span>';
    html += '    </div>';
    html += '    <div class="card-body p-0">';
    html += '      <div class="table-responsive">';
    html += '        <table class="table table-bordered table-hover align-middle mb-0" style="border-color: #f1f5f9;">';
    html += '          <thead style="background: #4680ff; color: #ffffff;">';
    html += '            <tr>';
    html += '              <th style="width: 45%; font-weight: 600; color: #ffffff;" class="ps-4 py-3">Modul / Fitur</th>';
    html += '              <th class="text-center" style="width: 10%; font-weight: 600; color: #ffffff; padding: 12px 0;">View</th>';
    html += '              <th class="text-center" style="width: 10%; font-weight: 600; color: #ffffff; padding: 12px 0;">Create</th>';
    html += '              <th class="text-center" style="width: 10%; font-weight: 600; color: #ffffff; padding: 12px 0;">Update</th>';
    html += '              <th class="text-center" style="width: 10%; font-weight: 600; color: #ffffff; padding: 12px 0;">Delete</th>';
    html += '              <th class="text-center" style="width: 15%; font-weight: 600; color: #ffffff; padding: 12px 0;">Semua</th>';
    html += '            </tr>';
    html += '          </thead>';
    html += '          <tbody>';

    if (navigasi.modules && navigasi.modules.length > 0) {
      navigasi.modules.forEach(function(modul) {
        html += renderTableRow(modul, false, isSuperAdmin);
        if (modul.children && modul.children.length > 0) {
          modul.children.forEach(function(subModul) {
            html += renderTableRow(subModul, true, isSuperAdmin);
          });
        }
      });
    } else {
      html += '      <tr><td colspan="6" class="text-center text-muted p-4">Belum ada modul di kategori navigasi ini.</td></tr>';
    }

    html += '          </tbody>';
    html += '        </table>';
    html += '      </div>';
    html += '    </div>';
    html += '  </div>';
    html += '</div>';
  });

  html += '</div>';

  $('#modulList').html(html);

  // Search filter
  $('#searchModul').on('keyup', function() {
    var val = $(this).val().toLowerCase();
    if (val === '') {
      $('.modul-card').show();
    } else {
      $('.modul-card').each(function() {
        var txt = ($(this).data('search') || '').toString();
        if (txt.includes(val)) {
          $(this).show();
        } else {
          $(this).hide();
        }
      });
    }
  });
}

function renderTableRow(item, isSubmodul, isSuperAdmin) {
  var perm = currentPermissions[item.id] || { can_view: false, can_create: false, can_update: false, can_delete: false };
  if (isSuperAdmin) {
    perm = { can_view: true, can_create: true, can_update: true, can_delete: true };
  }

  var disabledAttr = isSuperAdmin ? 'disabled' : '';
  var allChecked = perm.can_view && perm.can_create && perm.can_update && perm.can_delete;

  var html = '<tr class="permission-row-item" data-submodul="' + item.id + '">';
  
  html += '  <td class="ps-4">';
  if (isSubmodul) {
    html += '    <span style="padding-left: 20px;" class="d-inline-block text-dark fw-semibold">';
    html += '      <i class="ti ti-corner-down-right me-2 text-secondary"></i>';
    html += '      <i class="' + (item.icon || 'ph-duotone ph-link') + ' me-2 text-primary"></i>';
    html += '      ' + escapeHtml(item.label);
    html += '    </span>';
    if (item.route) {
      html += '    <small class="d-block text-secondary" style="padding-left: 42px;">' + escapeHtml(item.route) + '</small>';
    }
  } else {
    html += '    <span class="fw-bold text-dark">';
    html += '      <i class="' + (item.icon || 'ph-duotone ph-squares-four') + ' me-2 text-primary fs-5 align-middle"></i>';
    html += '      ' + escapeHtml(item.label);
    html += '    </span>';
  }
  html += '  </td>';

  var checks = ['can_view', 'can_create', 'can_update', 'can_delete'];
  checks.forEach(function(key) {
    var inputId = 'modul_' + item.id + '_' + key;
    html += '  <td class="text-center">';
    html += '    <div class="form-check d-inline-block m-0">';
    html += '      <input class="form-check-input permission-check-input" type="checkbox" id="' + inputId + '" data-permission="' + key + '" data-submodul="' + item.id + '" ' + (perm[key] ? 'checked' : '') + ' ' + disabledAttr + '>';
    html += '    </div>';
    html += '  </td>';
  });

  html += '  <td class="text-center bg-light-subtle">';
  html += '    <div class="form-check d-inline-block m-0">';
  html += '      <input class="form-check-input row-toggle-all" type="checkbox" ' + (allChecked ? 'checked' : '') + ' ' + disabledAttr + '>';
  html += '    </div>';
  html += '  </td>';

  html += '</tr>';
  return html;
}

// Master checkbox row toggle
$(document).on('change', '.row-toggle-all', function() {
   var isChecked = $(this).is(':checked');
   var tr = $(this).closest('tr');
   tr.find('.permission-check-input:not(:disabled)').prop('checked', isChecked);
});

// Individual checkbox change triggers update of "Semua" master checkbox
$(document).on('change', '.permission-check-input', function() {
   var tr = $(this).closest('tr');
   var totalChecks = tr.find('.permission-check-input').length;
   var checkedCount = tr.find('.permission-check-input:checked').length;
   tr.find('.row-toggle-all').prop('checked', totalChecks === checkedCount);
});

function simpanHakAkses() {
  var levelUserId = $('#level_user_id').val();
  var isSuperAdmin = $('#nama_level_display').text().toLowerCase().includes('super admin');
  var permissions = [];

  if (isSuperAdmin) {
    modulStructure.forEach(function(navigasi) {
      (navigasi.modules || []).forEach(function(modul) {
        permissions.push({
          modul_id: navigasi.id,
          sub_modul_id: modul.id,
          granted: true
        });

        (modul.children || []).forEach(function(subModul) {
          permissions.push({
            modul_id: navigasi.id,
            sub_modul_id: subModul.id,
            granted: true
          });
        });
      });
    });
  } else {
    $('.permission-row-item').each(function() {
      var row = $(this);
      var subModulId = row.data('submodul');
      var modulId = findNavigasiIdByModuleId(subModulId);
      
      var permission = {
        modul_id: modulId,
        sub_modul_id: subModulId,
        can_view: row.find('[data-permission="can_view"]').is(':checked'),
        can_create: row.find('[data-permission="can_create"]').is(':checked'),
        can_update: row.find('[data-permission="can_update"]').is(':checked'),
        can_delete: row.find('[data-permission="can_delete"]').is(':checked')
      };

      if (permission.can_view || permission.can_create || permission.can_update || permission.can_delete) {
        permissions.push(permission);
      }
    });
  }

  var btnSimpan = $('#btnSimpanHakAkses');
  var btnSimpanBawah = $('#btnSimpanHakAksesBawah');
  btnSimpan.prop('disabled', true).html('<i class="ti ti-loader me-1"></i> Menyimpan...');
  btnSimpanBawah.prop('disabled', true).html('<i class="ti ti-loader me-1"></i> Menyimpan...');

  $.ajax({
    url: '{$saveHakAksesUrl}',
    method: 'POST',
    data: withCsrf({
      level_user_id: levelUserId,
      permissions: permissions
    }),
    headers: {
      'X-CSRF-Token': csrfToken
    },
    dataType: 'json',
    success: function(response) {
      if (response.success) {
        Swal.fire({
          icon: 'success',
          title: 'Berhasil',
          text: 'Hak akses berhasil disimpan.'
        }).then(function() {
          window.location.reload();
        });
      } else {
        Swal.fire('Error', response.message || 'Gagal menyimpan hak akses', 'error');
      }
      btnSimpan.prop('disabled', false).html('<i class="ti ti-device-floppy me-1"></i> Simpan Hak Akses');
      btnSimpanBawah.prop('disabled', false).html('<i class="ti ti-device-floppy me-1"></i> Simpan Hak Akses');
    },
    error: function(xhr) {
      Swal.fire('Error', 'Gagal menyimpan hak akses', 'error');
      btnSimpan.prop('disabled', false).html('<i class="ti ti-device-floppy me-1"></i> Simpan Hak Akses');
      btnSimpanBawah.prop('disabled', false).html('<i class="ti ti-device-floppy me-1"></i> Simpan Hak Akses');
    }
  });
}

function findNavigasiIdByModuleId(moduleId) {
  var found = null;
  modulStructure.some(function(navigasi) {
    return (navigasi.modules || []).some(function(modul) {
      if (String(modul.id) === String(moduleId)) {
        found = navigasi.id;
        return true;
      }
      return false;
    });
  });
  return found;
}

function collectNavigasiKeywords(navigasi) {
  var texts = [];
  (navigasi.modules || []).forEach(function(modul) {
    texts.push(modul.label || '');
    texts.push(modul.nama_sub_modul || '');
    (modul.children || []).forEach(function(subModul) {
      texts.push(subModul.label || '');
      texts.push(subModul.nama_sub_modul || '');
      texts.push(subModul.route || '');
    });
  });
  return texts.join(' ');
}

function escapeHtml(value) {
  return String(value || '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function escapeHtmlAttr(value) {
  return escapeHtml(value).replace(/`/g, '&#096;');
}

$(document).ready(function() {
  var levelUserId = $('#level_user_id').val();
  if (levelUserId) {
    loadModulStructure();
    loadHakAkses(levelUserId);
  }

  $('#btnSimpanHakAkses, #btnSimpanHakAksesBawah').on('click', function(e) {
    e.preventDefault();
    simpanHakAkses();
  });
});
JS
);
?>
