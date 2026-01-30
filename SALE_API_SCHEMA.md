# Схема данных Sale API

## Диаграмма связей

```
┌─────────────┐
│   Users     │
└──────┬──────┘
       │
       ├──────────────────┐
       │                  │
       ▼                  ▼
┌─────────────┐    ┌─────────────┐
│  Cashiers   │    │  Shoppers   │
│             │    │             │
│ • user_id   │    │ • user_id   │
│ • name      │    │ • phone     │
│ • uuid      │    │ • addresses │
│ • device_id │    └──────┬──────┘
│ • enabled   │           │
└──────┬──────┘           │
       │                  │
       │    ┌─────────┐   │
       │    │ Shifts  │   │
       │    │         │   │
       │    │ • opened│   │
       │    │ • closed│   │
       │    └────┬────┘   │
       │         │        │
       └────┐    │    ┐───┘
            │    │    │
            ▼    ▼    ▼
       ┌──────────────────┐
       │      Sales       │
       │                  │
       │ • cashier_id  ◄──┤
       │ • shift_id    ◄──┤
       │ • shopper_id  ◄──┤
       │ • date           │
       │ • receipt_number │
       │ • items (JSON)   │
       │ • total_price    │
       └────────┬─────────┘
                │
                │ items[] содержит:
                │ ┌──────────────┐
                └►│ • product_id │
                  │ • quantity   │
                  │ • price      │
                  └──────┬───────┘
                         │
                         ▼
                  ┌─────────────┐
                  │  Products   │
                  │             │
                  │ • name      │
                  │ • barcode   │
                  │ • quantity  │
                  │ • price_*   │
                  └─────────────┘
```

---

## Структура таблицы Sales

```sql
CREATE TABLE sales (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    
    -- Внешние ключи
    cashier_id BIGINT UNSIGNED NULL,     -- FK к таблице cashiers
    shift_id BIGINT UNSIGNED NULL,       -- FK к таблице shifts
    shopper_id BIGINT UNSIGNED NULL,     -- FK к таблице shoppers
    
    -- Данные о продаже
    date DATETIME NOT NULL,              -- Дата и время продажи
    receipt_number TEXT NOT NULL,        -- Номер чека (рекомендуется UNIQUE)
    
    -- Товары и расчеты
    items JSON NOT NULL,                 -- Массив товаров в продаже
    total_price INT NOT NULL,            -- Общая сумма в копейках
    
    -- Временные метки
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    -- Индексы
    INDEX idx_shopper_id (shopper_id),
    INDEX idx_date (date),
    INDEX idx_cashier_id (cashier_id),
    INDEX idx_shift_id (shift_id)
);
```

---

## Формат JSON поля items

### Структура одного элемента
```json
{
  "product_id": 15,      // ID товара из таблицы products
  "quantity": 2,         // Количество единиц товара
  "price": 1000          // Цена за единицу в копейках (не за все количество!)
}
```

### Пример полного массива items
```json
[
  {
    "product_id": 1,
    "quantity": 3,
    "price": 1200
  },
  {
    "product_id": 5,
    "quantity": 1,
    "price": 2500
  },
  {
    "product_id": 12,
    "quantity": 2,
    "price": 750
  }
]
```

### Расчет total_price
```
total_price = Σ (item.quantity × item.price)

Для примера выше:
total_price = (3 × 1200) + (1 × 2500) + (2 × 750)
            = 3600 + 2500 + 1500
            = 7600 копеек (76.00 руб)
```

---

## Связи между таблицами

### Sale → Shopper (покупатель)
```php
// В Sale.php
public function shopper(): BelongsTo
{
    return $this->belongsTo(Shopper::class);
}

// Использование
$sale = Sale::with('shopper')->find(1);
echo $sale->shopper->phone; // +79001234567
```

### Sale → Cashier (кассир)
```php
// В Sale.php (нужно добавить!)
public function cashier(): BelongsTo
{
    return $this->belongsTo(Cashier::class);
}

// Использование
$sale = Sale::with('cashier')->find(1);
echo $sale->cashier->name; // "Иванов И.И."
```

### Sale → Shift (смена)
```php
// В Sale.php (нужно добавить!)
public function shift(): BelongsTo
{
    return $this->belongsTo(Shift::class);
}

// Использование
$sale = Sale::with('shift')->find(1);
echo $sale->shift->opened_at; // 2026-01-30 08:00:00
```

### Обратные связи
```php
// В Shopper.php (уже есть)
public function sales(): HasMany
{
    return $this->hasMany(Sale::class);
}

// В Cashier.php (нужно добавить!)
public function sales(): HasMany
{
    return $this->hasMany(Sale::class);
}

// В Shift.php (нужно добавить!)
public function sales(): HasMany
{
    return $this->hasMany(Sale::class);
}
```

---

## Бизнес-логика

### Процесс создания продажи

