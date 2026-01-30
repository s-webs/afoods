@extends('layouts.public')

@section('content')
<div class="container mx-auto px-2 xl:px-40">
    <div class="max-w-4xl mx-auto mt-10">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-main-graphit">Корзина</h2>
                @if(empty($items))
                    <span class="text-sm text-gray-500">Пусто</span>
                @else
                    <span class="text-sm text-gray-500">{{ $totalCount }} {{ $totalCount === 1 ? 'товар' : 'товаров' }}</span>
                @endif
            </div>

            @if (session('status'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-sm text-green-600">{{ session('status') }}</p>
                </div>
            @endif

            @if(count($items) > 0)
                <div class="space-y-4 mb-6">
                    @foreach($items as $item)
                        @php
                            $product = $item['product'];
                            $firstImage = data_get($product->images, '0');
                        @endphp
                        <div class="border border-gray-200 rounded-lg p-4 flex items-center flex-wrap gap-4">
                            <div class="w-20 h-20 shrink-0 rounded-lg overflow-hidden">
                                <img
                                    src="{{ $firstImage ? asset($firstImage) : asset('assets/images/placeholder.png') }}"
                                    alt="{{ $product->name }}"
                                    class="w-full h-full object-cover"
                                >
                            </div>

                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-main-graphit mb-1">
                                    <a href="{{ route('products.show', $product->slug) }}" class="hover:text-main">
                                        {{ $product->name }}
                                    </a>
                                </h3>
                                <p class="text-sm text-gray-600">
                                    {{ $item['price'] }} ₸ за {{ $product->unit }}
                                </p>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="flex items-center border border-gray-300 rounded-lg">
                                    <form action="{{ route('cart.update', $product->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="quantity" value="{{ max(1, $item['quantity'] - 1) }}">
                                        <button
                                            type="submit"
                                            class="px-3 py-1 text-main hover:bg-halftone transition"
                                            @if($item['quantity'] <= 1) disabled @endif
                                        >
                                            <i class="ph-bold ph-minus"></i>
                                        </button>
                                    </form>
                                    <span class="px-4 py-1 min-w-[3rem] text-center">
                                        {{ $item['quantity'] }}
                                    </span>
                                    <form action="{{ route('cart.update', $product->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="quantity" value="{{ $item['quantity'] + 1 }}">
                                        <button
                                            type="submit"
                                            class="px-3 py-1 text-main hover:bg-halftone transition"
                                        >
                                            <i class="ph-bold ph-plus"></i>
                                        </button>
                                    </form>
                                </div>

                                <div class="text-right min-w-[100px]">
                                    <p class="font-semibold text-main-graphit">
                                        {{ $item['total'] }} ₸
                                    </p>
                                </div>

                                <form action="{{ route('cart.destroy', $product->id) }}" method="POST" class="inline" onsubmit="return confirm('Удалить товар из корзины?')">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="text-red-600 hover:text-red-700 p-2"
                                        title="Удалить"
                                    >
                                        <i class="ph-bold ph-trash text-xl"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-gray-200 pt-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-lg font-semibold text-main-graphit">Итого:</span>
                        <span class="text-2xl font-bold text-main cart-total">{{ $totalPrice }} ₸</span>
                    </div>

                    <div class="flex gap-3">
                        <a
                            href="{{ route('home') }}"
                            class="flex-1 bg-gray-200 text-gray-700 py-3 rounded-lg font-medium hover:bg-gray-300 transition duration-200 text-center"
                        >
                            Продолжить покупки
                        </a>
                        <a
                            href="{{ route('cart.checkout') }}"
                            class="flex-1 bg-main text-white py-3 rounded-lg font-medium hover:bg-opacity-90 transition duration-200 text-center"
                        >
                            Оформить заказ
                        </a>
                    </div>
                </div>
            @else
                <div class="text-center py-12">
                    <i class="ph-bold ph-shopping-cart text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-600 mb-4 text-lg">Ваша корзина пуста</p>
                    <a href="{{ route('home') }}" class="inline-block bg-main text-white px-6 py-3 rounded-lg font-medium hover:bg-opacity-90 transition">
                        Начать покупки
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
