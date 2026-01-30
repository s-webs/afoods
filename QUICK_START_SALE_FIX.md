# 🚀 Быстрое применение исправлений Sale API

## Время на исправление: ~75 минут

---

## Шаг 1: Обновить модель Sale (5 минут)

Откройте `app/Models/Sale.php` и добавьте после метода `shopper()`:

```php
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
```

---

## Шаг 2: Обновить модель Cashier (2 минуты)

Откройте `app/Models/Cashier.php` и добавьте:

```php
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Get all sales processed by this cashier.
 */
public function sales(): HasMany
{
    return $this->hasMany(Sale::class);
}
```

---

## Шаг 3: Обновить модель Shift (2 минуты)

Откройте `app/Models/Shift.php` и добавьте:

```php
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Get all sales made during this shift.
 */
public function sales(): HasMany
{
    return $this->hasMany(Sale::class);
}
```

---

## Шаг 4: Создать миграцию для FK (10 минут)

```bash
php artisan make:migration update_sales_table_foreign_keys
```

Содержимое миграции (скопируйте в созданный файл):

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['cashier_id', 'shift_id', 'shopper_id']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('cashier_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('shift_id')->nullable()->after('cashier_id')->constrained()->nullOnDelete();
            $table->foreignId('shopper_id')->nullable()->after('shift_id')->constrained()->nullOnDelete();
        });
    }

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

Примените миграцию:
```bash
php artisan migrate
```

---

## Шаг 5: Полностью заменить контроллер (30 минут)

**ВАЖНО:** Сделайте backup текущего файла!

```bash
cp app/Http/Controllers/Api/SaleApiController.php app/Http/Controllers/Api/SaleApiController.php.bak
```

Полностью замените содержимое `app/Http/Controllers/Api/SaleApiController.php` кодом из **SALE_API_FIXES.md** (раздел 2).

Основные изменения:
- ✅ Добавлена валидация cashier_id, shift_id, shopper_id
- ✅ Автоматический расчет total_price
- ✅ Проверка наличия товара на складе
- ✅ Списание товара со склада при создании
- ✅ Возврат товара на склад при удалении
- ✅ Eager loading для всех связей
- ✅ Дополнительные фильтры
- ✅ Уникальность receipt_number
- ✅ Метод statistics()

---

## Шаг 6: Добавить route для статистики (2 минуты)

Откройте `routes/api.php` и добавьте ПОСЛЕ блока sales-api:

```php
Route::prefix('v1')->group(function () {
    // ... существующие routes

    // API для продаж
    Route::apiResource('sales-api', SaleApiController::class)->only([
        'index', 'show', 'store', 'update', 'destroy'
    ]);
    
    // Добавьте эту строку:
    Route::get('sales-api-statistics', [SaleApiController::class, 'statistics']);
    
    // ... остальные routes
});
```

---

## Шаг 7: Тестирование (15 минут)

### 7.1 Создайте тестовый файл

```bash
php artisan make:test SaleApiTest
```

### 7.2 Скопируйте тесты

Скопируйте содержимое из **SALE_API_TESTS.md** в `tests/Feature/SaleApiTest.php`

### 7.3 Запустите тесты

```bash
# Все тесты Sale API
php artisan test --filter SaleApiTest

# Или конкретный тест
php artisan test --filter test_can_create_sale
```

---

## Шаг 8: Ручное тестирование (10 минут)

### Тест 1: Создание продажи
```bash
curl -X POST http://localhost/api/v1/sales-api \
  -H "Content-Type: application/json" \
  -d '{
    "cashier_id": 1,
    "shift_id": 1,
    "date": "2026-01-30 16:00:00",
    "receipt_number": "TEST-001",
    "items": [
      {"product_id": 1, "quantity": 2, "price": 1000}
    ]
  }'
```

**Проверьте:**
- ✅ Продажа создана с HTTP 201
- ✅ `total_price` автоматически = 2000
- ✅ Товар с ID=1 уменьшился на 2 единицы

