<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixProductSequence extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:fix-sequence
                            {--connection=pgsql : Database connection to fix}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix the products table auto-increment sequence in PostgreSQL';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $connection = $this->option('connection');

        try {
            $this->info("Fixing products sequence on '{$connection}' connection...");

            // Get the current maximum ID from products table
            $maxId = DB::connection($connection)
                ->table('products')
                ->max('id') ?? 0;

            $this->info("Current maximum product ID: {$maxId}");

            // Reset the sequence to max_id + 1
            $nextId = $maxId + 1;
            
            DB::connection($connection)->statement(
                "SELECT setval('products_id_seq', ?, false)",
                [$nextId]
            );

            $this->info("✓ Sequence reset successfully. Next ID will be: {$nextId}");

            // Verify the fix
            $currentSequenceValue = DB::connection($connection)
                ->selectOne("SELECT last_value FROM products_id_seq");

            $this->info("✓ Verified: Sequence last_value is now {$currentSequenceValue->last_value}");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to fix sequence: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
