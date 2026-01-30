<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateProductsToExternal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:migrate-to-external
                            {--chunk=100 : Number of records to process at a time}
                            {--dry-run : Run without actually migrating data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate all products from local database to external database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $chunkSize = (int) $this->option('chunk');

        $this->info('Starting product migration...');

        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No data will be migrated');
        }

        try {
            // Проверка подключений
            $this->info('Checking database connections...');
            
            DB::connection('pgsql')->getPdo();
            $this->info('✓ Local database connected');
            
            DB::connection('pgsql_external')->getPdo();
            $this->info('✓ External database connected');

            // Подсчет записей в локальной БД
            $totalProducts = DB::connection('pgsql')
                ->table('products')
                ->count();

            $this->info("Found {$totalProducts} products in local database");

            if ($totalProducts === 0) {
                $this->warn('No products to migrate!');
                return Command::SUCCESS;
            }

            // Подсчет записей во внешней БД
            $externalProducts = DB::connection('pgsql_external')
                ->table('products')
                ->count();

            $this->info("External database currently has {$externalProducts} products");

            if (!$isDryRun) {
                if (!$this->confirm('Do you want to continue with the migration?')) {
                    $this->warn('Migration cancelled by user');
                    return Command::SUCCESS;
                }
            }

            $migratedCount = 0;
            $skippedCount = 0;
            $errorCount = 0;

            $progressBar = $this->output->createProgressBar($totalProducts);
            $progressBar->start();

            // Миграция данных чанками
            DB::connection('pgsql')
                ->table('products')
                ->orderBy('id')
                ->chunk($chunkSize, function ($products) use (
                    &$migratedCount,
                    &$skippedCount,
                    &$errorCount,
                    $isDryRun,
                    $progressBar
                ) {
                    foreach ($products as $product) {
                        $progressBar->advance();

                        if ($isDryRun) {
                            $migratedCount++;
                            continue;
                        }

                        try {
                            // Проверяем, существует ли продукт во внешней БД
                            $exists = DB::connection('pgsql_external')
                                ->table('products')
                                ->where('id', $product->id)
                                ->exists();

                            if ($exists) {
                                // Обновляем существующий продукт
                                DB::connection('pgsql_external')
                                    ->table('products')
                                    ->where('id', $product->id)
                                    ->update((array) $product);
                                $migratedCount++;
                            } else {
                                // Вставляем новый продукт
                                DB::connection('pgsql_external')
                                    ->table('products')
                                    ->insert((array) $product);
                                $migratedCount++;
                            }
                        } catch (\Exception $e) {
                            $errorCount++;
                            $this->newLine();
                            $this->error("Error migrating product ID {$product->id}: " . $e->getMessage());
                        }
                    }
                });

            $progressBar->finish();
            $this->newLine(2);

            // Результаты
            $this->info('Migration completed!');
            $this->table(
                ['Status', 'Count'],
                [
                    ['Migrated', $migratedCount],
                    ['Skipped', $skippedCount],
                    ['Errors', $errorCount],
                    ['Total', $totalProducts],
                ]
            );

            if ($isDryRun) {
                $this->warn('This was a DRY RUN - no data was actually migrated');
            } else {
                // Проверка финального состояния
                $finalCount = DB::connection('pgsql_external')
                    ->table('products')
                    ->count();
                $this->info("External database now has {$finalCount} products");

                // Исправляем последовательность ID для PostgreSQL
                $this->info('Fixing product ID sequence...');
                $maxId = DB::connection('pgsql_external')
                    ->table('products')
                    ->max('id') ?? 0;
                
                $nextId = $maxId + 1;
                DB::connection('pgsql_external')->statement(
                    "SELECT setval('products_id_seq', ?, false)",
                    [$nextId]
                );
                
                $this->info("✓ Sequence fixed. Next product ID will be: {$nextId}");
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Migration failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
