<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parental_controls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->boolean('enabled')->default(false);
            $table->string('control_mode')->default('none');
            $table->decimal('daily_spending_limit', 10, 2)->nullable();
            $table->decimal('weekly_spending_limit', 10, 2)->nullable();
            $table->boolean('allow_tab_usage')->default(true);
            $table->boolean('allow_wallet_usage')->default(true);
            $table->boolean('allow_convenience_access')->default(false);
            $table->boolean('allow_snack_access')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'student_id']);
            $table->index('tenant_id');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parental_controls');
    }
};
