<?php
$u = array();
$u['335'] = array(
    "ALTER TABLE `api_weipinhuijit_pick` ADD COLUMN is_execute TINYINT(1) DEFAULT '0' COMMENT '是否生成销货单'; ", //唯品会拣货单表增加是否生成销货单状态
    
    "UPDATE api_weipinhuijit_pick a JOIN (SELECT pick_no,count(1) FROM api_weipinhuijit_store_out_record GROUP BY pick_no) b ON a.pick_no=b.pick_no SET a.is_execute=1;", //维护历史数据 is_execute字段
    
    "DELETE FROM `sys_params` WHERE param_code in('auto_print_box','auto_print_jit_box');", //自动打印装箱单，自动打印JIT箱唛，去掉参数设置
    
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8040400', '8040000', 'url', '唯品会仓库管理', 'api/api_weipinhuijit_warehouse/do_list', '4', '1', '0', '1', '0');", 
//增加唯品会JIT仓库管理菜单
    
    "CREATE TABLE `api_weipinhuijit_warehouse` (
	`warehouse_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`warehouse_no` INT (11) NOT NULL COMMENT '序号值',
	`warehouse_code` VARCHAR (128) DEFAULT '' COMMENT '仓库代码',
	`warehouse_name` VARCHAR (128) DEFAULT '' COMMENT '仓库名称',
	`status` TINYINT (1) DEFAULT '1' COMMENT '状态：0-停用，1-启用',
	`desc` VARCHAR (255) DEFAULT '' COMMENT '描述',
	`create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
	`lastchanged` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
	PRIMARY KEY (`warehouse_id`),
	UNIQUE KEY `idxu_key` (`warehouse_code`),
	KEY `ix_name` (`warehouse_name`)
    ) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '唯品会JIT仓库';", //唯品会JIT仓库表
    
    "INSERT INTO `api_weipinhuijit_warehouse` (`warehouse_no`,`warehouse_code`,`warehouse_name`,`status`,`desc`,`create_time`)VALUES
        (1,'VIP_NH','南海仓',1,'华南：南海仓',NOW()),
        (2,'VIP_SH','上海仓',1,'华东：上海仓',NOW()),
        (3,'VIP_CD','成都仓',1,'西北：成都仓',NOW()),
        (4,'VIP_BJ','北京仓',1,'北京仓',NOW()),
        (5,'VIP_HZ','鄂州仓',1,'华中：鄂州仓',NOW()),
        (7,'VIP_HH','花海仓',1,'花海仓',NOW()),
        (8,'VIP_ZZ','郑州',0,'郑州',NOW()),
        (9,'VIP_SE','首尔',0,'首尔',NOW()),
        (10,'VIP_JC','白云',0,'白云',NOW()),
        (11,'VIP_DA','唯品团',0,'唯品团',NOW()),
        (12,'VIP_MRC','唯品卡',0,'唯品卡',NOW()),
        (13,'VIP_ZZKG','郑州空港',0,'郑州空港',NOW()),
        (14,'VIP_GZNS','广州南沙',0,'广州南沙',NOW()),
        (15,'VIP_CQKG','重庆空港',0,'重庆空港',NOW()),
        (16,'VIP_SZGY','苏州工业',0,'苏州工业',NOW()),
        (17,'VIP_FZPT','福州平潭',0,'福州平潭',NOW()),
        (18,'VIP_QDHD','青岛黄岛',0,'青岛黄岛',NOW()),
        (19,'HT_GZZY','广州中远',0,'广州中远',NOW()),
        (20,'HT_GZFLXY','富力心怡仓',0,'富力心怡仓',NOW()),
        (21,'VIP_NBJCBS','机场保税仓',0,'机场保税仓',NOW()),
        (22,'HT_NBYC','云仓代运营',0,'云仓代运营',NOW()),
        (23,'HT_HZHD','杭州航都仓',0,'杭州航都仓',NOW()),
        (24,'HT_JPRT','日本日通仓',0,'日本日通仓',NOW()),
        (25,'HT_AUXNXY','悉尼心怡仓',0,'悉尼心怡仓',NOW()),
        (26,'HT_USALATM','洛杉矶天马仓',0,'洛杉矶天马仓',NOW()),
        (27,'HT_USANYTM','纽约天马仓',0,'纽约天马仓',NOW()),
        (28,'HT_SZQHBH','前海保宏仓',0,'前海保宏仓',NOW()),
        (29,'FJFZ','福建福州仓',1,'福建福州仓',NOW());", //唯品会JIT仓库数据
);

