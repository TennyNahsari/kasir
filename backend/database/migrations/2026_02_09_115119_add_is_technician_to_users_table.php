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
        // Drop the CHECK constraint on role column
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        
        // Convert role column from enum to VARCHAR (removes the enum constraint)
        DB::statement("ALTER TABLE users ALTER COLUMN role TYPE VARCHAR(20)");
        
        // Add is_technician field
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_technician')->default(false)->after('role');
        });
        
        // Migrate existing technician role users to is_technician flag
        DB::table('users')
            ->where('role', 'technician')
            ->update([
                'is_technician' => true,
                'role' => 'staff'
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore technician role for users with is_technician flag
        DB::table('users')
            ->where('is_technician', true)
            ->update(['role' => 'technician']);
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_technician');
        });
    }
};
