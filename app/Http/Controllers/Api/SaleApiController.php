<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Services\ReceiptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SaleApiController extends Controller
{
    /**
     * Получить список всех продаж
     */
    public function index(Request $request): JsonResponse
    {
        $query = Sale::query();

        // Фильтрация по дате
        if ($request->has('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        // Сортировка
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');
        $allowedSortFields = ['id', 'date', 'total_price', 'created_at'];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Пагинация
        $perPage = min(max((int) $request->get('per_page', 15), 1), 100);

        $sales = $query->paginate($perPage);

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
     * Получить продажу по ID
     */
    public function show(int $id): JsonResponse
    {
        $sale = Sale::with('shopper')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $sale,
        ]);
    }

    /**
     * Создать продажу
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cashier_id' => 'nullable|integer',
            'shift_id' => 'nullable|integer',
            'receipt_number' => 'required|string',
            'total_price' => 'required|numeric',
            'total_qty' => 'required|numeric',
            'date' => 'nullable|string',  // ← сделайте nullable
            'items' => 'required|array',
            'items.*.product_id' => 'required|integer',
            'items.*.name_snapshot' => 'required|string',
            'items.*.price' => 'required|numeric',
            'items.*.quantity' => 'required|numeric',
            'items.*.discount_type' => 'nullable|string',
            'items.*.discount_value' => 'nullable|numeric',
        ]);

        // Если date не пришел, устанавливаем текущую дату
        if (empty($validated['date'])) {
            $validated['date'] = now();
        }

        $sale = Sale::create($validated);

        // Списываем количество товара (разрешён отрицательный остаток)
        foreach ($validated['items'] as $item) {
            Product::where('id', $item['product_id'])->decrement('quantity', (int) $item['quantity']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Продажа успешно создана',
            'data' => $sale,
        ], 201);
    }

    /**
     * Обновить продажу
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $sale = Sale::findOrFail($id);

        $validated = $request->validate([
            'cashier_id' => 'nullable|integer',
            'shift_id' => 'nullable|integer',
            'receipt_number' => 'nullable|string',
            'total_price' => 'nullable|numeric',
            'total_qty' => 'nullable|numeric',
            'items' => 'nullable|array',
            'items.*.product_id' => 'required|integer',
            'items.*.name_snapshot' => 'required|string',
            'items.*.price' => 'required|numeric',
            'items.*.quantity' => 'required|numeric',
            'items.*.discount_type' => 'nullable|string',
            'items.*.discount_value' => 'nullable|numeric',
        ]);

        if (isset($validated['items'])) {
            // Возвращаем количество по старым позициям
            foreach ($sale->items as $oldItem) {
                Product::where('id', $oldItem['product_id'])->increment('quantity', (int) $oldItem['quantity']);
            }
            // Списываем по новым позициям (разрешён отрицательный остаток)
            foreach ($validated['items'] as $item) {
                Product::where('id', $item['product_id'])->decrement('quantity', (int) $item['quantity']);
            }
        }

        $sale->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Продажа успешно обновлена',
            'data' => $sale->fresh(),
        ]);
    }

    /**
     * Удалить продажу
     */
    public function destroy(int $id): JsonResponse
    {
        $sale = Sale::findOrFail($id);

        // Возвращаем количество товара на склад
        foreach ($sale->items as $item) {
            Product::where('id', $item['product_id'])->increment('quantity', (int) $item['quantity']);
        }

        $sale->delete();

        return response()->json([
            'success' => true,
            'message' => 'Продажа успешно удалена',
        ]);
    }

    /**
     * Получить чек по продаже
     *
     * @param int $id ID продажи
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function receipt(int $id, Request $request)
    {
        $sale = Sale::findOrFail($id);
        $format = $request->get('format', 'json');

        $receiptService = new ReceiptService();

        switch ($format) {
            case 'html':
                $html = $receiptService->generateHtml($sale);
                return response($html)
                    ->header('Content-Type', 'text/html');

            case 'pdf':
                $pdf = $receiptService->generatePdf($sale);
                return $pdf->download("receipt-{$sale->receipt_number}.pdf");

            case 'pdf-inline':
                $pdf = $receiptService->generatePdf($sale);
                return $pdf->stream("receipt-{$sale->receipt_number}.pdf");

            case 'text':
                $text = $receiptService->generateText($sale);
                return response($text)
                    ->header('Content-Type', 'text/plain; charset=utf-8');

            case 'json':
            default:
                $data = $receiptService->generateJson($sale);
                return response()->json([
                    'success' => true,
                    'data' => $data,
                ]);
        }
    }
}
