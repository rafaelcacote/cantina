<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('parents')->nullOnDelete();
            $table->foreignId('placed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('order_channel')->default('app');
            $table->string('order_type')->default('immediate');
            $table->string('status')->default('pending');
            $table->string('payment_mode')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('final_amount', 10, 2)->default(0);
            $table->timestamp('scheduled_for')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('school_id');
            $table->index('student_id');
            $table->index('parent_id');
            $table->index('status');
            $table->index('payment_mode');
            $table->index('order_channel');
            $table->index('order_type');
            $table->index('scheduled_for');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
