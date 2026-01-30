# Генерация API токенов на бэкэнде

Если у вас во внешнем приложении нет формы авторизации, вы можете создать токен напрямую на бэкэнде.

## Быстрый способ: Artisan команда

### 1. Создать токен для пользователя

```bash
php artisan api:generate-token admin@example.com
```

**С дополнительными параметрами:**

```bash
# Создать токен с пользовательским названием
php artisan api:generate-token admin@example.com --name="mobile-app"

# Показать информацию о пользователе
php artisan api:generate-token admin@example.com --show-user
```

**Пример вывода:**
```
✅ API токен успешно создан!

Токен для пользователя: Администратор (admin@example.com)
Название токена: api-token

═══════════════════════════════════════════════════════════════
1|abc123def456ghi789...
═══════════════════════════════════════════════════════════════

Используйте этот токен в заголовке Authorization:
Authorization: Bearer 1|abc123def456ghi789...

💡 Сохраните токен в безопасном месте. Он больше не будет показан.
```

### 2. Просмотреть все токены пользователя

```bash
php artisan api:tokens list admin@example.com
```

**Вывод:**
```
Токены пользователя: Администратор (admin@example.com)

┌────┬──────────────┬───────┬──────────────────────────┬─────────────────────┐
│ ID │ Название     │ Права │ Последнее использование  │ Создан              │
├────┼──────────────┼───────┼──────────────────────────┼─────────────────────┤
│ 1  │ api-token    │ все   │ 30.01.2026 15:30:45     │ 30.01.2026 10:00:00 │
│ 2  │ mobile-app   │ все   │ никогда                 │ 30.01.2026 14:00:00 │
└────┴──────────────┴───────┴──────────────────────────┴─────────────────────┘
```

### 3. Удалить конкретный токен

```bash
php artisan api:tokens revoke admin@example.com --token-id=1
```

### 4. Удалить все токены пользователя

```bash
php artisan api:tokens revoke-all admin@example.com
```

---

## Альтернативный способ: Через tinker

```bash
php artisan tinker
```

Затем выполните:

```php
// Найти пользователя
$user = App\Models\MoonshineUser::where('email', 'admin@example.com')->first();

// Создать токен
$token = $user->createToken('my-app-token');

// Вывести токен
echo $token->plainTextToken;

// Выход из tinker
exit
```

---

## Использование токена во внешнем приложении

После получения токена, используйте его в заголовках HTTP запросов:

### Пример с curl:
```bash
curl -X GET "http://your-domain.com/api/v1/products-api" \
  -H "Authorization: Bearer 1|abc123def456..." \
  -H "Accept: application/json"
```

### Пример с JavaScript (fetch):
```javascript
const token = '1|abc123def456...';

fetch('http://your-domain.com/api/v1/products-api', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
  }
})
.then(response => response.json())
.then(data => console.log(data));
```

### Пример с Python (requests):
```python
import requests

token = '1|abc123def456...'
headers = {
    'Authorization': f'Bearer {token}',
    'Accept': 'application/json'
}

response = requests.get('http://your-domain.com/api/v1/products-api', headers=headers)
print(response.json())
```

### Пример с PHP (Guzzle):
```php
use GuzzleHttp\Client;

$token = '1|abc123def456...';
$client = new Client();

$response = $client->get('http://your-domain.com/api/v1/products-api', [
    'headers' => [
        'Authorization' => 'Bearer ' . $token,
        'Accept' => 'application/json'
    ]
]);

$data = json_decode($response->getBody(), true);
```

---

## Управление токенами

### Список всех пользователей MoonShine

Чтобы узнать email пользователя:

```bash
php artisan tinker
```

```php
App\Models\MoonshineUser::select('id', 'name', 'email')->get();
exit
```

### Создать несколько токенов

Вы можете создать разные токены для разных приложений:

```bash
php artisan api:generate-token admin@example.com --name="mobile-app"
php artisan api:generate-token admin@example.com --name="desktop-app"
php artisan api:generate-token admin@example.com --name="integration-service"
```

Каждый токен можно будет отозвать независимо от других.

---

## Безопасность

⚠️ **Важно:**
- Токен показывается только один раз при создании
- Храните токены в безопасном месте
- Не передавайте токены третьим лицам
- Используйте HTTPS для передачи токенов
- При компрометации токена удалите его через команду `api:tokens revoke`

---

## Срок действия токенов

По умолчанию токены **не истекают**. Чтобы установить срок действия, отредактируйте `config/sanctum.php`:

```php
'expiration' => 60, // токены будут действовать 60 минут
```

Или оставьте `null` для бессрочных токенов (по умолчанию).

---

## Частые вопросы

**Q: Можно ли создать токен с ограниченными правами?**

A: Да, используйте abilities:

```php
// В tinker
$token = $user->createToken('limited-token', ['products:read', 'sales:read']);
echo $token->plainTextToken;
```

**Q: Как узнать, работает ли токен?**

A: Проверьте через API:

```bash
curl -X GET "http://your-domain.com/api/v1/auth/user" \
  -H "Authorization: Bearer {ваш_токен}"
```

**Q: Что делать, если токен не работает?**

A: Проверьте:
1. Правильность формата: `Bearer {токен}` (с пробелом)
2. Токен скопирован полностью
3. Кеш очищен: `php artisan config:clear`

---

## Дополнительно

Подробная документация по работе с API:
- **API.md** - полная документация всех эндпоинтов
- **API_AUTH_GUIDE.md** - руководство по авторизации API
