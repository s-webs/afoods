// Yandex Maps integration for checkout page
let checkoutMap, checkoutPlacemark;

// Almaty bounds
const ALMATY_BOUNDS = [
    [43.0, 76.5], // Southwest
    [43.5, 77.0]  // Northeast
];

// Almaty center
const ALMATY_CENTER = [43.2220, 76.8512];

// Shop coordinates (delivery origin)
const SHOP_COORDS = {
    latitude: 43.264236,
    longitude: 76.954691
};

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

    // Create placemark if default address exists and calculate delivery
    if (defaultAddress && defaultAddress.latitude && defaultAddress.longitude) {
        const coords = [parseFloat(defaultAddress.latitude), parseFloat(defaultAddress.longitude)];
        createCheckoutPlacemark(coords);
        // Calculate delivery for default address after a delay to ensure function is loaded
        setTimeout(function() {
            if (typeof window.calculateDeliveryForAddress === 'function') {
                window.calculateDeliveryForAddress(coords[0], coords[1]);
            } else if (typeof calculateDeliveryForAddress === 'function') {
                calculateDeliveryForAddress(coords[0], coords[1]);
            } else {
                console.warn('calculateDeliveryForAddress function not available yet');
                // Try again after a bit more time
                setTimeout(function() {
                    if (typeof window.calculateDeliveryForAddress === 'function') {
                        window.calculateDeliveryForAddress(coords[0], coords[1]);
                    }
                }, 1000);
            }
        }, 1000);
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
    
    // Automatically calculate delivery when address is selected
    if (coords && coords[0] && coords[1]) {
        console.log('Address selected, calculating delivery for:', coords[0], coords[1]);
        // Use setTimeout to ensure the function is available
        setTimeout(function() {
            if (typeof window.calculateDeliveryForAddress === 'function') {
                window.calculateDeliveryForAddress(coords[0], coords[1]);
            } else if (typeof calculateDeliveryForAddress === 'function') {
                calculateDeliveryForAddress(coords[0], coords[1]);
            } else {
                console.warn('calculateDeliveryForAddress not available');
            }
        }, 100);
    }
}

// Calculate delivery cost for selected address
function calculateDeliveryForAddress(latitude, longitude) {
    const deliveryResults = document.getElementById('delivery-results');
    if (!deliveryResults) {
        console.warn('Delivery results element not found');
        return;
    }
    
    console.log('Calculating delivery for address:', latitude, longitude);
    
    // Shop coordinates (Almaty)
    const shopCoords = SHOP_COORDS;
    
    deliveryResults.innerHTML = '<p class="text-sm text-gray-600">Расчет стоимости доставки...</p>';
    
    fetch('/cart/calculate-delivery', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({
            from_latitude: shopCoords.latitude,
            from_longitude: shopCoords.longitude,
            to_latitude: latitude,
            to_longitude: longitude
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Delivery calculation response:', data);
        
        if (data.success && data.options && data.options.length > 0) {
            displayDeliveryOptions(data.options);
        } else {
            console.warn('No delivery options returned', data);
            deliveryResults.innerHTML = '<p class="text-sm text-orange-600">Не удалось рассчитать стоимость доставки. Попробуйте выбрать другой адрес или оформите заказ - доставка будет рассчитана при оформлении.</p>';
        }
    })
    .catch(error => {
        console.error('Delivery calculation error:', error);
        deliveryResults.innerHTML = '<p class="text-sm text-orange-600">Ошибка при расчете доставки. Попробуйте обновить страницу или оформите заказ - доставка будет рассчитана при оформлении.</p>';
    });
}

// Make function globally available
window.calculateDeliveryForAddress = calculateDeliveryForAddress;

function displayDeliveryOptions(options) {
    const deliveryResults = document.getElementById('delivery-results');
    if (!deliveryResults) return;
    
    let html = '<div class="space-y-2">';
    
    options.forEach(function(option) {
        const price = option.price || 0;
        const time = option.estimated_time ? formatDeliveryTime(option.estimated_time) : '';
        
        html += `
            <div class="flex items-center justify-between p-3 bg-white rounded-lg border border-main/20 hover:border-main/40 transition">
                <div>
                    <p class="text-sm font-semibold text-main-graphit">${option.name || 'Доставка'}</p>
                    ${time ? `<p class="text-xs text-gray-600 mt-1">${time}</p>` : ''}
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-main">${price} ₸</p>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    deliveryResults.innerHTML = html;
}

function formatDeliveryTime(seconds) {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    
    if (hours > 0) {
        return `${hours} ч ${minutes} мин`;
    }
    return `${minutes} мин`;
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
