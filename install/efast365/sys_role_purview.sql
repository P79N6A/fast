
DROP TABLE IF EXISTS `sys_role_purview`;
CREATE TABLE `sys_role_purview` (
  `role_purview_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(11) unsigned NOT NULL COMMENT '角色id',
  `purview_id` int(11) unsigned NOT NULL COMMENT '权限id',
  `purview_type` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '权限类型 1：菜单权限 2：操作权限 3：价格权限 4：仓库权限 5：店铺权限 6：供应商权限 7：客户权限',
  PRIMARY KEY (`role_purview_id`),
  KEY `role_purview_index` (`role_id`,`purview_id`,`purview_type`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=6642 DEFAULT CHARSET=utf8 COMMENT='角色权限表';
