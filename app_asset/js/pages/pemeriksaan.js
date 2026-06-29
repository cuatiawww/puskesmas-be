// Global variables
let editMode = false;
let editId = null;
console.log("=== PEMERIKSAAN JS DIMUAT ===");

function waitForDataTable(maxAttempts = 25, interval = 200, cb) {
  let attempts = 0;
  const iv = setInterval(function () {
    if (window.$ && $.fn && $.fn.DataTable) {
      clearInterval(iv);
      cb(null);
      return;
    }
    attempts++;
    if (attempts >= maxAttempts) {
      clearInterval(iv);
      cb(new Error("DataTables plugin not available"));
    }
  }, interval);
}

$(document).ready(function () {
  console.log("Inisialisasi halaman Pemeriksaan");

  (function loadIcd10Options() {
    $.ajax({
      url: "/flat-able-ver2/backend/web/index.php?r=data-icd-x/get-list",
      method: "GET",
      dataType: "json",
      success: function (resp) {
        const sel = $("#diagnosa_utama");
        sel.empty();
        sel.append('<option value="">-- Pilih Diagnosa (ICD-10) --</option>');
        if (resp && resp.success && Array.isArray(resp.data)) {
          resp.data.forEach(function (it) {
            sel.append(`<option value="${it.kode_icd}">${it.kode_icd} - ${it.nama_diagnosis}</option>`);
          });
        }
      },
      error: function () {
        console.warn('Gagal memuat daftar ICD-10 dari server');
      }
    });
  })();

  (function loadIcd9Options() {
    $.ajax({
      url: "/flat-able-ver2/backend/web/index.php?r=data-icd-ix/get-list",
      method: "GET",
      dataType: "json",
      success: function (resp) {
        const sel = $('[name="tindakan_medis"]');
        sel.empty();
        sel.append('<option value="">-- Pilih Tindakan Medis (ICD-9) --</option>');
        if (resp && resp.success && Array.isArray(resp.data)) {
          resp.data.forEach(function (it) {
            sel.append(`<option value="${it.kode_icd}">${it.kode_icd} - ${it.nama_prosedur}</option>`);
          });
        }
      },
      error: function () {
        console.warn('Gagal memuat daftar ICD-9 dari server');
      }
    });
  })();

  loadPemeriksaan();

  function loadPemeriksaan() {
    console.log("Memuat data pemeriksaan...");

    if (
      window.$ &&
      $.fn &&
      $.fn.DataTable &&
      $.fn.DataTable.isDataTable("#table-pemeriksaan")
    ) {
      $("#table-pemeriksaan").DataTable().destroy();
    } else if (!(window.$ && $.fn && $.fn.DataTable)) {
      console.warn(
        "DataTables plugin tidak tersedia saat mencoba menghancurkan tabel sebelumnya."
      );
    }

    $.ajax({
      url: "/flat-able-ver2/backend/web/index.php?r=pemeriksaan/get-list",
      method: "GET",
      dataType: "json",
      success: function (response) {
        console.log("Respon pemeriksaan:", response);
        let html = "";

        if (response.success && response.data && response.data.length > 0) {
          response.data.forEach(function (item, index) {
            html += `
              <tr>
                <td>${index + 1}</td>
                <td>${item.nama_lengkap || "-"}</td>
                <td>${item.nomor_porsi || "-"}</td>
                <td>${item.nomor_paspor || "-"}</td>
                <td>${item.tanggal_pemeriksaan || "-"}</td>
                <td>${item.total_pemeriksaan || "-"}</td>
                <td>${item.keluhan_terakhir || "Tidak ada keluhan"}</td>
                <td class="text-center">
                  <button class="btn btn-sm btn-warning" onclick="editPemeriksaan(${item.id_jamaah})" title="Pemeriksaan"><i class="ti ti-edit"></i></button>
                  <button class="btn btn-sm btn-danger" onclick="hapusPemeriksaan(${item.id})" title="Hapus"><i class="ti ti-trash"></i></button>
                </td>
              </tr>
            `;
          });
        } else {
          console.warn(
            "Pemeriksaan: tidak ada data atau response.success=false"
          );
          html =
            '<tr><td colspan="8" class="text-center">Tidak ada data</td></tr>';
        }

        $("#tbody-pemeriksaan").html(html);

        (function initPemeriksaanTable() {
          const opts = {
            pageLength: 10,
            lengthMenu: [[10,25,50],[10,25,50]],
            ordering: true,
            order: [[4, 'desc']],
            columnDefs: [ { orderable: false, targets: [0,7] }, { className: 'text-center', targets: [0,4,5,7] } ],
            searching: true,
            language: {
              search: "Cari:",
              lengthMenu: "Tampilkan _MENU_ data",
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
          };

          if (window.$ && $.fn && $.fn.DataTable) {
            $("#table-pemeriksaan").DataTable(opts);
            return;
          }

          console.warn(
            "DataTables belum dimuat, mencoba inisialisasi ulang sebentar..."
          );
          waitForDataTable(25, 200, function (err) {
            if (err) {
              console.error(
                "Plugin DataTables tidak ditemukan. Tabel tidak akan interaktif."
              );
              return;
            }
            $("#table-pemeriksaan").DataTable(opts);
          });
        })();
      },
      error: function (xhr, status, error) {
        console.error("Gagal memuat data pemeriksaan:", error);
        console.error("Status:", status);
        console.error("Response:", xhr.responseText);
        alert("Gagal memuat data pemeriksaan");
      },
    });
  }

  $("#btn-add-pemeriksaan").on("click", function () {
    $("#matrix-view").hide();
    $("#form-view").hide();
    $("#search-jamaah-section").show();
    loadJamaahList();
    setTimeout(function () {
      $("#select-jamaah").focus();
    }, 50);
  });

  function loadJamaahList() {
    $.ajax({
      url: "/flat-able-ver2/backend/web/index.php?r=data-jamaah/get-list",
      method: "GET",
      dataType: "json",
      success: function (response) {
        const select = $("#select-jamaah");
        select.empty();
        select.append('<option value="">-- Pilih Jamaah --</option>');

        if (response.success && response.data) {
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
        }
      },
      error: function (xhr, status, error) {
        console.error("Gagal memuat daftar jamaah:", error);
        alert("Gagal memuat data jamaah");
      },
    });
  }

  $("#select-jamaah").on("change", function () {
    $("#btn-select-jamaah").prop("disabled", !$(this).val());
  });
  $("#btn-select-jamaah").prop("disabled", true);

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
    $("#display-nama-lengkap").val(namaJamaah);
    $("#display-nama-profil").text(namaJamaah);
    $("#display-jenis-kelamin").val(jenisKelamin || "-");
    $("#display-umur").val(umur ? umur + " Tahun" : "-");
    $("#display-nomor-porsi").text(nomorPorsi || "-");
    $("#display-nomor-paspor").text(nomorPaspor || "-");
    $("#id_jamaah-feedback").hide();
    $("#display-nama-lengkap")
      .removeClass("border border-danger is-invalid")
      .addClass("is-valid");

    const today = new Date().toISOString().split("T")[0];
    $("#tanggal_pemeriksaan").val(today);
    $.ajax({
      url:
        "/flat-able-ver2/backend/web/index.php?r=data-jamaah/view&id=" +
        idJamaah,
      method: "GET",
      dataType: "json",
      success: function (resp) {
        if (resp.success && resp.data) {
          const jamaah = resp.data;
          const parts = [];
          if (jamaah.hotel_madinah) parts.push(jamaah.hotel_madinah);
          if (jamaah.sektor_daker_maktab)
            parts.push(jamaah.sektor_daker_maktab);
          const lokasiVal = parts.filter(Boolean).join(" - ");
          $("#lokasi").val(lokasiVal);

          $.ajax({
            url:
              "/flat-able-ver2/backend/web/index.php?r=pemeriksaan/view-by-jamaah&id_jamaah=" +
              idJamaah,
            method: "GET",
            dataType: "json",
            success: function (resp2) {
              if (resp2.success && resp2.data && resp2.data.waktu_pemeriksaan) {
                var t = resp2.data.waktu_pemeriksaan;
                if (t.indexOf(":") !== -1) {
                  var parts = t.split(":");
                  t =
                    (parts[0] || "00").padStart(2, "0") +
                    ":" +
                    (parts[1] || "00").padStart(2, "0");
                }
                $("#waktu_pemeriksaan").val(t);
              } else {
                var now = new Date();
                var hh = String(now.getHours()).padStart(2, "0");
                var mm = String(now.getMinutes()).padStart(2, "0");
                $("#waktu_pemeriksaan").val(hh + ":" + mm);
              }
            },
            error: function () {
              var now = new Date();
              var hh = String(now.getHours()).padStart(2, "0");
              var mm = String(now.getMinutes()).padStart(2, "0");
              $("#waktu_pemeriksaan").val(hh + ":" + mm);
            },
          });
        }
      },
      error: function () {
        $("#lokasi").val("");
        $("#waktu_pemeriksaan").val("");
      },
    });

    $("#search-jamaah-section").hide();
    $("#matrix-view").hide();

    $("#pemeriksaan-form .is-invalid, #pemeriksaan-form .is-valid").removeClass(
      "is-invalid is-valid"
    );
    $("#pemeriksaan-form .invalid-feedback, #pemeriksaan-form .valid-feedback")
      .hide()
      .text("");

    $("#form-view").show();

    editMode = false;
    editId = null;
  });

  function loadIcd10Options() {
    const select = $("#diagnosa_utama");
    if (!select.length) return;
    if (select.find("option").length > 1) return;
    $.ajax({
      url: "/flat-able-ver2/dist/api/get-icd10.php",
      method: "GET",
      dataType: "json",
      success: function (resp) {
        if (resp && resp.success && Array.isArray(resp.data)) {
          resp.data.forEach(function (item) {
            select.append(
              `<option value="${item.code}">${item.label}</option>`
            );
          });
        }
      },
      error: function () {
        select.append(
          '<option value="I10">I10 - Essential (primary) hypertension</option>'
        );
      },
    });
  }

  loadIcd10Options();

  let currentTerapi = [];

  function renderTerapiTable() {
    $("#terapi-table-body").empty();
    if (!currentTerapi || currentTerapi.length === 0) {
      $("#terapi-table-body").append(
        '<tr><td colspan="5" class="text-center text-muted">Belum ada terapi</td></tr>'
      );
      return;
    }
    currentTerapi.forEach(function (item, idx) {
      const row = `<tr data-index="${idx}">
                    <td class="text-center align-middle">${idx + 1}</td>
                    <td class="align-middle">${item.nama_obat}</td>
                    <td class="align-middle">${item.aturan_pakai}</td>
                    <td class="align-middle text-wrap">${
                      item.keterangan || ""
                    }</td>
                    <td class="text-center align-middle">
                      <button type="button" class="btn btn-sm btn-warning btn-edit-terapi me-1"><i class="ti ti-edit"></i></button>
                      <button type="button" class="btn btn-sm btn-danger btn-hapus-terapi"><i class="ti ti-trash"></i></button>
                    </td>
                  </tr>`;
      $("#terapi-table-body").append(row);
    });
  }

  let terapiEditIndex = null;

  $("#btn-kembali-pemeriksaan").on("click", function () {
    resetForm();
    $("#form-view").hide();
    $("#matrix-view").show();
  });

  $("#btn-simpan-pemeriksaan").on("click", function (e) {
    e.preventDefault();
    simpanPemeriksaan();
  });

  $("#pemeriksaan-form").on("keydown", "input, select", function (e) {
    if (e.key === "Enter") {
      e.preventDefault();
      simpanPemeriksaan();
    }
  });

  $("#pemeriksaan-form").on("submit", function (e) {
    e.preventDefault();
    simpanPemeriksaan();
  });

  $("#pemeriksaan-form").on(
    "input change blur",
    "input, textarea, select",
    function (e) {
      const $el = $(this);
      const name = $el.attr("name") || $el.attr("id");

      if (name === "edukasi_opt[]") {
        validateEdukasiGroup(this);
        return;
      }
      if (name === "kesimpulan") {
        validateKesimpulanGroup(this);
        return;
      }
      if (
        $el.is('[name="tindakan_medis"]') ||
        $el.is('[name="tindakan_lainnya"]')
      ) {
        validateTindakanGroup(this);
        if (
          $el.is('[name="tindakan_medis"]') &&
          ($el.val() || "").toString().trim().length > 0
        ) {
          $("#tindakan-feedback").hide().removeClass("d-block");
        }
        return;
      }
      if (
        $el.is('[name="thoraks"]') ||
        $el.is('[name="abdomen"]') ||
        $el.is('[name="neurologi"]') ||
        $el.is('[name="fisik_lainnya"]')
      ) {
        validateFisikGroup(this);
        return;
      }
      if ($el.is("#id_jamaah")) {
        validateJamaah();
        return;
      }
      validateField($el);
    }
  );

  function validateField($el) {
    if (!$el || !$el.length) return true;
    const id = $el.attr("id");
    const name = $el.attr("name");

    if (id === "keluhan_utama")
      return setState(
        "#keluhan_utama",
        ($el.val() || "").trim().length >= 5,
        "Keluhan Utama minimal 5 karakter"
      );
    if (id === "anamnesa")
      return setState(
        "#anamnesa",
        ($el.val() || "").trim().length >= 10,
        "Anamnesa minimal 10 karakter"
      );

    if (name === "gcs") {
      const v = ($el.val() || "").trim();
      if (v.length === 0)
        return setState('input[name="gcs"]', false, "Kesadaran wajib di isi");
      const m = v.match(/E(\d)\s*V(\d)\s*M(\d)/i);
      let ok = false;
      if (/^\d{1,2}$/.test(v)) {
        const num = parseInt(v, 10);
        ok = num >= 3 && num <= 15;
      } else if (m) {
        const E = parseInt(m[1], 10),
          V = parseInt(m[2], 10),
          M = parseInt(m[3], 10);
        const tot = E + V + M;
        ok =
          E >= 1 &&
          E <= 4 &&
          V >= 1 &&
          V <= 5 &&
          M >= 1 &&
          M <= 6 &&
          tot >= 3 &&
          tot <= 15;
      }
      return setState(
        'input[name="gcs"]',
        ok,
        "GCS harus dalam format E# V# M# (contoh: E4 V5 M6)",
        "GCS valid"
      );
    }

    if (name === "tekanan_darah") {
      const v = ($el.val() || "").trim();
      const m = v.match(/^(\d{2,3})\s*\/\s*(\d{2,3})$/);
      const ok = !!(
        m &&
        parseInt(m[1], 10) >= 70 &&
        parseInt(m[1], 10) <= 250 &&
        parseInt(m[2], 10) >= 40 &&
        parseInt(m[2], 10) <= 150
      );
      return setState(
        'input[name="tekanan_darah"]',
        ok,
        "Tekanan darah harus format Sistolik/Diastolik (contoh: 120/80)",
        "Tekanan darah valid"
      );
    }

    if (name === "nadi") {
      const v = parseInt($el.val() || "", 10);
      return setState(
        'input[name="nadi"]',
        !(isNaN(v) || v === 0) && Number.isInteger(v) && v >= 40 && v <= 200,
        "Nadi harus bilangan bulat 40–200",
        "Nadi valid"
      );
    }
    if (name === "respirasi") {
      const v = parseInt($el.val() || "", 10);
      return setState(
        'input[name="respirasi"]',
        !(isNaN(v) || v === 0) && Number.isInteger(v) && v >= 10 && v <= 60,
        "Respirasi harus bilangan bulat 10–60",
        "Respirasi valid"
      );
    }
    if (name === "suhu_tubuh") {
      const v = parseFloat($el.val() || "");
      return setState(
        'input[name="suhu_tubuh"]',
        !isNaN(v) && v >= 30.0 && v <= 45.0,
        "Suhu tubuh harus antara 30.0–45.0",
        "Suhu valid"
      );
    }
    if (name === "spo") {
      const v = parseInt($el.val() || "", 10);
      return setState(
        'input[name="spo"]',
        Number.isInteger(v) && v >= 50 && v <= 100,
        "SpO₂ harus 50–100",
        "SpO₂ valid"
      );
    }
    if (name === "gds") {
      const v = parseInt($el.val() || "", 10);
      return setState(
        'input[name="gds"]',
        Number.isInteger(v) && v >= 50 && v <= 600,
        "GDS harus 50–600",
        "GDS valid"
      );
    }
    if (name === "kolesterol") {
      const v = parseInt($el.val() || "", 10);
      return setState(
        'input[name="kolesterol"]',
        Number.isInteger(v) && v >= 100 && v <= 400,
        "Kolesterol harus 100–400",
        "Kolesterol valid"
      );
    }
    if (name === "asam_urat") {
      const v = parseFloat($el.val() || "");
      return setState(
        'input[name="asam_urat"]',
        !isNaN(v) && v >= 2.0 && v <= 10.0,
        "Asam urat harus 2.0–10.0",
        "Asam urat valid"
      );
    }
    if (name === "diagnosa_utama") {
      return setState(
        'select[name="diagnosa_utama"]',
        !!$el.val(),
        "Diagnosa Utama wajib dipilih (ICD-10)",
        "Diagnosa dipilih"
      );
    }
    if (id === "tanggal_pemeriksaan") {
      return setState(
        "#tanggal_pemeriksaan",
        ($el.val() || "").toString().trim().length > 0,
        "Tanggal Pemeriksaan wajib diisi!"
      );
    }

    if (($el.val() || "").toString().trim().length > 0) {
      $el.removeClass("is-invalid").addClass("is-valid");
      const $group = $el.closest(".input-group");
      const $invalidFb = $group.length
        ? $group.nextAll(".invalid-feedback").first()
        : $el.nextAll(".invalid-feedback").first();
      if ($invalidFb.length) {
        $invalidFb.hide().text("");
      }
    }
    return true;
  }

  function validateTindakanGroup(changedEl) {
    const $med = $('[name="tindakan_medis"]');
    const $lain = $('textarea[name="tindakan_lainnya"]');
    const medOk = ($med.val() || "").toString().trim().length > 0;
    const lainOk = ($lain.val() || "").toString().trim().length >= 5;
    const tindakanOk = medOk || lainOk;
    if (changedEl) {
      const $changed = $(changedEl);
      if (!tindakanOk) {
        $("#tindakan-feedback")
          .text(
            "Pilih kode ICD-9 (prosedur) atau isi Tindakan Lainnya (minimal 5 karakter)"
          )
          .show()
          .addClass("d-block");
        $changed.removeClass("is-valid").addClass("is-invalid");
        return false;
      }
      $("#tindakan-feedback").hide().removeClass("d-block");
      if ($changed.is('[name="tindakan_medis"]')) {
        if (medOk) $changed.removeClass("is-invalid").addClass("is-valid");
        else $changed.removeClass("is-invalid is-valid");
      } else {
        if (lainOk) $changed.removeClass("is-invalid").addClass("is-valid");
        else $changed.removeClass("is-invalid is-valid");
      }
      return true;
    }
    if (!tindakanOk) {
      $("#tindakan-feedback")
        .text(
          "Pilih kode ICD-9 (prosedur) atau isi Tindakan Lainnya (minimal 5 karakter)"
        )
        .show()
        .addClass("d-block");
      $med.removeClass("is-valid").addClass("is-invalid");
      $lain.removeClass("is-valid").addClass("is-invalid");
      return false;
    }
    $("#tindakan-feedback").hide().removeClass("d-block");
    $med.removeClass("is-invalid").addClass("is-valid");
    $lain.removeClass("is-invalid").addClass("is-valid");
    return true;
  }
  function validateEdukasiGroup(changedEl) {
    const $boxes = $('input[name="edukasi_opt[]"]');
    const checked = $boxes.filter(":checked");
    if (changedEl) {
      const $changed = $(changedEl);
      if ($changed.is(":checked")) {
        $changed.removeClass("is-invalid").addClass("is-valid");
        $("#edukasi_opt-feedback").hide().removeClass("d-block");
        return true;
      }
      if (checked.length > 0) {
        $changed.removeClass("is-invalid is-valid");
        $("#edukasi_opt-feedback").hide().removeClass("d-block");
        return true;
      }
      $("#edukasi_opt-feedback")
        .text("Minimal satu opsi edukasi harus dipilih")
        .show()
        .addClass("d-block");
      $changed.removeClass("is-valid").addClass("is-invalid");
      return false;
    }
    if (checked.length < 1) {
      $("#edukasi_opt-feedback")
        .text("Minimal satu opsi edukasi harus dipilih")
        .show()
        .addClass("d-block");
      $boxes.removeClass("is-valid").addClass("is-invalid");
      return false;
    }
    $("#edukasi_opt-feedback").hide().removeClass("d-block");
    $boxes.each(function () {
      if ($(this).is(":checked"))
        $(this).removeClass("is-invalid").addClass("is-valid");
      else $(this).removeClass("is-invalid is-valid");
    });
    return true;
  }

  function validateKesimpulanGroup(changedEl) {
    const $boxes = $('input[name="kesimpulan"]');
    const checked = $boxes.filter(":checked");
    if (changedEl) {
      const $changed = $(changedEl);
      if ($changed.is(":checked")) {
        $changed.removeClass("is-invalid").addClass("is-valid");
        $("#kesimpulan-feedback").hide().removeClass("d-block");
        return true;
      }
      if (checked.length > 0) {
        $changed.removeClass("is-invalid is-valid");
        $("#kesimpulan-feedback").hide().removeClass("d-block");
        return true;
      }
      $("#kesimpulan-feedback")
        .text("Minimal satu opsi kesimpulan harus dipilih")
        .show()
        .addClass("d-block");
      $changed.removeClass("is-valid").addClass("is-invalid");
      return false;
    }
    if (checked.length < 1) {
      $("#kesimpulan-feedback")
        .text("Minimal satu opsi kesimpulan harus dipilih")
        .show()
        .addClass("d-block");
      $boxes.removeClass("is-valid").addClass("is-invalid");
      return false;
    }
    $("#kesimpulan-feedback").hide().removeClass("d-block");
    $boxes.each(function () {
      if ($(this).is(":checked"))
        $(this).removeClass("is-invalid").addClass("is-valid");
      else $(this).removeClass("is-invalid is-valid");
    });
    return true;
  }

  function validateFisikGroup(changedEl) {
    const $thoraks = $('textarea[name="thoraks"]');
    const $abdomen = $('textarea[name="abdomen"]');
    const $neurologi = $('textarea[name="neurologi"]');
    const $lain = $('textarea[name="fisik_lainnya"]');
    const fThoraks = ($thoraks.val() || "").toString();
    const fAbdomen = ($abdomen.val() || "").toString();
    const fNeuro = ($neurologi.val() || "").toString();
    const fLain = ($lain.val() || "").toString();
    const totalLen = (
      fThoraks.trim() +
      fAbdomen.trim() +
      fNeuro.trim() +
      fLain.trim()
    ).length;
    if (changedEl) {
      const $changed = $(changedEl);
      if (totalLen === 0) {
        $("#fisik-feedback").hide();
        $changed.removeClass("is-invalid is-valid");
        return true;
      }
      $("#fisik-feedback").hide();
      if (($changed.val() || "").toString().trim().length > 0) {
        $changed.removeClass("is-invalid").addClass("is-valid");
      } else {
        $changed.removeClass("is-invalid is-valid");
      }
      return true;
    }
    if (totalLen === 0) {
      $("#fisik-feedback").hide();
      $thoraks.removeClass("is-invalid is-valid");
      $abdomen.removeClass("is-invalid is-valid");
      $neurologi.removeClass("is-invalid is-valid");
      $lain.removeClass("is-invalid is-valid");
      return true;
    }

    $("#fisik-feedback").hide();
    if (fThoraks.trim().length > 0)
      $thoraks.removeClass("is-invalid").addClass("is-valid");
    else $thoraks.removeClass("is-invalid is-valid");
    if (fAbdomen.trim().length > 0)
      $abdomen.removeClass("is-invalid").addClass("is-valid");
    else $abdomen.removeClass("is-invalid is-valid");
    if (fNeuro.trim().length > 0)
      $neurologi.removeClass("is-invalid").addClass("is-valid");
    else $neurologi.removeClass("is-invalid is-valid");
    if (fLain.trim().length > 0)
      $lain.removeClass("is-invalid").addClass("is-valid");
    else $lain.removeClass("is-invalid is-valid");
    return true;
  }

  function validateJamaah() {
    const idJ = $("#id_jamaah").val();
    if (!idJ) {
      $("#display-nama-lengkap")
        .removeClass("is-valid")
        .addClass("is-invalid")
        .addClass("border border-danger");
      $("#id_jamaah-feedback").show();
      return false;
    }
    $("#display-nama-lengkap")
      .removeClass("is-invalid border border-danger")
      .addClass("is-valid");
    $("#id_jamaah-feedback").hide();
    return true;
  }

  function simpanPemeriksaan() {
    const isValid = validatePemeriksaan();
    if (!isValid) {
      $("#pemeriksaan-form").addClass("was-validated");
      normalizeStates();
      const firstInvalid = $("#pemeriksaan-form .is-invalid").first();
      if (firstInvalid.length) {
        firstInvalid[0].scrollIntoView({ behavior: "smooth", block: "center" });
        firstInvalid.focus();
      }
      return;
    }
    const $saveBtn = $("#btn-simpan-pemeriksaan");
    if ($saveBtn.prop("disabled")) return; 
    const idJamaah = $("#id_jamaah").val();
    const tanggalPemeriksaan = $("#tanggal_pemeriksaan").val();

    function getValByName(name) {
      const $el = $('[name="' + name + '"]');
      if ($el.length === 0) return null;
      if ($el.is(":checkbox")) return $el.is(":checked") ? $el.val() || 1 : 0;
      if ($el.is(":radio"))
        return $('[name="' + name + '"]:checked')
          .map(function () {
            return this.value;
          })
          .get();
      return $el.val() || null;
    }

    const formData = {
      id_jamaah: idJamaah,
      tanggal_pemeriksaan: tanggalPemeriksaan,
      lokasi: $("#lokasi").val(),
      waktu_pemeriksaan: $("#waktu_pemeriksaan").val(),
      keluhan_utama: $("#keluhan_utama").val(),
      anamnesa: $("#anamnesa").val(),
      gcs: getValByName("gcs"),
      tekanan_darah: getValByName("tekanan_darah"),
      nadi: getValByName("nadi"),
      respirasi: getValByName("respirasi"),
      suhu_tubuh: getValByName("suhu_tubuh"),
      spo: getValByName("spo"),
      gds: getValByName("gds"),
      kolesterol: getValByName("kolesterol"),
      asam_urat: getValByName("asam_urat"),
      thoraks: getValByName("thoraks"),
      abdomen: getValByName("abdomen"),
      neurologi: getValByName("neurologi"),
      fisik_lainnya: getValByName("fisik_lainnya"),
      diagnosa_utama: getValByName("diagnosa_utama"),
      diagnosa_tambahan: getValByName("diagnosa_tambahan"),
      tindakan_medis: getValByName("tindakan_medis"),
      tindakan_lainnya: getValByName("tindakan_lainnya"),
      edukasi_opt: $('input[name="edukasi_opt[]"]:checked')
        .map(function () {
          return this.value;
        })
        .get(),
      kesimpulan: $('input[name="kesimpulan"]:checked')
        .map(function () {
          return this.value;
        })
        .get(),
      terapi: JSON.stringify(currentTerapi),
    };

    const url = editMode
      ? "/flat-able-ver2/backend/web/index.php?r=pemeriksaan/update&id=" +
        editId
      : "/flat-able-ver2/backend/web/index.php?r=pemeriksaan/create";

    function processServerErrors(errors) {
      $("#pemeriksaan-form .is-invalid").removeClass("is-invalid");
      $("#pemeriksaan-form .is-valid").removeClass("is-valid");
      $(
        "#edukasi_opt-feedback, #kesimpulan-feedback, #tindakan-feedback, #fisik-feedback"
      ).hide();
      $("#tindakan-feedback").removeClass("d-block");
      $(
        "#pemeriksaan-form .valid-icon, #pemeriksaan-form .input-group-text.valid-icon"
      ).remove();

      if (!errors || typeof errors !== "object") return;
      Object.keys(errors).forEach(function (field) {
        const msg = errors[field];
        if (field === "tindakan" || field === "tindakan_lainnya") {
          $("#tindakan-feedback")
            .text(Array.isArray(msg) ? msg.join("\n") : msg)
            .show()
            .addClass("d-block");
          $('[name="tindakan_medis"]')
            .removeClass("is-valid")
            .addClass("is-invalid");
          $('[name="tindakan_lainnya"]')
            .removeClass("is-valid")
            .addClass("is-invalid");
          $('[name="tindakan_medis"]').nextAll(".valid-icon").hide();
          $('[name="tindakan_medis"]')
            .closest(".input-group")
            .find(".input-group-text.valid-icon")
            .hide();
          $('[name="tindakan_lainnya"]').nextAll(".valid-icon").hide();
          return;
        }
        if (field === "edukasi") {
          $("#edukasi_opt-feedback")
            .text(Array.isArray(msg) ? msg.join("\n") : msg)
            .show()
            .addClass("d-block");
          $('input[name="edukasi_opt[]"]')
            .removeClass("is-valid")
            .addClass("is-invalid");
          $('input[name="edukasi_opt[]"]').each(function () {
            $(this).nextAll(".valid-icon").hide();
          });
          return;
        }
        if (field === "kesimpulan") {
          $("#kesimpulan-feedback")
            .text(Array.isArray(msg) ? msg.join("\n") : msg)
            .show()
            .addClass("d-block");
          $('input[name="kesimpulan"]')
            .removeClass("is-valid")
            .addClass("is-invalid");
          $('input[name="kesimpulan"]').each(function () {
            $(this).nextAll(".valid-icon").hide();
          });
          return;
        }
        if (field === "fisik") {
          $("#fisik-feedback")
            .text(Array.isArray(msg) ? msg.join("\n") : msg)
            .show();
          $('[name="thoraks"]').removeClass("is-valid").addClass("is-invalid");
          $('[name="abdomen"]').removeClass("is-valid").addClass("is-invalid");
          $('[name="neurologi"]')
            .removeClass("is-valid")
            .addClass("is-invalid");
          $('[name="fisik_lainnya"]')
            .removeClass("is-valid")
            .addClass("is-invalid");
          return;
        }

        const $fById = $("#" + field);
        if ($fById.length) {
          const $group = $fById.closest(".input-group");
          const $invalidFb = $group.length
            ? $group.nextAll(".invalid-feedback").first()
            : $fById.nextAll(".invalid-feedback").first();
          const $validFb = $group.length
            ? $group.nextAll(".valid-feedback").first()
            : $fById.nextAll(".valid-feedback").first();
          $fById.removeClass("is-valid").addClass("is-invalid");
          if ($validFb.length) {
            $validFb.text("");
            $validFb.hide();
          }
          if ($invalidFb.length) {
            $invalidFb.text(Array.isArray(msg) ? msg.join("\n") : msg);
            $invalidFb.show();
          }
          return;
        }

        const $fByName = $('[name="' + field + '"]');
        if ($fByName.length) {
          const $group = $fByName.closest(".input-group");
          const $invalidFb = $group.length
            ? $group.nextAll(".invalid-feedback").first()
            : $fByName.nextAll(".invalid-feedback").first();
          const $validFb = $group.length
            ? $group.nextAll(".valid-feedback").first()
            : $fByName.nextAll(".valid-feedback").first();
          $fByName.removeClass("is-valid").addClass("is-invalid");
          if ($validFb.length) {
            $validFb.text("");
            $validFb.hide();
          }
          if ($invalidFb.length) {
            $invalidFb.text(Array.isArray(msg) ? msg.join("\n") : msg);
            $invalidFb.show();
          }
          return;
        }
      });

      $("#pemeriksaan-form").addClass("was-validated");
      const firstInvalid = $("#pemeriksaan-form .is-invalid").first();
      if (firstInvalid.length) {
        firstInvalid[0].scrollIntoView({ behavior: "smooth", block: "center" });
        firstInvalid.focus();
      }
      normalizeStates();
    }

    $saveBtn.prop("disabled", true);
    if (!$saveBtn.data("orig-html"))
      $saveBtn.data("orig-html", $saveBtn.html());
    $saveBtn.html(
      '<i class="ph-duotone ph-floppy-disk-back"></i> Menyimpan... <span class="spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true"></span>'
    );
    $.ajax({
      url: url,
      method: "POST",
      data: formData,
      dataType: "json",
      success: function (response) {
        if (response.success) {
          Swal.fire({
            icon: "success",
            title: "Berhasil",
            text: response.message || "Data pemeriksaan berhasil disimpan",
            showConfirmButton: false,
            timer: 1400,
          });
          resetForm();
          $("#form-view").hide();
          $("#matrix-view").show();
          loadPemeriksaan();
        } else {
          if (response.errors) {
            processServerErrors(response.errors);
          } else {
            Swal.fire({
              icon: "error",
              title: "Gagal",
              text: response.message || "Gagal menyimpan data",
            });
          }
        }
      },
      error: function (xhr, status, error) {
        if (xhr && xhr.status === 400) {
          let body = null;
          try {
            body =
              xhr.responseJSON ||
              (xhr.responseText ? JSON.parse(xhr.responseText) : null);
          } catch (e) {
            body = null;
          }
          if (body && body.errors) {
            processServerErrors(body.errors);
            return;
          }
        }
        console.error("Kesalahan AJAX:", error);
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "Terjadi kesalahan saat menyimpan data",
        });
      },
      complete: function () {
        $saveBtn.prop("disabled", false);
        if ($saveBtn.data("orig-html")) {
          $saveBtn.html($saveBtn.data("orig-html"));
          $saveBtn.removeData("orig-html");
        }
      },
    });
  }

  $("#btn-simpan-terapi").on("click", function () {
    const nama = ($("#input-nama-obat").val() || "").trim();
    const aturan = ($("#input-aturan-pakai").val() || "").trim();
    const keterangan = ($("#input-keterangan").val() || "").trim();

    let modalOk = true;
    if (nama.length < 2) {
      setState(
        "#input-nama-obat",
        false,
        "Nama obat wajib diisi (minimal 2 karakter)"
      );
      modalOk = false;
    } else {
      setState("#input-nama-obat", true, null, "Nama obat valid");
    }

    if (aturan.length < 2) {
      setState(
        "#input-aturan-pakai",
        false,
        "Aturan pakai wajib diisi (minimal 2 karakter)"
      );
      modalOk = false;
    } else {
      setState("#input-aturan-pakai", true, null, "Aturan valid");
    }

    if (keterangan.length < 1) {
      setState("#input-keterangan", false, "Keterangan wajib diisi");
      modalOk = false;
    } else {
      setState("#input-keterangan", true, null, "Keterangan valid");
    }

    if (!modalOk) {
      $("#modalTambahTerapi .is-invalid:first").focus();
      return;
    }
    if (terapiEditIndex !== null && typeof terapiEditIndex === "number") {
      currentTerapi[terapiEditIndex] = {
        nama_obat: nama,
        aturan_pakai: aturan,
        keterangan: keterangan,
      };
      Swal.fire({
        icon: "success",
        title: "Berhasil",
        text: "Terapi berhasil diperbarui",
        showConfirmButton: false,
        timer: 1200,
      });
      terapiEditIndex = null;
    } else {
      currentTerapi.push({
        nama_obat: nama,
        aturan_pakai: aturan,
        keterangan: keterangan,
      });
      Swal.fire({
        icon: "success",
        title: "Berhasil",
        text: "Terapi berhasil ditambahkan",
        showConfirmButton: false,
        timer: 1200,
      });
    }
    formDirty = true;
    renderTerapiTable();
    $("#formTambahTerapi")[0].reset();
    $("#formTambahTerapi .form-control").removeClass("is-valid is-invalid");
    $("#formTambahTerapi .invalid-feedback, #formTambahTerapi .valid-feedback")
      .hide()
      .text("");
    $(
      "#formTambahTerapi .valid-icon, #formTambahTerapi .input-group-text.valid-icon"
    ).remove();
    $("#modalTambahTerapi").modal("hide");
  });

  $("#terapi-table-body").on("click", ".btn-hapus-terapi", function () {
    const $tr = $(this).closest("tr");
    const idx = parseInt($tr.data("index"), 10);
    if (isNaN(idx)) return;

    Swal.fire({
      icon: "question",
      title: "Yakin Hapus Terapi?",
      text: "Terapi akan dihapus dari daftar. Lanjutkan?",
      showCancelButton: true,
      confirmButtonColor: "#d33",
      cancelButtonColor: "#3085d6",
      confirmButtonText: "Ya, Hapus",
      cancelButtonText: "Batal",
    }).then((result) => {
      if (!result.isConfirmed) return;
      currentTerapi.splice(idx, 1);
      formDirty = true;
      renderTerapiTable();
      Swal.fire({
        icon: "success",
        title: "Dihapus",
        text: "Terapi berhasil dihapus",
        showConfirmButton: false,
        timer: 1000,
      });
    });
  });

  $("#terapi-table-body").on("click", ".btn-edit-terapi", function () {
    const $tr = $(this).closest("tr");
    const idx = parseInt($tr.data("index"), 10);
    if (isNaN(idx)) return;
    const item = currentTerapi[idx];
    if (!item) return;
    $("#input-nama-obat").val(item.nama_obat);
    $("#input-aturan-pakai").val(item.aturan_pakai);
    $("#input-keterangan").val(item.keterangan || "");
    terapiEditIndex = idx;
    updateTerapiModalState();
    $("#modalTambahTerapi").modal("show");
  });

  function updateTerapiModalState() {
    const nama = ($("#input-nama-obat").val() || "").trim();
    const aturan = ($("#input-aturan-pakai").val() || "").trim();
    const keterangan = ($("#input-keterangan").val() || "").trim();
    const ok = nama.length >= 2 && aturan.length >= 2 && keterangan.length >= 1;
    $("#btn-simpan-terapi").prop("disabled", !ok);
  }

  $("#modalTambahTerapi").on("shown.bs.modal", function () {
    $("#input-nama-obat").trigger("focus");
    if (terapiEditIndex !== null && typeof terapiEditIndex === "number") {
      $("#btn-simpan-terapi").text("Perbarui");
    } else {
      $("#btn-simpan-terapi").text("Simpan");
    }
    updateTerapiModalState();
  });

  $("#modalTambahTerapi").on("hidden.bs.modal", function () {
    $("#formTambahTerapi")[0].reset();
    $("#formTambahTerapi .form-control").removeClass("is-valid is-invalid");
    $("#formTambahTerapi .invalid-feedback, #formTambahTerapi .valid-feedback")
      .hide()
      .text("");
    $(
      "#formTambahTerapi .valid-icon, #formTambahTerapi .input-group-text.valid-icon"
    ).remove();
    $("#btn-simpan-terapi").prop("disabled", false).text("Simpan");
    terapiEditIndex = null;
  });

  $("#formTambahTerapi").on("input", ".form-control", function () {
    const $el = $(this);
    const id = $el.attr("id");
    const val = ($el.val() || "").toString().trim();
    const $group = $el.closest(".input-group");
    const $invalidFb = $group.length
      ? $group.nextAll(".invalid-feedback").first()
      : $el.nextAll(".invalid-feedback").first();
    const $validFb = $group.length
      ? $group.nextAll(".valid-feedback").first()
      : $el.nextAll(".valid-feedback").first();
    if ($invalidFb.length) $invalidFb.hide().text("");
    if ($validFb.length) $validFb.hide().text("");

    if (id === "input-nama-obat") {
      if (val.length >= 2)
        setState("#input-nama-obat", true, null, "Nama obat valid");
      else {
        $el.removeClass("is-valid is-invalid");
      }
    } else if (id === "input-aturan-pakai") {
      if (val.length >= 2)
        setState("#input-aturan-pakai", true, null, "Aturan valid");
      else {
        $el.removeClass("is-valid is-invalid");
      }
    } else if (id === "input-keterangan") {
      if (val.length >= 1)
        setState("#input-keterangan", true, null, "Keterangan valid");
      else {
        $el.removeClass("is-valid is-invalid");
      }
    }

    updateTerapiModalState();
  });

  function validatePemeriksaan() {
    let ok = true;
    const keluhan = $("#keluhan_utama");
    const kelVal = (keluhan.val() || "").trim();
    const kelMsg =
      kelVal.length === 0
        ? "Keluhan Utama wajib diisi!"
        : "Keluhan Utama minimal 5 karakter";
    ok = ok && setState("#keluhan_utama", kelVal.length >= 5, kelMsg);

    const anamnesa = $("#anamnesa");
    const anaVal = (anamnesa.val() || "").trim();
    const anaMsg =
      anaVal.length === 0
        ? "Anamnesa wajib diisi!"
        : "Anamnesa minimal 10 karakter";
    ok = ok && setState("#anamnesa", anaVal.length >= 10, anaMsg);

    const gcs = $('input[name="gcs"]');
    const gcsV = (gcs.val() || "").trim();
    const gcsMatch = gcsV.match(/E(\d)\s*V(\d)\s*M(\d)/i);
    let gcsOk = false;
    if (gcsV.length === 0) {
      gcsOk = false;
      ok = ok && setState('input[name="gcs"]', false, "Kesadaran wajib di isi");
    } else {
      if (/^\d{1,2}$/.test(gcsV)) {
        const num = parseInt(gcsV, 10);
        gcsOk = num >= 3 && num <= 15;
      } else if (gcsMatch) {
        const E = parseInt(gcsMatch[1], 10),
          V = parseInt(gcsMatch[2], 10),
          M = parseInt(gcsMatch[3], 10);
        const tot = E + V + M;
        gcsOk =
          E >= 1 &&
          E <= 4 &&
          V >= 1 &&
          V <= 5 &&
          M >= 1 &&
          M <= 6 &&
          tot >= 3 &&
          tot <= 15;
      }
      ok =
        ok &&
        setState(
          'input[name="gcs"]',
          gcsOk,
          "GCS harus dalam format E# V# M# (contoh: E4 V5 M6)",
          "GCS valid"
        );
    }

    const td = $('input[name="tekanan_darah"]');
    const tdV = (td.val() || "").trim();
    const tdM = tdV.match(/^(\d{2,3})\s*\/\s*(\d{2,3})$/);
    let tdOk = false;
    if (tdV.length === 0) {
      ok =
        ok &&
        setState(
          'input[name="tekanan_darah"]',
          false,
          "Tekanan darah wajib diisi"
        );
    } else {
      if (tdM) {
        const s = parseInt(tdM[1], 10),
          d = parseInt(tdM[2], 10);
        tdOk = s >= 70 && s <= 250 && d >= 40 && d <= 150;
      }
      ok =
        ok &&
        setState(
          'input[name="tekanan_darah"]',
          tdOk,
          "Tekanan darah harus format Sistolik/Diastolik (contoh: 120/80)",
          "Tekanan darah valid"
        );
    }

    const nadi = $('input[name="nadi"]');
    const nval = parseInt(nadi.val() || "", 10);
    const nOk =
      !(isNaN(nval) || nval === 0) &&
      Number.isInteger(nval) &&
      nval >= 40 &&
      nval <= 200;
    if ((nadi.val() || "").toString().trim().length === 0)
      ok = ok && setState('input[name="nadi"]', false, "Nadi wajib diisi");
    else
      ok =
        ok &&
        setState(
          'input[name="nadi"]',
          nOk,
          "Nadi harus bilangan bulat 40–200",
          "Nadi valid"
        );

    const rr = $('input[name="respirasi"]');
    const rval = parseInt(rr.val() || "", 10);
    const rrOk =
      !(isNaN(rval) || rval === 0) &&
      Number.isInteger(rval) &&
      rval >= 10 &&
      rval <= 60;
    if ((rr.val() || "").toString().trim().length === 0)
      ok =
        ok &&
        setState('input[name="respirasi"]', false, "Respirasi wajib diisi");
    else
      ok =
        ok &&
        setState(
          'input[name="respirasi"]',
          rrOk,
          "Respirasi harus bilangan bulat 10–60",
          "Respirasi valid"
        );

    const suhu = $('input[name="suhu_tubuh"]');
    const suhuVal = parseFloat(suhu.val() || "");
    if ((suhu.val() || "").toString().trim().length === 0)
      ok =
        ok &&
        setState('input[name="suhu_tubuh"]', false, "Suhu tubuh wajib diisi");
    else
      ok =
        ok &&
        setState(
          'input[name="suhu_tubuh"]',
          !isNaN(suhuVal) && suhuVal >= 30.0 && suhuVal <= 45.0,
          "Suhu tubuh harus antara 30.0–45.0",
          "Suhu valid"
        );

    const spo = $('input[name="spo"]');
    const spoVal = parseInt(spo.val() || "", 10);
    if ((spo.val() || "").toString().trim().length === 0)
      ok = ok && setState('input[name="spo"]', false, "SpO₂ wajib diisi");
    else
      ok =
        ok &&
        setState(
          'input[name="spo"]',
          Number.isInteger(spoVal) && spoVal >= 50 && spoVal <= 100,
          "SpO₂ harus 50–100",
          "SpO₂ valid"
        );

    const gds = $('input[name="gds"]');
    const gdsVal = parseInt(gds.val() || "", 10);
    if ((gds.val() || "").toString().trim().length === 0)
      ok = ok && setState('input[name="gds"]', false, "GDS wajib diisi");
    else
      ok =
        ok &&
        setState(
          'input[name="gds"]',
          Number.isInteger(gdsVal) && gdsVal >= 50 && gdsVal <= 600,
          "GDS harus 50–600",
          "GDS valid"
        );

    const kol = $('input[name="kolesterol"]');
    const kolVal = parseInt(kol.val() || "", 10);
    if ((kol.val() || "").toString().trim().length === 0)
      ok =
        ok &&
        setState('input[name="kolesterol"]', false, "Kolesterol wajib diisi");
    else
      ok =
        ok &&
        setState(
          'input[name="kolesterol"]',
          Number.isInteger(kolVal) && kolVal >= 100 && kolVal <= 400,
          "Kolesterol harus 100–400",
          "Kolesterol valid"
        );

    const au = $('input[name="asam_urat"]');
    const auVal = parseFloat(au.val() || "");
    if ((au.val() || "").toString().trim().length === 0)
      ok =
        ok &&
        setState('input[name="asam_urat"]', false, "Asam urat wajib diisi");
    else
      ok =
        ok &&
        setState(
          'input[name="asam_urat"]',
          !isNaN(auVal) && auVal >= 2.0 && auVal <= 10.0,
          "Asam urat harus 2.0–10.0",
          "Asam urat valid"
        );

    const diag = $('select[name="diagnosa_utama"]');
    const diagVal = diag.val();
    ok =
      ok &&
      setState(
        'select[name="diagnosa_utama"]',
        !!diagVal,
        "Diagnosa Utama wajib dipilih (ICD-10)",
        "Diagnosa dipilih"
      );

    const idJ = $("#id_jamaah").val();
    if (!idJ) {
      $("#display-nama-lengkap")
        .removeClass("is-valid")
        .addClass("is-invalid")
        .addClass("border border-danger");
      $("#id_jamaah-feedback").show();
      ok = false;
    } else {
      $("#display-nama-lengkap")
        .removeClass("is-invalid border border-danger")
        .addClass("is-valid");
      $("#id_jamaah-feedback").hide();
    }

    const tindakanOk =
      ($('[name="tindakan_medis"]').val() || "").toString().trim().length > 0 ||
      ($('textarea[name="tindakan_lainnya"]').val() || "").toString().trim()
        .length >= 5;
    if (!tindakanOk) {
      $("#tindakan-feedback")
        .text(
          "Pilih kode ICD-9 (prosedur) atau isi Tindakan Lainnya (minimal 5 karakter)"
        )
        .show()
        .addClass("d-block");
      $('[name="tindakan_medis"]').addClass("is-invalid");
      $('textarea[name="tindakan_lainnya"]').addClass("is-invalid");
      ok = false;
    } else {
      $("#tindakan-feedback").hide().removeClass("d-block");
      $('[name="tindakan_medis"]')
        .removeClass("is-invalid")
        .addClass("is-valid");
      $('textarea[name="tindakan_lainnya"]')
        .removeClass("is-invalid")
        .addClass("is-valid");
    }

    const edukasiChecked = $('input[name="edukasi_opt[]"]:checked').length;
    if (edukasiChecked < 1) {
      $("#edukasi_opt-feedback")
        .text("Minimal satu opsi edukasi harus dipilih")
        .show();
      ok = false;
      $('input[name="edukasi_opt[]"]')
        .removeClass("is-valid")
        .addClass("is-invalid");
    } else {
      $("#edukasi_opt-feedback").hide();
      $('input[name="edukasi_opt[]"]')
        .removeClass("is-invalid")
        .addClass("is-valid");
    }

    const kesimpChecked = $('input[name="kesimpulan"]:checked').length;
    if (kesimpChecked < 1) {
      $("#kesimpulan-feedback")
        .text("Minimal satu kesimpulan harus dipilih")
        .show();
      ok = false;
      $('input[name="kesimpulan"]')
        .removeClass("is-valid")
        .addClass("is-invalid");
    } else {
      $("#kesimpulan-feedback").hide();
      $('input[name="kesimpulan"]')
        .removeClass("is-invalid")
        .addClass("is-valid");
    }

    const fThoraks = $('textarea[name="thoraks"]').val() || "";
    const fAbdomen = $('textarea[name="abdomen"]').val() || "";
    const fNeuro = $('textarea[name="neurologi"]').val() || "";
    const fLain = $('textarea[name="fisik_lainnya"]').val() || "";
    if ((fThoraks + fAbdomen + fNeuro + fLain).trim().length === 0) {
      $("#fisik-feedback").show();
      $('textarea[name="thoraks"]').addClass("is-invalid");
      $('textarea[name="abdomen"]').addClass("is-invalid");
      $('textarea[name="neurologi"]').addClass("is-invalid");
      $('textarea[name="fisik_lainnya"]').addClass("is-invalid");
      ok = false;
    } else {
      $("#fisik-feedback").hide();
      $('textarea[name="thoraks"]')
        .removeClass("is-invalid")
        .addClass("is-valid");
      $('textarea[name="abdomen"]')
        .removeClass("is-invalid")
        .addClass("is-valid");
      $('textarea[name="neurologi"]')
        .removeClass("is-invalid")
        .addClass("is-valid");
      $('textarea[name="fisik_lainnya"]')
        .removeClass("is-invalid")
        .addClass("is-valid");
    }

    normalizeStates();
    return ok;
  }

  function normalizeStates() {
    $("#pemeriksaan-form .form-control").each(function () {
      const $el = $(this);
      const $group = $el.closest(".input-group");
      let $invalidFb = $group.length
        ? $group.nextAll(".invalid-feedback").first()
        : $el.nextAll(".invalid-feedback").first();
      if (!$invalidFb.length) {
        $invalidFb = $el.parent().nextAll(".invalid-feedback").first();
      }
      if ($invalidFb.length && $invalidFb.is(":visible")) {
        $el.removeClass("is-valid").addClass("is-invalid");
        const $validFb = $group.length
          ? $group.nextAll(".valid-feedback").first()
          : $el.nextAll(".valid-feedback").first();
        if ($validFb.length) {
          $validFb.hide().text("");
        }
        if ($group.length) {
          $group.find(".input-group-text.valid-icon").hide();
        } else {
          $el.nextAll(".valid-icon").hide();
        }
      }
    });
    $("#pemeriksaan-form .form-control.is-invalid").removeClass("is-valid");
  }

  function setState(selector, valid, invalidMessage, validMessage) {
    const $el = $(selector);
    if (!$el.length) return true;

    const $group = $el.closest(".input-group");
    const $invalidFb = $group.length
      ? $group.nextAll(".invalid-feedback").first()
      : $el.nextAll(".invalid-feedback").first();
    const $validFb = $group.length
      ? $group.nextAll(".valid-feedback").first()
      : $el.nextAll(".valid-feedback").first();

    if (valid) {
      $el.removeClass("is-invalid border border-danger").addClass("is-valid");
      if ($invalidFb.length) {
        $invalidFb.text("");
        $invalidFb.hide();
      }
      if ($validFb.length) {
        if (validMessage) $validFb.text(validMessage);
        $validFb.show();
      }
      if ($group.length) {
        let $icon = $group.find(".input-group-text.valid-icon");
        if ($icon.length === 0) {
          $icon = $("");
          $group.append($icon);
        }
        $icon.show();
        $group.removeClass("border border-danger");
        $group
          .find(".form-control")
          .removeClass("is-invalid")
          .addClass("is-valid");
      }
      return true;
    } else {
      $el.removeClass("is-valid").addClass("is-invalid border border-danger");
      if ($validFb.length) {
        $validFb.text("");
        $validFb.hide();
      }
      if ($invalidFb.length) {
        if (invalidMessage) $invalidFb.text(invalidMessage);
        $invalidFb.show();
      }
      if ($group.length) {
        $group.find(".input-group-text.valid-icon").remove();
        $group.addClass("border border-danger");
        $group
          .find(".form-control")
          .removeClass("is-valid")
          .addClass("is-invalid");
      } else {
        $el.nextAll(".valid-icon").remove();
      }
      try {
        $el.parentsUntil("form").find(".is-valid").removeClass("is-valid");
        $el
          .parentsUntil("form")
          .find(".border-success")
          .removeClass("border-success");
      } catch (e) {}

      $el.addClass("border border-danger");

      return false;
    }
  }

  window.editPemeriksaan = function (idJamaah) {
    $.ajax({
      url:
        "/flat-able-ver2/backend/web/index.php?r=pemeriksaan/get-last-pemeriksaan&id_jamaah=" +
        idJamaah,
      method: "GET",
      dataType: "json",
      success: function (response) {
        if (response.status === 'success' || response.success) {
          const data = response.data;

          // Populate form and profile card
          $("#id_jamaah").val(data.id_jamaah);
          $("#display-nama-lengkap").val(data.nama_jamaah);
          $("#display-nama-profil").text(data.nama_jamaah);
          $("#display-jenis-kelamin").val(data.jenis_kelamin || "-");
          $("#display-umur").val(data.umur ? data.umur + " Tahun" : "-");
          $("#display-nomor-porsi").text(data.nomor_porsi || "-");
          $("#display-nomor-paspor").text(data.nomor_paspor || "-");

          // Populate form fields
          $("#tanggal_pemeriksaan").val(data.tanggal_pemeriksaan || "");
          $("#lokasi").val(data.lokasi || "");
          $("#waktu_pemeriksaan").val(data.waktu_pemeriksaan || "");
          $("#keluhan_utama").val(data.keluhan_utama || "");
          $("#anamnesa").val(data.anamnesa || "");

          // Populate objektif / vitals if present
          $('input[name="gcs"]').val(data.gcs || "");
          $('input[name="tekanan_darah"]').val(data.tekanan_darah || "");
          $('input[name="nadi"]').val(data.nadi || "");
          $('input[name="respirasi"]').val(data.respirasi || "");
          $('input[name="suhu_tubuh"]').val(data.suhu_tubuh || "");
          $('input[name="spo"]').val(data.spo || "");
          $('input[name="gds"]').val(data.gds || "");
          $('input[name="kolesterol"]').val(data.kolesterol || "");
          $('input[name="asam_urat"]').val(data.asam_urat || "");

          // Populate pemeriksaan fisik
          $('textarea[name="thoraks"]').val(data.thoraks || "");
          $('textarea[name="abdomen"]').val(data.abdomen || "");
          $('textarea[name="neurologi"]').val(data.neurologi || "");
          $('textarea[name="fisik_lainnya"]').val(data.fisik_lainnya || "");

          // Populate assesmen & plan
          $('select[name="diagnosa_utama"]').val(data.diagnosa_utama || "");
          $('input[name="diagnosa_tambahan"]').val(
            data.diagnosa_tambahan || ""
          );
          const tmVal = data.tindakan_medis || "";
          const $tindakanSel = $('[name="tindakan_medis"]');
          if (tmVal) {
            if ($tindakanSel.find('option[value="' + tmVal + '"]').length === 0)
              $tindakanSel.append(
                '<option value="' + tmVal + '">' + tmVal + "</option>"
              );
            $tindakanSel.val(tmVal);
          } else {
            $tindakanSel.val("");
          }
          $('textarea[name="tindakan_lainnya"]').val(
            data.tindakan_lainnya || ""
          );

          if (data.edukasi) $("#ed_rawat").prop("checked", true);

          $('input[name="kesimpulan"]').prop("checked", false);
          if (data.kesimpulan) {
            const kesArr = data.kesimpulan.split(",");
            kesArr.forEach(function (k) {
              $('input[name="kesimpulan"][value="' + k.trim() + '"]').prop(
                "checked",
                true
              );
            });
          }

          currentTerapi = Array.isArray(data.terapi) ? data.terapi.slice() : [];
          renderTerapiTable();

          editMode = true;
          editId = data.id || null;

          if (data.diagnosa_utama) {
            $("#diagnosa_utama").val(data.diagnosa_utama);
          }

          $(
            "#pemeriksaan-form .is-invalid, #pemeriksaan-form .is-valid"
          ).removeClass("is-invalid is-valid");
          $(
            "#pemeriksaan-form .invalid-feedback, #pemeriksaan-form .valid-feedback"
          )
            .hide()
            .text("");
          $("#matrix-view").hide();
          $("#form-view").show();
        } else {
          $.ajax({
            url:
              "/flat-able-ver2/backend/web/index.php?r=data-jamaah/view&id=" +
              idJamaah,
            method: "GET",
            dataType: "json",
            success: function (resp) {
              if (resp.success) {
                const jamaah = resp.data;
                $("#id_jamaah").val(jamaah.id);
                $("#display-nama-lengkap").val(jamaah.nama_lengkap);
                $("#display-nama-profil").text(jamaah.nama_lengkap);
                $("#display-jenis-kelamin").val(jamaah.jenis_kelamin || "-");
                $("#display-umur").val(
                  jamaah.umur ? jamaah.umur + " Tahun" : "-"
                );
                $("#display-nomor-porsi").text(jamaah.nomor_porsi || "-");
                $("#display-nomor-paspor").text(jamaah.nomor_paspor || "-");
                const today = new Date().toISOString().split("T")[0];
                $("#tanggal_pemeriksaan").val(today);
                $("#lokasi").val("");
                $("#waktu_pemeriksaan").val("");
                $("#keluhan_utama").val("");
                $("#anamnesa").val("");
                editMode = false;
                editId = null;
                $(
                  "#pemeriksaan-form .is-invalid, #pemeriksaan-form .is-valid"
                ).removeClass("is-invalid is-valid");
                $(
                  "#pemeriksaan-form .invalid-feedback, #pemeriksaan-form .valid-feedback"
                )
                  .hide()
                  .text("");
                $("#matrix-view").hide();
                $("#form-view").show();
              } else {
                alert("Data jamaah tidak ditemukan");
              }
            },
            error: function () {
              alert("Gagal memuat data jamaah");
            },
          });
        }
      },
      error: function () {
        alert("Gagal memuat data pemeriksaan");
      },
    });
  };

  window.hapusPemeriksaan = function (id) {
    Swal.fire({
      icon: "question",
      title: "Yakin Hapus Data?",
      text: "Data yang dihapus tidak dapat dikembalikan!",
      showCancelButton: true,
      confirmButtonColor: "#d33",
      cancelButtonColor: "#3085d6",
      confirmButtonText: "Ya, Hapus!",
      cancelButtonText: "Batal",
    }).then((result) => {
      if (!result.isConfirmed) return;

      $.ajax({
        url:
          "/flat-able-ver2/backend/web/index.php?r=pemeriksaan/delete&id=" + id,
        method: "POST",
        dataType: "json",
        success: function (response) {
          if (response.success) {
            Swal.fire({
              icon: "success",
              title: "Berhasil!",
              text: response.message || "Data berhasil dihapus",
              showConfirmButton: false,
              timer: 1500,
            });
            loadPemeriksaan();
          } else {
            Swal.fire({
              icon: "error",
              title: "Gagal",
              text: response.message || "Gagal menghapus data",
            });
          }
        },
        error: function (xhr, status, error) {
          console.error("Kesalahan:", error);
          Swal.fire({
            icon: "error",
            title: "Error",
            text: "Terjadi kesalahan saat menghapus data",
          });
        },
      });
    });
  };

  function resetForm() {
    $("#pemeriksaan-form")[0].reset();
    $("#id_jamaah").val("");
    $("#display-nama-lengkap").val("-");
    $("#display-nama-profil").text("-");
    $("#display-jenis-kelamin").val("-");
    $("#display-umur").val("-");
    $("#display-nomor-porsi").text("-");
    $("#display-nomor-paspor").text("-");
    editMode = false;
    editId = null;
    $("#diagnosa_utama").val("");
    $('input[name="edukasi_opt[]"]').prop("checked", false);
    $('input[name="kesimpulan"]').prop("checked", false);
    $("#terapi-table-body").empty();
    $("#pemeriksaan-form .is-invalid, #pemeriksaan-form .is-valid").removeClass(
      "is-invalid is-valid"
    );
    $("#pemeriksaan-form .invalid-feedback, #pemeriksaan-form .valid-feedback")
      .hide()
      .text("");
    $("#pemeriksaan-form").removeClass("was-validated");
    $("#id_jamaah-feedback").hide();
    $("#display-nama-lengkap").removeClass(
      "border border-danger is-invalid is-valid"
    );
    $("#edukasi_opt-feedback").hide();
    $("#kesimpulan-feedback").hide();
    $("#tindakan-feedback").hide();
    $("#fisik-feedback").hide();
    $('textarea[name="thoraks"]').removeClass("is-invalid is-valid");
    $('textarea[name="abdomen"]').removeClass("is-invalid is-valid");
    $('textarea[name="neurologi"]').removeClass("is-invalid is-valid");
    $('textarea[name="fisik_lainnya"]').removeClass("is-invalid is-valid");

    currentTerapi = [];
    $("#terapi-table-body").empty();
  }
});
