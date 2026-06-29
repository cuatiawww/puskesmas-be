  $(document).ready(function() {
  // helper to call backend API using existing global helper if present
  // Prefer pretty URL (base + '/' + route). Fall back to query-style if needed.
  function backendApi(route) {
    if (window.backendApi) return window.backendApi(route);
    var base = (window.APP_BASE_URL || '').replace(/\/$/, '');
    return base + '/' + route;
  }

  // --- LOAD LIST FROM BACKEND ---
  function loadDataDukungList() {
    var api = backendApi('data-dukung/get-list');
    $.get(api, function(resp) {
      if (resp && resp.success) {
        var tbody = $('#tbody-dukung');
        tbody.empty();
        resp.data.forEach(function(item, idx) {
          var statusBadge = '<span class="badge bg-warning">Menunggu Verifikasi</span>';
          if (item.status_verifikasi == 'terverifikasi') statusBadge = '<span class="badge bg-success">Terverifikasi</span>';
          if (item.status_verifikasi == 'ditolak') statusBadge = '<span class="badge bg-danger">Ditolak</span>';

          var actions = '<button class="btn btn-sm btn-info text-white btn-view" data-id="'+item.id+'"><i class="ti ti-eye"></i></button> '
                      + '<button class="btn btn-sm btn-warning btn-edit" data-id="'+item.id+'"><i class="ti ti-pencil"></i></button> '
                      + '<button class="btn btn-sm btn-danger btn-delete" data-id="'+item.id+'"><i class="ti ti-trash"></i></button>';

          var displayId = (item.id_dukung && item.id_dukung.indexOf('DOK-') === 0) ? item.id_dukung : ('DOK-'+String(item.id).padStart(3,'0'));

          var tr = '<tr>'
                 + '<td>'+(idx+1)+'</td>'
                 + '<td>'+displayId+'</td>'
                 + '<td>'+(item.nama_dokumen || '-')+'</td>'
                 + '<td>'+(item.jenis_dokumen || '-')+'</td>'
                 + '<td>'+(item.tanggal_upload || '-')+'</td>'
                 + '<td>'+statusBadge+'</td>'
                 + '<td>'+actions+'</td>'
                 + '</tr>';
          tbody.append(tr);
        });
        // initialize datatable after rows appended
        if ($('#tbody-dukung').find('tr').length > 0) initDukungTable();
      
      } else {
        // if fallback provided show it
        if (resp && resp.fallback && resp.data) {
          var tbody = $('#tbody-dukung');
          tbody.empty();
          var trIntro = '<tr><td colspan="7"><div class="alert alert-warning">'+(resp.message || 'Menampilkan data contoh')+'</div></td></tr>';
          tbody.append(trIntro);
          resp.data.forEach(function(item, idx) {
            var displayId = (item.id_dukung && item.id_dukung.indexOf('DOK-') === 0) ? item.id_dukung : ('DOK-'+String(item.id).padStart(3,'0'));
            var tr = '<tr>'
                   + '<td>'+(idx+1)+'</td>'
                   + '<td>'+displayId+'</td>'
                   + '<td>'+(item.nama_dokumen || '-')+'</td>'
                   + '<td>'+(item.jenis_dokumen || '-')+'</td>'
                   + '<td>'+(item.tanggal_upload || '-')+'</td>'
                   + '<td>'+(item.status_verifikasi || '-')+'</td>'
                   + '<td>-</td>'
                   + '</tr>';
            tbody.append(tr);
          });
          // initialize datatable for fallback rows
          if ($('#tbody-dukung').find('tr').length > 0) initDukungTable();
        
        }
      }
    }, 'json').fail(function(jqXHR, textStatus, errorThrown){
      // network or HTML response (login redirect) - show fallback message
      var tbody = $('#tbody-dukung');
      tbody.empty();
      tbody.append('<tr><td colspan="7" class="text-center text-danger">Gagal memuat data</td></tr>');
    });
  }

  // initial load
  loadDataDukungList();

  // --- SHOW FORM (add) ---
  $('#btn-add-hasil').on('click', function(e){
    e.preventDefault();
    $('#matrix-view').hide();
    $('#form-view').show();
    $('#hasil-form')[0].reset();
    $('#data_dukung_id').val('');
    $('#id_dukung').val('');
    $('#id_jemaah').val('');
    $('input[name="status_verifikasi"][value="menunggu"]').prop('checked', true);
    $('#collapseA').addClass('show');
  });

  // --- BACK TO TABLE ---
  $('#btn-kembali').on('click', function(){
    $('#form-view').hide();
    $('#matrix-view').show();
  });

  // --- VIEW DETAIL ---
  $('#table-dukung tbody').on('click', '.btn-view', function() {
    var id = $(this).data('id');
    var api = backendApi('data-dukung/view&id=' + id);
    $.get(api, function(resp) {
      if (resp && resp.success) {
        var d = resp.data;
        var displayId = (d.id_dukung && d.id_dukung.indexOf('DOK-') === 0) ? d.id_dukung : ('DOK-'+String(d.id).padStart(3,'0'));

        $('#preview-dukung-id').text(displayId);
        $('#preview-nama-jemaah').text(d.nama_jemaah || '-');
        $('#preview-nama-dokumen').text(d.nama_dokumen || '-');
        $('#preview-jenis-dokumen').text(d.jenis_dokumen || '-');
        $('#preview-nomor-dokumen').text(d.nomor_dokumen || '-');
        $('#preview-tanggal-upload').text(d.tanggal_upload || '-');
        $('#preview-keterangan').text(d.keterangan || '-');
        $('#preview-status').html(getStatusBadge(d.status_verifikasi));
        $('#preview-tanggal-verifikasi').text(d.tanggal_verifikasi || '-');
        $('#preview-petugas-verifikasi').text(d.petugas_verifikasi || '-');
        $('#preview-catatan-verifikasi').text(d.catatan_verifikasi || '-');

        var filePath = d.file_path || d.file_name || '';
        var fileUrl = d.file_url || null;
        if (filePath) {
          var fileNameOnly = filePath.split('/').pop();
          var downloadUrl = fileUrl ? fileUrl : ('../uploads/data-dukung/' + encodeURIComponent(fileNameOnly));
          $('#preview-file').html('<a href="' + downloadUrl + '" target="_blank">' + fileNameOnly + '</a>');
        } else {
          $('#preview-file').text('-');
        }

        var modalEl = document.getElementById('previewDukungModal');
        var previewModal = new bootstrap.Modal(modalEl);
        previewModal.show();
      } else {
        Swal.fire('Gagal', 'Gagal mengambil data detail', 'error');
      }
    }, 'json').fail(function(){
      Swal.fire('Error', 'Gagal memuat data detail', 'error');
    });
  });

  // --- EDIT ---
  $('#table-dukung tbody').on('click', '.btn-edit', function() {
    var id = $(this).data('id');
    var api = backendApi('data-dukung/view&id=' + id);
    $.get(api, function(resp) {
      if (resp && resp.success) {
        var d = resp.data;
        $('#data_dukung_id').val(d.id);
        if (d.id_dukung && d.id_dukung.indexOf('DOK-') === 0) {
          $('#id_dukung').val(d.id_dukung);
        } else {
          $('#id_dukung').val('');
        }
        $('#id_jemaah').val(d.id_jemaah);
        $('#nama_jemaah').val(d.nama_jemaah);
        $('#jenis_dokumen').val(d.jenis_dokumen);
        $('#nama_dokumen').val(d.nama_dokumen);
        $('#nomor_dokumen').val(d.nomor_dokumen);
        $('#keterangan').val(d.keterangan);
        if (d.status_verifikasi) $('input[name="status_verifikasi"][value="' + d.status_verifikasi + '"]').prop('checked', true); else $('input[name="status_verifikasi"][value="menunggu"]').prop('checked', true);
        $('#tanggal_verifikasi').val(d.tanggal_verifikasi);
        $('#petugas_verifikasi').val(d.petugas_verifikasi);
        $('#catatan_verifikasi').val(d.catatan_verifikasi);

        $('#matrix-view').hide();
        $('#form-view').show();
      } else {
        Swal.fire('Gagal', 'Gagal mengambil data untuk edit', 'error');
      }
    }, 'json');
  });

  // --- DELETE ---
  $('#table-dukung tbody').on('click', '.btn-delete', function() {
    var id = $(this).data('id');
    Swal.fire({
      title: 'Yakin Hapus Data?',
      text: 'Data yang dihapus tidak dapat dikembalikan!',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Ya, Hapus!',
      cancelButtonText: 'Batal',
      confirmButtonColor: '#28a745',
      cancelButtonColor: '#3085d6'
    }).then((result) => {
      if (!result.isConfirmed) return;
      var api = backendApi('data-dukung/delete&id=' + id);
      $.post(api, {}, function(resp) {
        if (resp && resp.success) {
          Swal.fire({ icon: 'success', title: 'Sukses', text: (resp && resp.message) ? resp.message : 'Data dihapus', timer: 1400, showConfirmButton: false }).then(function(){
            loadDataDukungList();
          });
        } else {
          Swal.fire('Gagal', (resp && resp.message) ? resp.message : 'Gagal menghapus', 'error');
        }
      }, 'json').fail(function(jqXHR, textStatus, errorThrown){
        var msg = 'Error jaringan: ' + textStatus + (errorThrown ? ' - ' + errorThrown : '');
        try { msg += '\nStatus: ' + jqXHR.status + ' ' + (jqXHR.statusText || '');
              if (jqXHR.responseText) msg += '\nResponse: ' + jqXHR.responseText.substring(0,800);
        } catch(e) { }
        Swal.fire('Error', msg, 'error');
      });
    });
  });

  // --- FORM SUBMIT (create / update) ---
  var form = document.getElementById('hasil-form');
  if (form) {
    form.addEventListener('submit', function(e) {
      e.preventDefault();

      // basic validation
      var nama = $('#nama_jemaah').val();
      if (!nama || !nama.toString().trim()) { Swal.fire('Gagal', 'Nama Jemaah harus diisi.', 'error'); return; }

      var id = $('#data_dukung_id').val();
      var payload = {
        id_dukung: $('#id_dukung').val(),
        id_jemaah: $('#id_jemaah').val(),
        nama_jemaah: $('#nama_jemaah').val(),
        jenis_dokumen: $('#jenis_dokumen').val(),
        nama_dokumen: $('#nama_dokumen').val(),
        nomor_dokumen: $('#nomor_dokumen').val(),
        keterangan: $('#keterangan').val(),
        status_verifikasi: $('input[name="status_verifikasi"]:checked').val(),
        tanggal_verifikasi: $('#tanggal_verifikasi').val(),
        petugas_verifikasi: $('#petugas_verifikasi').val(),
        catatan_verifikasi: $('#catatan_verifikasi').val(),
        file_name: ($('#file_dokumen')[0] && $('#file_dokumen')[0].files[0]) ? $('#file_dokumen')[0].files[0].name : null
      };

      var api = id ? backendApi('data-dukung/update&id=' + id) : backendApi('data-dukung/create');
      var $btn = $(form).find('button[type="submit"]');
      $btn.prop('disabled', true);

      $.post(api, payload, function(resp) {
        if (resp && resp.success) {
          var savedId = '';
          if (resp.id_dukung) {
            if (resp.id_dukung.indexOf('DOK-') === 0) savedId = resp.id_dukung;
            else if (resp.id_dukung.indexOf('DUK-') === 0 && (resp.id || id)) savedId = 'DOK-' + String(resp.id || id).padStart(3,'0');
            else savedId = resp.id_dukung;
          } else if (resp.id) {
            savedId = 'DOK-' + String(resp.id).padStart(3,'0');
          } else if (id) {
            savedId = 'DOK-' + String(id).padStart(3,'0');
          }

          if (resp && resp.id_jemaah) {
            try { $('#id_jemaah').val(resp.id_jemaah); } catch(e) {}
          }
          var message = savedId ? ('Data dukung berhasil disimpan ID: ' + savedId) : (resp.message || 'Data berhasil disimpan');
          if (resp && resp.id_jemaah) message += '\nID Jemaah: ' + resp.id_jemaah;

          Swal.fire({ icon: 'success', title: 'Sukses', text: message, timer: 1400, showConfirmButton: false }).then(function(){
            $('#matrix-view').show();
            $('#form-view').hide();
            loadDataDukungList();
          });
        } else {
          var msg = (resp && resp.message) ? resp.message : 'Gagal menyimpan data';
          if (resp && resp.errors) {
            var errParts = [];
            for (var k in resp.errors) {
              if (!resp.errors.hasOwnProperty(k)) continue;
              var v = resp.errors[k];
              if (Array.isArray(v)) errParts.push(k + ': ' + v.join('; ')); else errParts.push(k + ': ' + v);
            }
            if (errParts.length) msg += '\n\n' + errParts.join('\n');
          }
          Swal.fire('Gagal', msg, 'error');
        }
      }, 'json').fail(function(jqXHR, textStatus, errorThrown){
        var msg = 'Error jaringan: ' + textStatus + (errorThrown ? ' - ' + errorThrown : '');
        try { msg += '\nStatus: ' + jqXHR.status + ' ' + (jqXHR.statusText || '');
              if (jqXHR.responseText) msg += '\nResponse: ' + jqXHR.responseText.substring(0,800);
        } catch(e) { }
        Swal.fire('Error', msg, 'error');
      }).always(function(){ $btn.prop('disabled', false); });

    });
  }

  function initDukungTable() {
    if ($.fn.DataTable.isDataTable('#table-dukung')) {
      $('#table-dukung').DataTable().clear().destroy();
    }
    $('#table-dukung').DataTable({
      destroy: true,
      pageLength: 10,
      lengthMenu: [
        [5, 10, 25, 50, -1],
        [5, 10, 25, 50, "Semua"],
      ],
      order: [[1, "asc"]],
      columnDefs: [
        { orderable: true, targets: [0, 1, 2, 3, 4, 5] },
        { orderable: false, targets: 6 },
        { searchable: true, targets: [1, 2, 3] },
        { searchable: false, targets: [0, 4, 5, 6] },
      ],
      language: {
        search: "Cari:",
        lengthMenu: "Tampilkan _MENU_ data",
        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ dokumen",
        infoEmpty: "Menampilkan 0 sampai 0 dari 0 dokumen",
        infoFiltered: "(difilter dari _MAX_ total data)",
        paginate: {
          first: "Pertama",
          last: "Terakhir",
          next: "Selanjutnya",
          previous: "Sebelumnya",
        },
        zeroRecords: "Data tidak ditemukan",
        emptyTable: "Tidak ada data yang tersedia",
      },
    });
  }

  // helpers
  function formatTanggalIndonesia(dateString) {
    if (!dateString) return '-';
    var parts = dateString.split('-');
    if (parts.length !== 3) return dateString;
    var day = parseInt(parts[2],10);
    var month = parseInt(parts[1],10) - 1;
    var year = parts[0];
    var monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    return day + ' ' + monthNames[month] + ' ' + year;
  }

  function getStatusBadge(status) {
    if (!status) return '<span class="badge bg-warning">Menunggu Verifikasi</span>';
    if (status === 'terverifikasi') return '<span class="badge bg-success">Terverifikasi</span>';
    if (status === 'ditolak') return '<span class="badge bg-danger">Ditolak</span>';
    if (status === 'perbaikan') return '<span class="badge bg-secondary">Memerlukan Perbaikan</span>';
    return '<span class="badge bg-secondary">' + status + '</span>';
  }

});
