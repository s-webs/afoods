# 🧾 Руководство по API генерации чеков

## Обзор

API генерации чеков позволяет получать чеки для продаж в различных форматах:
- **JSON** - для кассовых систем и мобильных приложений
- **HTML** - для просмотра и печати в браузере
- **PDF** - для скачивания и архивирования
- **TEXT** - для термопринтеров (чековых принтеров 80мм)

---

## Endpoint

```
GET /api/v1/sales-api/{id}/receipt
```

### Параметры

| Параметр | Тип | Обязательный | По умолчанию | Описание |
|----------|-----|--------------|--------------|----------|
| `id` | integer | Да | - | ID продажи |
| `format` | string | Нет | `json` | Формат чека |

### Доступные форматы

- `json` - JSON объект
- `html` - HTML страница
- `pdf` - PDF файл (скачивание)
- `pdf-inline` - PDF файл (просмотр в браузере)
- `text` - Текстовый формат для термопринтера

---

## Примеры использования

### 1. JSON формат (для API интеграций)

**Запрос:**
```bash
curl http://localhost/api/v1/sales-api/1/receipt?format=json
```

**Ответ:**
```json
{
  "success": true,
  "data": {
    "receipt_number": "RCP-2026-001",
    "date": "30.01.2026 14:25:00",
    "cashier": {
      "id": 1,
      "name": "Иванова А.С."
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
        "name": "Молоко Простоквашино 3.2% 1л",
        "barcode": "4607025392119",
        "quantity": 2,
        "price": 8900,
        "total": 17800
      },
      {
        "name": "Хлеб Бородинский 400г",
        "barcode": "4600957023456",
        "quantity": 1,
        "price": 4500,
        "total": 4500
      }
    ],
    "subtotal": 22300,
    "total": 22300,
    "payment_method": "cash"
  }
}
```

**Использование (JavaScript):**
```javascript
async function getReceipt(saleId) {
  const response = await fetch(`/api/v1/sales-api/${saleId}/receipt?format=json`);
  const data = await response.json();
  
  if (data.success) {
    console.log('Чек №:', data.data.receipt_number);
    console.log('Итого:', data.data.total / 100, '₽');
    
    data.data.items.forEach(item => {
      console.log(`${item.name}: ${item.quantity} x ${item.price / 100} ₽`);
    });
  }
}

getReceipt(1);
```

---

### 2. HTML формат (для браузера)

**Запрос:**
```bash
curl http://localhost/api/v1/sales-api/1/receipt?format=html
```

**Ответ:** HTML страница с чеком

**Использование:**
- Открыть в новой вкладке: `window.open('/api/v1/sales-api/1/receipt?format=html')`
- Встроить в iframe: `<iframe src="/api/v1/sales-api/1/receipt?format=html"></iframe>`
- Распечатать: после открытия нажать Ctrl+P или вызвать `window.print()`

**Пример (HTML/JavaScript):**
```html
<!-- Кнопка для просмотра чека -->
<button onclick="viewReceipt(1)">Просмотреть чек</button>

<!-- Кнопка для печати чека -->
<button onclick="printReceipt(1)">Распечатать чек</button>

<script>
function viewReceipt(saleId) {
  window.open(
    `/api/v1/sales-api/${saleId}/receipt?format=html`,
    '_blank',
    'width=500,height=700'
  );
}

function printReceipt(saleId) {
  const printWindow = window.open(
    `/api/v1/sales-api/${saleId}/receipt?format=html`,
    '_blank'
  );
  
  printWindow.onload = function() {
    printWindow.print();
  };
}
</script>
```

---

### 3. PDF формат (скачивание)

**Запрос:**
```bash
curl -O http://localhost/api/v1/sales-api/1/receipt?format=pdf
```

**Результат:** Скачивание файла `receipt-RCP-2026-001.pdf`

**Использование (HTML):**
```html
<!-- Прямая ссылка для скачивания -->
<a href="/api/v1/sales-api/1/receipt?format=pdf" download>
  Скачать чек (PDF)
</a>

<!-- Кнопка со скачиванием через JavaScript -->
<button onclick="downloadReceipt(1)">Скачать PDF</button>

<script>
function downloadReceipt(saleId) {
  const link = document.createElement('a');
  link.href = `/api/v1/sales-api/${saleId}/receipt?format=pdf`;
  link.download = `receipt-${saleId}.pdf`;
  link.click();
}
</script>
```

