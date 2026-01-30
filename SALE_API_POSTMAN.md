# 📬 Postman Collection для Sale API

## Импорт в Postman

Скопируйте JSON ниже и импортируйте в Postman (File → Import → Raw text)

```json
{
  "info": {
    "name": "AFoods - Sale API",
    "description": "Коллекция для тестирования Sale API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Sales",
      "item": [
        {
          "name": "1. Получить список продаж",
          "request": {
            "method": "GET",
            "header": [],
            "url": {
              "raw": "{{base_url}}/api/v1/sales-api?per_page=20&sort_by=date&sort_order=desc",
              "host": ["{{base_url}}"],
              "path": ["api", "v1", "sales-api"],
              "query": [
                {"key": "per_page", "value": "20"},
                {"key": "sort_by", "value": "date"},
                {"key": "sort_order", "value": "desc"},
                {"key": "date_from", "value": "2026-01-01", "disabled": true},
                {"key": "date_to", "value": "2026-01-31", "disabled": true},
                {"key": "cashier_id", "value": "1", "disabled": true},
                {"key": "shift_id", "value": "1", "disabled": true},
                {"key": "shopper_id", "value": "1", "disabled": true},
                {"key": "receipt_number", "value": "RCP", "disabled": true}
              ]
            }
          }
        },
        {
          "name": "2. Получить продажу по ID",
          "request": {
            "method": "GET",
            "header": [],
            "url": {
              "raw": "{{base_url}}/api/v1/sales-api/1",
              "host": ["{{base_url}}"],
              "path": ["api", "v1", "sales-api", "1"]
            }
          }
        },
        {
          "name": "3. Создать продажу (простую)",
          "request": {
            "method": "POST",
            "header": [
              {"key": "Content-Type", "value": "application/json"}
            ],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"date\": \"2026-01-30 15:00:00\",\n  \"receipt_number\": \"RCP-2026-{{$randomInt}}\",\n  \"items\": [\n    {\n      \"product_id\": 1,\n      \"quantity\": 2,\n      \"price\": 1000\n    }\n  ]\n}"
            },
            "url": {
              "raw": "{{base_url}}/api/v1/sales-api",
              "host": ["{{base_url}}"],
              "path": ["api", "v1", "sales-api"]
            }
          }
        },
        {
          "name": "4. Создать продажу (полную)",
          "request": {
            "method": "POST",
            "header": [
              {"key": "Content-Type", "value": "application/json"}
            ],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"cashier_id\": 1,\n  \"shift_id\": 1,\n  \"shopper_id\": 1,\n  \"date\": \"2026-01-30 15:30:00\",\n  \"receipt_number\": \"RCP-FULL-{{$randomInt}}\",\n  \"items\": [\n    {\n      \"product_id\": 1,\n      \"quantity\": 3,\n      \"price\": 1200\n    },\n    {\n      \"product_id\": 2,\n      \"quantity\": 1,\n      \"price\": 850\n    },\n    {\n      \"product_id\": 3,\n      \"quantity\": 5,\n      \"price\": 350\n    }\n  ]\n}"
            },
            "url": {
              "raw": "{{base_url}}/api/v1/sales-api",
              "host": ["{{base_url}}"],
              "path": ["api", "v1", "sales-api"]
            }
          }
        },
        {
          "name": "5. Обновить продажу",
          "request": {
            "method": "PUT",
            "header": [
              {"key": "Content-Type", "value": "application/json"}
            ],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"receipt_number\": \"RCP-UPDATED-001\",\n  \"items\": [\n    {\n      \"product_id\": 1,\n      \"quantity\": 1,\n      \"price\": 1200\n    }\n  ]\n}"
            },
            "url": {
              "raw": "{{base_url}}/api/v1/sales-api/1",
              "host": ["{{base_url}}"],
              "path": ["api", "v1", "sales-api", "1"]
            }
          }
        },
        {
          "name": "6. Удалить продажу",
          "request": {
            "method": "DELETE",
            "header": [],
            "url": {
              "raw": "{{base_url}}/api/v1/sales-api/1",
              "host": ["{{base_url}}"],
              "path": ["api", "v1", "sales-api", "1"]
            }
          }
        },
        {
          "name": "7. Статистика продаж",
          "request": {
            "method": "GET",
            "header": [],
            "url": {
              "raw": "{{base_url}}/api/v1/sales-api-statistics?date_from=2026-01-01&date_to=2026-01-31",
              "host": ["{{base_url}}"],
              "path": ["api", "v1", "sales-api-statistics"],
              "query": [
                {"key": "date_from", "value": "2026-01-01"},
                {"key": "date_to", "value": "2026-01-31"},
                {"key": "cashier_id", "value": "1", "disabled": true},
                {"key": "shift_id", "value": "1", "disabled": true}
              ]
            }
          }
        }
      ]
    },
    {
      "name": "Фильтрация продаж",
      "item": [
        {
          "name": "По датам",
          "request": {
            "method": "GET",
            "header": [],
            "url": {
              "raw": "{{base_url}}/api/v1/sales-api?date_from=2026-01-25&date_to=2026-01-30",
              "host": ["{{base_url}}"],
              "path": ["api", "v1", "sales-api"],
              "query": [
                {"key": "date_from", "value": "2026-01-25"},
                {"key": "date_to", "value": "2026-01-30"}
              ]
            }
          }
        },
        {
          "name": "По кассиру",
          "request": {
            "method": "GET",
            "header": [],
            "url": {
              "raw": "{{base_url}}/api/v1/sales-api?cashier_id=1",
              "host": ["{{base_url}}"],
              "path": ["api", "v1", "sales-api"],
              "query": [
                {"key": "cashier_id", "value": "1"}
              ]
            }
          }
        },
        {
          "name": "По смене",
          "request": {
            "method": "GET",
            "header": [],
            "url": {
              "raw": "{{base_url}}/api/v1/sales-api?shift_id=5",
              "host": ["{{base_url}}"],
              "path": ["api", "v1", "sales-api"],
              "query": [
                {"key": "shift_id", "value": "5"}
              ]
            }
          }
        },
        {
          "name": "По номеру чека",
          "request": {
            "method": "GET",
            "header": [],
            "url": {
              "raw": "{{base_url}}/api/v1/sales-api?receipt_number=RCP-2026",
              "host": ["{{base_url}}"],
              "path": ["api", "v1", "sales-api"],
              "query": [
                {"key": "receipt_number", "value": "RCP-2026"}
              ]
            }
          }
        }
      ]
    },
    {
      "name": "Ошибки (для тестирования)",
      "item": [
        {
          "name": "Создать без обязательных полей",
          "request": {
            "method": "POST",
            "header": [
              {"key": "Content-Type", "value": "application/json"}
            ],
            "body": {
              "mode": "raw",
              "raw": "{}"
            },
            "url": {
              "raw": "{{base_url}}/api/v1/sales-api",
              "host": ["{{base_url}}"],
              "path": ["api", "v1", "sales-api"]
            }
          }
        },
        {
          "name": "Несуществующий товар",
          "request": {
            "method": "POST",
            "header": [
              {"key": "Content-Type", "value": "application/json"}
            ],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"date\": \"2026-01-30 15:00:00\",\n  \"receipt_number\": \"ERROR-001\",\n  \"items\": [\n    {\n      \"product_id\": 99999,\n      \"quantity\": 1,\n      \"price\": 1000\n    }\n  ]\n}"
            },
            "url": {
              "raw": "{{base_url}}/api/v1/sales-api",
              "host": ["{{base_url}}"],
              "path": ["api", "v1", "sales-api"]
            }
          }
        },
        {
          "name": "Недостаточно товара",
          "request": {
            "method": "POST",
            "header": [
              {"key": "Content-Type", "value": "application/json"}
            ],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"date\": \"2026-01-30 15:00:00\",\n  \"receipt_number\": \"ERROR-002\",\n  \"items\": [\n    {\n      \"product_id\": 1,\n      \"quantity\": 999999,\n      \"price\": 1000\n    }\n  ]\n}"
            },
            "url": {
              "raw": "{{base_url}}/api/v1/sales-api",
              "host": ["{{base_url}}"],
              "path": ["api", "v1", "sales-api"]
            }
          }
        },
        {
          "name": "Дубликат номера чека",
          "request": {
            "method": "POST",
            "header": [
              {"key": "Content-Type", "value": "application/json"}
            ],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"date\": \"2026-01-30 15:00:00\",\n  \"receipt_number\": \"DUPLICATE-001\",\n  \"items\": [\n    {\n      \"product_id\": 1,\n      \"quantity\": 1,\n      \"price\": 1000\n    }\n  ]\n}"
            },
            "url": {
              "raw": "{{base_url}}/api/v1/sales-api",
              "host": ["{{base_url}}"],
              "path": ["api", "v1", "sales-api"]
            }
          }
        },
        {
          "name": "Получить несуществующую продажу",
          "request": {
            "method": "GET",
            "header": [],
            "url": {
              "raw": "{{base_url}}/api/v1/sales-api/99999",
              "host": ["{{base_url}}"],
              "path": ["api", "v1", "sales-api", "99999"]
            }
          }
        }
      ]
    }
  ],
  "variable": [
    {
      "key": "base_url",
      "value": "http://localhost",
      "type": "string"
    }
  ]
}
```

