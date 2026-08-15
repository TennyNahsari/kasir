<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('transactions', 'has_unconfirmed_addon')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->boolean('has_unconfirmed_addon')->default(false)->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('transactions', 'has_unconfirmed_addon')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn('has_unconfirmed_addon');
            });
        }
    }
};
