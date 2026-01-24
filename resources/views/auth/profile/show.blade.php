@extends('layouts.public')

@section('content')
<div class="container mx-auto px-2 xl:px-40">
    <div class="max-w-2xl mx-auto mt-10">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-main-graphit">Мой профиль</h2>
                <a href="{{ route('profile.edit') }}" class="text-main hover:underline text-sm font-medium">
                    Редактировать
                </a>
            </div>

            @if (session('status'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-sm text-green-600">{{ session('status') }}</p>
                </div>
            @endif

            @if (session('verified'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-sm text-green-600">Email успешно подтвержден!</p>
                </div>
            @endif

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Имя</label>
                    <p class="text-lg text-main-graphit">{{ $user->name }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Email</label>
                    <p class="text-lg text-main-graphit">{{ $user->email }}</p>
                    @if (!$user->hasVerifiedEmail())
                        <p class="text-sm text-red-600 mt-1">
                            Email не подтвержден. 
                            <a href="{{ route('verification.notice') }}" class="underline">Подтвердить</a>
                        </p>
                    @else
                        <p class="text-sm text-green-600 mt-1">
                            <i class="ph-bold ph-check-circle"></i> Email подтвержден
                        </p>
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Дата регистрации</label>
                    <p class="text-lg text-main-graphit">{{ $user->created_at->format('d.m.Y') }}</p>
                </div>

                <div class="pt-4 border-t border-gray-200">
                    <a href="{{ route('profile.edit-password') }}" class="text-main hover:underline text-sm font-medium">
                        Изменить пароль
                    </a>
                </div>

                <div class="pt-4 border-t border-gray-200">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-red-600 hover:underline text-sm font-medium">
                            Выйти из аккаунта
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
