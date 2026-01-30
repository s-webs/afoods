# 🚀 Быстрый старт: Генерация чеков

## Шаг 1: Установка (5 минут)

```bash
# Установить пакет для PDF
composer require barryvdh/laravel-dompdf
```

## Шаг 2: Проверка (1 минута)

Убедитесь, что все файлы созданы:
- ✅ `app/Services/ReceiptService.php`
- ✅ `resources/views/receipts/receipt-html.blade.php`
- ✅ `resources/views/receipts/receipt-pdf.blade.php`
- ✅ Роут добавлен в `routes/api.php`
- ✅ Метод `receipt()` добавлен в `SaleApiController`

## Шаг 3: Тестирование (3 минуты)

### Создайте тестовую продажу

```bash
curl -X POST http://localhost/api/v1/sales-api \
  -H "Content-Type: application/json" \
  -d '{
    "cashier_id": 1,
    "shift_id": 1,
    "date": "2026-01-30 15:00:00",
    "receipt_number": "TEST-001",
    "items": [
      {"product_id": 1, "quantity": 2, "price": 1000}
    ],
    "total_price": 2000
  }'
```

### Получите чек в JSON

```bash
curl http://localhost/api/v1/sales-api/1/receipt?format=json
```

### Откройте HTML чек в браузере

```
http://localhost/api/v1/sales-api/1/receipt?format=html
```

### Скачайте PDF чек

```
http://localhost/api/v1/sales-api/1/receipt?format=pdf
```

### Получите текстовый чек

```bash
curl http://localhost/api/v1/sales-api/1/receipt?format=text
```

## Готово! ✅

Теперь у вас работает генерация чеков в 4 форматах:
- ✅ JSON - для API интеграций
- ✅ HTML - для браузера
- ✅ PDF - для скачивания и печати
- ✅ TEXT - для термопринтеров

---

## Примеры использования

### В JavaScript (Frontend)

```javascript
// Просмотр HTML чека в новой вкладке
function viewReceipt(saleId) {
  window.open(
    `/api/v1/sales-api/${saleId}/receipt?format=html`,
    '_blank'
  );
}

// Скачать PDF
function downloadPdf(saleId) {
  window.location.href = `/api/v1/sales-api/${saleId}/receipt?format=pdf`;
}

// Получить данные чека
async function getReceiptData(saleId) {
  const response = await fetch(`/api/v1/sales-api/${saleId}/receipt?format=json`);
  const data = await response.json();
  console.log(data);
}
```

### В PHP

```php
use App\Services\ReceiptService;
use App\Models\Sale;

$sale = Sale::find(1);
$receiptService = new ReceiptService();

// HTML
$html = $receiptService->generateHtml($sale);

// PDF
$pdf = $receiptService->generatePdf($sale);
$pdf->download('receipt.pdf');

// JSON
$json = $receiptService->generateJson($sale);

// TEXT
$text = $receiptService->generateText($sale);
```

---

## Настройка

### Изменить внешний вид HTML/PDF чека

Отредактируйте файлы:
- `resources/views/receipts/receipt-html.blade.php`
- `resources/views/receipts/receipt-pdf.blade.php`

### Изменить текстовый чек

Отредактируйте:
- `app/Services/ReceiptService.php` (метод `generateText`)

### Добавить логотип

В view файлы добавьте:
```html
<img src="{{ asset('images/logo.png') }}" alt="Logo">
```

---

## Документация

📚 Полная документация: [RECEIPT_API_GUIDE.md](RECEIPT_API_GUIDE.md)

---

## Возможные проблемы

### Ошибка "Class 'PDF' not found"
**Решение:** Установите пакет
```bash
composer require barryvdh/laravel-dompdf
```

### Ошибка "View not found"
**Решение:** Убедитесь, что файлы view созданы в `resources/views/receipts/`

### PDF не генерируется
**Решение:** Проверьте права на запись в `storage/` папке
```bash
chmod -R 775 storage
```

---

**Успехов! 🎉**
