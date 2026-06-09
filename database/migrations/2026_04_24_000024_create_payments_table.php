<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('parents')->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('payment_method');
            $table->string('reference')->nullable();
            $table->string('status')->default('completed');
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('student_id');
            $table->index('parent_id');
            $table->index('payment_method');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
