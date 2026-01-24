@extends('layouts.public')

@section('content')
    <div class="container mx-auto px-4 mt-7.5">
        <x-categories-grid>
            @foreach($categories as $category)
                <div class="bg-[linear-gradient(141deg,rgb(75_157_203)_0%,rgb(219_235_245)_100%)]
            w-full aspect-square overflow-hidden rounded-lg shadow-[0px_1px_6px_-2px_rgba(0,0,0,0.8)]">
                    <a href="{{ route('products.index', $category->slug) }}"
                       class="block w-full h-full relative group p-1.75 lg:p-[20px]">
                        <span
                            class="font-semibold text-[13px] lg:text-2xl text-white">{{ $category->name }}</span>
                        <img src="/{{ $category->image }}" alt="{{ $category->name }}"
                             class="absolute -right-3 -bottom-4 w-26 h-26 md:w-44 md:h-44 lg:w-50 lg:h-50 object-contain group-hover:scale-110 transition-all duration-300">
                    </a>
                </div>
            @endforeach
        </x-categories-grid>
    </div>
@endsection
