<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;

class ReceiptService
{
    /**
     * Генерация HTML чека
     */
    public function generateHtml(Sale $sale): string
    {
        $sale->load(['shopper', 'cashier', 'shift']);
        $products = $this->getProductsData($sale);

        return view('receipts.receipt-html', [
            'sale' => $sale,
            'products' => $products,
        ])->render();
    }

    /**
     * Генерация PDF чека
     */
    public function generatePdf(Sale $sale): \Barryvdh\DomPDF\PDF
    {
        $sale->load(['shopper', 'cashier', 'shift']);
        $products = $this->getProductsData($sale);

        $pdf = Pdf::loadView('receipts.receipt-pdf', [
            'sale' => $sale,
            'products' => $products,
        ]);

        $pdf->setPaper([0, 0, 226.77, 841.89], 'portrait'); // 80mm width (thermal printer)

        return $pdf;
    }

    /**
     * Генерация JSON чека (для кассовых принтеров)
     */
    public function generateJson(Sale $sale): array
    {
        $sale->load(['shopper', 'cashier', 'shift']);
        $products = $this->getProductsData($sale);

        return [
            'receipt_number' => $sale->receipt_number,
            'date' => $sale->date->format('d.m.Y H:i:s'),
            'cashier' => $sale->cashier ? [
                'id' => $sale->cashier->id,
                'name' => $sale->cashier->name,
            ] : null,
            'shift' => $sale->shift ? [
                'id' => $sale->shift->id,
                'opened_at' => $sale->shift->opened_at->format('d.m.Y H:i'),
            ] : null,
            'shopper' => $sale->shopper ? [
                'id' => $sale->shopper->id,
                'phone' => $sale->shopper->phone,
            ] : null,
            'items' => $products->map(function ($product) use ($sale) {
                $item = collect($sale->items)->firstWhere('product_id', $product->id);
                return [
                    'name' => $product->name,
                    'barcode' => $product->barcode,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['quantity'] * $item['price'],
                ];
            })->values(),
            'subtotal' => $sale->total_price,
            'total' => $sale->total_price,
            'payment_method' => 'cash', // Можно расширить
        ];
    }

    /**
     * Генерация текстового чека (для термопринтера)
     */
    public function generateText(Sale $sale): string
    {
        $sale->load(['shopper', 'cashier', 'shift']);
        $products = $this->getProductsData($sale);

        $width = 32; // Ширина чека в символах (для 80мм принтера)
        $line = str_repeat('-', $width);
        $doubleLine = str_repeat('=', $width);

        $receipt = [];
        $receipt[] = $this->centerText('AFОODS', $width);
        $receipt[] = $this->centerText('Продуктовый магазин', $width);
        $receipt[] = $line;
        $receipt[] = '';
        $receipt[] = 'Чек № ' . $sale->receipt_number;
        $receipt[] = 'Дата: ' . $sale->date->format('d.m.Y H:i:s');
        
        if ($sale->cashier) {
            $receipt[] = 'Кассир: ' . $sale->cashier->name;
        }
        
        if ($sale->shift) {
            $receipt[] = 'Смена: ' . $sale->shift->id;
        }

        $receipt[] = '';
        $receipt[] = $doubleLine;
        $receipt[] = '';

        // Товары
        foreach ($products as $product) {
            $item = collect($sale->items)->firstWhere('product_id', $product->id);
            $quantity = $item['quantity'];
            $price = $item['price'];
            $total = $quantity * $price;

            $receipt[] = $product->name;
            $receipt[] = $this->formatLine(
                "  {$quantity} x " . $this->formatMoney($price),
                $this->formatMoney($total),
                $width
            );
        }

        $receipt[] = '';
        $receipt[] = $doubleLine;
        $receipt[] = '';
        $receipt[] = $this->formatLine(
            'ИТОГО:',
            $this->formatMoney($sale->total_price),
            $width
        );
        $receipt[] = '';
        $receipt[] = $line;
        $receipt[] = $this->centerText('Спасибо за покупку!', $width);
        $receipt[] = $this->centerText('Приходите еще!', $width);
        $receipt[] = $line;
        $receipt[] = '';

        return implode("\n", $receipt);
    }

    /**
     * Получить данные о товарах
     */
    protected function getProductsData(Sale $sale)
    {
        $productIds = collect($sale->items)->pluck('product_id')->unique();
        return Product::whereIn('id', $productIds)->get();
    }

    /**
     * Центрировать текст
     */
    protected function centerText(string $text, int $width): string
    {
        $padding = max(0, floor(($width - mb_strlen($text)) / 2));
        return str_repeat(' ', $padding) . $text;
    }

    /**
     * Форматировать строку с выравниванием
     */
    protected function formatLine(string $left, string $right, int $width): string
    {
        $leftLen = mb_strlen($left);
        $rightLen = mb_strlen($right);
        $spaces = max(1, $width - $leftLen - $rightLen);
        
        return $left . str_repeat(' ', $spaces) . $right;
    }

    /**
     * Форматировать деньги
     */
    protected function formatMoney(int $amount): string
    {
        return number_format($amount / 100, 2, '.', ' ') . ' ₽';
    }
}
