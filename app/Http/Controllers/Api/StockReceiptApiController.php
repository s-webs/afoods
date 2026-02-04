<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockReceipt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockReceiptApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = StockReceipt::with('product');

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->has('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $sortBy = $request->get('sort_by', 'date');
        $sortOrder = $request->get('sort_order', 'desc');
        $allowedSortFields = ['id', 'date', 'quantity', 'price_amount', 'created_at'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $perPage = min(max((int) $request->get('per_page', 15), 1), 100);
        $items = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $items->items(),
            'pagination' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $receipt = StockReceipt::with('product')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $receipt,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'price_amount' => ['required', 'integer', 'min:0'],
            'date' => ['nullable', 'string', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['date'] = $validated['date'] ?? now();

        $receipt = StockReceipt::create($validated);

        Product::where('id', $validated['product_id'])->increment('quantity', $validated['quantity']);

        return response()->json([
            'success' => true,
            'message' => 'Приход успешно создан',
            'data' => $receipt->load('product'),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $receipt = StockReceipt::findOrFail($id);

        $validated = $request->validate([
            'product_id' => ['sometimes', 'required', 'integer', 'exists:products,id'],
            'quantity' => ['sometimes', 'required', 'integer', 'min:1'],
            'price_amount' => ['sometimes', 'required', 'integer', 'min:0'],
            'date' => ['nullable', 'string', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $newQuantity = $validated['quantity'] ?? $receipt->quantity;
        $newProductId = $validated['product_id'] ?? $receipt->product_id;

        Product::where('id', $receipt->product_id)->decrement('quantity', $receipt->quantity);
        Product::where('id', $newProductId)->increment('quantity', $newQuantity);

        $receipt->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Приход успешно обновлён',
            'data' => $receipt->fresh('product'),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $receipt = StockReceipt::findOrFail($id);

        Product::where('id', $receipt->product_id)->decrement('quantity', $receipt->quantity);

        $receipt->delete();

        return response()->json([
            'success' => true,
            'message' => 'Приход успешно удалён',
        ]);
    }
}
