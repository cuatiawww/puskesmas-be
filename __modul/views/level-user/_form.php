<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\level_user\LevelUser $model */
/** @var yii\widgets\ActiveForm $form */

$isEdit = !$model->isNewRecord;
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
                                    <input type="text" class="form-control" id="nama_level" name="nama_level" required>
                                    <small class="form-text text-muted">Contoh: Super Admin, Admin, User</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                        <label class="form-check-label" for="is_active">Aktif</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="deskripsi" class="form-label">Deskripsi</label>
                                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"></textarea>
                                    <small class="form-text text-muted">Deskripsi singkat tentang level user ini</small>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 justify-content-end mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i> Simpan Level User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Hak Akses Section (only show if editing) -->
        <?php if ($isEdit): ?>
        <div class="col-md-12" id="hakAksesSection">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Pengaturan Hak Akses Modul & Sub Modul</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="ph-duotone ph-info me-2"></i>
                        <strong>Petunjuk:</strong> Gunakan kotak pencarian untuk mencari modul/sub modul. Atur izin View, Create, Update, dan Delete sesuai matriks hak akses.
                        Untuk Super Admin, semua akses otomatis diizinkan.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>

                    <div id="superAdminNotice" class="alert alert-warning d-none">
                        <i class="ph-duotone ph-warning me-2"></i>
                        <strong>Super Admin:</strong> Level ini memiliki akses penuh ke semua modul dan fitur secara otomatis.
                    </div>

                    <div id="modulList">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Memuat data modul...</p>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-success btn-simpan-hak-akses" id="btnSimpanHakAkses">
                            <i class="ti ti-check me-1"></i> Simpan Hak Akses
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
$viewUrl = Url::to(['level-user/view']);
$createUrl = Url::to(['level-user/create']);
$updateUrl = Url::to(['level-user/update']);
$getModulStructureUrl = Url::to(['level-user/get-modul-structure']);
$getHakAksesUrl = Url::to(['level-user/get-hak-akses']);
$saveHakAksesUrl = Url::to(['level-user/save-hak-akses']);
$indexUrl = Url::to(['level-user/index']);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->getCsrfToken();

$this->registerCss("
.modul-checkbox-item {
    padding: 0.625rem 0.75rem;
    border-radius: 0.375rem;
    transition: all 0.2s;
}

.modul-checkbox-item.checked {
    background-color: rgba(70, 128, 255, 0.1);
}

.permission-module-block + .permission-module-block {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
}

.permission-module-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}

.permission-submodule-list {
    margin-left: 1.25rem;
    padding-left: 1rem;
    border-left: 1px solid #e9ecef;
}

.permission-submodule-empty {
    margin-left: 1.25rem;
    color: #6c757d;
    font-size: 0.875rem;
}

.permission-label-main {
    font-weight: 600;
    color: #1f2937;
}

.permission-label-meta {
    display: block;
    margin-top: 0.125rem;
    color: #6b7280;
    font-size: 0.8125rem;
}

.permission-item-row {
    display: grid;
    grid-template-columns: minmax(180px, 1fr) repeat(4, 82px);
    gap: 0.75rem;
    align-items: center;
}

.permission-checks {
    display: contents;
}

.permission-check {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.25rem;
    margin: 0;
    font-size: 0.8125rem;
    color: #4b5563;
}

@media (max-width: 768px) {
    .permission-item-row {
        grid-template-columns: 1fr 1fr;
    }

    .permission-label-wrap {
        grid-column: 1 / -1;
    }
}

.input-group-lg .form-control {
    font-size: 1rem;
}
");

