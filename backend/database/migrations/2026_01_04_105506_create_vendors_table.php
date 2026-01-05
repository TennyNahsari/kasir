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
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            
            // Contact
            $table->text('address')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('contact_person')->nullable();
            
            // Legal
            $table->string('tax_id', 100)->nullable();
            $table->string('business_license', 100)->nullable();
            
            // Terms
            $table->integer('payment_term_days')->default(30);
            $table->decimal('credit_limit', 15, 2)->default(0);
            
            // Rating (1-5)
            $table->decimal('rating', 2, 1)->default(0);
            
            // Status
            $table->boolean('is_active')->default(true);
            
            // Metadata
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('code');
            $table->index('name');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
