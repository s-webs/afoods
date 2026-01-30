# Примеры тестов для Sale API

## Создание теста

Создайте файл `tests/Feature/SaleApiTest.php`:

```bash
php artisan make:test SaleApiTest
```

---

## Полный код теста

```php
<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Category;
use App\Models\Cashier;
use App\Models\Shift;
use App\Models\Shopper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Создаем тестовые данные
        $category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);

        Product::create([
            'category_id' => $category->id,
            'name' => 'Test Product 1',
            'slug' => 'test-product-1',
            'barcode' => '123456',
            'price_amount' => 1000,
            'sale_price_amount' => 900,
            'quantity' => 100,
        ]);

        Product::create([
            'category_id' => $category->id,
            'name' => 'Test Product 2',
            'slug' => 'test-product-2',
            'barcode' => '789012',
            'price_amount' => 500,
            'sale_price_amount' => 450,
            'quantity' => 50,
        ]);

        Cashier::create([
            'name' => 'Test Cashier',
            'enabled' => true,
        ]);

        Shift::create([
            'opened_at' => now(),
        ]);

        Shopper::create([
            'phone' => '+79001234567',
        ]);
    }

    /**
     * Тест получения списка продаж
     */
    public function test_can_get_sales_list(): void
    {
        // Создаем тестовую продажу
        Sale::create([
            'date' => now(),
            'receipt_number' => 'TEST-001',
            'items' => [
                ['product_id' => 1, 'quantity' => 2, 'price' => 1000],
            ],
            'total_price' => 2000,
        ]);

        $response = $this->getJson('/api/v1/sales-api');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'date',
                        'receipt_number',
                        'items',
                        'total_price',
                    ]
                ],
                'pagination' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ])
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * Тест фильтрации по дате
     */
    public function test_can_filter_sales_by_date(): void
    {
        Sale::create([
            'date' => '2026-01-15 10:00:00',
            'receipt_number' => 'OLD-001',
            'items' => [['product_id' => 1, 'quantity' => 1, 'price' => 1000]],
            'total_price' => 1000,
        ]);

        Sale::create([
            'date' => '2026-01-25 10:00:00',
            'receipt_number' => 'NEW-001',
            'items' => [['product_id' => 1, 'quantity' => 1, 'price' => 1000]],
            'total_price' => 1000,
        ]);

        $response = $this->getJson('/api/v1/sales-api?date_from=2026-01-20');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
        $this->assertEquals('NEW-001', $response->json('data.0.receipt_number'));
    }

    /**
     * Тест получения продажи по ID
     */
    public function test_can_get_sale_by_id(): void
    {
        $sale = Sale::create([
            'cashier_id' => 1,
            'shift_id' => 1,
            'shopper_id' => 1,
            'date' => now(),
            'receipt_number' => 'TEST-001',
            'items' => [
                ['product_id' => 1, 'quantity' => 2, 'price' => 1000],
                ['product_id' => 2, 'quantity' => 1, 'price' => 500],
            ],
            'total_price' => 2500,
        ]);

        $response = $this->getJson("/api/v1/sales-api/{$sale->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $sale->id,
                    'receipt_number' => 'TEST-001',
                    'total_price' => 2500,
                ],
            ])
            ->assertJsonStructure([
                'data' => [
                    'shopper' => ['id', 'phone'],
                ]
            ]);
    }

    /**
     * Тест создания продажи
     */
    public function test_can_create_sale(): void
    {
        $response = $this->postJson('/api/v1/sales-api', [
            'cashier_id' => 1,
            'shift_id' => 1,
            'date' => '2026-01-30 15:00:00',
            'receipt_number' => 'TEST-CREATE-001',
            'items' => [
                ['product_id' => 1, 'quantity' => 2, 'price' => 1000],
                ['product_id' => 2, 'quantity' => 1, 'price' => 500],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Продажа успешно создана',
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'receipt_number',
                    'total_price',
                ],
            ]);

        // Проверяем, что total_price рассчитан автоматически
        $this->assertEquals(2500, $response->json('data.total_price'));

        // Проверяем, что количество товара уменьшилось
        $this->assertEquals(98, Product::find(1)->quantity);
        $this->assertEquals(49, Product::find(2)->quantity);
    }

    /**
     * Тест валидации обязательных полей
     */
    public function test_validation_fails_for_missing_fields(): void
    {
        $response = $this->postJson('/api/v1/sales-api', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date', 'receipt_number', 'items']);
    }

    /**
     * Тест валидации items
     */
    public function test_validation_fails_for_invalid_items(): void
    {
        $response = $this->postJson('/api/v1/sales-api', [
            'date' => now(),
            'receipt_number' => 'TEST-001',
            'items' => [
                ['product_id' => 999, 'quantity' => -1, 'price' => -100],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'items.0.product_id',
                'items.0.quantity',
                'items.0.price',
            ]);
    }

    /**
     * Тест проверки наличия товара на складе
     */
    public function test_cannot_create_sale_with_insufficient_stock(): void
    {
        $response = $this->postJson('/api/v1/sales-api', [
            'date' => now(),
            'receipt_number' => 'TEST-STOCK-001',
            'items' => [
                ['product_id' => 1, 'quantity' => 999, 'price' => 1000],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        $this->assertStringContainsString('Недостаточно', $response->json('message'));
    }

    /**
     * Тест уникальности номера чека
     */
    public function test_receipt_number_must_be_unique(): void
    {
        Sale::create([
            'date' => now(),
            'receipt_number' => 'DUPLICATE-001',
            'items' => [['product_id' => 1, 'quantity' => 1, 'price' => 1000]],
            'total_price' => 1000,
        ]);

        $response = $this->postJson('/api/v1/sales-api', [
            'date' => now(),
            'receipt_number' => 'DUPLICATE-001',
            'items' => [['product_id' => 1, 'quantity' => 1, 'price' => 1000]],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['receipt_number']);
    }

    /**
     * Тест обновления продажи
     */
    public function test_can_update_sale(): void
    {
        $sale = Sale::create([
            'date' => now(),
            'receipt_number' => 'ORIGINAL-001',
            'items' => [['product_id' => 1, 'quantity' => 2, 'price' => 1000]],
            'total_price' => 2000,
        ]);

        $response = $this->putJson("/api/v1/sales-api/{$sale->id}", [
            'receipt_number' => 'UPDATED-001',
            'items' => [
                ['product_id' => 1, 'quantity' => 1, 'price' => 1000],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Продажа успешно обновлена',
                'data' => [
                    'receipt_number' => 'UPDATED-001',
                    'total_price' => 1000,
                ],
            ]);
    }

    /**
     * Тест удаления продажи
     */
    public function test_can_delete_sale(): void
    {
        $sale = Sale::create([
            'date' => now(),
            'receipt_number' => 'DELETE-001',
            'items' => [['product_id' => 1, 'quantity' => 5, 'price' => 1000]],
            'total_price' => 5000,
        ]);

        $initialQuantity = Product::find(1)->quantity;

        $response = $this->deleteJson("/api/v1/sales-api/{$sale->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Продажа успешно удалена',
            ]);

        // Проверяем, что товар вернулся на склад
        $this->assertEquals($initialQuantity + 5, Product::find(1)->quantity);

        // Проверяем, что продажа удалена
        $this->assertDatabaseMissing('sales', ['id' => $sale->id]);
    }

    /**
     * Тест пагинации
     */
    public function test_pagination_works(): void
    {
        // Создаем 25 продаж
        for ($i = 1; $i <= 25; $i++) {
            Sale::create([
                'date' => now(),
                'receipt_number' => "TEST-{$i}",
                'items' => [['product_id' => 1, 'quantity' => 1, 'price' => 1000]],
                'total_price' => 1000,
            ]);
        }

        $response = $this->getJson('/api/v1/sales-api?per_page=10');

        $response->assertStatus(200)
            ->assertJson([
                'pagination' => [
                    'current_page' => 1,
                    'per_page' => 10,
                    'total' => 25,
                    'last_page' => 3,
                ],
            ]);

        $this->assertCount(10, $response->json('data'));
    }

    /**
     * Тест сортировки
     */
    public function test_can_sort_sales(): void
    {
        Sale::create([
            'date' => now(),
            'receipt_number' => 'A-001',
            'items' => [['product_id' => 1, 'quantity' => 1, 'price' => 5000]],
            'total_price' => 5000,
        ]);

        Sale::create([
            'date' => now(),
            'receipt_number' => 'B-001',
            'items' => [['product_id' => 1, 'quantity' => 1, 'price' => 1000]],
            'total_price' => 1000,
        ]);

        $response = $this->getJson('/api/v1/sales-api?sort_by=total_price&sort_order=asc');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals(1000, $data[0]['total_price']);
        $this->assertEquals(5000, $data[1]['total_price']);
    }

    /**
     * Тест получения статистики
     */
    public function test_can_get_sales_statistics(): void
    {
        Sale::create([
            'date' => '2026-01-15',
            'receipt_number' => 'STAT-001',
            'items' => [['product_id' => 1, 'quantity' => 1, 'price' => 1000]],
            'total_price' => 1000,
        ]);

        Sale::create([
            'date' => '2026-01-20',
            'receipt_number' => 'STAT-002',
            'items' => [['product_id' => 1, 'quantity' => 1, 'price' => 2000]],
            'total_price' => 2000,
        ]);

        $response = $this->getJson('/api/v1/sales-api-statistics?date_from=2026-01-01&date_to=2026-01-31');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_sales' => 2,
                    'total_revenue' => 3000,
                    'average_sale' => 1500,
                    'max_sale' => 2000,
                    'min_sale' => 1000,
                ],
            ]);
    }

    /**
     * Тест с несуществующей продажей
     */
    public function test_returns_404_for_nonexistent_sale(): void
    {
        $response = $this->getJson('/api/v1/sales-api/999');

        $response->assertStatus(404);
    }

    /**
     * Тест создания продажи с несуществующим товаром
     */
    public function test_validation_fails_for_nonexistent_product(): void
    {
        $response = $this->postJson('/api/v1/sales-api', [
            'date' => now(),
            'receipt_number' => 'TEST-INVALID',
            'items' => [
                ['product_id' => 9999, 'quantity' => 1, 'price' => 1000],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.product_id']);
    }

    /**
     * Тест eager loading связей
     */
    public function test_shopper_is_loaded_with_sale(): void
    {
        $sale = Sale::create([
            'shopper_id' => 1,
            'date' => now(),
            'receipt_number' => 'TEST-SHOPPER',
            'items' => [['product_id' => 1, 'quantity' => 1, 'price' => 1000]],
            'total_price' => 1000,
        ]);

        $response = $this->getJson("/api/v1/sales-api/{$sale->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'shopper' => ['id', 'phone'],
                ]
            ]);
    }

    /**
     * Тест фильтрации по кассиру
     */
    public function test_can_filter_by_cashier(): void
    {
        Sale::create([
            'cashier_id' => 1,
            'date' => now(),
            'receipt_number' => 'CASHIER-1',
            'items' => [['product_id' => 1, 'quantity' => 1, 'price' => 1000]],
            'total_price' => 1000,
        ]);

        Sale::create([
            'cashier_id' => null,
            'date' => now(),
            'receipt_number' => 'CASHIER-NULL',
            'items' => [['product_id' => 1, 'quantity' => 1, 'price' => 1000]],
            'total_price' => 1000,
        ]);

        $response = $this->getJson('/api/v1/sales-api?cashier_id=1');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
        $this->assertEquals('CASHIER-1', $response->json('data.0.receipt_number'));
    }

    /**
     * Тест поиска по номеру чека
     */
    public function test_can_search_by_receipt_number(): void
    {
        Sale::create([
            'date' => now(),
            'receipt_number' => 'ABC-123',
            'items' => [['product_id' => 1, 'quantity' => 1, 'price' => 1000]],
            'total_price' => 1000,
        ]);

        Sale::create([
            'date' => now(),
            'receipt_number' => 'XYZ-456',
            'items' => [['product_id' => 1, 'quantity' => 1, 'price' => 1000]],
            'total_price' => 1000,
        ]);

        $response = $this->getJson('/api/v1/sales-api?receipt_number=ABC');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
        $this->assertEquals('ABC-123', $response->json('data.0.receipt_number'));
    }
}
```

