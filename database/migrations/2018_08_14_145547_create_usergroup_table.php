<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsergroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usergroup', function (Blueprint $table) {
            $table->increments('id');
            $table->string('group_name', 100)->unique()->comment('用户组名');
            $table->integer('master_id' )->comment('创建者');
            $table->integer('admin')->nullable()->comment('管理员');
            $table->string('wechat_id')->nullable()->comment('企业微信支付账户');
            $table->string('alipay_id')->nullable()->comment('企业支付宝支付账户');
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
        Schema::dropIfExists('usergroup');
    }
}