1. **Проверка данных**
   - Валидация всех полей
   - Проверка существования продуктов
   - Проверка существования cashier/shift/shopper (если указаны)

2. **Проверка наличия товара**
   - Для каждого товара в items проверить `product.quantity >= item.quantity`
   - Если недостаточно - вернуть ошибку 422

3. **Расчет суммы**
   - Автоматически рассчитать total_price из items
   - `total_price = Σ (item.quantity × item.price)`

4. **Создание записи**
   - Сохранить продажу в БД
   - Уменьшить quantity у каждого товара

5. **Возврат результата**
   - Вернуть созданную продажу с загруженными связями

### Процесс обновления продажи

1. **Найти продажу**
   - Если не найдена - вернуть 404

2. **Если обновляются items**
   - Вернуть старое количество товара на склад
   - Проверить наличие нового количества
   - Пересчитать total_price
   - Списать новое количество

3. **Обновить запись**
   - Применить изменения

### Процесс удаления продажи

1. **Найти продажу**
   - Если не найдена - вернуть 404

2. **Вернуть товар на склад**
   - Для каждого item увеличить product.quantity

3. **Удалить запись**
   - Мягкое или жесткое удаление (зависит от настроек)

---

## Типичные сценарии использования

### Сценарий 1: Продажа в магазине
```
1. Кассир открывает смену → POST /api/v1/shifts/open
2. Покупатель приходит с товарами
3. Кассир сканирует товары (получение информации) → GET /api/v1/products?barcode=...
4. Кассир создает продажу → POST /api/v1/sales-api
5. Система автоматически:
   - Рассчитывает сумму
   - Списывает товар со склада
   - Генерирует чек
```

### Сценарий 2: Отчет за смену
```
1. Менеджер запрашивает продажи за смену → GET /api/v1/sales-api?shift_id=5
2. Получает статистику → GET /api/v1/sales-api-statistics?shift_id=5
3. Закрывает смену → POST /api/v1/shifts/5/close
```

### Сценарий 3: Возврат товара
```
1. Находим продажу → GET /api/v1/sales-api?receipt_number=RCP-001
2. Удаляем продажу → DELETE /api/v1/sales-api/123
3. Система автоматически возвращает товар на склад
```

### Сценарий 4: Аналитика продаж
```
1. Продажи за месяц → GET /api/v1/sales-api?date_from=2026-01-01&date_to=2026-01-31
2. Статистика → GET /api/v1/sales-api-statistics?date_from=2026-01-01&date_to=2026-01-31
3. Топ кассиров → Группировка по cashier_id
```

---

## Расширенные возможности (для будущего)

### 1. Частичный возврат
```php
POST /api/v1/sales-api/{id}/partial-refund
{
  "items": [
    {"product_id": 1, "quantity": 1}  // Вернуть только 1 штуку товара 1
  ]
}
```

### 2. Отчеты
```php
GET /api/v1/sales-api/reports/daily?date=2026-01-30
GET /api/v1/sales-api/reports/monthly?month=2026-01
GET /api/v1/sales-api/reports/by-cashier?cashier_id=1
```

### 3. Экспорт
```php
GET /api/v1/sales-api/export?format=xlsx&date_from=2026-01-01&date_to=2026-01-31
GET /api/v1/sales-api/export?format=csv&shift_id=5
```

### 4. Webhook уведомления
```php
// При создании продажи отправлять webhook
POST https://your-service.com/webhooks/sale-created
{
  "sale_id": 123,
  "total_price": 5000,
  "date": "2026-01-30 15:00:00"
}
```

---

## Примеры интеграции

### JavaScript/Fetch
```javascript
// Создание продажи
async function createSale(saleData) {
  const response = await fetch('/api/v1/sales-api', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(saleData),
  });
  
  return await response.json();
}

// Использование
const result = await createSale({
  cashier_id: 1,
  shift_id: 1,
  date: '2026-01-30 15:00:00',
  receipt_number: 'RCP-001',
  items: [
    { product_id: 1, quantity: 2, price: 1000 },
  ],
});

console.log(result.data.total_price); // 2000
```

### Python/Requests
```python
import requests

def create_sale(sale_data):
    response = requests.post(
        'http://localhost/api/v1/sales-api',
        json=sale_data
    )
    return response.json()

# Использование
result = create_sale({
    'cashier_id': 1,
    'shift_id': 1,
    'date': '2026-01-30 15:00:00',
    'receipt_number': 'RCP-001',
    'items': [
        {'product_id': 1, 'quantity': 2, 'price': 1000},
    ],
})

print(result['data']['total_price'])  # 2000
```

### PHP/Guzzle
```php
use GuzzleHttp\Client;

$client = new Client(['base_uri' => 'http://localhost']);

$response = $client->post('/api/v1/sales-api', [
    'json' => [
        'cashier_id' => 1,
        'shift_id' => 1,
        'date' => '2026-01-30 15:00:00',
        'receipt_number' => 'RCP-001',
        'items' => [
            ['product_id' => 1, 'quantity' => 2, 'price' => 1000],
        ],
    ],
]);

$data = json_decode($response->getBody(), true);
echo $data['data']['total_price']; // 2000
```

