@extends('layouts.public')

@section('content')
    <div class="container mx-auto px-2">
        <div class="mt-7.5">
            <x-search-field/>
        </div>
        <div class="mt-7.5">
            slider
        </div>
        <div class="mt-7.5">
            <x-heading heading="Новое поступление"/>
            <div class="mt-3.75">
                <x-products-grid>
                    <x-product-card
                        image="/assets/images/test-image.png"
                        name="Семга Kingfisher стейк замороженный"
                        price="10546"
                    />
                    <x-product-card
                        image="/assets/images/test-image.png"
                        name="Семга Kingfisher стейк замороженный"
                        price="10546"
                    />
                    <x-product-card
                        image="/assets/images/test-image.png"
                        name="Семга Kingfisher стейк замороженный"
                        price="10546"
                    />
                    <x-product-card
                        image="/assets/images/test-image.png"
                        name="Семга Kingfisher стейк замороженный"
                        price="10546"
                    />
                </x-products-grid>
            </div>
        </div>
        <div class="mt-7.5">
            <x-heading heading="Популярное"/>
            <x-products-grid>
                <x-product-card
                    image="/assets/images/test-image.png"
                    name="Семга Kingfisher стейк замороженный"
                    price="10546"
                />
                <x-product-card
                    image="/assets/images/test-image.png"
                    name="Семга Kingfisher стейк замороженный"
                    price="10546"
                />
                <x-product-card
                    image="/assets/images/test-image.png"
                    name="Семга Kingfisher стейк замороженный"
                    price="10546"
                />
                <x-product-card
                    image="/assets/images/test-image.png"
                    name="Семга Kingfisher стейк замороженный"
                    price="10546"
                />
            </x-products-grid>
        </div>
        <div class="mt-7.5">
            <x-heading heading="Предыдущий заказ"/>
        </div>
    </div>
@endsection
