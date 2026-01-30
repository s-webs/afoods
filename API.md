# API Документация

## Авторизация

### Обзор
API использует **Laravel Sanctum** для авторизации. Все защищённые эндпоинты требуют токен Bearer для доступа.

### Как получить токен

**Эндпоинт:** `POST /api/v1/auth/login`

**Параметры запроса:**
- `email` (string, **required**) - email пользователя MoonShine
- `password` (string, **required**) - пароль пользователя
- `token_name` (string, optional) - название токена (по умолчанию "api-token")

**Пример запроса:**

```bash
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "password",
  "token_name": "my-device"
}
```

**Пример ответа:**

```json
{
  "success": true,
  "message": "Успешная авторизация",
  "data": {
    "token": "1|abc123def456...",
    "token_type": "Bearer",
    "user": {
      "id": 1,
      "name": "Администратор",
      "email": "admin@example.com"
    }
  }
}
```

### Использование токена

После получения токена добавляйте его в заголовок `Authorization` всех защищённых запросов:

```bash
Authorization: Bearer 1|abc123def456...
```

**Пример с curl:**

```bash
curl -X GET "http://your-domain.com/api/v1/products-api" \
  -H "Authorization: Bearer 1|abc123def456..." \
  -H "Accept: application/json"
```

**Пример с JavaScript (fetch):**

```javascript
fetch('http://your-domain.com/api/v1/products-api', {
  method: 'GET',
  headers: {
    'Authorization': 'Bearer 1|abc123def456...',
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  }
})
.then(response => response.json())
.then(data => console.log(data));
```

**Пример с Postman:**
1. Откройте вкладку **Authorization**
2. Выберите тип: **Bearer Token**
3. Вставьте токен в поле **Token**

### Управление токенами

#### Получить информацию о текущем пользователе
```bash
GET /api/v1/auth/user
Authorization: Bearer {token}
```

**Ответ:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Администратор",
    "email": "admin@example.com",
    "moonshine_user_role_id": 1,
    "created_at": "2026-01-30T10:00:00.000000Z"
  }
}
```

#### Выйти (удалить текущий токен)
```bash
POST /api/v1/auth/logout
Authorization: Bearer {token}
```

**Ответ:**
```json
{
  "success": true,
  "message": "Вы успешно вышли из системы"
}
```

#### Выйти со всех устройств (удалить все токены)
```bash
POST /api/v1/auth/logout-all
Authorization: Bearer {token}
```

**Ответ:**
```json
{
  "success": true,
  "message": "Вы вышли со всех устройств"
}
```

### Ошибки авторизации

**401 Unauthorized** - токен отсутствует или недействителен:
```json
{
  "message": "Unauthenticated."
}
```

**422 Validation Error** - неверные учётные данные при логине:
```json
{
  "message": "The email field must be a valid email address. (and 1 more error)",
  "errors": {
    "email": ["Неверные учетные данные"]
  }
}
```

### Полный пример работы с API

```bash
# 1. Получаем токен
curl -X POST "http://your-domain.com/api/v1/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password"
  }'

# Ответ: {"success":true,"data":{"token":"1|abc123..."}}

# 2. Используем токен для доступа к API
curl -X GET "http://your-domain.com/api/v1/products-api" \
  -H "Authorization: Bearer 1|abc123..." \
  -H "Accept: application/json"

# 3. Создаём товар
curl -X POST "http://your-domain.com/api/v1/products-api" \
  -H "Authorization: Bearer 1|abc123..." \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Новый товар",
    "price_amount": 1000
  }'

# 4. Выходим
curl -X POST "http://your-domain.com/api/v1/auth/logout" \
  -H "Authorization: Bearer 1|abc123..."
