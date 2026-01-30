<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->integer('category_id')->nullable();
            $table->string('name');
            $table->string('new_name')->nullable();
            $table->string('barcode')->unique()->nullable();
            $table->jsonb('images')->nullable();
            $table->longText('description')->nullable();
            $table->jsonb('specs')->nullable();
            $table->string('unit')->default('pcs');
            $table->integer('price_amount')->default(0);
            $table->integer('sale_price_amount')->default(0);
            $table->integer('quantity')->default(0);
            $table->jsonb('obj')->nullable();
            $table->string('slug')->nullable();
            $table->string('fetch_status')->default('not_found');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
