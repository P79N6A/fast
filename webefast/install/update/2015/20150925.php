<?php
$u = array();
$u['FSF-1652'] = array(
	"delete from sys_user_pref where iid = 'oms/sell_record_short_list' and type='custom_table_field'"
);
$u['FSF-1670'] = array(
		"INSERT INTO `base_sale_channel` VALUES ('33', 'aliexpress', 'smt', '速卖通', '1', '1', '', '2015-09-14 16:12:24')",
);

$u['FSF-1672'] = array(
    "CREATE TABLE `pur_advide_record` (
  `record_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `record_date` date DEFAULT '0000-00-00' COMMENT '业务时间',
  `start_time` int(11) DEFAULT '0' COMMENT '开始时间',
  `end_time` int(11) DEFAULT '0' COMMENT '结束时间',
  `is_create_pur` tinyint(3) DEFAULT '0' COMMENT '是否生存采购单',
  `pur_code` text COMMENT '是否生存采购单',
  PRIMARY KEY (`record_id`),
  UNIQUE KEY `_key` (`record_date`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='补货建议记录';
",
        "CREATE TABLE `pur_advide_detail` (
  `detail_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT '0',
  `goods_code` varchar(128) DEFAULT '' COMMENT '商品代码',
  `spec1_code` varchar(128) DEFAULT NULL,
  `spec2_code` varchar(128) DEFAULT NULL,
  `sku` varchar(128) DEFAULT '' COMMENT 'sku',
  `barcode` varchar(128) DEFAULT '' COMMENT '条码',
  `record_date` date DEFAULT '0000-00-00' COMMENT '业务时间',
  `sale_week_num` float(11,2) DEFAULT '0.00' COMMENT '7天销售数量',
  `sale_month_num` float(11,2) DEFAULT '0.00' COMMENT '30天销售数量',
  PRIMARY KEY (`detail_id`),
  UNIQUE KEY `record_sku` (`record_date`,`sku`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='补货建议明细';",

    "CREATE TABLE `pur_advide_inv` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_code` varchar(128) NOT NULL,
  `sku` varchar(128) NOT NULL,
  `stock_num` int(11) DEFAULT '0' COMMENT '在库数',
  `road_num` int(11) DEFAULT '0' COMMENT '在途数量',
  `wait_deliver_num` int(11) DEFAULT '0' COMMENT '等待发货数量',
  `out_num` int(11) DEFAULT NULL,
  `pur_num` int(11) DEFAULT '0' COMMENT '补货数',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8; ",
    
    
 "insert into sys_params(param_code,parent_code,param_name,type,form_desc,`value`,remark,memo) 
values('week_proportion','pur_advise','近7天日均销量占比','text','','50','是一个变量，根据销售的淡旺季会有所调整，默认为50%','');"   ,



"insert into sys_params(param_code,parent_code,param_name,type,form_desc,`value`,remark,memo) 
values('month_proportion','pur_advise','近30天日均销量占比','text','','50','是一个变量，根据销售的淡旺季会有所调整，默认为50%','');"   ,

"insert into sys_params(param_code,parent_code,param_name,type,form_desc,`value`,remark,memo) 
values('pur_advise_day','pur_advise','补货天数','text','','30','是一个变量，根据季节月变化系统调整，默认为30','')"   ,
    "INSERT INTO `sys_action` VALUES ('3030200', '3030000', 'url', '商品补货建议', 'op/pur_advise/do_list', '2', '1', '0', '1','0');",
    "INSERT INTO `sys_schedule` VALUES ('48', 'create_pur_advise_data', '商品补货建议数据分析服务', 'create_pur_advise_data', '', '1', '4', '通过数据分析提供补货建议具体数值，默认每天运行一次', '{\"app_act\":\"op\\/pur_advise\\/create_pur_advise_data\",\"app_fmt\":\"json\"}', 'webefast/web/index.php', '0', '0', '0', '86400', '0', 'sys', '', '1443114000', '0');",
    
 );
$u['FSF-1575'] = array(
		"INSERT INTO `sys_action` VALUES ('12020100','12020000','url','BSERP2单据同步','erp/bserp/trade_list','1','1','0','1','1');",
		"CREATE TABLE `api_bserp_categories` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `erp_config_id` int(10) NOT NULL COMMENT 'erp配置id',
		  `DLDM` varchar(50) NOT NULL COMMENT '分类代码',
		  `DLMC` varchar(100) DEFAULT NULL COMMENT '分类名称',
		  `updated` datetime NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `erp_config_id` (`erp_config_id`),
		  UNIQUE KEY `DLDM` (`DLDM`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
		"CREATE TABLE `api_bserp_brands` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `erp_config_id` int(10) NOT NULL COMMENT 'erp配置id',
		  `PPDM` varchar(50) NOT NULL COMMENT '品牌代码',
		  `PPMC` varchar(100) DEFAULT NULL COMMENT '品牌名称',
		  `updated` datetime NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `erp_config_id` (`erp_config_id`),
		  UNIQUE KEY `PPDM` (`PPDM`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
		"CREATE TABLE `api_bserp_item_quantity` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `erp_config_id` int(10) NOT NULL COMMENT 'erp配置id',
		  `efast_store_code` varchar(50) NOT NULL COMMENT 'efast仓库代码',
		  `CKDM` varchar(50) NOT NULL COMMENT '仓库代码',
		  `KWDM` varchar(50) NOT NULL COMMENT '库位代码',
		  `SPDM` varchar(50) NOT NULL COMMENT '商品代码',
		  `GG1DM` varchar(50) NOT NULL COMMENT '颜色代码',
		  `GG2DM` varchar(50) NOT NULL COMMENT '尺码代码',
		  `SL` int(8) NOT NULL DEFAULT '0' COMMENT '数量',
		  `SL1` int(8) DEFAULT '0' COMMENT '数量1',
		  `IDRank` varchar(5) DEFAULT NULL,
		  `updated` datetime NOT NULL COMMENT '最后更新时间 ',
		  `efast_update` datetime DEFAULT NULL COMMENT 'efast库存更新时间',
		  `update_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '库存更新状态',
		  PRIMARY KEY (`id`),
		  KEY `efast_store_code` (`efast_store_code`),
		  KEY `SPDM` (`SPDM`),
		  KEY `CKDM` (`CKDM`),
		  UNIQUE KEY `ckdm_spdm_gg1dm_gg2dm` (`erp_config_id`,`CKDM`,`SPDM`,`GG1DM`,`GG2DM`),
		  KEY `erp_config_id` (`erp_config_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
		"CREATE TABLE `api_bserp_item` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `erp_config_id` int(10) NOT NULL COMMENT 'erp配置id',
		  `SPDM` varchar(50) NOT NULL COMMENT '商品编号',
		  `SPMC` varchar(200) DEFAULT NULL COMMENT '商品名称',
		  `BZSJ` decimal(10,2) DEFAULT NULL COMMENT '标准售价',
		  `CKJ_IN` decimal(10,2) DEFAULT NULL COMMENT '整调价',
		  `BYZD3` varchar(50) DEFAULT NULL COMMENT '品牌',
		  `BYZD4` varchar(50) DEFAULT NULL COMMENT '分类',
		  `BYZD5` varchar(50) DEFAULT NULL COMMENT '季节',
		  `BYZD8` varchar(50) DEFAULT NULL COMMENT '年份',
		  `DWMC` varchar(50) DEFAULT NULL COMMENT '单位名称',
		  `TZSY` varchar(50) DEFAULT NULL,
		  `IDRanK` varchar(50) DEFAULT NULL,
		  `updated` datetime NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `erp_config_id` (`erp_config_id`),
		  UNIQUE KEY `SPDM` (`SPDM`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8;",
		"CREATE TABLE `api_bserp_sizes` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `erp_config_id` int(10) NOT NULL COMMENT 'erp配置id',
		  `GGDM` varchar(50) NOT NULL COMMENT '尺码代码',
		  `GGMC` varchar(100) DEFAULT NULL COMMENT '尺码名称',
		  `updated` datetime NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `erp_config_id` (`erp_config_id`),
		  UNIQUE KEY `GGDM` (`GGDM`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
		"CREATE TABLE `api_bserp_seasons` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `erp_config_id` int(10) NOT NULL COMMENT 'erp配置id',
		  `JJDM` varchar(50) NOT NULL COMMENT '季节代码',
		  `JJMC` varchar(100) DEFAULT NULL COMMENT '季节名称',
		  `updated` datetime NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `erp_config_id` (`erp_config_id`),
		  UNIQUE KEY `JJDM` (`JJDM`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
		"CREATE TABLE `api_bserp_item_size` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `erp_config_id` int(10) NOT NULL COMMENT 'erp配置id',
		  `SPDM` varchar(50) NOT NULL COMMENT '商品代码',
		  `GGDM` varchar(50) NOT NULL COMMENT '尺码代码',
		  `updated` datetime NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `erp_config_id` (`erp_config_id`),
		  UNIQUE KEY `SPDMGGDM` (`SPDM`,`GGDM`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
		"CREATE TABLE `api_bserp_item_color` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `erp_config_id` int(10) NOT NULL COMMENT 'erp配置id',
		  `SPDM` varchar(50) NOT NULL COMMENT '商品代码',
		  `GGDM` varchar(50) NOT NULL COMMENT '颜色代码',
		  `updated` datetime NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `erp_config_id` (`erp_config_id`),
		  UNIQUE KEY `SPDMGGDM` (`SPDM`,`GGDM`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
		"CREATE TABLE `api_bserp_colors` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `erp_config_id` int(10) NOT NULL COMMENT 'erp配置id',
		  `GGDM` varchar(50) NOT NULL COMMENT '颜色代码',
		  `GGMC` varchar(100) DEFAULT NULL COMMENT '规格描述',
		  `updated` datetime NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `erp_config_id` (`erp_config_id`),
		  UNIQUE KEY `GGDM` (`GGDM`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
		"CREATE TABLE `api_bserp_trade` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `sell_record_code` varchar(20) NOT NULL DEFAULT '' COMMENT '单据编号(订单号/退单号)',
		  `deal_code` varchar(80) NOT NULL DEFAULT '' COMMENT '平台交易号(交易号)',
		  `deal_code_list` varchar(200) NOT NULL DEFAULT '' COMMENT '平台交易号列表',
		  `order_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '单据类型 1 销售订单 2销售退单',
		  `store_code` varchar(20) NOT NULL DEFAULT '' COMMENT '仓库代码',
		  `shop_code` varchar(20) NOT NULL DEFAULT '' COMMENT '商店代码',
		  `upload_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '上传状态 0未上传 1已上传 2上传失败',
		  `upload_time` datetime NOT NULL COMMENT '上传时间',
		  `upload_msg` varchar(255) DEFAULT NULL COMMENT '上传失败原因',
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `sell_record_code` (`sell_record_code`,`order_type`),
		  KEY `upload_time` (`upload_time`),
		  KEY `shop_code` (`shop_code`),
		  KEY `store_code` (`store_code`),
		  KEY `order_type` (`order_type`),
		  KEY `deal_code` (`deal_code`) USING BTREE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='erp上传单据';",

);



$u['FSF-1683'] = array(
 "CREATE TABLE `oms_sell_record_notice` (
  `sell_record_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sell_record_code` varchar(20) NOT NULL DEFAULT '' COMMENT '单据编号(订单号)',
  `deal_code` varchar(80) NOT NULL DEFAULT '' COMMENT '平台交易号(交易号)',
  `deal_code_list` varchar(200) NOT NULL DEFAULT '' COMMENT '平台交易号列表',
  `sale_channel_code` varchar(20) NOT NULL,
  `alipay_no` varchar(30) NOT NULL DEFAULT '' COMMENT '支付宝交易号',
  `is_handwork` tinyint(4) NOT NULL DEFAULT '0' COMMENT '手工单 0不是 1是',
  `store_code` varchar(20) NOT NULL DEFAULT '' COMMENT '仓库代码',
  `shop_code` varchar(20) NOT NULL DEFAULT '' COMMENT '商店代码',
  `user_code` varchar(20) NOT NULL DEFAULT '' COMMENT '业务员代码',
  `pay_type` varchar(20) NOT NULL DEFAULT 'secured' COMMENT 'secured 担保交易 cod货到付款 nosecured 非担保交易',
  `pay_code` varchar(20) NOT NULL DEFAULT '' COMMENT '支付方式代码',
  `pay_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '付款状态 0:未付款;1:付款中(部分付款);2:已付款',
  `pay_time` datetime NOT NULL COMMENT '支付时间',
  `customer_code` varchar(20) NOT NULL DEFAULT '' COMMENT '会员代码',
  `buyer_name` varchar(20) NOT NULL DEFAULT '' COMMENT '购买人名称',
  `receiver_name` varchar(20) NOT NULL DEFAULT '' COMMENT '收货人名称',
  `receiver_country` bigint(11) NOT NULL DEFAULT '0' COMMENT '收货人国家',
  `receiver_province` bigint(11) NOT NULL DEFAULT '0' COMMENT '收货人省',
  `receiver_city` bigint(11) NOT NULL DEFAULT '0' COMMENT '收货人市',
  `receiver_district` bigint(11) NOT NULL DEFAULT '0' COMMENT '收货人区',
  `receiver_street` bigint(11) NOT NULL DEFAULT '0' COMMENT '收货人街道',
  `receiver_address` varchar(100) NOT NULL DEFAULT '' COMMENT '收货人地址(包含省市区)',
  `receiver_addr` varchar(100) NOT NULL DEFAULT '' COMMENT '收货人地址(不包含省市区)',
  `receiver_zip_code` varchar(20) NOT NULL DEFAULT '' COMMENT '收货人邮政编码',
  `receiver_mobile` varchar(20) NOT NULL DEFAULT '' COMMENT '收货人手机',
  `receiver_phone` varchar(20) NOT NULL DEFAULT '' COMMENT '收货人固定电话',
  `receiver_email` varchar(40) NOT NULL DEFAULT '' COMMENT '收货人email',
  `express_code` varchar(20) NOT NULL DEFAULT '' COMMENT '配送方式CODE',
  `express_no` varchar(40) NOT NULL DEFAULT '' COMMENT '快递单号',
  `express_data` varchar(2000) NOT NULL DEFAULT '' COMMENT '云栈获取数据',
  `plan_send_time` datetime NOT NULL COMMENT '计划发货时间',
  `goods_num` smallint(11) NOT NULL DEFAULT '0' COMMENT '商品数量',
  `sku_num` tinyint(11) NOT NULL DEFAULT '0' COMMENT 'sku种类数量',
  `goods_weigh` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '商品总重量-克',
  `real_weigh` decimal(20,2) DEFAULT '0.00' COMMENT '实际总重量',
  `weigh_express_money` decimal(20,2) DEFAULT '0.00' COMMENT '称重后计算的快递费用',
  `weigh_time` datetime DEFAULT NULL COMMENT '称重时间',
  `buyer_remark` varchar(255) NOT NULL DEFAULT '' COMMENT '买家留言',
  `seller_remark` varchar(255) NOT NULL DEFAULT '' COMMENT '商家备注',
  `seller_flag` tinyint(4) NOT NULL DEFAULT '0' COMMENT '淘宝订单的旗帜',
  `order_remark` varchar(255) NOT NULL DEFAULT '' COMMENT '订单备注',
  `store_remark` varchar(255) NOT NULL DEFAULT '' COMMENT '仓库留言',
  `order_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '订单总额,商品总额+运费+配送手续费',
  `goods_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '商品总额',
  `express_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '运费',
  `delivery_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '配送手续费',
  `payable_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '订单应付款,商品均摊总金额+运费',
  `paid_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '订单已付款,已支付状态下与应付款相等',
  `invoice_type` varchar(20) NOT NULL DEFAULT '' COMMENT '发票类型',
  `invoice_title` varchar(100) NOT NULL DEFAULT '' COMMENT '发票抬头',
  `invoice_content` varchar(255) NOT NULL DEFAULT '' COMMENT '发票内容',
  `invoice_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '发票金额',
  `invoice_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '发票状态：0没有发票，1未审核状态，2已审核状态，3已开票状态,4已作废状态',
  `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '转入系统时间',
  `record_time` datetime NOT NULL COMMENT '下单时间',
  `record_date` date NOT NULL DEFAULT '0000-00-00' COMMENT '业务日期',
  `is_lock` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否锁定 0不是 1是',
  `is_change_record` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否换货单 0不是 1是',
  `change_record_from` varchar(20) NOT NULL DEFAULT '' COMMENT '换货单的原单号',
  `is_split` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否为被拆分订单 0:不是 1：是',
  `is_split_new` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否为拆分产生的新订单 0:不是 1是',
  `split_order` varchar(20) NOT NULL DEFAULT '0' COMMENT '被拆分的订单号（is_split_new=1的时候有效）',
  `split_new_orders` varchar(255) NOT NULL DEFAULT '' COMMENT '拆分之后的新订单号字符串，逗号分隔（is_split=1的时候有效）',
  `is_combine` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否被合并 0:不是 1：是',
  `is_combine_new` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否为合并产生的新订单 0:不是 1：是',
  `combine_orders` varchar(255) NOT NULL DEFAULT '' COMMENT '被合并的订单号字符串，逗号分隔（is_combine_new=1的时候有效）',
  `combine_new_order` varchar(20) NOT NULL DEFAULT '0' COMMENT '合并之后的新订单号（is_combine=1的时候有效）',
  `is_copy` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否复制单 0:不是 1：是',
  `is_copy_from` varchar(20) NOT NULL DEFAULT '0' COMMENT '复制来源订单号',
  `is_wap` tinyint(1) NOT NULL COMMENT '是否移动端订单',
  `is_jhs` tinyint(1) NOT NULL COMMENT '是否聚划算',
  `point_fee` decimal(10,2) NOT NULL COMMENT '付款-积分',
  `alipay_point_fee` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '付款-集分宝',
  `coupon_fee` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '付款-抵用金额',
  `yfx_fee` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '运费险',
  `change_sell_record` varchar(20) NOT NULL DEFAULT '' COMMENT '换货单原单号',
  `is_print_sellrecord` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否打印订单',
  `is_print_express` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否打印快递',
  `is_fenxiao` tinyint(3) NOT NULL DEFAULT '0',
  `is_notice_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '通知配货时间',
  `check_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '确认时间',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `is_buyer_remark` tinyint(4) DEFAULT '0' COMMENT '是否有买家留言',
  `is_seller_remark` tinyint(4) DEFAULT '0' COMMENT '是否有卖家留言',
  `is_rush` tinyint(4) DEFAULT '0' COMMENT '是否是急单',
  `confirm_person` varchar(20) NOT NULL DEFAULT '' COMMENT '确认人',
  `notice_person` varchar(20) NOT NULL DEFAULT '' COMMENT '通知配货人',
  `fenxiao_id` int(11) DEFAULT NULL COMMENT '分销商id',
  `fenxiao_name` varchar(200) DEFAULT '' COMMENT '分销商名称',
  PRIMARY KEY (`sell_record_id`),
  UNIQUE KEY `idxu_record_code` (`sell_record_code`) USING BTREE,
  UNIQUE KEY `idxu_deal_code` (`deal_code`) USING BTREE,
  KEY `lastchanged` (`lastchanged`) USING BTREE,
  KEY `pay_code` (`pay_code`) USING BTREE,
  KEY `customer_code` (`customer_code`) USING BTREE,
  KEY `record_time` (`record_time`) USING BTREE,
  KEY `pay_time` (`pay_time`) USING BTREE,
  KEY `shop_code` (`shop_code`) USING BTREE,
  KEY `sku_num` (`sku_num`) USING BTREE,
  KEY `is_notice_time` (`is_notice_time`) USING BTREE,
  KEY `plan_send_time` (`plan_send_time`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1943 DEFAULT CHARSET=utf8 COMMENT='通知配货订单';

",
 "CREATE TABLE `oms_sell_record_notice_detail` (
  `sell_record_detail_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sell_record_code` varchar(64) NOT NULL DEFAULT '' COMMENT '单据编号',
  `deal_code` varchar(64) NOT NULL DEFAULT '' COMMENT '平台交易号',
  `sub_deal_code` varchar(64) NOT NULL DEFAULT '' COMMENT '平台子交易号',
  `goods_code` varchar(20) NOT NULL DEFAULT '' COMMENT '商品代码',
  `spec1_code` varchar(20) NOT NULL DEFAULT '' COMMENT '颜色代码',
  `spec2_code` varchar(20) NOT NULL DEFAULT '' COMMENT '尺码代码',
  `sku` varchar(30) NOT NULL DEFAULT '' COMMENT 'sku',
  `barcode` varchar(30) NOT NULL DEFAULT '' COMMENT '条码',
  `goods_price` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '商品单价(实际售价)',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '数量',
  `goods_weigh` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '商品重量',
  `avg_money` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '均摊金额',
  `platform_spec` varchar(255) NOT NULL DEFAULT '' COMMENT '平台规格',
  `is_gift` tinyint(4) NOT NULL DEFAULT '0' COMMENT '礼品标识：0-普通商品1-礼品',
  `sale_mode` varchar(10) NOT NULL DEFAULT 'stock' COMMENT '销售模式：现货stock，预售presale',
  `delivery_mode` varchar(10) NOT NULL DEFAULT 'days' COMMENT 'days承诺发货天数 ; time预售发货时间',
  `delivery_days_or_time` varchar(20) NOT NULL COMMENT '存放承诺发货期或预售发货时间',
  `plan_send_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'SKU计划发货时间',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `pic_path` varchar(200) DEFAULT '' COMMENT '商品图片地址',
  PRIMARY KEY (`sell_record_detail_id`),
  UNIQUE KEY `idxu_key` (`sell_record_code`,`deal_code`,`sku`) USING BTREE,
  KEY `index_sku` (`sku`) USING BTREE,
  KEY `lastchanged` (`lastchanged`) USING BTREE,
  KEY `sub_deal_code` (`sub_deal_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=10580 DEFAULT CHARSET=utf8 COMMENT='通知配货订单明细表';

",
    
"insert into oms_sell_record_notice (sell_record_id,sell_record_code,deal_code,deal_code_list,sale_channel_code
,alipay_no,is_handwork,store_code,shop_code,user_code,pay_type,pay_code,pay_status,pay_time,customer_code
,buyer_name,receiver_name,receiver_country,receiver_province,receiver_city,receiver_district,receiver_street
,receiver_address,receiver_addr,receiver_zip_code,receiver_mobile,receiver_phone,receiver_email,express_code
,express_no,express_data,plan_send_time,goods_num,sku_num,goods_weigh,real_weigh,weigh_express_money
,weigh_time,buyer_remark,seller_remark,seller_flag,order_remark,store_remark,order_money,goods_money
,express_money,delivery_money,payable_money,paid_money,invoice_type,invoice_title,invoice_content,invoice_money
,invoice_status,create_time,record_time,record_date,is_lock,is_change_record,change_record_from,is_split
,is_split_new,split_order,split_new_orders,is_combine,is_combine_new,combine_orders,combine_new_order
,is_copy,is_copy_from,is_wap,is_jhs,point_fee,alipay_point_fee,coupon_fee,yfx_fee,change_sell_record
,is_print_sellrecord,is_print_express,is_fenxiao,is_notice_time,check_time,lastchanged,is_buyer_remark
,is_seller_remark,is_rush,confirm_person,notice_person,fenxiao_id,fenxiao_name) select sell_record_id
,sell_record_code,deal_code,deal_code_list,sale_channel_code,alipay_no,is_handwork,store_code,shop_code
,user_code,pay_type,pay_code,pay_status,pay_time,customer_code,buyer_name,receiver_name,receiver_country
,receiver_province,receiver_city,receiver_district,receiver_street,receiver_address,receiver_addr,receiver_zip_code
,receiver_mobile,receiver_phone,receiver_email,express_code,express_no,express_data,plan_send_time,goods_num
,sku_num,goods_weigh,real_weigh,weigh_express_money,weigh_time,buyer_remark,seller_remark,seller_flag
,order_remark,store_remark,order_money,goods_money,express_money,delivery_money,payable_money,paid_money
,invoice_type,invoice_title,invoice_content,invoice_money,invoice_status,create_time,record_time,record_date
,is_lock,is_change_record,change_record_from,is_split,is_split_new,split_order,split_new_orders,is_combine
,is_combine_new,combine_orders,combine_new_order,is_copy,is_copy_from,is_wap,is_jhs,point_fee,alipay_point_fee
,coupon_fee,yfx_fee,change_sell_record,is_print_sellrecord,is_print_express,is_fenxiao,is_notice_time
,check_time,lastchanged,is_buyer_remark,is_seller_remark,is_rush,confirm_person,notice_person,fenxiao_id
,fenxiao_name from oms_sell_record   where shipping_status=1 AND order_status<>3 AND waves_record_id=0;",

"insert into oms_sell_record_notice_detail
 (sell_record_detail_id,sell_record_code,deal_code,sub_deal_code,goods_code,spec1_code,spec2_code,sku
,barcode,goods_price,num,goods_weigh,avg_money,platform_spec,is_gift,sale_mode,delivery_mode,delivery_days_or_time
,plan_send_time,lastchanged,pic_path) select sell_record_detail_id,sell_record_code,deal_code,sub_deal_code
,goods_code,spec1_code,spec2_code,sku,barcode,goods_price,num,goods_weigh,avg_money,platform_spec,is_gift
,sale_mode,delivery_mode,delivery_days_or_time,plan_send_time,lastchanged,pic_path from oms_sell_record_detail
   WHERE sell_record_code IN (
	select sell_record_code from oms_sell_record   where shipping_status=1 AND order_status<>3 AND waves_record_id=0
   );",



);



$u['FSF-1685'] = array(
	"
CREATE TABLE `oms_waves_strategy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_code` varchar(128) DEFAULT '0',
  `code` varchar(128) DEFAULT '',
  `name` varchar(255) DEFAULT '',
  `condition` text,
  `is_sys` tinyint(3) DEFAULT '0' COMMENT '是否系统策略',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;",
	"INSERT INTO `sys_action` VALUES ('7010104','7010001','url','波次生成策略','oms/sell_record_notice/do_list','9','1','0','1','0');
	"
);

$u['FSF-1668'] = array("delete from sys_user_pref where iid = 'oms/sell_record_question_list' and type='custom_table_field'");
$u['FSF-1693'] = array(
		"insert into `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`)
			values('','erp_quantity_sync','erp','只拉取店铺销售的商品库存','radio','[\"关闭\",\"开启\"]','0','1','1-开启 0-关闭','2015-09-23 13:17:36','拉取ERP库存，只拉取平台有销售的商品库存，不销售的商品库存不予拉取，默认关闭');",
		);
$u['FSF-1691'] = array(
		"CREATE TABLE `api_data_monitor` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `shop_code` varchar(50) NOT NULL COMMENT '店铺代码',
		  `action_code` varchar(50) NOT NULL COMMENT '行为代码，比如订单下载等',
		  `start_time` varchar(20) NOT NULL COMMENT '开始时间',
		  `end_time` varchar(20) NOT NULL COMMENT '结束时间',
		  `count_for_online` int(11) NOT NULL DEFAULT '0' COMMENT '线上数量',
		  `count_for_offine` int(11) NOT NULL DEFAULT '0' COMMENT '本地数量',
		  `msg` varchar(500) DEFAULT NULL COMMENT '备注信息',
		  PRIMARY KEY (`id`),
		  KEY `key_action_code` (`shop_code`,`action_code`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
		);
$u['FSF-1672-add'] = array(
	"update sys_schedule set loop_time=3600 ,plan_exec_time=0 where code='create_pur_advise_data' ;",
	"TRUNCATE TABLE  pur_advide_inv;",
	"ALTER TABLE `pur_advide_inv` ADD UNIQUE INDEX `_key` (`store_code`, `sku`) USING BTREE ;",
);