```

---

# API Документация - Управление товарами

## Базовый URL
```
/api/v1/products-api
```

**Требуется авторизация:** Да (токен Bearer)

## Эндпоинты

### 1. Получить список товаров
**GET** `/api/v1/products-api`

**Требуется авторизация:** Да

**Параметры запроса (query parameters):**
- `category_id` (integer, optional) - фильтр по категории
- `search` (string, optional) - поиск по названию или штрих-коду
- `in_stock` (boolean, optional) - фильтр по наличию (`true`/`false`)
- `sort_by` (string, optional) - поле для сортировки: `id`, `name`, `price_amount`, `sale_price_amount`, `quantity`, `created_at`
- `sort_order` (string, optional) - порядок сортировки: `asc` или `desc` (по умолчанию `desc`)
- `per_page` (integer, optional) - количество товаров на страницу (1-100, по умолчанию 15)
- `page` (integer, optional) - номер страницы

**Пример запроса:**
```bash
GET /api/v1/products-api?category_id=1&in_stock=true&sort_by=price_amount&sort_order=asc&per_page=20
Authorization: Bearer {token}
```

**Пример ответа:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "category_id": 1,
      "name": "Название товара",
      "barcode": "123456789",
      "price_amount": 1000,
      "sale_price_amount": 800,
      "quantity": 10,
      "category": {
        "id": 1,
        "name": "Категория"
      }
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

---

### 2. Получить товар по ID
**GET** `/api/v1/products-api/{id}`

**Требуется авторизация:** Да

**Пример запроса:**
```bash
GET /api/v1/products-api/1
Authorization: Bearer {token}
```

**Пример ответа:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "category_id": 1,
    "name": "Название товара",
    "barcode": "123456789",
    "images": ["image1.jpg", "image2.jpg"],
    "description": "Описание товара",
    "specs": {},
    "unit": "pcs",
    "price_amount": 1000,
    "sale_price_amount": 800,
    "quantity": 10,
    "obj": {},
    "slug": "nazvanie-tovara",
    "category": {
      "id": 1,
      "name": "Категория"
    }
  }
}
```

---

### 3. Создать товар
**POST** `/api/v1/products-api`

**Требуется авторизация:** Да

**Content-Type:** `application/json`

**Поля запроса:**
- `category_id` (integer, optional) - ID категории
- `name` (string, **required**) - название товара
- `new_name` (string, optional) - новое название
- `barcode` (string, optional) - штрих-код (уникальный)
- `images` (array, optional) - массив путей к изображениям
- `description` (string, optional) - описание товара
- `specs` (object, optional) - спецификации (JSON объект)
- `unit` (string, optional) - единица измерения (по умолчанию `pcs`)
- `price_amount` (integer, optional) - цена (по умолчанию 0)
- `sale_price_amount` (integer, optional) - цена со скидкой (по умолчанию 0)
- `quantity` (integer, optional) - количество на складе (по умолчанию 0)
- `obj` (object, optional) - дополнительные настройки (JSON объект)

**Примечание:** Если `slug` не указан, он будет автоматически сгенерирован из названия товара.

**Пример запроса:**
```bash
POST /api/v1/products-api
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Новый товар",
  "category_id": 1,
  "price_amount": 1500,
  "sale_price_amount": 1200,
  "quantity": 20,
  "description": "Описание нового товара",
  "unit": "pcs",
  "images": ["image1.jpg"],
  "specs": {
    "weight": "500g",
    "dimensions": "10x10x10"
  }
}
```

**Пример ответа:**
```json
{
  "success": true,
  "message": "Товар успешно создан",
  "data": {
    "id": 1,
    "name": "Новый товар",
    "slug": "novyy-tovar",
    ...
  }
}
```

---

### 4. Обновить товар
**PUT/PATCH** `/api/v1/products-api/{id}`

**Требуется авторизация:** Да

**Content-Type:** `application/json`

**Поля запроса:** (все поля опциональны, кроме `name` если используется `sometimes`)
- Все поля аналогичны созданию товара
- `slug` (string, optional) - URL-адрес товара (уникальный)

**Примечание:** Если изменяется `name` и `slug` не указан, он будет автоматически обновлен.

**Пример запроса:**
```bash
PUT /api/v1/products-api/1
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Обновленное название",
  "price_amount": 2000,
  "quantity": 15
}
```

**Пример ответа:**
```json
{
  "success": true,
  "message": "Товар успешно обновлен",
  "data": {
    "id": 1,
    "name": "Обновленное название",
    ...
  }
}
```

---

### 5. Удалить товар
**DELETE** `/api/v1/products-api/{id}`

**Требуется авторизация:** Да

**Пример запроса:**
```bash
DELETE /api/v1/products-api/1
Authorization: Bearer {token}
```

**Пример ответа:**
```json
{
  "success": true,
  "message": "Товар успешно удален"
}
```

---

## Коды ответов

- `200` - Успешный запрос
- `201` - Ресурс успешно создан
- `401` - Не авторизован (отсутствует или недействителен токен)
- `404` - Ресурс не найден
- `422` - Ошибка валидации
- `500` - Внутренняя ошибка сервера

## Формат ответов

Все ответы возвращаются в формате JSON со следующей структурой:

**Успешный ответ:**
```json
{
  "success": true,
  "message": "Сообщение (опционально)",
  "data": {}
}
```

