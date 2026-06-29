// Global variables
let editMode = false;
let editId = null;
// API root (uses server-provided base if available)
const API_ROOT = (typeof window.APP_BASE_URL !== 'undefined' && window.APP_BASE_URL)
  ? window.APP_BASE_URL.replace(/\/$/, '') + '/'
  : '/';

// Helper to build API URLs that work with pretty URLs or index.php?r= format
function buildApiUrl(action, params) {
  // if API_ROOT contains 'index.php' we assume query param format
  const usesIndexPhp = API_ROOT.indexOf('index.php') !== -1 || API_ROOT.indexOf('index.php?r') !== -1;
  if (usesIndexPhp) {
    return API_ROOT + action + (params ? '&' + params : '');
  } else {
    return API_ROOT + action + (params ? '?' + params : '');
  }
}

// Example: buildApiUrl('data-jamaah/get-list') -> '/data-jamaah/get-list'
// Example with params: buildApiUrl('data-jamaah/view', 'id=1') -> '/data-jamaah/view?id=1'
function neutralizeForm() {
  // reset DOM values
  const formEl = document.getElementById("hasil-form");
  if (formEl) formEl.reset();

  try {
    $("#provinsi").val("");
    $("#kabupaten").html('<option value="">Pilih Kabupaten</option>');
    $("#kecamatan").html('<option value="">Pilih Kecamatan</option>');
    $("#golongan_darah").val("");
  } catch (e) {}

  clearErrorMessages();

  $("#hasil-form").find('input[type="radio"]').prop("checked", false);
  $("#hasil-form").find("input, select, textarea").blur().trigger("change");

  editMode = false;
  editId = null;
  try {
    document.getElementById("umur").value = "";
    stopTanggalPolling();
  } catch (e) {}
}

