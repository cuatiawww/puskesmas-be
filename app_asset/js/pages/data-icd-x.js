// ICD-X client logic
let icdEditingId = null;

const API_ROOT_ICD = (typeof window.APP_BASE_URL !== 'undefined' && window.APP_BASE_URL)
  ? window.APP_BASE_URL.replace(/\/$/, '') + '/'
  : '/';

function buildApiIcd(action, params) {
  if (API_ROOT_ICD.indexOf('index.php') !== -1 || API_ROOT_ICD.indexOf('index.php?r') !== -1) {
    return API_ROOT_ICD + 'index.php?r=' + action + (params ? '&' + params : '');
  }
  return API_ROOT_ICD + action + (params ? '?' + params : '');
}

$(document).ready(function() {
  // add button
  $('#btn-add-icd').on('click', function() {
    $('#matrix-view').hide();
    $('#form-view').show();
    $('#icd-form')[0].reset();
    clearValidation();
    icdEditingId = null;
    $('#icd-form').find('button[type=submit]').text('Simpan Data ICD-X');
    $('#collapseA').addClass('show');
  });

  $('#btn-kembali').on('click', function() {
    if ($('#matrix-view').length) {
      $('#form-view').hide();
      $('#matrix-view').show();
      return;
    }
    try {
      var base = window.APP_BASE_URL || '';
      if (base.indexOf('index.php') !== -1) {
        var sep = base.indexOf('?') !== -1 ? '&' : '?';
        window.location.href = base + sep + 'r=data-icd-x';
      } else {
        window.location.href = (base || '') + '/data-icd-x';
      }
    } catch (e) { window.location.href = '/data-icd-x'; }
  });

  function clearValidation() {
    $('#icd-form').find('.is-invalid').removeClass('is-invalid');
    $('#icd-form').find('.invalid-feedback').remove();
  }

  function showValidationErrors(errors) {
    clearValidation();
    if (!errors) return;
    Object.keys(errors).forEach(function(key) {
      var el = $('#' + key);
      if (el.length) {
        el.addClass('is-invalid');
        var msg = errors[key][0] || errors[key];
        el.after('<div class="invalid-feedback d-block">' + msg + '</div>');
      }
    });
  }

  function loadIcdList() {
    if ($.fn.DataTable && typeof $.fn.DataTable.isDataTable === 'function' && $.fn.DataTable.isDataTable('#table-icd-x')) {
      try { $('#table-icd-x').DataTable().clear().destroy(); } catch(e){}
    }

    $.ajax({
      url: buildApiIcd('data-icd-x/get-list'),
      dataType: 'json',
      success: function(res) {
        if (res.success) {
          var tbody = $('#tbody-icd-x').empty();
          res.data.forEach(function(item, idx) {
            var badge = item.status === 'Aktif' ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Tidak Aktif</span>';
            var row = '<tr>'+
              '<td>'+(idx+1)+'</td>'+
              '<td>'+ (item.kode_icd||'') +'</td>'+
              '<td>'+ (item.nama_diagnosis||'') +'</td>'+
              '<td>'+ (item.deskripsi||'') +'</td>'+
              '<td>'+ (item.kategori||'') +'</td>'+
              '<td>'+ (item.sub_kategori||'') +'</td>'+
              '<td>'+ badge +'</td>'+
              '<td>'+
                '<button class="btn btn-sm btn-info btn-view text-white" data-id="'+item.id+'"><i class="ti ti-eye"></i></button> '+
                '<button class="btn btn-sm btn-warning btn-edit" data-id="'+item.id+'"><i class="ti ti-pencil"></i></button> '+
                '<button class="btn btn-sm btn-danger btn-delete" data-id="'+item.id+'"><i class="ti ti-trash"></i></button>'+
              '</td>'+
            '</tr>';
            tbody.append(row);
          });

          try {
            $('#table-icd-x').DataTable({
              destroy: true,
              pageLength: 10,
              lengthMenu: [
                [5, 10, 25, 50, -1],
                [5, 10, 25, 50, "Semua"],
              ],
              order: [[1, 'asc']],
              columnDefs: [
                { orderable: true, targets: [0, 1, 2, 3, 4, 5, 6] },
                { orderable: false, targets: 7 },
                { searchable: true, targets: [1, 2, 3] },
                { searchable: false, targets: [0, 4, 5, 6, 7] },
              ],
              language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ prosedur",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 prosedur",
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
          } catch (e) {}
        } else {
          $('#tbody-icd-x').html('<tr><td colspan="8" class="text-center text-danger">Gagal memuat data</td></tr>');
        }
      },
      error: function() {
        $('#tbody-icd-x').html('<tr><td colspan="8" class="text-center text-danger">Gagal memuat data</td></tr>');
      }
    });
  }

  // initial
  loadIcdList();

  $('#tbody-icd-x').on('click', '.btn-view', function() {
    var id = $(this).data('id');
    $.getJSON(buildApiIcd('data-icd-x/view', 'id='+id)).done(function(res) {
      if (res.success) {
        var d = res.data;
        var html = '<p><strong>Kode:</strong> '+(d.kode_icd||'')+'</p>'+
                   '<p><strong>Nama Diagnosis:</strong> '+(d.nama_diagnosis||'')+'</p>'+
                   '<p><strong>Deskripsi:</strong> '+(d.deskripsi||'-')+'</p>'+
                   '<p><strong>Kategori:</strong> '+(d.kategori||'-')+'</p>'+
                   '<p><strong>Sub Kategori:</strong> '+(d.sub_kategori||'-')+'</p>'+
                   '<p><strong>Status:</strong> '+(d.status||'-')+'</p>';
        $('#modal-icd-x-view .modal-body').html(html);
        var modal = new bootstrap.Modal($('#modal-icd-x-view'));
        modal.show();
      } else { alert(res.message || 'Data tidak ditemukan'); }
    });
  });

  $('#tbody-icd-x').on('click', '.btn-edit', function() {
    var id = $(this).data('id');
    $.getJSON(buildApiIcd('data-icd-x/update', 'id='+id)).done(function(res) {
      if (res.success) {
        var d = res.data; icdEditingId = d.id;
        $('#kode_icd').val(d.kode_icd);
        $('#nama_diagnosis').val(d.nama_diagnosis);
        $('#deskripsi').val(d.deskripsi);
        $('#kategori').val(d.kategori);
        $('#sub_kategori').val(d.sub_kategori);
        $('#status').val(d.status);

        $('#matrix-view').hide(); $('#form-view').show(); $('#collapseA').addClass('show');
        $('#icd-form').find('button[type=submit]').text('Perbarui Data ICD-X');
      } else { alert(res.message || 'Data tidak ditemukan'); }
    });
  });

  $('#tbody-icd-x').on('click', '.btn-delete', function() {
    var id = $(this).data('id');
    Swal.fire({icon:'question',title:'Yakin Hapus Data?',text:'Data yang dihapus tidak dapat dikembalikan!',showCancelButton:true,confirmButtonText:'Ya, Hapus!',cancelButtonText:'Batal'}).then(function(result){
      if (!result.isConfirmed) return;
      $.post(buildApiIcd('data-icd-x/delete&id='+id), {}, function(res){
        if (res.success) { Swal.fire({icon:'success',title:'Berhasil!',text:res.message,showConfirmButton:false,timer:1400}); loadIcdList(); }
        else { Swal.fire({icon:'error',title:'Error!',text:res.message||'Gagal menghapus data'}); }
      }, 'json').fail(function(){ Swal.fire({icon:'error',title:'Error!',text:'Gagal menghapus data'}); });
    });
  });

  // validation
  var validateIcdXForm = (function(){
    var form = $('#icd-form'); var controls = form.find('input, textarea, select');
    controls.on('focus', function(){ this.dataset.touched = 'true'; });
    controls.on('input change', function(){ this.dataset.touched = 'true'; validateField(this); });
    function validateField(el){ el = $(el); if (el.prop('required')){ var val = (el.val()||'').toString().trim(); if (!val){ el.addClass('is-invalid').removeClass('is-valid'); if (el.next('.invalid-feedback').length===0) el.after('<div class="invalid-feedback d-block">Data wajib diisi</div>'); return false; } else { el.removeClass('is-invalid'); if (el[0].dataset && el[0].dataset.touched==='true') el.addClass('is-valid'); el.next('.invalid-feedback').remove(); return true; } } return true; }
    return function validate(){ var ok = true; controls.each(function(){ if ($(this).prop('disabled')) return; if ($(this).prop('required')) { if (!validateField(this)) ok=false; } }); if (!ok){ var first = form.find('.is-invalid').first(); if (first.length){ first[0].scrollIntoView({behavior:'smooth',block:'center'}); first.focus(); } } return ok; };
  })();



  $('#icd-form').on('submit', function(e){ e.preventDefault(); clearValidation(); if (!validateIcdXForm()) return; var data = { kode_icd:$('#kode_icd').val(), nama_diagnosis:$('#nama_diagnosis').val(), deskripsi:$('#deskripsi').val(), kategori:$('#kategori').val(), sub_kategori:$('#sub_kategori').val(), status:$('#status').val() };
  var icdEditingId = $('#icdEditingId').val();
    var btn = $(this).find('button[type=submit]'); btn.prop('disabled', true).text(icdEditingId? 'Memperbarui...':'Menyimpan...');
    var url = buildApiIcd('data-icd-x/' + (icdEditingId ? ('update&id=' + icdEditingId) : 'create'));
    $.post(url, data, function(res){ btn.prop('disabled', false).text('Simpan Data ICD-X'); if (res.success){ Swal.fire({icon:'success',title:'Berhasil',text:res.message||'Data tersimpan',showConfirmButton:false,timer:1400}); if ($('#matrix-view').length){ $('#form-view').hide(); $('#matrix-view').show(); loadIcdList(); icdEditingId=null; $('#icd-form')[0].reset(); $('#icd-form').find('button[type=submit]').text('Simpan Data ICD-X'); } else { // standalone
          try { var base = window.APP_BASE_URL || ''; if (base.indexOf('index.php')!==-1){ var sep = base.indexOf('?')!==-1 ? '&' : '?'; window.location.href = base + sep + 'r=data-icd-x'; } else { window.location.href = (base||'') + '/data-icd-x'; } } catch(e){ window.location.href = '/data-icd-x'; }
        } } else { if (res.errors) showValidationErrors(res.errors); else alert(res.message||'Terjadi kesalahan'); } }, 'json').fail(function(){ btn.prop('disabled', false).text('Simpan Data ICD-X'); alert('Gagal menghubungi server'); });
  });

});
