<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->decimal('balance', 10, 2)->default(0);
            $table->decimal('credit_limit', 10, 2)->default(0);
            $table->boolean('allow_negative_balance')->default(false);
            $table->timestamps();

            $table->unique(['tenant_id', 'student_id']);
            $table->index('tenant_id');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_wallets');
    }
};