$(document).ready(function () {
  loadJamaah();

  const btnAddHasil = document.getElementById("btn-add-hasil");
  if (btnAddHasil) {
    btnAddHasil.addEventListener("click", function (e) {
      try {
        e.preventDefault();
      } catch (ignore) {}
      try {
        console.debug("btn-add-hasil clicked");
      } catch (ignore) {}
      editMode = false;
      editId = null;
      neutralizeForm();
      document.getElementById("matrix-view").style.display = "none";
      document.getElementById("form-view").style.display = "block";
    });
  } else {
    $(document).on("click", "#btn-add-hasil", function (e) {
      try {
        e.preventDefault();
      } catch (ignore) {}
      try {
        console.debug("btn-add-hasil (delegated) clicked");
      } catch (ignore) {}
      editMode = false;
      editId = null;
      neutralizeForm();
      $("#matrix-view").hide();
      $("#form-view").show();
      startTanggalPolling();
    });
  }

  $("#btn-kembali").on("click", function (e) {
    try { e && e.preventDefault && e.preventDefault(); } catch (ignore) {}

    // If we're inside the list page with #matrix-view present, just switch views
    if ($("#matrix-view").length) {
      $("#form-view").hide();
      $("#matrix-view").show();
      neutralizeForm();
      return;
    }

    // Otherwise, we're on a standalone form page (e.g. GET /data-jamaah/create)
    // Navigate back to the listing page. Use APP_BASE_URL if provided, and support both
    // pretty URLs and index.php?r= format.
    try {
      var base = (typeof window.APP_BASE_URL !== 'undefined' && window.APP_BASE_URL)
        ? window.APP_BASE_URL.replace(/\/$/, '')
        : '';

      // If the app base already contains index.php, prefer index.php?r= format
      if (base.indexOf('index.php') !== -1) {
        window.location.href = base + (base.indexOf('?r=') !== -1 ? '&' : (base.indexOf('?') !== -1 ? '&' : '?')) + 'r=data-jamaah';
      } else {
        // Try pretty URL first
        window.location.href = base + '/data-jamaah';
      }
    } catch (err) {
      // fallback: reload to root list path
      window.location.href = '/data-jamaah';
    }
  });

  // Intercept standalone form submissions and handle via AJAX
  $(document).on('submit', '#hasil-form', function (e) {
    try { e && e.preventDefault && e.preventDefault(); } catch (ignore) {}
    simpanJamaah();
  });

  $("#tanggal_lahir").on("change input", calculateAge);
  $("#tanggal_lahir").on("changeDate dp.change", calculateAge);

  try {
    var tanggalEl = document.getElementById("tanggal_lahir");
    if (tanggalEl && window.MutationObserver) {
      var mo = new MutationObserver(function (muts) {
        muts.forEach(function (m) {
          if (m.type === "attributes" && m.attributeName === "value") {
            try {
              calculateAge();
            } catch (e) {
              /* ignore */
            }
          }
        });
      });
      mo.observe(tanggalEl, { attributes: true, attributeFilter: ["value"] });
      var mo2 = new MutationObserver(function (muts) {
        setTimeout(function () {
          try {
            calculateAge();
          } catch (e) {}
        }, 10);
      });
      mo2.observe(tanggalEl, { childList: true, subtree: true });

      var wrapper =
        tanggalEl.closest(".col-md-4, .form-group, .input-group") ||
        tanggalEl.parentElement;
      if (wrapper && wrapper !== tanggalEl) {
        var wrapperMo = new MutationObserver(function (muts) {
          setTimeout(function () {
            try {
              var wrapperText = wrapper.textContent || "";
              var detected = extractDateFromString(wrapperText);
              if (detected && detected !== (tanggalEl.value || "")) {
                try {
                  tanggalEl.value = detected;
                } catch (e) {}
                try {
                  calculateAge();
                } catch (e) {}
                try {
                  flashUmurFeedback();
                } catch (e) {}
              }
            } catch (e) {
              /* ignore */
            }
          }, 10);
        });
        wrapperMo.observe(wrapper, {
          childList: true,
          subtree: true,
          characterData: true,
          attributes: true,
        });
      }
    }
  } catch (err) {
  }
  // --- Wilayah ---
  const provinsiSelect = document.getElementById("provinsi");
  const kabupatenSelect = document.getElementById("kabupaten");
  const kecamatanSelect = document.getElementById("kecamatan");

  window.provinsiSelect = provinsiSelect;

  window.loadProvinsi = function () {
    if (!provinsiSelect) return;
    fetch("api/get-wilayah.php?action=provinsi")
      .then((r) => r.json())
      .then((data) => {
        if (data.success && data.data) {
          provinsiSelect.innerHTML = '<option value="">Pilih Provinsi</option>';
          data.data.forEach(function (p) {
            const opt = document.createElement("option");
            opt.value = p.prov_id;
            opt.textContent = p.prov_name;
            provinsiSelect.appendChild(opt);
          });
        }
      })
      .catch((err) => console.error("Error loading provinsi", err));
  };

  window.loadKabupaten = function (prov_id, cb) {
    if (!kabupatenSelect) return;
    kabupatenSelect.innerHTML = '<option value="">Pilih Kabupaten</option>';
    kecamatanSelect &&
      (kecamatanSelect.innerHTML = '<option value="">Pilih Kecamatan</option>');
    if (!prov_id) return;
    fetch(`api/get-wilayah.php?action=kabupaten&prov_id=${prov_id}`)
      .then((r) => r.json())
      .then((data) => {
        if (data.success && data.data) {
          data.data.forEach(function (k) {
            const opt = document.createElement("option");
            opt.value = k.kab_id;
            opt.textContent = k.kab_name;
            kabupatenSelect.appendChild(opt);
          });
        }
        if (typeof cb === "function") cb();
      })
      .catch((err) => console.error("Error loading kabupaten", err));
  };

  window.loadKecamatan = function (kab_id, cb) {
    if (!kecamatanSelect) return;
    kecamatanSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
    if (!kab_id) return;
    fetch(`api/get-wilayah.php?action=kecamatan&kab_id=${kab_id}`)
      .then((r) => r.json())
      .then((data) => {
        if (data.success && data.data) {
          data.data.forEach(function (kec) {
            const opt = document.createElement("option");
            opt.value = kec.kec_name;
            opt.textContent = kec.kec_name;
            kecamatanSelect.appendChild(opt);
          });
        }
        if (typeof cb === "function") cb();
      })
      .catch((err) => console.error("Error loading kecamatan", err));
  };

  if (provinsiSelect) {
    provinsiSelect.addEventListener("change", function () {
      loadKabupaten(this.value);
    });
  }
  if (kabupatenSelect) {
    kabupatenSelect.addEventListener("change", function () {
      loadKecamatan(this.value);
    });
  }

  //  provinsi
  loadProvinsi();

  $(document).on("click", ".btn-edit-jamaah", function (e) {
    e.preventDefault();
    var id = $(this).data("id");
    if (typeof id === "undefined") id = $(this).attr("data-id");
    console.debug("btn-edit-jamaah clicked, id=", id);
    if (!id && id !== 0) {
      console.warn("No id found on clicked edit button", this);
    }
    editJamaah(id);
  });
  $(document).on("click", ".btn-delete-jamaah", function (e) {
    e.preventDefault();
    var id = $(this).data("id");
    if (typeof id === "undefined") id = $(this).attr("data-id");
    console.debug("btn-delete-jamaah clicked, id=", id);
    if (!id && id !== 0) {
      console.warn("No id found on clicked delete button", this);
    }
    hapusJamaah(id);
  });
});

