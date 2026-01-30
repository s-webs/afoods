<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductApiController extends Controller
{
    /**
     * Получить список всех товаров
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('category');

        // Фильтрация по категории
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Поиск по названию
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Фильтрация по наличию
        if ($request->has('in_stock')) {
            if ($request->in_stock === 'true' || $request->in_stock === '1') {
                $query->where('quantity', '>', 0);
            } elseif ($request->in_stock === 'false' || $request->in_stock === '0') {
                $query->where('quantity', '<=', 0);
            }
        }

        // Сортировка
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');
        $allowedSortFields = ['id', 'name', 'price_amount', 'sale_price_amount', 'quantity', 'created_at'];
        
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Пагинация
        $perPage = $request->get('per_page', 15);
        $perPage = min(max((int)$perPage, 1), 100); // Ограничение от 1 до 100
        
        $products = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    /**
     * Получить товар по ID
     */
    public function show(int $id): JsonResponse
    {
        $product = Product::with('category')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }

    /**
     * Создать новый товар
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'new_name' => ['nullable', 'string', 'max:255'],
            'barcode' => ['nullable', 'string', 'max:255', 'unique:products,barcode'],
            'images' => ['nullable', 'array'],
            'images.*' => ['string'],
            'description' => ['nullable', 'string'],
            'specs' => ['nullable', 'array'],
            'unit' => ['nullable', 'string', 'max:50'],
            'price_amount' => ['nullable', 'integer', 'min:0'],
            'sale_price_amount' => ['nullable', 'integer', 'min:0'],
            'quantity' => ['nullable', 'integer', 'min:0'],
            'obj' => ['nullable', 'array'],
        ]);

        // Генерируем slug из названия, если не передан
        if (empty($validated['slug'] ?? null) && !empty($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
            
            // Проверяем уникальность slug
            $baseSlug = $validated['slug'];
            $counter = 1;
            while (Product::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $baseSlug . '-' . $counter;
                $counter++;
            }
        }

        // Устанавливаем значения по умолчанию
        $validated['unit'] = $validated['unit'] ?? 'pcs';
        $validated['price_amount'] = $validated['price_amount'] ?? 0;
        $validated['sale_price_amount'] = $validated['sale_price_amount'] ?? 0;
        $validated['quantity'] = $validated['quantity'] ?? 0;

        $product = Product::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Товар успешно создан',
            'data' => $product,
        ], 201);
    }

    /**
     * Обновить товар
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'new_name' => ['nullable', 'string', 'max:255'],
            'barcode' => ['nullable', 'string', 'max:255', 'unique:products,barcode,' . $id],
            'images' => ['nullable', 'array'],
            'images.*' => ['string'],
            'description' => ['nullable', 'string'],
            'specs' => ['nullable', 'array'],
            'unit' => ['nullable', 'string', 'max:50'],
            'price_amount' => ['nullable', 'integer', 'min:0'],
            'sale_price_amount' => ['nullable', 'integer', 'min:0'],
            'quantity' => ['nullable', 'integer', 'min:0'],
            'obj' => ['nullable', 'array'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug,' . $id],
        ]);

        // Если изменилось название, обновляем slug
        if (isset($validated['name']) && $validated['name'] !== $product->name) {
            if (!isset($validated['slug'])) {
                $baseSlug = Str::slug($validated['name']);
                $validated['slug'] = $baseSlug;
                
                // Проверяем уникальность slug
                $counter = 1;
                while (Product::where('slug', $validated['slug'])->where('id', '!=', $id)->exists()) {
                    $validated['slug'] = $baseSlug . '-' . $counter;
                    $counter++;
                }
            }
        }

        $product->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Товар успешно обновлен',
            'data' => $product->fresh(),
        ]);
    }

    /**
     * Удалить товар
     */
    public function destroy(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Товар успешно удален',
        ]);
    }
}
