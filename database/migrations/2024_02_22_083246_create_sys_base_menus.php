<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::create('sys_base_menus', function (Blueprint $table) {
            $table->id(); // 主键，自增ID
            $table->timestamps(); // 创建时间和更新时间
            $table->softDeletes(); // 软删除字段

            $table->bigInteger('menu_level')->unsigned()->nullable(); // 菜单等级
            $table->string('parent_id', 191)->nullable()->comment('父菜单ID'); // 父菜单ID
            $table->string('path', 191)->nullable()->comment('路由path'); // 路由path
            $table->string('name', 191)->nullable()->comment('路由name'); // 路由name
            $table->string('meta', 255)->nullable()->comment('meta'); // meta
            $table->string('icon', 191)->nullable()->comment('图标信息'); // 图标信息
            $table->tinyInteger('hidden')->nullable()->comment('是否在列表隐藏'); // 是否在列表隐藏
            $table->string('component', 191)->nullable()->comment('对应前端文件路径'); // 对应前端文件路径
            $table->bigInteger('sort')->nullable()->comment('排序标记'); // 排序标记
        });

        // 设置表备注
        DB::statement("ALTER TABLE sys_base_menus COMMENT '系统菜单'");

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_base_menus');
    }
};
