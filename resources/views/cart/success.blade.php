@extends('layouts.public')

@section('content')
<div class="container mx-auto px-2 xl:px-40">
    <div class="max-w-2xl mx-auto mt-10">
        <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
            <div class="mb-6">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="ph-bold ph-check text-4xl text-green"></i>
                </div>
                <h2 class="text-2xl font-bold text-main-graphit mb-2">Заказ успешно оформлен!</h2>
                <p class="text-gray-600">
                    Номер чека: <span class="font-semibold text-main">{{ $sale->receipt_number }}</span>
                </p>
            </div>

            <div class="bg-gray-50 rounded-lg p-6 mb-6 text-left">
                <h3 class="font-semibold text-main-graphit mb-4">Детали заказа:</h3>
                
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Дата:</span>
                        <span class="font-medium">{{ $sale->date->format('d.m.Y H:i') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Номер чека:</span>
                        <span class="font-medium">{{ $sale->receipt_number }}</span>
                    </div>
                    @if($sale->shopper && $sale->shopper->phone)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Телефон:</span>
                            <span class="font-medium">{{ $sale->shopper->phone }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between pt-2 border-t border-gray-200">
                        <span class="text-gray-600">Сумма заказа:</span>
                        <span class="font-bold text-main text-lg">{{ $sale->total_price }} ₸</span>
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <a 
                    href="{{ route('home') }}" 
                    class="block w-full bg-main text-white py-3 rounded-lg font-medium hover:bg-opacity-90 transition duration-200"
                >
                    Вернуться на главную
                </a>
                @auth
                    <a 
                        href="{{ route('profile.show') }}" 
                        class="block w-full bg-gray-200 text-gray-700 py-3 rounded-lg font-medium hover:bg-gray-300 transition duration-200"
                    >
                        Перейти в профиль
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