function calculateAge() {
  var tanggalLahir = $("#tanggal_lahir").val();

  if (!tanggalLahir) {
    $("#umur").val("");
    return;
  }

  function parseDateString(s) {
    if (!s) return null;
    s = String(s).trim();
    var iso = /^\d{4}-\d{2}-\d{2}$/;
    var dmy = /^\d{2}\/\d{2}\/\d{4}$/; 
    if (iso.test(s)) {
      var parts = s.split("-").map(Number);
      return new Date(parts[0], parts[1] - 1, parts[2]);
    }
    if (dmy.test(s)) {
      var parts = s.split("/").map(Number);
      var day = parts[0],
        month = parts[1],
        year = parts[2];
      var dt = new Date(year, month - 1, day);

      if (!isNaN(dt.getTime())) {
        var __tanggalPollId = null;
        var __lastTanggalVal = null;
        function startTanggalPolling() {
          try {
            stopTanggalPolling();
          } catch (e) {}
          var el = document.getElementById("tanggal_lahir");
          if (!el) return;
          __lastTanggalVal = el.value;
          tryDetectTanggal();
          __tanggalPollId = setInterval(function () {
            try {
              var v = el.value;
              if (v !== __lastTanggalVal) {
                __lastTanggalVal = v;
                calculateAge();
              } else {
                var wrapper =
                  el.closest(".col-md-4, .form-group, .input-group") ||
                  el.parentElement;
                var wrapperText = wrapper ? wrapper.textContent : "";
                var detected = extractDateFromString(wrapperText);
                if (detected && detected !== __lastTanggalVal) {
                  try {
                    el.value = detected;
                  } catch (e) {}
                  __lastTanggalVal = detected;
                  calculateAge();
                  try {
                    flashUmurFeedback();
                  } catch (e) {}
                }
              }
              var fv = document.getElementById("form-view");
              if (!fv || fv.style.display === "none") {
                stopTanggalPolling();
              }
            } catch (e) {
              /* ignore */
            }
          }, 300);
        }

        function tryDetectTanggal() {
          try {
            var el = document.getElementById("tanggal_lahir");
            if (!el) return false;

            var v = (el.value || "").trim();
            if (v) {
              try {
                calculateAge();
                console.debug("tryDetectTanggal: used input.value", v);
              } catch (e) {}
              return true;
            }

            var attr = (el.getAttribute && el.getAttribute("value")) || "";
            if (attr) {
              try {
                el.value = attr;
                calculateAge();
                console.debug("tryDetectTanggal: used attr value", attr);
              } catch (e) {}
              return true;
            }

            var wrapper =
              el.closest(".col-md-4, .form-group, .input-group") ||
              el.parentElement;
            if (wrapper) {
              var hidden = wrapper.querySelector('input[type="hidden"]');
              if (hidden && hidden.value) {
                try {
                  el.value = hidden.value;
                  calculateAge();
                  console.debug(
                    "tryDetectTanggal: used hidden input",
                    hidden.value
                  );
                } catch (e) {}
                return true;
              }

              var children = wrapper.querySelectorAll("*");
              for (var i = 0; i < children.length; i++) {
                var ch = children[i];
                var dv =
                  ch.getAttribute &&
                  (ch.getAttribute("data-date") ||
                    ch.getAttribute("data-value") ||
                    ch.getAttribute("data-original"));
                if (dv && dv.match(/\d/)) {
                  var extracted = extractDateFromString(dv) || dv;
                  if (extracted) {
                    try {
                      el.value = extracted;
                      calculateAge();
                      console.debug(
                        "tryDetectTanggal: used child data attr",
                        dv
                      );
                    } catch (e) {}
                    return true;
                  }
                }

                var txt = (ch.textContent || "").trim();
                var found = extractDateFromString(txt);
                if (found) {
                  try {
                    el.value = found;
                    calculateAge();
                    console.debug("tryDetectTanggal: used child text", txt);
                  } catch (e) {}
                  return true;
                }
              }
            }
          } catch (e) {
            console.debug("tryDetectTanggal error", e);
          }
          return false;
        }

        function stopTanggalPolling() {
          if (__tanggalPollId) {
            clearInterval(__tanggalPollId);
            __tanggalPollId = null;
          }
        }
        var now = new Date();
        var ageTry = now.getFullYear() - dt.getFullYear();
        if (ageTry >= 0 && ageTry <= 120) return dt;
      }
      var monthAlt = parts[0],
        dayAlt = parts[1];
      var dtAlt = new Date(year, monthAlt - 1, dayAlt);
      if (!isNaN(dtAlt.getTime())) return dtAlt;
      return null;
    }
    var dt = new Date(s);
    if (!isNaN(dt.getTime())) return dt;
    return null;
  }

  var birthDate = parseDateString(tanggalLahir);
  if (!birthDate) {
    try {
      console.debug(
        "calculateAge: unable to parse tanggal_lahir:",
        tanggalLahir
      );
    } catch (e) {}
    $("#umur").val("");
    return;
  }

  var today = new Date();
  var age = today.getFullYear() - birthDate.getFullYear();
  var monthDiff = today.getMonth() - birthDate.getMonth();

  if (
    monthDiff < 0 ||
    (monthDiff === 0 && today.getDate() < birthDate.getDate())
  ) {
    age--;
  }

  if (age < 0 || isNaN(age)) {
    $("#umur").val("");
  } else {
    $("#umur").val(age);
    try {
      console.debug(
        "calculateAge: parsed",
        tanggalLahir,
        "->",
        birthDate.toISOString().slice(0, 10),
        "age",
        age
      );
    } catch (e) {}
  }
}

