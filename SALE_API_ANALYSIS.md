# Анализ API для управления продажами (Sales)

## Дата анализа: 30 января 2026

---

## Обзор

API для управления продажами реализован в контроллере `SaleApiController` и предоставляет стандартные CRUD операции:
- Получение списка продаж с фильтрацией и пагинацией
- Получение конкретной продажи по ID
- Создание новой продажи
- Обновление существующей продажи
- Удаление продажи

---

## Структура базы данных

### Таблица `sales`
```sql
- id (bigint, primary key)
- cashier_id (integer, nullable)
- shift_id (integer, nullable)
- shopper_id (integer, nullable, indexed)
- date (datetime)
- receipt_number (text)
- items (json) - массив товаров в продаже
- total_price (integer) - общая сумма в копейках
- created_at (timestamp)
- updated_at (timestamp)
```

### Формат поля `items`
```json
[
  {
    "product_id": 15,
    "quantity": 2,
    "price": 1000
  }
]
```

---

## Модель Sale

### Fillable поля
```php
'cashier_id', 'shift_id', 'shopper_id', 'date', 
'receipt_number', 'items', 'total_price'
```

### Casts
```php
'items' => 'array',
'date' => 'datetime',
'total_price' => 'integer'
```

### Связи (Relations)
- `shopper()` - BelongsTo Shopper ✅
- `cashier()` - **отсутствует** ❌
- `shift()` - **отсутствует** ❌

---

## Найденные проблемы

### 🔴 Критические проблемы

#### 1. Отсутствуют связи в модели Sale
**Проблема:**
В модели `Sale` определено только отношение `shopper()`, но отсутствуют связи:
- `cashier()` - для связи с таблицей `cashiers`
- `shift()` - для связи с таблицей `shifts`

**Код текущий:**
```php
// app/Models/Sale.php
public function shopper(): BelongsTo
{
    return $this->belongsTo(Shopper::class);
}
```

**Рекомендуемое исправление:**
```php
// Добавить в app/Models/Sale.php
public function cashier(): BelongsTo
{
    return $this->belongsTo(Cashier::class);
}

public function shift(): BelongsTo
{
    return $this->belongsTo(Shift::class);
}
```

**Влияние:** Средняя важность
- Невозможно удобно получать связанные данные кассира и смены
- Нельзя использовать eager loading для оптимизации запросов

---

#### 2. Миграция использует integer вместо foreignId
**Проблема:**
В миграции `create_sales_table.php` используется `integer()` для внешних ключей вместо `foreignId()`.

**Код текущий:**
```php
$table->integer('cashier_id')->nullable();
$table->integer('shift_id')->nullable();
$table->integer('shopper_id')->nullable()->index();
```

**Рекомендуемое исправление:**
```php
$table->foreignId('cashier_id')->nullable()->constrained()->nullOnDelete();
$table->foreignId('shift_id')->nullable()->constrained()->nullOnDelete();
$table->foreignId('shopper_id')->nullable()->constrained()->nullOnDelete();
```

**Влияние:** Средняя важность
- Отсутствует referential integrity на уровне БД
- При удалении кассира/смены/покупателя связанные продажи останутся с некорректными ID
- Нет автоматических индексов для FK (только для shopper_id есть ручной индекс)

**Дополнительная проблема:**
- Аналогичная проблема в миграции `create_cashiers_table.php` для поля `user_id`:
  ```php
  // Текущий код:
  $table->integer('user_id')->unsigned()->nullable();
  
  // Должно быть:
  $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
  ```

---

### 🟡 Средние проблемы

#### 3. Валидация не включает поля cashier_id, shift_id, shopper_id
**Проблема:**
В контроллере `SaleApiController` методы `store()` и `update()` не валидируют поля:
- `cashier_id`
- `shift_id`
- `shopper_id`

**Текущий код:**
```php
$validated = $request->validate([
    'date' => ['required', 'date'],
    'receipt_number' => ['required', 'string', 'max:255'],
    'items' => ['required', 'array'],
    // ... cashier_id, shift_id, shopper_id отсутствуют
]);
```

