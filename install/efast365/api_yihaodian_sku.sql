-- ----------------------------
-- Table structure for api_yihaodian_sku
-- ----------------------------
DROP TABLE IF EXISTS `api_yihaodian_sku`;
CREATE TABLE `api_yihaodian_sku` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_productId` varchar(50) DEFAULT NULL COMMENT 'api_yihaodian_goods productId字段',
  `productId` varchar(50) DEFAULT NULL COMMENT '产品ID',
  `productCode` varchar(50) DEFAULT NULL COMMENT '商家产品编码',
  `productCname` varchar(255) DEFAULT NULL COMMENT '产品中文名称',
  `ean13` varchar(20) DEFAULT NULL COMMENT '产品条形码',
  `status` varchar(10) DEFAULT NULL COMMENT 'sku状态: 有效-Valid 无效-Invalid 删除-Delete',
  `categoryId` varchar(20) DEFAULT NULL COMMENT '产品类目ID',
  `canSale` varchar(10) DEFAULT NULL COMMENT '上下架状态0：下架，1：上架',
  `stockStatus` varchar(10) DEFAULT NULL COMMENT '产品库存状态',
  `outerId` varchar(20) DEFAULT NULL COMMENT '外部产品ID',
  `canShow` varchar(10) DEFAULT NULL COMMENT '是否可见(强制上/下架),1是0否',
  `isMainProduct` varchar(20) DEFAULT NULL COMMENT '是否为主打产品（1：是、0：否）',
  `verifyFlg` varchar(20) DEFAULT NULL COMMENT '产品审核状态:1.新增未审核;2.编辑待审核;3.审核未通过;4.审核通过;5.图片审核失败;6.文描审核失败;7:生码中(第一次审核中)',
  `isDupAudit` varchar(20) DEFAULT NULL COMMENT '是否二次审核0：非二次审核；1：是二次审核',
  `allWareHouseStocList` text DEFAULT NULL COMMENT '所有仓库库存信息',
  `prodImg` varchar(255) DEFAULT NULL COMMENT '图片信息列表（逗号分隔，图片id、图片URL、主图标识之间用竖线分隔；其中1：表示主图，0：表示非主图）',
  `prodDetailUrl` varchar(255) DEFAULT NULL COMMENT '前台商品详情页链接（正式产品才会有）',
  `brandId` varchar(50) DEFAULT NULL COMMENT '品牌Id',
  `merchantCategoryId` varchar(200) DEFAULT NULL COMMENT '商家产品类别。多个类别用逗号分隔',
  `productType` varchar(20) DEFAULT NULL COMMENT '产品类型',
  `pmInfoId` varchar(50) DEFAULT NULL COMMENT '官方未说明是什么',
  `prices` text DEFAULT NULL COMMENT '价格信息（一系列价格）',
  `attributes` text DEFAULT NULL COMMENT '属性(颜色尺码)',
  `shop_code` varchar(30) NOT NULL COMMENT 'efast商店代码',
  `first_insert_time` datetime DEFAULT NULL COMMENT '平台第一次数据下载时间',
  `last_update_time` datetime DEFAULT NULL COMMENT '平台最后一次更新时间',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '数据变更时间戳',
  PRIMARY KEY (`id`),
  UNIQUE KEY `productId_sd` (`productId`,`shop_code`),
  KEY `productId` (`productId`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

