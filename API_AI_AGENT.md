# API Документация для ИИ-агента

> Документ предназначен для программного использования ИИ-агентами. Содержит полную спецификацию API продуктового магазина AFOODS.

## Базовые сведения

- **Базовый URL:** `{BASE_URL}/api`
- **Версия API:** v1
- **Формат данных:** JSON
- **Кодировка:** UTF-8
- **Авторизация:** Bearer token (Laravel Sanctum)

---

## 1. Аутентификация

### 1.1 Получение токена (логин)

**Перед вызовом защищённых эндпоинтов необходимо получить токен.**

| Параметр | Значение |
|----------|----------|
| Метод | POST |
| URL | `/api/v1/auth/login` |
| Авторизация | Не требуется |
| Content-Type | application/json |

**Тело запроса:**
```json
{
  "email": "string (required)",
  "password": "string (required)",
  "token_name": "string (optional, default: api-token)"
}
```

**Успешный ответ (200):**
```json
{
  "success": true,
  "message": "Успешная авторизация",
  "data": {
    "token": "1|plainTextToken...",
    "token_type": "Bearer",
    "user": {
      "id": 1,
      "name": "string",
      "email": "string"
    }
  }
}
```

**Использование токена:** Добавить заголовок `Authorization: Bearer {token}` во все защищённые запросы.

**Ошибки:**
- 422: Неверные учётные данные — `{"message": "...", "errors": {"email": ["Неверные учетные данные"]}}`

---

### 1.2 Выход (удалить текущий токен)

| Метод | POST | URL | `/api/v1/auth/logout` | Auth | Да |
|-------|------|-----|------------------------|------|-----|

**Ответ (200):** `{"success": true, "message": "Вы успешно вышли из системы"}`

---

### 1.3 Выход со всех устройств

| Метод | POST | URL | `/api/v1/auth/logout-all` | Auth | Да |
|-------|------|-----|---------------------------|------|-----|

---

### 1.4 Информация о текущем пользователе

| Метод | GET | URL | `/api/v1/auth/user` | Auth | Да |
|-------|-----|-----|---------------------|------|-----|

**Ответ (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "string",
    "email": "string",
    "moonshine_user_role_id": 1,
    "created_at": "2026-01-30T10:00:00.000000Z"
  }
}
```

---

## 2. Health Check

| Метод | GET | URL | `/api/health` | Auth | Нет |
|-------|-----|-----|---------------|------|-----|

**Ответ (200):**
```json
{
  "success": true,
  "message": "Server is healthy",
  "timestamp": "2026-02-03T12:00:00+00:00"
}
```

---

## 3. Товары (Products)

**Базовый путь:** `/api/v1/products-api`

### 3.1 Список товаров

| Метод | GET | Auth | Публичный (без токена) |
|-------|-----|------|-------------------------|

**Query-параметры:**
| Параметр | Тип | Обязательный | Описание |
|----------|-----|--------------|----------|
| category_id | integer | Нет | Фильтр по категории |
| search | string | Нет | Поиск по name или barcode |
| in_stock | boolean | Нет | true/false — фильтр по наличию (quantity > 0) |
| sort_by | string | Нет | id, name, price_amount, sale_price_amount, quantity, created_at |
| sort_order | string | Нет | asc, desc (default: desc) |
| per_page | integer | Нет | 1-100 (default: 15) |
| page | integer | Нет | Номер страницы |

**Ответ (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "category_id": 1,
      "name": "string",
      "barcode": "string",
      "price_amount": 1000,
      "sale_price_amount": 800,
      "quantity": 10,
      "category": {"id": 1, "name": "string"}
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75
  }
}
```

**Важно:** Цены в копейках (1000 = 10.00 руб).

---

### 3.2 Получить товар по ID

| Метод | GET | URL | `/api/v1/products-api/{id}` | Auth | Да |
|-------|-----|-----|-----------------------------|------|-----|

---

### 3.3 Создать товар

| Метод | POST | URL | `/api/v1/products-api` | Auth | Да |
|-------|------|-----|-------------------------|------|-----|

**Тело запроса:**
```json
{
  "category_id": "integer (optional)",
  "name": "string (required)",
  "barcode": "string (optional, unique)",
  "images": ["string"] (optional),
  "description": "string (optional)",
  "specs": {} (optional, object),
  "unit": "string (optional, default: pcs)",
  "price_amount": "integer (optional, default: 0)",
  "sale_price_amount": "integer (optional, default: 0)",
  "quantity": "integer (optional, default: 0)",
  "obj": {} (optional)
}
```

**Ответ (201):** `{"success": true, "message": "Товар успешно создан", "data": {...}}`

---

### 3.4 Обновить товар

