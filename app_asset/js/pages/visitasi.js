// Global variables
let editMode = false;
let editId = null;

console.log("=== VISITASI JS LOADED ===");
console.log("jQuery version:", $.fn.jquery);
console.log("DataTables available:", typeof $.fn.DataTable !== "undefined");

$(document).ready(function () {
  console.log("Document ready - Starting initialization...");

  loadVisitasi();

  $("#btn-add-visitasi").on("click", function () {
    console.log("TAMBAH button clicked");
    $("#matrix-view").hide();
    $("#form-view").hide();
    $("#search-jamaah-section").show();
    loadJamaahList();
    setTimeout(function () {
      $("#select-jamaah").focus();
    }, 50);
  });

  $("#select-jamaah").on("change", function () {
    $("#btn-select-jamaah").prop("disabled", !$(this).val());
  });

  $("#btn-select-jamaah").on("click", function () {
    const selectedOption = $("#select-jamaah option:selected");
    const idJamaah = selectedOption.val();

    if (!idJamaah) {
      alert("Pilih jamaah terlebih dahulu");
      return;
    }

    const namaJamaah = selectedOption.data("nama");
    const nomorPorsi = selectedOption.data("porsi");
    const nomorPaspor = selectedOption.data("paspor");
    const jenisKelamin = selectedOption.data("jk");
    const umur = selectedOption.data("umur");

    $("#id_jamaah").val(idJamaah);
    $("#display-nama-lengkap").text(namaJamaah);
    $("#display-nama-profil").text(namaJamaah);
    $("#display-jenis-kelamin").text(jenisKelamin || "-");
    $("#display-umur").text(umur ? umur + " Tahun" : "-");
    $("#display-nomor-porsi").text(nomorPorsi || "-");
    $("#display-nomor-paspor").text(nomorPaspor || "-");

    const today = new Date().toISOString().split("T")[0];
    $("#tanggal_visitasi").val(today);

    $("#lokasi").val("");
    $("#waktu_visitasi").val("");
    $("#keluhan_utama").val("");
    $("#anamnesa").val("");

    $("#modalCariJamaah").modal("hide");
    $("#matrix-view").hide();
    $("#form-view").show();

    editMode = false;
    editId = null;
    try {
      if (typeof refreshDeleteBtn === "function") refreshDeleteBtn();
    } catch (e) {}
  });

  $("#btn-kembali-visitasi").on("click", function () {
    resetForm();
    $("#form-view").hide();
    $("#matrix-view").show();
  });

  $("#visitasi-form").on("submit", function (e) {
    e.preventDefault();
    simpanVisitasi();
  });
});

/**
 * Load data visitasi dari backend
 */
