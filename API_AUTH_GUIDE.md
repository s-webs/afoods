# Руководство по авторизации API для LLM

Это краткое руководство для интеграции с API системы управления товарами и продажами.

## Авторизация

API использует **токены Bearer (Laravel Sanctum)**. Для работы с API необходимо:

### Способы получения токена

Есть два способа получить токен:

**A. Через API эндпоинт** (если у вас есть форма логина в приложении)
**B. Через бэкэнд** (если формы логина нет - см. раздел ниже)

### 1. Получить токен через API эндпоинт

**Эндпоинт:** `POST /api/v1/auth/login`

**Тело запроса:**
```json
{
  "email": "admin@example.com",
  "password": "password"
}
```

**Ответ:**
```json
{
  "success": true,
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

**Сохраните значение `token`** - его нужно использовать во всех последующих запросах.

### 1B. Получить токен на бэкэнде (альтернатива)

Если у вас нет формы авторизации, создайте токен напрямую на сервере:

**Быстрый способ через Artisan команду:**

```bash
php artisan api:generate-token admin@example.com
```

Скопируйте полученный токен и используйте в приложении.

**Другие команды:**
```bash
# Создать токен с названием
php artisan api:generate-token admin@example.com --name="my-app"

# Посмотреть все токены
php artisan api:tokens list admin@example.com

# Удалить токен
php artisan api:tokens revoke admin@example.com --token-id=1
```

📖 Подробнее: см. файл **API_TOKEN_GENERATOR.md**

### 2. Использование токена

Во всех защищённых запросах добавляйте заголовок:

```
Authorization: Bearer {полученный_токен}
```

### 3. Проверка авторизации

Проверить, что токен действителен:

```bash
GET /api/v1/auth/user
Authorization: Bearer {token}
```

Ответ вернёт информацию о текущем пользователе.

### 4. Выход

Удалить текущий токен:

```bash
POST /api/v1/auth/logout
Authorization: Bearer {token}
```

---

## Доступные API эндпоинты

Все эндпоинты требуют токен Bearer в заголовке `Authorization`.

### Товары (Products)
- `GET /api/v1/products-api` - список товаров
- `GET /api/v1/products-api/{id}` - товар по ID
- `POST /api/v1/products-api` - создать товар
- `PUT /api/v1/products-api/{id}` - обновить товар
- `DELETE /api/v1/products-api/{id}` - удалить товар

### Категории (Categories)
- `GET /api/v1/categories-api` - список категорий
- `GET /api/v1/categories-api/{id}` - категория по ID
- `POST /api/v1/categories-api` - создать категорию
- `PUT /api/v1/categories-api/{id}` - обновить категорию
- `DELETE /api/v1/categories-api/{id}` - удалить категорию

### Продажи (Sales)
- `GET /api/v1/sales-api` - список продаж
- `GET /api/v1/sales-api/{id}` - продажа по ID
- `POST /api/v1/sales-api` - создать продажу
- `PUT /api/v1/sales-api/{id}` - обновить продажу
- `DELETE /api/v1/sales-api/{id}` - удалить продажу
- `GET /api/v1/sales-api/{id}/receipt` - получить чек

### Кассиры (Cashiers)
- `GET /api/v1/cashiers-api` - список кассиров
- `GET /api/v1/cashiers-api/{id}` - кассир по ID
- `POST /api/v1/cashiers-api` - создать кассира
- `PUT /api/v1/cashiers-api/{id}` - обновить кассира
- `DELETE /api/v1/cashiers-api/{id}` - удалить кассира

### Смены (Shifts)
- `GET /api/v1/shifts` - список смен
- `GET /api/v1/shifts/current` - текущая смена
- `POST /api/v1/shifts/open` - открыть смену
- `POST /api/v1/shifts/{id}/close` - закрыть смену
- `DELETE /api/v1/shifts/{id}` - удалить смену

---

## Пример использования

```bash
# Шаг 1: Получить токен
curl -X POST "https://your-domain.com/api/v1/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'

# Ответ: {"success":true,"data":{"token":"1|abc123..."}}

# Шаг 2: Использовать токен для запроса товаров
curl -X GET "https://your-domain.com/api/v1/products-api" \
  -H "Authorization: Bearer 1|abc123..." \
  -H "Accept: application/json"

# Шаг 3: Создать товар
curl -X POST "https://your-domain.com/api/v1/products-api" \
  -H "Authorization: Bearer 1|abc123..." \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Новый товар",
    "price_amount": 1500,
    "quantity": 10
  }'
```

---

## Обработка ошибок

### 401 Unauthorized
Токен отсутствует, недействителен или истёк. Необходимо получить новый токен через `/api/v1/auth/login`.

### 422 Validation Error
Данные запроса не прошли валидацию. Проверьте поля в ответе `errors`.

### 404 Not Found
Запрашиваемый ресурс не найден.

---

## Важные замечания

1. **Токен не истекает автоматически** - он действителен до тех пор, пока не будет удалён через logout
2. **Один токен на устройство/приложение** - можно создать несколько токенов с разными именами
3. **Безопасность** - храните токен в безопасном месте, не передавайте третьим лицам
4. **Формат данных** - все запросы и ответы в формате JSON
5. **Кодировка** - UTF-8

---

## Полная документация

Подробная документация со всеми параметрами и примерами находится в файле `API.md`.