---

### 4. PDF формат (просмотр в браузере)

**Запрос:**
```bash
curl http://localhost/api/v1/sales-api/1/receipt?format=pdf-inline
```

**Результат:** PDF файл открывается в браузере (inline)

**Использование:**
```html
<!-- Встроить PDF в страницу -->
<iframe 
  src="/api/v1/sales-api/1/receipt?format=pdf-inline" 
  width="100%" 
  height="800px"
  style="border: 1px solid #ccc;">
</iframe>

<!-- Открыть в новой вкладке -->
<button onclick="viewPdfReceipt(1)">Просмотреть PDF</button>

<script>
function viewPdfReceipt(saleId) {
  window.open(
    `/api/v1/sales-api/${saleId}/receipt?format=pdf-inline`,
    '_blank'
  );
}
</script>
```

---

### 5. TEXT формат (для термопринтера)

**Запрос:**
```bash
curl http://localhost/api/v1/sales-api/1/receipt?format=text
```

**Ответ (text/plain):**
```
            AFOODS            
     Продуктовый магазин      
--------------------------------

Чек № RCP-2026-001
Дата: 30.01.2026 14:25:00
Кассир: Иванова А.С.
Смена: 5

================================

Молоко Простоквашино 3.2% 1л
  2 x 89.00 ₽         178.00 ₽

Хлеб Бородинский 400г
  1 x 45.00 ₽          45.00 ₽

================================

ИТОГО:                223.00 ₽

--------------------------------
     Спасибо за покупку!     
       Приходите еще!        
--------------------------------
```

**Использование (для термопринтера):**

#### Вариант 1: Прямая отправка через JavaScript
```javascript
async function printThermal(saleId) {
  const response = await fetch(`/api/v1/sales-api/${saleId}/receipt?format=text`);
  const text = await response.text();
  
  // Отправка на термопринтер через специальный драйвер
  // Например, через WebUSB или специальное приложение
  sendToThermalPrinter(text);
}
```

#### Вариант 2: Через ESC/POS команды
```javascript
async function printEscPos(saleId) {
  const response = await fetch(`/api/v1/sales-api/${saleId}/receipt?format=text`);
  const text = await response.text();
  
  // Конвертация в ESC/POS команды
  const escPosCommands = convertToEscPos(text);
  
  // Отправка на принтер
  await sendToPort(escPosCommands);
}
```

#### Вариант 3: Через backend (PHP)
```php
// В контроллере или сервисе
$receipt = file_get_contents("http://localhost/api/v1/sales-api/1/receipt?format=text");

// Отправка на термопринтер через PHP принтер
$connector = new NetworkPrintConnector("192.168.1.100", 9100);
$printer = new Printer($connector);
$printer->text($receipt);
$printer->cut();
$printer->close();
```

---

## Структура чека

### Заголовок
- Название магазина
- Тип магазина/описание

### Информация о продаже
- Номер чека
- Дата и время
- Кассир (если указан)
- Смена (если указана)
- Покупатель (если указан)

### Список товаров
Для каждого товара:
- Название
- Штрихкод (если есть)
- Количество x Цена за единицу
- Итого за товар

### Итого
- Общая сумма покупки

### Подвал
- Благодарность
- Дата печати чека

---

## Интеграция с кассовым ПО

### Пример интеграции с POS-системой

