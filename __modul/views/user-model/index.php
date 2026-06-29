<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Data User';
$this->params['active_menu'] = 'data-user';
$this->params['breadcrumbs'][] = ['label' => 'Akses', 'url' => ['#']];
$this->params['breadcrumbs'][] = $this->title;

$swal = Yii::$app->session->getFlash('swal', null);
if ($swal) {
    $swalJson = \yii\helpers\Json::encode($swal);
    $this->registerJs(<<<JS
  var opt = {$swalJson};
  if (typeof Swal !== 'undefined') {
    Swal.fire(opt);
  }
JS);
}
?>

<div class="user-model-index">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center justify-content-between">
                <div class="col-sm-auto">
                    <div class="page-header-title">
                        <h5 class="mb-0 font-weight-600 fw-bold">DATA USER</h5>
                    </div>
                    <p>Tatakelola Data User Sistem</p>
                </div>
                <div class="d-flex">
                    <a class="btn btn-sm btn-primary rounded-pill px-2" role="button" href="javascript:void(0);">
                        <i class="ti ti-external-link me-1"></i> Info Selengkapnya
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Cards -->
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card bg-light-blue">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">⚙️ Setup Hak Akses</h6>
                            <p class="mb-0 text-muted">Kelola permission user per level</p>
                        </div>
                        <?= Html::a('<i class="ti ti-arrow-right"></i>', ['level-user/index'], ['class' => 'btn btn-sm btn-primary']) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-light-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">🎖️ Kelola Level User</h6>
                            <p class="mb-0 text-muted">Tambah atau edit level akses user</p>
                        </div>
                        <?= Html::a('<i class="ti ti-arrow-right"></i>', ['level-user/index'], ['class' => 'btn btn-sm btn-info']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar User</h5>
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Tambah User', ['create'], ['class' => 'btn btn-primary']) ?>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="table-user">
                            <thead>
                                <tr>
                                    <th>NO</th>
                                    <th>USERNAME</th>
                                    <th>EMAIL</th>
                                    <th>LEVEL USER</th>
                                    <th>PROVINSI</th>
                                    <th>KABUPATEN/KOTA</th>
                                    <th class="text-center">STATUS</th>
                                    <th class="text-center">AKSI</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-user">
                                <tr>
                                        <td colspan="8" class="text-center">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DataTables CSS & JS -->
<?php
$this->registerCssFile('https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css', ['depends' => [\yii\web\JqueryAsset::class]]);
$this->registerJsFile('https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);
$this->registerJsFile('https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);
?>

<?php
$this->registerCss("
/* DataTable Custom Styling */
.dataTables_wrapper .dataTables_length select {
    padding: 0.375rem 2rem 0.375rem 0.75rem;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
}

.dataTables_wrapper .dataTables_filter input {
    padding: 0.375rem 0.75rem;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    margin-left: 0.5rem;
}

.dataTables_wrapper .dataTables_paginate .paginate_button {
    padding: 0.375rem 0.75rem;
    margin: 0 0.125rem;
    border-radius: 0.375rem;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: #4680ff !important;
    color: white !important;
    border-color: #4680ff !important;
}

.dataTables_wrapper .dataTables_info {
    padding-top: 0.85rem;
}

/* Table hover effect */
#table-user tbody tr:hover {
    background-color: rgba(70, 128, 255, 0.05) !important;
}
");

$listUrl = Url::to(['user-model/get-list']);
$deleteUrl = Url::to(['user-model/delete']);
$updateUrl = Url::to(['user-model/update']);

$this->registerJs("
let userTable = null;
let deleteUrl = '" . $deleteUrl . "';
let listUrl = '" . $listUrl . "';
let updateUrl = '" . $updateUrl . "';

function initUserTable() {
  if ($.fn.DataTable.isDataTable('#table-user')) {
    $('#table-user').DataTable().destroy();
  }

  userTable = $('#table-user').DataTable({
    processing: true,
    serverSide: true,
    deferRender: true,
    pageLength: 10,
    lengthMenu: [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]],
    order: [[0, 'desc']],
    ajax: {
      url: listUrl,
      type: 'GET',
      dataSrc: function(response) {
        if (!response || response.success === false) {
          const message = response && response.message ? response.message : 'Gagal memuat data';
          $('#tbody-user').html('<tr><td colspan=\"8\" class=\"text-center text-danger\">' + message + '</td></tr>');
          return [];
        }
        return response.data || [];
      }
    },
    columns: [
      {
        data: 'id',
        render: function(data, type, row, meta) {
          if (type !== 'display') {
            return data;
          }
          return meta.row + meta.settings._iDisplayStart + 1;
        }
      },
      { data: 'username', defaultContent: '-' },
      { data: 'email', defaultContent: '-' },
      {
        data: 'level_user_nama',
        defaultContent: '-',
        render: function(data) {
          return data || '-';
        }
      },
      {
        data: 'provinsi_nama',
        defaultContent: '-',
        render: function(data) {
          return data || '-';
        }
      },
      {
        data: 'kabupaten_nama',
        defaultContent: '-',
        render: function(data) {
          return data || '-';
        }
      },
      {
        data: 'is_active',
        className: 'text-center',
        render: function(data) {
          return data ? '<span class=\"badge bg-light-success\">Aktif</span>' : '<span class=\"badge bg-light-danger\">Nonaktif</span>';
        }
      },
      {
        data: 'id',
        orderable: false,
        searchable: false,
        className: 'text-center',
        render: function(data) {
          return `
            <a href=\"${updateUrl}?id=\${data}\" class=\"btn btn-sm btn-warning\" title=\"Edit\">
              <i class=\"ti ti-edit\"></i>
            </a>
            <button class=\"btn btn-sm btn-danger\" onclick=\"hapusUser(\${data})\" title=\"Hapus\">
              <i class=\"ti ti-trash\"></i>
            </button>
          `;
        }
      }
    ],
    language: {
      processing: 'Memuat data...',
      search: 'Cari:',
      lengthMenu: 'Tampilkan _MENU_ data',
      info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ user',
      infoEmpty: 'Menampilkan 0 sampai 0 dari 0 user',
      infoFiltered: '(difilter dari _MAX_ total data)',
      paginate: {
        first: 'Pertama',
        last: 'Terakhir',
        next: 'Selanjutnya',
        previous: 'Sebelumnya'
      },
      zeroRecords: 'Data tidak ditemukan',
      emptyTable: 'Tidak ada data yang tersedia'
    }
  });
}

function hapusUser(id) {
  Swal.fire({
    title: 'Apakah Anda yakin?',
    text: 'Data user akan dihapus',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      console.log('Sending delete request for ID:', id);
      console.log('Delete URL:', deleteUrl);
      
      $.ajax({
        url: deleteUrl,
        type: 'POST',
        data: {
          id: id
        },
        dataType: 'json',
        success: function(response) {
          console.log('Delete response:', response);
          if (response && response.success) {
            Swal.fire('Berhasil!', 'Data berhasil dihapus', 'success');
            if (userTable) {
              userTable.ajax.reload(null, false);
            }
          } else {
            Swal.fire('Gagal!', (response && response.message) ? response.message : 'Gagal menghapus data', 'error');
          }
        },
        error: function(xhr, status, error) {
          console.log('Delete error:', {status, error, xhr});
          console.log('Response text:', xhr.responseText);
          let errorMsg = 'Gagal menghapus data';
          try {
            let resp = JSON.parse(xhr.responseText);
            if (resp && resp.message) {
              errorMsg = resp.message;
            }
            console.log('Parsed error response:', resp);
          } catch(e) {
            console.log('Could not parse response as JSON:', e);
          }
          Swal.fire('Gagal!', errorMsg + ' (Status: ' + xhr.status + ')', 'error');
        }
      });
    }
  });
}

// Load data saat halaman pertama kali dibuka
$(document).ready(function() {
  initUserTable();
});

// Ensure functions are in global scope
window.initUserTable = initUserTable;
window.hapusUser = hapusUser;
");
?>
