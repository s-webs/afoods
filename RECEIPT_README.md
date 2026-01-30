# 🧾 Генерация чеков для Sale API

## 📋 Обзор

Функционал генерации чеков позволяет получать чеки для продаж в 4 форматах:

| Формат | Описание | Использование |
|--------|----------|---------------|
| **JSON** | Структурированные данные | API интеграции, мобильные приложения |
| **HTML** | Веб-страница | Просмотр в браузере, печать |
| **PDF** | PDF документ | Скачивание, архивирование, email |
| **TEXT** | Текстовый формат | Термопринтеры (58мм/80мм) |

---

## 🚀 Быстрый старт

### 1. Установка
```bash
composer require barryvdh/laravel-dompdf
```

### 2. Тестирование
```bash
# JSON формат
curl http://localhost/api/v1/sales-api/1/receipt?format=json

# HTML формат
curl http://localhost/api/v1/sales-api/1/receipt?format=html

# PDF формат
curl -O http://localhost/api/v1/sales-api/1/receipt?format=pdf

# TEXT формат
curl http://localhost/api/v1/sales-api/1/receipt?format=text
```

👉 **Подробнее:** [RECEIPT_QUICK_START.md](RECEIPT_QUICK_START.md)

---

## 📚 Документация

### Для разработчиков
- [RECEIPT_QUICK_START.md](RECEIPT_QUICK_START.md) - Быстрый старт (5 минут)
- [RECEIPT_API_GUIDE.md](RECEIPT_API_GUIDE.md) - Полное руководство
- [RECEIPT_API_SETUP.md](RECEIPT_API_SETUP.md) - Установка и настройка

### Основная документация
- [API.md](API.md) - Обновленная документация Sale API с чеками

---

## 🎯 Endpoint

```
GET /api/v1/sales-api/{id}/receipt?format={format}
```

### Параметры
- `id` - ID продажи (обязательно)
- `format` - Формат чека: `json`, `html`, `pdf`, `pdf-inline`, `text` (по умолчанию `json`)

---

## 💡 Примеры использования

### JavaScript (Frontend)
```javascript
// Просмотр HTML чека
window.open(`/api/v1/sales-api/${saleId}/receipt?format=html`, '_blank');

// Скачивание PDF
window.location.href = `/api/v1/sales-api/${saleId}/receipt?format=pdf`;

// Получение JSON данных
const response = await fetch(`/api/v1/sales-api/${saleId}/receipt?format=json`);
const data = await response.json();
```

### PHP (Backend)
```php
use App\Services\ReceiptService;

$receiptService = new ReceiptService();
$sale = Sale::find(1);

// Генерация в разных форматах
$html = $receiptService->generateHtml($sale);
$pdf = $receiptService->generatePdf($sale);
$json = $receiptService->generateJson($sale);
$text = $receiptService->generateText($sale);
```

### cURL
```bash
# Просмотр в браузере
open "http://localhost/api/v1/sales-api/1/receipt?format=html"

# Скачивание PDF
curl -O "http://localhost/api/v1/sales-api/1/receipt?format=pdf"

# Получение JSON
curl "http://localhost/api/v1/sales-api/1/receipt?format=json" | jq

# Текстовый чек
curl "http://localhost/api/v1/sales-api/1/receipt?format=text"
```

---

## 🏗️ Архитектура

### Структура файлов
```
app/
├── Services/
│   └── ReceiptService.php          # Сервис генерации чеков
├── Http/Controllers/Api/
│   └── SaleApiController.php       # Добавлен метод receipt()
└── Models/
    └── Sale.php                     # Добавлены связи cashier, shift

resources/views/receipts/
├── receipt-html.blade.php           # HTML шаблон
└── receipt-pdf.blade.php            # PDF шаблон

routes/
└── api.php                          # Роут GET /sales-api/{id}/receipt
```

### Классы и методы

**ReceiptService:**
- `generateHtml(Sale $sale): string` - HTML чек
- `generatePdf(Sale $sale): PDF` - PDF чек
- `generateJson(Sale $sale): array` - JSON чек
- `generateText(Sale $sale): string` - Текстовый чек

**SaleApiController:**
- `receipt(int $id, Request $request)` - Эндпоинт получения чека

---

## 🎨 Кастомизация

### Изменить внешний вид чека

#### HTML/PDF чек
Отредактируйте:
- `resources/views/receipts/receipt-html.blade.php`
- `resources/views/receipts/receipt-pdf.blade.php`

#### Текстовый чек
Отредактируйте метод `generateText()` в `app/Services/ReceiptService.php`

### Добавить логотип
```html
<!-- В receipt-html.blade.php или receipt-pdf.blade.php -->
<div class="logo">
  <img src="{{ asset('images/logo.png') }}" alt="Logo">
</div>
```

### Добавить QR-код
```html
<div class="qr-code">
  <img src="https://api.qrserver.com/v1/create-qr-code/?data={{ $sale->receipt_number }}" alt="QR">
</div>
```

### Изменить размер PDF
```php
// В ReceiptService::generatePdf()
$pdf->setPaper([0, 0, 226.77, 841.89], 'portrait'); // 80mm
$pdf->setPaper([0, 0, 164.41, 841.89], 'portrait'); // 58mm
$pdf->setPaper('a4', 'portrait');                    // A4
```

