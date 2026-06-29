// Global variables
let editMode = false;
let editId = null;

console.log("=== PENGAWASAN MAKANAN JS LOADED ===");
console.log("jQuery version:", $.fn.jquery);
console.log("DataTables available:", typeof $.fn.DataTable !== "undefined");

$(document).ready(function () {
  console.log("Document ready - Starting initialization...");
  loadPengawasan();

  $("#btn-add-pengawasan").on("click", function () {
    editMode = false;
    editId = null;
    resetForm();
    $("#matrix-view").hide();
    $("#form-view").show();
  });

  $("#btn-kembali-pengawasan").on("click", function () {
    $("#form-view").hide();
    $("#matrix-view").show();
    resetForm();
  });

  $("#pengawasan-form").on("submit", function (e) {
    e.preventDefault();
    simpanPengawasan();
  });
});


function loadPengawasan() {
  console.log("Loading pengawasan data...");
 
  var existingRows = $("#pengawasan-table-body tr").length;
  if (existingRows > 0) {
    if (!$.fn.DataTable.isDataTable("#table-pengawasan")) {
      $("#table-pengawasan").DataTable({
        pageLength: 10,
        ordering: true,
        searching: true,
        language: {
          search: "Cari:",
          lengthMenu: "Tampilkan _MENU_ data per halaman",
          info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
          infoEmpty: "Tidak ada data",
          infoFiltered: "(difilter dari _MAX_ total data)",
          paginate: {
            first: "Pertama",
            last: "Terakhir",
            next: "Selanjutnya",
            previous: "Sebelumnya",
          },
          zeroRecords: "Tidak ada data yang cocok",
        },
      });
      console.log(
        "Initialized DataTable from server-rendered DOM with",
        existingRows,
        "rows"
      );
    } else {
      console.log("DataTable already initialized; skipping AJAX load");
    }
    return;
  }

  $.ajax({
    url: "/flat-able-ver2/backend/web/index.php?r=pengawasan-makanan/get-list",
    method: "GET",
    dataType: "json",
    success: function (response) {
      console.log("Pengawasan response:", response);
      let html = "";

      if (response.data && response.data.length > 0) {
        response.data.forEach(function (item, index) {
          html += `
            <tr>
              <td>${index + 1}</td>
              <td>${item.katering || "-"}</td>
              <td>${item.daker || "-"}</td>
              <td>${item.area_kerja || "-"}</td>
              <td>${item.tanggal_pemeriksaan || "-"}</td>
              <td>${item.waktu_makan || "-"}</td>
              <td>${item.jumlah_sample || 0}</td>
              <td>${item.kesimpulan || "-"}</td>
              <td class="text-center">
                <button class="btn btn-sm btn-warning" onclick="editPengawasan(${
                  item.id
                })" title="Edit">
                  <i class="ti ti-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="hapusPengawasan(${
                  item.id
                })" title="Hapus">
                  <i class="ti ti-trash"></i>
                </button>
              </td>
            </tr>
          `;
        });
      } else {
        html =
          '<tr><td colspan="9" class="text-center">Tidak ada data</td></tr>';
      }

      $("#pengawasan-table-body").html(html);

      $("#table-pengawasan").DataTable({
        pageLength: 10,
        ordering: true,
        searching: true,
        language: {
          search: "Cari:",
          lengthMenu: "Tampilkan _MENU_ data per halaman",
          info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
          infoEmpty: "Tidak ada data",
          infoFiltered: "(difilter dari _MAX_ total data)",
          paginate: {
            first: "Pertama",
            last: "Terakhir",
            next: "Selanjutnya",
            previous: "Sebelumnya",
          },
          zeroRecords: "Tidak ada data yang cocok",
        },
      });

      console.log(
        "Table loaded successfully with",
        response.data.length,
        "records"
      );
    },
    error: function (xhr, status, error) {
      console.error("AJAX Error:", error);
      console.error("Status:", status);
      console.error("Response:", xhr.responseText);
      Swal.fire({
        icon: "error",
        title: "Gagal",
        text: "Gagal memuat data pengawasan makanan",
        confirmButtonColor: "#d33",
      });
    },
  });
}

/**
 * Save pengawasan (Create or Update)
 */
