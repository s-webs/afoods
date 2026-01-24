<div class="">
    <a
        href="{{ route($routeName) }}"
        class="group relative flex flex-col items-center justify-center py-1.5 px-2 transition-all duration-300 ease-in-out {{ $isActive ? 'active' : '' }}"
    >
        <div class="relative mb-0.5">
            <div class="absolute inset-0 -m-2 rounded-full transition-all duration-300 opacity-0 {{ $isActive ? 'opacity-100' : 'group-hover:opacity-50' }}"></div>
            <i class="{{ $icon }} text-base relative z-10 transition-all duration-300 {{ $isActive ? 'text-main' : 'text-light group-hover:text-main' }}"></i>
            @if($isActive)
                <div class="absolute -top-1 -right-1 w-1 h-1 bg-main rounded-full animate-pulse shadow-lg shadow-main/50"></div>
            @endif
        </div>
        <span class="text-[9px] font-semibold transition-all duration-300 {{ $isActive ? 'text-main' : 'text-light group-hover:text-main' }}">
            {{ $name }}
        </span>
        @if($isActive)
            <div class="absolute bottom-0.5 left-1/2 transform -translate-x-1/2 w-10 h-0.5 bg-main rounded-full shadow-lg shadow-main/50"></div>
        @endif
    </a>
</div>
