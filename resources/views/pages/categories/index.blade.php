@extends('layouts.public')

@section('content')
    <div class="container mx-auto px-2 mt-7.5">
        <x-categories-grid>
            @foreach($categories as $category)
                <div
                    class="bg-[linear-gradient(141deg,rgb(75_157_203)_0%,rgb(219_235_245)_100%)] w-28 h-28 overflow-hidden rounded-lg shadow-[0px_1px_6px_-2px_rgba(0,0,0,0.8)]">
                    <a href="{{ route('products.index', $category->slug) }}" class="block w-full h-full relative group p-1.75">
                        <span class="font-semibold text-[13px] text-white group-hover:text-light">{{ $category->name }}</span>
                        <img src="/{{ $category->image }}" alt="{{ $category->name }}"
                             class="absolute -right-[12px] -bottom-[16px] w-[134px] h-[96px] object-cover group-hover:scale-110 transition-all duration-300">
                    </a>
                </div>
            @endforeach
        </x-categories-grid>
    </div>
@endsection
