<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goods', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('shop_id')->comment('店铺id');
            $table->string('name')->comment('商品名称');
            $table->string('main_pic')->default('default_goods_main_pic.png')->comment('商品图');
            $table->json('media')->nullable()->comment('商品图片集');
            $table->mediumText('description')->nullable()->comment('描述');
            $table->longText('content')->nullable()->comment('商品详情');
            $table->enum('status',['售尽','下架','正常'])->default('正常')->comment();
            $table->string('type')->comment('商品分类');
            $table->string('tag')->nullable()->comment('商品标签');
            $table->double('price',15, 2)->comment('商品价格');
            $table->integer('stock')->default(0)->comment('商品库存数量');
            $table->integer('sold')->default(0)->comment('已售出');
            $table->double('discount_price',15, 2)->nullable()->comment('折扣价');
            $table->index(['shop_id','type','tag']);
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
        Schema::dropIfExists('goods');
    }
}
