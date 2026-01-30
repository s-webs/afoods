<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cashier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CashierApiController extends Controller
{
    /**
     * Получить список всех кассиров
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->get('per_page', 15), 1), 100);

        $cashiers = Cashier::query()
            ->orderBy('name', 'asc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $cashiers->items(),
            'pagination' => [
                'current_page' => $cashiers->currentPage(),
                'last_page' => $cashiers->lastPage(),
                'per_page' => $cashiers->perPage(),
                'total' => $cashiers->total(),
            ],
        ]);
    }

    /**
     * Получить кассира по ID
     */
    public function show(int $id): JsonResponse
    {
        $cashier = Cashier::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $cashier,
        ]);
    }

    /**
     * Создать кассира
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'user_id' => ['nullable', 'integer'],
            'uuid' => ['nullable', 'string', 'unique:cashiers,uuid'],
            'device_id' => ['nullable', 'string'],
            'enabled' => ['nullable', 'boolean'],
        ]);

        $validated['enabled'] = $validated['enabled'] ?? false;

        $cashier = Cashier::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Кассир успешно создан',
            'data' => $cashier,
        ], 201);
    }

    /**
     * Обновить кассира
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $cashier = Cashier::findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'user_id' => ['nullable', 'integer'],
            'uuid' => ['nullable', 'string', 'unique:cashiers,uuid,' . $id],
            'device_id' => ['nullable', 'string'],
            'enabled' => ['nullable', 'boolean'],
        ]);

        $cashier->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Кассир успешно обновлен',
            'data' => $cashier->fresh(),
        ]);
    }

    /**
     * Удалить кассира
     */
    public function destroy(int $id): JsonResponse
    {
        $cashier = Cashier::findOrFail($id);
        $cashier->delete();

        return response()->json([
            'success' => true,
            'message' => 'Кассир успешно удален',
        ]);
    }
}
