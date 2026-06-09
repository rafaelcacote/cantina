<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('enrollment_number')->nullable();
            $table->string('name');
            $table->date('birth_date')->nullable();
            $table->string('grade')->nullable();
            $table->string('classroom')->nullable();
            $table->string('shift')->nullable();
            $table->string('status')->default('pending');
            $table->string('photo_url')->nullable();
            $table->string('personal_pin_hash')->nullable();
            $table->boolean('can_buy_on_credit')->default(false);
            $table->boolean('can_buy_on_tab')->default(false);
            $table->boolean('convenience_access')->default(false);
            $table->boolean('snack_access')->default(true);
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('school_id');
            $table->index('status');
            $table->index('name');
            $table->index('enrollment_number');
            $table->unique(['tenant_id', 'enrollment_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
