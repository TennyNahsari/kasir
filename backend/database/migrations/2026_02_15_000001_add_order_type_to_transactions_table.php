<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('transactions', 'order_type')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->enum('order_type', ['dine_in', 'take_away'])->nullable()->default(null)->after('table_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('transactions', 'order_type')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn('order_type');
            });
        }
    }
};
