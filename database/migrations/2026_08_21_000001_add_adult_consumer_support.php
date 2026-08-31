<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('profile_kind')->default('student')->after('user_id');
            $table->index('profile_kind');
        });

        Schema::table('parents', function (Blueprint $table) {
            $table->foreignId('self_student_id')
                ->nullable()
                ->after('user_id')
                ->constrained('students')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('parents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('self_student_id');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['profile_kind']);
            $table->dropColumn('profile_kind');
        });
    }
};
