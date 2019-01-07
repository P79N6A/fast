<?php
$u['2017']=array(
    "update sys_params set param_name='快递单打印，商品信息排序方式',type='select',form_desc='[\"按库位+商品编码+条码升序\",\"按吊牌价金额升序\",\"按吊牌价金额倒序\"]',memo='快递单中商品信息将按相应方式排序',value=(select IF(a.ret=1,2,0) from (select value as ret from sys_params where param_code='order_by_goods_sprice') a) where param_code='order_by_goods_sprice'"
);
$u['2018']=array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('9030301', '9030300', 'act', '批量人工核销', 'acc/api_taobao_alipay/do_check_account_muilt', '1', '1', '0', '1', '0');"
);
$u['bug_2050']=array(
    "ALTER TABLE `oms_sell_record` MODIFY COLUMN `fx_payable_money` decimal(10,3) NOT NULL DEFAULT '0.00' COMMENT '分销结算金额';"
);

$u['2003'] = array(
    "DELETE from sys_params WHERE param_code='aligenius_deliver_refunds_check'",
    "UPDATE sys_params set memo=\'开启后，系统推送“未发货仅退款”订单的拦截取消结果给AG，AG在“待我处理的退款” 页面透传订单取消状态，实现人工筛选后的批量退款处理\' WHERE param_code='aligenius_sendgoods_cancel';",
    "UPDATE sys_params set memo='开启后，系统推送“买家已退货待卖家确认收货”订单的退货入仓状态给AG，AG在“待我处理的退款”页面透传退货入仓状态，实现人工筛选后的批量退款处理' WHERE param_code='aligenius_warehouse_update';",
    "UPDATE sys_params set memo='开启后，在透传发货订单取消/退货入库状态后，系统再次推送审核结果，通知AG提交审核，此时待处理的任务将进入AG的“待审核列表”，商家若在AG“自动化退款策略”中配置“同意审核”指令，可实现该场景的批量自动退款',param_name='已发货退货入仓结果审核' WHERE param_code='aligenius_refunds_check';",
    "INSERT INTO sys_schedule (code, name, task_type_code, sale_channel_code, status, type, `desc`, request, path, max_num, add_time, last_time, loop_time, task_type, task_module, exec_ip, plan_exec_time, plan_exec_data, update_time) VALUES ('cli_ag_record', '退单处理状态更新', 'ag', '', 0, 13, '开启后，此服务自动刷新淘系退单处理状态，根据关联交易号/售后服务单的处理状态更新对应的退单是否已取消成功/已入库', '{\"app_act\":\"oms/taobao_ag/aligenius_cli\"}', 'webefast/web/index.php', 0, 0, 0, 120, 0, 'sys', '', 0, null, 0);",
    
    "CREATE TABLE `api_taobao_ag_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_code` varchar(64) DEFAULT '' COMMENT '用户代码',
  `action_name` varchar(50) NOT NULL DEFAULT '' COMMENT '操作名称',
  `action_time` datetime DEFAULT NULL COMMENT '操作时间',
  `record_status` varchar(128) DEFAULT '' COMMENT '单据状态',
  `action_note` mediumtext NOT NULL COMMENT '操作描述',
  `refund_id` varchar(30) DEFAULT NULL COMMENT '退款单号',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='淘宝ag日志'",

    "CREATE TABLE `api_taobao_ag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `refund_id` varchar(30) DEFAULT NULL COMMENT '退款单号',
  `tid` varchar(30) DEFAULT NULL COMMENT '交易号',
  `oid` varchar(30) DEFAULT NULL COMMENT '子订单号。如果是单笔交易oid会等于tid',
  `refund_record_code` varchar(30) NOT NULL DEFAULT '' COMMENT '业务系统单据编号(售后服务单号)',
  `source` varchar(10) DEFAULT NULL COMMENT '平台来源标识符',
  `shop_code` varchar(30) DEFAULT NULL COMMENT '业务系统店铺代码',
  `status` tinyint(1) DEFAULT NULL COMMENT '0：不可转单 1：可转单',
  `seller_nick` varchar(30) DEFAULT NULL COMMENT '平台卖家昵称',
  `buyer_nick` varchar(255) DEFAULT NULL COMMENT '平台买家昵称',
  `refund_fee` float(7,2) DEFAULT NULL COMMENT '退还金额(退还给买家的金额)',
  `payment` float(7,2) DEFAULT NULL COMMENT '支付给卖家的金额(交易总金额-退还给买家的金额)。精确到2位小数',
  `order_last_update_time` datetime DEFAULT NULL COMMENT '平台订单最后一次更新订单时间,淘宝平台：modified',
  `order_first_insert_time` datetime DEFAULT NULL COMMENT '平台订单第一次插入订单时间,淘宝平台：created',
  `last_update_time` datetime DEFAULT NULL COMMENT '最后一次更新订单时间,数据在本平台的更新时间',
  `first_insert_time` datetime DEFAULT NULL COMMENT '第一次插入订单时间,数据在本平台的插入时间',
  `ag_status` int(10) NOT NULL DEFAULT '1' COMMENT '当前单据状态  1-初始状态 2-处理中 3-已处理，待推送  4-已推送，待审核 5-完成 6-强制完成',
  `ag_record_type` int(10) NOT NULL DEFAULT '0' COMMENT '关联单据类型 1-售后服务单 2-订单',
  `cancel_status` int(10) NOT NULL DEFAULT '0' COMMENT '关联订单是否已全部作废 1-部分作废 2-全部作废',
  `sell_record_code` varchar(120) DEFAULT NULL COMMENT '系统订单号',
  `process_status` varchar(120) DEFAULT NULL COMMENT '处理状态',
  `push_val` varchar(120) DEFAULT NULL COMMENT '推送值',
  `push_status` varchar(120) DEFAULT NULL COMMENT '推送状态',
  `push_log` varchar(120) DEFAULT NULL COMMENT '最新推送日志',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `refund_id` (`refund_id`) USING BTREE,
  KEY `tid` (`tid`) USING BTREE,
  KEY `oid` (`oid`) USING BTREE,
  KEY `refund_record_code` (`refund_record_code`) USING BTREE,
  KEY `source` (`source`) USING BTREE,
  KEY `shop_code` (`shop_code`) USING BTREE,
  KEY `buyer_nick` (`buyer_nick`) USING BTREE,
  KEY `seller_nick` (`seller_nick`) USING BTREE,
  KEY `order_first_insert_time` (`order_first_insert_time`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='淘宝ag中间表'",

    "insert into `sys_action` ( `action_code`, `action_name`, `other_priv_type`, `status`, `ui_entrance`, `parent_id`, `sort_order`, `type`, `appid`, `action_id`) values ( 'taobao-characteristics', '淘宝特性', '0', '1', '0', '4000000', '7', 'group', '1', '4070000');",
    "insert into `sys_action` ( `action_code`, `action_name`, `other_priv_type`, `status`, `ui_entrance`, `parent_id`, `sort_order`, `type`, `appid`, `action_id`) values ( 'oms/taobao_ag/do_list', 'AG', '0', '1', '0', '4070000', '1', 'url', '1', '4070100');"
);


$u['bug_2140']=array(
    "UPDATE oms_return_package set shop_code='' WHERE shop_code='请选择';",
);
$u['task_2010'] = array(
   
    " update order_combine_strategy set rule_desc='问题单（不包含买家申请退款）参与合并（合并后为问题单）' where rule_code='order_combine_is_problem'",
    
   "INSERT INTO `order_combine_strategy` (`rule_code`, `rule_status_value`, `rule_desc`, `rule_scene_value`, `remark`) VALUES ('order_combine_is_problem_reimburse', '0', '买家申请退款(部分退)的问题单参与合并（合并后为问题单）', '0', '');",
);