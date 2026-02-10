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
        // First, update any invalid roles to a valid role
        // This ensures we can add the new constraint
        $validRoles = ['owner', 'supervisor', 'kasir', 'kitchen', 'warehouse', 'procurement'];
        
        // Update any users with invalid roles to 'kasir' (default)
        DB::statement("
            UPDATE users 
            SET role = 'kasir' 
            WHERE role NOT IN ('" . implode("','", $validRoles) . "')
        ");
        
        // Drop existing check constraint if exists
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        
        // Add new check constraint with technician role
        DB::statement("
            ALTER TABLE users ADD CONSTRAINT users_role_check 
            CHECK (role IN ('owner', 'supervisor', 'kasir', 'kitchen', 'warehouse', 'procurement', 'technician'))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the constraint
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        
        // Restore old constraint without technician
        DB::statement("
            ALTER TABLE users ADD CONSTRAINT users_role_check 
            CHECK (role IN ('owner', 'supervisor', 'kasir', 'kitchen', 'warehouse', 'procurement'))
        ");
    }
};
