<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->comment('店铺拥有者id');
            $table->string('location')->comment('店铺地址');
            $table->string('logo')->defalut('default_shop_logo.png')->comment('店铺logo');
            $table->string('name')->comment('店铺名称');
            $table->string('type')->comment('店铺类型');
            $table->string('description')->nullable()->comment('店铺描述');
            $table->timestamp('open_at')->nullable()->comment('开始营业时间');
            $table->timestamp('close_at')->nullable()->comment('结束营业时间');
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
        Schema::dropIfExists('shops');
    }
}
