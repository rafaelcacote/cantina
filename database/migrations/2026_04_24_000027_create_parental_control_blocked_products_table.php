<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parental_control_blocked_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parental_control_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(
                ['tenant_id', 'parental_control_id', 'product_id'],
                'pc_blocked_prod_unique'
            );
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parental_control_blocked_products');
    }
};
