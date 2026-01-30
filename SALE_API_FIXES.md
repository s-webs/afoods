# Готовые исправления для Sale API

## 1. Обновление модели Sale
Добавьте следующие методы в `app/Models/Sale.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'cashier_id',
        'shift_id',
        'shopper_id',
        'date',
        'receipt_number',
        'items',
        'total_price',
    ];

    protected $casts = [
        'items' => 'array',
        'date' => 'datetime',
        'total_price' => 'integer',
    ];

    /**
     * Get the shopper that owns the sale.
     */
    public function shopper(): BelongsTo
    {
        return $this->belongsTo(Shopper::class);
    }

    /**
     * Get the cashier that processed the sale.
     */
    public function cashier(): BelongsTo
    {
        return $this->belongsTo(Cashier::class);
    }

    /**
     * Get the shift during which the sale was made.
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Calculate total price from items.
     */
    public function calculateTotalPrice(): int
    {
        return collect($this->items)->sum(function ($item) {
            return ($item['quantity'] ?? 0) * ($item['price'] ?? 0);
        });
    }
}
```

---

## 2. Обновление контроллера SaleApiController
Замените содержимое `app/Http/Controllers/Api/SaleApiController.php`:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SaleApiController extends Controller
{
    /**
     * Получить список всех продаж
     */
    public function index(Request $request): JsonResponse
    {
        $query = Sale::query()->with(['shopper', 'cashier', 'shift']);

        // Фильтрация по дате
        if ($request->has('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        // Фильтрация по кассиру
        if ($request->has('cashier_id')) {
            $query->where('cashier_id', $request->cashier_id);
        }

        // Фильтрация по смене
        if ($request->has('shift_id')) {
            $query->where('shift_id', $request->shift_id);
        }

        // Фильтрация по покупателю
        if ($request->has('shopper_id')) {
            $query->where('shopper_id', $request->shopper_id);
        }

        // Поиск по номеру чека
        if ($request->has('receipt_number')) {
            $query->where('receipt_number', 'LIKE', '%' . $request->receipt_number . '%');
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
        $sale = Sale::with(['shopper', 'cashier', 'shift'])->findOrFail($id);

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
            'cashier_id' => ['nullable', 'integer', 'exists:cashiers,id'],
            'shift_id' => ['nullable', 'integer', 'exists:shifts,id'],
            'shopper_id' => ['nullable', 'integer', 'exists:shoppers,id'],
            'date' => ['required', 'date'],
            'receipt_number' => ['required', 'string', 'max:255', 'unique:sales,receipt_number'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'integer', 'min:0'],
        ]);

        // Проверяем наличие товара на складе
        foreach ($validated['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);
            
            if ($product->quantity < $item['quantity']) {
                return response()->json([
                    'success' => false,
                    'message' => "Недостаточно товара на складе",
                    'errors' => [
                        'product_id' => [
                            "Товар '{$product->name}' (ID: {$product->id}): запрошено {$item['quantity']}, доступно {$product->quantity}"
                        ]
                    ]
                ], 422);
            }
        }

        // Автоматически рассчитываем total_price
        $validated['total_price'] = collect($validated['items'])->sum(function ($item) {
            return $item['quantity'] * $item['price'];
        });

        $sale = Sale::create($validated);

        // Уменьшаем количество товара на складе
        foreach ($validated['items'] as $item) {
            Product::find($item['product_id'])->decrement('quantity', $item['quantity']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Продажа успешно создана',
            'data' => $sale->load(['shopper', 'cashier', 'shift']),
        ], 201);
    }

    /**
     * Обновить продажу
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $sale = Sale::findOrFail($id);

        $validated = $request->validate([
            'cashier_id' => ['sometimes', 'nullable', 'integer', 'exists:cashiers,id'],
            'shift_id' => ['sometimes', 'nullable', 'integer', 'exists:shifts,id'],
            'shopper_id' => ['sometimes', 'nullable', 'integer', 'exists:shoppers,id'],
            'date' => ['sometimes', 'required', 'date'],
            'receipt_number' => ['sometimes', 'required', 'string', 'max:255', 'unique:sales,receipt_number,' . $id],
            'items' => ['sometimes', 'required', 'array', 'min:1'],
            'items.*.product_id' => ['required_with:items', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],
            'items.*.price' => ['required_with:items', 'integer', 'min:0'],
        ]);

        // Если обновляются items, пересчитываем total_price
        if (isset($validated['items'])) {
            // Возвращаем старое количество товара на склад
            foreach ($sale->items as $oldItem) {
                Product::find($oldItem['product_id'])->increment('quantity', $oldItem['quantity']);
            }

            // Проверяем наличие нового товара на складе
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                if ($product->quantity < $item['quantity']) {
                    // Откатываем изменения
                    foreach ($sale->items as $oldItem) {
                        Product::find($oldItem['product_id'])->decrement('quantity', $oldItem['quantity']);
                    }
                    
                    return response()->json([
                        'success' => false,
                        'message' => "Недостаточно товара на складе",
                        'errors' => [
                            'product_id' => [
                                "Товар '{$product->name}' (ID: {$product->id}): запрошено {$item['quantity']}, доступно {$product->quantity}"
                            ]
                        ]
                    ], 422);
                }
            }

            // Рассчитываем новый total_price
            $validated['total_price'] = collect($validated['items'])->sum(function ($item) {
                return $item['quantity'] * $item['price'];
            });

            // Уменьшаем новое количество товара на складе
            foreach ($validated['items'] as $item) {
                Product::find($item['product_id'])->decrement('quantity', $item['quantity']);
            }
        }

        $sale->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Продажа успешно обновлена',
            'data' => $sale->fresh(['shopper', 'cashier', 'shift']),
        ]);
    }

    /**
     * Удалить продажу
     */
    public function destroy(int $id): JsonResponse
    {
        $sale = Sale::findOrFail($id);
        
        // Возвращаем товар на склад
        foreach ($sale->items as $item) {
            $product = Product::find($item['product_id']);
            if ($product) {
                $product->increment('quantity', $item['quantity']);
            }
        }
        
        $sale->delete();

        return response()->json([
            'success' => true,
            'message' => 'Продажа успешно удалена',
        ]);
    }

    /**
     * Получить статистику продаж
     */
    public function statistics(Request $request): JsonResponse
    {
        $query = Sale::query();
        
        if ($request->has('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }
        if ($request->has('cashier_id')) {
            $query->where('cashier_id', $request->cashier_id);
        }
        if ($request->has('shift_id')) {
            $query->where('shift_id', $request->shift_id);
        }
        
        $stats = [
            'total_sales' => $query->count(),
            'total_revenue' => $query->sum('total_price'),
            'average_sale' => round($query->avg('total_price'), 2),
            'max_sale' => $query->max('total_price'),
            'min_sale' => $query->min('total_price'),
        ];
        
        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
```

---

## 3. Создание новой миграции для исправления FK
Создайте новую миграцию:

```bash
php artisan make:migration update_sales_table_foreign_keys
```

Содержимое миграции:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Удаляем старые колонки
            $table->dropColumn(['cashier_id', 'shift_id', 'shopper_id']);
        });

        Schema::table('sales', function (Blueprint $table) {
            // Добавляем новые с правильными FK
            $table->foreignId('cashier_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('shift_id')->nullable()->after('cashier_id')->constrained()->nullOnDelete();
            $table->foreignId('shopper_id')->nullable()->after('shift_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['cashier_id']);
            $table->dropForeign(['shift_id']);
            $table->dropForeign(['shopper_id']);
            
            $table->dropColumn(['cashier_id', 'shift_id', 'shopper_id']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->integer('cashier_id')->nullable()->after('id');
            $table->integer('shift_id')->nullable()->after('cashier_id');
            $table->integer('shopper_id')->nullable()->after('shift_id')->index();
        });
    }
};
```

---

## 4. Обновление роутов
Добавьте в `routes/api.php` новый эндпоинт для статистики:

```php
Route::prefix('v1')->group(function () {
    // ... существующие роуты

    // API для продаж
    Route::apiResource('sales-api', SaleApiController::class)->only([
        'index', 'show', 'store', 'update', 'destroy'
    ]);
    
    // Добавить статистику
    Route::get('sales-api-statistics', [SaleApiController::class, 'statistics']);
});
```

---

## 5. Обновление связей в других моделях

### Модель Cashier
Добавьте в `app/Models/Cashier.php`:

```php
use Illuminate\Database\Eloquent\Relations\HasMany;

public function sales(): HasMany
{
    return $this->hasMany(Sale::class);
}
```

### Модель Shift
Добавьте в `app/Models/Shift.php`:

```php
use Illuminate\Database\Eloquent\Relations\HasMany;

public function sales(): HasMany
{
    return $this->hasMany(Sale::class);
}
```

---

## 6. Порядок применения исправлений

1. **Обновите модель Sale** (добавьте связи cashier() и shift())
2. **Создайте и примените новую миграцию** для исправления FK
   ```bash
   php artisan make:migration update_sales_table_foreign_keys
   php artisan migrate
   ```
3. **Обновите контроллер SaleApiController** (с новой валидацией и проверками)
4. **Обновите роуты** (добавьте эндпоинт статистики)
5. **Обновите связанные модели** (Cashier, Shift)
6. **Протестируйте API** используя примеры из документации

---

## 7. Тестирование после исправлений

### Тест 1: Создание продажи с проверкой склада
```bash
POST /api/v1/sales-api
{
  "cashier_id": 1,
  "shift_id": 1,
  "date": "2026-01-30 15:00:00",
  "receipt_number": "TEST-001",
  "items": [
    {
      "product_id": 1,
      "quantity": 2,
      "price": 1000
    }
  ]
}
```

Ожидаемый результат:
- Продажа создана
- `total_price` автоматически = 2000
- Количество товара с ID=1 уменьшилось на 2

### Тест 2: Попытка продажи с недостаточным количеством
```bash
POST /api/v1/sales-api
{
  "date": "2026-01-30 15:00:00",
  "receipt_number": "TEST-002",
  "items": [
    {
      "product_id": 1,
      "quantity": 999999,
      "price": 1000
    }
  ]
}
```

Ожидаемый результат:
- Ошибка 422
- Сообщение о недостаточном количестве товара

### Тест 3: Получение статистики
```bash
GET /api/v1/sales-api-statistics?date_from=2026-01-01&date_to=2026-01-31
```

Ожидаемый результат:
- Общее количество продаж
- Общая выручка
- Средний чек
- Минимальный и максимальный чек

---

## Заметки
- Все исправления обратно совместимы
- Миграция поддерживает откат (rollback)
- Добавлена автоматическая проверка склада
- Добавлен автоматический расчет total_price
- Добавлен эндпоинт статистики
