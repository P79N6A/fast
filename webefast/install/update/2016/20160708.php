<?php

$u = array();
$u['425'] = array(
    "INSERT INTO `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`) VALUES ('', 'buyer_remark', 'op', '快递适配-买家留言匹配', 'radio', '[\"关闭\",\"开启\"]', '1', '0.00', '1-开启 0-关闭', '2016-07-01 15:43:19', '开启后，快递适配策略增加‘买家留言匹配’设置项，系统转单时会按照买家留言自动匹配对应的配送方式');",
);
$u['408'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('3040000', '3000000', 'group', '活动管理', 'activity-manage', '2', '1', '0', '0', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('3040100', '3040000', 'url', '活动列表', 'crm/activity/do_list', '1', '1', '0', '0', '0');",
    "CREATE TABLE crm_activity
(
	activity_id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	activity_code VARCHAR(50) NOT NULL DEFAULT '' COMMENT '活动编码',
	activity_name VARCHAR(100) NOT NULL DEFAULT '' COMMENT '活动名称',
	start_time datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '开始时间',
	end_time datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '结束时间',
	shop_code VARCHAR(128) NOT NULL DEFAULT '0' COMMENT '商店编号',
	status SMALLINT(1) NOT NULL DEFAULT '0' COMMENT '启用状态 0 未启用 1 已启用',
	event_desc VARCHAR(200) DEFAULT '' COMMENT'描述',
	last_update TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间'
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='活动表';",
    "CREATE TABLE crm_goods
(
	crm_goods_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	activity_code VARCHAR(50) NOT NULL DEFAULT '' COMMENT '活动代码',
	shop_code VARCHAR(128) NOT NULL DEFAULT '' COMMENT '店铺代码',
	sku VARCHAR(128) DEFAULT '' COMMENT 'sku',
	sync_ratio DECIMAL(4,2) NOT NULL DEFAULT '0.00' COMMENT '同步比例',
	lastchanged TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间'
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='活动商品表';",
    "ALTER TABLE crm_activity ADD UNIQUE KEY(activity_code);",
    "ALTER TABLE crm_goods ADD UNIQUE KEY(activity_code, sku);",
);

$u['429-1'] = array(
    "CREATE TABLE `fx_goods_manage` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `goods_line_code` varchar(128) DEFAULT '' COMMENT '产品线代码',
        `goods_line_name` varchar(128) DEFAULT '' COMMENT '产品线名称',
        `goods_num` varchar(128) DEFAULT '' COMMENT '商品总数',
        `sku_num` varchar(128) DEFAULT '' COMMENT 'SKU总数',
        `create_time` datetime NOT NULL COMMENT '创建时间',
        `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
        PRIMARY KEY (`id`),
        UNIQUE KEY `goods_line_code` (`goods_line_code`) USING BTREE
    ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='分销产品线管理';",
    "alter table fx_goods_manage add last_change_time datetime NOT NULL DEFAULT '0000-00-00' COMMENT '最后更改时间';",
    "CREATE TABLE `fx_goods_price_custom_grade` (
        `price_custom_grade_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `goods_line_code` varchar(128) DEFAULT '' COMMENT '产品线代码',
        `grade_code` varchar(128) DEFAULT '' COMMENT '分销商等级code',
        `grade_name` varchar(128) DEFAULT '' COMMENT '分销商等级名称',
        `rebates` varchar(128) DEFAULT '' COMMENT '折扣（基于吊牌价）',
        `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
        PRIMARY KEY (`price_custom_grade_id`),
        UNIQUE KEY `grade_code_goods_line_code` (`goods_line_code`,`grade_code`) USING BTREE
    ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='商品定价(分销商等级)';",
    "CREATE TABLE `fx_goods_price_custom` (
        `price_custom_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `goods_line_code` varchar(128) DEFAULT '' COMMENT '产品线代码',
        `custom_code` varchar(128) DEFAULT '' COMMENT '分销商code',
        `custom_name` varchar(128) DEFAULT '' COMMENT '分销商名称',
        `rebates` varchar(128) DEFAULT '' COMMENT '折扣（基于吊牌价）',
        `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
        PRIMARY KEY (`price_custom_id`),
        UNIQUE KEY `custom_code_goods_line_code` (`goods_line_code`,`custom_code`) USING BTREE
    ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='商品定价(指定分销商)';",
    "alter table sys_user change login_type login_type tinyint(3) DEFAULT '0' COMMENT '0：后台用户 1：门店用户 2：分销账户';",
    "INSERT INTO `sys_action` VALUES ('8080000', '8000000', 'group', '分销商品', 'fenxiao_goods', '3', '1', '0', '1','0');",
    "INSERT INTO `sys_action` VALUES ('8080100', '8080000', 'url', '分销商品列表', 'fx/goods/do_list', '1', '1', '0', '1','0');",
    "INSERT INTO `sys_action` VALUES ('8080200', '8080000', 'url', '分销产品线管理', 'fx/goods_manage/do_list', '5', '1', '0', '1','0');",
    "INSERT INTO `sys_action` VALUES ('8080300', '8080000', 'url', '商品库存查询', 'fx/goods_inv/do_list', '10', '1', '0', '1','0');",
    "CREATE TABLE `fx_goods` (
  `goods_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `goods_line_code` varchar(128) DEFAULT '' COMMENT '产品线代码',
  `goods_name` varchar(128) DEFAULT '' COMMENT '商品名称',
  `goods_code` varchar(128) DEFAULT '' COMMENT '商品编码',
  `goods_barcode` varchar(128) DEFAULT '' COMMENT '商品条形码',
  `goods_sku` varchar(128) DEFAULT '' COMMENT 'goods_sku',
  `spec1_code` varchar(64) DEFAULT '' COMMENT '颜色代码',
  `spec1_name` varchar(128) DEFAULT NULL,
  `spec2_code` varchar(64) DEFAULT '' COMMENT '尺码代码',
  `spec2_name` varchar(128) DEFAULT NULL,
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`goods_id`),
  UNIQUE KEY `code_goods_sku` (`goods_line_code`,`goods_sku`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='分销产品线管理商品';"

);
$u['bug_328'] = array(
    "DROP PROCEDURE IF EXISTS `tools_goods_init_from_taobao`;",
    "CREATE PROCEDURE `tools_goods_init_from_taobao`(p_code varchar(50),
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

if  p_brand_name = '' or p_brand_name is null
        then
                set p_brand_name = '通用';
                end if;


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
            0,'');

        if p_sku_id <>'' and p_sku_id is not null and s_respose <> '-1'
        then
                update api_goods_sku set sys_goods_barcode = s_respose where sku_id = p_sku_id;
        end if;

        select s_respose;

        END",
    "DROP FUNCTION IF EXISTS `f_goods_init`;",
    "CREATE FUNCTION `f_goods_init`(p_code varchar(50),
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
        p_purchase_price decimal(20,3), p_goods_produce_name varchar(100)) RETURNS varchar(50) CHARSET utf8
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
season_code,season_name,year_code,year_name,sell_price,cost_price,purchase_price,trade_price,weight,goods_short_name,goods_produce_name)
        VALUES 
(p_code,p_name,s_cat_code,p_cat_name,s_brand_code,p_brand_name,0,2,0,p_state,
s_season_code,p_season_name,s_year_code,p_year_name,p_sell_price,p_cost_price,p_purchase_price,p_trade_price,
p_weight,
p_goods_short_name,p_goods_produce_name );

	else

		set s_cat_code = 	f_cat_init(p_cat_name);

		update base_goods set `season_code`=s_season_code,`season_name`=p_season_name,year_code=s_year_code ,
year_name=p_year_name , goods_produce_name =  p_goods_produce_name ,cost_price=p_cost_price,purchase_price=p_purchase_price,
category_name = p_cat_name,category_code = s_cat_code 
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

        END",
    
);

$u['443'] = array(
    "INSERT INTO `sys_params` (`param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`) VALUES ('waves_create_sort_shelf', 'waves_property', '波次订单按照商品库位排序生成', 'radio', '[\"关闭\",\"开启\"]', '0', '0.00', '1-开启 0-关闭', NOW(), '开启后，波次单中订单按照商品库位排序后生成序号，便于仓库打印快递单拣货');"
);
$u['bug_345'] = array(
    "ALTER TABLE api_goods_sku ADD COLUMN `last_sync_inv_num` int(8) DEFAULT '-1' COMMENT '最后一次更新的库存';",
);
$u['bug_347'] = array(
    "ALTER TABLE api_taobao_refund DROP  PRIMARY  KEY;",
    "ALTER TABLE api_taobao_refund ADD  UNIQUE KEY `refund_id` (`refund_id`);",
    "ALTER TABLE api_taobao_refund ADD COLUMN `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST ;",
);
