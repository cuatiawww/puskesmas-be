(function() {
  // Inisialisasi peta
  const petaElement = document.getElementById('peta-lokasi-kejadian');
  
  if (!petaElement) return;

  // Koordinat default (Jakarta)
  const defaultLocation = { lat: -6.2088, lng: 106.8456 };
  
  const map = new google.maps.Map(petaElement, {
    zoom: 13,
    center: defaultLocation,
    mapTypeControl: true,
    fullscreenControl: true,
  });

  let marker = new google.maps.Marker({
    position: defaultLocation,
    map: map,
    draggable: true,
  });

  // Search box
  const searchInput = document.getElementById('searchInput');
  const autocomplete = new google.maps.places.Autocomplete(searchInput);
  autocomplete.bindTo('bounds', map);

  autocomplete.addListener('place_changed', function() {
    const place = autocomplete.getPlace();
    if (!place.geometry) return;

    if (place.geometry.viewport) {
      map.fitBounds(place.geometry.viewport);
    } else {
      map.setCenter(place.geometry.location);
      map.setZoom(17);
    }

    marker.setPosition(place.geometry.location);
    marker.setVisible(true);

    // Update search input dengan nama lengkap lokasi
    searchInput.value = place.formatted_address || place.name;
  });

  // Klik pada peta untuk menandai lokasi
  map.addListener('click', function(event) {
    marker.setPosition(event.latLng);
  });

  // Drag marker untuk update lokasi
  marker.addListener('dragend', function(event) {
    const lat = event.latLng.lat();
    const lng = event.latLng.lng();
    
    // Reverse geocode untuk mendapatkan nama lokasi
    const geocoder = new google.maps.Geocoder();
    geocoder.geocode({ location: { lat: lat, lng: lng } }, function(results) {
      if (results && results[0]) {
        searchInput.value = results[0].formatted_address;
      }
    });
  });
})();
