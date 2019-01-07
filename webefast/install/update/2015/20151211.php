<?php
$u = array();
$u['FSF-1883'] = array("delete from sys_user_pref where iid = 'sell_return_finance/do_list' and type='custom_table_field'");
$u["FSF-1882"] = array(
		"INSERT INTO `base_sale_channel` VALUES ('35', 'miya', 'my', '蜜芽宝贝', '1', '1', '', '2015-12-02 16:12:24');",
		"ALTER TABLE api_refund_detail ADD COLUMN `sku_id` varchar(30) DEFAULT NULL COMMENT 'sku_id';",
		"ALTER TABLE api_refund_detail DROP KEY  refund_id;",
		"ALTER TABLE api_refund_detail ADD UNIQUE KEY `refund_id_sku_id` (`refund_id`,`sku_id`);",
		"ALTER TABLE api_refund_detail ADD KEY `refund_id` (`refund_id`);",
		);




$u['FSF-1880'] = array(
    "ALTER TABLE `base_goods` ADD COLUMN `category_name`  varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `category_code`;
", "
ALTER TABLE `base_goods` ADD COLUMN `brand_name`  varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `brand_code`;
", "
ALTER TABLE `base_goods` ADD COLUMN `season_name`  varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `season_code`;
", "
ALTER TABLE `base_goods` MODIFY COLUMN `price`  decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '价格' AFTER `supplier_code`;
", "
ALTER TABLE `base_goods` MODIFY COLUMN `status`  int(4) NULL DEFAULT 0 COMMENT '0：启用 1：停用' AFTER `stuff`;
", "
ALTER TABLE `base_goods` ADD COLUMN `year_name`  varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `year_code`;
", "
ALTER TABLE `base_goods` ADD COLUMN `cost_price`  decimal(20,3) NULL DEFAULT 0.000 COMMENT '成本价' AFTER `state`;
", "
ALTER TABLE `base_goods` ADD COLUMN `sell_price`  decimal(20,3) NULL DEFAULT 0.000 COMMENT '标准售价' AFTER `cost_price`;
", "
ALTER TABLE `base_goods` ADD COLUMN `sell_price1`  decimal(20,3) NULL DEFAULT 0.000 COMMENT '售价1' AFTER `sell_price`;
", "
ALTER TABLE `base_goods` ADD COLUMN `sell_price2`  decimal(20,3) NULL DEFAULT 0.000 COMMENT '售价2' AFTER `sell_price1`;
", "
ALTER TABLE `base_goods` ADD COLUMN `sell_price3`  decimal(20,3) NULL DEFAULT 0.000 COMMENT '售价3' AFTER `sell_price2`;
", "
ALTER TABLE `base_goods` ADD COLUMN `sell_price4`  decimal(20,3) NULL DEFAULT 0.000 COMMENT '售价4' AFTER `sell_price3`;
", "
ALTER TABLE `base_goods` ADD COLUMN `sell_price5`  decimal(20,3) NULL DEFAULT 0.000 COMMENT '售价5' AFTER `sell_price4`;
", "
ALTER TABLE `base_goods` ADD COLUMN `sell_price6`  decimal(20,3) NULL DEFAULT 0.000 COMMENT '售价6' AFTER `sell_price5`;
", "
ALTER TABLE `base_goods` ADD COLUMN `buy_price`  decimal(20,3) NULL DEFAULT 0.000 COMMENT '标准进价' AFTER `sell_price6`;
", "
ALTER TABLE `base_goods` ADD COLUMN `buy_price1`  decimal(20,3) NULL DEFAULT 0.000 COMMENT '进价1' AFTER `buy_price`;
", "
ALTER TABLE `base_goods` ADD COLUMN `buy_price2`  decimal(20,3) NULL DEFAULT 0.000 COMMENT '进价2' AFTER `buy_price1`;
", "
ALTER TABLE `base_goods` ADD COLUMN `buy_price3`  decimal(20,3) NULL DEFAULT 0.000 COMMENT '进价3' AFTER `buy_price2`;
", "
ALTER TABLE `base_goods` ADD COLUMN `trade_price`  decimal(20,3) NULL DEFAULT 0.000 COMMENT '批发价' AFTER `buy_price3`;
", "
ALTER TABLE `base_goods` ADD COLUMN `purchase_price`  decimal(20,3) NULL DEFAULT 0.000 COMMENT '进货价' AFTER `trade_price`;
", "
ALTER TABLE `base_goods` DROP COLUMN `year`;
", "
CREATE INDEX `_statsu` ON `base_goods`(`status`) USING BTREE ;
", "
ALTER TABLE `goods_sku` ADD COLUMN `spec1_name`  varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `spec1_code`;
", "
ALTER TABLE `goods_sku` ADD COLUMN `spec2_name`  varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `spec2_code`;
", "
ALTER TABLE `goods_sku` ADD COLUMN `barcode`  varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `sku`;
", "
ALTER TABLE `goods_sku` DROP COLUMN `goods_id`;
", "
ALTER TABLE `goods_sku` DROP COLUMN `spec1_id`;
",
    "
ALTER TABLE `goods_sku` DROP COLUMN `spec2_id`;
",
    "
 update goods_sku,base_spec1 set goods_sku.spec1_name = base_spec1.spec1_name 
where goods_sku.spec1_code=base_spec1.spec1_code;
",
    "
update goods_sku,base_spec2 set goods_sku.spec2_name = base_spec2.spec2_name 
where goods_sku.spec2_code=base_spec2.spec2_code;
",
    "
update base_goods,goods_price set
 base_goods.cost_price = goods_price.cost_price ,
 base_goods.sell_price= goods_price.sell_price ,
 base_goods.sell_price1= goods_price.sell_price1 ,
 base_goods.sell_price2= goods_price.sell_price2 ,
 base_goods.sell_price3= goods_price.sell_price3 ,
 base_goods.sell_price4= goods_price.sell_price4 ,
 base_goods.sell_price5= goods_price.sell_price5 ,
 base_goods.sell_price6= goods_price.sell_price6 ,
 base_goods.buy_price= goods_price.buy_price ,
 base_goods.buy_price1= goods_price.buy_price1 ,
 base_goods.buy_price2= goods_price.buy_price2 ,
 base_goods.buy_price3= goods_price.buy_price3 ,
 base_goods.trade_price= goods_price.trade_price ,
 base_goods.purchase_price= goods_price.purchase_price 
where base_goods.goods_code=goods_price.goods_code;
",
    "

update base_goods,base_brand 
set base_goods.brand_name = base_brand.brand_name
where base_goods.brand_code = base_brand.brand_code;
",
    "
update base_goods,base_category
set base_goods.category_name = base_category.category_name
where base_goods.category_code = base_category.category_code;
",
    "
update base_goods,base_brand 
set base_goods.brand_name = base_brand.brand_name
where base_goods.brand_code = base_brand.brand_code;
",
    "update base_goods ,base_season set base_goods.season_name= base_season.season_name
where  base_goods.season_code= base_season.season_code",
    
    
    "

update base_goods,base_year 
set base_goods.year_name = base_year.year_name
where base_goods.year_code = base_year.year_code;

",
    "
update goods_sku,goods_barcode set 
 goods_sku.barcode=goods_barcode.barcode 
where  goods_sku.sku=goods_barcode.sku ;   
 ",
  "ALTER TABLE `goods_inv`
DROP INDEX `goods_id`,
DROP INDEX `_index_key` ,
ADD UNIQUE INDEX `_index_key` (`sku`, `store_code`) USING BTREE ;
",  
    
 "ALTER TABLE `goods_inv_lof`
DROP INDEX `goods_id`,
DROP INDEX `_index_key` ,
ADD UNIQUE INDEX `_index_key` (`sku`, `store_code`, `lof_no`, `production_date`) USING BTREE ,
DROP INDEX `_index_sku` ,
ADD INDEX `_index_goods` (`goods_code`) USING BTREE ;

"  ,
    "
 ALTER TABLE `b2b_lof_datail`
DROP INDEX `_index_key` ,
ADD UNIQUE INDEX `_index_key` (`order_type`, `order_code`, `sku`, `lof_no`, `production_date`) USING BTREE ;
",

"
ALTER TABLE `oms_return_package_detail`
DROP INDEX `barcode` ,
ADD INDEX `sku` (`sku`) USING BTREE ;
  ", 
    
    
);