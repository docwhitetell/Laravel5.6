<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConsumeLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('consume_log', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type')->nullable()->comment('消费支出类型');
            $table->string('name')->comment('消费名');
            $table->double('payment')->comment('实付金额');
            $table->bigInteger('order_id')->comment('订单号');
            $table->integer('payee_id')->comment('收款账户id');
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
        Schema::dropIfExists('consume_log');
    }
}
