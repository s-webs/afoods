@extends('layouts.public')

@section('content')
<div class="container mx-auto px-2 xl:px-40">
    <div class="max-w-2xl mx-auto mt-10">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-main-graphit">Редактировать адрес</h2>
                <a href="{{ route('profile.addresses.index') }}" class="text-gray-600 hover:text-main">
                    <i class="ph-bold ph-x text-2xl"></i>
                </a>
            </div>

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <ul class="text-sm text-red-600">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('profile.addresses.update', $addressId) }}" id="addressForm">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label for="title" class="block text-sm font-medium text-main-graphit mb-2">Название адреса (необязательно)</label>
                    <input
                        id="title"
                        type="text"
                        name="title"
                        value="{{ old('title', $address['title'] ?? '') }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-main focus:border-transparent outline-none transition"
                        placeholder="Например: Дом, Работа"
                    >
                </div>

                <div class="mb-4">
                    <label for="address_search" class="block text-sm font-medium text-main-graphit mb-2">Поиск адреса</label>
                    <input
                        id="address_search"
                        type="text"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-main focus:border-transparent outline-none transition"
                        placeholder="Введите адрес в Алматы"
                        value="{{ old('address', $address['address'] ?? '') }}"
                    >
                    <p class="text-xs text-gray-500 mt-1">Введите адрес и нажмите Enter, или используйте поиск на карте</p>
                </div>

                <div class="mb-4">
                    <div id="map" style="width: 100%; height: 400px; border-radius: 8px; overflow: hidden;"></div>
                    <p class="text-xs text-gray-500 mt-1">Выберите точку на карте или используйте поиск</p>
                </div>

                <input type="hidden" id="address" name="address" value="{{ old('address', $address['address'] ?? '') }}" required>
                <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude', $address['latitude'] ?? '') }}" required>
                <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude', $address['longitude'] ?? '') }}" required>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="house" class="block text-sm font-medium text-main-graphit mb-2">Дом</label>
                        <input
                            id="house"
                            type="text"
                            name="house"
                            value="{{ old('house', $address['house'] ?? '') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-main focus:border-transparent outline-none transition"
                            placeholder="150"
                        >
                    </div>
                    <div>
                        <label for="apartment" class="block text-sm font-medium text-main-graphit mb-2">Квартира</label>
                        <input
                            id="apartment"
                            type="text"
                            name="apartment"
                            value="{{ old('apartment', $address['apartment'] ?? '') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-main focus:border-transparent outline-none transition"
                            placeholder="25"
                        >
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="entrance" class="block text-sm font-medium text-main-graphit mb-2">Подъезд</label>
                        <input
                            id="entrance"
                            type="text"
                            name="entrance"
                            value="{{ old('entrance', $address['entrance'] ?? '') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-main focus:border-transparent outline-none transition"
                            placeholder="1"
                        >
                    </div>
                    <div>
                        <label for="floor" class="block text-sm font-medium text-main-graphit mb-2">Этаж</label>
                        <input
                            id="floor"
                            type="text"
                            name="floor"
                            value="{{ old('floor', $address['floor'] ?? '') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-main focus:border-transparent outline-none transition"
                            placeholder="5"
                        >
                    </div>
                </div>

                <div class="mb-4">
                    <label for="notes" class="block text-sm font-medium text-main-graphit mb-2">Дополнительные заметки</label>
                    <textarea
                        id="notes"
                        name="notes"
                        rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-main focus:border-transparent outline-none transition"
                        placeholder="Например: код домофона, ориентир и т.д."
                    >{{ old('notes', $address['notes'] ?? '') }}</textarea>
                </div>

                <div class="mb-6">
                    <label class="flex items-center">
                        <input
                            type="checkbox"
                            name="is_default"
                            value="1"
                            {{ old('is_default', $address['is_default'] ?? false) ? 'checked' : '' }}
                            class="w-4 h-4 text-main border-gray-300 rounded focus:ring-main"
                        >
                        <span class="ml-2 text-sm text-gray-600">Сделать адресом по умолчанию</span>
                    </label>
                </div>

                <div class="flex gap-3">
                    <button
                        type="submit"
                        class="flex-1 bg-main text-white py-3 rounded-lg font-medium hover:bg-opacity-90 transition duration-200"
                    >
                        Сохранить изменения
                    </button>
                    <a
                        href="{{ route('profile.addresses.index') }}"
                        class="flex-1 bg-gray-200 text-gray-700 py-3 rounded-lg font-medium hover:bg-gray-300 transition duration-200 text-center"
                    >
                        Отмена
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://api-maps.yandex.ru/2.1/?apikey={{ $yandexApiKey }}&lang=ru_RU" type="text/javascript"></script>
<script src="{{ asset('js/yandex-map.js') }}"></script>
@endsection
