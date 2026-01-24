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
<script src="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.2"></script>
</body>
</html>
