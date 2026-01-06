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
        // For PostgreSQL, we need to alter the enum type
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        DB::statement("ALTER TABLE users ALTER COLUMN role TYPE VARCHAR(50)");
        
        // Add back constraint with staff included
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('owner', 'supervisor', 'kasir', 'kitchen', 'staff'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove constraint
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        
        // Remove staff role from existing users (set to kasir)
        DB::statement("UPDATE users SET role = 'kasir' WHERE role = 'staff'");
        
        // Add back old constraint without staff
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('owner', 'supervisor', 'kasir', 'kitchen'))");
    }
};