$u['331'] = array(
    "ALTER table op_gift_strategy_detail ADD COLUMN ranking_time_type TINYINT(3) NOT null DEFAULT '1' COMMENT '1:指定时间点 ;0: 循环整点';",
    "ALTER table op_gift_strategy_detail ADD COLUMN ranking_hour VARCHAR(50) NOT null DEFAULT '' COMMENT '存指定时间点时';",
    "CREATE TABLE `oms_sell_record_rank` (
        `sell_record_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `sell_record_code` varchar(20) NOT NULL DEFAULT '' COMMENT '单据编号(订单号)',
        `deal_code_list` varchar(200) NOT NULL DEFAULT '' COMMENT '交易号',  
        `sale_channel_code` varchar(20) NOT NULL,
        `buyer_name` varchar(30) DEFAULT NULL,
        `store_code` varchar(20) NOT NULL DEFAULT '' COMMENT '仓库代码',
        `shop_code` varchar(20) NOT NULL DEFAULT '' COMMENT '商店代码',
        `order_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '订单总额,商品总额+运费+配送手续费',
        `record_time` datetime NOT NULL COMMENT '下单时间',
        `op_gift_strategy_detail_id` int(11) DEFAULT NULL COMMENT '规则id',
        `pay_time` datetime NOT NULL COMMENT '支付时间',
        `ranking_hour` datetime NOT NULL COMMENT '指定时间点',
        `is_has_given` int(11) NOT NULL DEFAULT '0',
        `rank_start` int(11) NOT NULL DEFAULT '0',
        `rank_end` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`sell_record_id`),
        UNIQUE KEY `idxu_record_code` (`sell_record_code`,`shop_code`,`op_gift_strategy_detail_id`,`ranking_hour`,`rank_start`,`rank_end`) USING BTREE
    ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='排名送订单列表';",
    "INSERT INTO `sys_action` VALUES ('4060000', '4040000', 'url', '按排名送赠品', 'oms/order_gift/rank_list', '2', '1', '0', '1','0');"
);
$u['340'] = array(
    "INSERT INTO `sys_action` VALUES ('5050000', '5000000', 'group', '商品初始化', 'goods-init', '5', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` VALUES ('5050100', '5050000', 'url', '商品初始化', 'prm/goods_init/do_list', '1', '1', '0', '1', '0');",
    "DROP PROCEDURE IF EXISTS `tools_goods_init_from_taobao`;",
    "CREATE PROCEDURE `tools_goods_init_from_taobao`(
        p_code varchar(50),
        p_name varchar(50),
        p_cat_name varchar(50),
        p_brand_name varchar(50),
        p_price varchar(50),
        p_state int,
        p_barcode varchar(50),
        p_sku_desc varchar(150),
        p_sku_id varchar(50))
     mypro:BEGIN
        DECLARE spec1_str varchar(150);
        DECLARE s_spec1_name varchar(50);

        DECLARE spec2_str varchar(150);
        DECLARE s_spec2_name varchar(50);

        DECLARE s_spec1_code varchar(50);
        DECLARE s_spec2_code varchar(50);

        DECLARE s_respose varchar(50) default '';

        if  p_sku_desc = '' or p_sku_desc is null
        then
                set s_spec1_name = '通用';
                set s_spec2_name = '通用';
        else
        	set spec1_str = SUBSTRING_INDEX(p_sku_desc,';',1);
        	set s_spec1_name = SUBSTRING_INDEX(spec1_str,':',-1);
   		if s_spec1_name = '' then set s_spec1_name = '通用'; end if;
        	set spec2_str = MID(p_sku_desc,CHAR_LENGTH(spec1_str)+1);
        	set s_spec2_name = SUBSTRING_INDEX(spec2_str,':',-1);
		if s_spec2_name = '' then set s_spec2_name = '通用'; end if;
        end if;

	set s_respose = f_goods_init
            (p_code,
            p_name,
            p_cat_name,
            p_brand_name,
            p_state,
            s_spec1_name,
            s_spec2_name,
            p_barcode,
            '',
            '默认',
            '默认',
            0,
            0,
            0,
            0,
            p_price,
            0,
            0);

        if p_sku_id <>'' and p_sku_id is not null and s_respose <> '-1'
        then
                update api_goods_sku set sys_goods_barcode = s_respose where sku_id = p_sku_id;
        end if;

        select s_respose;

        END;",
    "DROP PROCEDURE IF EXISTS `tools_goods_init_from_taobao_shop`;",
    "CREATE PROCEDURE `tools_goods_init_from_taobao_shop`(p_shop_code varchar(50))
     mypro:BEGIN
        DECLARE s_item_count int;
        DECLARE s_code varchar(50);
        DECLARE s_name varchar(50);
        DECLARE s_cat_name varchar(50);
        DECLARE s_brand_name varchar(50);
        DECLARE s_price varchar(50);
        DECLARE s_state int;
        DECLARE s_barcode varchar(50);
        DECLARE s_sku_desc varchar(150);
        DECLARE s_sku_id varchar(50);
        DECLARE done bool DEFAULT false;
        DECLARE curl CURSOR FOR 
        select api_goods.goods_code,api_goods.goods_name,api_goods.price,api_goods.cat,api_goods.brand,
        api_goods_sku.sku_properties_name,api_goods_sku.goods_barcode,api_goods.status ,api_goods_sku.sku_id  
        from api_goods,api_goods_sku where api_goods.shop_code =p_shop_code 
        and api_goods.goods_from_id = api_goods_sku.goods_from_id and api_goods.invalid_status = 1 and api_goods_sku.status = 1 AND api_goods.goods_code = '1511021';

        DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = true;  
            OPEN curl;  
            personLoop: LOOP  
              FETCH curl INTO s_code,s_name,s_price,s_cat_name,s_brand_name,s_sku_desc,s_barcode,s_state,s_sku_id;  
              IF done THEN  
                LEAVE personLoop;  
              ELSE  
                        call tools_goods_init_from_taobao(s_code,s_name,s_cat_name,s_brand_name,s_price,s_state,s_barcode,s_sku_desc,s_sku_id);
              END IF;  
            END LOOP personLoop;  
            CLOSE curl;  
        END;",
    "DROP FUNCTION IF EXISTS `f_brand_init`;",
    "CREATE FUNCTION `f_brand_init`(p_brand_name varchar(50)) RETURNS varchar(50) CHARSET utf8
     BEGIN
        DECLARE spec_str_code varchar(50) default '';
        DECLARE spec_str_code_max varchar(50)  default '';

        select  `brand_code` into spec_str_code from base_brand where `brand_name` = p_brand_name limit 1;

        if spec_str_code is null or spec_str_code = ''
        then
                select max(`brand_code`) into spec_str_code_max from base_brand WHERE `brand_code` LIKE '1%'  limit 1; 

                if spec_str_code_max = '' or spec_str_code_max is null
                then 
                        set spec_str_code_max = 100;
                end if;

                set spec_str_code = spec_str_code_max + 1;

                INSERT INTO base_brand  (`brand_code`,`brand_name`)  VALUES (spec_str_code,p_brand_name);

        end if;

        RETURN spec_str_code;
        END;",
    "DROP FUNCTION IF EXISTS `f_cat_init`;",
    "CREATE FUNCTION `f_cat_init`(p_cat_name varchar(50)) RETURNS varchar(50) CHARSET utf8
     BEGIN
        DECLARE spec_str_code varchar(50) default '';
        DECLARE spec_str_code_max varchar(50)  default '';

        select  `category_code` into spec_str_code from base_category where `category_name` = p_cat_name limit 1;

        if spec_str_code is null or spec_str_code = ''
        then
           select max(`category_code`) into spec_str_code_max from base_category 
