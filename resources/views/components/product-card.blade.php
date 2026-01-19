<div class="p-3.75 w-full h-full rounded-sm shadow-md flex flex-col">
    @php
        $firstImage = data_get($product, 'images.0');
    @endphp

    <div class="w-full aspect-square overflow-hidden rounded-sm">
        <img
            src="{{ $firstImage ? asset($firstImage) : asset('images/placeholder.png') }}"
            alt="{{ $product->name }}"
            class="w-full h-full object-cover"
        >
    </div>

    <div class="mt-1.75">
        <a href="{{ route('products.show', $product->slug) }}" class="text-md font-semibold text-main-graphit">
            {{ $product->name }}
        </a>
    </div>

    <div class="mt-0.75">
        <span class="text-xs font-semibold text-light">
            {{ $product->price_amount }} {{ $currency }} / {{ $units }}
        </span>
    </div>

    <div class="flex items-center justify-between mt-auto pt-3.75">
        <div class="bg-main text-sm py-0.75 px-1.75 rounded-sm text-white font-semibold">
            <span>{{ $product->price_amount }} {{ $currency }}</span>
        </div>
        <div class="cursor-pointer text-sm h-6.5 w-6.5 text-white text-center bg-main rounded-sm">
            <i class="ph ph-plus translate-y-1.5 block"></i>
        </div>
    </div>
</div>
