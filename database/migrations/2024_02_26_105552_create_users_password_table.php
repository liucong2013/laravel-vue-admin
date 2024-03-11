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
        $sql = " CREATE TABLE `users_password` (
  `id` int(10) UNSIGNED NOT NULL,
  `uid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户id',
  `password` varchar(191) NOT NULL DEFAULT '' COMMENT '密码',
  `type` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户类型：1(app客户端) 2(管理后台)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='用户密码表，app和管理后台用户通用，但是密码要区分' ROW_FORMAT=DYNAMIC;

INSERT INTO `users_password` (`id`, `uid`, `password`, `type`) VALUES
(1, 1, '483440346677203805b4c59b3c77fa68de488d4887693c3f8793b25fb5e0eebe', 2);


ALTER TABLE `users_password`
  ADD PRIMARY KEY (`id`) USING BTREE;


ALTER TABLE `users_password`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2; ";

        DB::unprepared($sql);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_password');
    }
};