---

## Переменные окружения

После импорта настройте переменную окружения:

- `base_url` - адрес вашего сервера (например: `http://localhost` или `https://yourdomain.com`)

---

## Сценарии тестирования

### Сценарий 1: Полный цикл продажи
1. **Создать продажу** - "3. Создать продажу (простую)"
2. **Получить созданную продажу** - "2. Получить продажу по ID" (подставить ID)
3. **Обновить продажу** - "5. Обновить продажу" (подставить ID)
4. **Удалить продажу** - "6. Удалить продажу" (подставить ID)

### Сценарий 2: Тестирование фильтров
1. Создать несколько продаж с разными датами
2. Протестировать все фильтры из папки "Фильтрация продаж"

### Сценарий 3: Тестирование ошибок
Запустить все запросы из папки "Ошибки (для тестирования)"

Ожидаемые результаты:
- Все запросы должны вернуть 422
- Сообщения об ошибках должны быть понятными

---

## Примеры cURL команд

### Создание продажи
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
      {"product_id": 2, "quantity": 1, "price": 500}
    ]
  }'
```

### Получение списка продаж
```bash
curl "http://localhost/api/v1/sales-api?date_from=2026-01-01&date_to=2026-01-31&per_page=50"
```

### Получение продажи с ID
```bash
curl http://localhost/api/v1/sales-api/1
```

### Обновление продажи
```bash
curl -X PUT http://localhost/api/v1/sales-api/1 \
  -H "Content-Type: application/json" \
  -d '{
    "receipt_number": "RCP-UPDATED-001"
  }'
