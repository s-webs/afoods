# 📋 Сводка по Sale API

## ✅ Что работает

Sale API полностью функционален и включает все стандартные операции:

1. **GET /api/v1/sales-api** - получение списка с фильтрацией по датам
2. **GET /api/v1/sales-api/{id}** - получение конкретной продажи
3. **POST /api/v1/sales-api** - создание новой продажи
4. **PUT/PATCH /api/v1/sales-api/{id}** - обновление продажи
5. **DELETE /api/v1/sales-api/{id}** - удаление продажи

---

## ❌ Найденные проблемы

### Критические
1. **Отсутствуют связи в модели Sale**
   - Нет `cashier()` и `shift()` отношений
   - Есть только `shopper()`

2. **Неправильные FK в миграции**
   - Используется `integer()` вместо `foreignId()`
   - Нет каскадного удаления
   - Нет автоматических индексов

### Средние
3. **Нет валидации для cashier_id, shift_id, shopper_id**
   - Эти поля не проверяются при создании/обновлении

4. **Нет проверки total_price**
   - Можно указать неправильную сумму

5. **Нет проверки наличия товара на складе**
   - Можно продать больше, чем есть

### Незначительные
6. **Нет eager loading** - может быть N+1 запросы
7. **Нет фильтров** по cashier_id, shift_id, shopper_id
8. **Нет уникальности** для receipt_number

---

## 📁 Созданные файлы

1. **API.md** - обновлен с полной документацией Sale API
2. **SALE_API_ANALYSIS.md** - детальный анализ всех проблем
3. **SALE_API_FIXES.md** - готовые решения для копирования

---

## 🚀 Быстрый старт для исправления

### Шаг 1: Обновите модель Sale
Добавьте два новых метода в `app/Models/Sale.php`:

```php
public function cashier(): BelongsTo
{
    return $this->belongsTo(Cashier::class);
}

public function shift(): BelongsTo
{
    return $this->belongsTo(Shift::class);
}
```

### Шаг 2: Создайте миграцию для FK
```bash
php artisan make:migration update_sales_table_foreign_keys
```

Код миграции смотрите в **SALE_API_FIXES.md**

### Шаг 3: Обновите контроллер
Замените `app/Http/Controllers/Api/SaleApiController.php` кодом из **SALE_API_FIXES.md**

---

## 📊 Примеры использования

### Создать продажу
```bash
curl -X POST http://localhost/api/v1/sales-api \
  -H "Content-Type: application/json" \
  -d '{
    "cashier_id": 1,
    "shift_id": 1,
    "date": "2026-01-30 15:00:00",
    "receipt_number": "RCP-001",
    "items": [
      {"product_id": 1, "quantity": 2, "price": 1000},
      {"product_id": 3, "quantity": 1, "price": 500}
    ]
  }'
```

### Получить продажи за сегодня
```bash
curl http://localhost/api/v1/sales-api?date_from=2026-01-30&date_to=2026-01-30
```

### Получить статистику
```bash
curl http://localhost/api/v1/sales-api-statistics?date_from=2026-01-01&date_to=2026-01-31
```

---

## 💡 Рекомендации
- Применить все исправления из **SALE_API_FIXES.md**
- Протестировать создание продаж с разными сценариями
- Добавить middleware для аутентификации API
- Рассмотреть добавление rate limiting
