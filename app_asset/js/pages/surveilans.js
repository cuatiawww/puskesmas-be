(function ($) {
  const API_ROOT = (typeof window.APP_BASE_URL !== 'undefined' && window.APP_BASE_URL)
    ? window.APP_BASE_URL.replace(/\/$/, '') + '/'
    : '/';

  function buildApiUrl(action, params) {
    const usesIndexPhp = API_ROOT.indexOf('index.php') !== -1 || API_ROOT.indexOf('index.php?r') !== -1;
    if (usesIndexPhp) {
      return API_ROOT + action + (params ? '&' + params : '');
    }
    return API_ROOT + action + (params ? '?' + params : '');
  }

  $(document).ready(function () {

    // =======================
    // INIT DATATABLE
    // =======================
    function initTable() {
      if (!$.fn.DataTable || !$('#table-surveilans').length) return;

      if ($.fn.DataTable.isDataTable('#table-surveilans')) {
        $('#table-surveilans').DataTable().destroy();
      }

      $('#table-surveilans').DataTable({
        destroy: true,
        autoWidth: false,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, 'Semua']],
        order: [[1, 'asc']],
        columnDefs: [
          { orderable: false, targets: -1 },
          { searchable: true, targets: [1, 2, 3] },
          { searchable: false, targets: [0, 4, 5, 6, 7] }
        ],
        language: {
          search: "Cari:",
          lengthMenu: "Tampilkan _MENU_ data",
          info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
          infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
          infoFiltered: "(difilter dari _MAX_ total data)",
          paginate: {
            first: "Pertama",
            last: "Terakhir",
            next: "Selanjutnya",
            previous: "Sebelumnya"
          },
          zeroRecords: "Data tidak ditemukan",
          emptyTable: "Tidak ada data yang tersedia"
        }
      });
    }

    initTable();

    // Re-init setelah PJAX reload
    $(document).on('pjax:end', '#pjax-data-surveilans', function () {
      initTable();
    });

    // =======================
    // DELETE (SWEETALERT + POST)
    // =======================
    $(document).on('click', '#table-surveilans .btn-delete', function (e) {
      e.preventDefault();

      const $btn = $(this);
      const id = $btn.data('id');
      if (!id) return;

      const action = $btn.attr('href') || buildApiUrl('surveilans/delete');

      Swal.fire({
        icon: 'question',
        title: 'Yakin Hapus Data?',
        text: 'Data yang dihapus tidak dapat dikembalikan!',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
      }).then(function (res) {
        if (!res.isConfirmed) return;

        const payload = { id: id };
        const csrfParam = $('meta[name="csrf-param"]').attr('content') || '_csrf';
        const csrfToken = $('meta[name="csrf-token"]').attr('content') || '';
        payload[csrfParam] = csrfToken;

        $.post(action, payload, function (resp) {
          if (resp && resp.success) {
            Swal.fire({ icon: 'success', title: 'Berhasil', text: resp.message || 'Data dihapus' })
              .then(function () {
                if ($.pjax) {
                  $.pjax.reload({ container: '#pjax-data-surveilans', timeout: 2000 });
                } else {
                  window.location.reload();
                }
              });
          } else {
            Swal.fire({ icon: 'error', title: 'Gagal', text: resp.message || 'Gagal menghapus data' });
          }
        }, 'json');
      });
    });

  });
})(jQuery);
