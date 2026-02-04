<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Todo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TodoApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Todo::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $allowedSortFields = ['id', 'title', 'status', 'priority', 'due_date', 'created_at'];
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
        $todo = Todo::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $todo,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:pending,in_progress,completed'],
            'priority' => ['nullable', 'string', 'in:low,medium,high'],
            'due_date' => ['nullable', 'string', 'date'],
        ]);

        $validated['status'] = $validated['status'] ?? 'pending';

        $todo = Todo::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Задача успешно создана',
            'data' => $todo,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $todo = Todo::findOrFail($id);

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:pending,in_progress,completed'],
            'priority' => ['nullable', 'string', 'in:low,medium,high'],
            'due_date' => ['nullable', 'string', 'date'],
        ]);

        if (isset($validated['status']) && $validated['status'] === 'completed') {
            $validated['completed_at'] = now();
        } elseif (isset($validated['status']) && $validated['status'] !== 'completed') {
            $validated['completed_at'] = null;
        }

        $todo->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Задача успешно обновлена',
            'data' => $todo->fresh(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $todo = Todo::findOrFail($id);
        $todo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Задача успешно удалена',
        ]);
    }

    /**
     * Отметить задачу как выполненную
     */
    public function complete(int $id): JsonResponse
    {
        $todo = Todo::findOrFail($id);
        $todo->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Задача отмечена как выполненная',
            'data' => $todo->fresh(),
        ]);
    }
}
