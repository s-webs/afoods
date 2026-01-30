<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Чек № {{ $sale->receipt_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            background: #f5f5f5;
            padding: 20px;
        }

        .receipt {
            max-width: 400px;
            margin: 0 auto;
            background: white;
            padding: 30px 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h1 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 12px;
            color: #666;
        }

        .info {
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.6;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .info-label {
            font-weight: bold;
        }

        .divider {
            border-top: 1px dashed #999;
            margin: 15px 0;
        }

        .items {
            margin-bottom: 20px;
        }

        .item {
            margin-bottom: 15px;
            font-size: 14px;
        }

        .item-name {
            font-weight: bold;
            margin-bottom: 3px;
        }

        .item-details {
            display: flex;
            justify-content: space-between;
            color: #666;
            font-size: 13px;
        }

        .total-section {
            border-top: 2px solid #333;
            padding-top: 15px;
            margin-top: 20px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 16px;
            margin-bottom: 5px;
        }

        .total-row.grand-total {
            font-size: 20px;
            font-weight: bold;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #333;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #333;
            font-size: 14px;
        }

        .footer p {
            margin-bottom: 5px;
        }

        .qr-code {
            text-align: center;
            margin-top: 20px;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .receipt {
                box-shadow: none;
                max-width: 100%;
            }
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
                <span>{{ $sale->receipt_number }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Дата:</span>
                <span>{{ $sale->date->format('d.m.Y H:i:s') }}</span>
            </div>
            @if($sale->cashier)
            <div class="info-row">
                <span class="info-label">Кассир:</span>
                <span>{{ $sale->cashier->name }}</span>
            </div>
            @endif
            @if($sale->shift)
            <div class="info-row">
                <span class="info-label">Смена:</span>
                <span>№{{ $sale->shift->id }}</span>
            </div>
            @endif
            @if($sale->shopper && $sale->shopper->phone)
            <div class="info-row">
                <span class="info-label">Покупатель:</span>
                <span>{{ $sale->shopper->phone }}</span>
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
                    <div style="color: #999; font-size: 12px; margin-bottom: 3px;">
                        Штрихкод: {{ $product->barcode }}
                    </div>
                    @endif
                    <div class="item-details">
                        <span>{{ $quantity }} x {{ number_format($price / 100, 2, '.', ' ') }} ₽</span>
                        <span><strong>{{ number_format($total / 100, 2, '.', ' ') }} ₽</strong></span>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Итого -->
        <div class="total-section">
            <div class="total-row grand-total">
                <span>ИТОГО:</span>
                <span>{{ number_format($sale->total_price / 100, 2, '.', ' ') }} ₽</span>
            </div>
        </div>

        <!-- Подвал -->
        <div class="footer">
            <p><strong>Спасибо за покупку!</strong></p>
            <p>Приходите еще!</p>
            <p style="margin-top: 10px; font-size: 12px; color: #999;">
                Дата печати: {{ now()->format('d.m.Y H:i:s') }}
            </p>
        </div>
    </div>

    <script>
        // Автоматически открыть диалог печати
        // window.print();
    </script>
</body>
</html>
