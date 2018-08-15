<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoodsCommentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goods_comment', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->comment('评论者id');
            $table->integer('goods_id')->comment('商品id');
            $table->string('tags')->comment('评论标签');
            $table->integer('stars')->default(0)->comment('');
            $table->mediumText('comment')->comment('买家评论');
            $table->boolean('is_first')->default(true)->comment('用户第一次评论？');
            $table->boolean('is_reply')->default(false)->comment('是否是回复的评论');
            $table->integer('reply_comment_id')->nullable()->comment('被回复的评论');
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
        Schema::dropIfExists('goods_comment');
    }
}
