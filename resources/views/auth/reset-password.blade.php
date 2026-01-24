@extends('layouts.auth')

@section('content')
<div class="container mx-auto px-2 xl:px-40">
    <div class="max-w-md mx-auto mt-10">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h2 class="text-2xl font-bold text-main-graphit mb-6 text-center">Сброс пароля</h2>

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <ul class="text-sm text-red-600">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-main-graphit mb-2">Email</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ $email }}"
                        required
                        readonly
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 outline-none"
                    >
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-main-graphit mb-2">Новый пароль</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autofocus
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-main focus:border-transparent outline-none transition"
                        placeholder="Минимум 8 символов"
                    >
                </div>

                <div class="mb-6">
                    <label for="password_confirmation" class="block text-sm font-medium text-main-graphit mb-2">Подтвердите пароль</label>
                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-main focus:border-transparent outline-none transition"
                        placeholder="Повторите пароль"
                    >
                </div>

                <button
                    type="submit"
                    class="w-full bg-main text-white py-3 rounded-lg font-medium hover:bg-opacity-90 transition duration-200"
                >
                    Сбросить пароль
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
