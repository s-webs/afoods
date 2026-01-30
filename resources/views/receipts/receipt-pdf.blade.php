<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Чек № {{ $sale->receipt_number }}</title>
    <style>
        @page {
            margin: 5mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9pt;
            line-height: 1.3;
        }

        .receipt {
            width: 100%;
            padding: 5mm;
        }

        .header {
            text-align: center;
            margin-bottom: 5mm;
            padding-bottom: 3mm;
            border-bottom: 2px solid #000;
        }

        .header h1 {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 2mm;
        }

        .header p {
            font-size: 8pt;
            color: #666;
        }

        .info {
            margin-bottom: 5mm;
            font-size: 8pt;
        }

        .info-row {
            margin-bottom: 1mm;
            overflow: hidden;
        }

        .info-row::after {
            content: '';
            display: table;
            clear: both;
        }

        .info-label {
            float: left;
            font-weight: bold;
            width: 40%;
        }

        .info-value {
            float: right;
            text-align: right;
            width: 60%;
        }

        .divider {
            border-top: 1px dashed #999;
            margin: 3mm 0;
        }

        .items {
            margin-bottom: 5mm;
        }

        .item {
            margin-bottom: 4mm;
            font-size: 8pt;
        }

        .item-name {
            font-weight: bold;
            margin-bottom: 1mm;
        }

        .item-barcode {
            color: #999;
            font-size: 7pt;
            margin-bottom: 1mm;
        }

        .item-details {
            overflow: hidden;
        }

        .item-details::after {
            content: '';
            display: table;
            clear: both;
        }

        .item-qty {
            float: left;
            width: 50%;
        }

        .item-total {
            float: right;
            text-align: right;
            width: 50%;
            font-weight: bold;
        }

        .total-section {
            border-top: 2px solid #000;
            padding-top: 3mm;
            margin-top: 5mm;
        }

        .total-row {
            overflow: hidden;
            font-size: 10pt;
            margin-bottom: 2mm;
        }

        .total-row::after {
            content: '';
            display: table;
            clear: both;
        }

        .total-label {
            float: left;
        }

        .total-value {
            float: right;
            text-align: right;
        }

        .total-row.grand-total {
            font-size: 14pt;
            font-weight: bold;
            margin-top: 3mm;
            padding-top: 3mm;
            border-top: 2px solid #000;
        }

        .footer {
            text-align: center;
            margin-top: 8mm;
            padding-top: 3mm;
            border-top: 2px solid #000;
            font-size: 8pt;
        }

        .footer p {
            margin-bottom: 2mm;
        }

        .print-info {
            margin-top: 5mm;
            font-size: 7pt;
            color: #999;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Заголовок -->
        <div class="header">
            <h1>AFOODS</h1>
            <p>Продуктовый магазин</p>
        </div>

        <!-- Информация о чеке -->
        <div class="info">
            <div class="info-row">
                <span class="info-label">Чек №:</span>
                <span class="info-value">{{ $sale->receipt_number }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Дата:</span>
                <span class="info-value">{{ $sale->date->format('d.m.Y H:i:s') }}</span>
            </div>
            @if($sale->cashier)
            <div class="info-row">
                <span class="info-label">Кассир:</span>
                <span class="info-value">{{ $sale->cashier->name }}</span>
            </div>
            @endif
            @if($sale->shift)
            <div class="info-row">
                <span class="info-label">Смена:</span>
                <span class="info-value">№{{ $sale->shift->id }}</span>
            </div>
            @endif
            @if($sale->shopper && $sale->shopper->phone)
            <div class="info-row">
                <span class="info-label">Покупатель:</span>
                <span class="info-value">{{ $sale->shopper->phone }}</span>
            </div>
            @endif
        </div>

        <div class="divider"></div>

        <!-- Товары -->
        <div class="items">
            @foreach($products as $product)
                @php
                    $item = collect($sale->items)->firstWhere('product_id', $product->id);
                    $quantity = $item['quantity'];
                    $price = $item['price'];
                    $total = $quantity * $price;
                @endphp
                <div class="item">
                    <div class="item-name">{{ $product->name }}</div>
                    @if($product->barcode)
                    <div class="item-barcode">
                        Штрихкод: {{ $product->barcode }}
                    </div>
                    @endif
                    <div class="item-details">
                        <div class="item-qty">
                            {{ $quantity }} x {{ number_format($price / 100, 2, '.', ' ') }} ₽
                        </div>
                        <div class="item-total">
                            {{ number_format($total / 100, 2, '.', ' ') }} ₽
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Итого -->
        <div class="total-section">
            <div class="total-row grand-total">
                <span class="total-label">ИТОГО:</span>
                <span class="total-value">{{ number_format($sale->total_price / 100, 2, '.', ' ') }} ₽</span>
            </div>
        </div>

        <!-- Подвал -->
        <div class="footer">
            <p><strong>Спасибо за покупку!</strong></p>
            <p>Приходите еще!</p>
        </div>

        <div class="print-info">
            Дата печати: {{ now()->format('d.m.Y H:i:s') }}
        </div>
    </div>
</body>
</html>
