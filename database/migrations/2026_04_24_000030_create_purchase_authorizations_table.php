<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_authorizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('tab_entry_id')->nullable()->constrained()->nullOnDelete();
            $table->string('authorization_type')->default('tab_confirmation');
            $table->string('auth_method')->default('pin');
            $table->boolean('success')->default(false);
            $table->string('failure_reason')->nullable();
            $table->string('device_type')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('school_id');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_authorizations');
    }
};
