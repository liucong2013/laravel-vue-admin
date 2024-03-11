<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {


        Schema::create('code', function (Blueprint $table) {
            $table->increments('id')->comment('主键，自增');
            $table->string('code', 20)->comment('编号');
            $table->mediumInteger('batch')->unsigned()->comment('批次');
            $table->timestamps();

            // 添加索引
            $table->index('code');
            $table->index('batch');
        });

        // 添加表备注
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `code` COMMENT '二维码表'");

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('code');
    }
};
