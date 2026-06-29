var var_kajian = kajian.toLowerCase().replace(/-/g,'_') || "";
var var_bencana = bencana.toLowerCase().replace(/-/g,'_') || "";
alert(var_kajian);
var height = $(window).height();
$("#mapcontainer").attr("style", "height: "+height);
// var mapcenter = [parseFloat(mapscalecenter[maplevel]["centerx"]/10000), parseFloat(mapscalecenter[maplevel]["centery"]/10000)];
// mapcenter = (maplevel == "provinsi") ? [133.01028, -2.52392] : mapcenter;
// var mapscale = mapscalecenter[maplevel]["scale"]+3;
// mapscale = (maplevel == "provinsi") ? 5 : mapscale;
// mapscale = $(window).width() <= 480 ? 3 : mapscale;

// if (maplevel != "provinsi") {
  var xmin = mapscalecenter[maplevel]["xmin"], ymin = mapscalecenter[maplevel]["ymin"], 
  xmax = mapscalecenter[maplevel]["xmax"], ymax = mapscalecenter[maplevel]["ymax"];

  if (kabupaten != "kabupaten") {
    xmin = mapscalecenter[kabupaten]["xmin"];
    ymin = mapscalecenter[kabupaten]["ymin"];
    xmax = mapscalecenter[kabupaten]["xmax"];
    ymax = mapscalecenter[kabupaten]["ymax"];    
  }

  var bound = [[parseFloat(xmin/10000), parseFloat(ymin/10000)], [parseFloat(xmax/10000), parseFloat(ymax/10000)]];
// }

// $("#mapcontainer").height(height*0.65);
$("#mapcontainer").height(height);
var baseLayerMap = 'http://inarisk.bnpb.go.id:6080/arcgis/rest/services/basemap/hilshade/ImageServer';
var adminLayerMap = 'http://inarisk.bnpb.go.id:6080/arcgis/rest/services/basemap/batas_administrasi/MapServer';
var DASLayerMap = 'http://inarisk.bnpb.go.id:6080/arcgis/rest/services/basemap/DAS/MapServer';
var SungaiLayerMap = 'http://inarisk.bnpb.go.id:6080/arcgis/rest/services/basemap/Sungai_250K/MapServer';

if (var_bencana == 'gelombang_ekstrim_abrasi') var_bencana = 'gelombang_ekstrim_dan_abrasi';
if (var_bencana == 'kebakaran_hutan_lahan') var_bencana = 'kebakaran_hutan_dan_lahan';
if (var_bencana == 'gempa_bumi') var_bencana = 'gempabumi';
if (var_bencana == 'letusan_gunung_api') var_bencana = 'letusan_gunungapi';
if (var_bencana == 'multi_bahaya') var_bencana = 'multi';

var url = 'http://inarisk.bnpb.go.id:6080/arcgis/rest/services/inaRISK/layer_' + var_kajian + '_' + var_bencana + '/ImageServer';
if (kajian == "" || bencana == "") url = baseLayerMap;
if (kajian == "kapasitas") url = 'http://inarisk.bnpb.go.id:6080/arcgis/rest/services/inaRISK/layer_peta_kapasitas/ImageServer';

var cookieLang = lang, imageLegenda = '/peta/legenda_Indeks_'+kajian;
imageLegenda += (cookieLang == false) ? '_en.jpg' : (cookieLang == "eng") ? '_en.jpg' : '.jpg';
if (kajian != ""){
  $("#maplegend").html('<img src="'+imageLegenda+'" style="position:absolute;width:200px;right:30px;bottom:50px;"/>');
}

var pathname = window.location.pathname.toString().split("&")[0];

var baseLayer = new ol.source.ImageArcGISRest({
  ratio: 1,
  params: {},
  url: baseLayerMap,
  projection: 'EPSG:3857'
});

var secondLayer = new ol.source.ImageArcGISRest({
  ratio: 1,
  params: {},
  url: url,
  projection: 'EPSG:3857',
});

var adminLayer = new ol.source.TileArcGISRest({
  ratio: 1,
  params: {},
  url: adminLayerMap,
  projection: 'EPSG:3857'
});

var DASLayer = new ol.source.TileArcGISRest({
  ratio: 1,
  params: {},
  url: DASLayerMap,
  projection: 'EPSG:3857'
});

var SungaiLayer = new ol.source.TileArcGISRest({
  ratio: 1,
  params: {},
  url: SungaiLayerMap,
  projection: 'EPSG:3857'
});

var gmap = new google.maps.Map(document.getElementById('gmap'), {
  disableDefaultUI: true,
  keyboardShortcuts: false,
  draggable: false,
  disableDoubleClickZoom: true,
  scrollwheel: false,
  streetViewControl: false,
  zoomControl: false,
});
var view = new ol.View({
  // make sure the view doesn't go beyond the 22 zoom levels of Google Maps
  maxZoom: 21
});
view.on('change:center', function() {
  var center = ol.proj.transform(view.getCenter(), 'EPSG:3857', 'EPSG:4326');
  gmap.setCenter(new google.maps.LatLng(center[1], center[0]));
});
view.on('change:resolution', function() {
  gmap.setZoom(view.getZoom());
});
var layersDefault = [
              new ol.layer.Tile({ source: SungaiLayer, name: 'Sungai 250K', visible: false }),
              new ol.layer.Tile({ source: DASLayer, name: 'Batas DAS', visible: false }),
              new ol.layer.Tile({ source: adminLayer, name: 'Batas Admin', visible: false })
              ]
