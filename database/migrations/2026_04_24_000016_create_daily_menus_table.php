<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->date('menu_date');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'school_id', 'menu_date']);
            $table->index('tenant_id');
            $table->index('school_id');
            $table->index('menu_date');
            $table->index('active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_menus');
    }
};
