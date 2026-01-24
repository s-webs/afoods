@extends('layouts.public')

@section('content')
<div class="container mx-auto px-2 xl:px-40">
    <div class="max-w-4xl mx-auto mt-10">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-main-graphit">Мои адреса</h2>
                <a href="{{ route('profile.addresses.create') }}" class="bg-main text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-opacity-90 transition">
                    <i class="ph-bold ph-plus"></i> Добавить адрес
                </a>
            </div>

            @if (session('status'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-sm text-green-600">{{ session('status') }}</p>
                </div>
            @endif

            @if(count($addresses) > 0)
                <div class="space-y-4">
                    @foreach($addresses as $address)
                        <div class="border border-gray-200 rounded-lg p-4 {{ ($address['is_default'] ?? false) ? 'border-main bg-halftone/30' : '' }}">
                            <div class="flex items-start flex-wrap justify-between">
                                <div class="flex-1">
                                    @if($address['title'] ?? null)
                                        <h3 class="font-semibold text-main-graphit mb-2">{{ $address['title'] }}</h3>
                                    @endif
                                    <p class="text-sm text-gray-700 mb-1">{{ $address['address'] ?? '' }}</p>
                                    @if($address['house'] ?? null)
                                        <p class="text-xs text-gray-600">
                                            Дом: {{ $address['house'] }}
                                            @if($address['apartment'] ?? null), кв. {{ $address['apartment'] }}@endif
                                            @if($address['entrance'] ?? null), подъезд {{ $address['entrance'] }}@endif
                                            @if($address['floor'] ?? null), этаж {{ $address['floor'] }}@endif
                                        </p>
                                    @endif
                                    @if($address['notes'] ?? null)
                                        <p class="text-xs text-gray-500 mt-2 italic">{{ $address['notes'] }}</p>
                                    @endif
                                    @if($address['is_default'] ?? false)
                                        <span class="inline-block mt-2 text-xs bg-main text-white px-2 py-1 rounded">По умолчанию</span>
                                    @endif
                                </div>
                                <div class="flex items-center ml-4 mt-7.5 sm:mt-0">
                                    @if(!($address['is_default'] ?? false))
                                        <form method="POST" action="{{ route('profile.addresses.set-default', $address['id']) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-xs text-main hover:underline" title="Сделать адресом по умолчанию">
                                                По умолчанию
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('profile.addresses.edit', $address['id']) }}" class="text-xs text-main hover:underline mx-7.5">
                                        <span>Редактировать</span><i class="ph ph-pencil-line ml-1.25 inline-block translate-y-0.5"></i>
                                    </a>
                                    <form method="POST" action="{{ route('profile.addresses.destroy', $address['id']) }}" class="inline" onsubmit="return confirm('Вы уверены, что хотите удалить этот адрес?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-600 hover:underline">
                                            <span>Удалить</span><i class="ph ph-trash ml-1.25 inline-block translate-y-0.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <i class="ph-bold ph-map-pin text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-600 mb-4">У вас пока нет сохраненных адресов</p>
                    <a href="{{ route('profile.addresses.create') }}" class="inline-block bg-main text-white px-6 py-3 rounded-lg font-medium hover:bg-opacity-90 transition">
                        Добавить первый адрес
                    </a>
                </div>
            @endif

            <div class="mt-6 pt-6 border-t border-gray-200">
                <a href="{{ route('profile.show') }}" class="text-gray-600 hover:text-main text-sm">
                    ← Вернуться в профиль
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
