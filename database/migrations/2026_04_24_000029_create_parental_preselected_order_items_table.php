<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parental_preselected_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('parental_preselected_order_id');

            $table->foreign('parental_preselected_order_id', 'ppo_items_order_fk')
                ->references('id')
                ->on('parental_preselected_orders')
                ->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parental_preselected_order_items');
    }
};
