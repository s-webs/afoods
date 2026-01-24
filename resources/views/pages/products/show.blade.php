@extends('layouts.public')
@section('content')
    <div class="container mx-auto px-4 xl:px-40 mt-7.5">
        <div>
            <ul class="flex items-center text-xs md:text-sm xl:text-xl text-gray-400 border-b border-b-gray-100 pb-0.75">
                <x-breadcrumbs-item icon="ph ph-house" name="Главная" :link="route('home')"/>
                <li class="mx-1.25">/</li>
                <x-breadcrumbs-item icon="ph ph-list-bullets" :name="$product->category->name"
                                    link="{{ route('products.index', $product->category->slug) }}"/>
                <li class="mx-1.25">/</li>
                <x-breadcrumbs-item icon="ph ph-cheese" :name="$product->name"
                                    link="{{ route('products.show', $product) }}"/>
            </ul>
        </div>

        <div class="flex items-start justify-between mt-12.5">
            <div>
                <div class="flex items-center justify-between">
                    <div>
                        <x-product-slider-button icon="ph-bold ph-caret-left"/>
                    </div>
                    <div class="relative mx-2.5">
                        <img src="/{{ $product->images[0] }}" alt=""
                             class="rounded-lg w-57.5 h-57.5 md:w-60 md:h-60 lg:w-100 lg:h-100 object-cover shadow-sm border border-gray-100">
                        <div class="absolute top-2.5 left-2.5 bg-main-red px-3 py-0.75 rounded-lg">
                            <div class="font-semibold text-white text-xs md:text-sm">-13%</div>
                        </div>
                    </div>
                    <div>
                        <x-product-slider-button icon="ph-bold ph-caret-right"/>
                    </div>
                </div>
                <div class="mt-7.5">
                    <h1 class="text-main-graphit font-semibold text-xl md:text-2xl text-center md:hidden">{{ $product->name }}</h1>
                </div>
                <div class="flex items-center justify-between flex-wrap mt-5 md:hidden">
                    <div class="bg-green text-white py-3.5 px-12 text-sm md:text-lg rounded-lg shadow-md">
                        <span class="font-semibold">{{ $product->price_amount }} ₸</span>
                    </div>
                    <button
                        type="button"
                        class="add-to-cart bg-main-red text-white py-3.5 px-5 text-sm md:text-lg rounded-lg shadow-md cursor-pointer hover:bg-opacity-90 transition"
                        data-product-id="{{ $product->id }}"
                    >
                        <span class="font-semibold">Добавить в корзину</span>
                        <i class="ph-bold ph-shopping-cart-simple"></i>
                    </button>
                </div>
                <div class="mt-5 text-main-graphit">
                    {!! $product->description !!}
                </div>
            </div>
            <div
                class="shrink-0 w-80 lg:w-100 xl:w-125 hidden md:block ml-7.5 shadow-lg border rounded-lg border-gray-100 p-5">
                <div>
                    <h1 class="text-main-graphit font-semibold text-xl lg:text-3xl">{{ $product->name }}</h1>
                </div>
                <div class="flex items-center justify-between flex-wrap">
                    <div class="text-main-graphit w-full text-4xl mt-7.5">
                        <span class="font-semibold">{{ $product->price_amount }} ₸</span>
                        <span class="font-semibold text-lg line-through">{{ $product->price_amount }} ₸</span>
                    </div>
                    <div class="text-main-graphit w-full text-xl mt-7.5">
                        <span class="text-green font-semibold">В наличии:</span><span class="ml-2.5">232</span>
                    </div>
                    <button
                        type="button"
                        class="add-to-cart bg-main-red text-white w-full py-3.5 mt-7.5 px-5 text-md lg:text-xl rounded-lg shadow-md cursor-pointer hover:bg-opacity-90 transition"
                        data-product-id="{{ $product->id }}"
                    >
                        <span class="font-semibold">Добавить в корзину</span>
                        <i class="ph-bold ph-shopping-cart-simple inline-block translate-y-0.5"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="mt-12.5">
            <div>
                <x-heading heading="Похожие товары"/>
            </div>
            <div class="mt-5">
                <x-products-grid>
                    @foreach($products as $item)
                        <x-product-card :product="$item"/>
                    @endforeach
                </x-products-grid>
            </div>
        </div>
    </div>
@endsection
