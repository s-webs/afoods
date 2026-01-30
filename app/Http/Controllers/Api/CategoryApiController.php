<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryApiController extends Controller
{
    /**
     * Получить список всех категорий
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->get('per_page', 15), 1), 100);

        $categories = Category::query()
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $categories->items(),
            'pagination' => [
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
            ],
        ]);
    }

    /**
     * Получить категорию по ID
     */
    public function show(int $id): JsonResponse
    {
        $category = Category::with('products')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $category,
        ]);
    }

    /**
     * Создать категорию
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'image' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        // Генерируем slug из названия
        $validated['slug'] = Str::slug($validated['name']);
        
        // Проверяем уникальность slug
        $baseSlug = $validated['slug'];
        $counter = 1;
        while (Category::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $baseSlug . '-' . $counter;
            $counter++;
        }

        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $category = Category::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Категория успешно создана',
            'data' => $category,
        ], 201);
    }

    /**
     * Обновить категорию
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'image' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:categories,slug,' . $id],
        ]);

        // Если изменилось название, обновляем slug
        if (isset($validated['name']) && $validated['name'] !== $category->name) {
            if (!isset($validated['slug'])) {
                $baseSlug = Str::slug($validated['name']);
                $validated['slug'] = $baseSlug;
                
                // Проверяем уникальность slug
                $counter = 1;
                while (Category::where('slug', $validated['slug'])->where('id', '!=', $id)->exists()) {
                    $validated['slug'] = $baseSlug . '-' . $counter;
                    $counter++;
                }
            }
        }

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Категория успешно обновлена',
            'data' => $category->fresh(),
        ]);
    }

    /**
     * Удалить категорию
     */
    public function destroy(int $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        // Проверяем наличие товаров в категории
        if ($category->products()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Невозможно удалить категорию: она содержит товары',
            ], 409);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Категория успешно удалена',
        ]);
    }
}
