<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigInteger('id');
            $table->integer('consumer_id')->comment('消费者id');
            $table->integer('payee_id')->comment('收款人id');
            $table->double('amount')->comment('订单金额');
            $table->enum('status', [
                    'done'=>'订单完成',
                    'paid'=>'已付款',
                    'fail'=>'交易失败',
                    'cancel'=>'订单已取消',
                    'waiting_pay'=>'未支付'
                ])->comment('订单状态');
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
        Schema::dropIfExists('orders');
    }
}
