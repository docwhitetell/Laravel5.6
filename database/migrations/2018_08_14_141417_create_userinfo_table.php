<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserinfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('userinfo', function (Blueprint $table) {

            $table->integer('user_id')->unique();
            $table->smallInteger('year')->nullable();
            $table->string('wechat_id', 30)->nullable();
            $table->string('alipay_id', 30)->nullable();
            $table->integer('wallet_id')->nullable()->comment('钱包id');
            $table->integer('group_id')->nullable();
            $table->timestamps();

            $table->primary('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('userinfo');
    }
}