WHERE `category_code` LIKE '1%' or `category_code` LIKE '2%' or `category_code` LIKE '3%' or `category_code` LIKE '4%' 
or `category_code` LIKE '5%' or `category_code` LIKE '6%' or `category_code` LIKE '7%' or `category_code` LIKE '8%' limit 1; 

                if spec_str_code_max = '' or spec_str_code_max is null
                then 
                        set spec_str_code_max = 100;
                end if;

                set spec_str_code = spec_str_code_max + 1;

                INSERT INTO base_category  (`category_code`,`category_name`,p_code)  VALUES (spec_str_code,p_cat_name,0);

        end if;

        RETURN spec_str_code;
        END;",
    "DROP FUNCTION IF EXISTS `f_goods_init`;",
    "CREATE FUNCTION `f_goods_init`(
        p_code varchar(50),
        p_name varchar(50),
        p_cat_name varchar(50),
        p_brand_name varchar(50),
        p_state int,
        p_spec1_name varchar(50),
        p_spec2_name varchar(50),
        p_barcode varchar(50),
        p_goods_short_name varchar(50),
        p_season_name varchar(50),
        p_year_name varchar(50),
        p_weight varchar(50),
        p_period_validity varchar(50),
        p_operating_cycles varchar(50),
        p_cost_price varchar(50),
        p_sell_price varchar(50),

        p_trade_price decimal(20,3),
        p_purchase_price decimal(20,3)

        ) RETURNS varchar(50) CHARSET utf8
      BEGIN

	DECLARE return_msg varchar(50);

        DECLARE s_goods_count varchar(50);
        DECLARE s_cat_code varchar(50);
        DECLARE s_brand_code varchar(50);
        DECLARE s_spec1_code varchar(50);
        DECLARE s_spec2_code varchar(50);
        DECLARE s_sku varchar(50);

	declare s_year_code varchar(50);
	declare s_season_code varchar(50);

        DECLARE s_respose varchar(50) default '1';

        if p_code = '' or p_name = '' or p_cat_name = '' or p_brand_name = '' or p_spec1_name = '' or p_spec2_name = '' or 
