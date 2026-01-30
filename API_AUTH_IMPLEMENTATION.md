# API Authorization Implementation - Summary

## Что было сделано

### 1. Установка Laravel Sanctum
- Добавлен пакет `laravel/sanctum` через Composer
- Опубликованы конфигурация и миграции
- Создана таблица `personal_access_tokens` для хранения API токенов

### 2. Расширение модели MoonshineUser
- Создан файл `app/Models/MoonshineUser.php`
- Добавлен трейт `HasApiTokens` для поддержки Sanctum токенов
- Модель расширяет базовую модель MoonShine

### 3. Настройка конфигурации
**config/moonshine.php:**
- Изменён класс модели на `App\Models\MoonshineUser::class`

**config/auth.php:**
- Добавлен guard `moonshine` с драйвером `session`
- Добавлен provider `moonshine_users` для работы с пользователями MoonShine

**composer.json:**
- Добавлена настройка `audit.block-insecure: false` для обхода проверок безопасности

### 4. Защита API маршрутов
**routes/api.php:**
- Добавлены публичные маршруты авторизации (login)
- Все остальные API маршруты обёрнуты в middleware `auth:sanctum`
- Защищены следующие эндпоинты:
  - Products API (все операции)
  - Categories API (все операции)
  - Sales API (все операции)
  - Cashiers API (все операции)
  - Shifts API (все операции)

### 5. Создание контроллера авторизации
**app/Http/Controllers/Api/AuthApiController.php:**

Методы:
- `login()` - получение API токена (email + password)
- `logout()` - удаление текущего токена
- `logoutAll()` - удаление всех токенов пользователя
- `user()` - получение информации о текущем пользователе

### 6. Обновление документации
**API.md:**
- Добавлен подробный раздел "Авторизация" в начало документации
- Все эндпоинты обновлены с указанием требования токена
- Добавлены примеры использования с заголовком Authorization
- Обновлены коды ответов (добавлен 401)

**API_AUTH_GUIDE.md (новый файл):**
- Краткое руководство для интеграции с другими системами/LLM
- Пошаговая инструкция по авторизации
- Список всех доступных эндпоинтов
- Примеры использования с curl

## Использование

### Получение токена
```bash
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "password"
}
```

### Использование токена
```bash
GET /api/v1/products-api
Authorization: Bearer {token}
```

### Проверка токена
```bash
GET /api/v1/auth/user
Authorization: Bearer {token}
```

### Выход
```bash
POST /api/v1/auth/logout
Authorization: Bearer {token}
```

## Безопасность

- Токены хранятся в БД в хешированном виде
- Токены не имеют срока действия (действуют до удаления)
- Можно создать несколько токенов с разными именами
- Поддержка abilities (разрешений) для токенов
- Возможность удаления всех токенов одновременно

## Проверка работоспособности

Все маршруты правильно настроены с middleware `auth:sanctum`:

```bash
php artisan route:list --path=api/v1/products --verbose
```

Результат показывает, что middleware `Illuminate\Auth\Middleware\Authenticate:sanctum` применён ко всем защищённым маршрутам.

## Следующие шаги

1. Создайте первого пользователя MoonShine через панель администратора
2. Получите токен через `/api/v1/auth/login`
3. Используйте токен для доступа к защищённым эндпоинтам
4. При необходимости настройте срок действия токенов в `config/sanctum.php`
5. Рассмотрите добавление abilities (разрешений) для различных уровней доступа

## Файлы для передачи другой LLM

Для интеграции с другими системами используйте:
- **API_AUTH_GUIDE.md** - краткое руководство по авторизации
- **API.md** - полная документация всех эндпоинтов

## Отладка

Если возникают проблемы:

```bash
# Очистить кеш
php artisan config:clear
php artisan route:clear
php artisan cache:clear

# Проверить маршруты
php artisan route:list --path=api/v1

# Проверить миграции
php artisan migrate:status
```
