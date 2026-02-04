<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Counterparty;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CounterpartyApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Counterparty::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('bin', 'like', "%{$search}%");
            });
        }

        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $allowedSortFields = ['id', 'name', 'bin', 'created_at'];
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
        $counterparty = Counterparty::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $counterparty,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'bin' => ['nullable', 'string', 'max:50'],
            'kbe' => ['nullable', 'string', 'max:50'],
            'iik' => ['nullable', 'string', 'max:50'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bik' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'director' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $counterparty = Counterparty::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Контрагент успешно создан',
            'data' => $counterparty,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $counterparty = Counterparty::findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'bin' => ['nullable', 'string', 'max:50'],
            'kbe' => ['nullable', 'string', 'max:50'],
            'iik' => ['nullable', 'string', 'max:50'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bik' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'director' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $counterparty->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Контрагент успешно обновлён',
            'data' => $counterparty->fresh(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $counterparty = Counterparty::findOrFail($id);
        $counterparty->delete();

        return response()->json([
            'success' => true,
            'message' => 'Контрагент успешно удалён',
        ]);
    }
}