| Метод | PUT/PATCH | URL | `/api/v1/products-api/{id}` | Auth | Да |
|-------|-----------|-----|-----------------------------|------|-----|

Поля как при создании (все опциональны при обновлении).

---

### 3.5 Удалить товар

| Метод | DELETE | URL | `/api/v1/products-api/{id}` | Auth | Да |
|-------|--------|-----|-----------------------------|------|-----|

---

### 3.6 Установить скидку на товар

| Метод | POST | URL | `/api/v1/products-api/{id}/discount` | Auth | Да |
|-------|------|-----|--------------------------------------|------|-----|

**Тело запроса:**
```json
{
  "discount_type": "percent | fixed (required)",
  "discount_value": "integer (required)"
}
```
- `percent`: 0-100 (15 = 15%)
- `fixed`: цена в копейках (sale_price_amount), должна быть ≤ price_amount

---

### 3.7 Массовая установка скидки

| Метод | POST | URL | `/api/v1/products-api/bulk-discount` | Auth | Да |
|-------|------|-----|--------------------------------------|------|-----|

**Тело запроса:**
```json
{
  "product_ids": [1, 2, 3],
  "discount_type": "percent | fixed",
  "discount_value": 20
}
```

---

### 3.8 Сбросить скидку

| Метод | DELETE | URL | `/api/v1/products-api/{id}/discount` | Auth | Да |
|-------|--------|-----|--------------------------------------|------|-----|

---

### 3.9 Массовый сброс скидок

| Метод | POST | URL | `/api/v1/products-api/bulk-remove-discount` | Auth | Да |
|-------|------|-----|---------------------------------------------|------|-----|

**Тело запроса:** `{"product_ids": [1, 2, 3]}`

---

## 4. Категории (Categories)

**Базовый путь:** `/api/v1/categories-api`

### 4.1 Список категорий

| Метод | GET | Auth | Да |
|-------|-----|------|-----|

**Query:** per_page (1-100), page

**Ответ:** data + pagination. Сортировка: sort_order asc, name asc.

---

### 4.2 Получить категорию по ID

| Метод | GET | URL | `/api/v1/categories-api/{id}` | Auth | Да |
|-------|-----|-----|-------------------------------|------|-----|

Включает products.

---

### 4.3 Создать категорию

| Метод | POST | Auth | Да |
|-------|------|------|-----|

**Тело:**
```json
{
  "name": "string (required)",
  "parent_id": "integer (optional)",
  "image": "string (optional)",
  "sort_order": "integer (optional, default: 0)"
}
```

---

### 4.4 Обновить категорию

| Метод | PUT/PATCH | URL | `/api/v1/categories-api/{id}` | Auth | Да |
|-------|-----------|-----|-------------------------------|------|-----|

---

### 4.5 Удалить категорию

| Метод | DELETE | URL | `/api/v1/categories-api/{id}` | Auth | Да |
|-------|--------|-----|-------------------------------|------|-----|

**Ошибка 409:** Категория содержит товары — удаление невозможно.

---

## 5. Кассиры (Cashiers)

**Базовый путь:** `/api/v1/cashiers-api`

### 5.1 Список кассиров — GET
### 5.2 Получить кассира — GET `/api/v1/cashiers-api/{id}`
### 5.3 Создать кассира — POST

**Тело:**
```json
{
  "name": "string (required)",
  "user_id": "integer (optional)",
  "uuid": "string (optional, unique)",
  "device_id": "string (optional)",
  "enabled": "boolean (optional, default: false)"
}
```

### 5.4 Обновить кассира — PUT/PATCH `/api/v1/cashiers-api/{id}`
### 5.5 Удалить кассира — DELETE `/api/v1/cashiers-api/{id}`

---

## 6. Продажи (Sales)

**Базовый путь:** `/api/v1/sales-api`

### 6.1 Список продаж

| Метод | GET | Auth | Да |
|-------|-----|------|-----|

**Query:**
| Параметр | Тип | Описание |
|----------|-----|----------|
| date_from | date (YYYY-MM-DD) | Фильтр по дате |
| date_to | date (YYYY-MM-DD) | Фильтр по дате |
| sort_by | string | id, date, total_price, created_at |
| sort_order | string | asc, desc |
| per_page | integer | 1-100 |
| page | integer | Номер страницы |

---

### 6.2 Получить продажу по ID

| Метод | GET | URL | `/api/v1/sales-api/{id}` | Auth | Да |
|-------|-----|-----|--------------------------|------|-----|

Включает shopper.

---

### 6.3 Создать продажу

| Метод | POST | URL | `/api/v1/sales-api` | Auth | Да |
|-------|------|-----|---------------------|------|-----|

