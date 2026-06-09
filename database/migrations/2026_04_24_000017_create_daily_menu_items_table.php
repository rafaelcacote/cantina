<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('daily_menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('planned_quantity')->nullable();
            $table->integer('available_quantity')->nullable();
            $table->decimal('price_override', 10, 2)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'daily_menu_id', 'product_id']);
            $table->index('tenant_id');
            $table->index('daily_menu_id');
            $table->index('product_id');
            $table->index('active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_menu_items');
    }
};
