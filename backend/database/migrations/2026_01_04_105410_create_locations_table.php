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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->enum('type', ['WAREHOUSE', 'OUTLET']);
            $table->text('address')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('person_in_charge')->nullable();
            $table->boolean('is_active')->default(true);
            
            // Link to existing outlets table (optional)
            $table->foreignId('outlet_id')->nullable()->constrained('outlets')->nullOnDelete();
            
            $table->timestamps();
            
            // Indexes
            $table->index('code');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
