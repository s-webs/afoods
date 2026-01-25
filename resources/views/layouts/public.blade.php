<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Almaty-Foods</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('js/cart.js') }}"></script>
    <style>
        /* Animation for quantity change */
        .quantity-animate {
            animation: quantityPulse 0.3s ease-in-out;
        }
        
        @keyframes quantityPulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.2);
                color: var(--main-color, #4B9DCB);
            }
            100% {
                transform: scale(1);
            }
        }
        
        /* Smooth transitions for cart controls */
        .cart-quantity-display {
            transition: all 0.2s ease-in-out;
        }
    </style>
</head>
<body>
<header class="pt-3.75">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between relative">
            <div class="flex-1"></div>
            <a href="{{ route('home') }}" class="absolute left-1/2 transform -translate-x-1/2">
                <img src="/assets/images/logo.png" alt="almaty-foods logo" class="w-45">
            </a>
            <div class="flex-1 flex justify-end">
                <a
                    href="{{ route('orders.search') }}"
                    class="flex items-center gap-2 text-main hover:text-opacity-80 transition text-sm md:text-base font-medium"
                    title="Поиск заказов"
                >
                    <i class="ph-bold ph-magnifying-glass text-xl"></i>
                    <span class="hidden md:inline">Поиск заказов</span>
                </a>
            </div>
        </div>
    </div>
</header>
@yield('content')
<div class="w-full fixed bottom-5 z-50">
    <div class="container mx-auto px-2">
        <div class="relative w-full">
            <!-- Decorative shadow layer -->
            <div class="absolute z-10 bg-linear-to-r from-main/30 via-main/20 to-main/30 h-full inset-x-0 -bottom-0.75 rounded-full blur-sm"></div>

            <!-- Main menu with glass effect -->
            <div class="bg-white/95 backdrop-blur-md rounded-full relative z-20 w-full flex items-center px-2 py-2 shadow-[0px_4px_24px_-2px_rgba(75,157,203,0.4)] border border-main/10">
                <div class="flex items-center flex-1 px-[20px] justify-between w-full">
                    <x-bottom-nav-item name="главная" icon="ph-bold ph-house"/>
                    <x-bottom-nav-item name="категории" route-name="categories.index" icon="ph-bold ph-list-bullets"/>
                    <x-bottom-nav-item name="корзина" route-name="cart.index" icon="ph-bold ph-basket"/>
                    @auth
                        <x-bottom-nav-item name="профиль" route-name="profile.show" icon="ph-bold ph-user"/>
                    @else
                        <x-bottom-nav-item name="профиль" route-name="login" icon="ph-bold ph-user"/>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>
<div class="w-full h-25"></div>
<script src="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.2"></script>
</body>
</html>
