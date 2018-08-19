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
            $table->string('open_at', 10)->nullable()->default('9:00')->comment('开始营业时间');
            $table->string('close_at',10)->nullable()->default('22:00')->comment('结束营业时间');
            $table->enum('status', ['close'=>'打烊了','open'=>'营业中'])->default('打烊了');
            $table->enum('certify', ['failure'=>'未审核','being_audited'=>'正在审核','pass_audited'=>'通过审核'])->default('未审核');
            $table->boolean('editable')->default(true);
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