function loadVisitasi() {
  console.log("Loading visitasi data...");

  if ($.fn.DataTable.isDataTable("#table-visitasi")) {
    $("#table-visitasi").DataTable().destroy();
  }

  $.ajax({
    url: "/flat-able-ver2/backend/web/index.php?r=visitasi/get-list",
    method: "GET",
    dataType: "json",
    success: function (response) {
      console.log("Visitasi response:", response);
      let html = "";

      if (response.success && response.data && response.data.length > 0) {
        response.data.forEach(function (item, index) {
          html += `
            <tr>
              <td>${index + 1}</td>
              <td>${item.tanggal_visitasi || "-"}</td>
              <td>${item.nama_jamaah || "-"}</td>
              <td>${item.umur || "-"}</td>
              <td>${item.keluhan_utama || "Tidak ada keluhan"}</td>
              <td class="text-center">
                <button class="btn btn-sm btn-primary" onclick="editVisitasi(${
                  item.id
                })" title="Visitasi">
                  <i class="ti ti-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="hapusVisitasi(${
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
          '<tr><td colspan="6" class="text-center">Tidak ada data</td></tr>';
      }

      $("#visitasi-table-body").html(html);

      $("#table-visitasi").DataTable({
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
        response.data ? response.data.length : 0,
        "records"
      );
    },
    error: function (xhr, status, error) {
      console.error("AJAX Error:", error);
      console.error("Status:", status);
      console.error("Response:", xhr.responseText);
      alert("Gagal memuat data visitasi");
    },
  });
}

/**
 * Load jamaah list for dropdown
 */
function loadJamaahList() {
  $.ajax({
    url: "/flat-able-ver2/backend/web/index.php?r=data-jamaah/get-list",
    method: "GET",
    dataType: "json",
    success: function (response) {
      if (response.success) {
        const select = $("#select-jamaah");
        select.empty();
        select.append('<option value="">-- Pilih Jamaah --</option>');

        response.data.forEach(function (jamaah) {
          select.append(`<option value="${jamaah.id}"
            data-nama="${jamaah.nama_lengkap}"
            data-porsi="${jamaah.nomor_porsi || ""}"
            data-paspor="${jamaah.nomor_paspor || ""}"
            data-jk="${jamaah.jenis_kelamin || ""}"
            data-umur="${
              jamaah.umur || ""
            }">${jamaah.nama_lengkap} - ${jamaah.nomor_porsi || "N/A"}</option>`);
        });

        console.log("Jamaah list loaded:", response.data.length, "records");
      }
    },
    error: function (xhr, status, error) {
      console.error("Error loading jamaah:", error);
      alert("Gagal memuat data jamaah");
    },
  });
}

/**
 * Save visitasi (Create or Update)
 */
