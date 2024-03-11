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
        Schema::create('sys_authorities', function (Blueprint $table) {
            $table->timestamps();
            $table->softDeletes();
            $table->string('authority_id', 90)->primary()->comment('角色ID');
            $table->string('authority_name', 191)->nullable()->comment('角色名');
            $table->string('parent_id', 191)->nullable()->comment('父角色ID');
            $table->string('menu_ids', 191)->nullable()->comment('菜单IDS');
        });

        // 设置表备注
        DB::statement("ALTER TABLE sys_authorities COMMENT '系统角色表'");


        //导入原始数据
        $currentDirectory = base_path();
        $sql = file_get_contents($currentDirectory . '/database/import/sys_authorities.sql');
        DB::unprepared($sql);


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_authorities');
    }
};
