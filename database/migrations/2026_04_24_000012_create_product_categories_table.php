<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('product_sections')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('section_id');
            $table->index('active');
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_categories');
    }
};