function simpanVisitasi() {
  // --- AWAL LOGIKA VALIDASI ---

  // 1. Validasi field dasar
  const idJamaah = $("#id_jamaah").val();
  const tanggalVisitasi = $("#tanggal_visitasi").val();
  let isValid = true;
  let firstErrorElement = null;

  // Reset semua tanda validasi sebelumnya
  $(".is-invalid").removeClass("is-invalid");

  if (!idJamaah) {
    alert("Data jamaah belum dipilih");
    return;
  }

  if (!tanggalVisitasi) {
    $("#tanggal_visitasi").addClass("is-invalid");
    isValid = false;
    firstErrorElement = firstErrorElement || $("#tanggal_visitasi");
  }

  // 2. Validasi field di Accordion SUBJEKTIF
  const keluhanUtama = $("#keluhan_utama").val().trim();
  const anamnesa = $("#anamnesa").val().trim();

  if (!keluhanUtama) {
    $("#keluhan_utama").addClass("is-invalid");
    isValid = false;
    firstErrorElement = firstErrorElement || $("#keluhan_utama");
  }

  if (!anamnesa) {
    $("#anamnesa").addClass("is-invalid");
    isValid = false;
    firstErrorElement = firstErrorElement || $("#anamnesa");
  }

  // // 3. Validasi field di Accordion OBJEKTIF
  // const gcs = $("#gcs").val().trim();
  // const tekananDarah = $("#tekanan_darah").val().trim();
  // const nadi = $("#nadi").val().trim();
  // const respirasi = $("#respirasi").val().trim();
  // const suhu = $("#suhu_tubuh").val().trim();
  // const spo = $("#spo").val().trim();

  // if (!gcs) {
  //   $("#gcs").addClass("is-invalid");
  //   isValid = false;
  //   firstErrorElement = firstErrorElement || $("#gcs");
  // }

  // if (!tekananDarah) {
  //   $("#tekanan_darah").addClass("is-invalid");
  //   isValid = false;
  //   firstErrorElement = firstErrorElement || $("#tekanan_darah");
  // }

  // if (!nadi) {
  //   $("#nadi").addClass("is-invalid");
  //   isValid = false;
  //   firstErrorElement = firstErrorElement || $("#nadi");
  // }

  // if (!respirasi) {
  //   $("#respirasi").addClass("is-invalid");
  //   isValid = false;
  //   firstErrorElement = firstErrorElement || $("#respirasi");
  // }

  // if (!suhu) {
  //   $("#suhu_tubuh").addClass("is-invalid");
  //   isValid = false;
  //   firstErrorElement = firstErrorElement || $("#suhu_tubuh");
  // }

  // if (!spo) {
  //   $("#spo").addClass("is-invalid");
  //   isValid = false;
  //   firstErrorElement = firstErrorElement || $("#spo");
  // }

  // 4. Jika ada error, tampilkan accordion yang salah dan fokus ke field tersebut
  if (!isValid) {
    alert("Harap lengkapi semua field yang ditandai.");

    // Cari accordion yang berisi error
    const accordionWithError = $(firstErrorElement).closest(
      ".accordion-collapse"
    );

    // Buka accordion tersebut jika tertutup
    if (accordionWithError.length && !accordionWithError.hasClass("show")) {
      const accordionButton = $(
        'button[data-bs-target="#' + accordionWithError.attr("id") + '"]'
      );
      if (accordionButton.length) {
        $(".accordion-button").not(accordionButton).removeClass("show");
        $(".accordion-collapse").not(accordionWithError).removeClass("show");
        accordionButton.addClass("show");
        accordionWithError.addClass("show");
      }
    }

    $("html, body").animate(
      {
        scrollTop: $(firstErrorElement).offset().top - 100,
      },
      500
    );

    // Fokus ke field yang error
    firstErrorElement.focus();
    return; 
  }

  // --- AKHIR LOGIKA VALIDASI ---

  const formData = {
    id_jamaah: idJamaah,
    tanggal_visitasi: tanggalVisitasi,
    lokasi: $("#lokasi").val(),
    waktu_visitasi: $("#waktu_visitasi").val(),
    keluhan_utama: keluhanUtama,
    anamnesa: anamnesa,
    gcs: gcs,
    tekanan_darah: tekananDarah,
    nadi: nadi,
    respirasi: respirasi,
    suhu_tubuh: suhu,
    spo: spo,
    // Tambahkan field lainnya jika diperlukan
  };

  const url = editMode
    ? "/flat-able-ver2/backend/web/index.php?r=visitasi/update&id=" + editId
    : "/flat-able-ver2/backend/web/index.php?r=visitasi/create";

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
        alert(response.message);
        resetForm();
        $("#form-view").hide();
        $("#matrix-view").show();
        loadVisitasi();
      } else {
        alert("Error: " + (response.message || "Gagal menyimpan data"));
        console.error("Validation errors:", response.errors);
      }
    },
    error: function (xhr, status, error) {
      console.error("AJAX Error:", error);
      console.error("Response:", xhr.responseText);
      alert("Terjadi kesalahan saat menyimpan data");
    },
  });
}

/**
 * Edit visitasi - load data to form
 */
function editVisitasi(id) {
  console.log("Editing visitasi ID:", id);

  $.ajax({
    url: "/flat-able-ver2/backend/web/index.php?r=visitasi/view&id=" + id,
    method: "GET",
    dataType: "json",
    success: function (response) {
      if (response.success) {
        const data = response.data;
        d;
        $("#id_jamaah").val(data.id_jamaah);
        $("#display-nama-lengkap").text(data.nama_jamaah || "-");
        $("#display-nama-profil").text(data.nama_jamaah || "-");
        $("#display-jenis-kelamin").text(data.jenis_kelamin || "-");
        $("#display-umur").text(data.umur ? data.umur + " Tahun" : "-");
        $("#display-nomor-porsi").text(data.nomor_porsi || "-");
        $("#display-nomor-paspor").text(data.nomor_paspor || "-");

        $("#tanggal_visitasi").val(data.tanggal_visitasi || "");
        $("#lokasi").val(data.lokasi || "");
        $("#waktu_visitasi").val(data.waktu_visitasi || "");
        $("#keluhan_utama").val(data.keluhan_utama || "");
        $("#anamnesa").val(data.anamnesa || "");

        $('input[name="gcs"]').val(data.gcs || "");
        $('input[name="tekanan_darah"]').val(data.tekanan_darah || "");
        $('input[name="nadi"]').val(data.nadi || "");
        $('input[name="respirasi"]').val(data.respirasi || "");
        $('input[name="suhu_tubuh"]').val(data.suhu_tubuh || "");
        $('input[name="spo"]').val(data.spo || "");

        editMode = true;
        editId = id;

        $("#matrix-view").hide();
        $("#form-view").show();
        try {
          if (typeof refreshDeleteBtn === "function") refreshDeleteBtn();
        } catch (e) {}
      } else {
        alert("Data visitasi tidak ditemukan");
      }
    },

    error: function () {
      alert("Gagal memuat data visitasi");
    },
  });
}

