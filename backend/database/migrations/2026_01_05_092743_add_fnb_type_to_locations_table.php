<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For PostgreSQL: Change column to varchar first, then back to enum with new values
        DB::statement("ALTER TABLE locations ALTER COLUMN type TYPE VARCHAR(20)");
        DB::statement("ALTER TABLE locations DROP CONSTRAINT IF EXISTS locations_type_check");
        DB::statement("ALTER TABLE locations ADD CONSTRAINT locations_type_check CHECK (type IN ('WAREHOUSE', 'OUTLET', 'FNB'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE locations DROP CONSTRAINT IF EXISTS locations_type_check");
        DB::statement("ALTER TABLE locations ADD CONSTRAINT locations_type_check CHECK (type IN ('WAREHOUSE', 'OUTLET'))");
    }
};
