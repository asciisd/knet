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
            $table->after('udf5', function () use ($table) {
                $table->string('udf6')->nullable();
                $table->string('udf7')->nullable();
                $table->string('udf8')->nullable();
                $table->string('udf9')->nullable();
                $table->string('udf10')->nullable();
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
