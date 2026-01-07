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
        Schema::table('goods_receipt_items', function (Blueprint $table) {
            // Service Contract Fields (only applicable if product type = SERVICE)
            $table->date('service_start_date')->nullable()->after('rejected_quantity');
            $table->date('service_end_date')->nullable()->after('service_start_date');
            $table->enum('contract_type', [
                'RENTAL',
                'SUBSCRIPTION',
                'MAINTENANCE',
                'CONSULTING',
                'UTILITY',
                'OTHER'
            ])->nullable()->after('service_end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goods_receipt_items', function (Blueprint $table) {
            $table->dropColumn(['service_start_date', 'service_end_date', 'contract_type']);
        });
    }
};
