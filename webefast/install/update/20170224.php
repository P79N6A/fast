<?php

$u = array();

$u['1051'] = array(
    "INSERT INTO `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`) VALUES ('', 'sync_seller_remark_node', 'oms_common', '同步商家备注到系统，订单拦截最晚状态', 'select', '[\"未确认\",\"已确认\",\"已拣货\"]', '0', '0.00', '0-未确认 1-已确认 2-已拣货', now(), '默认为未确认状态，即当前状态订单会设问，其他状态不处理，仅更新商家备注信息');",
    "UPDATE sys_schedule SET `name`='更新商家备注并拦截设问',`status`=1,`type`=1,`desc`='开启后，系统自动将已确认未发货且更新了商家备注的订单进行拦截设问，默认10分钟执行一次',`loop_time`=600 WHERE `code`='opt_record_by_seller_remark';",
    //api_order增加lastchanged索引
    "ALTER TABLE api_order ADD INDEX `ind_lastchanged` (`lastchanged`) USING BTREE;"
);
$u['1049'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('4010601', '4010600', 'url', '库存同步', 'api/sys/goods/fenxiao_sync_goods_inv', '1', '1', '0', '1', '0');",
);
$u['1047'] = array(
    "ALTER TABLE op_express_by_goods ADD COLUMN `goods_priority` INT NOT NULL DEFAULT '1' COMMENT '配送方式优先级';",
    "ALTER TABLE op_express_by_goods ADD UNIQUE KEY `idxu_sku`(sku);",
    "ALTER TABLE op_express_by_goods ADD COLUMN `is_diy` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否组装';",
    "CREATE TABLE op_express_priority 
(
	id int(11) unsigned NOT NULL AUTO_INCREMENT,
	express_code varchar(128) NOT NULL DEFAULT '' COMMENT '配送方式代码',
	priority int(11) NOT NULL DEFAULT '1' COMMENT '配送方式优先级',
  lastchanged timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
	UNIQUE KEY `_key` (`express_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='配送方式优先级表';",
);

$u['1033'] = array(
    "UPDATE `sys_schedule` SET `type`=0,`loop_time`=1800,`desc`='开启后，系统自动将已确认未发货且更新了商家备注的订单进行拦截设问，默认30分钟执行一次' WHERE `code`='opt_record_by_seller_remark'",
);

$u['1044'] = array(
    "ALTER TABLE wms_b2b_order_detail ADD COLUMN `lof_no` varchar(128) NOT NULL DEFAULT '' COMMENT '批次号' AFTER barcode;",
    "ALTER TABLE wms_b2b_order_detail ADD COLUMN `production_date` date NOT NULL DEFAULT '0000-00-00' COMMENT '生产日期' AFTER lof_no;",
    "ALTER TABLE wms_b2b_order_detail DROP INDEX idx_record_code_type;",
    "ALTER TABLE wms_b2b_order_detail ADD UNIQUE KEY `idx_record_code_type` (`record_code`,`record_type`,`barcode`,`lof_no`,`item_type`,`new_record_code`) USING BTREE;"
);

$u['1048'] = array(
	"insert into sys_action values('7010114','7010001','url','发货快递单回收','oms/sell_record/express_recycling',80,1,0,1,0);",
);

$u['1084'] = array(
	"delete from sys_user_pref where iid='prm/goods_inv_do_list/table';",
	"delete from sys_user_pref where iid='prm/goods_inv_do_list_goods/table';",
);