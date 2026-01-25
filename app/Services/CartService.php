<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Session;

class CartService
{
    const SESSION_KEY = 'cart';

    /**
     * Get all items in cart.
     */
    public static function getItems(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    /**
     * Get cart total count.
     */
    public static function getTotalCount(): int
    {
        $items = self::getItems();
        return array_sum(array_column($items, 'quantity'));
    }

    /**
     * Get cart total price.
     */
    public static function getTotalPrice(): int
    {
        $items = self::getItems();
        $total = 0;

        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            if ($product) {
                $price = $product->sale_price_amount > 0 ? $product->sale_price_amount : $product->price_amount;
                $total += $price * $item['quantity'];
            }
        }

        return $total;
    }

    /**
     * Add product to cart.
     */
    public static function add(int $productId, int $quantity = 1): bool
    {
        $product = Product::find($productId);
        
        if (!$product) {
            return false;
        }

        $items = self::getItems();
        $existingIndex = self::findItemIndex($items, $productId);

        if ($existingIndex !== false) {
            // Update existing item
            $items[$existingIndex]['quantity'] += $quantity;
        } else {
            // Add new item
            $items[] = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'added_at' => now()->toDateTimeString(),
            ];
        }

        Session::put(self::SESSION_KEY, $items);
        return true;
    }

    /**
     * Update product quantity in cart.
     */
    public static function update(int $productId, int $quantity): bool
    {
        if ($quantity <= 0) {
            return self::remove($productId);
        }

        $items = self::getItems();
        $index = self::findItemIndex($items, $productId);

        if ($index !== false) {
            $items[$index]['quantity'] = $quantity;
            Session::put(self::SESSION_KEY, $items);
            return true;
        }

        return false;
    }

    /**
     * Remove product from cart.
     */
    public static function remove(int $productId): bool
    {
        $items = self::getItems();
        $index = self::findItemIndex($items, $productId);

        if ($index !== false) {
            unset($items[$index]);
            Session::put(self::SESSION_KEY, array_values($items));
            return true;
        }

        return false;
    }

    /**
     * Clear cart.
     */
    public static function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    /**
     * Get cart items with product details.
     */
    public static function getItemsWithProducts(): array
    {
        $items = self::getItems();
        $result = [];

        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            if ($product) {
                $price = $product->sale_price_amount > 0 ? $product->sale_price_amount : $product->price_amount;
                
                $result[] = [
                    'product_id' => $product->id,
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'price' => $price,
                    'total' => $price * $item['quantity'],
                    'added_at' => $item['added_at'] ?? null,
                ];
            }
        }

        return $result;
    }

    /**
     * Get quantity of specific product in cart.
     */
    public static function getProductQuantity(int $productId): int
    {
        $items = self::getItems();
        $index = self::findItemIndex($items, $productId);
        
        return $index !== false ? $items[$index]['quantity'] : 0;
    }

    /**
     * Check if cart is empty.
     */
    public static function isEmpty(): bool
    {
        return empty(self::getItems());
    }

    /**
     * Find item index in cart array.
     */
    private static function findItemIndex(array $items, int $productId): int|false
    {
        foreach ($items as $index => $item) {
            if ($item['product_id'] === $productId) {
                return $index;
            }
        }

        return false;
    }
}