**Ответ с ошибкой:**
```json
{
  "success": false,
  "message": "Описание ошибки",
  "errors": {
    "field": ["Сообщение об ошибке"]
  }
}
```

## Примеры использования

### Получить все товары в категории 1, отсортированные по цене
```bash
GET /api/v1/products-api?category_id=1&sort_by=price_amount&sort_order=asc
Authorization: Bearer {token}
```

### Найти товары по названию
```bash
GET /api/v1/products-api?search=молоко
Authorization: Bearer {token}
```

### Получить только товары в наличии
```bash
GET /api/v1/products-api?in_stock=true
Authorization: Bearer {token}
```

### Создать товар с минимальными данными
```bash
POST /api/v1/products-api
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Товар",
  "price_amount": 1000
}
```

---

# API Документация - Управление продажами (Sales)

## Базовый URL
```
/api/v1/sales-api
```

**Требуется авторизация:** Да (токен Bearer)

## Эндпоинты

### 1. Получить список продаж
**GET** `/api/v1/sales-api`

**Требуется авторизация:** Да

**Параметры запроса (query parameters):**
- `date_from` (date, optional) - фильтр по дате начала (формат: YYYY-MM-DD)
- `date_to` (date, optional) - фильтр по дате окончания (формат: YYYY-MM-DD)
- `sort_by` (string, optional) - поле для сортировки: `id`, `date`, `total_price`, `created_at`
- `sort_order` (string, optional) - порядок сортировки: `asc` или `desc` (по умолчанию `desc`)
- `per_page` (integer, optional) - количество записей на страницу (1-100, по умолчанию 15)
- `page` (integer, optional) - номер страницы

**Пример запроса:**
```bash
GET /api/v1/sales-api?date_from=2026-01-01&date_to=2026-01-31&sort_by=total_price&sort_order=desc&per_page=20
Authorization: Bearer {token}
```

**Пример ответа:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "cashier_id": 1,
      "shift_id": 5,
      "shopper_id": 10,
      "date": "2026-01-30T10:30:00.000000Z",
      "receipt_number": "RCP-2026-001",
      "items": [
        {
          "product_id": 15,
          "quantity": 2,
          "price": 1000
        },
        {
          "product_id": 23,
          "quantity": 1,
          "price": 500
        }
      ],
      "total_price": 2500,
      "created_at": "2026-01-30T10:30:00.000000Z",
      "updated_at": "2026-01-30T10:30:00.000000Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 95
  }
}
```

---

### 2. Получить продажу по ID
**GET** `/api/v1/sales-api/{id}`

**Требуется авторизация:** Да

**Пример запроса:**
```bash
GET /api/v1/sales-api/1
Authorization: Bearer {token}
```

**Пример ответа:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "cashier_id": 1,
    "shift_id": 5,
    "shopper_id": 10,
    "date": "2026-01-30T10:30:00.000000Z",
    "receipt_number": "RCP-2026-001",
    "items": [
      {
        "product_id": 15,
        "quantity": 2,
        "price": 1000
      },
      {
        "product_id": 23,
        "quantity": 1,
        "price": 500
      }
    ],
    "total_price": 2500,
    "created_at": "2026-01-30T10:30:00.000000Z",
    "updated_at": "2026-01-30T10:30:00.000000Z",
    "shopper": {
      "id": 10,
      "user_id": 5,
      "phone": "+79001234567",
      "addresses": [
        {
          "id": "abc123",
          "street": "ул. Ленина",
          "building": "10",
          "apartment": "25",
          "is_default": true
        }
      ]
    }
  }
}
```

---

### 3. Создать продажу
**POST** `/api/v1/sales-api`

**Требуется авторизация:** Да

**Content-Type:** `application/json`

**Поля запроса:**
- `date` (datetime, **required**) - дата и время продажи (формат: YYYY-MM-DD HH:MM:SS или ISO 8601)
- `receipt_number` (string, **required**) - номер чека (макс. 255 символов)
- `items` (array, **required**) - массив товаров в продаже
  - `product_id` (integer, **required**) - ID товара (должен существовать в таблице products)
  - `quantity` (integer, **required**) - количество (минимум 1)
  - `price` (integer, **required**) - цена за единицу товара в копейках (минимум 0)
- `total_price` (integer, **required**) - общая сумма продажи в копейках (минимум 0)

**Примечания:**
- `cashier_id`, `shift_id`, `shopper_id` - опциональные поля, которые можно добавить в модели, но в текущей валидации не требуются
- Цены указываются в копейках (например, 1000 = 10.00 руб)
- `total_price` должен соответствовать сумме всех items