```

### Удаление продажи
```bash
curl -X DELETE http://localhost/api/v1/sales-api/1
```

### Получение статистики
```bash
curl "http://localhost/api/v1/sales-api-statistics?date_from=2026-01-01&date_to=2026-01-31&cashier_id=1"
```

---

## Автоматизация тестирования

### Newman (CLI для Postman)

Установка:
```bash
npm install -g newman
```

Запуск коллекции:
```bash
newman run postman_collection.json -e environment.json
```

### Создание environment.json
```json
{
  "name": "Local Environment",
  "values": [
    {
      "key": "base_url",
      "value": "http://localhost",
      "enabled": true
    }
  ]
}
```

---

## Тестовые данные

### Сгенерировать тестовые продажи

```bash
php artisan tinker
```

```php
use App\Models\Sale;
use App\Models\Product;

// Создать 10 тестовых продаж
for ($i = 1; $i <= 10; $i++) {
    Sale::create([
        'cashier_id' => rand(1, 3),
        'shift_id' => 1,
        'shopper_id' => rand(1, 5),
        'date' => now()->subDays(rand(0, 30)),
        'receipt_number' => 'TEST-' . str_pad($i, 4, '0', STR_PAD_LEFT),
        'items' => [
            [
                'product_id' => 1,
                'quantity' => rand(1, 5),
                'price' => 1000,
            ],
            [
                'product_id' => 2,
                'quantity' => rand(1, 3),
                'price' => 500,
            ],
        ],
        'total_price' => rand(1000, 10000),
    ]);
}
```

---

## Ожидаемые HTTP коды

| Код | Ситуация                           | Пример                              |
|-----|------------------------------------|-------------------------------------|
| 200 | Успешный GET/PUT/DELETE            | Продажа получена/обновлена/удалена  |
| 201 | Успешное создание (POST)           | Продажа создана                     |
| 404 | Ресурс не найден                   | GET /sales-api/99999                |
| 422 | Ошибка валидации                   | Отсутствует обязательное поле       |
| 500 | Ошибка сервера                     | Ошибка в коде                       |

---

## Checklist для тестирования

### Функциональность
- [ ] Создание продажи работает
- [ ] Получение списка работает
- [ ] Фильтрация по датам работает
- [ ] Пагинация работает
- [ ] Сортировка работает
- [ ] Обновление работает
- [ ] Удаление работает
- [ ] Статистика работает

### Валидация
- [ ] Проверка обязательных полей
- [ ] Проверка существования product_id
- [ ] Проверка существования cashier_id (после исправлений)
- [ ] Проверка существования shift_id (после исправлений)
- [ ] Проверка существования shopper_id (после исправлений)
- [ ] Проверка уникальности receipt_number (после исправлений)
- [ ] Проверка наличия товара на складе (после исправлений)

### Бизнес-логика
- [ ] total_price рассчитывается автоматически (после исправлений)
- [ ] Товар списывается со склада (после исправлений)
- [ ] Товар возвращается при удалении (после исправлений)
- [ ] Связи shopper/cashier/shift загружаются (после исправлений)

---

**Готово к использованию!** 🎯
