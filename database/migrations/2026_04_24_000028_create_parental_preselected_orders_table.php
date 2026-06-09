<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parental_preselected_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->constrained('parents')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->date('order_date');
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('school_id');
            $table->index('parent_id');
            $table->index('student_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parental_preselected_orders');
    }
};
