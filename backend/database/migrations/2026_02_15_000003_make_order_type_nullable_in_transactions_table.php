<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE transactions ALTER COLUMN order_type DROP NOT NULL");
            DB::statement("ALTER TABLE transactions ALTER COLUMN order_type SET DEFAULT NULL");
        } else if ($driver === 'mysql') {
            DB::statement("ALTER TABLE transactions MODIFY order_type VARCHAR(50) NULL DEFAULT NULL");
        } else {
            try {
                Schema::table('transactions', function (Blueprint $table) {
                    $table->string('order_type')->nullable()->change();
                });
            } catch (\Throwable $e) {
                // Ignore if driver handles differently
            }
        }
    }

    public function down(): void
    {
    }
};