p_season_name = '' or p_year_name = '' 
        then 
                set return_msg = '-1';RETURN return_msg;
        end if;

        select count(1) into s_goods_count  from base_goods where goods_code = p_code;

	set s_cat_code = 	f_cat_init(p_cat_name);
        set s_brand_code = f_brand_init(p_brand_name);
	set s_year_code = f_year_init(p_year_name);
        set s_season_code = f_season_init(p_season_name);

        if s_goods_count = 0
        then
              INSERT INTO 
base_goods (goods_code,goods_name,category_code,category_name,brand_code,brand_name,sell_code,sell_status,status,state,
season_code,season_name,year_code,year_name,sell_price,cost_price,purchase_price,trade_price,weight,goods_short_name)
        VALUES 
(p_code,p_name,s_cat_code,p_cat_name,s_brand_code,p_brand_name,0,2,0,p_state,
s_season_code,p_season_name,s_year_code,p_year_name,p_sell_price,p_cost_price,p_purchase_price,p_trade_price,
p_weight,
p_goods_short_name);

	else
		update base_goods set `season_code`=s_season_code,`season_name`=p_season_name,year_code=s_year_code ,
year_name=p_year_name  
		where goods_code = p_code;
        end if;

        set s_spec1_code = f_spec1_init(p_spec1_name);
        set s_spec2_code = f_spec2_init(p_spec2_name);

        set s_sku = concat(p_code,s_spec1_code,s_spec2_code);

        if  p_barcode = '' or p_barcode is null
        then
                set p_barcode = s_sku;
        end if;

        INSERT ignore INTO goods_sku (goods_code,spec1_code,spec1_name,spec2_code,spec2_name,sku,barcode)
        VALUES (p_code,s_spec1_code,p_spec1_name,s_spec2_code,p_spec2_name,s_sku,p_barcode);

        INSERT ignore INTO goods_barcode (goods_code,spec1_code,spec2_code,sku,barcode,add_time)
        VALUES (p_code,s_spec1_code,s_spec2_code,s_sku,p_barcode,now());

	set return_msg = p_barcode;

        RETURN return_msg;

        END;",
    "DROP FUNCTION IF EXISTS `f_season_init`;",
    "CREATE FUNCTION `f_season_init`(p_season_name varchar(50)) RETURNS varchar(50) CHARSET utf8
BEGIN

        DECLARE spec_str_code varchar(50) default '';
        DECLARE spec_str_code_max varchar(50)  default '';

        select  `season_code` into spec_str_code from base_season where `season_name` = p_season_name limit 1;

        if spec_str_code is null or spec_str_code = ''
        then
                select max(`season_code`) into spec_str_code_max from base_season  
WHERE `season_code` LIKE '1%' or `season_code` like '2%'  or `season_code` like '3%'  limit 1; 

                if spec_str_code_max = '' or spec_str_code_max is null
                then 
                        set spec_str_code_max = 100;
                end if;

                set spec_str_code = spec_str_code_max + 1;

                INSERT INTO base_season  (`season_name`,`season_code`)  VALUES (p_season_name,spec_str_code);

        end if;

        RETURN spec_str_code;
        END;",
    "DROP FUNCTION IF EXISTS `f_spec1_init`;",
    "CREATE FUNCTION `f_spec1_init`(p_spec_name varchar(50)) RETURNS varchar(100) CHARSET utf8
