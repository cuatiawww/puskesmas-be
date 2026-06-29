var mapdata = [],
    center_path = [],
    color = d3.scale.category20(),
    w = 1000,
    w_map = 1000,
    h = 350,
    x = 250,
    y = 150,
    z_height = 20,
    inner = 0.3,
    topojsonfile = '/topojson/kabupaten.json',
    topojsonid = 'collection',
    mapcenter = [118.010278125,-2.5239283215],
    mapscale = 1000,
    mapcolors = ["#ffffff", "#00dc14", "#FFCC00", "#FF0000"],
    maplinecolors = ["#000000", "#000000"];

function init_irbi(){
    update_data();
    init_tematik();
}

function refresh_irbi() {
    update_data();
    refresh_tematik();
}

function init_tematik(){
    $("#provinceirbimap").empty();
    var svg = d3.select("#provinceirbimap").append("svg").attr("width", w_map).attr("height", h);
    var path = d3.geo.path();
    
    d3.json(topojsonfile, function(error, topology) {
        var featureCollection = topojson.feature(topology, topology.objects[topojsonid]);
        var bounds = d3.geo.bounds(featureCollection);
        var centerX = d3.sum(bounds, function(d) {return d[0];}) / 2,
            centerY = d3.sum(bounds, function(d) {return d[1];}) / 2;
        var tooltip = d3.select('#provinceirbimap').append('div')
            .attr('class', 'hidden tooltip');
        var projection = d3.geo.mercator()
                          .scale(mapscale)                                   
                          .center(mapcenter)
                          .translate([w / 2, h / 2]);
        
        path.projection(projection);
        svg.selectAll("path").data(featureCollection.features)
            .enter().append("path")
            .attr("id", function(d) { var xyz = get_xyz(path, d); center_path.push({id: d.properties.id, x: xyz[0], y: xyz[1]}); return d.properties.id; })
            .attr("fill", function(d) { return iso_rentang(d.properties.id); })
            .attr("stroke", function(d) { return maplinecolors[0]; })
            .attr("d", path)
            .on('mouseover', function(d) {
                var html = '<div class="col-xs-12">';
                html += labelmap[d.properties.id]["name"];
                html += ' : ';
                html += get_selected_value(mapdata, d.properties.id).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                html += '</div>';

                tooltip.classed('hidden', false)
                    .attr('style', 'left:' + (d3.event.pageX - 100) + 'px; top:' + (d3.event.pageY - 200) + 'px')
                    .html(html);
            })
            .on('mouseout', function() {
                tooltip.classed('hidden', true);
            });

        // svg.selectAll(".place-label")
        //     .data(featureCollection.features)
        //     .enter().append("text")
        //     .attr("class", "place-label font8 fontbold")
        //     .attr("x", function(d){ var xyz = get_xyz(path, d); return xyz[0] - (labelmap[d.properties.id]["name"]).toString().length*2; }) //
        //     .attr("y", function(d){ var xyz = get_xyz(path, d); return xyz[1] - 10; })
        //     .attr("dy", "-2em")
        //     .attr("fill", function(d){ return mapline(d.properties.id); })
        //     .text(function(d) { return (mapline(d.properties.id) == "#FFFFFF") ? "" : labelmap[d.properties.id]["name"]; });
    });

    init_legend("#provinceirbimap", maplegendcolor(), w_map*0.82, 30);
}

function update_data(){
    baselayer = $("input[name=baselayer]:checked").val() || 2015;
    mapdata = irbimapdata.map((d) => { return {key: d.KODE_KAB, values: (d[baselayer]) ? parseFloat(d[baselayer]) : 0}; });
}

function refresh_tematik(){
    var svg = d3.select("#provinceirbimap").selectAll("svg");
    svg.selectAll("path")
        .transition()
        .duration(200)
        .attr("id", function(d) { return d.properties.id; })
        .attr("fill", function(d) { return iso_rentang(d.properties.id); })
        .attr("stroke", function(d) { return maplinecolors[0]; });
}

