-- ----------------------------
-- Table structure for api_jingdong_goods
-- ----------------------------
DROP TABLE IF EXISTS `api_jingdong_goods`;
CREATE TABLE `api_jingdong_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ware_id` varchar(20) NOT NULL COMMENT '京东内部商品ID',
  `vender_id` varchar(10) DEFAULT NULL COMMENT '商家id',
  `title` varchar(255) DEFAULT NULL COMMENT '商品名称',
  `ware_status` varchar(20) DEFAULT NULL COMMENT '商品状态:NEVER_UP:从未上架, CUSTORMER_DOWN:自主下架, SYSTEM_DOWN:系统下架, ON_SALE:在售, AUDIT_AWAIT: 待审核, AUDIT_FAIL: 审核不通过',
  `jd_price` varchar(10) DEFAULT NULL COMMENT '京东价,精确到2位小数，单位:元',
  `stock_num` varchar(10) DEFAULT NULL COMMENT '商品总库存',
  `online_time` varchar(20) DEFAULT NULL COMMENT '上架时间',
  `offline_time` varchar(20) DEFAULT NULL COMMENT '下架时间',
  `shop_code` varchar(30) NOT NULL COMMENT 'efast商店代码',
  `modified` datetime DEFAULT NULL COMMENT 'WARE_WARE修改时间, 时间格式：yyyy-MM-ddHH:mm:ss',
  `status` varchar(30) DEFAULT '' COMMENT '状态：Delete:删除, Invalid:无效, Valid :有效',
  `spu_id` varchar(50) DEFAULT NULL COMMENT 'spu ID',
  `cid` varchar(50) DEFAULT NULL COMMENT '分类ID 三级类目ID',
  `shop_id` varchar(10) DEFAULT NULL COMMENT '京东店铺ID',
  `item_num` varchar(30) DEFAULT NULL COMMENT '外部商品编号，对应商家后台"货号"',
  `upc_code` varchar(50) DEFAULT NULL COMMENT 'UPC编码',
  `transport_id` varchar(30) DEFAULT NULL COMMENT '运费模板',
  `attributes` varchar(200) DEFAULT NULL COMMENT '可选属性',
  `cost_price` varchar(10) DEFAULT NULL COMMENT '进货价, 精确到2位小数，单位:元',
  `market_price` varchar(10) DEFAULT NULL COMMENT '市场价, 精确到2位小数，单位:元',
  `logo` varchar(200) DEFAULT NULL COMMENT '商品的主图',
  `creator` varchar(20) DEFAULT NULL COMMENT '录入人',
  `weight` varchar(20) DEFAULT NULL COMMENT '重量,单位:公斤',
  `created` varchar(20) DEFAULT NULL COMMENT 'WARE_WARE创建时间时间格式：yyyy-MM-ddHH:mm:ss',
  `outer_id` varchar(50) DEFAULT NULL COMMENT '外部id,商家设置的外部id',
  `first_insert_time` datetime DEFAULT NULL COMMENT '平台第一次数据下载时间',
  `last_update_time` datetime DEFAULT NULL COMMENT '平台最后一次更新时间',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '数据变更时间戳',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ware_id_sd` (`ware_id`,`shop_code`),
  KEY `ware_id` (`ware_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

