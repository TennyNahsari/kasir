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
        // Drop existing constraint
        DB::statement("ALTER TABLE locations DROP CONSTRAINT IF EXISTS locations_type_check");
        
        // Add new constraint with DEPARTMENT type
        DB::statement("ALTER TABLE locations ADD CONSTRAINT locations_type_check CHECK (type IN ('WAREHOUSE', 'OUTLET', 'FNB', 'DEPARTMENT'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop constraint
        DB::statement("ALTER TABLE locations DROP CONSTRAINT IF EXISTS locations_type_check");
        
        // Add back old constraint without DEPARTMENT
        DB::statement("ALTER TABLE locations ADD CONSTRAINT locations_type_check CHECK (type IN ('WAREHOUSE', 'OUTLET', 'FNB'))");
    }
};
