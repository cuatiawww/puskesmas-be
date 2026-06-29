(function(){
  // Derive a safe APP_BASE_URL and expose a build helper
  var _APP_BASE_URL = '';
  try {
    if (typeof window.APP_BASE_URL !== 'undefined' && window.APP_BASE_URL) {
      _APP_BASE_URL = window.APP_BASE_URL.replace(/\/$/, '');
    }
  } catch(e) {}

  if (!_APP_BASE_URL) {
    // Graceful fallback if view didn't set it
    _APP_BASE_URL = '';
  }

  function buildApiUrl(action) {
    if (_APP_BASE_URL) return _APP_BASE_URL + '/' + action;
    return '/' + action;
  }

  window.__buildApiUrl = buildApiUrl;

  // Delete handler for jenis-radiologi
  $(document).on('click', '.btn-delete-vaccine', function(e) {
    try { e && e.preventDefault && e.preventDefault(); } catch(ignore) {}
    var id = $(this).data('id');
    if (!id && id !== 0) return;
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
      $.ajax({
        url: __buildApiUrl('jenis-radiologi/delete'),
        method: 'POST',
        data: { id: id },
        dataType: 'json',
        success: function(resp){
          if (resp && resp.success) {
            Swal.fire({ icon: 'success', title: 'Berhasil', text: resp.message }).then(function(){
              try { window.location.reload(); } catch(e) { window.location = window.location.href; }
            });
          } else {
            Swal.fire({ icon: 'error', title: 'Gagal', text: resp && resp.message ? resp.message : 'Gagal menghapus' });
          }
        },
        error: function(xhr, status, err){
          var serverMsg = '';
          try {
            if (xhr && xhr.responseText) {
              var json = null;
              try { json = JSON.parse(xhr.responseText); } catch(e) {}
              if (json && json.message) serverMsg = json.message;
              else serverMsg = xhr.responseText;
            }
          } catch(ignore) {}

          console.error('Delete request failed', status, err, xhr);
          Swal.fire({ icon: 'error', title: 'Gagal', text: serverMsg ? ('Server: ' + serverMsg) : ('Gagal menghapus (client) - ' + status) });
        }
      });
    });
  });

})();
