<?php

$u['1211'] = array(
    "INSERT INTO `order_combine_strategy` ( `rule_code`, `rule_status_value`, `rule_desc`, `rule_scene_value`, `remark`) VALUES ( 'order_combine_is_presell', '0', '预售订单参与合并（合并后为预售单）', '0', '');",
);

$u['1367'] = array("ALTER TABLE api_yamaxun_order MODIFY InvoiceTitle VARCHAR (100) DEFAULT '' COMMENT '买家指定的发票抬头';");


$u['1363'] = array(
    "CREATE TABLE `crm_goods_children_temp` (
	`id` INT (11) NOT NULL AUTO_INCREMENT,
	`activity_code` VARCHAR (50) NOT NULL DEFAULT '' COMMENT '活动代码',
	`sku` VARCHAR (128) NOT NULL DEFAULT '' COMMENT 'sku',
	`num` INT (10) NOT NULL DEFAULT '0' COMMENT '套餐锁定库存数',
	PRIMARY KEY (`id`),
	UNIQUE KEY `_key` (`activity_code`, `sku`)
    ) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '活动子商品上报库存处理临时表';"
);

$u['1362'] = array(
	"ALTER TABLE report_base_goods_collect DROP INDEX kh_id_biz_date_shop_code_barcode;",
	"ALTER TABLE report_base_goods_collect ADD UNIQUE KEY `kh_id_biz_date_shop_code_sku` (`kh_id`,`biz_date`,`shop_code`,`sku`) USING BTREE;",
	"drop procedure pro_report_base_goods_collect;",
	"CREATE  PROCEDURE `pro_report_base_goods_collect`(
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
	"CREATE PROCEDURE `pro_report_base_goods_collect_repair`() 
	  COMMENT '修复数据' 
	BEGIN
	  
	declare repair_date varchar(30);

	set repair_date = DATE(now());

	WHILE  repair_date > '2017-03-01' 
	DO 

	  set repair_date = date_add(repair_date, interval -1 day);

	  CALL pro_report_base_goods_collect (2348,repair_date);


	END WHILE ;

	select repair_date;


	END ",
);

$u['1319'] = array(
	"update sys_params set parent_code='app' where param_code='clodop_print';",
);

