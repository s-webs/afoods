# Установка генерации чеков для Sale API

## Установка зависимостей

### 1. Установить пакет для генерации PDF

```bash
composer require barryvdh/laravel-dompdf
```

### 2. Опубликовать конфигурацию (опционально)

```bash
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
```

## Настройка

Пакет установится автоматически и будет готов к использованию.

### Опциональная настройка в config/app.php

Если нужно, добавьте в `providers`:

```php
Barryvdh\DomPDF\ServiceProvider::class,
```

И в `aliases`:

```php
'PDF' => Barryvdh\DomPDF\Facade\Pdf::class,
```

## Проверка установки

```bash
# Проверить, что пакет установлен
composer show barryvdh/laravel-dompdf
```

## Альтернативные пакеты (если dompdf не подходит)

### Snappy (использует wkhtmltopdf)
```bash
composer require barryvdh/laravel-snappy
```

### TCPDF
```bash
composer require elibyy/tcpdf-laravel
```

## Готово!

После установки все endpoints для генерации чеков будут работать.
