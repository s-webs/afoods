<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Almaty-Foods</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<header class="pt-3.75">
    <div class="">
        <a href="{{ route('home') }}" class="">
            <img src="/assets/images/logo.png" alt="almaty-foods logo" class="w-45 mx-auto">
        </a>
    </div>
</header>
@yield('content')
<div class="w-full fixed bottom-5">
    <div class="container mx-auto px-2">
        <div class="relative w-full shadow-[0px_1px_6px_-2px_rgba(0,0,0,0.8)] rounded-full">
            <div class="bg-white rounded-full relative z-20 w-full flex items-center px-5 py-2.5">
{{--                <div class="text-2xl text-main cursor-pointer"><i class="ph-bold ph-list"></i></div>--}}
{{--                <div class="bg-main w-0.5 h-7.5 ml-2.75 mr-4.5 rounded-full"></div>--}}
                <div class="flex items-center flex-1 justify-between">
                    <x-bottom-nav-item name="главная" icon="ph-bold ph-house"/>
                    <x-bottom-nav-item name="категории" route-name="categories.index" icon="ph-bold ph-list-bullets"/>
                    <x-bottom-nav-item name="корзина" icon="ph-bold ph-basket"/>
                    <x-bottom-nav-item name="профиль" icon="ph-bold ph-user"/>
                </div>
            </div>

            <div class="absolute z-10 bg-main h-full inset-x-0 -bottom-0.75 rounded-full"></div>
        </div>
    </div>
</div>
<div class="w-full h-25"></div>
<script src="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.2"></script>
</body>
</html>