function extractDateFromString(s) {
  if (!s) return null;
  s = String(s).trim();
  var isoMatch = s.match(/(\d{4})-(\d{1,2})-(\d{1,2})/);
  if (isoMatch) {
    var y = isoMatch[1],
      m = String(isoMatch[2]).padStart(2, "0"),
      d = String(isoMatch[3]).padStart(2, "0");
    return y + "-" + m + "-" + d;
  }
  var dmyMatch = s.match(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/);
  if (dmyMatch) {
    var day = String(dmyMatch[1]).padStart(2, "0");
    var month = String(dmyMatch[2]).padStart(2, "0");
    var year = dmyMatch[3];
    return year + "-" + month + "-" + day;
  }
  return null;
}

function flashUmurFeedback() {
  try {
    var umurEl = document.getElementById("umur");
    if (!umurEl) return;
    var fb = document.getElementById("umur-feedback");
    if (!fb) {
      fb = document.createElement("span");
      fb.id = "umur-feedback";
      fb.style.cssText =
        "margin-left:8px;color:#198754;font-weight:600;opacity:0;transition:opacity .25s";
      fb.textContent = "Terhitung";
      umurEl.parentNode.insertBefore(fb, umurEl.nextSibling);
    }
    fb.style.opacity = "1";
    setTimeout(function () {
      fb.style.opacity = "0";
    }, 900);
  } catch (e) {
    /* ignore */
  }
}

/**
 * data jamaah
 */
