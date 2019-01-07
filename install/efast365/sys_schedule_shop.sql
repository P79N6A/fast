
DROP TABLE IF EXISTS `sys_schedule_shop`;
CREATE TABLE `sys_schedule_shop` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `sys_schedule_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '任务ID',
  `shop_id` int(11) NOT NULL DEFAULT '0' COMMENT '类型代码',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1停用状态',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sys_schedule_id` (`sys_schedule_id`,`shop_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