### Тест 2: Попытка продажи больше, чем на складе
```bash
curl -X POST http://localhost/api/v1/sales-api \
  -H "Content-Type: application/json" \
  -d '{
    "date": "2026-01-30 16:00:00",
    "receipt_number": "TEST-002",
    "items": [
      {"product_id": 1, "quantity": 99999, "price": 1000}
    ]
  }'
```

**Проверьте:**
- ✅ Ошибка 422
- ✅ Сообщение "Недостаточно товара на складе"

### Тест 3: Получение статистики
```bash
curl "http://localhost/api/v1/sales-api-statistics?date_from=2026-01-01&date_to=2026-01-31"
```

**Проверьте:**
- ✅ HTTP 200
- ✅ Возвращаются: total_sales, total_revenue, average_sale, max_sale, min_sale

### Тест 4: Удаление продажи
```bash
# Сначала создайте продажу и запомните ID
# Затем удалите:
curl -X DELETE http://localhost/api/v1/sales-api/1
```

**Проверьте:**
- ✅ Продажа удалена
- ✅ Товар вернулся на склад

---

## Шаг 9: Проверка базы данных (5 минут)

### Проверьте FK в БД
```bash
php artisan tinker
```

```php
// В tinker выполните:
use Illuminate\Support\Facades\DB;

// Проверка FK
$fks = DB::select("
    SELECT 
        CONSTRAINT_NAME,
        COLUMN_NAME,
        REFERENCED_TABLE_NAME,
        REFERENCED_COLUMN_NAME
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE TABLE_NAME = 'sales'
    AND CONSTRAINT_SCHEMA = DATABASE()
    AND REFERENCED_TABLE_NAME IS NOT NULL
");

print_r($fks);
```

**Должны увидеть 3 FK:**
- sales_cashier_id_foreign → cashiers(id)
- sales_shift_id_foreign → shifts(id)
- sales_shopper_id_foreign → shoppers(id)

---

## ✅ Checklist завершения

- [ ] Модель Sale обновлена (добавлены связи cashier, shift)
- [ ] Модель Cashier обновлена (добавлена связь sales)
- [ ] Модель Shift обновлена (добавлена связь sales)
- [ ] Создана миграция update_sales_table_foreign_keys
- [ ] Миграция применена (`php artisan migrate`)
- [ ] FK проверены в БД
- [ ] Контроллер SaleApiController обновлен
- [ ] Route для statistics добавлен
- [ ] Тесты созданы
- [ ] Тесты запущены и прошли успешно
- [ ] Ручное тестирование выполнено
- [ ] Документация актуализирована

---

## 🆘 Что делать при ошибках

### Ошибка миграции: "Cannot drop column cashier_id"
**Причина:** В БД есть данные с заполненными cashier_id

**Решение:**
```bash
# Вариант 1: Очистить таблицу (только для dev!)
php artisan tinker
>>> App\Models\Sale::truncate();

# Вариант 2: Изменить миграцию для сохранения данных
# (не удалять колонки, а только добавить FK)
```

### Ошибка: "Class 'Product' not found"
**Причина:** Не добавлен use statement

**Решение:**
```php
// В начале SaleApiController.php
use App\Models\Product;
```

### Тесты не проходят
**Решение:**
```bash
# Очистить кеш
php artisan config:clear
php artisan cache:clear

# Пересоздать БД для тестов
php artisan migrate:fresh --env=testing
```

---

## 📞 Поддержка

При проблемах проверьте:
1. Логи Laravel: `storage/logs/laravel.log`
2. Версию PHP: должна быть >= 8.1
3. Расширения PHP: должен быть включен JSON и PDO

---

## 🎉 После применения всех исправлений

Sale API будет:
- ✅ Полностью функционален
- ✅ Безопасен (с правильными FK)
- ✅ Оптимизирован (с eager loading)
- ✅ Валиден (все проверки на месте)
- ✅ Готов к production (после добавления auth)

---

**Успехов в разработке!** 🚀
