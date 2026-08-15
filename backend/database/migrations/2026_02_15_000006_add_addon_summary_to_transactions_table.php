<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('transactions', 'addon_summary')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->text('addon_summary')->nullable()->after('has_unconfirmed_addon');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('transactions', 'addon_summary')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn('addon_summary');
            });
        }
    }
};