---

## 🧪 Тестирование

### Ручное тестирование
```bash
# 1. Создать тестовую продажу
curl -X POST http://localhost/api/v1/sales-api \
  -H "Content-Type: application/json" \
  -d '{
    "cashier_id": 1,
    "date": "2026-01-30 15:00:00",
    "receipt_number": "TEST-001",
    "items": [{"product_id": 1, "quantity": 2, "price": 1000}],
    "total_price": 2000
  }'

# 2. Получить чек
curl http://localhost/api/v1/sales-api/1/receipt?format=json
```

### Unit тесты
Добавьте в `tests/Feature/ReceiptApiTest.php`:

```php
public function test_can_generate_json_receipt()
{
    $sale = Sale::factory()->create();
    
    $response = $this->getJson("/api/v1/sales-api/{$sale->id}/receipt?format=json");
    
    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'receipt_number',
                'date',
                'items',
                'total',
            ],
        ]);
}

public function test_can_generate_html_receipt()
{
    $sale = Sale::factory()->create();
    
    $response = $this->get("/api/v1/sales-api/{$sale->id}/receipt?format=html");
    
    $response->assertStatus(200)
        ->assertHeader('Content-Type', 'text/html; charset=UTF-8');
}

public function test_can_generate_pdf_receipt()
{
    $sale = Sale::factory()->create();
    
    $response = $this->get("/api/v1/sales-api/{$sale->id}/receipt?format=pdf");
    
    $response->assertStatus(200)
        ->assertHeader('Content-Type', 'application/pdf');
}

public function test_can_generate_text_receipt()
{
    $sale = Sale::factory()->create();
    
    $response = $this->get("/api/v1/sales-api/{$sale->id}/receipt?format=text");
    
    $response->assertStatus(200)
        ->assertHeader('Content-Type', 'text/plain; charset=utf-8');
}
```

---

## 📊 Примеры чеков

### JSON формат
```json
{
  "success": true,
  "data": {
    "receipt_number": "RCP-2026-001",
    "date": "30.01.2026 15:30:00",
    "items": [
      {
        "name": "Молоко 3.2%",
        "quantity": 2,
        "price": 8900,
        "total": 17800
      }
    ],
    "total": 17800
  }
}
```

### TEXT формат
```
            AFOODS            
     Продуктовый магазин      
--------------------------------
Чек № RCP-2026-001
Дата: 30.01.2026 15:30:00
Кассир: Иванова А.С.
================================
Молоко 3.2%
  2 x 89.00 ₽         178.00 ₽
================================
ИТОГО:                178.00 ₽
--------------------------------
     Спасибо за покупку!     
--------------------------------
```

---

## 🔧 Настройка для Production

### 1. Кэширование
```php
// В SaleApiController
public function receipt(int $id, Request $request)
{
    $format = $request->get('format', 'json');
    $cacheKey = "receipt:{$id}:{$format}";
    
    return Cache::remember($cacheKey, 3600, function () use ($id, $format) {
        // Генерация чека
    });
}
```

### 2. Очередь для PDF
```php
// Создать job
php artisan make:job GenerateReceiptPdf

// В GenerateReceiptPdf
public function handle()
{
    $pdf = (new ReceiptService())->generatePdf($this->sale);
    Storage::put("receipts/{$this->sale->id}.pdf", $pdf->output());
}
```

### 3. Хранение PDF
```php
// Сохранение в S3
$pdf = $receiptService->generatePdf($sale);
Storage::disk('s3')->put(
    "receipts/{$sale->receipt_number}.pdf",
    $pdf->output()
);
```

---

## ❓ FAQ

**Q: Как добавить НДС на чек?**  
A: Добавьте поле `tax` в Sale модель и отобразите в шаблонах

**Q: Как отправить чек на email?**  
A: Создайте Mail класс и используйте PDF attachment

**Q: Поддерживает ли фискальные чеки?**  
A: Нет, но можно интегрировать с ФН через отдельный сервис

**Q: Как настроить для разных магазинов?**  
A: Добавьте в модель Store и передавайте в ReceiptService

---

## 🚧 Roadmap

### Планируемые функции:
- [ ] Отправка чека на email
- [ ] Интеграция с ОФД (онлайн фискализация)
- [ ] Поддержка скидок и промокодов
- [ ] Мультиязычность
- [ ] QR-код для проверки чека
- [ ] Экспорт в Excel
- [ ] История печати чеков
- [ ] Шаблоны чеков для разных магазинов

---

## 📝 Changelog

### v1.0.0 (30.01.2026)
- ✅ Добавлена генерация чеков в 4 форматах
- ✅ Создан ReceiptService
- ✅ Добавлен endpoint /sales-api/{id}/receipt
- ✅ Созданы HTML/PDF шаблоны
- ✅ Поддержка термопринтеров
- ✅ Полная документация

---

## 📞 Поддержка

При возникновении проблем:
1. Проверьте [RECEIPT_QUICK_START.md](RECEIPT_QUICK_START.md)
2. Изучите [RECEIPT_API_GUIDE.md](RECEIPT_API_GUIDE.md)
3. Проверьте логи Laravel: `storage/logs/laravel.log`

---

**Документация подготовлена:** 30 января 2026  
**Версия:** 1.0.0  
**Статус:** ✅ Готово к использованию
