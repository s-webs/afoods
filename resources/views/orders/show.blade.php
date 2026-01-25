@extends('layouts.public')

@section('content')
<div class="container mx-auto px-2 xl:px-40">
    <div class="max-w-4xl mx-auto mt-10">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-main-graphit">Заказ #{{ $sale->receipt_number }}</h2>
                    <p class="text-sm text-gray-600 mt-1">{{ $sale->date->format('d.m.Y H:i') }}</p>
                </div>
                <a href="{{ route('orders.search') }}" class="text-gray-600 hover:text-main">
                    <i class="ph-bold ph-x text-2xl"></i>
                </a>
            </div>

            <div class="space-y-6">
                <!-- Order Items -->
                <div>
                    <h3 class="text-lg font-semibold text-main-graphit mb-4">Товары</h3>
                    <div class="space-y-3">
                        @foreach($sale->items as $item)
                            @php
                                $product = is_array($item['product']) ? null : ($item['product'] ?? null);
                                $productName = $product ? $product->name : ($item['product']['name'] ?? 'Товар');
                                $firstImage = null;
                                if ($product && isset($product->images)) {
                                    $firstImage = data_get($product->images, '0');
                                } elseif (isset($item['product']['images'])) {
                                    $firstImage = data_get($item['product']['images'], '0');
                                }
                            @endphp
                            <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
                                @if($firstImage)
                                    <div class="w-16 h-16 flex-shrink-0 rounded-lg overflow-hidden">
                                        <img 
                                            src="{{ asset($firstImage) }}" 
                                            alt="{{ $productName }}"
                                            class="w-full h-full object-cover"
                                        >
                                    </div>
                                @else
                                    <div class="w-16 h-16 flex-shrink-0 rounded-lg overflow-hidden bg-gray-200 flex items-center justify-center">
                                        <i class="ph-bold ph-image text-gray-400 text-2xl"></i>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-semibold text-main-graphit">
                                        {{ $productName }}
                                    </h4>
                                    <p class="text-sm text-gray-600">
                                        Количество: {{ $item['quantity'] ?? 1 }} × {{ $item['price'] ?? 0 }} ₸
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-main">{{ $item['total'] ?? 0 }} ₸</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="border-t border-gray-200 pt-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-lg font-semibold text-main-graphit">Итого:</span>
                        <span class="text-2xl font-bold text-main">{{ $sale->total_price }} ₸</span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="border-t border-gray-200 pt-6">
                    <a 
                        href="{{ route('orders.search') }}" 
                        class="inline-block bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-medium hover:bg-gray-300 transition"
                    >
                        ← Вернуться к поиску
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
