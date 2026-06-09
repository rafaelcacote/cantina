<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tab_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_tab_id')->constrained('student_tabs')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('description')->nullable();
            $table->date('entry_date');
            $table->string('status')->default('open');
            $table->boolean('authorized_by_pin')->default(false);
            $table->string('authorization_method')->nullable();
            $table->timestamp('authorized_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('student_tab_id');
            $table->index('student_id');
            $table->index('entry_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tab_entries');
    }
};
