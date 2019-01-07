<?php

$u = array();

$u['466'] = array(
    //门店商品库存查询菜单
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('30010300', '30010000', 'url', '商品库存查询', 'prm/inv/entity_do_list', '11', '1', '0', '1', '0');",
);

$u['698'] = array(
    //唯品会JIT菜单维护
    "UPDATE sys_action SET action_name='多PO拣货单' WHERE action_id='8040600';",
    "UPDATE sys_action SET action_name='单PO拣货单' WHERE action_id='8040200';",
    //表唯一键修改
    "DROP INDEX pick_no_barcode ON api_weipinhuijit_pick_goods;",
    "ALTER TABLE api_weipinhuijit_pick_goods ADD UNIQUE KEY `pick_no_barcode` (`po_no`,`pick_no`,`barcode`) USING BTREE;",
    "DROP INDEX _key ON api_weipinhuijit_delivery_detail;",
    "ALTER TABLE api_weipinhuijit_delivery_detail ADD UNIQUE KEY `pick_po_sku` (`sku`,`record_code`,`pick_no`,`po_no`,`delivery_id`) USING BTREE;",
    "ALTER TABLE api_weipinhuijit_delivery MODIFY COLUMN `delivery_id` varchar(50) DEFAULT NULL COMMENT '出库单ID,多PO时值为storage_no';",
    "ALTER TABLE api_weipinhuijit_pick MODIFY COLUMN `delivery_id` int(11) DEFAULT NULL COMMENT '出库单ID,多PO时值为storage_no';",
    "ALTER TABLE api_weipinhuijit_store_out_record MODIFY COLUMN `delivery_id` varchar(50) DEFAULT NULL COMMENT '出库单ID,多PO时值为storage_no';"
);

$u['bug_557'] = array(
    "ALTER TABLE goods_barcode_child DROP INDEX goods_code_spec;",
    "ALTER TABLE goods_barcode_child ADD UNIQUE KEY(barcode);",
    "ALTER TABLE goods_barcode_child ADD INDEX (`sku`);",
);

$u['721'] = array(
	"insert into sys_action values('3020702','3020700','act','删除','op/op_gift_strategy/do_delete',2,1,0,1,0);",
);

$u['670'] = array("ALTER TABLE api_goods_sku ADD `invalid_time` datetime DEFAULT NULL COMMENT '标记为删除状态的时间';");

$u['739'] = array(
   "INSERT INTO `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`) VALUES ('', 'order_by_goods_sprice', 'waves_property', '快递单打印，商品信息按照吊牌价大小排序', 'radio', '[\"关闭\",\"开启\"]', '0', '0.00', '1-开启 0-关闭', '2016-07-29 09:39:41', '开启后，快递单中商品信息按照吊牌价从大到小排序');"
    );

$u['680'] = array(
    "UPDATE `sys_schedule` SET `status`='0' WHERE (`code`='log_clean_up');"
);
