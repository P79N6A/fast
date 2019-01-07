-- ----------------------------
-- Table structure for api_yihaodian_goods_general
-- ----------------------------
DROP TABLE IF EXISTS `api_yihaodian_goods_general`;
CREATE TABLE `api_yihaodian_goods_general` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `productId` varchar(50) DEFAULT NULL COMMENT '产品ID',
  `productCode` varchar(50) DEFAULT NULL COMMENT '商家产品编码',
  `productCname` varchar(255) DEFAULT NULL COMMENT '产品中文名称',
  `ean13` varchar(20) DEFAULT NULL COMMENT '产品条形码',
  `categoryId` varchar(20) DEFAULT NULL COMMENT '产品类目ID',
  `outerId` varchar(20) DEFAULT NULL COMMENT '外部产品ID',
  `canShow` varchar(10) DEFAULT NULL COMMENT '是否可见(强制上/下架),1是0否',
  `canSale` varchar(10) DEFAULT NULL COMMENT '上下架状态0：下架，1：上架',
  `stockStatus` varchar(10) DEFAULT NULL COMMENT '产品库存状态',
  `stock` text DEFAULT NULL COMMENT '产品库存json',
  `verifyFlg` varchar(10) DEFAULT NULL COMMENT '产品审核状态:1.新增未审核;2.编辑待审核;3.审核未通过;4.审核通过;5.图片审核失败;6.文描审核失败;7:生码中(第一次审核中)',
  `isDupAudit` varchar(30) DEFAULT NULL COMMENT '是否二次审核0：非二次审核；1：是二次审核',
  `prodImg` varchar(255) DEFAULT NULL COMMENT '图片信息列表',
  `prodDetailUrl` varchar(255) DEFAULT NULL COMMENT '前台商品详情页链接（正式产品才会有）',
  `brandId` varchar(20) DEFAULT NULL COMMENT '品牌Id',
  `merchantCategoryId` varchar(250) DEFAULT NULL COMMENT '商家产品类别。多个类别用逗号分隔',
  `productDesc` text DEFAULT NULL COMMENT '描述信息json',
  `attributes` text DEFAULT NULL COMMENT '属性json',
  `price` text DEFAULT NULL COMMENT '价格json',
  `shop_code` varchar(30) NOT NULL COMMENT 'efast商店代码',
  `first_insert_time` datetime DEFAULT NULL COMMENT '平台第一次数据下载时间',
  `last_update_time` datetime DEFAULT NULL COMMENT '平台最后一次更新时间',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '数据变更时间戳',
  PRIMARY KEY (`id`),
  UNIQUE KEY `productiId_sd` (`productId`,`shop_code`),
  KEY `productId` (`productId`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

