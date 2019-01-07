<?php

$u['1325'] = array(
    "CREATE TABLE `api_weipinhuijit_goods` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `shop_code` varchar(100) NOT NULL COMMENT '店铺code',
  `barcode` varchar(128) DEFAULT '' COMMENT '商品条码',
  `product_name` varchar(128) DEFAULT '' COMMENT '商品名称',
  `sn` varchar(128) DEFAULT '' COMMENT '货号',
  `selling_status` tinyint(1) DEFAULT '0' COMMENT '商品销售状态，0：未售，1：在售',
  `cooperation_no` varchar(128) DEFAULT '' COMMENT '常态合作编码',
  `warehouse` varchar(128) DEFAULT '' COMMENT '唯品会仓库编码',
  `latest_update_time` datetime DEFAULT NULL COMMENT '该商品最近一次更新时间',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `_key` (`shop_code`,`barcode`,`cooperation_no`) USING BTREE,
  KEY `_index1` (`barcode`) USING BTREE,
  KEY `_index2` (`cooperation_no`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='唯品会商品信息表';",

);



$u['1318']=array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('12040000', '12000000', 'group', '集成接口', 'api-erp-record', '101', '1', '0', '1', '1');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('12040100', '12040000', 'url', '单据管理', 'erp/o2o_record/do_list', '1', '1', '0', '1', '1');"
);

$u['1320'] = array(
        "
    INSERT IGNORE INTO `sys_schedule` (
    `code`,
     `name`,
     task_type_code,
     sale_channel_code,
     `status`,
     type,
     `desc`,
     request,
     path,
     max_num,
     add_time,
     last_time,
     loop_time,
     task_type,
     task_module,
     exec_ip,
     plan_exec_time,
     plan_exec_data,
     update_time) VALUES('cli_sync_archive', '集成档案同步', 'cli_order_shipping_mid', '', 0, 2, '', '{\"app_act\":\"mid\\/mid\\/cli_sync_archive\"}', 'webefast/web/index.php', 0, 0, 0, 900, 0, 'sys', '', 0, NULL, 0);
    ",
        "
    INSERT IGNORE INTO `sys_schedule` (
    `code`,
     `name`,
     task_type_code,
     sale_channel_code,
     `status`,
     type,
     `desc`,
     request,
     path,
     max_num,
     add_time,
     last_time,
     loop_time,
     task_type,
     task_module,
     exec_ip,
     plan_exec_time,
     plan_exec_data,
     update_time) VALUES('cli_sync_inv', '集成仓库同步', 'cli_order_shipping_mid', '', 0, 2, '', '{\"app_act\":\"mid\\/mid\\/cli_sync_inv\"}', 'webefast/web/index.php', 0, 0, 0, 900, 0, 'sys', '', 0, NULL, 0);
    ",
    );

$u['1342'] = array("INSERT INTO `sys_schedule` (
                        `code`,
                        `name`,
                        `task_type_code`,
                        `status`,
                        `type`,
                        `desc`,
                        `request`,
                        `path`,
                        `loop_time`,
                        `task_module`
                    )
                    VALUES
                        (
                            'cli_upload_lock_inv',
                            '销售订单锁定库存上传',
                            'cli_upload_lock_inv',
                            '1',
                            '3',
                            '仅支持BSERP2，系统的销售订单锁定库存上传到ERP。此服务10分钟运行一次。',
                            '{\"app_act\":\"mid/mid/cli_upload_lock_inv\"}',
                            'webefast/web/index.php',
                            '600',
                            'sys'
                        );");

$u['1349']=array(
	"ALTER TABLE crm_activity ADD `is_first` tinyint(1) DEFAULT '0' COMMENT '启用权限';",
	"insert into sys_action values('3040102','3040100','act','删除','crm/activity/delete',1,1,0,1,0);",
	"insert into sys_action values('3040103','3040100','act','复制','crm/activity/copy_activity',2,1,0,1,0);",
);

$u['1326']=array(
    "ALTER TABLE api_weipinhuijit_order_detail ADD COLUMN `create_time` datetime DEFAULT NULL COMMENT '创建时间';",
);
$u['1353']=array(
    "UPDATE `sys_schedule` SET `loop_time`=60 WHERE `code`='opt_record_by_seller_remark';",
);

$u['1351'] = array(
	"insert into sys_action values('8070312','8070300','act','批量订单拦截','oms/order_opt/opt_intercept_list',1,1,0,1,0);",
	"insert into sys_action values('8070201','8070200','act','批量订单拦截','oms/order_opt/opt_intercept_do_list',1,1,0,1,0);",
);

