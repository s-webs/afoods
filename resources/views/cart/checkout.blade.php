@extends('layouts.public')

@section('content')
<div class="container mx-auto px-2 xl:px-40">
    <div class="max-w-4xl mx-auto mt-10">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Order Summary -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-lg p-8">
                    <h2 class="text-2xl font-bold text-main-graphit mb-6">Оформление заказа</h2>

                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <ul class="text-sm text-red-600">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('cart.process-order') }}" id="checkoutForm">
                        @csrf

                        <div class="space-y-6">
                            <!-- Contact Information -->
                            <div>
                                <h3 class="text-lg font-semibold text-main-graphit mb-4">Контактная информация</h3>
                                
                                <div class="mb-4">
                                    <label for="name" class="block text-sm font-medium text-main-graphit mb-2">Имя *</label>
                                    <input 
                                        id="name" 
                                        type="text" 
                                        name="name" 
                                        value="{{ old('name', auth()->user()->name ?? '') }}" 
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-main focus:border-transparent outline-none transition"
                                        placeholder="Введите ваше имя"
                                    >
                                </div>

                                <div class="mb-4">
                                    <label for="phone" class="block text-sm font-medium text-main-graphit mb-2">Телефон *</label>
                                    <input 
                                        id="phone" 
                                        type="tel" 
                                        name="phone" 
                                        value="{{ old('phone', $shopper->phone ?? '') }}" 
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-main focus:border-transparent outline-none transition"
                                        placeholder="+7 (700) 123-45-67"
                                    >
                                </div>

                                <div class="mb-4">
                                    <label for="email" class="block text-sm font-medium text-main-graphit mb-2">Email (необязательно)</label>
                                    <input 
                                        id="email" 
                                        type="email" 
                                        name="email" 
                                        value="{{ old('email', auth()->user()->email ?? '') }}" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-main focus:border-transparent outline-none transition"
                                        placeholder="example@mail.com"
                                    >
                                </div>
                            </div>

                            <!-- Delivery Address -->
                            <div>
                                <h3 class="text-lg font-semibold text-main-graphit mb-4">Адрес доставки</h3>

                                @if(auth()->check() && count($addresses) > 0)
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-main-graphit mb-2">Выберите адрес</label>
                                        <select 
                                            id="address_select" 
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-main focus:border-transparent outline-none transition"
                                        >
                                            <option value="">Выберите сохраненный адрес</option>
                                            @foreach($addresses as $addr)
                                                <option value="{{ $addr['id'] }}" {{ ($defaultAddress['id'] ?? null) === ($addr['id'] ?? null) ? 'selected' : '' }}>
                                                    {{ $addr['address'] ?? '' }}
                                                    @if($addr['house'] ?? null), д. {{ $addr['house'] }}@endif
                                                    @if($addr['apartment'] ?? null), кв. {{ $addr['apartment'] }}@endif
                                                </option>
                                            @endforeach
                                            <option value="new">Новый адрес</option>
                                        </select>
                                    </div>
                                @endif

                                <div id="address_fields">
                                    <div class="mb-4">
                                        <label for="address_search" class="block text-sm font-medium text-main-graphit mb-2">Поиск адреса</label>
                                        <input 
                                            id="address_search" 
                                            type="text" 
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-main focus:border-transparent outline-none transition"
                                            placeholder="Введите адрес в Алматы"
                                        >
                                        <p class="text-xs text-gray-500 mt-1">Введите адрес и нажмите Enter, или выберите точку на карте</p>
                                    </div>

                                    <div class="mb-4">
                                        <div id="map" style="width: 100%; height: 400px; border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb;"></div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="delivery_address_address" class="block text-sm font-medium text-main-graphit mb-2">Адрес *</label>
                                        <input 
                                            id="delivery_address_address" 
                                            type="text" 
                                            name="delivery_address[address]" 
                                            value="{{ old('delivery_address.address', $defaultAddress['address'] ?? '') }}" 
                                            required
                                            readonly
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-main focus:border-transparent outline-none transition"
                                            placeholder="Выберите адрес на карте"
                                        >
                                    </div>

                                    <input type="hidden" id="delivery_address_latitude" name="delivery_address[latitude]" value="{{ old('delivery_address.latitude', $defaultAddress['latitude'] ?? '') }}" required>
                                    <input type="hidden" id="delivery_address_longitude" name="delivery_address[longitude]" value="{{ old('delivery_address.longitude', $defaultAddress['longitude'] ?? '') }}" required>

                                    <div class="grid grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <label for="delivery_address_house" class="block text-sm font-medium text-main-graphit mb-2">Дом</label>
                                            <input 
                                                id="delivery_address_house" 
                                                type="text" 
                                                name="delivery_address[house]" 
                                                value="{{ old('delivery_address.house', $defaultAddress['house'] ?? '') }}" 
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-main focus:border-transparent outline-none transition"
                                                placeholder="150"
                                            >
                                        </div>
                                        <div>
                                            <label for="delivery_address_apartment" class="block text-sm font-medium text-main-graphit mb-2">Квартира</label>
                                            <input 
                                                id="delivery_address_apartment" 
                                                type="text" 
                                                name="delivery_address[apartment]" 
                                                value="{{ old('delivery_address.apartment', $defaultAddress['apartment'] ?? '') }}" 
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-main focus:border-transparent outline-none transition"
                                                placeholder="25"
                                            >
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <label for="delivery_address_entrance" class="block text-sm font-medium text-main-graphit mb-2">Подъезд</label>
                                            <input 
                                                id="delivery_address_entrance" 
                                                type="text" 
                                                name="delivery_address[entrance]" 
                                                value="{{ old('delivery_address.entrance', $defaultAddress['entrance'] ?? '') }}" 
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-main focus:border-transparent outline-none transition"
                                                placeholder="1"
                                            >
                                        </div>
                                        <div>
                                            <label for="delivery_address_floor" class="block text-sm font-medium text-main-graphit mb-2">Этаж</label>
                                            <input 
                                                id="delivery_address_floor" 
                                                type="text" 
                                                name="delivery_address[floor]" 
                                                value="{{ old('delivery_address.floor', $defaultAddress['floor'] ?? '') }}" 
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-main focus:border-transparent outline-none transition"
                                                placeholder="5"
                                            >
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="delivery_address_notes" class="block text-sm font-medium text-main-graphit mb-2">Дополнительные заметки к адресу</label>
                                        <textarea 
                                            id="delivery_address_notes" 
                                            name="delivery_address[notes]" 
                                            rows="2"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-main focus:border-transparent outline-none transition"
                                            placeholder="Код домофона, ориентир и т.д."
                                        >{{ old('delivery_address.notes', $defaultAddress['notes'] ?? '') }}</textarea>
                                    </div>
                                </div>

                                @if(auth()->check())
                                    <div class="mb-4">
                                        <a href="{{ route('profile.addresses.create') }}" class="text-main hover:underline text-sm">
                                            Добавить новый адрес
                                        </a>
                                    </div>
                                @endif

                                <!-- Yandex Delivery Widget -->
                                <div id="yandex-delivery-widget" class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <h4 class="text-sm font-semibold text-main-graphit mb-3 flex items-center gap-2">
                                        <i class="ph-bold ph-truck text-main"></i>
                                        Доставка Яндекс Доставкой
                                    </h4>
                                    <div id="delivery-options" class="space-y-2">
                                        <div id="delivery-results" class="space-y-2">
                                            <p class="text-sm text-gray-600">Выберите адрес на карте для расчета стоимости доставки</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Order Notes -->
                            <div>
                                <label for="notes" class="block text-sm font-medium text-main-graphit mb-2">Комментарий к заказу</label>
                                <textarea 
                                    id="notes" 
                                    name="notes" 
                                    rows="3"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-main focus:border-transparent outline-none transition"
                                    placeholder="Дополнительные пожелания к заказу"
                                >{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Order Summary Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-lg p-6 sticky top-5">
                    <h3 class="text-lg font-semibold text-main-graphit mb-4">Ваш заказ</h3>
                    
                    <div class="space-y-3 mb-4">
                        @foreach($items as $item)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">
                                    {{ $item['product']->name }} × {{ $item['quantity'] }}
                                </span>
                                <span class="font-semibold">{{ $item['total'] }} ₸</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="border-t border-gray-200 pt-4 mb-4">
                        <div class="flex items-center justify-between">
                            <span class="text-lg font-semibold text-main-graphit">Итого:</span>
                            <span class="text-xl font-bold text-main">{{ $totalPrice }} ₸</span>
                        </div>
                    </div>

                    <button 
                        type="submit" 
                        form="checkoutForm"
                        class="w-full bg-main text-white py-3 rounded-lg font-medium hover:bg-opacity-90 transition duration-200"
                    >
                        Оформить заказ
                    </button>

                    <a 
                        href="{{ route('cart.index') }}" 
                        class="block mt-3 text-center text-sm text-gray-600 hover:text-main"
                    >
                        ← Вернуться в корзину
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@if(auth()->check() && count($addresses) > 0)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const addressSelect = document.getElementById('address_select');
    const addresses = @json($addresses);
    
    addressSelect.addEventListener('change', function() {
        const selectedId = this.value;
        
            if (selectedId === 'new' || selectedId === '') {
            // Clear fields for new address
            document.getElementById('delivery_address_address').value = '';
            document.getElementById('delivery_address_latitude').value = '';
            document.getElementById('delivery_address_longitude').value = '';
            document.getElementById('delivery_address_house').value = '';
            document.getElementById('delivery_address_apartment').value = '';
            document.getElementById('delivery_address_entrance').value = '';
            document.getElementById('delivery_address_floor').value = '';
            document.getElementById('delivery_address_notes').value = '';
            document.getElementById('address_search').value = '';
            
            // Clear delivery results
            const deliveryResults = document.getElementById('delivery-results');
            if (deliveryResults) {
                deliveryResults.innerHTML = '<p class="text-sm text-gray-600">Выберите адрес на карте для расчета стоимости доставки</p>';
            }
            
            // Clear map
            if (window.checkoutMap && window.checkoutPlacemark) {
                window.checkoutMap.geoObjects.remove(window.checkoutPlacemark);
                window.checkoutPlacemark = null;
                window.map = null;
            }
            if (window.checkoutMap) {
                window.checkoutMap.setCenter([43.2220, 76.8512], 12);
            }
        } else {
            // Fill fields with selected address
            const address = addresses.find(a => (a.id || null) === selectedId);
            if (address) {
                document.getElementById('delivery_address_address').value = address.address || '';
                document.getElementById('delivery_address_latitude').value = address.latitude || '';
                document.getElementById('delivery_address_longitude').value = address.longitude || '';
                document.getElementById('delivery_address_house').value = address.house || '';
                document.getElementById('delivery_address_apartment').value = address.apartment || '';
                document.getElementById('delivery_address_entrance').value = address.entrance || '';
                document.getElementById('delivery_address_floor').value = address.floor || '';
                document.getElementById('delivery_address_notes').value = address.notes || '';
                document.getElementById('address_search').value = address.address || '';
                
                // Update map
                if (address.latitude && address.longitude && window.checkoutMap) {
                    const coords = [parseFloat(address.latitude), parseFloat(address.longitude)];
                    window.checkoutMap.setCenter(coords, 16);
                    if (window.createCheckoutPlacemark) {
                        window.createCheckoutPlacemark(coords);
                    } else if (window.createPlacemark) {
                        window.createPlacemark(coords);
                    }
                    
                    // Calculate delivery for selected address
                    if (typeof window.calculateDeliveryForAddress === 'function') {
                        window.calculateDeliveryForAddress(parseFloat(address.latitude), parseFloat(address.longitude));
                    } else if (typeof calculateDeliveryForAddress === 'function') {
                        calculateDeliveryForAddress(parseFloat(address.latitude), parseFloat(address.longitude));
                    }
                }
            }
        }
    });
});
</script>
@endif

<script src="https://api-maps.yandex.ru/2.1/?apikey={{ $yandexApiKey }}&lang=ru_RU"></script>
<script src="{{ asset('js/yandex-map-checkout.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize map for checkout page
    const defaultAddress = @json($defaultAddress);
    
    if (typeof ymaps !== 'undefined') {
        ymaps.ready(function() {
            initCheckoutMap(defaultAddress);
        });
    } else {
        // Wait for Yandex Maps to load
        const checkYmaps = setInterval(function() {
            if (typeof ymaps !== 'undefined') {
                clearInterval(checkYmaps);
                ymaps.ready(function() {
                    initCheckoutMap(defaultAddress);
                });
            }
        }, 100);
        
        // Timeout after 5 seconds
        setTimeout(function() {
            clearInterval(checkYmaps);
        }, 5000);
    }
});
</script>
@endsection
