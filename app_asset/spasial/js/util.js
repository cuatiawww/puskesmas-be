var setCookies = (cookieName, cookieValue) => {
    $.ajax({
        method: "POST",
        url: "/cookies",
        data: { name: cookieName, value: cookieValue },
        success: function() { location.reload(); }
    })
    .done(function() {
        location.reload();
    });
}

var GetObjectValue = (o, search_key, target_id, target_value) => {
	for (var i = 0; i < o.length; i++) {
		if (o[i][target_id] == search_key) return o[i][target_value];
	}
	return false;
}

var titleCase = (str) => {
    return str.toLowerCase().split(' ').map(function(word) {
        return (word.charAt(0).toUpperCase() + word.slice(1));
    }).join(' ');
}

function download_csv(csv, filename) {
    var csvFile;
    var downloadLink;
    // CSV FILE
    csvFile = new Blob([csv], {type: "text/csv"});
    // Download link
    downloadLink = document.createElement("a");
    // File name
    downloadLink.download = filename;
    // We have to create a link to the file
    downloadLink.href = window.URL.createObjectURL(csvFile);
    // Make sure that the link is not displayed
    downloadLink.style.display = "none";
    // Add the link to your DOM
    document.body.appendChild(downloadLink);
    // Lanzamos
    downloadLink.click();
}

function export_table_to_csv(tableid, filename) {
    var csv = [];
    var rows = document.querySelectorAll("table#"+tableid+" tr");
    var SelectedWilayah = document.getElementById("SelectedWilayah");
    SelectedWilayah = SelectedWilayah.innerHTML.replace("<strong>","").replace("</strong>","");
    console.log(SelectedWilayah);
    csv.push(SelectedWilayah);
    for (var i = 0; i < rows.length; i++) {
        var row = [], cols = rows[i].querySelectorAll("td, th");
        
        for (var j = 0; j < cols.length; j++) {
            var tmp = cols[j].innerHTML;
            if (parseInt(tmp[0]) == tmp[0]) tmp = cols[j].innerHTML.replace(/\./g,"");
            row.push(tmp);
        }

        csv.push(row.join(","));        
    }

    // Download CSV
    download_csv(csv.join("\n"), filename);
}