/**
 * Delete visitasi
 */
function hapusVisitasi(id) {
  Swal.fire({
    icon: "question",
    title: "Yakin Hapus Data?",
    text: "Data yang dihapus tidak dapat dikembalikan!",
    showCancelButton: true,
    confirmButtonText: "Ya, Hapus!",
    cancelButtonText: "Batal",
    confirmButtonColor: "#26c281",
    cancelButtonColor: "#4680ff",
  }).then(function (result) {
    if (!result.isConfirmed) return;

    console.log("Deleting visitasi ID:", id);
    $.ajax({
      url: "/flat-able-ver2/backend/web/index.php?r=visitasi/delete&id=" + id,
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
          });
          loadVisitasi();
        } else {
          Swal.fire({
            icon: "error",
            title: "Error!",
            text: response.message || "Gagal menghapus data",
          });
        }
      },
      error: function (xhr, status, error) {
        console.error("Error:", error);
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
  $("#visitasi-form")[0].reset();
  $("#id_jamaah").val("");
  $("#display-nama-lengkap").text("-");
  $("#display-nama-profil").text("-");
  $("#display-jenis-kelamin").text("-");
  $("#display-umur").text("-");
  $("#display-nomor-porsi").text("-");
  $("#display-nomor-paspor").text("-");
  editMode = false;
  editId = null;
}

function refreshDeleteBtn() {
  const $btn = $("#btn-hapus-visitasi");
  if ($btn.length === 0) return;
  if (editMode && editId) $btn.show();
  else $btn.hide();
}

$(document).on("click", "#btn-hapus-visitasi", function (e) {
  e.preventDefault();
  if (!editMode || !editId) {
    Swal.fire({
      icon: "warning",
      title: "Tidak ada data",
      text: "Tidak ada visitasi untuk dihapus",
    });
    return;
  }
  Swal.fire({
    icon: "question",
    title: "Yakin Hapus Data?",
    text: "Data yang dihapus tidak dapat dikembalikan!",
    showCancelButton: true,
    confirmButtonText: "Ya, Hapus!",
    cancelButtonText: "Batal",
    confirmButtonColor: "#26c281",
    cancelButtonColor: "#4680ff",
  }).then(function (result) {
    if (!result.isConfirmed) return;

    $.ajax({
      url:
        "/flat-able-ver2/backend/web/index.php?r=visitasi/delete&id=" + editId,
      method: "POST",
      dataType: "json",
      success: function (resp) {
        if (resp && resp.success) {
          Swal.fire({
            icon: "success",
            title: "Berhasil!",
            text: resp.message || "Data berhasil dihapus",
            showConfirmButton: false,
            timer: 2000,
          });
          resetForm();
          $("#form-view").hide();
          $("#matrix-view").show();
          loadVisitasi();
        } else {
          Swal.fire({
            icon: "error",
            title: "Error!",
            text: resp.message || "Gagal menghapus data",
          });
        }
      },
      error: function () {
        Swal.fire({
          icon: "error",
          title: "Error!",
          text: "Gagal menghapus data",
        });
      },
    });
  });
});
