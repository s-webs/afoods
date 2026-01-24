@extends('layouts.public')

@section('content')
    <div class="container mx-auto px-4 xl:px-40 mt-7.5">
        <div>
            <ul class="flex items-center text-xs md:text-sm xl:text-xl text-gray-400 border-b border-b-gray-100 pb-0.75">
                <x-breadcrumbs-item icon="ph ph-house" name="Главная" :link="route('home')"/>
                <li class="mx-1.25">/</li>
                <x-breadcrumbs-item icon="ph ph-list-bullets" :name="$category->name"
                                    link="{{ route('products.index', $category->slug) }}"/>
            </ul>
        </div>
        <div class="mt-12.5">
            <x-products-grid>
                @foreach($products as $product)
                    <x-product-card :product="$product"/>
                @endforeach
            </x-products-grid>
        </div>
    </div>
@endsection