**Пример запроса:**
```bash
POST /api/v1/sales-api
Authorization: Bearer {token}
Content-Type: application/json

{
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

**Пример ответа:**
```json
{
  "success": true,
  "message": "Продажа успешно создана",
  "data": {
    "id": 25,
    "date": "2026-01-30T14:25:00.000000Z",
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
    "total_price": 4450,
    "cashier_id": null,
    "shift_id": null,
    "shopper_id": null,
    "created_at": "2026-01-30T14:25:10.000000Z",
    "updated_at": "2026-01-30T14:25:10.000000Z"
  }
}
```

---

### 4. Обновить продажу
**PUT/PATCH** `/api/v1/sales-api/{id}`

**Требуется авторизация:** Да

**Content-Type:** `application/json`

**Поля запроса:** (все поля опциональны)
- `date` (datetime, optional) - дата и время продажи
- `receipt_number` (string, optional) - номер чека
- `items` (array, optional) - массив товаров
  - `product_id` (integer, required если указан items) - ID товара
  - `quantity` (integer, required если указан items) - количество
  - `price` (integer, required если указан items) - цена
- `total_price` (integer, optional) - общая сумма

**Пример запроса:**
```bash
PUT /api/v1/sales-api/25
Authorization: Bearer {token}
Content-Type: application/json

{
  "receipt_number": "RCP-2026-002-CORRECTED",
  "total_price": 4500,
  "items": [
    {
      "product_id": 15,
      "quantity": 3,
      "price": 1200
    },
    {
      "product_id": 8,
      "quantity": 1,
      "price": 900
    }
  ]
}
```

**Пример ответа:**
```json
{
  "success": true,
  "message": "Продажа успешно обновлена",
  "data": {
    "id": 25,
    "date": "2026-01-30T14:25:00.000000Z",
    "receipt_number": "RCP-2026-002-CORRECTED",
    "items": [
      {
        "product_id": 15,
        "quantity": 3,
        "price": 1200
      },
      {
        "product_id": 8,
        "quantity": 1,
        "price": 900
      }
    ],
    "total_price": 4500,
    "cashier_id": null,
    "shift_id": null,
    "shopper_id": null,
    "created_at": "2026-01-30T14:25:10.000000Z",
    "updated_at": "2026-01-30T14:30:15.000000Z"
  }
}
```

---

### 5. Удалить продажу
**DELETE** `/api/v1/sales-api/{id}`

**Требуется авторизация:** Да

**Пример запроса:**
```bash
DELETE /api/v1/sales-api/25
Authorization: Bearer {token}
```

**Пример ответа:**
```json
{
  "success": true,
  "message": "Продажа успешно удалена"
}
```

---

### 6. Получить чек по продаже
**GET** `/api/v1/sales-api/{id}/receipt`

**Требуется авторизация:** Да

**Параметры запроса (query parameters):**
- `format` (string, optional) - формат чека: `json`, `html`, `pdf`, `pdf-inline`, `text` (по умолчанию `json`)

**Форматы:**
- `json` - JSON объект с данными чека (для кассовых систем)
- `html` - HTML страница с чеком (можно открыть в браузере)
- `pdf` - PDF файл для скачивания
- `pdf-inline` - PDF файл для просмотра в браузере
- `text` - Текстовый чек для термопринтера (80мм)

**Примеры запросов:**

#### JSON формат (по умолчанию)
```bash
GET /api/v1/sales-api/1/receipt
Authorization: Bearer {token}

GET /api/v1/sales-api/1/receipt?format=json
Authorization: Bearer {token}
```

**Пример ответа:**
```json
{
  "success": true,
  "data": {
    "receipt_number": "RCP-2026-001",
    "date": "30.01.2026 10:30:00",
    "cashier": {
      "id": 1,
      "name": "Иванов Иван"
    },
    "shift": {
      "id": 5,
      "opened_at": "30.01.2026 08:00"
    },
    "shopper": {
      "id": 10,
      "phone": "+79001234567"
    },
    "items": [
      {
        "name": "Молоко 3.2%",
        "barcode": "4607025392119",
        "quantity": 2,
        "price": 1000,
        "total": 2000
      },
      {
        "name": "Хлеб белый",
        "barcode": "4607025123456",
        "quantity": 1,
        "price": 500,
        "total": 500
      }
    ],
    "subtotal": 2500,
    "total": 2500,
    "payment_method": "cash"
  }
}
```

#### HTML формат
```bash
GET /api/v1/sales-api/1/receipt?format=html
Authorization: Bearer {token}
```

Возвращает HTML страницу с чеком, которую можно:
- Открыть в браузере
- Распечатать (Ctrl+P)
- Сохранить как HTML файл

#### PDF формат (скачивание)
```bash
GET /api/v1/sales-api/1/receipt?format=pdf
Authorization: Bearer {token}
```

Скачивает PDF файл `receipt-RCP-2026-001.pdf`

#### PDF формат (просмотр)
```bash
GET /api/v1/sales-api/1/receipt?format=pdf-inline
Authorization: Bearer {token}
```

Открывает PDF в браузере для просмотра

#### Текстовый формат (термопринтер)
```bash
GET /api/v1/sales-api/1/receipt?format=text
Authorization: Bearer {token}
```

**Пример ответа (text/plain):**
```
            AFOODS            
     Продуктовый магазин      
