@extends('layouts.public')

@section('content')
    <div class="container mx-auto px-2 xl:px-40">
        <div class="mt-7.5">
            <x-search-field/>
        </div>
        <div class="mt-7.5">
            <x-banner-slider :slides="$slides"/>
        </div>

        <div class="mt-7.5">
            <x-heading heading="Новое поступление"/>
            <div class="mt-3.75">
                <x-products-grid>
                    @foreach($products as $product)
                        <x-product-card :product="$product"/>
                    @endforeach
                </x-products-grid>
            </div>
        </div>
        <div class="mt-7.5">
            <x-heading heading="Популярное"/>
            <div class="mt-3.75">
                <x-products-grid>
                    @foreach($products as $product)
                        <x-product-card :product="$product"/>
                    @endforeach
                </x-products-grid>
            </div>
        </div>
        <div class="mt-7.5">
            <x-heading heading="Предыдущий заказ"/>
        </div>
    </div>
@endsection
