@extends('layouts.public')

@section('content')
<div class="container mx-auto px-2 xl:px-40">
    <div class="max-w-md mx-auto mt-10">
        <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
            <div class="mb-6">
                <div class="w-20 h-20 bg-halftone rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="ph-bold ph-user text-4xl text-main"></i>
                </div>
                <h2 class="text-2xl font-bold text-main-graphit mb-2">Вы не авторизованы</h2>
                <p class="text-gray-600 text-sm">
                    Для просмотра профиля необходимо войти в систему или зарегистрироваться
                </p>
            </div>

            <div class="space-y-3">
                <a 
                    href="{{ route('login') }}" 
                    class="block w-full bg-main text-white py-3 rounded-lg font-medium hover:bg-opacity-90 transition duration-200"
                >
                    Войти
                </a>
                <a 
                    href="{{ route('register') }}" 
                    class="block w-full bg-halftone text-main py-3 rounded-lg font-medium hover:bg-opacity-80 transition duration-200"
                >
                    Зарегистрироваться
                </a>
            </div>

            <div class="mt-6 pt-6 border-t border-gray-200">
                <p class="text-xs text-gray-500">
                    После регистрации вы сможете управлять своим профилем, отслеживать заказы и многое другое
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
