"use strict";
document.addEventListener("DOMContentLoaded", function () {
  setTimeout(function () {
    floatchart();
  }, 500);
  if (!!document.querySelector(".product-scroll")) {
    new SimpleBar(document.querySelector(".product-scroll"));
  }
});

function floatchart() {
  // [ revenue-chart ] start
  (function () {
    var options = {
      chart: {
        height: 305,
        type: "bar",
        toolbar: {
          show: false,
        },
      },
      plotOptions: {
        bar: {
          horizontal: false,
          columnWidth: "50%",
        },
      },
      dataLabels: {
        enabled: false,
      },
      colors: ["#3498DB", "#1abc9c"],
      stroke: {
        show: true,
        width: 2,
        colors: ["transparent"],
      },
      series: [
        {
          name: "Rujuk",
          data:
            window.dataRujuk && window.dataRujuk.length
              ? window.dataRujuk
              : Array(30).fill(0),
        },
        {
          name: "Meninggal",
          data:
            window.dataMeninggal && window.dataMeninggal.length
              ? window.dataMeninggal
              : Array(30).fill(0),
        },
      ],
      xaxis: {
        categories:
          window.chartDays && window.chartDays.length
            ? window.chartDays
            : Array.from({ length: 30 }, function (_, i) {
                return "Dec " + (i + 1);
              }),
      },
      fill: { opacity: 1 },
      tooltip: {
        y: {
          formatter: function (val) {
            return val + " Jamaah";
          },
        },
      },
      legend: { position: "bottom" },
    };
    try {
      var _el =
        document.querySelector("#revenue-chart-des") ||
        document.querySelector("#revenue-chart");
      if (!_el) console.warn("#revenue-chart-des not found for initial chart");
      else {
        try {
          if (
            window.revenueChartDes &&
            typeof window.revenueChartDes.destroy === "function"
          )
            window.revenueChartDes.destroy();
        } catch (e) {}
        window.revenueChartDes = new ApexCharts(_el, options);
        window.revenueChartDes
          .render()
          .then(function () {
            console.log("initial revenueChartDes rendered");
          })
          .catch(function (e) {
            console.error("initial revenueChartDes render failed", e);
          });
      }
    } catch (e) {
      console.error("initial revenue chart creation error", e);
    }
  })();
  // [ revenue-chart ] end
  // [ customer-chart ] start
  (function () {
    var options = {
      chart: {
        height: 150,
        type: "donut",
      },
      dataLabels: {
        enabled: false,
      },
      plotOptions: {
        pie: {
          donut: {
            size: "75%",
          },
        },
      },
      labels: ["Memenuhi Syarat", "Tidak Memenuhi Syarat"],
      series: [memenuhi, tidakMemenuhi],
      legend: {
        show: false,
      },
      grid: {
        padding: {
          top: 20,
          right: 0,
          bottom: 0,
          left: 0,
        },
      },
      colors: ["#1abc9c", "#1abc9c"],
      fill: {
        opacity: [1, 0.3],
      },
      tooltip: {
        theme: "dark",
      },
      stroke: {
        width: 0,
      },
    };
    var chart = new ApexCharts(
      document.querySelector("#customer-chart"),
      options
    );
    chart.render();
    var options1 = {
      chart: {
        height: 150,
        type: "donut",
      },
      dataLabels: {
        enabled: false,
      },
      plotOptions: {
        pie: {
          donut: {
            size: "75%",
          },
        },
      },
      labels: ["Rujuk", "Meniggal"],
      series: [rujuk, meninggal],
      legend: {
        show: false,
      },
      grid: {
        padding: {
          top: 20,
          right: 0,
          bottom: 0,
          left: 0,
        },
      },
      colors: ["#fff", "#fff"],
      fill: {
        opacity: [1, 0.3],
      },
      tooltip: {
        fillSeriesColor: false,
        theme: "dark",
      },
      stroke: {
        width: 0,
      },
    };
    var chart = new ApexCharts(
      document.querySelector("#customer-chart1"),
      options1
    );
    chart.render();
  })();
  // [ customer-chart ] end
}

// Fetch historical daily series and apply to the revenue chart
function fetchAndApplyHistory() {
  console.log("fetchAndApplyHistory: fetching history");
  fetch(
    "/flat-able-ver2/backend/web/index.php?r=dashboard/get-history-pemeriksaan",
    { credentials: "same-origin", headers: { Accept: "application/json" } }
  )
    .then(function (r) {
      return r.json();
    })
    .then(function (h) {
      if (!(h && h.success && h.data)) {
        console.warn("fetchAndApplyHistory: no data in response", h);
        return;
      }
      try {
        var rawDays = Array.isArray(h.data.days) ? h.data.days : [];
        var days = Array.from({ length: 30 }, function (_, i) {
          return "Dec " + (i + 1);
        });
        if (rawDays.length === 30)
          days = rawDays.map(function (d) {
            return ("" + d).replace(/^Dec\s*/i, "Dec ").trim();
          });

        var rujukSeries = Array.isArray(h.data.rujuk)
          ? h.data.rujuk.slice(0, 30).map(function (v) {
              return Number(v) || 0;
            })
          : Array(30).fill(0);
        var meninggalSeries = Array.isArray(h.data.meninggal)
          ? h.data.meninggal.slice(0, 30).map(function (v) {
              return Number(v) || 0;
            })
          : Array(30).fill(0);
        while (rujukSeries.length < 30) rujukSeries.push(0);
        while (meninggalSeries.length < 30) meninggalSeries.push(0);

        window.chartDays = days.slice();
        window.dataRujuk = rujukSeries.slice();
        window.dataMeninggal = meninggalSeries.slice();

        if (
          window.revenueChartDes &&
          typeof window.revenueChartDes.updateOptions === "function"
        ) {
          window.revenueChartDes
            .updateOptions(
              { xaxis: { categories: days, tickAmount: days.length } },
              true,
              false,
              true
            )
            .then(function () {
              return window.revenueChartDes.updateSeries(
                [
                  { name: "Rujuk", data: rujukSeries },
                  { name: "Meninggal", data: meninggalSeries },
                ],
                true
              );
            })
            .then(function () {
              try {
                var maxVal = Math.max.apply(
                  null,
                  rujukSeries
                    .concat(meninggalSeries)
                    .map(function (v) {
                      return Number(v) || 0;
                    })
                    .concat([1])
                );
                window.revenueChartDes
                  .updateOptions({ yaxis: { max: maxVal } }, true, false, true)
                  .catch(function () {});
              } catch (e) {}
              console.log("fetchAndApplyHistory: applied history to chart");
            })
            .catch(function (e) {
              console.error("fetchAndApplyHistory: apply failed", e);
              if (typeof window.recreateRevenueChart === "function")
                try {
                  window.recreateRevenueChart();
                } catch (ex) {}
            });
        } else {
          setTimeout(fetchAndApplyHistory, 300);
        }
      } catch (e) {
        console.error("fetchAndApplyHistory: processing failed", e);
      }
    })
    .catch(function (err) {
      console.error("fetchAndApplyHistory failed", err);
    });
}

window.fetchAndApplyHistory = fetchAndApplyHistory;

setTimeout(function () {
  try {
    fetchAndApplyHistory();
  } catch (e) {}
}, 600);