**Рекомендуемое исправление:**
```php
$validated = $request->validate([
    'cashier_id' => ['nullable', 'integer', 'exists:cashiers,id'],
    'shift_id' => ['nullable', 'integer', 'exists:shifts,id'],
    'shopper_id' => ['nullable', 'integer', 'exists:shoppers,id'],
    'date' => ['required', 'date'],
    'receipt_number' => ['required', 'string', 'max:255'],
    'items' => ['required', 'array'],
    'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
    'items.*.quantity' => ['required', 'integer', 'min:1'],
    'items.*.price' => ['required', 'integer', 'min:0'],
    'total_price' => ['required', 'integer', 'min:0'],
]);
```

**Влияние:** Средняя важность
- Эти поля можно устанавливать только через прямое обращение к модели, минуя API
- Нет проверки существования связанных записей при создании через API

---

#### 4. Отсутствует проверка согласованности total_price
**Проблема:**
API принимает `total_price` как параметр, но не проверяет, соответствует ли он сумме всех товаров в `items`.

**Пример проблемы:**
```json
{
  "items": [
    {"product_id": 1, "quantity": 2, "price": 1000},
    {"product_id": 2, "quantity": 1, "price": 500}
  ],
  "total_price": 9999999  // Несоответствует реальной сумме 2500
}
```

**Рекомендуемые варианты исправления:**

**Вариант 1: Автоматический расчет (рекомендуется)**
```php
// В SaleApiController::store()
$validated = $request->validate([...]);

// Автоматически рассчитываем total_price
$calculatedTotal = collect($validated['items'])->sum(function ($item) {
    return $item['quantity'] * $item['price'];
});

$validated['total_price'] = $calculatedTotal;

$sale = Sale::create($validated);
```

**Вариант 2: Валидация соответствия**
```php
// Создать кастомное правило валидации
$request->validate([
    // ... другие поля
    'total_price' => [
        'required', 
        'integer', 
        'min:0',
        function ($attribute, $value, $fail) use ($request) {
            $calculated = collect($request->items)->sum(fn($item) => 
                $item['quantity'] * $item['price']
            );
            
            if ($value != $calculated) {
                $fail("Total price ($value) does not match calculated sum ($calculated)");
            }
        }
    ],
]);
```

**Влияние:** Средняя важность
- Возможность создания записей с некорректной суммой
- Риск финансовых расхождений

---

### 🟢 Незначительные проблемы и улучшения

#### 5. Отсутствует eager loading в методе index()
**Проблема:**
При получении списка продаж не загружаются связанные данные.

**Текущий код:**
```php
$sales = $query->paginate($perPage);
```

**Рекомендуемое улучшение:**
```php
$sales = $query->with(['shopper', 'cashier', 'shift'])->paginate($perPage);
```

**Влияние:** Низкая важность, но улучшает производительность

---

#### 6. Нет фильтрации по cashier_id, shift_id, shopper_id
**Проблема:**
В методе `index()` есть фильтрация только по датам, но нет возможности фильтровать по:
- кассиру (cashier_id)
- смене (shift_id)
- покупателю (shopper_id)

**Рекомендуемое добавление:**
```php
public function index(Request $request): JsonResponse
{
    $query = Sale::query();

    // Существующие фильтры по датам
    if ($request->has('date_from')) {
        $query->whereDate('date', '>=', $request->date_from);
    }
    if ($request->has('date_to')) {
        $query->whereDate('date', '<=', $request->date_to);
    }

    // Новые фильтры
    if ($request->has('cashier_id')) {
        $query->where('cashier_id', $request->cashier_id);
    }
    if ($request->has('shift_id')) {
        $query->where('shift_id', $request->shift_id);
    }
    if ($request->has('shopper_id')) {
        $query->where('shopper_id', $request->shopper_id);
    }
    
    // Поиск по номеру чека
    if ($request->has('receipt_number')) {
        $query->where('receipt_number', 'LIKE', '%' . $request->receipt_number . '%');
    }

    // Остальной код...
}
```

**Влияние:** Низкая важность, но полезно для бизнес-логики

---

#### 7. Нет валидации уникальности receipt_number
**Проблема:**
Номер чека (`receipt_number`) должен быть уникальным, но это не проверяется.