var layers = [new ol.layer.Image({ source: baseLayer, name: 'Hilshade' })]
if (kajian == "" || bencana == "") {
  layers = layers.concat(layersDefault)
} else {
  var selectedLayers = new ol.layer.Image({ source: secondLayer, name: titleCase(var_kajian.replace(/_/g,' '))+' '+titleCase(var_bencana.replace(/_/g,' ')) })
  selectedLayers.setOpacity(0.4)
  layers.push(selectedLayers)
  layers = layers.concat(layersDefault)
}
// var layers = [new ol.layer.Image({ source: baseLayer, name: 'Hilshade' }), , new ol.layer.Tile({ source: adminLayer, name: 'Batas Admin', visible: false })];
// layers = (kajian == "" || bencana == "") ? [new ol.layer.Image({ source: baseLayer, name: 'Hilshade' }), new ol.layer.Tile({ source: adminLayer, name: 'Batas Admin', visible: false })] : layers;
var map = new ol.Map({
  layers: layers,
  target: 'olmap',
  interactions: ol.interaction.defaults({
    altShiftDragRotate: false,
    dragPan: false,
    rotate: false,
  }).extend([new ol.interaction.DragPan({kinetic: null})]),
  view: view,
  controls: []
});

// if (maplevel != "provinsi") {
  var ext = ol.extent.boundingExtent(bound);
  ext = ol.proj.transformExtent(ext, ol.proj.get('EPSG:4326'), ol.proj.get('EPSG:3857'));
  map.getView().fit(ext,map.getSize());
  // console.log(bound);
// } else {
//   // console.log(mapcenter);
//   view.setCenter(mapcenter);
//   view.setZoom(3);
// }
// console.log(bound);

var olMapDiv = document.getElementById('olmap');
olMapDiv.parentNode.removeChild(olMapDiv);
gmap.controls[google.maps.ControlPosition.TOP_LEFT].push(olMapDiv);

new LayerSwitcher({map:map, div:"LayerSwitcher"});

var left_sign = "fa-angle-double-left", right_sign = "fa-angle-double-right";

$("#arrow-nav").click(function(){
  $("#left-menu").animate({
    width: "toggle"
  });
  // $("#fa-chevron").animate({
  //   "margin-left": ($(this).hasClass(right_sign)) ? "259px" : "2px"
  // });
  // if ($(this).hasClass(right_sign)) $(this).addClass(left_sign).removeClass(right_sign);
  // else $(this).addClass(right_sign).removeClass(left_sign);
});

$("#arrow-nav-2").click(function(){
  $("#right-menu").animate({
    width: "toggle"
  });
  // $("#fa-chevron-2").animate({
  //   "right": ($(this).hasClass(left_sign)) ? "215px" : "2px"
  // });
  // if ($(this).hasClass(right_sign)) $(this).addClass(left_sign).removeClass(right_sign);
  // else $(this).addClass(right_sign).removeClass(left_sign);
});

$("#arrow-nav-switcher").click(function(e){
  e.preventDefault();
  $("#LayerSwitcher").toggle('slow');
});

$("#input-wilayah").keyup(function(e){
  if ( e.which == 13 ) {
    e.preventDefault();
  }
  var searchQ = $(this).val().toString();
  if (searchQ.length >= 3) {
    $.ajax({
      url: "http://inarisk.bnpb.go.id/search-wilayah",
      method: "POST",
      data: { stringKey: searchQ },
      dataType: 'html'
    }).done(function(search_result) {
      // $("#left-menu").attr("height", "300px");
      $("#search-result").show();
      $("#search-result").empty().html(search_result);
    });
  }
  if (searchQ.length < 3) {
    $("#search-result").hide();
    // $("#left-menu").attr("height", "200px");
  }
});

$("body").on("click", ".search-result", function(){
  var kode = $(this).data("kode");
  var name = $(this).data("name");
  var maplevel = (kode.toString() >= 2) ? kode.toString().substring(0,2) : "provinsi";
  var kabupaten = (kode.toString().length > 2) ? kode.toString() : "kabupaten";
  $("#input-wilayah").val(name);
  $("input[name=maplevel]").val(maplevel);
  $("input[name=kabupaten]").val(kabupaten);
  // $("#hasil-kajian").attr("action", pathname);
  // $("#hasil-kajian").attr("action", pathname+"/"+maplevel+"/"+kabupaten);
  // $('<input />').attr('type', 'hidden')
  //       .attr('name', "maplevel").val(maplevel)
  //       .appendTo('#hasil-kajian');
  // $('<input />').attr('type', 'hidden')
  //       .attr('name', "kabupaten").val(kabupaten)
  //       .appendTo('#hasil-kajian');
  // $('<input />').attr('type', 'hidden')
  //       .attr('name', "submit_kajian").val(1)
  //       .appendTo('#hasil-kajian');
  // $("#hasil-kajian").submit();
  $("#search-result").hide();
  $("#hasil-kajian").submit();
});

$("body").on("click", ".export2csv", function(){
  $("table").tableToCSV();
});

$(".setGmap").click(function(){
  gmap.setMapTypeId($(this).data('value'));
});