BEGIN
                #Routine body goes here...
        DECLARE spec1_str_code varchar(50) default '';
        DECLARE spec1_str_code_max varchar(50)  default '';

        select  `spec1_code` into spec1_str_code from `base_spec1` where `spec1_name` = p_spec_name limit 1;

        if spec1_str_code is null or spec1_str_code = ''
        then
                select max(spec1_code) into spec1_str_code_max from base_spec1 
        WHERE spec1_code LIKE '1%' or spec1_code LIKE '2%' or spec1_code LIKE '3%' or spec1_code LIKE '4%' or spec1_code LIKE '5%' 
or spec1_code LIKE '6%'  or spec1_code LIKE '7%'  or spec1_code LIKE '8%'  limit 1; 

                if spec1_str_code_max = '' or spec1_str_code_max is null
                then 
                        set spec1_str_code_max = 100;
                end if;

                set spec1_str_code = spec1_str_code_max + 1;

                INSERT INTO `base_spec1`  (`spec1_name`,`spec1_code`)  VALUES (p_spec_name,spec1_str_code);

        end if;

        RETURN spec1_str_code;

        END;",
    "DROP FUNCTION IF EXISTS `f_spec2_init`;",
    "CREATE FUNCTION `f_spec2_init`(p_spec_name varchar(50)) RETURNS varchar(50) CHARSET utf8
BEGIN

        DECLARE spec_str_code varchar(50) default '';
        DECLARE spec_str_code_max varchar(50)  default '';

        select  `spec2_code` into spec_str_code from `base_spec2` where `spec2_name` = p_spec_name limit 1;

        if spec_str_code is null or spec_str_code = ''
        then
                select max(spec2_code) into spec_str_code_max from base_spec2 
WHERE spec2_code LIKE '1%' or spec2_code like '2%'  or spec2_code like '3%' or spec2_code like '4%' or spec2_code like '5%' 
or spec2_code like '6%' or spec2_code like '7%' or spec2_code like '8%' limit 1; 

                if spec_str_code_max = '' or spec_str_code_max is null
                then 
                        set spec_str_code_max = 100;
                end if;

                set spec_str_code = spec_str_code_max + 1;

                INSERT INTO `base_spec2`  (`spec2_name`,`spec2_code`)  VALUES (p_spec_name,spec_str_code);

        end if;

        RETURN spec_str_code;
        END;",
    "DROP FUNCTION IF EXISTS `f_year_init`;",
    "CREATE FUNCTION `f_year_init`(p_year_name varchar(50)) RETURNS varchar(50) CHARSET utf8
BEGIN

        DECLARE spec_str_code varchar(50) default '';
        DECLARE spec_str_code_max varchar(50)  default '';

        select  year_code into spec_str_code from base_year where year_name = p_year_name limit 1;

        if spec_str_code is null or spec_str_code = ''
        then
                select max(year_code) into spec_str_code_max from base_year  
WHERE year_code LIKE '1%' or year_code like '2%'  or year_code like '3%'  limit 1; 

                if spec_str_code_max = '' or spec_str_code_max is null
                then 
                        set spec_str_code_max = 100;
                end if;

                set spec_str_code = spec_str_code_max + 1;

                INSERT INTO base_year  (year_name,year_code)  VALUES (p_year_name,spec_str_code);

        end if;

        RETURN spec_str_code;
        END;"
);

$u['339'] = array(
    "INSERT INTO `base_sale_channel` VALUES ('43', 'renrendian', 'rrd', '人人店', '1', '1', '', '2016-05-21 13:57:24');",
);
$u['329'] = array(
    "DELETE FROM sys_user_pref WHERE iid = 'goods_do_list/table';"
);
$u['354'] = array(
    "UPDATE `base_area` SET  `name`='郧阳区' WHERE (`id`='420321000000');",
    "INSERT INTO `base_area` (`id`, `type`, `name`, `parent_id`, `zip`, `lastchanged`, `url`, `catch`) VALUES ('420301000000', '4', '十堰经济技术开发区', '420300000000', '', NOW('yyyy-mm-dd hh:mm:ss'), '', '1');",
);