**Тело запроса (ВАЖНО — точная схема):**
```json
{
  "cashier_id": "integer (optional)",
  "shift_id": "integer (optional)",
  "receipt_number": "string (required)",
  "total_price": "number (required)",
  "total_qty": "number (required)",
  "date": "string (optional, default: now)",
  "items": [
    {
      "product_id": "integer (required)",
      "name_snapshot": "string (required)",
      "price": "number (required)",
      "quantity": "number (required)",
      "discount_type": "string (optional)",
      "discount_value": "number (optional)"
    }
  ]
}
```

**Ключевые поля:**
- `name_snapshot` — снимок названия товара на момент продажи (обязательно)
- `total_qty` — общая суммарная величина количества по всем items
- Цены в копейках

**Пример:**
```json
{
  "receipt_number": "RCP-2026-001",
  "total_price": 2500,
  "total_qty": 3,
  "items": [
    {"product_id": 1, "name_snapshot": "Молоко 3.2%", "price": 1000, "quantity": 2},
    {"product_id": 2, "name_snapshot": "Хлеб", "price": 500, "quantity": 1}
  ]
}
```

---

### 6.4 Обновить продажу

| Метод | PUT/PATCH | URL | `/api/v1/sales-api/{id}` | Auth | Да |
|-------|-----------|-----|--------------------------|------|-----|

Поля как при создании (все опциональны).

---

### 6.5 Удалить продажу

| Метод | DELETE | URL | `/api/v1/sales-api/{id}` | Auth | Да |
|-------|--------|-----|--------------------------|------|-----|

---

### 6.6 Получить чек по продаже

| Метод | GET | URL | `/api/v1/sales-api/{id}/receipt` | Auth | Да |
|-------|-----|-----|----------------------------------|------|-----|

**Query:** `format` — json (default), html, pdf, pdf-inline, text

- **json** — данные чека для кассовых систем
- **html** — HTML-страница
- **pdf** — скачивание PDF
- **pdf-inline** — просмотр PDF в браузере
- **text** — текст для термопринтера (80мм)

---

## 7. Смены (Shifts)

**Базовый путь:** `/api/v1/shifts`

### 7.1 Список смен

| Метод | GET | Auth | Да |
|-------|-----|------|-----|

**Query:** per_page, page. Сортировка: opened_at desc.

---

### 7.2 Текущая смена

| Метод | GET | URL | `/api/v1/shifts/current` | Auth | Да |
|-------|-----|-----|--------------------------|------|-----|

**Ответ 404:** Нет открытой смены — `{"success": false, "message": "Нет открытой смены", "data": null}`

---

### 7.3 Открыть смену

| Метод | POST | URL | `/api/v1/shifts/open` | Auth | Да |
|-------|------|-----|-----------------------|------|-----|

**Ошибка 409:** Уже есть незакрытая смена.

---

### 7.4 Закрыть смену

| Метод | POST | URL | `/api/v1/shifts/{id}/close` | Auth | Да |
|-------|------|-----|----------------------------|------|-----|

**Ошибка 409:** Смена уже закрыта.

---

### 7.5 Удалить смену

| Метод | DELETE | URL | `/api/v1/shifts/{id}` | Auth | Да |
|-------|--------|-----|-----------------------|------|-----|

---

## 8. Общий формат ответов

**Успех:**
```json
{
  "success": true,
  "message": "string (optional)",
  "data": {}
}
```

**Ошибка:**
```json
{
  "success": false,
  "message": "string",
  "errors": {
    "field": ["validation message"]
  }
}
```

---

## 9. Коды HTTP

| Код | Значение |
|-----|----------|
| 200 | OK |
| 201 | Created |
| 401 | Unauthenticated — токен отсутствует или недействителен |
| 404 | Not Found |
| 409 | Conflict — бизнес-ограничение (напр. смена уже закрыта) |
| 422 | Validation Error |
| 500 | Server Error |

**401 ответ:** `{"message": "Unauthenticated."}`

---

## 10. Рекомендации для ИИ-агента

1. **Порядок вызовов:** Сначала `POST /api/v1/auth/login` → сохранить `data.token` → использовать в `Authorization: Bearer {token}`.
2. **Проверка доступности:** `GET /api/health` — не требует авторизации.
3. **Товары:** `GET /api/v1/products-api` — публичный, остальные методы требуют токен.
4. **Продажи:** При создании обязательно передавать `name_snapshot` для каждого item и `total_qty`.
5. **Смены:** Перед созданием продажи можно проверить `GET /api/v1/shifts/current` и при необходимости открыть смену.
6. **Пагинация:** Во всех списках используется `per_page` (1-100) и `page`.
7. **Цены:** Все суммы в копейках (1000 = 10.00 руб).
