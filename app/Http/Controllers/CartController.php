<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\YandexDeliveryService;
use App\Models\Product;
use App\Models\Shopper;
use App\Models\Sale;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Show the cart page.
     */
    public function index()
    {
        $items = CartService::getItemsWithProducts();
        $totalPrice = CartService::getTotalPrice();
        $totalCount = CartService::getTotalCount();

        return view('cart.index', [
            'items' => $items,
            'totalPrice' => $totalPrice,
            'totalCount' => $totalCount,
        ]);
    }

    /**
     * Add product to cart.
     */
    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $quantity = $validated['quantity'] ?? 1;
        $added = CartService::add($validated['product_id'], $quantity);

        if (!$added) {
            return response()->json([
                'success' => false,
                'message' => 'Товар не найден',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Товар добавлен в корзину',
            'cart_count' => CartService::getTotalCount(),
        ]);
    }

    /**
     * Update product quantity in cart.
     */
    public function update(Request $request, int $productId)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $updated = CartService::update($productId, $validated['quantity']);

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'Товар не найден в корзине',
            ], 404);
        }

        $items = CartService::getItemsWithProducts();
        $item = collect($items)->firstWhere('product_id', $productId);

        return response()->json([
            'success' => true,
            'message' => 'Количество обновлено',
            'item_total' => $item['total'] ?? 0,
            'cart_total' => CartService::getTotalPrice(),
            'cart_count' => CartService::getTotalCount(),
        ]);
    }

    /**
     * Remove product from cart.
     */
    public function remove(int $productId)
    {
        $removed = CartService::remove($productId);

        if (!$removed) {
            return response()->json([
                'success' => false,
                'message' => 'Товар не найден в корзине',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Товар удален из корзины',
            'cart_total' => CartService::getTotalPrice(),
            'cart_count' => CartService::getTotalCount(),
        ]);
    }

    /**
     * Clear cart.
     */
    public function clear()
    {
        CartService::clear();

        return redirect()->route('cart.index')
            ->with('status', 'Корзина очищена');
    }

    /**
     * Get cart summary (for AJAX requests).
     */
    public function summary()
    {
        return response()->json([
            'count' => CartService::getTotalCount(),
            'total' => CartService::getTotalPrice(),
        ]);
    }

    /**
     * Show checkout page.
     */
    public function checkout()
    {
        $items = CartService::getItemsWithProducts();

        if (empty($items)) {
            return redirect()->route('cart.index')
                ->with('error', 'Корзина пуста');
        }

        $totalPrice = CartService::getTotalPrice();
        $totalCount = CartService::getTotalCount();

        // Get shopper for address selection
        $shopper = null;
        $addresses = [];
        $defaultAddress = null;

        if (auth()->check()) {
            $user = auth()->user();
            $addresses = $shopper->addresses ?? [];
            $defaultAddress = $shopper->getDefaultAddress();
        }

        return view('cart.checkout', [
            'items' => $items,
            'totalPrice' => $totalPrice,
            'totalCount' => $totalCount,
            'shopper' => $shopper,
            'addresses' => $addresses,
            'defaultAddress' => $defaultAddress,
            'yandexApiKey' => env('YANDEX_MAPS_API_KEY'),
        ]);
    }

    /**
     * Process order.
     */
    public function processOrder(Request $request)
    {
        $items = CartService::getItemsWithProducts();

        if (empty($items)) {
            return redirect()->route('cart.index')
                ->with('error', 'Корзина пуста');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'delivery_address' => ['required', 'array'],
            'delivery_address.address' => ['required', 'string'],
            'delivery_address.latitude' => ['required', 'numeric'],
            'delivery_address.longitude' => ['required', 'numeric'],
            'delivery_address.house' => ['nullable', 'string'],
            'delivery_address.apartment' => ['nullable', 'string'],
            'delivery_address.entrance' => ['nullable', 'string'],
            'delivery_address.floor' => ['nullable', 'string'],
            'delivery_address.notes' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $shopper = null;

        // Get or create shopper (only if user is authenticated)
        if (auth()->check()) {
            $user = auth()->user();
            $shopper = $user->getOrCreateShopper();

            // Update shopper phone if provided
            if ($validated['phone']) {
                $shopper->update(['phone' => $validated['phone']]);
            }
        } else {
            // For guests, create a temporary shopper
            $shopper = Shopper::create([
                'user_id' => null,
                'phone' => $validated['phone'],
                'addresses' => [],
            ]);
        }

        // Generate receipt number
        $receiptNumber = 'RCP-' . date('Ymd') . '-' . str_pad(Sale::count() + 1, 6, '0', STR_PAD_LEFT);

        // Create sale
        $sale = Sale::create([
            'cashier_id' => null, // Will be filled later
            'shift_id' => null, // Will be filled later
            'date' => now(),
            'receipt_number' => $receiptNumber,
            'items' => $items,
            'total_price' => CartService::getTotalPrice(),
        ]);

        // Clear cart
        CartService::clear();

        return redirect()->route('cart.success', $sale->id)
            ->with('status', 'Заказ успешно оформлен!');
    }

    /**
     * Calculate delivery cost.
     */
    public function calculateDelivery(Request $request)
    {
        $validated = $request->validate([
            'from_latitude' => ['required', 'numeric'],
            'from_longitude' => ['required', 'numeric'],
            'to_latitude' => ['required', 'numeric'],
            'to_longitude' => ['required', 'numeric'],
        ]);

        $deliveryService = new YandexDeliveryService();
        $options = $deliveryService->getDeliveryOptions(
            [$validated['from_latitude'], $validated['from_longitude']],
            [$validated['to_latitude'], $validated['to_longitude']]
        );

        \Log::info('Delivery calculation result', [
            'from' => [$validated['from_latitude'], $validated['from_longitude']],
            'to' => [$validated['to_latitude'], $validated['to_longitude']],
            'options_count' => count($options),
            'options' => $options,
        ]);

        return response()->json([
            'success' => true,
            'options' => $options,
            'debug' => [
                'from' => [$validated['from_latitude'], $validated['from_longitude']],
                'to' => [$validated['to_latitude'], $validated['to_longitude']],
            ],
        ]);
    }

    /**
     * Show order success page.
     */
    public function success(int $saleId)
    {
        $sale = Sale::with('shopper')->findOrFail($saleId);

        return view('cart.success', [
            'sale' => $sale,
        ]);
    }
}
