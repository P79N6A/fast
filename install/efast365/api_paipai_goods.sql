-- ----------------------------
-- Table structure for api_paipai_goods 拍拍商品信息
-- ----------------------------
DROP TABLE IF EXISTS `api_paipai_goods`;
CREATE TABLE `api_paipai_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_code` varchar(30) NOT NULL COMMENT 'efast商店代码',
  `itemCode` varchar(50) DEFAULT NULL COMMENT '商品编码，商品在拍拍上标识的唯一编码',
  `itemLocalCode` varchar(50) DEFAULT NULL COMMENT '商家对商品的编码，商家自行保证该编码的唯一性，否则根据该编码查询可能出错',
  `itemName` varchar(50) DEFAULT NULL COMMENT '商品名称',
  `itemState` varchar(20) DEFAULT NULL COMMENT '1.出售中， 2.仓库中，组合状态包括：我下架的+定期下架的+定时上架+从未上架的 3.我下架的 4.定期下架的 5.等待上架 6.定时上架 7.从未上架 8.售完的 9.等待处理 10.删除的商品',
  `stateDesc` varchar(50) DEFAULT NULL COMMENT '商品状态的说明',
  `relatedItems` varchar(100) DEFAULT NULL COMMENT '推荐搭配商品编码，多个以‘|’号隔开',
  `itemProperty` varchar(255) DEFAULT NULL COMMENT '商品属性',
  `properties` varchar(255) DEFAULT NULL COMMENT '商品的属性组合串格式如：key1_value1|key1_value1|key1_value1|..... 例如：13_1|18_1|422_1|....40=团购标识6=推荐位商品26=今日特价',
  `stockCount` int(5) DEFAULT '0' COMMENT '商品库存总数量',
  `itemPrice` varchar(20) DEFAULT '0' COMMENT '商品销售单价',
  `marketPrice` varchar(20) DEFAULT '0' COMMENT '商品的市场价格',
  `expressPrice` varchar(20) DEFAULT '0' COMMENT '商品的快递费用',
  `emsPrice` varchar(20) DEFAULT '0' COMMENT '商品的EMS费用',
  `mailPrice` varchar(20) DEFAULT '0' COMMENT '商品的邮寄费用',
  `categoryId` varchar(20) DEFAULT '' COMMENT '商品的种类id（店铺自定义分类',
  `classId` varchar(20) DEFAULT '' COMMENT '商品的类目id',
  `cityId` varchar(20) DEFAULT '' COMMENT '城市id',
  `provinceId` varchar(20) DEFAULT '' COMMENT '省份id',
  `countryId` varchar(20) DEFAULT '' COMMENT '国家id',
  `freeReturn` tinyint(1) DEFAULT '0' COMMENT '是否7天免邮包退：1=是、0=否， 默认为0',
  `attr` varchar(100) DEFAULT '' COMMENT '原有商品的属性串',
  `attr2` varchar(100) DEFAULT '' COMMENT '新格式商品的属性串,如：version=1,1:1|2:2|a:a|b:1,2,4^a:你好',
  `customAttr` varchar(255) DEFAULT '' COMMENT '商品的商家自定义属性',
  `parsedAttrList` text COMMENT '商品的商家自定义属性说明列表(json)',
  `extendList` text COMMENT '商品扩展属性列表(json)',
  `buyLimit` int(5) DEFAULT '0' COMMENT '购买时的限制数量',
  `detailInfo` varchar(255) DEFAULT '' COMMENT '商品的详情内容',
  `freightId` int(5) DEFAULT '0' COMMENT '商品的运费模板id',
  `guarantee14Days` tinyint(1) DEFAULT '0' COMMENT '是否14天包换 1是 0否',
  `guarantee7Days` tinyint(1) DEFAULT '0' COMMENT '是否7天包退 1是 0否',
  `guaranteeCompensation` tinyint(1) DEFAULT '0' COMMENT '是否假一赔三 1是 0否',
  `guaranteeRepair` tinyint(1) DEFAULT '0' COMMENT '是否提供保修服务 1是 0否',
  `invoiceItem` tinyint(1) DEFAULT '0' COMMENT '是否提供发票 1是 0否',
  `createTime` datetime DEFAULT NULL COMMENT '发布时间',
  `lastModifyTime` datetime DEFAULT NULL COMMENT '最后修改时间',
  `lastToSaleTime` datetime DEFAULT NULL COMMENT '上次上架时间',
  `lastToStoreTime` datetime DEFAULT NULL COMMENT '上次下架时间',
  `payType` varchar(50) DEFAULT '' COMMENT '支持的付款方式 (发货方式)，以“,”隔开PT_MONEY=款到发货PT_COD_OLD=货到付款PT_BAOBEI=见宝贝描述PT_TENPAY=支持财付通方式(暂时不用)PT_COD=货到付款',
  `picLink` varchar(100) DEFAULT '' COMMENT '商品图片连接',
  `qqvipDiscount` int(1) DEFAULT '0' COMMENT 'QQ会员折扣 万分之几',
  `qqvipItem` tinyint(1) DEFAULT '0' COMMENT '是否QQ会员店商品',
  `recommendItem` tinyint(1) DEFAULT '0' COMMENT '是否推荐商品 1是 0否',
  `regionInfo` varchar(20) DEFAULT '' COMMENT '地区信息',
  `reloadCount` int(1) DEFAULT '0' COMMENT '重上架次数',
  `secondHandItem` tinyint(1) DEFAULT '0' COMMENT '是否为二手商品',
  `sellerPayFreight` int(1) DEFAULT '0' COMMENT '卖家或者买家承担运费的情况1 卖家承担运费2 买家承担运费3 同城交易，无需运费大于或等于10 买家承担运费，表示支持运费模板，该值即为运费模板ID',
  `sellerName` varchar(20) DEFAULT '' COMMENT '店铺名称',
  `sellerUin` varchar(20) DEFAULT '' COMMENT '卖家QQ号',
  `theme` varchar(20) DEFAULT '' COMMENT '商品详情页主题',
  `themeId` varchar(20) DEFAULT '' COMMENT '商品详情模版ID',
  `validDuration` varchar(20) DEFAULT '' COMMENT '商品上架后卖N天后下架（单位以秒计，目前系统N只支持7天和14天两个值）',
  `visitCount` int(10) DEFAULT '0' COMMENT '访问的次数',
  `soldCount` int(5) DEFAULT '0' COMMENT '近期销售的商品数量',
  `soldTotalCount` int(5) DEFAULT '0' COMMENT '销售的商品数量',
  `soldTimes` int(5) DEFAULT '0' COMMENT '近期销售的订单次数',
  `soldTotalTimes` int(5) DEFAULT '0' COMMENT '销售订单的总次数',
  `buyNum` int(5) DEFAULT '0' COMMENT '近期购买商品数量',
  `totalBuyNum` int(5) DEFAULT '0' COMMENT '购买商品的总数量',
  `buyCount` int(5) DEFAULT '0' COMMENT '近期下单的订单次数',
  `totalBuyCount` int(5) DEFAULT '0' COMMENT '下单的订单总次数',
  `weight` varchar(20) DEFAULT '0' COMMENT '商品的重量',
  `windowItem` tinyint(1) DEFAULT '0' COMMENT '是否为橱窗商品 1是 0否',
  `sizeTableId` int(5) DEFAULT '0' COMMENT '商品的尺码表Id',
  `icsonDesc` varchar(255) DEFAULT '' COMMENT '易迅商品详情内容',
  `first_insert_time` datetime DEFAULT NULL COMMENT '平台第一次数据下载时间',
  `last_update_time` datetime DEFAULT NULL COMMENT '平台最后一次更新时间',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '数据变更时间戳',
  PRIMARY KEY (`id`),
  UNIQUE KEY `itemCode` (`itemCode`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='拍拍商品信息';