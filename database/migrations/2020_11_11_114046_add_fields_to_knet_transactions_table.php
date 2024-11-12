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
        Schema::table('knet_transactions', function (Blueprint $table) {
            $table->string('card_number')
                ->after('auth_resp_code')
                ->nullable();

            $table->string('brand_id')
                ->after('card_number')
                ->nullable();

            $table->string('ip_address')
                ->after('brand_id')
                ->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('knet_transactions', function (Blueprint $table) {
            $table->dropColumn('card_number', 'brand_id', 'ip_address');
        });
    }
};