function simpanPengawasan() {
  const katering = $("#katering").val();
  const daker = $("#daker").val();
  const areaKerja = $('input[name="area_kerja"]:checked').val();
  const tanggalPemeriksaan = $("#tanggal_pemeriksaan").val();
  const waktuMakan = $('input[name="waktu_makan"]:checked').val();
  const jumlahSample = $("#jumlah_sample").val();
  const kesimpulan = $('input[name="kesimpulan"]:checked').val();
  const penyebabTidakLayak = $("#penyebab_tidak_layak").val();

  const kategoriPenyebab = [];
  $('input[name="kategori_penyebab"]:checked').each(function () {
    kategoriPenyebab.push($(this).val());
  });

  if (
    !katering ||
    !daker ||
    !areaKerja ||
    !tanggalPemeriksaan ||
    !waktuMakan ||
    !jumlahSample ||
    !kesimpulan
  ) {
    Swal.fire({
      icon: "warning",
      title: "Data tidak lengkap",
      text: "Mohon isi semua field yang wajib (*)",
      confirmButtonColor: "#4680ff",
    });
    return;
  }

  const formData = {
    katering: katering,
    daker: daker,
    area_kerja: areaKerja,
    tanggal_pemeriksaan: tanggalPemeriksaan,
    waktu_makan: waktuMakan,
    jumlah_sample: jumlahSample,
    kesimpulan: kesimpulan,
    penyebab_tidak_layak: penyebabTidakLayak,
    kategori_penyebab: kategoriPenyebab.join(", "),
  };

  const url = editMode
    ? "/flat-able-ver2/backend/web/index.php?r=pengawasan-makanan/update&id=" +
      editId
    : "/flat-able-ver2/backend/web/index.php?r=pengawasan-makanan/create";

  console.log("Saving data:", formData);
  console.log("URL:", url);

  $.ajax({
    url: url,
    method: "POST",
    data: formData,
    dataType: "json",
    success: function (response) {
      console.log("Save response:", response);
      if (response.success) {
        Swal.fire({
          icon: "success",
          title: "Berhasil!",
          text: response.message || "Data berhasil disimpan",
          showConfirmButton: false,
          timer: 2000,
        }).then(function () {
          resetForm();
          $("#form-view").hide();
          $("#matrix-view").show();
          loadPengawasan();
        });
      } else {
        Swal.fire({
          icon: "error",
          title: "Gagal",
          text: response.message || "Gagal menyimpan data",
          confirmButtonColor: "#d33",
        });
        console.error("Validation errors:", response.errors);
      }
    },
    error: function (xhr, status, error) {
      console.error("AJAX Error:", error);
      console.error("Response:", xhr.responseText);
      Swal.fire({
        icon: "error",
        title: "Terjadi Kesalahan",
        text: "Terjadi kesalahan saat menyimpan data",
        confirmButtonColor: "#d33",
      });
    },
  });
}

function editPengawasan(id) {
  console.log("Editing pengawasan ID:", id);

  $.ajax({
    url:
      "/flat-able-ver2/backend/web/index.php?r=pengawasan-makanan/view&id=" +
      id,
    method: "GET",
    dataType: "json",
    success: function (response) {
      console.log("Edit response:", response);
      if (response.success) {
        const data = response.data;

        $("#pengawasan_id").val(data.id);
        $("#katering").val(data.katering);
        $("#daker").val(data.daker);
        $("#area_kerja").val(data.area_kerja);
        $("#tanggal_pemeriksaan").val(data.tanggal_pemeriksaan);

        $('input[name="waktu_makan"][value="' + data.waktu_makan + '"]').prop(
          "checked",
          true
        );

        $("#jumlah_sample").val(data.jumlah_sample);
        $('input[name="area_kerja"]').prop("checked", false);
        if (data.area_kerja) {
          $('input[name="area_kerja"][value="' + data.area_kerja + '"]').prop(
            "checked",
            true
          );
        }
        $('input[name="kesimpulan"]').prop("checked", false);
        if (data.kesimpulan) {
          $('input[name="kesimpulan"][value="' + data.kesimpulan + '"]').prop(
            "checked",
            true
          );
        }
        $("#penyebab_tidak_layak").val(data.penyebab_tidak_layak);

        $('input[name="kategori_penyebab"]').prop("checked", false);
        if (data.kategori_penyebab) {
          const categories = data.kategori_penyebab.split(", ");
          categories.forEach(function (cat) {
            $('input[name="kategori_penyebab"][value="' + cat + '"]').prop(
              "checked",
              true
            );
          });
        }

        editMode = true;
        editId = id;

        $("#matrix-view").hide();
        $("#form-view").show();
      } else {
        Swal.fire({
          icon: "error",
          title: "Tidak ditemukan",
          text: "Data tidak ditemukan",
          confirmButtonColor: "#d33",
        });
      }
    },
    error: function (xhr, status, error) {
      console.error("Error:", error);
      Swal.fire({
        icon: "error",
        title: "Gagal",
        text: "Gagal memuat data",
        confirmButtonColor: "#d33",
      });
    },
  });
}

/**
 * Delete pengawasan
 */
function hapusPengawasan(id) {
  Swal.fire({
    icon: "question",
    title: "Yakin Hapus Data?",
    text: "Data yang dihapus tidak dapat dikembalikan!",
    showCancelButton: true,
    confirmButtonText: "Ya, Hapus!",
    cancelButtonText: "Batal",
    confirmButtonColor: "#26c281",
    cancelButtonColor: "#4680ff",
  }).then((result) => {
    if (!result.isConfirmed) return;

    console.log("Deleting pengawasan ID:", id);

    $.ajax({
      url:
        "/flat-able-ver2/backend/web/index.php?r=pengawasan-makanan/delete&id=" +
        id,
      method: "POST",
      dataType: "json",
      success: function (response) {
        console.log("Delete response:", response);
        if (response.success) {
          Swal.fire({
            icon: "success",
            title: "Berhasil!",
            text: response.message || "Data berhasil dihapus",
            showConfirmButton: false,
            timer: 2000,
          }).then(function () {
            loadPengawasan();
          });
        } else {
          Swal.fire({
            icon: "error",
            title: "Error!",
            text: "Gagal menghapus data",
            confirmButtonColor: "#d33",
          });
        }
      },
      error: function () {
        console.error("Error deleting pengawasan:", arguments);
        Swal.fire({
          icon: "error",
          title: "Error!",
          text: "Gagal menghapus data",
        });
      },
    });
  });
}

function resetForm() {
  $("#pengawasan-form")[0].reset();
  $("#pengawasan_id").val("");
  $('input[name="waktu_makan"]').prop("checked", false);
  $('input[name="kategori_penyebab"]').prop("checked", false);
  $('input[name="area_kerja"]').prop("checked", false);
  $('input[name="kesimpulan"]').prop("checked", false);
  editMode = false;
  editId = null;
}
