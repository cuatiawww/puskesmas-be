<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Level User';
$this->params['breadcrumbs'][] = ['label' => 'Akses', 'url' => ['#']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="level-user-index">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center justify-content-between">
                <div class="col-sm-auto">
                    <div class="page-header-title">
                        <h5 class="mb-0 font-weight-600 fw-bold">LEVEL USER</h5>
                    </div>
                    <p>Tatakelola Level & Hak Akses User</p>
                </div>
                <div class="d-flex">
                    <a class="btn btn-sm btn-primary rounded-pill px-2" role="button" href="javascript:void(0);">
                        <i class="ti ti-external-link me-1"></i> Info Selengkapnya
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar Level User</h5>
                    <?= Html::a('<i class="ti ti-plus me-1"></i> Tambah Level User', ['create'], ['class' => 'btn btn-primary']) ?>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="table-level-user">
                            <thead>
                                <tr>
                                    <th>NO</th>
                                    <th>NAMA LEVEL</th>
                                    <th>DESKRIPSI</th>
                                    <th class="text-center">JUMLAH USER</th>
                                    <th class="text-center">STATUS</th>
                                    <th class="text-center">AKSI</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-level-user">
                                <tr>
                                    <td colspan="6" class="text-center">
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
#table-level-user tbody tr:hover {
    background-color: rgba(70, 128, 255, 0.05) !important;
}
");

$listUrl = Url::to(['level-user/get-list']);
$deleteUrl = Url::to(['level-user/delete']);
$updateUrl = Url::to(['level-user/update']);

$this->registerJs("
// Load data level user dari backend Yii
window.loadLevelUser = function() {
  // Destroy existing DataTable if exists
  if ($.fn.DataTable.isDataTable('#table-level-user')) {
    $('#table-level-user').DataTable().destroy();
  }

  $.ajax({
    url: '$listUrl',
    method: 'GET',
    dataType: 'json',
    success: function(response) {
      let html = '';
      if (response.data && response.data.length > 0) {
        response.data.forEach(function(item, index) {
          html += `
            <tr>
              <td>\${index + 1}</td>
              <td>\${item.nama_level}</td>
              <td>\${item.deskripsi || '-'}</td>
              <td class=\"text-center\">\${item.jumlah_user}</td>
              <td class=\"text-center\">
                \${item.is_active ? '<span class=\"badge bg-light-success\">Aktif</span>' : '<span class=\"badge bg-light-danger\">Nonaktif</span>'}
              </td>
              <td class=\"text-center\">
                <a href=\"$updateUrl?id=\${item.id}\" class=\"btn btn-sm btn-warning\" title=\"Edit\">
                  <i class=\"ti ti-edit\"></i>
                </a>
                <button class=\"btn btn-sm btn-danger\" onclick=\"hapusLevelUser(\${item.id})\" title=\"Hapus\">
                  <i class=\"ti ti-trash\"></i>
                </button>
              </td>
            </tr>
          `;
        });
      } else {
        html = '<tr><td colspan=\"6\" class=\"text-center\">Tidak ada data</td></tr>';
      }
      $('#tbody-level-user').html(html);

      // Initialize DataTables after data is loaded
      setTimeout(function() {
        $('#table-level-user').DataTable({
          pageLength: 10,
          lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, \"Semua\"]],
          order: [[1, 'asc']], // Order by nama level
          columnDefs: [
            { orderable: true, targets: [0, 1, 2, 3, 4] },
            { orderable: false, targets: 5 }, // Actions column not sortable
            { searchable: true, targets: [1, 2] }, // Search in nama and deskripsi
            { searchable: false, targets: [0, 3, 4, 5] }
          ],
          language: {
            search: \"Cari:\",
            lengthMenu: \"Tampilkan _MENU_ data\",
            info: \"Menampilkan _START_ sampai _END_ dari _TOTAL_ level user\",
            infoEmpty: \"Menampilkan 0 sampai 0 dari 0 level user\",
            infoFiltered: \"(difilter dari _MAX_ total data)\",
            paginate: {
              first: \"Pertama\",
              last: \"Terakhir\",
              next: \"Selanjutnya\",
              previous: \"Sebelumnya\"
            },
            zeroRecords: \"Data tidak ditemukan\",
            emptyTable: \"Tidak ada data yang tersedia\"
          }
        });
      }, 100);
    },
    error: function() {
      $('#tbody-level-user').html('<tr><td colspan=\"6\" class=\"text-center text-danger\">Gagal memuat data</td></tr>');
    }
  });
};

window.hapusLevelUser = function(id) {
  Swal.fire({
    title: 'Apakah Anda yakin?',
    text: 'Data level user akan dihapus',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: '$deleteUrl?id=' + id,
        method: 'POST',
        dataType: 'json',
        success: function(response) {
          if (response.success) {
            Swal.fire('Berhasil!', 'Data berhasil dihapus', 'success');
            loadLevelUser();
          } else {
            Swal.fire('Gagal!', response.message || 'Gagal menghapus data', 'error');
          }
        },
        error: function() {
          Swal.fire('Gagal!', 'Gagal menghapus data', 'error');
        }
      });
    }
  });
};

// Load data saat halaman pertama kali dibuka
$(document).ready(function() {
  loadLevelUser();
});
", \yii\web\View::POS_END);
?>



