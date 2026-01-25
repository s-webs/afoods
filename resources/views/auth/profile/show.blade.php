@extends('layouts.public')

@section('content')
<div class="container mx-auto px-2 xl:px-40">
    <div class="max-w-2xl mx-auto mt-10">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-main-graphit">Мой профиль</h2>
                <a href="{{ route('profile.edit') }}" class="text-main hover:underline text-sm font-medium">
                    Редактировать
                </a>
            </div>

            @if (session('status'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-sm text-green-600">{{ session('status') }}</p>
                </div>
            @endif

            @if (session('verified'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-sm text-green-600">Email успешно подтвержден!</p>
                </div>
            @endif

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Имя</label>
                    <p class="text-lg text-main-graphit">{{ $user->name }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Email</label>
                    <p class="text-lg text-main-graphit">{{ $user->email }}</p>
                    @if (!$user->hasVerifiedEmail())
                        <p class="text-sm text-red-600 mt-1">
                            Email не подтвержден. 
                            <a href="{{ route('verification.notice') }}" class="underline">Подтвердить</a>
                        </p>
                    @else
                        <p class="text-sm text-green-600 mt-1">
                            <i class="ph-bold ph-check-circle"></i> Email подтвержден
                        </p>
                    @endif
                </div>

                @php
                    $shopper = $user->getOrCreateShopper();
                    $addresses = $shopper->addresses ?? [];
                    $defaultAddress = $shopper->getDefaultAddress();
                @endphp

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Телефон</label>
                    @if($shopper->phone)
                        <p class="text-lg text-main-graphit">{{ $shopper->phone }}</p>
                    @else
                        <p class="text-sm text-gray-500">Не указан</p>
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Дата регистрации</label>
                    <p class="text-lg text-main-graphit">{{ $user->created_at->format('d.m.Y') }}</p>
                </div>

                <div class="pt-4 border-t border-gray-200">
                    <div class="flex items-center justify-between mb-3">
                        <label class="block text-sm font-medium text-gray-500">Адреса доставки</label>
                        <a href="{{ route('profile.addresses.index') }}" class="text-main hover:underline text-sm font-medium">
                            Управление адресами
                        </a>
                    </div>
                    @if($defaultAddress)
                        <div class="bg-halftone rounded-lg p-3 mt-2">
                            <p class="text-sm font-medium text-main-graphit">{{ $defaultAddress['address'] ?? '' }}</p>
                            @if($defaultAddress['house'] ?? null)
                                <p class="text-xs text-gray-600 mt-1">
                                    Дом: {{ $defaultAddress['house'] }}
                                    @if($defaultAddress['apartment'] ?? null), кв. {{ $defaultAddress['apartment'] }}@endif
                                </p>
                            @endif
                            <span class="inline-block mt-2 text-xs bg-main text-white px-2 py-1 rounded">По умолчанию</span>
                        </div>
                    @elseif(count($addresses) > 0)
                        <div class="bg-gray-50 rounded-lg p-3 mt-2">
                            <p class="text-sm text-gray-600">У вас {{ count($addresses) }} {{ count($addresses) === 1 ? 'адрес' : 'адресов' }}</p>
                            <a href="{{ route('profile.addresses.index') }}" class="text-main hover:underline text-xs mt-1 inline-block">
                                Выбрать адрес по умолчанию
                            </a>
                        </div>
                    @else
                        <div class="bg-gray-50 rounded-lg p-3 mt-2">
                            <p class="text-sm text-gray-600 mb-2">Адреса не добавлены</p>
                            <a href="{{ route('profile.addresses.create') }}" class="text-main hover:underline text-sm font-medium">
                                Добавить адрес
                            </a>
                        </div>
                    @endif
                </div>

                <div class="pt-4 border-t border-gray-200">
                    <div class="flex items-center justify-between mb-3">
                        <label class="block text-sm font-medium text-gray-500">История заказов</label>
                        @if(count($sales) > 0)
                            <a href="{{ route('profile.orders') }}" class="text-main hover:underline text-sm font-medium">
                                Все заказы
                            </a>
                        @endif
                    </div>
                    @if(count($sales) > 0)
                        <div class="space-y-3 mt-3">
                            @foreach($sales->take(5) as $sale)
                                <a href="{{ route('profile.order.show', $sale->id) }}" class="block bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-semibold text-main-graphit">Заказ #{{ $sale->receipt_number }}</p>
                                            <p class="text-xs text-gray-600 mt-1">{{ $sale->date->format('d.m.Y H:i') }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-bold text-main">{{ $sale->total_price }} ₸</p>
                                            <p class="text-xs text-gray-600 mt-1">{{ count($sale->items) }} {{ count($sale->items) === 1 ? 'товар' : 'товаров' }}</p>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                            @if(count($sales) > 5)
                                <a href="{{ route('profile.orders') }}" class="block text-center text-sm text-main hover:underline mt-2">
                                    Показать все заказы ({{ count($sales) }})
                                </a>
                            @endif
                        </div>
                    @else
                        <div class="bg-gray-50 rounded-lg p-4 mt-2">
                            <p class="text-sm text-gray-600">У вас пока нет заказов</p>
                            <a href="{{ route('home') }}" class="text-main hover:underline text-sm font-medium mt-2 inline-block">
                                Начать покупки
                            </a>
                        </div>
                    @endif
                </div>

                <div class="pt-4 border-t border-gray-200">
                    <a href="{{ route('profile.edit-password') }}" class="text-main hover:underline text-sm font-medium">
                        Изменить пароль
                    </a>
                </div>

                <div class="pt-4 border-t border-gray-200">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-red-600 hover:underline text-sm font-medium">
                            Выйти из аккаунта
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
