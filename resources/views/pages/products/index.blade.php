@extends('layouts.public')

@section('content')
    <div class="mt-7.5">
        <x-products-grid>
            @foreach($products as $product)
                <x-product-card :product="$product"/>
            @endforeach
        </x-products-grid>
    </div>
@endsection
