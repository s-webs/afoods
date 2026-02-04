<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Debtor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DebtorApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Debtor::with('counterparty');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('bin', 'like', "%{$search}%");
            });
        }
        if ($request->has('has_debt') && ($request->has_debt === 'true' || $request->has_debt === '1')) {
            $query->where('amount', '>', 0);
        }

        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $allowedSortFields = ['id', 'name', 'amount', 'created_at'];
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
        $debtor = Debtor::with('counterparty')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $debtor,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'counterparty_id' => ['nullable', 'integer', 'exists:counterparties,id'],
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['nullable', 'integer', 'min:0'],
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

        $validated['amount'] = $validated['amount'] ?? 0;

        $debtor = Debtor::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Должник успешно создан',
            'data' => $debtor->load('counterparty'),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $debtor = Debtor::findOrFail($id);

        $validated = $request->validate([
            'counterparty_id' => ['nullable', 'integer', 'exists:counterparties,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'amount' => ['nullable', 'integer', 'min:0'],
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

        $debtor->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Должник успешно обновлён',
            'data' => $debtor->fresh('counterparty'),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $debtor = Debtor::findOrFail($id);
        $debtor->sales()->update(['debtor_id' => null]);
        $debtor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Должник успешно удалён',
        ]);
    }

    /**
     * Покупки должника
     */
    public function sales(Request $request, int $id): JsonResponse
    {
        $debtor = Debtor::findOrFail($id);

        $perPage = min(max((int) $request->get('per_page', 15), 1), 100);
        $sales = $debtor->sales()->orderBy('date', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $sales->items(),
            'pagination' => [
                'current_page' => $sales->currentPage(),
                'last_page' => $sales->lastPage(),
                'per_page' => $sales->perPage(),
                'total' => $sales->total(),
            ],
        ]);
    }

    /**
     * Обновление суммы долга
     */
    public function updateAmount(Request $request, int $id): JsonResponse
    {
        $debtor = Debtor::findOrFail($id);

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:0'],
        ]);

        $debtor->update(['amount' => $validated['amount']]);

        return response()->json([
            'success' => true,
            'message' => 'Сумма долга обновлена',
            'data' => $debtor->fresh(),
        ]);
    }
}
