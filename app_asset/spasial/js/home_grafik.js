var chartcolor = ["#666666","#5D8AA8","#AF002A","#FFBF00","#A4C639","#8F9779","#3B444B","#E9D66B","#007FFF", "#E0218A","#967117","#CAE00D","#3B3C36","#648C11","#72A0C1","#C46210","#3B7A57","#FF7E00", "#9966CC","#841B2D","#008000","#00FFFF","#B2BEB5","#FDEE00","#89CFF0","#FF91AF","#21ABCD", "#98777B","#848482","#3D2B1F"]

var types = {data0:"bar", data1:"bar", data2:"bar", data3:"bar", data4:"bar"}
var names = {data0: lang._LUAS_BAHAYA_, data1: lang._JIWA_TERPAPAR_, data2: lang._FISIK_RUPIAH_, data3: lang._EKONOMI_RUPIAH_, data4: lang._LINGKUNGAN_HA_}

// $("#chartid").css("border", "0px solid rgb(169, 169, 169)")
// $("#chartid").css("margin-left", "-70px")
// $("#graph_table").css("margin-top", "-50px")
// $("#graph_table").css("padding", "40px 10px")
// $("#graph_table").css("background-color", "rgb(245,245,245)")

var chart = c3.generate({
    bindto: '#chartid',
    data: { rows: chartdata, type: 'bar', types: types, names: names, colors: chartcolor },
    axis: {
        x: { type: 'category', categories: label, tick: { rotate: -35, culling: { max: 40 } }, height: 130 },
        y: { label: { text: 'Potensi Dampak', position: 'outer-top' }, tick: { format: d3.format(",") } },
    },
    zoom: { enabled: true },
    legend: { position: 'right' }
})