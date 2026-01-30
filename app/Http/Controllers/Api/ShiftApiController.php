<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShiftApiController extends Controller
{
    /**
     * Получить список всех смен
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->get('per_page', 15), 1), 100);

        $shifts = Shift::query()
            ->orderByDesc('opened_at')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $shifts->items(),
            'pagination' => [
                'current_page' => $shifts->currentPage(),
                'last_page' => $shifts->lastPage(),
                'per_page' => $shifts->perPage(),
                'total' => $shifts->total(),
            ],
        ]);
    }

    /**
     * Получить текущую смену (последняя открытая и не закрытая)
     */
    public function current(): JsonResponse
    {
        $activeShift = Shift::query()
            ->whereNull('closed_at')
            ->latest('opened_at')
            ->first();

        if (!$activeShift) {
            return response()->json([
                'success' => false,
                'message' => 'Нет открытой смены',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $activeShift,
        ]);
    }

    /**
     * Открыть смену
     */
    public function open(): JsonResponse
    {
        // Запрет открытия, если есть незакрытая смена
        $activeShift = Shift::query()
            ->whereNull('closed_at')
            ->latest('opened_at')
            ->first();
        if ($activeShift) {
            return response()->json([
                'success' => false,
                'message' => 'Нельзя открыть смену: есть незакрытая смена',
                'data' => $activeShift,
            ], 409);
        }

        $shift = Shift::create([
            'opened_at' => now(),
            'closed_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Смена открыта',
            'data' => $shift,
        ], 201);
    }

    /**
     * Закрыть смену
     */
    public function close(int $id): JsonResponse
    {
        $shift = Shift::findOrFail($id);

        if ($shift->closed_at !== null) {
            return response()->json([
                'success' => false,
                'message' => 'Смена уже закрыта',
                'data' => $shift,
            ], 409);
        }

        $shift->update([
            'closed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Смена закрыта',
            'data' => $shift->fresh(),
        ]);
    }

    /**
     * Удалить смену
     */
    public function destroy(int $id): JsonResponse
    {
        $shift = Shift::findOrFail($id);
        $shift->delete();

        return response()->json([
            'success' => true,
            'message' => 'Смена удалена',
        ]);
    }
}

