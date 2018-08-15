<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCertifyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('certify', function (Blueprint $table) {
            /*
             * 实名认证
             * */
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('zhima')->nullable()->comment('芝麻分');
            $table->string('name')->comment('姓名');
            $table->string('certificate_type')->comment('认证类型');
            $table->string('certificate_num')->comment('证件号');
            $table->string('positive_pic')->comment('证件正面照');
            $table->string('negative_pic')->comment('反面照');
            $table->boolean('certificated')->default(false)->comment('认证状态');
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
        Schema::dropIfExists('certify');
    }
}
