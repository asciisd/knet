<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('knet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();

            $table->string('paymentid')->nullable();
            $table->string('result')->nullable();
            $table->string('auth')->nullable();
            $table->string('avr')->nullable();
            $table->string('ref')->nullable();
            $table->string('tranid')->nullable();
            $table->string('trackid');
            $table->string('postdate')->nullable();
            $table->string('udf1')->nullable();
            $table->string('udf2')->nullable();
            $table->string('udf3')->nullable();
            $table->string('udf4')->nullable();
            $table->string('udf5')->nullable();
            $table->string('udf6')->nullable();
            $table->string('udf7')->nullable();
            $table->string('udf8')->nullable();
            $table->string('udf9')->nullable();
            $table->string('udf10')->nullable();
            $table->string('amt')->nullable();
            $table->integer('rspcode')->nullable();
            $table->boolean('paid')->nullable();
            $table->string('error_text')->nullable();
            $table->string('error')->nullable();
            $table->boolean('livemode')->default(false);
            $table->string('card_number')->nullable();
            $table->string('brand_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('url');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knet_transactions');
    }
};