$this->registerJs("
var modulStructure = [];
var currentPermissions = {};
var dataLoadStatus = {
  modulStructure: false,
  hakAkses: false
};
var csrfParam = " . json_encode($csrfParam) . ";
var csrfToken = " . json_encode($csrfToken) . ";

function withCsrf(data) {
  data = data || {};
  data[csrfParam] = csrfToken;
  return data;
}

function loadLevelUserData(id) {
  $.ajax({
    url: '$viewUrl?id=' + id,
    method: 'GET',
    dataType: 'json',
    success: function(response) {
      if (response.success) {
        var data = response.data;
        $('#nama_level').val(data.nama_level);
        $('#deskripsi').val(data.deskripsi);
        $('#is_active').prop('checked', data.is_active);
      }
    },
    error: function() {
      Swal.fire('Error', 'Gagal mengambil data level user', 'error');
    }
  });
}

function simpanLevelUser() {
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
          text: 'Data level user berhasil disimpan'
        }).then(function() {
          // If creating new, redirect with ID to enable permissions section
          if (!formData.id && response.data && response.data.id) {
            window.location.href = '$updateUrl?id=' + response.data.id;
          } else {
            // Reload to show updated data
            window.location.reload();
          }
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
}

function checkAndRender() {
  // Only render when BOTH module structure and hak akses are loaded
  if (dataLoadStatus.modulStructure && dataLoadStatus.hakAkses) {
    console.log('Both data loaded, rendering with permissions:', currentPermissions);
    renderModulList();
  }
}

function loadModulStructure() {
  $.ajax({
    url: '$getModulStructureUrl',
    method: 'GET',
    dataType: 'json',
    success: function(response) {
      if (response.success) {
        modulStructure = response.data;
        dataLoadStatus.modulStructure = true;
        console.log('Module structure loaded');
        checkAndRender();
      }
    },
    error: function() {
      $('#modulList').html('<p class=\"text-danger\">Gagal memuat struktur modul</p>');
    }
  });
}

function loadHakAkses(levelUserId) {
  $.ajax({
    url: '$getHakAksesUrl?id=' + levelUserId,
    method: 'GET',
    dataType: 'json',
    success: function(response) {
      if (response.success) {
        // Store permissions in a map for easy lookup
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
        console.log('Hak akses loaded:', Object.keys(currentPermissions).length, 'permissions');
        checkAndRender();
      }
    },
    error: function() {
      console.error('Failed to load hak akses');
      dataLoadStatus.hakAkses = true; // Mark as complete even on error so UI can render
      checkAndRender();
    }
  });
}

function renderModulList() {
  if (modulStructure.length === 0) return;

  // Check if this is Super Admin
  var isSuperAdmin = $('#nama_level').val().toLowerCase().includes('super admin');
  if (isSuperAdmin) {
    $('#superAdminNotice').removeClass('d-none');
  }

  // Build search box
  var html = '<div class=\"mb-4\">';
  html += '<div class=\"input-group input-group-lg\">';
  html += '<span class=\"input-group-text bg-white\"><i class=\"ti ti-search\"></i></span>';
  html += '<input type=\"text\" class=\"form-control form-control-lg\" id=\"searchModul\" placeholder=\"Cari navigasi, modul, atau sub-modul...\">';
  html += '</div>';
  html += '</div>';

  html += '<div class=\"row g-3\">';

  modulStructure.forEach(function(navigasi) {
    var searchText = [
      navigasi.label || '',
      navigasi.nama_modul || '',
      collectNavigasiKeywords(navigasi)
    ].join(' ').toLowerCase();

    html += '<div class=\"col-md-12 modul-card\" data-search=\"' + escapeHtmlAttr(searchText) + '\">';
    html += '<div class=\"card\">';
    html += '<div class=\"card-header\">';
    html += '<div class=\"d-flex align-items-start justify-content-between gap-3\">';
    html += '<div>';
    html += '<h5 class=\"mb-1\"><i class=\"' + (navigasi.icon || 'ti ti-layout-sidebar-left-expand') + ' me-2\"></i>' + escapeHtml(navigasi.label) + '</h5>';
    html += '<div class=\"text-muted small\">Navigasi: ' + escapeHtml(navigasi.nama_modul || '-') + '</div>';
    html += '</div>';
    html += '<span class=\"badge text-bg-light\">' + ((navigasi.modules || []).length) + ' modul</span>';
    html += '</div>';
    html += '</div>';
    html += '<div class=\"card-body\">';

    if (navigasi.modules && navigasi.modules.length > 0) {
      navigasi.modules.forEach(function(modul) {
        html += renderModuleBlock(navigasi, modul, isSuperAdmin);
      });
    } else {
      html += '<div class=\"text-muted\">Belum ada modul di navigasi ini.</div>';
    }

    html += '</div></div></div>';
  });

  html += '</div>';

  $('#modulList').html(html);

  // Add search functionality
  $('#searchModul').on('keyup', function() {
    var searchValue = $(this).val().toLowerCase();

    if (searchValue === '') {
      $('.modul-card').show();
    } else {
      $('.modul-card').each(function() {
        var searchableText = ($(this).data('search') || '').toString();
        if (searchableText.includes(searchValue)) {
          $(this).show();
        } else {
          $(this).hide();
        }
      });
    }
  });

  $(document).on('change', '.permission-check-input', function() {
    var item = $(this).closest('.modul-checkbox-item');
    var isChecked = item.find('.permission-check-input:checked').length > 0;
    if (isChecked) {
      item.addClass('checked');
    } else {
      item.removeClass('checked');
    }
  });

  $(document).on('change', '.form-check-input[data-module-toggle]', function() {
    var isChecked = $(this).is(':checked');
    var moduleId = $(this).data('moduleToggle');
    $('.permission-check-input[data-parent-module=\"' + moduleId + '\"]:not(:disabled)').prop('checked', isChecked).trigger('change');
  });
}

function renderModuleBlock(navigasi, modul, isSuperAdmin) {
  var childCount = modul.children ? modul.children.length : 0;
  var moduleChecked = hasPermission(modul.id) || areAllChildrenChecked(modul.children || [], isSuperAdmin);
  var disabledAttr = isSuperAdmin ? 'disabled' : '';

  var html = '<div class=\"permission-module-block\">';
  html += '<div class=\"permission-module-title\">';
  html += renderPermissionCheckbox(modul.id, modul.label, modul.icon, {
    isSuperAdmin: isSuperAdmin,
    forceChecked: moduleChecked,
    modulId: navigasi.id,
    disabledAttr: disabledAttr
  });
  html += '<span class=\"badge text-bg-light\">' + childCount + ' sub-modul</span>';
  html += '</div>';

  if (childCount > 0) {
    html += '<div class=\"permission-submodule-list\">';
    modul.children.forEach(function(subModul) {
      html += renderPermissionCheckbox(subModul.id, subModul.label, subModul.icon, {
        isSuperAdmin: isSuperAdmin,
        modulId: navigasi.id,
        parentModuleId: modul.id,
        meta: subModul.route || 'Sub-modul',
      });
    });
    html += '</div>';
  } else {
    html += '<div class=\"permission-submodule-empty\">Modul ini belum memiliki sub-modul. Centang modul jika level user boleh mengakses modul ini.</div>';
  }

  html += '</div>';
  return html;
}

function renderPermissionCheckbox(itemId, label, icon, options) {
  options = options || {};
  var perm = currentPermissions[itemId] || { can_view: false, can_create: false, can_update: false, can_delete: false };

  var hasAccess = perm.can_view || perm.can_create || perm.can_update || perm.can_delete;
  if (options.forceChecked) {
    hasAccess = true;
    perm = { can_view: true, can_create: true, can_update: true, can_delete: true };
  }

  if (options.isSuperAdmin) {
    hasAccess = true;
    perm = { can_view: true, can_create: true, can_update: true, can_delete: true };
  }

  var disabledAttr = options.disabledAttr || (options.isSuperAdmin ? 'disabled' : '');
  var checkedClass = hasAccess ? 'checked' : '';
  var extraAttributes = options.extraAttributes ? ' ' + options.extraAttributes : '';
  var parentModuleAttr = options.parentModuleId ? ' data-parent-module=\"' + options.parentModuleId + '\"' : '';
  var modulIdAttr = options.modulId ? ' data-modul-id=\"' + options.modulId + '\"' : '';
  var itemName = label.toLowerCase();
  var meta = options.meta ? '<span class=\"permission-label-meta\">' + escapeHtml(options.meta) + '</span>' : '';

  var checks = [
    ['can_view', 'View'],
    ['can_create', 'Create'],
    ['can_update', 'Update'],
    ['can_delete', 'Delete']
  ];

  var html = '<div class=\"modul-checkbox-item mb-2 ' + checkedClass + '\" data-item-name=\"' + escapeHtmlAttr(itemName) + '\" data-submodul=\"' + itemId + '\"' + modulIdAttr + '>';
  html += '<div class=\"permission-item-row\">';
  html += '<div class=\"permission-label-wrap\">';
  html += '<span class=\"permission-label-main\"><i class=\"' + (icon || 'ti ti-point-filled') + ' me-2\"></i>' + escapeHtml(label) + '</span>';
  html += meta;
  html += '</div>';
  html += '<div class=\"permission-checks\">';
  checks.forEach(function(item) {
    var key = item[0];
    var text = item[1];
    var inputId = 'modul_' + itemId + '_' + key;
    html += '<label class=\"permission-check\" for=\"' + inputId + '\">';
    html += '<input class=\"form-check-input permission-check-input\" type=\"checkbox\" id=\"' + inputId + '\" data-permission=\"' + key + '\" data-submodul=\"' + itemId + '\"' + parentModuleAttr + extraAttributes + ' ' + (perm[key] ? 'checked' : '') + ' ' + disabledAttr + '>';
    html += '<span>' + text + '</span>';
    html += '</label>';
  });
  html += '</div>';
  html += '</div>';
  html += '</div>';

  return html;
}

function hasPermission(itemId) {
  var perm = currentPermissions[itemId] || { can_view: false };
  return perm.can_view || perm.can_create || perm.can_update || perm.can_delete;
}

function areAllChildrenChecked(children, isSuperAdmin) {
  if (!children || children.length === 0) {
    return false;
  }

  if (isSuperAdmin) {
    return true;
  }

  return children.every(function(child) {
    return hasPermission(child.id);
  });
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
    .replace(/\"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function escapeHtmlAttr(value) {
  return escapeHtml(value).replace(/`/g, '&#096;');
}

function simpanHakAkses() {
  var levelUserId = $('#level_user_id').val();
  if (!levelUserId) {
    Swal.fire('Error', 'Level user belum tersimpan', 'error');
    return;
  }

  // Check if this is Super Admin
  var isSuperAdmin = $('#nama_level').val().toLowerCase().includes('super admin');

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
    $('.modul-checkbox-item[data-submodul]').each(function() {
      var row = $(this);
      var subModulId = row.data('submodul');
      var modulId = row.data('modulId') || findNavigasiIdByModuleId(subModulId);
      var permission = {
        modul_id: modulId,
        sub_modul_id: subModulId,
        can_view: row.find('[data-permission=\"can_view\"]').is(':checked'),
        can_create: row.find('[data-permission=\"can_create\"]').is(':checked'),
        can_update: row.find('[data-permission=\"can_update\"]').is(':checked'),
        can_delete: row.find('[data-permission=\"can_delete\"]').is(':checked')
      };

      if (permission.can_view || permission.can_create || permission.can_update || permission.can_delete) {
        permissions.push(permission);
      }
    });
  }

  // Debug: Log data yang akan dikirim
  console.log('Saving hak akses for level_user_id:', levelUserId);
  console.log('Total permissions to save:', permissions.length);
  console.log('Permissions data:', permissions);

  // Show loading state
  var btnSimpan = $('#btnSimpanHakAkses');
  var originalText = btnSimpan.html();
  btnSimpan.prop('disabled', true).html('<i class=\"ti ti-loader me-1\"></i> Menyimpan...');

  $.ajax({
    url: '$saveHakAksesUrl',
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
      console.log('Backend response:', response);
      if (response.success) {
        // Update currentPermissions dengan data yang baru disimpan
        currentPermissions = {};
        permissions.forEach(function(perm) {
          if (perm.granted || perm.can_view || perm.can_create || perm.can_update || perm.can_delete) {
            currentPermissions[perm.sub_modul_id] = {
              can_view: Boolean(perm.can_view || perm.granted),
              can_create: Boolean(perm.can_create || perm.granted),
              can_update: Boolean(perm.can_update || perm.granted),
              can_delete: Boolean(perm.can_delete || perm.granted)
            };
          }
        });

        Swal.fire('Berhasil', 'Hak akses berhasil disimpan', 'success');
        console.log('Updated currentPermissions:', currentPermissions);
      } else {
        Swal.fire('Error', response.message || 'Gagal menyimpan hak akses', 'error');
        console.error('Save failed:', response);
      }
      btnSimpan.prop('disabled', false).html(originalText);
    },
    error: function(xhr) {
      Swal.fire('Error', 'Gagal menyimpan hak akses', 'error');
      console.error('AJAX Error:', xhr.status, xhr.responseText);
      btnSimpan.prop('disabled', false).html(originalText);
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

// Initialize on document ready
$(document).ready(function() {
  var levelUserId = $('#level_user_id').val();

  // Load data if editing
  if (levelUserId) {
    loadLevelUserData(levelUserId);
    // Load both module structure and permissions before rendering
    loadModulStructure();
    loadHakAkses(levelUserId);
  }

  // Handle form submit
  $('#formLevelUser').on('submit', function(e) {
    e.preventDefault();
    simpanLevelUser();
  });

  // Bind click handler for save permissions button
  $('#btnSimpanHakAkses').on('click', function(e) {
    e.preventDefault();
    simpanHakAkses();
  });
});
");
?>
