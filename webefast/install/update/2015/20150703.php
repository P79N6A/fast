<?php
$u = array();

 $u['FSF-1357'] = array(
      "INSERT INTO `sys_action` VALUES ('4020503', '4020500', 'act', '导出', 'oms/sell_record/exprot_short_list', '1', '1', '0', '1','0');",
    );
 $u['FSF-1386'] = array(
        "INSERT INTO `sys_action` VALUES ('21010300', '21010000', 'url', '店铺运营数据分析', 'rpt/shop_report/data_analyse', '1', '1', '0', '1','0');",
          " DROP TABLE IF EXISTS `shop_data_analysis`;",
         " CREATE TABLE `shop_data_analysis` ( `shop_report_id` int(11) unsigned NOT NULL AUTO_INCREMENT, `summary_date` date NOT NULL DEFAULT '0000-00-00' COMMENT '汇总日期', `sale_channel_code` varchar(20) NOT NULL DEFAULT '' COMMENT '平台代码', `shop_code` varchar(20) NOT NULL DEFAULT '' COMMENT '商店代码', `order_total_num` smallint(5) NOT NULL DEFAULT '0' COMMENT '订单总量', `goods_total_num` int(10) NOT NULL DEFAULT '0' COMMENT '商品总量', `sale_total_money` decimal(20,2) DEFAULT '0.00' COMMENT '总销售金额', `shipping_fee` decimal(10,2) DEFAULT '0.00' COMMENT '运费', `send_order_num` smallint(10) NOT NULL DEFAULT '0' COMMENT '已发货订单量', `send_goods_num` int(10) NOT NULL DEFAULT '0' COMMENT '已发货商品量', `send_sell_total_price` decimal(20,2) DEFAULT '0.00' COMMENT '已发货总吊牌价', `send_avg_total_money` decimal(20,2) DEFAULT '0.00' COMMENT '已发货总销售金额', `send_goods_percentage` float(4,2) DEFAULT '0.00' COMMENT '发货比例', `no_send_order_num` smallint(5) NOT NULL DEFAULT '0' COMMENT '未发货订单量', `no_send_goods_distinct_num` smallint(5) NOT NULL DEFAULT '0' COMMENT '未发货商品种类量', `no_send_goods_num` int(10) NOT NULL DEFAULT '0' COMMENT '未发货商品量', `no_send_sell_total_price` decimal(20,2) DEFAULT '0.00' COMMENT '未发货总吊牌价', `no_send_avg_total_money` decimal(20,2) DEFAULT '0.00' COMMENT '未发货总销售金额', `no_send_goods_percentage` float(4,2) DEFAULT '0.00' COMMENT '未发货比例', `remaining_un_send_order_num` smallint(5) NOT NULL DEFAULT '0' COMMENT '剩余未发货订单量', `short_order_num` smallint(5) NOT NULL DEFAULT '0' COMMENT '缺货订单量', `short_goods_num` int(10) NOT NULL DEFAULT '0' COMMENT '缺货商品量', `short_sell_total_price` decimal(20,2) DEFAULT '0.00' COMMENT '缺货总吊牌价', `short_avg_total_money` decimal(20,2) DEFAULT '0.00' COMMENT '缺货总销售金额', `remaning_short_order_num` smallint(5) NOT NULL DEFAULT '0' COMMENT '剩余缺货订单量', `cancel_order_num` smallint(5) NOT NULL DEFAULT '0' COMMENT '已取消订单量', `cancel_sell_total_price` decimal(20,2) DEFAULT '0.00' COMMENT '已取消总吊牌价', `cancel_avg_total_money` decimal(20,2) DEFAULT '0.00' COMMENT '已取消总销售金额', `cancel_goods_percentage` float(4,2) DEFAULT '0.00' COMMENT '取消比例', `return_order_num` smallint(5) NOT NULL DEFAULT '0' COMMENT '退货订单量', `return_goods_num` int(10) NOT NULL DEFAULT '0' COMMENT '退货商品量', `return_sell_total_price` decimal(20,2) DEFAULT '0.00' COMMENT '退货总吊牌价', `return_avg_total_money` decimal(20,2) DEFAULT '0.00' COMMENT '退货总销售金额', PRIMARY KEY (`shop_report_id`), UNIQUE KEY `summary_date_shop_code` (`summary_date`,`shop_code`), KEY `sale_channel_code` (`sale_channel_code`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='店铺运营数据分析';",
    );
  $u['FSF-1366'] = array(
            "insert IGNORE into `sys_params` (param_id,param_code,parent_code,param_name,type,form_desc,value,sort,remark) values('','tran_order_auto_confirm','oms_property','转单自动确认','radio','[\"关闭\",\"开启\"]','0','0.00','1-开启 0-关闭');  ",
            "insert IGNORE  into `sys_params` (param_id,param_code,parent_code,param_name,type,form_desc,value,sort,remark) values('','unproblem_order_auto_confirm','oms_property','返回正常单自动确认','radio','[\"关闭\",\"开启\"]','1','0.00','1-开启 0-关闭'); "
    );
   $u['FSF-1402'] = array(
        "delete from api_jingdong_logistics_companies where id = 19 or id =20;",
       "INSERT INTO `api_jingdong_logistics_companies` VALUES ('19', '0', '0', '2016', '全峰快递', '', '0', 'QFKD');;",
      " INSERT INTO `api_jingdong_logistics_companies` VALUES ('20', '0', '0', '2465', '国通快递', '', '0', 'GTO');",
    );

   $u['FSF-1160'] = array(
        "INSERT IGNORE INTO `sys_schedule` VALUES ('20', 'cli_batch_remove_short', '自动解除缺货', 'cli_batch_remove_short', '', '0', '0', '自动解除缺货', '{\"app_act\":\"oms\\/sell_record\\/cli_batch_remove_short\"}', 'webefast/web/index.php', '0', '0', '0', '3600', '0', 'sys', '', '0', '0');",
    );


   $u['FSF-1048'] = array(
   		"update sys_schedule set name='订单自动解挂' where code='auto_record_unpending' ",
   );
   $u['FSF-1385'] = array(
   		"INSERT INTO `sys_action` VALUES ('4030112', '4030100', 'act', '换货单商品信息-删除', 'oms/return_opt/change_goods_del', '1', '1', '0', '1','0');",
   		"INSERT INTO `sys_action` VALUES ('4030113', '4030100', 'act', '换货单商品信息-改款', 'oms/return_opt/change_goods_change', '1', '1', '0', '1','0');",	
   );
  
?>


