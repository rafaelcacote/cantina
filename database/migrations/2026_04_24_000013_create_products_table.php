<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('product_sections')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('product_categories')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('sku')->nullable();
            $table->string('barcode')->nullable();
            $table->string('product_type')->default('resale');
            $table->string('sale_type')->default('unit');
            $table->decimal('price', 10, 2);
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->string('image_url')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('visible_in_app')->default(true);
            $table->boolean('allow_custom_request')->default(false);
            $table->boolean('requires_preparation')->default(false);
            $table->boolean('stock_controlled')->default(true);
            $table->integer('minimum_stock_alert')->default(5);
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('section_id');
            $table->index('category_id');
            $table->index('active');
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
