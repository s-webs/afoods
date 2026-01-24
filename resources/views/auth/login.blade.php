@extends('layouts.auth')

@section('content')
<div class="container mx-auto px-2 xl:px-40">
    <div class="max-w-md mx-auto mt-10">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h2 class="text-2xl font-bold text-main-graphit mb-6 text-center">Вход</h2>

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <ul class="text-sm text-red-600">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('status'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-sm text-green-600">{{ session('status') }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-main-graphit mb-2">Email</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-main focus:border-transparent outline-none transition"
                        placeholder="example@mail.com"
                    >
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-main-graphit mb-2">Пароль</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-main focus:border-transparent outline-none transition"
                        placeholder="Введите пароль"
                    >
                </div>

                <div class="mb-6 flex items-center justify-between">
                    <label class="flex items-center">
                        <input
                            type="checkbox"
                            name="remember"
                            class="w-4 h-4 text-main border-gray-300 rounded focus:ring-main"
                        >
                        <span class="ml-2 text-sm text-gray-600">Запомнить меня</span>
                    </label>
                    <a href="{{ route('password.request') }}" class="text-sm text-main hover:underline">
                        Забыли пароль?
                    </a>
                </div>

                <button
                    type="submit"
                    class="w-full bg-main text-white py-3 rounded-lg font-medium hover:bg-opacity-90 transition duration-200"
                >
                    Войти
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Нет аккаунта?
                    <a href="{{ route('register') }}" class="text-main font-medium hover:underline">Зарегистрироваться</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
