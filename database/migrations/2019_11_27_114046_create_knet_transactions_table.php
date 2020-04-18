<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKnetTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('knet_transactions', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('error_text')->nullable();
            $table->string('paymentid')->nullable();
            $table->boolean('paid')->nullable();
            $table->string('result')->nullable();
            $table->string('auth')->nullable();
            $table->string('avr')->nullable();
            $table->string('ref')->nullable();
            $table->string('tranid')->nullable();
            $table->string('postdate')->nullable();
            $table->string('udf1')->nullable();
            $table->string('udf2')->nullable();
            $table->string('udf3')->nullable();
            $table->string('udf4')->nullable();
            $table->string('udf5')->nullable();
            $table->string('amt')->nullable();
            $table->string('error')->nullable();
            $table->integer('auth_resp_code')->nullable();

            $table->uuid('trackid');
            $table->boolean('livemode')->default(false);
            $table->text('url');

            $table->foreign('user_id')->on('users')->references('id')->onDelete('CASCADE');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('knet_transactions');
    }
}
