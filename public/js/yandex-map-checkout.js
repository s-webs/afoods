// Yandex Maps integration for checkout page
let checkoutMap, checkoutPlacemark;

// Almaty bounds
const ALMATY_BOUNDS = [
    [43.0, 76.5], // Southwest
    [43.5, 77.0]  // Northeast
];

// Almaty center
const ALMATY_CENTER = [43.2220, 76.8512];

function initCheckoutMap(defaultAddress) {
    const addressSearch = document.getElementById('address_search');
    const addressInput = document.getElementById('delivery_address_address');
    const latitudeInput = document.getElementById('delivery_address_latitude');
    const longitudeInput = document.getElementById('delivery_address_longitude');

    // Get initial coordinates from default address or use Almaty center
    let initialLat = ALMATY_CENTER[0];
    let initialLon = ALMATY_CENTER[1];
    let initialZoom = 12;

    if (defaultAddress && defaultAddress.latitude && defaultAddress.longitude) {
        initialLat = parseFloat(defaultAddress.latitude);
        initialLon = parseFloat(defaultAddress.longitude);
        initialZoom = 16;
    }

    // Create map
    checkoutMap = new ymaps.Map('map', {
        center: [initialLat, initialLon],
        zoom: initialZoom,
        controls: ['zoomControl', 'fullscreenControl']
    });

    // Make map globally available
    window.map = checkoutMap;
    window.checkoutMap = checkoutMap;

    // Restrict map bounds to Almaty
    checkoutMap.setBounds(ALMATY_BOUNDS, {
        checkZoomRange: true,
        duration: 0
    });

    // Restrict map movement to Almaty area
    checkoutMap.events.add('boundschange', function() {
        const bounds = checkoutMap.getBounds();
        const sw = bounds[0];
        const ne = bounds[1];

        if (sw[0] < ALMATY_BOUNDS[0][0] || sw[1] < ALMATY_BOUNDS[0][1] ||
            ne[0] > ALMATY_BOUNDS[1][0] || ne[1] > ALMATY_BOUNDS[1][1]) {
            checkoutMap.setBounds(ALMATY_BOUNDS, {
                checkZoomRange: true,
                duration: 300
            });
        }
    });

    // Create placemark if default address exists
    if (defaultAddress && defaultAddress.latitude && defaultAddress.longitude) {
        const coords = [parseFloat(defaultAddress.latitude), parseFloat(defaultAddress.longitude)];
        createCheckoutPlacemark(coords);
    }

    // Handle map click
    checkoutMap.events.add('click', function(e) {
        const coords = e.get('coords');
        
        if (coords[0] >= ALMATY_BOUNDS[0][0] && coords[0] <= ALMATY_BOUNDS[1][0] &&
            coords[1] >= ALMATY_BOUNDS[0][1] && coords[1] <= ALMATY_BOUNDS[1][1]) {
            createCheckoutPlacemark(coords);
            geocodeCheckoutCoordinates(coords);
        } else {
            alert('Пожалуйста, выберите адрес в пределах города Алматы');
        }
    });

    // Handle address search input
    if (addressSearch) {
        addressSearch.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const query = addressSearch.value.trim();
                if (query.length >= 3) {
                    geocodeCheckoutAddress(query);
                } else {
                    alert('Введите минимум 3 символа для поиска');
                }
            }
        });
    }
}

function createCheckoutPlacemark(coords) {
    if (!checkoutMap) return;
    
    if (checkoutPlacemark) {
        checkoutMap.geoObjects.remove(checkoutPlacemark);
    }

    checkoutPlacemark = new ymaps.Placemark(coords, {}, {
        draggable: true,
        preset: 'islands#blueIcon'
    });

    checkoutMap.geoObjects.add(checkoutPlacemark);

    // Handle placemark drag
    checkoutPlacemark.events.add('dragend', function() {
        const newCoords = checkoutPlacemark.geometry.getCoordinates();
        geocodeCheckoutCoordinates(newCoords);
    });
    
    // Make available globally
    window.placemark = checkoutPlacemark;
    window.checkoutPlacemark = checkoutPlacemark;
    window.createPlacemark = createCheckoutPlacemark;
    window.createCheckoutPlacemark = createCheckoutPlacemark;
}

function geocodeCheckoutCoordinates(coords) {
    if (!ymaps) return;
    
    ymaps.geocode(coords, {
        results: 1
    }).then(function(res) {
        const firstGeoObject = res.geoObjects.get(0);
        if (firstGeoObject) {
            updateCheckoutFormFields(firstGeoObject, coords);
        }
    });
}

function geocodeCheckoutAddress(address) {
    if (!ymaps || !address || address.length < 3) return;
    
    ymaps.geocode(address + ', Алматы', {
        results: 1,
        boundedBy: ALMATY_BOUNDS
    }).then(function(res) {
        const firstGeoObject = res.geoObjects.get(0);
        if (firstGeoObject) {
            const coords = firstGeoObject.geometry.getCoordinates();
            
            if (coords[0] >= ALMATY_BOUNDS[0][0] && coords[0] <= ALMATY_BOUNDS[1][0] &&
                coords[1] >= ALMATY_BOUNDS[0][1] && coords[1] <= ALMATY_BOUNDS[1][1]) {
                checkoutMap.setCenter(coords, 16);
                createCheckoutPlacemark(coords);
                updateCheckoutFormFields(firstGeoObject, coords);
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

function updateCheckoutFormFields(geoObject, coords) {
    const addressInput = document.getElementById('delivery_address_address');
    const latitudeInput = document.getElementById('delivery_address_latitude');
    const longitudeInput = document.getElementById('delivery_address_longitude');
    const addressSearch = document.getElementById('address_search');
    
    if (!addressInput || !latitudeInput || !longitudeInput) return;
    
    const address = geoObject.getAddressLine();
    
    addressInput.value = address;
    latitudeInput.value = coords[0].toFixed(6);
    longitudeInput.value = coords[1].toFixed(6);
    
    if (addressSearch) {
        addressSearch.value = address;
    }
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const checkoutForm = document.getElementById('checkoutForm');
    if (!checkoutForm) return;

    checkoutForm.addEventListener('submit', function(e) {
        const latitudeInput = document.getElementById('delivery_address_latitude');
        const longitudeInput = document.getElementById('delivery_address_longitude');
        const addressInput = document.getElementById('delivery_address_address');
        
        if (!latitudeInput.value || !longitudeInput.value || !addressInput.value) {
            e.preventDefault();
            alert('Пожалуйста, выберите адрес на карте или используйте поиск');
            return false;
        }

        const lat = parseFloat(latitudeInput.value);
        const lon = parseFloat(longitudeInput.value);

        if (lat < ALMATY_BOUNDS[0][0] || lat > ALMATY_BOUNDS[1][0] ||
            lon < ALMATY_BOUNDS[0][1] || lon > ALMATY_BOUNDS[1][1]) {
            e.preventDefault();
            alert('Адрес должен находиться в пределах города Алматы');
            return false;
        }
    });
});