```javascript
class ReceiptService {
  constructor(baseUrl) {
    this.baseUrl = baseUrl;
  }

  // Получить JSON данные чека
  async getReceiptData(saleId) {
    const response = await fetch(
      `${this.baseUrl}/api/v1/sales-api/${saleId}/receipt?format=json`
    );
    return await response.json();
  }

  // Печать чека на термопринтере
  async printReceipt(saleId) {
    const response = await fetch(
      `${this.baseUrl}/api/v1/sales-api/${saleId}/receipt?format=text`
    );
    const text = await response.text();
    
    // Отправка на принтер
    await this.sendToPrinter(text);
  }

  // Отправка чека на email
  async emailReceipt(saleId, email) {
    const pdfUrl = `${this.baseUrl}/api/v1/sales-api/${saleId}/receipt?format=pdf`;
    
    // Отправка через ваш email сервис
    await this.emailService.send({
      to: email,
      subject: `Чек № ${saleId}`,
      attachments: [{ url: pdfUrl }]
    });
  }

  // Сохранение чека в архив
  async archiveReceipt(saleId) {
    const response = await fetch(
      `${this.baseUrl}/api/v1/sales-api/${saleId}/receipt?format=pdf`
    );
    const blob = await response.blob();
    
    // Сохранение в локальное хранилище или облако
    await this.storage.save(`receipts/${saleId}.pdf`, blob);
  }

  async sendToPrinter(text) {
    // Реализация отправки на принтер
    console.log('Printing:', text);
  }
}

// Использование
const receiptService = new ReceiptService('http://localhost');

// Печать чека
await receiptService.printReceipt(1);

// Отправка на email
await receiptService.emailReceipt(1, 'customer@example.com');

// Архивирование
await receiptService.archiveReceipt(1);
```

---

## Настройка внешнего вида чека

### HTML/PDF чек

Чтобы изменить внешний вид HTML/PDF чека, отредактируйте файлы:
- `resources/views/receipts/receipt-html.blade.php` - для HTML
- `resources/views/receipts/receipt-pdf.blade.php` - для PDF

### Текстовый чек

Для изменения текстового чека, отредактируйте:
- `app/Services/ReceiptService.php` - метод `generateText()`

Параметры:
- `$width` - ширина чека в символах (по умолчанию 32 для 80мм принтера)
- Центрирование, выравнивание текста
- Разделители (линии, двойные линии)

---

## Коды ошибок

| Код | Описание | Решение |
|-----|----------|---------|
| 404 | Продажа не найдена | Проверьте ID продажи |
| 500 | Ошибка генерации PDF | Проверьте установку dompdf |
| 500 | Товар не найден | Проверьте наличие товаров в БД |

---

## FAQ

### Как добавить QR-код на чек?

Отредактируйте view файлы и добавьте:

```html
<!-- В receipt-html.blade.php или receipt-pdf.blade.php -->
<div class="qr-code">
  <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ urlencode($sale->receipt_number) }}" alt="QR Code">
</div>
```

### Как изменить размер PDF чека?

В `ReceiptService.php`, метод `generatePdf()`:

```php
// Для A4
$pdf->setPaper('a4', 'portrait');

// Для 58mm термопринтера
$pdf->setPaper([0, 0, 164.41, 841.89], 'portrait');

// Для 80mm термопринтера (по умолчанию)
$pdf->setPaper([0, 0, 226.77, 841.89], 'portrait');
```

### Как добавить логотип магазина?

В view файлы добавьте:

```html
<div class="logo">
  <img src="{{ asset('images/logo.png') }}" alt="Logo" style="max-width: 100px;">
</div>
```

### Как отправить чек на email?

Создайте отдельный endpoint или используйте существующий с email параметром.

---

## Production рекомендации

1. **Кэширование:** Для часто запрашиваемых чеков добавьте кэширование PDF/HTML
2. **Очередь:** Генерацию PDF можно вынести в очередь для больших чеков
3. **Хранение:** Сохраняйте PDF чеки в S3 или локальном хранилище
4. **Логирование:** Логируйте все запросы генерации чеков
5. **Rate limiting:** Ограничьте частоту генерации чеков с одного IP

---

## Тестирование

```bash
# Тест JSON формата
curl http://localhost/api/v1/sales-api/1/receipt?format=json

# Тест HTML формата
curl http://localhost/api/v1/sales-api/1/receipt?format=html > receipt.html

# Тест PDF формата
curl -O http://localhost/api/v1/sales-api/1/receipt?format=pdf

# Тест текстового формата
curl http://localhost/api/v1/sales-api/1/receipt?format=text
```

---

**Документация подготовлена:** 30 января 2026
