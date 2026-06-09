<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('item_name_snapshot');
            $table->decimal('unit_price', 10, 2);
            $table->integer('quantity');
            $table->decimal('total_price', 10, 2);
            $table->text('observation')->nullable();
            $table->text('custom_request_text')->nullable();
            $table->string('item_status')->default('pending');
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('order_id');
            $table->index('product_id');
            $table->index('item_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