function loadJamaah() {
  if (
    $.fn &&
    $.fn.DataTable &&
    typeof $.fn.DataTable.isDataTable === "function" &&
    $.fn.DataTable.isDataTable("#table-jamaah")
  ) {
    try {
      $("#table-jamaah").DataTable().clear().destroy();
    } catch (e) {
      try {
        $("#table-jamaah").DataTable().destroy();
      } catch (err) {
        /* ignore */
      }
    }
    $("#table-jamaah tbody").empty();
  }

  $.ajax({
    url: buildApiUrl('data-jamaah/get-list'),
    method: "GET",
    dataType: "json",
    success: function (response) {
      // remove any previous fallback notice
      try { $("#jamaah-fallback").remove(); } catch (e) {}

      // show fallback notice when server returns fallback sample data
      if (response && response.fallback) {
        try {
          $("#matrix-view .card-body .table-responsive").before('<div id="jamaah-fallback" class="alert alert-warning">Menampilkan data contoh karena koneksi database sedang bermasalah.</div>');
        } catch (e) {}
      }

      let html = "";
      if (response.data && response.data.length > 0) {
        response.data.forEach(function (item, index) {
          html += `
            <tr>
              <td>${index + 1}</td>
              <td>${item.nama_lengkap}</td>
              <td>${item.nomor_porsi || "-"}</td>
              <td>${item.nomor_paspor || "-"}</td>
              <td>${item.jenis_kelamin || "-"}</td>
              <td>${item.umur || "-"}</td>
              <td>
                <button class="btn btn-sm btn-warning btn-edit-jamaah" data-id="${
                  item.id
                }" title="Edit">
                  <i class="ti ti-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger btn-delete-jamaah" data-id="${
                  item.id
                }" title="Hapus">
                  <i class="ti ti-trash"></i>
                </button>
              </td>
            </tr>
          `;
        });
      } else {
        html =
          '<tr><td colspan="7" class="text-center">Tidak ada data</td></tr>';
      }
      $("#tbody-jamaah").html(html);

      $("#tbody-jamaah")
        .find('button[onclick^="editJamaah("]')
        .each(function () {
          try {
            var onclick = $(this).attr("onclick") || "";
            var m = onclick.match(/editJamaah\(([^)]+)\)/);
            if (m && m[1]) {
              var id = m[1].trim().replace(/^['\"]|['\"]$/g, "");
              $(this)
                .attr("data-id", id)
                .addClass("btn-edit-jamaah")
                .removeAttr("onclick");
            }
          } catch (e) {}
        });

      $("#tbody-jamaah")
        .find('button[onclick^="hapusJamaah("]')
        .each(function () {
          try {
            var onclick = $(this).attr("onclick") || "";
            var m = onclick.match(/hapusJamaah\(([^)]+)\)/);
            if (m && m[1]) {
              var id = m[1].trim().replace(/^['\"]|['\"]$/g, "");
              $(this)
                .attr("data-id", id)
                .addClass("btn-delete-jamaah")
                .removeAttr("onclick");
            }
          } catch (e) {}
        });

      $("#tbody-jamaah")
        .find(".btn-edit-jamaah")
        .off("click")
        .on("click", function (e) {
          e.preventDefault();
          var id = $(this).data("id") || $(this).attr("data-id");
          console.debug("direct btn-edit-jamaah clicked, id=", id);
          editJamaah(id);
        });
      $("#tbody-jamaah")
        .find(".btn-delete-jamaah")
        .off("click")
        .on("click", function (e) {
          e.preventDefault();
          var id = $(this).data("id") || $(this).attr("data-id");
          console.debug("direct btn-delete-jamaah clicked, id=", id);
          hapusJamaah(id);
        });

      $("#table-jamaah").DataTable({
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
          info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ jamaah",
          infoEmpty: "Menampilkan 0 sampai 0 dari 0 jamaah",
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
    },
    error: function (xhr, textStatus, errorThrown) {
      console.error("data-jamaah/get-list failed:", {
        url: buildApiUrl('data-jamaah/get-list'),
        status: xhr.status,
        textStatus: textStatus,
        errorThrown: errorThrown,
        responseText: xhr.responseText ? xhr.responseText.substring(0, 2000) : ''
      });

      // If server returned HTML (login/error page) keep any server-side fallback rows,
      // and show a warning banner instead of overwriting the rows.
      var respText = xhr && xhr.responseText ? xhr.responseText.trim() : '';
      var isHtml = respText && respText.charAt(0) === '<';
      if (isHtml) {
        if ($('#jamaah-fallback').length === 0) {
          $('#matrix-view .card-body .table-responsive').before('<div id="jamaah-fallback" class="alert alert-warning">Menampilkan data contoh karena server mengembalikan halaman HTML. Periksa koneksi DB atau sesi.</div>');
        }
        // Only replace body if it's still the initial spinner/empty state
        var $tbody = $('#tbody-jamaah');
        var hasRows = $tbody.find('tr').length > 0 && !$tbody.find('tr td .spinner-border').length;
        if (!hasRows) {
          $tbody.html('<tr><td colspan="7" class="text-center text-danger">Gagal memuat data (server returned HTML)</td></tr>');
        }
        return;
      }

      // Generic non-HTML error: replace body with error row
      $("#tbody-jamaah").html(
        '<tr><td colspan="7" class="text-center text-danger">Gagal memuat data (lihat console untuk detail)</td></tr>'
      );
    },
  });
}

function showFallbackBanner(msg) {
  try {
    msg = msg || 'Menggunakan data fallback karena kesalahan server.';
    var $existing = $('#data-jamaah-fallback-banner');
    if ($existing.length) {
      $existing.text(msg).show();
      return;
    }
    var $container = $('#table-jamaah').closest('.card, .panel, .container, .table-responsive').first();
    if (!$container.length) $container = $('#table-jamaah').parent();
    var banner = '<div id="data-jamaah-fallback-banner" class="alert alert-warning" role="alert" style="margin-bottom:1rem;">' + msg + '</div>';
    $container.before(banner);
  } catch (e) {
    console.error('showFallbackBanner error', e);
  }
}

/**
 * Edit jamaah
 */
function editJamaah(id) {
  console.debug("editJamaah called with id:", id);
  editMode = true;
  editId = id;

  $.ajax({
    url: buildApiUrl('data-jamaah/view', 'id=' + id),
    method: "GET",
    dataType: "json",
    success: function (response) {
      if (response.success) {
        var data = response.data;
        try {
          window.isLoadingForm = true;
        } catch (e) {
          /* ignore */
        }

        $("#id_jemaah").val(data.id_jamaah);
        $("#nomor_porsi").val(data.nomor_porsi);
        $("#nomor_paspor").val(data.nomor_paspor);
        $("#nama_lengkap").val(data.nama_lengkap);

        if (data.jenis_kelamin === "Laki-laki") {
          $("#jk_l").prop("checked", true);
        } else if (data.jenis_kelamin === "Perempuan") {
          $("#jk_p").prop("checked", true);
        }

        $("#tanggal_lahir").val(data.tanggal_lahir);
        try {
          calculateAge();
        } catch (e) {
          $("#umur").val(data.umur || "");
        }

        //  MASTER DATA JAMAAH
        $("#golongan_darah").val(data.golongan_darah);
        $("#tempat_lahir").val(data.tempat_lahir);
        $("#tanggal_pemeriksaan").val(data.tanggal_pemeriksaan);
        $("#total_pemeriksaan").val(data.total_pemeriksaan);
        $("#keluhan_terakhir").val(data.keluhan_terakhir);

        if (data.status_perkawinan) {
          $(
            'input[name="status_perkawinan"][value="' +
              data.status_perkawinan +
              '"]'
          ).prop("checked", true);
        }

        $("#alamat_lengkap").val(data.alamat_lengkap);
        try {
          window.isLoadingForm = true;
        } catch (e) {}
        if (window.provinsiSelect) {
          if (data.provinsi_id) {
            $("#provinsi").val(data.provinsi_id);
            window.loadKabupaten &&
              window.loadKabupaten(data.provinsi_id, function () {
                if (data.kabupaten_id) {
                  $("#kabupaten").val(data.kabupaten_id);
                  window.loadKecamatan &&
                    window.loadKecamatan(data.kabupaten_id, function () {
                      if (data.kecamatan_kelurahan) {
                        $("#kecamatan").val(data.kecamatan_kelurahan);
                      }
                    });
                }
              });
          } else {
            $("#provinsi").val("");
            $("#kabupaten").html('<option value="">Pilih Kabupaten</option>');
            $("#kecamatan").html('<option value="">Pilih Kecamatan</option>');
          }
        }
        $("#nomor_hp").val(data.nomor_hp);

        // INFORMASI KLOTER & EMBARKASI
        $("#kode_kloter").val(data.kode_kloter);
        $("#nomor_kloter").val(data.nomor_kloter);
        $("#embarkasi_id").val(data.embarkasi_id);
        $("#embarkasi_asal").val(data.embarkasi_asal);

        if (data.embarkasi_tujuan) {
          $(
            'input[name="embarkasi_tujuan"][value="' +
              data.embarkasi_tujuan +
              '"]'
          ).prop("checked", true);
        }

        if (data.gelombang_keberangkatan) {
          $(
            'input[name="gelombang_keberangkatan"][value="' +
              data.gelombang_keberangkatan +
              '"]'
          ).prop("checked", true);
        }

        $("#tanggal_keberangkatan").val(data.tanggal_keberangkatan);
        $("#tanggal_pemulangan").val(data.tanggal_pemulangan);
        $("#hotel_madinah").val(data.hotel_madinah);
        $("#hotel_makkah").val(data.hotel_makkah);
        $("#sektor_daker_maktab").val(data.sektor_daker_maktab);

        $("#matrix-view").hide();
        $("#form-view").show();

        setTimeout(function () {
          try {
            window.isLoadingForm = false;
          } catch (e) {}
        }, 50);
      } else {
        Swal.fire({
          icon: "error",
          title: "Error!",
          text: "Gagal memuat data: " + response.message,
        });
      }
    },
    error: function () {
      Swal.fire({
        icon: "error",
        title: "Error!",
        text: "Gagal memuat data",
      });
    },
  });
}

function simpanJamaah() {
  clearErrorMessages();

  let jenisKelamin = $('input[name="jenis_kelamin"]:checked').val();
  let formData = {
    id_jamaah: $("#id_jemaah").val(),
    nomor_porsi: $("#nomor_porsi").val(),
    nomor_paspor: $("#nomor_paspor").val(),
    nama_lengkap: $("#nama_lengkap").val(),
    jenis_kelamin: jenisKelamin,
    tanggal_lahir: $("#tanggal_lahir").val(),
    umur: $("#umur").val(),
    golongan_darah: $("#golongan_darah").val(),
    tempat_lahir: $("#tempat_lahir").val(),
    tanggal_pemeriksaan: $("#tanggal_pemeriksaan").val(),
    total_pemeriksaan: $("#total_pemeriksaan").val(),
    keluhan_terakhir: $("#keluhan_terakhir").val(),
    status_perkawinan: $('input[name="status_perkawinan"]:checked').val(),
    alamat_lengkap: $("#alamat_lengkap").val(),
    provinsi_id: $("#provinsi").val(),
    kabupaten_id: $("#kabupaten").val(),
    kecamatan_kelurahan: $("#kecamatan").val(),
    nomor_hp: $("#nomor_hp").val(),
    kode_kloter: $("#kode_kloter").val(),
    nomor_kloter: $("#nomor_kloter").val(),
    embarkasi_id: $("#embarkasi_id").val(),
    embarkasi_asal: $("#embarkasi_asal").val(),
    embarkasi_tujuan: $('input[name="embarkasi_tujuan"]:checked').val(),
    gelombang_keberangkatan: $('input[name="gelombang_keberangkatan"]:checked').val(),
    tanggal_keberangkatan: $("#tanggal_keberangkatan").val(),
    tanggal_pemulangan: $("#tanggal_pemulangan").val(),
    hotel_madinah: $("#hotel_madinah").val(),
    hotel_makkah: $("#hotel_makkah").val(),
    sektor_daker_maktab: $("#sektor_daker_maktab").val(),
  };

  // URL 
  let url = editMode
    ? buildApiUrl('data-jamaah/update', 'id=' + editId)
    : buildApiUrl('data-jamaah/create');
  // Helper to perform actual AJAX submit
  function doSubmit(postUrl) {
    $.ajax({
      url: postUrl,
      method: "POST",
      data: formData,
      dataType: "json",
      success: function (response) {
        if (response.success) {
          Swal.fire({
            icon: "success",
            title: "Berhasil!",
            text: response.message,
            showConfirmButton: false,
            timer: 2000,
          });

          setTimeout(function () {
            // If the list container exists on the page (SPA flow), show it and reload via AJAX
            if (jQuery("#matrix-view").length) {
              $("#form-view").hide();
              $("#matrix-view").show();
              resetForm();
              loadJamaah();
              return;
            }

            // Standalone form page: navigate back to the index view
            try {
              var base = (typeof window.APP_BASE_URL !== 'undefined' && window.APP_BASE_URL)
                ? window.APP_BASE_URL.replace(/\/$/, '')
                : '';

              if (base.indexOf('index.php') !== -1) {
                // base already contains index.php - use query param style
                var sep = base.indexOf('?') !== -1 ? '&' : '?';
                window.location.href = base + sep + 'r=data-jamaah';
              } else {
                // pretty URL
                window.location.href = base + '/data-jamaah';
              }
            } catch (err) {
              // fallback
              window.location.href = '/data-jamaah';
            }
          }, 500);
        } else {
          if (response.errors) {
            displayFieldErrors(response.errors);
          }
          if (response.errors && Object.keys(response.errors).length > 0) {
            const $firstInvalid = $("#hasil-form").find(".is-invalid").first();
            if ($firstInvalid.length) {
              $firstInvalid[0].scrollIntoView({
                behavior: "smooth",
                block: "center",
              });
              $firstInvalid.focus();
            }
          } else {
            var msg = response.message || "Gagal menyimpan data";
            if (response.exception) {
              msg += "\n\nException: " + response.exception;
            }
            Swal.fire({
              icon: "error",
              title: "Error!",
              text: msg,
            });
            console.error("Save failed response:", response);
          }
        }
      },
      error: function () {
        Swal.fire({
          icon: "error",
          title: "Error!",
          text: "Gagal menyimpan data. Periksa log server atau network tab untuk detail.",
        });
      },
    });
  }

  if (!editMode && formData.id_jamaah && formData.id_jamaah.trim() !== "") {
    $.ajax({
      url: buildApiUrl('data-jamaah/check-id'),
      method: "GET",
      data: { id_jamaah: formData.id_jamaah },
      dataType: "json",
      success: function (resp) {
        if (resp && resp.exists) {
          Swal.fire({
            icon: "question",
            title: "ID sudah terdaftar",
            text:
              'ID Jamaah "' +
              formData.id_jamaah +
              '" sudah terdaftar. Apakah Anda ingin mengedit data yang ada?',
            showCancelButton: true,
            confirmButtonText: "Ya, edit",
            cancelButtonText: "Tidak",
            confirmButtonColor: "#26c281",
            cancelButtonColor: "#d33",
          }).then((result) => {
            if (result.isConfirmed) {
              var targetId = resp.data && resp.data.id ? resp.data.id : null;
              if (targetId) {
                doSubmit(buildApiUrl('data-jamaah/update', 'id=' + targetId));
              } else {
                editMode = true;
                editId = targetId;
                doSubmit(buildApiUrl('data-jamaah/update', 'id=' + (editId || '')));
              }
            } else {
            }
          });
        } else {
          doSubmit(url);
        }
      },
      error: function () {
        doSubmit(url);
      },
    });
  } else {
    doSubmit(url);
  }
}

function hapusJamaah(id) {
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
    if (result.isConfirmed) {
      $.ajax({
        url: buildApiUrl('data-jamaah/delete', 'id=' + id),
        method: "POST",
        dataType: "json",
        success: function (response) {
          if (response.success) {
            Swal.fire({
              icon: "success",
              title: "Berhasil!",
              text: "Data berhasil dihapus",
              showConfirmButton: false,
              timer: 2000,
            });
            loadJamaah();
          } else {
            Swal.fire({
              icon: "error",
              title: "Error!",
              text: "Gagal menghapus data",
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
    }
  });
}

function resetForm() {
  $("#hasil-form")[0].reset();
  editMode = false;
  editId = null;
  clearErrorMessages();
}

function clearErrorMessages() {
  const $controls = $("#hasil-form").find("input, select, textarea");
  $controls.removeClass("is-invalid is-valid");

  $controls.each(function () {
    try {
      delete this.dataset.touched;
    } catch (e) {
      $(this).removeAttr("data-touched");
    }
  });

  $("#hasil-form").find(".invalid-feedback, .valid-feedback").remove();
}

function displayFieldErrors(errors) {
  const fieldMap = {
    id_jamaah: "id_jemaah",
    nomor_porsi: "nomor_porsi",
    nomor_paspor: "nomor_paspor",
    nama_lengkap: "nama_lengkap",
    jenis_kelamin: "jenis_kelamin", 
    tanggal_lahir: "tanggal_lahir",
    umur: "umur",
    golongan_darah: "golongan_darah",
    tempat_lahir: "tempat_lahir",
    tanggal_pemeriksaan: "tanggal_pemeriksaan",
    total_pemeriksaan: "total_pemeriksaan",
    keluhan_terakhir: "keluhan_terakhir",
    status_perkawinan: "status_perkawinan", 
    alamat_lengkap: "alamat_lengkap",
    provinsi_id: "provinsi",
    kabupaten_id: "kabupaten",
    kecamatan_kelurahan: "kecamatan",
    nomor_hp: "nomor_hp",
    kode_kloter: "kode_kloter",
    nomor_kloter: "nomor_kloter",
    embarkasi_id: "embarkasi_id",
    embarkasi_asal: "embarkasi_asal",
    embarkasi_tujuan: "embarkasi_tujuan", 
    gelombang_keberangkatan: "gelombang_keberangkatan",
    tanggal_keberangkatan: "tanggal_keberangkatan",
    tanggal_pemulangan: "tanggal_pemulangan",
    hotel_madinah: "hotel_madinah",
    hotel_makkah: "hotel_makkah",
    sektor_daker_maktab: "sektor_daker_maktab",
  };

  for (var fieldName in errors) {
    if (!errors.hasOwnProperty(fieldName)) continue;

    var mapped = fieldMap[fieldName] || fieldName;
    var $byId = $("#" + mapped);
    var $byName = $('[name="' + mapped + '"]');

    var errorMsg = Array.isArray(errors[fieldName])
      ? errors[fieldName].join(", ")
      : errors[fieldName];
    try {
      errorMsg = errorMsg.replace(
        /has already been taken/gi,
        "sudah terdaftar"
      );
      errorMsg = errorMsg.replace(
        /cannot be blank|can't be blank/gi,
        "wajib diisi"
      );
    } catch (e) {
      /* ignore */
    }

    // If element found by id
    if ($byId.length) {
      $byId.addClass("is-invalid");

      if (!$byId.next(".invalid-feedback").length) {
        $(
          '<div class="invalid-feedback" style="display: block; color: #dc3545; font-size: 0.875em; margin-top: 0.25rem;"></div>'
        )
          .text(errorMsg)
          .insertAfter($byId);
      }
      continue;
    }

    if ($byName.length) {
      $byName.each(function () {
        $(this).addClass("is-invalid");
      });

      var $container = $byName
        .closest(".col-md-4, .col-md-6, .col-md-8, .form-group, .mb-3, .row")
        .first();
      if (!$container.length) $container = $byName.parent();

      if (!$container.find(".invalid-feedback").length) {
        $(
          '<div class="invalid-feedback" style="display: block; color: #dc3545; font-size: 0.875em; margin-top: 0.25rem;"></div>'
        )
          .text(errorMsg)
          .appendTo($container);
      }
      continue;
    }

    var $fallback = $("#" + fieldName);
    if ($fallback.length) {
      $fallback.addClass("is-invalid");
      if (!$fallback.next(".invalid-feedback").length) {
        $(
          '<div class="invalid-feedback" style="display: block; color: #dc3545; font-size: 0.875em; margin-top: 0.25rem;"></div>'
        )
          .text(errorMsg)
          .insertAfter($fallback);
      }
    }
  }
}
