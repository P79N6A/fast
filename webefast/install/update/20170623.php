<?php

$u['1402'] = array(
    "UPDATE sys_role_manage_price SET `desc`='在采购管理以及商品进销存分析/商品列表/移仓单进行控制，开启后，此角色对应用户可看到商品的进货价，其他用户显示****' WHERE manage_code='purchase_price'",
);

$u['1416'] = array(
    "INSERT INTO `base_sale_channel` (`sale_channel_code`, `short_code`, `sale_channel_name`, `is_system`, `is_active`, `remark`, `lastchanged`) VALUES ('yougou', 'yg', '优购', '1', '1', '', '2015-01-09 14:09:24');",
);

$u['1439']=array(
"CREATE TABLE `api_weipinhuijit_shop_warehouse` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `shop_code` varchar(128) NOT NULL DEFAULT '' COMMENT '店铺代码',
  `warehouse_code` varchar(128) NOT NULL DEFAULT '' COMMENT '仓库代码',
  `sync_val` int(11) DEFAULT '0' COMMENT '同步比例',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idxu_key` (`warehouse_code`,`shop_code`),
  KEY `index1` (`shop_code`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8 COMMENT='店铺与唯品会JIT仓库及同步比例关系表';",
    "ALTER TABLE api_weipinhuijit_goods DROP INDEX _key",
    "ALTER TABLE `api_weipinhuijit_goods` ADD UNIQUE KEY _key (`shop_code`,`barcode`,`cooperation_no`,`warehouse`) USING BTREE",
    "ALTER TABLE api_weipinhuijit_goods ADD COLUMN `last_sync_inv_num` int(8) DEFAULT '-1' COMMENT '最后一次更新的库存'",
    "ALTER TABLE api_weipinhuijit_goods ADD COLUMN `num` int(8) DEFAULT '0' COMMENT '平台可售库存'",
    "alter table api_weipinhuijit_goods add COLUMN `inv_up_time` datetime DEFAULT NULL COMMENT '向第三方平台库存上传时间'",
    "INSERT INTO `sys_schedule` (`code`, `name`, `task_type_code`, `sale_channel_code`, `status`, `type`, `desc`, `request`, `path`, `max_num`, `add_time`, `last_time`, `loop_time`, `task_type`, `task_module`, `exec_ip`, `plan_exec_time`, `plan_exec_data`, `update_time`) VALUES ('weipinhui_goods_download_cmd', '唯品会商品下载', '', 'weipinhui', '0', '1', '启用后，系统将自动从唯品会平台，拉取各店铺的商品信息', '{\"action\":\"api / goods / weipinhui_inv_upload_cmd\"}', '', '0', '0', '0', '36000', '0', 'api', '', '1475892963', NULL, '0');",
);