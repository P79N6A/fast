<?php
$u = array();

$u['FSF-1497'] = array(
		"ALTER TABLE `oms_sell_record_detail`
ADD COLUMN `api_refund_num`  int(11) NULL DEFAULT 0 COMMENT 'api退货数量' AFTER `is_delete`,
ADD COLUMN `api_refund_desc`  varchar(255) NULL DEFAULT '' COMMENT 'api退货描述' AFTER `api_refund_num`;
",
);

$u['FSF-1507'] = array(
         "CREATE TABLE `goods_inv_api_sync_log` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `sku` varchar(128) DEFAULT '',
      `barcode` varchar(128) DEFAULT '',
      `shop_code` varchar(128) DEFAULT '',
      `num` int(11) DEFAULT '0',
      `type` int(11) DEFAULT '0',
      `desc` varchar(255) DEFAULT '',
      `store_code` varchar(255) DEFAULT '',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

   
 );   

/**
 * 增加运营>运营罗盘>运营分析报表
 * by zdd 2015.07.16
 */
$u['FSF-1510'] = array(
	"INSERT INTO `sys_action` VALUES ('3030000', '3000000', 'group', '运营罗盘', 'operate-manage', '3', '1', '0', '1','0');",
	"INSERT INTO `sys_action` VALUES ('3030100', '3030000', 'url', '运营分析', 'crm/operate_fx/do_list', '1', '1', '0', '1','0');",
); 
  $u['FSF-1507'] = array(
     "DROP TABLE IF EXISTS `goods_inv_api_sync_log`;", 
      "CREATE TABLE `goods_inv_api_sync_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `sku` varchar(128) DEFAULT '',
  `barcode` varchar(128) DEFAULT '',
  `shop_code` varchar(128) DEFAULT '',
  `num` int(11) DEFAULT '0',
  `type` int(11) DEFAULT '0',
  `desc` varchar(255) DEFAULT '',
  `store_code` varchar(255) DEFAULT '',
  `inv_update_time` datetime DEFAULT NULL,
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='库存计算日志表';
",        
 );  
  /**
   * 折800表重新创建
   * by zdd 2015.07.20
   * @var unknown_type
   */
  $u['FSF-1526'] = array(
  		"DROP TABLE IF EXISTS `api_zhe800_goods`;",
  		"DROP TABLE IF EXISTS `api_zhe800_sku`;",
  		"DROP TABLE IF EXISTS `api_zhe800_trade`;",
  		"DROP TABLE IF EXISTS `api_zhe800_order`;",
  		"CREATE TABLE `api_zhe800_trade` (
		  `trade_id` int(11) NOT NULL AUTO_INCREMENT,
		  `id` varchar(30) NOT NULL DEFAULT '0' COMMENT '订单编号(兼容折800的字段）',
		  `from_source` tinyint(1) NOT NULL DEFAULT '1' COMMENT '订单来源 1-PC(默认)2-无线',
		  `url` varchar(100) NOT NULL DEFAULT '' COMMENT '订单-链接',
		  `seller_id` int(11) NOT NULL DEFAULT '0' COMMENT '卖家ID',
		  `seller_nickname` varchar(30) NOT NULL DEFAULT '' COMMENT '卖家昵称（商家眤称）',
		  `order_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总金额',
		  `goods_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '商品总金额',
		  `discount_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '商家优惠金额',
		  `postage` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '邮费(订单运费)',
		  `oos` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否超卖 0-正常 1-超卖',
		  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '订单-状态 1-待付款 2-等待发货 3-已发货(待确认收货) 5-交易成功 7-交易关闭, 99-等待第三方支付平台返回支付结果',
		  `deliver_time_limit` tinyint(1) NOT NULL DEFAULT '1' COMMENT '订单-发货时长(1天, 2天, 3天)',
		  `seller_comment` varchar(255) NOT NULL DEFAULT '' COMMENT '卖家备注(商家备注)',
		  `buyer_comment` varchar(255) NOT NULL DEFAULT '' COMMENT '买家留言(买家备注)',
		  `created_at` datetime NOT NULL COMMENT '拍下时间(下单时间)',
		  `close_time` datetime DEFAULT NULL COMMENT '订单关闭时间（原因：买家取消订单/超过未⽀支付/因为售后完成）',
		  `updated_at` datetime NOT NULL COMMENT '订单更新时间',
		  `express_time` datetime NOT NULL COMMENT '发货时间 PENDING',
		  `close_reason` varchar(255) NOT NULL COMMENT '关闭原因',
		  `pay_time` datetime NOT NULL COMMENT '付款时间',
		  `express_no` varchar(30) DEFAULT '' COMMENT '运单号',
		  `express_company` varchar(100) NOT NULL DEFAULT '' COMMENT '快递公司',
		  `nickname` varchar(100) NOT NULL DEFAULT '',
		  `seller_comment_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '卖家备注类型 0 无备注； 1-红色，2-黄色，3-绿色，4-蓝色，5-紫色',
		  `invoice_type` varchar(30) NOT NULL DEFAULT '' COMMENT '发票信息-发票类型',
		  `invoice_content` varchar(100) NOT NULL DEFAULT '' COMMENT '发票信息-发票内容',
		  `invoice_title` varchar(100) NOT NULL DEFAULT '' COMMENT '发票信息-发票抬头',
		  `receiver_name` varchar(30) NOT NULL DEFAULT '' COMMENT '收货人-姓名',
		  `receiver_province` varchar(30) NOT NULL DEFAULT '' COMMENT '收货人-地区-省',
		  `receiver_city` varchar(30) NOT NULL DEFAULT '' COMMENT '收货人-地区-市',
		  `receiver_county` varchar(30) NOT NULL DEFAULT '' COMMENT '收货人-地区-区县',
		  `receiver_address` varchar(50) NOT NULL DEFAULT '' COMMENT '收货人-地址(无省市区)',
		  `receiver_phone` varchar(30) NOT NULL DEFAULT '' COMMENT '收货人-手机',
		  `receiver_tel` varchar(30) NOT NULL DEFAULT '' COMMENT '收货人-电话',
		  `receiver_postCode` varchar(20) NOT NULL DEFAULT '' COMMENT '收货人-邮编',
		  PRIMARY KEY (`trade_id`),
		  UNIQUE KEY `id` (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8",
  		"CREATE TABLE `api_zhe800_order` (
		  `zhe800_order_id` int(11) NOT NULL AUTO_INCREMENT,
		  `order_id` varchar(30) NOT NULL DEFAULT '0' COMMENT '订单编号(兼容折800的字段）',
		  `id` varchar(30) NOT NULL DEFAULT '0' COMMENT 'zhe800商品id',
		  `count` int(11) NOT NULL DEFAULT '0' COMMENT '数量',
		  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '商品标题',
		  `short_name` varchar(100) NOT NULL DEFAULT '0' COMMENT '商品简称',
		  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '单价',
		  `goods_earning` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '实收⾦金额',
		  `num` int(11) NOT NULL DEFAULT '0' COMMENT '货号',
		  `seller_no` varchar(50) NOT NULL DEFAULT '0.00' COMMENT 'SKU商家编码',
		  `score` int(11) NOT NULL DEFAULT '0' COMMENT '赠送积分',
		  `postage` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '邮费',
		  `shelf` varchar(50) NOT NULL DEFAULT '' COMMENT '货位',
		  `image` varchar(100) NOT NULL DEFAULT '' COMMENT '图片',
		  `url` varchar(100) NOT NULL DEFAULT '' COMMENT '商品-链接',
		  `sku_num` varchar(50) NOT NULL DEFAULT '' COMMENT '折800sku标⽰示',
		  `refund_id` int(11) NOT NULL DEFAULT '0' COMMENT '最近售后ID',
		  `refund_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '最近售后状态 为空说明不存在售后 1.等待卖家处理,2.买家已退货，等待卖家确认收货 3.同意退货，等待买家处理4.拒绝退款，等待买家处理 5.退款成功 6.售后关闭',
		  `refund_url` varchar(100) NOT NULL DEFAULT '' COMMENT '最近售后链接',
		  `complain_id` int(11) NOT NULL DEFAULT '0' COMMENT '最近维权ID',
		  `complain_status` int(255) NOT NULL DEFAULT '0' COMMENT '最近维权状态 为空说明不存在维权 1.等待折800处理 4.等待双方提交证据 5.客服给出了结方案，等待审核 6.了结方案被拒绝 97.方案执行中(退货退款还未退货的状态) 98.方案执行中 99.维权关闭',
		  `complain_url` varchar(0) NOT NULL DEFAULT '' COMMENT '最近维权链接',
		  `sku_id` varchar(30) NOT NULL,
		  PRIMARY KEY (`zhe800_order_id`),
		  UNIQUE KEY `order_id` (`order_id`,`sku_id`) USING BTREE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8",
  		"CREATE TABLE `api_zhe800_goods` (
		  `goods_id` int(11) NOT NULL AUTO_INCREMENT,
		  `shop_code` varchar(20) NOT NULL,
		  `id` varchar(50) NOT NULL DEFAULT '0' COMMENT '商品ID',
		  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '商品名称',
		  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '商品副标题',
		  `short_name` varchar(50) NOT NULL DEFAULT '' COMMENT '商品短标题',
		  `stock` int(11) NOT NULL DEFAULT '0' COMMENT '商品库存',
		  `sales_count` int(11) NOT NULL DEFAULT '0' COMMENT '商品销售',
		  `org_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '商品原价',
		  `cur_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '商品现价',
		  `num` varchar(50) NOT NULL DEFAULT '' COMMENT '商品货号',
		  `image` varchar(255) NOT NULL DEFAULT '' COMMENT '商品主图',
		  `shelf` varchar(50) NOT NULL DEFAULT '' COMMENT '库位',
		  `place_of_dispatch` varchar(50) NOT NULL DEFAULT '' COMMENT '商品发货地',
		  PRIMARY KEY (`goods_id`),
		  UNIQUE KEY `id` (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8",
  		"CREATE TABLE `api_zhe800_sku` (
		  `api_zhe800_sku_id` int(11) NOT NULL AUTO_INCREMENT,
		  `shop_code` varchar(30) NOT NULL DEFAULT '' COMMENT '店铺代码',
		  `id` varchar(50) NOT NULL DEFAULT '0',
		  `sku_num` varchar(60) NOT NULL DEFAULT '' COMMENT 'sku_num',
		  `sku_desc` varchar(60) NOT NULL DEFAULT '' COMMENT 'sku_desc',
		  `stock` varchar(60) NOT NULL DEFAULT '' COMMENT '库存',
		  `cur_price` varchar(60) NOT NULL DEFAULT '' COMMENT '当前价格',
		  `org_price` varchar(60) NOT NULL DEFAULT '' COMMENT '原始价格',
		  `shelf` varchar(60) NOT NULL DEFAULT '' COMMENT '库位',
		  `seller_no` varchar(60) NOT NULL DEFAULT '' COMMENT '商家编码',
		  `sku_id` varchar(32) NOT NULL,
		  PRIMARY KEY (`api_zhe800_sku_id`),
		  UNIQUE KEY `outer_id` (`id`,`sku_id`) USING BTREE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8",
  	);
  	
  	/**
  	 * 修改运费字段长度
  	 * by zdd 2015.07.20
  	 */
  	$u['FSF-1527'] = array(
  			"ALTER TABLE api_order change express_money `express_money` float(7,2) DEFAULT '0.00' COMMENT '平台运费'",
  	);
  	/**
  	 * api_taobao_alipay增加字段
  	 * by zdd 2015.07.21
  	 */
  	$u['FSF-1528'] = array(
  			"alter table api_taobao_alipay add COLUMN account_item varchar(20) default '' COMMENT '会计科目';",
  			"alter table api_taobao_alipay add index idx_account_item(account_item);",
  			);
  	
    
/**
 * 零售结算单等表的精度修改
 * updated time: 2015.07.21
 */
$u['FSF-1505'] = array(
    "
    ALTER TABLE `oms_sell_settlement_record`   
	  CHANGE `point_fee` `point_fee` DECIMAL(10,3) DEFAULT 0.000  NOT NULL   COMMENT '积分抵用金额',
	  CHANGE `express_money` `express_money` DECIMAL(10,3) DEFAULT 0.000  NOT NULL   COMMENT '运费',
	  CHANGE `commission_fee` `commission_fee` DECIMAL(10,3) DEFAULT 0.000  NOT NULL   COMMENT '交易佣金',
	  CHANGE `compensate_money` `compensate_money` DECIMAL(20,3) DEFAULT 0.000  NOT NULL   COMMENT '赔付金额',
	  CHANGE `je` `je` DECIMAL(10,3) DEFAULT 0.000  NOT NULL   COMMENT '金额';
    ",
    "
    ALTER TABLE `oms_sell_settlement`   
	  CHANGE `total_fee` `total_fee` DECIMAL(10,3) DEFAULT 0.000  NOT NULL   COMMENT '总应收款',
	  CHANGE `express_money` `express_money` DECIMAL(10,3) DEFAULT 0.000  NOT NULL   COMMENT '运费',
	  CHANGE `point_fee` `point_fee` DECIMAL(10,3) NOT NULL   COMMENT '积分抵用金额',
	  CHANGE `ali_in_amount` `ali_in_amount` DECIMAL(10,3) DEFAULT 0.000  NOT NULL   COMMENT '收入金额',
	  CHANGE `ali_out_amount` `ali_out_amount` DECIMAL(10,3) DEFAULT 0.000  NOT NULL   COMMENT '支出金额',
	  CHANGE `commission_fee` `commission_fee` DECIMAL(10,3) DEFAULT 0.000  NOT NULL   COMMENT '交易佣金',
	  CHANGE `sell_record_avg_money` `sell_record_avg_money` DECIMAL(10,3) DEFAULT 0.000  NOT NULL   COMMENT '商品均摊总金额',
	  CHANGE `sell_return_avg_money` `sell_return_avg_money` DECIMAL(10,3) DEFAULT 0.000  NOT NULL   COMMENT '商品退货均摊总金额',
	  CHANGE `compensate_money` `compensate_money` DECIMAL(20,3) DEFAULT 0.000  NOT NULL   COMMENT '补差金额';
    ",
    "
    ALTER TABLE `oms_sell_settlement_detail`   
		CHANGE `avg_money` `avg_money` DECIMAL(10,3) DEFAULT 0.000  NOT NULL   COMMENT '均摊金额';
    "
);


