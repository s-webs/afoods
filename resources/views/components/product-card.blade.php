<div class="p-3.75 w-full rounded-sm shadow-md">
    <div class="w-full aspect-square overflow-hidden rounded-sm">
        <img src="{{ $image }}" alt=""
             class="w-full h-full object-cover">
    </div>

    <div class="mt-1.75">
        <a href="{{ $link }}" class="text-sm font-semibold text-main-graphit">
            {{ $name }}
        </a>
    </div>

    <div class="mt-0.75">
        <span class="text-xs font-semibold text-light">
            {{ $price }} {{ $currency }} / {{ $units }}
        </span>
    </div>

    <div class="flex items-center justify-between mt-3.75">
        <div class="bg-main text-sm py-0.75 px-1.75 rounded-sm text-white font-semibold">
            <span>{{ $price }} {{ $currency }}</span>
        </div>
        <div class="cursor-pointer text-sm h-6.5 w-6.5 text-white text-center bg-main rounded-sm">
            <i class="ph ph-plus translate-y-1.5 block"></i>
        </div>
    </div>
</div>
