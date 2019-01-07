<?php
$u['bug_1732'] = array(
    "ALTER TABLE `oms_waves_record` ADD COLUMN `cancelled_sell_record_count` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '取消订单数量';"
);

$u['1683'] = array(
    //订单表增加企业税号
     "ALTER TABLE `oms_sell_record` ADD COLUMN `taxpayers_code` varchar(100) DEFAULT '' COMMENT '企业税号' after `invoice_number`;"
);

$u['1708']=array(
"CREATE TABLE `base_shop_ag` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `shop_code` varchar(128) DEFAULT '' COMMENT '店铺代码',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idxu_key` (`shop_code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='淘宝AG档案表';",
    'INSERT INTO `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`) VALUES (\'\', \'aligenius_enable\', \'ag\', \'是否启用AG数据推送\', \'radio\', \'[\"关闭\",\"开启\"]\', \'0\', \'1.00\', \'1-开启 0-关闭\', \'2017-10-18 17:41:34\', \'开启后,可设置启用AG推送的店铺及不同业务场景,关闭该参数,不推送数据至AG\');',
    'INSERT INTO `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`) VALUES (\'\', \'aligenius_sendgoods_cancel\', \'ag\', \'AG001 未发货订单取消结果上传\', \'radio\', \'[\"关闭\",\"开启\"]\', \'0\', \'1.00\', \'1-开启 0-关闭\', \'2017-10-18 17:27:42\', \'开启后,系统推送未发货仅退款订单的拦截取消结果给AG,AG在待我处理的退款页面透传订单取消状态,实现人工筛选后的批量退款处理\');',
    'INSERT INTO `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`) VALUES (\'\', \'aligenius_refunds_check\', \'ag\', \'AG002 未发货订单取消结果审核\', \'radio\', \'[\"关闭\",\"开启\"]\', \'0\', \'1.00\', \'1-开启 0-关闭\', \'2017-10-18 17:27:42\', \'开启参数条件:开启AG001参数,开启后,在推送发货订单取消状态后,系统再次推送审核结果,通知AG提交审核,此时待处理的任务将进入AG的待审核列表,商家若在AG自动退款策略中配置同意审核指令,可实现该场景的批量自动退款\');',
    'INSERT INTO `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`) VALUES (\'\', \'aligenius_warehouse_update\', \'ag\', \'AG003 已发货退货入仓结果上传\', \'radio\', \'[\"关闭\",\"开启\"]\', \'0\', \'1.00\', \'1-开启 0-关闭\', \'2017-10-18 17:27:42\', \'开启后,系统推送买家已退货待卖家确认收货订单的退货仓库状态给AG,AG在待我处理的退款页面透传退货入仓状态,实现人工筛选后的批量退款处理\');',
    'INSERT INTO `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`) VALUES (\'\', \'aligenius_deliver_refunds_check\', \'ag\', \'AG004 已发货退货入仓结果审核\', \'radio\', \'[\"关闭\",\"开启\"]\', \'0\', \'1.00\', \'1-开启 0-关闭\', \'2017-10-18 17:41:34\', \'开启参数条件:开启AG003参数开启后,在透传退货入库状态后,系统再次推送审核结果,通知AG提交审核,此时待处理的任务将进入AG的待审核列表,商家若在AG自动化退款策略中配置同意审核指令,可实现该场景的批量自动退款\');',
    "ALTER TABLE oms_sell_return ADD COLUMN `ag_status` tinyint(1) DEFAULT '0' COMMENT '推送AG状态,0未推送,1:成功';",

);