---

## Безопасность

### Рекомендуемые меры

1. **Аутентификация**
```php
// В routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('sales-api', SaleApiController::class);
});
```

2. **Rate Limiting**
```php
Route::middleware(['throttle:api'])->group(function () {
    // API routes
});
```

3. **Права доступа**
```php
// В контроллере
public function destroy(int $id): JsonResponse
{
    $this->authorize('delete', Sale::class);
    // ...
}
```

4. **Валидация IP**
```php
// Только для доверенных IP (POS-терминалов)
Route::middleware(['check.ip'])->group(function () {
    Route::post('sales-api', [SaleApiController::class, 'store']);
});
```

---

## Производительность

### Оптимизация запросов

**Проблема N+1:**
```php
// Плохо
$sales = Sale::all();
foreach ($sales as $sale) {
    echo $sale->shopper->phone; // N+1 запрос
}

// Хорошо
$sales = Sale::with('shopper')->get();
foreach ($sales as $sale) {
    echo $sale->shopper->phone; // 1 запрос
}
```

**Eager loading нескольких связей:**
```php
$sales = Sale::with(['shopper', 'cashier', 'shift'])->get();
```

### Индексы для быстрого поиска

```sql
-- Рекомендуемые индексы
CREATE INDEX idx_sales_date ON sales(date);
CREATE INDEX idx_sales_cashier_id ON sales(cashier_id);
CREATE INDEX idx_sales_shift_id ON sales(shift_id);
CREATE INDEX idx_sales_shopper_id ON sales(shopper_id);
CREATE INDEX idx_sales_receipt_number ON sales(receipt_number(100));
CREATE INDEX idx_sales_created_at ON sales(created_at);
```

### Кэширование

```php
use Illuminate\Support\Facades\Cache;

// Кэширование статистики на 5 минут
public function statistics(Request $request): JsonResponse
{
    $cacheKey = 'sales_stats_' . md5(json_encode($request->all()));
    
    $stats = Cache::remember($cacheKey, 300, function () use ($request) {
        $query = Sale::query();
        // ... фильтры и расчеты
        return [
            'total_sales' => $query->count(),
            'total_revenue' => $query->sum('total_price'),
            // ...
        ];
    });
    
    return response()->json([
        'success' => true,
        'data' => $stats,
    ]);
}
```

---

## Мониторинг и логирование

### События для логирования

```php
use Illuminate\Support\Facades\Log;

// При создании продажи
Log::info('Sale created', [
    'sale_id' => $sale->id,
    'receipt_number' => $sale->receipt_number,
    'total_price' => $sale->total_price,
    'cashier_id' => $sale->cashier_id,
]);

// При ошибке наличия товара
Log::warning('Insufficient stock', [
    'product_id' => $product->id,
    'requested' => $quantity,
    'available' => $product->quantity,
]);

// При удалении продажи
Log::warning('Sale deleted', [
    'sale_id' => $sale->id,
    'receipt_number' => $sale->receipt_number,
    'items_returned' => count($sale->items),
]);
```

### Метрики

```php
// Отслеживание важных метрик
use Illuminate\Support\Facades\Cache;

Cache::increment('sales_total_count');
Cache::increment('sales_total_revenue', $sale->total_price);
Cache::increment('sales_items_sold', $totalItemsCount);
```

---

## Документация endpoints

### Базовый URL
```
http://yourdomain.com/api/v1/sales-api
```

### Список всех endpoints

| Метод  | URL                          | Описание                    | Auth |
|--------|------------------------------|-----------------------------|------|
| GET    | /sales-api                   | Список продаж               | Нет  |
| GET    | /sales-api/{id}              | Конкретная продажа          | Нет  |
| POST   | /sales-api                   | Создать продажу             | Нет  |
| PUT    | /sales-api/{id}              | Обновить продажу            | Нет  |
| DELETE | /sales-api/{id}              | Удалить продажу             | Нет  |
| GET    | /sales-api-statistics        | Статистика продаж           | Нет  |

**Примечание:** Для production рекомендуется добавить аутентификацию!

---

## Checklist готовности к production

- [ ] Все связи в моделях определены
- [ ] FK правильно настроены в миграциях
- [ ] Валидация всех полей работает
- [ ] Проверка наличия товара на складе
- [ ] Автоматический расчет total_price
- [ ] Eager loading для оптимизации
- [ ] Индексы на всех FK
- [ ] Уникальность receipt_number
- [ ] Аутентификация включена
- [ ] Rate limiting настроен
- [ ] Логирование работает
- [ ] Тесты написаны и проходят
- [ ] Документация актуальна
