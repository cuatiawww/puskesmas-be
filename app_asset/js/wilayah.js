fetch("http://localhost:8080/api-wilayah")
  .then(res => res.json())
  .then(data => {
     console.log("Data wilayah dari Yii:", data);
     renderWilayahTable(data);
  });

  function renderWilayahTable(data) {
    const tbody = document.querySelector("#table-wilayah-body");
    tbody.innerHTML = "";

    data.forEach(w => {
        tbody.innerHTML += `
            <tr>
                <td>${w.id}</td>
                <td>${w.nama}</td>
                <td>${w.kecamatan}</td>
                <td>${w.kabupaten}</td>
                <td>${w.provinsi}</td>
            </tr>
        `;
    });
}
