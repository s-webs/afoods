<div class="p-3.75 w-full h-full rounded-sm shadow-md flex flex-col border border-gray-100">
    @php
        $firstImage = data_get($product, 'images.0');
    @endphp

    <div class="w-full aspect-square overflow-hidden rounded-sm">
        <img
            src="{{ $firstImage ? asset($firstImage) : asset('assets/images/placeholder.png') }}"
            alt="{{ $product->name }}"
            class="w-full h-full object-cover"
        >
    </div>

    <div class="mt-1.75">
        <a href="{{ route('products.show', $product->slug) }}" class="text-sm md:text-md font-semibold text-main-graphit">
            {{ \Illuminate\Support\Str::limit($product->name, 38, '...') }}
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
        @if($cartQuantity > 0)
            <div class="flex items-center border border-main rounded-sm">
                <button
                    type="button"
                    class="cart-decrease text-sm h-6.5 w-6.5 text-main hover:bg-main hover:text-white transition flex items-center justify-center"
                    data-product-id="{{ $product->id }}"
                    title="Уменьшить количество"
                >
                    <i class="ph-bold ph-minus text-xs"></i>
                </button>
                <span class="cart-quantity-display px-2 text-sm font-semibold text-main min-w-[1.5rem] text-center" data-product-id="{{ $product->id }}">
                    {{ $cartQuantity }}
                </span>
                <button
                    type="button"
                    class="cart-increase text-sm h-6.5 w-6.5 text-main hover:bg-main hover:text-white transition flex items-center justify-center"
                    data-product-id="{{ $product->id }}"
                    title="Увеличить количество"
                >
                    <i class="ph-bold ph-plus text-xs"></i>
                </button>
            </div>
        @else
            <button
                type="button"
                class="add-to-cart cursor-pointer text-sm h-6.5 w-6.5 text-white text-center bg-main rounded-sm hover:bg-opacity-90 transition"
                data-product-id="{{ $product->id }}"
                title="Добавить в корзину"
            >
                <i class="ph ph-plus block"></i>
            </button>
        @endif
    </div>
</div>