$u['1306'] = array("INSERT INTO `base_express` (`company_code`, `express_code`, `express_name`) VALUES('SFC','SFC','三态速递');",
               "INSERT INTO `base_express_company` (`company_code`, `company_name`) VALUES ('SFC', '三态速递');",
               "DROP TABLE IF EXISTS sfc_rm_config",
               "CREATE TABLE `sfc_rm_config` (
                    `pid` int(11) NOT NULL AUTO_INCREMENT,
                    `sfckey` varchar(255) DEFAULT NULL COMMENT '用户数据标识',
                    `token` varchar(255) DEFAULT NULL COMMENT '密钥',
                    `sfcid` varchar(255) DEFAULT NULL COMMENT '三态的userCode',
                    `company_code_code` varchar(32) DEFAULT NULL COMMENT '快递公司',
                    `express_code` varchar(32) DEFAULT NULL COMMENT '配送方式',
                    `express_id` int(11) DEFAULT NULL COMMENT '配送方式id',
                    PRIMARY KEY (`pid`),
                    UNIQUE KEY `ATE` (`sfckey`,`token`,`express_code`) USING BTREE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$u['1362'] = array(
	"ALTER TABLE report_base_goods_collect ADD sku varchar(128) DEFAULT '0' COMMENT '商品sku';",
	"drop procedure pro_report_base_goods_collect;",
	"CREATE PROCEDURE `pro_report_base_goods_collect`(
		kh_id INT ,
		biz_date varchar(30)
) 
mypro:BEGIN
			/* 每日经营商品数据

				主要记录了每天的订单成交、退款申请、发货、收货以及未发货等情况

			*/

			declare start_date varchar(30);
			declare end_date varchar(30);

			SET start_date = CONCAT(biz_date,' 00:00:00');
			SET end_date = CONCAT(biz_date,' 23:59:59');

		/*销售数据插入*/
INSERT IGNORE INTO report_base_goods_collect
  (kh_id,biz_date,sale_channel_code,shop_code,goods_code,spec1_code,spec2_code,goods_barcode,sku,sale_count,sale_money)
  SELECT 
  kh_id,biz_date,sale_channel_code,shop_code,goods_code,spec1_code,spec2_code,barcode,sku,SUM(num) AS sale_count,SUM(avg_money) AS sale_money 
  FROM
  (
    SELECT 
    tmp.sale_channel_code,tmp.shop_code,oms_sell_record_detail.num,oms_sell_record_detail.goods_code,oms_sell_record_detail.barcode,
    oms_sell_record_detail.avg_money,oms_sell_record_detail.spec1_code,oms_sell_record_detail.spec2_code,oms_sell_record_detail.sku
    FROM 
    oms_sell_record_detail ,
    (
      SELECT 
        shop_code,sell_record_code,sale_channel_code
      FROM 
        oms_sell_record
      WHERE 
    oms_sell_record.`shipping_status` = 4 and oms_sell_record.`delivery_time` >= start_date  
and oms_sell_record.`delivery_time` <= end_date AND oms_sell_record.order_status<>3 
     )
    as tmp
    WHERE tmp.sell_record_code = oms_sell_record_detail.sell_record_code 
  )AS tmp2 
  GROUP BY 
  tmp2.shop_code,tmp2.sku
  ON DUPLICATE KEY UPDATE sale_count = values(sale_count),sale_money = values(sale_money);
		
		UPDATE report_base_goods_collect,base_goods,base_brand,base_category
		SET 
		report_base_goods_collect.goods_name = base_goods.goods_name ,
		report_base_goods_collect.cat_name = base_category.category_name ,
		report_base_goods_collect.brand_name = base_brand.brand_name
		WHERE report_base_goods_collect.goods_code = base_goods.goods_code AND
		base_goods.category_code = base_category.category_code AND
		base_goods.brand_code = base_brand.brand_code AND report_base_goods_collect.kh_id = kh_id AND report_base_goods_collect.biz_date = biz_date;


		END",
	"update report_base_goods_collect r1 inner join goods_sku r2 on r1.goods_barcode=r2.barcode set r1.sku=r2.sku;",
);

$u['bug_1365'] = array("INSERT INTO `base_express` (`company_code`,`express_code`,`express_name`,`status`,`sys`) VALUES('SFC','WWRAM','邮政小包（挂号）','1','1');",
                       "INSERT INTO `base_express` (`company_code`,`express_code`,`express_name`,`status`,`sys`) VALUES('SFC','WWAM','邮政小包（平邮）','1','1');");
        