

--
-- 转存表中的数据 `sys_base_menus`
--
INSERT INTO `sys_base_menus` (`id`, `created_at`, `updated_at`, `deleted_at`, `menu_level`, `parent_id`, `path`, `name`,
                              `meta`, `icon`, `hidden`, `component`, `sort`)
VALUES (1, '2020-10-09 21:46:11', '2020-10-09 21:46:11', NULL, 0, '0', 'dashboard', 'dashboard',
        '{\"title\":\"仪表盘\",\"icon\":\"setting\",\"defaultMenu\":false,\"keepAlive\":false}', 'setting', 0,
        'view/dashboard/index.vue', 1),
       (3, '2020-10-09 21:46:11', '2020-10-09 21:46:11', NULL, 0, '0', 'admin', 'superAdmin',
        '{\"title\":\"超级管理员\",\"icon\":\"user-solid\",\"defaultMenu\":false,\"keepAlive\":false}', 'user-solid', 0,
        'view/superAdmin/index.vue', 3),
       (4, '2020-10-09 21:46:11', '2020-10-09 21:46:11', NULL, 0, '3', 'authority', 'authority',
        '{\"title\":\"角色管理\",\"icon\":\"s-custom\",\"defaultMenu\":false,\"keepAlive\":false}', 's-custom', 0,
        'view/superAdmin/authority/authority.vue', 1),
       (5, '2020-10-09 21:46:11', '2020-10-09 21:46:11', NULL, 0, '3', 'menu', 'menu',
        '{\"title\":\"菜单管理\",\"icon\":\"s-order\",\"defaultMenu\":false,\"keepAlive\":false}', 's-order', 0,
        'view/superAdmin/menu/menu.vue', 2),
       (7, '2020-10-09 21:46:11', '2020-11-08 13:02:51', NULL, 0, '3', 'user', 'user',
        '{\"title\":\"用户管理\",\"icon\":\"coordinate\",\"defaultMenu\":false,\"keepAlive\":false}', 'coordinate', 0,
        'view/superAdmin/user/user.vue', 1),
       (24, '2020-10-09 21:46:11', '2020-10-09 21:46:11', NULL, 0, '3', 'operation', 'operation',
        '{\"title\":\"操作历史\",\"icon\":\"time\",\"defaultMenu\":false,\"keepAlive\":false}', 'time', 0,
        'view/superAdmin/operation/sysOperationRecord.vue', 6),
       (29, '2020-12-01 12:21:37', '2020-12-05 08:55:36', NULL, NULL, '28', 'area', 'area',
        '{\"title\":\"\\u5168\\u56fd\\u5730\\u5740\",\"icon\":\"s-flag\",\"defaultMenu\":false,\"keepAlive\":null}',
        NULL, NULL, 'view/base/baseArea/index.vue', 0),
       (30, '2023-01-25 12:35:49', '2023-01-25 12:35:49', NULL, NULL, '0', 'encoded', 'encoded',
        '{\"title\":\"\\u4e8c\\u7ef4\\u7801\",\"icon\":\"open\",\"defaultMenu\":false,\"keepAlive\":false}', NULL, NULL,
        'view/encoded/index.vue', 100),
       (31, '2023-01-25 12:37:13', '2023-01-25 12:37:47', NULL, NULL, '30', 'encodedCode', 'encodedCode',
        '{\"title\":\"\\u4e8c\\u7ef4\\u7801\\u5217\\u8868\",\"icon\":\"more-outline\",\"defaultMenu\":false,\"keepAlive\":false}',
        NULL, NULL, 'view/encoded/code/index.vue', 1);