**Рекомендуемое исправление:**
```php
// В миграции
$table->text('receipt_number')->unique();

// В валидации контроллера
'receipt_number' => ['required', 'string', 'max:255', 'unique:sales,receipt_number'],

// Для update
'receipt_number' => ['sometimes', 'required', 'string', 'max:255', 
                     'unique:sales,receipt_number,' . $id],
```

**Влияние:** Низкая важность, зависит от бизнес-требований

---

#### 8. Отсутствует проверка наличия товара на складе
**Проблема:**
При создании продажи не проверяется, достаточно ли товара на складе (поле `quantity` в таблице `products`).

**Рекомендуемое добавление:**
```php
// В SaleApiController::store()
foreach ($validated['items'] as $item) {
    $product = Product::findOrFail($item['product_id']);
    
    if ($product->quantity < $item['quantity']) {
        return response()->json([
            'success' => false,
            'message' => "Недостаточно товара на складе. Товар: {$product->name}, доступно: {$product->quantity}",
        ], 422);
    }
}

// После создания продажи - уменьшить количество товара
foreach ($validated['items'] as $item) {
    Product::find($item['product_id'])->decrement('quantity', $item['quantity']);
}
```

**Влияние:** Критично для реального бизнеса, но зависит от требований

---

## Рекомендации по приоритетам исправлений

### Высокий приоритет
1. ✅ Добавить связи `cashier()` и `shift()` в модель Sale
2. ✅ Исправить миграцию для использования foreignId
3. ✅ Добавить проверку/расчет total_price
4. ✅ Добавить валидацию cashier_id, shift_id, shopper_id

### Средний приоритет
5. Добавить проверку наличия товара на складе
6. Добавить уникальность receipt_number
7. Добавить eager loading для связанных моделей

### Низкий приоритет
8. Добавить дополнительные фильтры в index()
9. Документировать бизнес-процесс работы с продажами

---

## Примеры тестовых запросов

### Создание продажи (корректный запрос)
```bash
POST /api/v1/sales-api
Content-Type: application/json

{
  "cashier_id": 1,
  "shift_id": 5,
  "shopper_id": 10,
  "date": "2026-01-30 14:25:00",
  "receipt_number": "RCP-2026-002",
  "items": [
    {
      "product_id": 15,
      "quantity": 3,
      "price": 1200
    },
    {
      "product_id": 8,
      "quantity": 1,
      "price": 850
    }
  ],
  "total_price": 4450
}
```

### Фильтрация продаж за период
```bash
GET /api/v1/sales-api?date_from=2026-01-01&date_to=2026-01-31&sort_by=total_price&sort_order=desc&per_page=50
```

### Получение продажи с загрузкой покупателя
```bash
GET /api/v1/sales-api/1
```

---

## Дополнительные улучшения

### 1. Добавить эндпоинт статистики
```php
// GET /api/v1/sales-api/statistics
public function statistics(Request $request): JsonResponse
{
    $query = Sale::query();
    
    if ($request->has('date_from')) {
        $query->whereDate('date', '>=', $request->date_from);
    }
    if ($request->has('date_to')) {
        $query->whereDate('date', '<=', $request->date_to);
    }
    
    $stats = [
        'total_sales' => $query->count(),
        'total_revenue' => $query->sum('total_price'),
        'average_sale' => $query->avg('total_price'),
        'max_sale' => $query->max('total_price'),
        'min_sale' => $query->min('total_price'),
    ];
    
    return response()->json([
        'success' => true,
        'data' => $stats,
    ]);
}
```

### 2. Добавить экспорт в Excel/CSV
```php
// GET /api/v1/sales-api/export?format=csv&date_from=2026-01-01&date_to=2026-01-31
public function export(Request $request)
{
    // Реализация экспорта
}
```

### 3. Добавить возврат товара (refund)
```php
// POST /api/v1/sales-api/{id}/refund
public function refund(int $id, Request $request): JsonResponse
{
    // Реализация возврата
}
```

---

## Заключение

API для управления продажами в целом реализован корректно, но требует доработки в следующих областях:
1. Добавление недостающих связей в модели
2. Исправление миграции для использования FK
3. Валидация дополнительных полей
4. Проверка согласованности данных (total_price, наличие товара)

После внесения рекомендуемых исправлений API будет полностью готов к использованию в production.
