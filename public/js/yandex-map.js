// Yandex Maps integration for Almaty only
let map, placemark;

// Almaty bounds
const ALMATY_BOUNDS = [
    [43.0, 76.5], // Southwest
    [43.5, 77.0]  // Northeast
];

// Almaty center
const ALMATY_CENTER = [43.2220, 76.8512];

// Global functions
let addressInput, latitudeInput, longitudeInput, addressSearch;
let searchTimeout;

function createPlacemark(coords) {
    if (!map) return;
    
    if (placemark) {
        map.geoObjects.remove(placemark);
    }

    placemark = new ymaps.Placemark(coords, {}, {
        draggable: true,
        preset: 'islands#blueIcon'
    });

    map.geoObjects.add(placemark);

    // Handle placemark drag
    placemark.events.add('dragend', function() {
        const newCoords = placemark.geometry.getCoordinates();
        geocodeCoordinates(newCoords);
    });
}

function geocodeCoordinates(coords) {
    if (!ymaps) return;
    
    ymaps.geocode(coords, {
        results: 1
    }).then(function(res) {
        const firstGeoObject = res.geoObjects.get(0);
        if (firstGeoObject) {
            updateFormFields(firstGeoObject, coords);
        }
    });
}

function geocodeAddress(address) {
    if (!ymaps || !address || address.length < 3) return;
    
    ymaps.geocode(address + ', Алматы', {
        results: 1,
        boundedBy: ALMATY_BOUNDS
    }).then(function(res) {
        const firstGeoObject = res.geoObjects.get(0);
        if (firstGeoObject) {
            const coords = firstGeoObject.geometry.getCoordinates();
            
            // Check if coordinates are within Almaty
            if (coords[0] >= ALMATY_BOUNDS[0][0] && coords[0] <= ALMATY_BOUNDS[1][0] &&
                coords[1] >= ALMATY_BOUNDS[0][1] && coords[1] <= ALMATY_BOUNDS[1][1]) {
                map.setCenter(coords, 16);
                createPlacemark(coords);
                updateFormFields(firstGeoObject, coords);
            } else {
                alert('Выбранный адрес находится за пределами Алматы');
            }
        } else {
            alert('Адрес не найден. Попробуйте уточнить запрос или выберите точку на карте');
        }
    }).catch(function(error) {
        console.error('Geocoding error:', error);
        alert('Ошибка при поиске адреса. Попробуйте выбрать точку на карте');
    });
}

function updateFormFields(geoObject, coords) {
    if (!addressInput || !latitudeInput || !longitudeInput) return;
    
    const address = geoObject.getAddressLine();
    
    addressInput.value = address;
    latitudeInput.value = coords[0].toFixed(6);
    longitudeInput.value = coords[1].toFixed(6);
    
    if (addressSearch) {
        addressSearch.value = address;
    }
}

// Make functions globally available
window.createPlacemark = createPlacemark;
window.geocodeCoordinates = geocodeCoordinates;
window.updateFormFields = updateFormFields;

document.addEventListener('DOMContentLoaded', function() {
    const addressForm = document.getElementById('addressForm');
    if (!addressForm) return;

    addressSearch = document.getElementById('address_search');
    addressInput = document.getElementById('address');
    latitudeInput = document.getElementById('latitude');
    longitudeInput = document.getElementById('longitude');

    // Initialize Yandex Maps
    if (typeof ymaps !== 'undefined') {
        ymaps.ready(initMap);
    } else {
        console.error('Yandex Maps API not loaded');
    }

    function initMap() {
        // Get initial coordinates from form or use Almaty center
        const initialLat = parseFloat(latitudeInput.value) || ALMATY_CENTER[0];
        const initialLon = parseFloat(longitudeInput.value) || ALMATY_CENTER[1];

        // Create map with Almaty restrictions (without searchControl to avoid Suggest)
        map = new ymaps.Map('map', {
            center: [initialLat, initialLon],
            zoom: initialLat !== ALMATY_CENTER[0] ? 16 : 12,
            controls: ['zoomControl', 'fullscreenControl']
        });

        // Make map globally available
        window.map = map;

        // Restrict map bounds to Almaty
        map.setBounds(ALMATY_BOUNDS, {
            checkZoomRange: true,
            duration: 0
        });

        // Restrict map movement to Almaty area
        map.events.add('boundschange', function() {
            const bounds = map.getBounds();
            const sw = bounds[0];
            const ne = bounds[1];

            // Check if map is outside Almaty bounds
            if (sw[0] < ALMATY_BOUNDS[0][0] || sw[1] < ALMATY_BOUNDS[0][1] ||
                ne[0] > ALMATY_BOUNDS[1][0] || ne[1] > ALMATY_BOUNDS[1][1]) {
                map.setBounds(ALMATY_BOUNDS, {
                    checkZoomRange: true,
                    duration: 300
                });
            }
        });

        // Create placemark if coordinates exist
        if (initialLat && initialLon && initialLat !== ALMATY_CENTER[0]) {
            createPlacemark([initialLat, initialLon]);
        }

        // Handle map click
        map.events.add('click', function(e) {
            const coords = e.get('coords');
            
            // Check if coordinates are within Almaty bounds
            if (coords[0] >= ALMATY_BOUNDS[0][0] && coords[0] <= ALMATY_BOUNDS[1][0] &&
                coords[1] >= ALMATY_BOUNDS[0][1] && coords[1] <= ALMATY_BOUNDS[1][1]) {
                createPlacemark(coords);
                geocodeCoordinates(coords);
            } else {
                alert('Пожалуйста, выберите адрес в пределах города Алматы');
            }
        });

        // Handle address search input
        if (addressSearch) {
            // Handle Enter key
            addressSearch.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const query = addressSearch.value.trim();
                    if (query.length >= 3) {
                        geocodeAddress(query);
                    } else {
                        alert('Введите минимум 3 символа для поиска');
                    }
                }
            });

            // Handle input with debounce (disabled auto-search, only on Enter)
            // Uncomment if you want auto-search:
            // addressSearch.addEventListener('input', function() {
            //     clearTimeout(searchTimeout);
            //     const query = addressSearch.value.trim();
            //     
            //     if (query.length >= 3) {
            //         searchTimeout = setTimeout(function() {
            //             geocodeAddress(query);
            //         }, 500);
            //     }
            // });
        }
    }

    // Form validation
    addressForm.addEventListener('submit', function(e) {
        if (!latitudeInput.value || !longitudeInput.value || !addressInput.value) {
            e.preventDefault();
            alert('Пожалуйста, выберите адрес на карте или используйте поиск');
            return false;
        }

        const lat = parseFloat(latitudeInput.value);
        const lon = parseFloat(longitudeInput.value);

        // Validate coordinates are within Almaty
        if (lat < ALMATY_BOUNDS[0][0] || lat > ALMATY_BOUNDS[1][0] ||
            lon < ALMATY_BOUNDS[0][1] || lon > ALMATY_BOUNDS[1][1]) {
            e.preventDefault();
            alert('Адрес должен находиться в пределах города Алматы');
            return false;
        }
    });
});