---

## Запуск тестов

### Запустить все тесты Sale API
```bash
php artisan test --filter SaleApiTest
```

### Запустить конкретный тест
```bash
php artisan test --filter test_can_create_sale
```

### Запустить с подробным выводом
```bash
php artisan test --filter SaleApiTest --verbose
```

---

## Чеклист перед запуском тестов

- [ ] База данных настроена для тестирования (в `phpunit.xml` или `.env.testing`)
- [ ] Применены все миграции
- [ ] Установлены все зависимости (`composer install`)
- [ ] Модели имеют все необходимые связи

---

## Ожидаемые результаты

После применения всех исправлений из **SALE_API_FIXES.md** все тесты должны проходить успешно:

```
PASS  Tests\Feature\SaleApiTest
✓ can get sales list
✓ can filter sales by date
✓ can get sale by id
✓ can create sale
✓ validation fails for missing fields
✓ validation fails for invalid items
✓ cannot create sale with insufficient stock
✓ receipt number must be unique
✓ can update sale
✓ can delete sale
✓ pagination works
✓ can sort sales
✓ can get sales statistics
✓ returns 404 for nonexistent sale
✓ validation fails for nonexistent product
✓ shopper is loaded with sale
✓ can filter by cashier
✓ can search by receipt number

Tests:  18 passed
Time:   2.45s
```
