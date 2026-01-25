@extends('layouts.public')

@section('content')
<div class="container mx-auto px-2 xl:px-40">
    <div class="max-w-2xl mx-auto mt-10">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-main-graphit mb-2">Поиск заказов</h2>
                <p class="text-sm text-gray-600">Введите телефон или email, указанные при оформлении заказа</p>
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

            <form method="POST" action="{{ route('orders.search') }}">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label for="phone" class="block text-sm font-medium text-main-graphit mb-2">Телефон</label>
                        <input 
                            id="phone" 
                            type="tel" 
                            name="phone" 
                            value="{{ old('phone') }}" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-main focus:border-transparent outline-none transition"
                            placeholder="+7 (700) 123-45-67"
                        >
                        <p class="text-xs text-gray-500 mt-1">Или укажите email</p>
                    </div>

                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-gray-500">или</span>
                        </div>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-main-graphit mb-2">Email</label>
                        <input 
                            id="email" 
                            type="email" 
                            name="email" 
                            value="{{ old('email') }}" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-main focus:border-transparent outline-none transition"
                            placeholder="example@mail.com"
                        >
                    </div>

                    <div>
                        <button 
                            type="submit" 
                            class="w-full bg-main text-white py-3 rounded-lg font-medium hover:bg-opacity-90 transition duration-200"
                        >
                            Найти заказы
                        </button>
                    </div>
                </div>
            </form>

            @auth
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <p class="text-sm text-gray-600 text-center">
                        Вы авторизованы как <strong>{{ auth()->user()->email }}</strong>
                    </p>
                    <a href="{{ route('profile.orders') }}" class="block text-center text-main hover:underline text-sm font-medium mt-2">
                        Перейти к моим заказам
                    </a>
                </div>
            @endauth
        </div>
    </div>
</div>
@endsection
