@extends('layouts.public')

@section('content')
<div class="container mx-auto px-2 xl:px-40">
    <div class="max-w-md mx-auto mt-10">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-main-graphit">Изменение пароля</h2>
                <a href="{{ route('profile.show') }}" class="text-gray-600 hover:text-main">
                    <i class="ph-bold ph-x text-2xl"></i>
                </a>
            </div>

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <ul class="text-sm text-red-600">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('profile.password.update') }}">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label for="current_password" class="block text-sm font-medium text-main-graphit mb-2">Текущий пароль</label>
                    <input 
                        id="current_password" 
                        type="password" 
                        name="current_password" 
                        required 
                        autofocus
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-main focus:border-transparent outline-none transition"
                        placeholder="Введите текущий пароль"
                    >
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-main-graphit mb-2">Новый пароль</label>
                    <input 
                        id="password" 
                        type="password" 
                        name="password" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-main focus:border-transparent outline-none transition"
                        placeholder="Минимум 8 символов"
                    >
                </div>

                <div class="mb-6">
                    <label for="password_confirmation" class="block text-sm font-medium text-main-graphit mb-2">Подтвердите новый пароль</label>
                    <input 
                        id="password_confirmation" 
                        type="password" 
                        name="password_confirmation" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-main focus:border-transparent outline-none transition"
                        placeholder="Повторите новый пароль"
                    >
                </div>

                <div class="flex gap-3">
                    <button 
                        type="submit" 
                        class="flex-1 bg-main text-white py-3 rounded-lg font-medium hover:bg-opacity-90 transition duration-200"
                    >
                        Изменить пароль
                    </button>
                    <a 
                        href="{{ route('profile.show') }}" 
                        class="flex-1 bg-gray-200 text-gray-700 py-3 rounded-lg font-medium hover:bg-gray-300 transition duration-200 text-center"
                    >
                        Отмена
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
