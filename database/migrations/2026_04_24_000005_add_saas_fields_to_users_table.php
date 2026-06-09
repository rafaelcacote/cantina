<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('phone')->nullable()->after('email');
            $table->string('cpf')->nullable()->after('phone');
            $table->string('user_type')->default('tenant_admin')->after('cpf');
            $table->boolean('active')->default(true)->after('user_type');
            $table->timestamp('last_login_at')->nullable()->after('active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn([
                'tenant_id',
                'phone',
                'cpf',
                'user_type',
                'active',
                'last_login_at',
            ]);
        });
    }
};
