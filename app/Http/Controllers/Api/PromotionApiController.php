<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromotionApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Promotion::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $allowedSortFields = ['id', 'name', 'price', 'created_at'];
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
        $promotion = Promotion::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $promotion,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'products' => ['required', 'array'],
            'products.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'products.*.quantity' => ['required', 'integer', 'min:1'],
            'price' => ['nullable', 'integer', 'min:0'],
        ]);

        $promotion = Promotion::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Акция успешно создана',
            'data' => $promotion,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $promotion = Promotion::findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'products' => ['sometimes', 'required', 'array'],
            'products.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'products.*.quantity' => ['required', 'integer', 'min:1'],
            'price' => ['nullable', 'integer', 'min:0'],
        ]);

        $promotion->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Акция успешно обновлена',
            'data' => $promotion->fresh(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $promotion = Promotion::findOrFail($id);
        $promotion->delete();

        return response()->json([
            'success' => true,
            'message' => 'Акция успешно удалена',
        ]);
    }

    /**
     * Развернуть акцию в массив items для добавления в продажу.
     * POST /promotions-api/{id}/expand { "quantity": 2 }
     */
    public function expand(Request $request, int $id): JsonResponse
    {
        $promotion = Promotion::findOrFail($id);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $baseItems = $promotion->getProductsAsItems();
        $multiplier = $validated['quantity'];

        $items = collect($baseItems)->map(function ($item) use ($multiplier) {
            return [
                'product_id' => $item['product_id'],
                'name_snapshot' => $item['name_snapshot'],
                'price' => $item['price'],
                'quantity' => $item['quantity'] * $multiplier,
            ];
        })->values()->all();

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'promotion_name' => $promotion->name,
            ],
        ]);
    }
}
