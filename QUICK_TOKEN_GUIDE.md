# 🔑 Быстрая шпаргалка - Генерация токена

## Для тех, кто торопится

### 1️⃣ Создайте токен на сервере:

```bash
php artisan api:generate-token admin@example.com
```

### 2️⃣ Скопируйте полученный токен:

```
1|abc123def456ghi789...
```

### 3️⃣ Используйте в приложении:

```bash
Authorization: Bearer 1|abc123def456ghi789...
```

---

## Примеры запросов

### Получить список товаров:
```bash
curl -X GET "http://your-domain.com/api/v1/products-api" \
  -H "Authorization: Bearer 1|abc123def..." \
  -H "Accept: application/json"
```

### Создать товар:
```bash
curl -X POST "http://your-domain.com/api/v1/products-api" \
  -H "Authorization: Bearer 1|abc123def..." \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Новый товар",
    "price_amount": 1000,
    "quantity": 5
  }'
```

### Создать продажу:
```bash
curl -X POST "http://your-domain.com/api/v1/sales-api" \
  -H "Authorization: Bearer 1|abc123def..." \
  -H "Content-Type: application/json" \
  -d '{
    "date": "2026-01-30 15:00:00",
    "receipt_number": "RCP-001",
    "items": [
      {
        "product_id": 1,
        "quantity": 2,
        "price": 500
      }
    ],
    "total_price": 1000
  }'
```

---

## Дополнительные команды

```bash
# Посмотреть все токены пользователя
php artisan api:tokens list admin@example.com

# Удалить конкретный токен
php artisan api:tokens revoke admin@example.com --token-id=1

# Удалить все токены пользователя
php artisan api:tokens revoke-all admin@example.com
```

---

## Если токен не работает

1. Проверьте формат: `Bearer {токен}` (с пробелом после Bearer)
2. Убедитесь, что токен скопирован полностью
3. Очистите кеш: `php artisan config:clear`
4. Проверьте токен: `curl -X GET "http://your-domain.com/api/v1/auth/user" -H "Authorization: Bearer {токен}"`

---

📚 **Полная документация:**
- **API_TOKEN_GENERATOR.md** - подробное руководство по генерации токенов
- **API_AUTH_GUIDE.md** - руководство по авторизации API
- **API.md** - полная документация всех эндпоинтов