--------------------------------

Чек № RCP-2026-001
Дата: 30.01.2026 10:30:00
Кассир: Иванов Иван
Смена: 5

================================

Молоко 3.2%
  2 x 10.00 ₽          20.00 ₽

Хлеб белый
  1 x 5.00 ₽            5.00 ₽

================================

ИТОГО:                 25.00 ₽

--------------------------------
     Спасибо за покупку!     
       Приходите еще!        
--------------------------------
```

---

## Структура данных

### Объект Sale
```json
{
  "id": 1,
  "cashier_id": 1,           // ID кассира (nullable)
  "shift_id": 5,             // ID смены (nullable)
  "shopper_id": 10,          // ID покупателя (nullable)
  "date": "2026-01-30T10:30:00.000000Z",
  "receipt_number": "RCP-2026-001",
  "items": [                 // Массив товаров в продаже
    {
      "product_id": 15,
      "quantity": 2,
      "price": 1000
    }
  ],
  "total_price": 2500,       // Общая сумма в копейках
  "created_at": "2026-01-30T10:30:00.000000Z",
  "updated_at": "2026-01-30T10:30:00.000000Z"
}
```

### Формат items (товары в продаже)
Поле `items` представляет собой JSON-массив объектов:
```json
[
  {
    "product_id": 15,    // ID товара из таблицы products
    "quantity": 2,       // Количество товара
    "price": 1000        // Цена за единицу в копейках
  }
]
```

## Примеры использования

### Получить все продажи за последний месяц
```bash
GET /api/v1/sales-api?date_from=2026-01-01&date_to=2026-01-31&sort_by=date&sort_order=desc
Authorization: Bearer {token}
```

### Получить продажи отсортированные по сумме
```bash
GET /api/v1/sales-api?sort_by=total_price&sort_order=desc
Authorization: Bearer {token}
```

### Создать продажу с несколькими товарами
```bash
POST /api/v1/sales-api
Authorization: Bearer {token}
Content-Type: application/json

{
  "date": "2026-01-30 15:45:00",
  "receipt_number": "RCP-2026-003",
  "items": [
    {
      "product_id": 1,
      "quantity": 2,
      "price": 500
    },
    {
      "product_id": 5,
      "quantity": 1,
      "price": 1500
    },
    {
      "product_id": 12,
      "quantity": 3,
      "price": 300
    }
  ],
  "total_price": 3400
}
```

---

## Найденные проблемы и рекомендации

### 1. Отсутствуют связи в модели Sale
В модели `Sale` определено только отношение `shopper()`, но отсутствуют связи для:
- `cashier()` - связь с таблицей `cashiers`
- `shift()` - связь с таблицей `shifts`

**Рекомендация:** Добавить эти отношения в модель для удобства работы.

### 2. Валидация не включает дополнительные поля
В контроллере `SaleApiController` валидация не включает поля:
- `cashier_id`
- `shift_id`
- `shopper_id`

Эти поля можно устанавливать только напрямую через модель, но не через API.

**Рекомендация:** Если эти поля должны устанавливаться через API, добавить их в валидацию.

### 3. Миграция использует integer вместо foreignId
В миграции `create_sales_table.php` используется:
```php
$table->integer('cashier_id')->nullable();
$table->integer('shift_id')->nullable();
```

**Рекомендация:** Использовать `foreignId` для FK:
```php
$table->foreignId('cashier_id')->nullable()->constrained()->nullOnDelete();
$table->foreignId('shift_id')->nullable()->constrained()->nullOnDelete();
```

### 4. Нет проверки согласованности total_price
API принимает `total_price` как отдельное поле, но не проверяет, соответствует ли оно сумме всех items.

**Рекомендация:** Добавить валидацию или автоматический расчет total_price.
