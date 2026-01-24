@extends('layouts.auth')

@section('content')
<div class="container mx-auto px-2 xl:px-40">
    <div class="max-w-md mx-auto mt-10">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h2 class="text-2xl font-bold text-main-graphit mb-6 text-center">Подтвердите ваш email</h2>

            @if (session('status'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-sm text-green-600">
                        @if (session('status') === 'verification-link-sent')
                            Новая ссылка для подтверждения была отправлена на ваш email адрес.
                        @else
                            {{ session('status') }}
                        @endif
                    </p>
                </div>
            @endif

            <p class="text-sm text-gray-600 mb-6 text-center">
                Спасибо за регистрацию! Прежде чем начать, не могли бы вы подтвердить свой адрес электронной почты, перейдя по ссылке, которую мы только что отправили вам? Если вы не получили письмо, мы с радостью отправим вам другое.
            </p>

            <form method="POST" action="{{ route('verification.send') }}">
                @csrf

                <button
                    type="submit"
                    class="w-full bg-main text-white py-3 rounded-lg font-medium hover:bg-opacity-90 transition duration-200"
                >
                    Отправить письмо повторно
                </button>
            </form>

            <div class="mt-6 text-center">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-gray-600 hover:text-main transition">
                        Выйти
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
