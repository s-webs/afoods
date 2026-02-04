<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

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
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['nullable', 'file', 'image', 'mimes:webp,jpeg,jpg,png', 'max:2048'],
            'description' => ['nullable', 'string'],
            'specs' => ['nullable', 'array'],
            'unit' => ['nullable', 'string', 'max:50'],
            'price_amount' => ['nullable', 'integer', 'min:0'],
            'sale_price_amount' => ['nullable', 'integer', 'min:0'],
            'quantity' => ['nullable', 'integer'],
            'obj' => ['nullable', 'array'],
        ]);

        // Сохраняем изображения в public/uploads/products
        if (!empty($validated['images'])) {
            $validated['images'] = $this->saveProductImages($validated['images']);
        }

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
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['nullable', 'file', 'image', 'mimes:webp,jpeg,jpg,png', 'max:2048'],
            'description' => ['nullable', 'string'],
            'specs' => ['nullable', 'array'],
            'unit' => ['nullable', 'string', 'max:50'],
            'price_amount' => ['nullable', 'integer', 'min:0'],
            'sale_price_amount' => ['nullable', 'integer', 'min:0'],
            'quantity' => ['nullable', 'integer'],
            'obj' => ['nullable', 'array'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug,' . $id],
        ]);

        // Сохраняем изображения в public/uploads/products
        if (!empty($validated['images'])) {
            $validated['images'] = $this->saveProductImages($validated['images']);
        }

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

    /**
     * Установить скидку для одного товара
     */
    public function setDiscount(Request $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'discount_type' => ['required', 'string', 'in:percent,fixed'],
            'discount_value' => ['required', 'integer', 'min:0'],
        ]);

        $discountType = $validated['discount_type'];
        $discountValue = $validated['discount_value'];

        if ($discountType === 'percent') {
            // Валидация для процентов: от 0 до 100
            if ($discountValue > 100) {
                return response()->json([
                    'success' => false,
                    'message' => 'Процент скидки не может превышать 100',
                    'errors' => [
                        'discount_value' => ['Процент скидки должен быть от 0 до 100']
                    ]
                ], 422);
            }

            // Расчет скидки от price_amount
            $salePriceAmount = (int) round($product->price_amount * (100 - $discountValue) / 100);
        } else {
            // Для фиксированной суммы - проверяем что она не больше price_amount
            if ($discountValue > $product->price_amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Цена со скидкой не может превышать базовую цену',
                    'errors' => [
                        'discount_value' => ['Значение sale_price_amount не может быть больше price_amount']
                    ]
                ], 422);
            }

            $salePriceAmount = $discountValue;
        }

        $product->update(['sale_price_amount' => $salePriceAmount]);

        return response()->json([
            'success' => true,
            'message' => 'Скидка успешно установлена',
            'data' => $product->fresh(),
        ]);
    }

    /**
     * Массовая установка скидки
     */
    public function bulkSetDiscount(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['integer', 'exists:products,id'],
            'discount_type' => ['required', 'string', 'in:percent,fixed'],
            'discount_value' => ['required', 'integer', 'min:0'],
        ]);

        $productIds = $validated['product_ids'];
        $discountType = $validated['discount_type'];
        $discountValue = $validated['discount_value'];

        if ($discountType === 'percent') {
            // Валидация для процентов
            if ($discountValue > 100) {
                return response()->json([
                    'success' => false,
                    'message' => 'Процент скидки не может превышать 100',
                    'errors' => [
                        'discount_value' => ['Процент скидки должен быть от 0 до 100']
                    ]
                ], 422);
            }

            // Получаем товары и обновляем каждый
            $products = Product::whereIn('id', $productIds)->get();
            $updatedCount = 0;

            foreach ($products as $product) {
                $salePriceAmount = (int) round($product->price_amount * (100 - $discountValue) / 100);
                $product->update(['sale_price_amount' => $salePriceAmount]);
                $updatedCount++;
            }
        } else {
            // Для фиксированной суммы - проверяем каждый товар
            $products = Product::whereIn('id', $productIds)->get();
            $updatedCount = 0;
            $skippedProducts = [];

            foreach ($products as $product) {
                if ($discountValue > $product->price_amount) {
                    $skippedProducts[] = $product->id;
                    continue;
                }

                $product->update(['sale_price_amount' => $discountValue]);
                $updatedCount++;
            }

            // Если были пропущенные товары, добавляем предупреждение
            if (!empty($skippedProducts)) {
                return response()->json([
                    'success' => true,
                    'message' => "Скидка установлена для {$updatedCount} товаров",
                    'data' => [
                        'updated_count' => $updatedCount,
                        'product_ids' => $productIds,
                        'skipped_product_ids' => $skippedProducts,
                    ],
                    'warning' => 'Некоторые товары пропущены, т.к. фиксированная цена превышает их базовую цену'
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Скидка установлена для {$updatedCount} товаров",
            'data' => [
                'updated_count' => $updatedCount,
                'product_ids' => $productIds,
            ],
        ]);
    }

    /**
     * Сбросить скидку для одного товара
     */
    public function removeDiscount(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->update(['sale_price_amount' => 0]);

        return response()->json([
            'success' => true,
            'message' => 'Скидка успешно сброшена',
            'data' => $product->fresh(),
        ]);
    }

    /**
     * Массовый сброс скидок
     */
    public function bulkRemoveDiscount(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['integer', 'exists:products,id'],
        ]);

        $productIds = $validated['product_ids'];
        $updatedCount = Product::whereIn('id', $productIds)->update(['sale_price_amount' => 0]);

        return response()->json([
            'success' => true,
            'message' => "Скидки сброшены для {$updatedCount} товаров",
            'data' => [
                'updated_count' => $updatedCount,
                'product_ids' => $productIds,
            ],
        ]);
    }

    /**
     * Сохраняет изображения в public/uploads/products и возвращает массив путей
     *
     * @param array $images Массив: UploadedFile, base64-строки или пути к уже сохранённым файлам
     * @return array
     */
    private function saveProductImages(array $images): array
    {
        $savedPaths = [];
        $uploadDir = 'uploads/products';

        foreach ($images as $image) {
            if (empty($image)) {
                continue;
            }

            // Загруженный файл (multipart/form-data)
            if ($image instanceof \Illuminate\Http\UploadedFile) {
                $path = $image->store($uploadDir, 'public');
                if ($path) {
                    $savedPaths[] = $path;
                }
                continue;
            }

            // Base64-строка (data:image/...;base64,...)
            if (is_string($image) && preg_match('/^data:image\/(\w+);base64,(.+)$/', $image, $matches)) {
                $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
                $data = base64_decode($matches[2]);
                if ($data !== false) {
                    $filename = Str::random(40) . '.' . $extension;
                    $path = $uploadDir . '/' . $filename;
                    if (Storage::disk('public')->put($path, $data)) {
                        $savedPaths[] = $path;
                    }
                }
                continue;
            }

            // Уже сохранённый путь (начинается с uploads/products/)
            if (is_string($image) && str_starts_with($image, 'uploads/products/')) {
                $savedPaths[] = $image;
            }
        }

        return $savedPaths;
    }
}
