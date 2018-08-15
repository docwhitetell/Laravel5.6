<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopCertifyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_certify', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('owner')->comment('店铺拥有者id');
            $table->integer('shop_id')->comment('店铺id');
            $table->enum('approve',['failed'=>'审核失败', 'auditing'=>'正在审核', 'approve'=>'通过审核'])->comment('审核结果');
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
        Schema::dropIfExists('shop_certify');
    }
}
