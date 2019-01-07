-- ----------------------------
-- Table structure for api_yihaodian_goods
-- ----------------------------
DROP TABLE IF EXISTS `api_yihaodian_goods`;
CREATE TABLE `api_yihaodian_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `productId` varchar(50) DEFAULT NULL COMMENT '产品ID',
  `productCode` varchar(50) DEFAULT NULL COMMENT '商家产品编码',
  `productCname` varchar(255) DEFAULT NULL COMMENT '产品中文名称',
  `outerId` varchar(20) DEFAULT NULL COMMENT '外部产品ID',
  `canShow` varchar(10) DEFAULT NULL COMMENT '是否可见(强制上/下架),1是0否',
  `canSale` varchar(10) DEFAULT NULL COMMENT '上下架状态0：下架，1：上架',
  `ean13` varchar(20) DEFAULT NULL COMMENT '产品条形码',
  `categoryId` varchar(20) DEFAULT NULL COMMENT '产品类目ID',
  `isDupAudit` varchar(30) DEFAULT NULL COMMENT '是否二次审核0：非二次审核；1：是二次审核',
  `prodDetailUrl` varchar(255) DEFAULT NULL COMMENT '前台商品详情页链接（正式产品才会有）',
  `brandId` varchar(20) DEFAULT NULL COMMENT '品牌Id',
  `merchantCategoryId` varchar(250) DEFAULT NULL COMMENT '商家产品类别。多个类别用逗号分隔',
  `mainProductId` varchar(50) DEFAULT NULL COMMENT '主产品Id',
  `mainOuterId` varchar(50) DEFAULT NULL COMMENT '主产品外部编码',
  `mainPmInfoId` varchar(50) DEFAULT NULL COMMENT '官方未说明是什么',
  `pmInfoId` varchar(50) DEFAULT NULL COMMENT '官方未说明是什么',
  `productDesc` text DEFAULT NULL COMMENT '描述信息',
  `attributes` text DEFAULT NULL COMMENT '属性(颜色尺码)',
  `shop_code` varchar(30) NOT NULL COMMENT 'efast商店代码',
  `first_insert_time` datetime DEFAULT NULL COMMENT '平台第一次数据下载时间',
  `last_update_time` datetime DEFAULT NULL COMMENT '平台最后一次更新时间',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '数据变更时间戳',
  PRIMARY KEY (`id`),
  UNIQUE KEY `productiId_sd` (`productId`,`shop_code`),
  KEY `productId` (`productId`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

