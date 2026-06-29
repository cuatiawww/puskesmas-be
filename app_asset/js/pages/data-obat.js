(function($){
  // Prefer server-provided base if available
  const API_ROOT = (typeof window.APP_BASE_URL !== 'undefined' && window.APP_BASE_URL) ? window.APP_BASE_URL.replace(/\/$/, '') + '/' : '/';

  function buildApiUrl(action, params) {
    var usesIndexPhp = API_ROOT.indexOf('index.php') !== -1 || API_ROOT.indexOf('index.php?r') !== -1;
    if (usesIndexPhp) {
      return API_ROOT + action + (params ? '&' + params : '');
    }
    return API_ROOT + action + (params ? '?' + params : '');
  }

  $(document).ready(function(){    // Initialize DataTable: length menu, search box, and column sorting
    function initTable(){
      try{
        // Use the actual table id from the GridView
        if (!$.fn.DataTable || !$('#table-obat').length) return;
        if ($.fn.DataTable.isDataTable('#table-obat')) { try { $('#table-obat').DataTable().destroy(); } catch(e){} }
        $('#table-obat').DataTable({
          destroy: true,
          pageLength: 10,
          lengthMenu: [[5,10,25,50,-1],[5,10,25,50,'Semua']],
          order: [[1,'asc']], // sort by Nama Obat by default (column 1 after serial)
          columnDefs: [
            { orderable: false, targets: -1 }, // action column
            { searchable: true, targets: [1,2,3] }
          ],
          language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(difilter dari _MAX_ total data)",
            paginate: { first: "Pertama", last: "Terakhir", next: "Selanjutnya", previous: "Sebelumnya" },
            zeroRecords: "Tidak ada data yang ditemukan",
            emptyTable: "Tidak ada data yang tersedia"
          }
        });
      } catch (e) { console.warn('DataObat: initTable error', e); }
    }
    try{ initTable(); } catch(e){}

    // Re-init DataTable after Pjax replacement completes
    $(document).on('pjax:end', '#pjax-data-obat', function(){
      try{ initTable(); } catch(e){}
    });

    // delete (SweetAlert2 flow to match Data Vaksin)
    $(document).on('click', '#table-obat .btn-delete', function(e){
      try{ 
        // Ignore non-left clicks and allow ctrl/meta (open in new tab) to proceed
        if (e && e.which && e.which !== 1) return;
        if (e && (e.ctrlKey || e.metaKey)) return;
        e && e.preventDefault && e.preventDefault(); 
        e && e.stopImmediatePropagation && e.stopImmediatePropagation();
      } catch(ignore) {}

      var $btn = $(this);
      var id = $btn.data('id');
      if (!id && id !== 0) return;

      // Prefer explicit href (useful when view renders the server-side URL like data-icd-ix)
      var endpoint = $btn.attr('href') || $btn.data('url') || buildApiUrl('data-obat/delete');

      Swal.fire({
        icon: 'question',
        title: 'Yakin Hapus Data?',
        text: 'Data yang dihapus tidak dapat dikembalikan!',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
      }).then(function(choose){
        if (!choose || !choose.isConfirmed) return;
        var payload = { id: id };
        // attach CSRF if present
        var csrfParam = $('meta[name="csrf-param"]').attr('content') || '_csrf';
        var csrfToken = $('meta[name="csrf-token"]').attr('content') || '';
        if (csrfParam && csrfToken) payload[csrfParam] = csrfToken;

        $.post(endpoint, payload, function(resp){
          if (resp && resp.success) {
            Swal.fire({icon:'success', title:'Berhasil', text: resp.message || 'Data berhasil dihapus'})
              .then(function(){
                try {
                  // Reload the Pjax container so the GridView updates without a full page reload
                  if ($.pjax) {
                    $.pjax.reload({container:'#pjax-data-obat', timeout: 2000});
                  } else {
                    window.location = buildApiUrl('data-obat');
                  }
                } catch(e) { window.location = buildApiUrl('data-obat'); }
              });
          } else {
            Swal.fire({icon:'error', title:'Gagal', text: resp && resp.message ? resp.message : 'Gagal menghapus'});
          }
        }, 'json').fail(function(){
          Swal.fire({icon:'error', title:'Gagal', text: 'Gagal menghapus (client)'});
        });
      });
    });
  });
})(jQuery);