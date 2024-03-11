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

        //这张表原本就有,直接用sql创建了
        $sql = "CREATE TABLE `sys_access_log_error` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `ip` varchar(191) DEFAULT NULL COMMENT '请求ip',
  `method` varchar(191) DEFAULT NULL COMMENT '请求方法',
  `path` varchar(191) DEFAULT NULL COMMENT '请求路径',
  `status` bigint(20) DEFAULT NULL COMMENT '请求状态',
  `latency` float(20,3) DEFAULT NULL COMMENT '延迟（用时）',
  `agent` text COMMENT '代理',
  `error_message` longtext COMMENT '错误信息',
  `body` text COMMENT '请求Body',
  `resp` longtext COMMENT '响应Body',
  `user_id` varchar(20) DEFAULT NULL COMMENT '用户id',
  `user_name` varchar(40) DEFAULT NULL COMMENT '用户姓名'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4  COMMENT='api请求错误日志表' ROW_FORMAT=DYNAMIC;

ALTER TABLE `sys_access_log_error`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `idx_sys_operation_records_deleted_at` (`deleted_at`) USING BTREE,
  ADD KEY `created_at` (`created_at`),
  ADD KEY `path` (`path`),
  ADD KEY `method` (`method`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `sys_access_log_error`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

";



        DB::connection('accessLog')->unprepared($sql);



    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_access_log_error');
    }
};
