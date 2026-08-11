<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('order_id')
                ->nullable()
                ->after('parent_id')
                ->constrained('orders')
                ->nullOnDelete();
            $table->foreignId('tab_entry_id')
                ->nullable()
                ->after('order_id')
                ->constrained('tab_entries')
                ->nullOnDelete();

            $table->index('order_id');
            $table->index('tab_entry_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('order_id');
            $table->dropConstrainedForeignId('tab_entry_id');
        });
    }
};
