<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_tabs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->decimal('current_balance', 10, 2)->default(0);
            $table->string('billing_cycle_type')->default('monthly');
            $table->integer('due_day')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'student_id']);
            $table->index('tenant_id');
            $table->index('student_id');
            $table->index('active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_tabs');
    }
};
