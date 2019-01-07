<?php
$u['FSF-1845'] = array(
		"ALTER TABLE api_taobao_goods ADD COLUMN relation_msg VARCHAR(255) NOT NULL COMMENT '关联备注'",
		"ALTER TABLE api_taobao_sku ADD COLUMN relation_msg VARCHAR(255) NOT NULL COMMENT '关联备注'",
		);

$u['FSF-1843'] = array(
		"ALTER TABLE `api_taobao_fx_product_sku` ADD COLUMN `inv_num` int(8) DEFAULT '-1' COMMENT '业务系统库存数量'",
		"ALTER TABLE api_taobao_fx_product_sku ADD COLUMN `inv_update_time` datetime DEFAULT NULL COMMENT '业务系统向本表更新库存时间'",
		"ALTER TABLE api_taobao_fx_product_sku ADD COLUMN `sys_update_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '业务库存变化时间'",
);

$u['FSF-1831'] = array(
		"ALTER TABLE `oms_sell_record` ADD KEY `must_occupy_inv` (`must_occupy_inv`);",
		"ALTER TABLE `oms_sell_record` ADD KEY `lock_inv_status` (`lock_inv_status`);",
);
$u['FSF-1831'] = array(
		"ALTER TABLE `oms_sell_record` ADD KEY `must_occupy_inv` (`must_occupy_inv`);",
		"ALTER TABLE `oms_sell_record` ADD KEY `lock_inv_status` (`lock_inv_status`);",
);

$u['FSF-1824'] = array(
		"ALTER TABLE `stm_take_stock_record`
MODIFY COLUMN `record_code`  varchar(256) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '单据编号' AFTER `take_stock_record_id`,
MODIFY COLUMN `relation_code`  varchar(256) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '关联单号' AFTER `record_code`;
",
"ALTER TABLE `stm_profit_loss_lof`
MODIFY COLUMN `order_code`  varchar(256) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '单据编号' AFTER `id`,
MODIFY COLUMN `record_code_list`  varchar(256) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `order_code`;
",
);

$u['FSF-1789'] = array(
	"delete from sys_user_pref where iid = 'oms/sell_record_td_list'"
);



