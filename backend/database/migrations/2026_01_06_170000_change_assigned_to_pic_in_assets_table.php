<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            // Drop foreign key and index for assigned_to
            $table->dropForeign(['assigned_to']);
            $table->dropIndex(['assigned_to']);
            
            // Drop the assigned_to column
            $table->dropColumn('assigned_to');
            
            // Add new pic column as string
            $table->string('pic', 100)->nullable()->after('location_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            // Drop pic column
            $table->dropColumn('pic');
            
            // Re-add assigned_to as foreign key
            $table->foreignId('assigned_to')->nullable()->after('location_id')->constrained('users')->nullOnDelete();
            $table->index('assigned_to');
        });
    }
};