function get_xyz(path, d) {
    var bounds = path.bounds(d);
    var w_scale = (bounds[1][0] - bounds[0][0]) / w_map;
    var h_scale = (bounds[1][1] - bounds[0][1]) / h;
    var z = .96 / Math.max(w_scale, h_scale);
    var x = (bounds[1][0] + bounds[0][0]) / 2;
    var y = (bounds[1][1] + bounds[0][1]) / 2 + (h / z / 6);

    return [x, y, z];
}

function maplegendcolor(){
    var colors = [];
    var i = 0;
    colors.push({value: 1, label: ' Tidak ada data.'});
    colors.push({value: 1, label: ' < 13'});
    colors.push({value: 1, label: ' 13 - 144'});
    colors.push({value: 1, label: ' > 144'});
    return colors;
}

function iso_rentang(id){
    var selected_value = get_selected_value(mapdata, id);
    return selected_value <= 0 ? mapcolors[0] : selected_value < 13 ? mapcolors[1] : selected_value > 144 ? mapcolors[3] : mapcolors[2];
}

function get_selected_value(data, id){
    for(var i=0;i<data.length;i++){
        if(data[i].key == id) return parseInt(data[i].values);
    }
    return 0;
}

function init_legend(svgid, svgdata, svgposx, svgposy){
    var svg = d3.select(svgid).select("svg");
    svg.append("g").attr("class","labels")
    var legend = svg.select(".labels").selectAll("text").data(svgdata);
    var j = 0;
    legend.enter()
        .append("rect")
        .attr("width", 12)
        .attr("height", 12)
        .attr("style","z-index:999")
        .attr("x", svgposx)
        .attr("y", function(d,i){ return svgposy + i*15; }) //if(d.value > 0){ return svgposy + i*15; }else{ return 0; } 
        .style("fill", function(d,i) { if(d.value > 0){ return mapcolors[i]; }else{ return (d.value == 0) ? "#ffffff" : "transparent"; } })
        .style("stroke", "#000000");

    legend.enter()
        .append("text")
        .attr("x", svgposx+20)
        .attr("y", function(d,i){ return svgposy + i*15 + 9; })
        .attr("class","font10")
        .text(function(d,i){ return d.label; });
}

function refresh_legend_tematik(svgid, svgdata, svgposx, svgposy){
    var svg = d3.select(svgid).select("svg");
    svg.selectAll("g.labels").remove();
    svg.append("g").attr("class","labels")
    var legend = svg.select(".labels").selectAll("text").data(svgdata);
    var j = 0;
    legend.enter()
        .append("rect")
        .attr("width", 12)
        .attr("height", 12)
        .attr("style","z-index:999")
        .attr("x", svgposx)
        .attr("y", function(d,i){ return svgposy + i*15; }) //if(d.value > 0){ return svgposy + i*15; }else{ return 0; } 
        .style("fill", function(d,i) { if(d.value > 0){ return mapcolors[i]; }else{ return (d.value == 0) ? "#ffffff" : "transparent"; } });

    legend.enter()
        .append("text")
        .attr("x", svgposx+20)
        .attr("y", function(d,i){ return svgposy + i*15 + 9; })
        .attr("class","font10")
        .text(function(d,i){ return d.label; });
}

$("#input-wilayah").keyup(function(e){
  if ( e.which == 13 ) {
    e.preventDefault();
  }
  var searchQ = $(this).val().toString();
  if (searchQ.length >= 3) {
    $.ajax({
      url: "http://inarisk.bnpb.go.id/search-wilayah/search-kabupaten",
      method: "POST",
      data: { stringKey: searchQ },
      dataType: 'html'
    }).done(function(search_result) {
      $("#search-result").show();
      $("#search-result").empty().html(search_result);
    });
  }
  if (searchQ.length < 3) {
    $("#search-result").hide();
  }
});

$("body").on("click", ".search-result", function(){
  var kode = $(this).data("kode");
  var name = $(this).data("name");
  var kabcode = (kode.toString() >= 2) ? kode.toString() : "";
  var kabname = (kode.toString().length > 2) ? name.toString() : "";
  $("#input-wilayah").val(name);
  $("input[name=kabname]").val(kabname);
  $("input[name=kabcode]").val(kabcode);
  $("#search-result").hide();
  $("#irbi").submit();
});