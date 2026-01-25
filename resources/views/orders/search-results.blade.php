@extends('layouts.public')

@section('content')
<div class="container mx-auto px-2 xl:px-40">
    <div class="max-w-4xl mx-auto mt-10">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-main-graphit">Результаты поиска</h2>
                    @if($searchPhone || $searchEmail)
                        <p class="text-sm text-gray-600 mt-1">
                            @if($searchPhone) Телефон: <strong>{{ $searchPhone }}</strong>@endif
                            @if($searchPhone && $searchEmail) • @endif
                            @if($searchEmail) Email: <strong>{{ $searchEmail }}</strong>@endif
                        </p>
                    @endif
                </div>
                <a href="{{ route('orders.search') }}" class="text-gray-600 hover:text-main">
                    <i class="ph-bold ph-x text-2xl"></i>
                </a>
            </div>

            @if($sales->count() > 0)
                <div class="space-y-4">
                    @foreach($sales as $sale)
                        <a 
                            href="{{ route('orders.show', ['saleId' => $sale->id, 'phone' => $searchPhone, 'email' => $searchEmail]) }}" 
                            class="block bg-gray-50 rounded-lg p-5 hover:bg-gray-100 transition border border-gray-200"
                        >
                            <div class="flex items-center justify-between flex-wrap gap-4">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-3 mb-2">
                                        <p class="text-lg font-semibold text-main-graphit">Заказ #{{ $sale->receipt_number }}</p>
                                        <span class="text-xs bg-main text-white px-2 py-1 rounded">{{ $sale->date->format('d.m.Y') }}</span>
                                    </div>
                                    <p class="text-sm text-gray-600">
                                        {{ $sale->date->format('H:i') }} • {{ count($sale->items) }} {{ count($sale->items) === 1 ? 'товар' : 'товаров' }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xl font-bold text-main">{{ $sale->total_price }} ₸</p>
                                    <i class="ph-bold ph-arrow-right text-main mt-1 block"></i>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $sales->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="ph-bold ph-magnifying-glass text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-600 mb-4 text-lg">Заказы не найдены</p>
                    <p class="text-sm text-gray-500 mb-4">Проверьте правильность введенных данных</p>
                    <a href="{{ route('orders.search') }}" class="inline-block bg-main text-white px-6 py-3 rounded-lg font-medium hover:bg-opacity-90 transition">
                        Попробовать снова
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
