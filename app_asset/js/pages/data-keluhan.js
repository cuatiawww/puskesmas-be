(function(){
  // Derive a safe APP_BASE_URL (prefer window.APP_BASE_URL set server-side by the view)
  var APP_BASE_URL = '';
  try {
    if (typeof window.APP_BASE_URL !== 'undefined' && window.APP_BASE_URL) {
      APP_BASE_URL = window.APP_BASE_URL.replace(/\/$/, '');
    }
  } catch(e) {}

  function buildApiUrl(action) {
    return APP_BASE_URL ? APP_BASE_URL + '/' + action : '/' + action;
  }

  // Delete handler for data-keluhan
  $(document).on('click', '.btn-delete-vaccine', function (e) {
    e.preventDefault();

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
    }).then(function (result) {

      if (!result.isConfirmed) return;

      $.ajax({
        url: buildApiUrl('data-keluhan/delete'),
        type: 'POST',
        dataType: 'json',
        data: { id: id },
        success: function (resp) {
          if (resp && resp.success) {
            Swal.fire({ icon: 'success', title: 'Berhasil', text: resp.message }).then(function () {
              location.reload();
            });
          } else {
            var msg = (resp && resp.message) ? resp.message : 'Gagal menghapus data';
            Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
          }
        },
        error: function (xhr) {
          var msg = 'Terjadi kesalahan server';
          try {
            var j = JSON.parse(xhr.responseText || '{}');
            if (j && j.message) msg = j.message;
          } catch (e) {}
          Swal.fire({ icon: 'error', title: 'Error', text: msg });
        }
      });
    });
  });

})();
