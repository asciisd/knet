<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('knet_transactions', function (Blueprint $table) {
            $table->after('utf5', function () use ($table) {
                $table->string('utf6')->nullable();
                $table->string('utf7')->nullable();
                $table->string('utf8')->nullable();
                $table->string('utf9')->nullable();
                $table->string('utf10')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('knet_transactions', function (Blueprint $table) {
            $table->dropColumn(['utf6', 'utf7', 'utf8', 'utf9', 'utf10']);
        });
    }
};
