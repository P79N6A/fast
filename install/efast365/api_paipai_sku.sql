-- ----------------------------
-- Table structure for api_paipai_sku 拍拍商品sku列表
-- ----------------------------
DROP TABLE IF EXISTS `api_paipai_sku`;
CREATE TABLE `api_paipai_sku` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `skuId` varchar(50) DEFAULT NULL COMMENT '商品的库存唯一标识码,由拍拍平台生成',
  `itemCode` varchar(20) DEFAULT '' COMMENT '商品编码',
  `status` int(5) DEFAULT 0 COMMENT '库存状态码:1-[IS_IN_STORE:仓库中],2-[IS_FOR_SALE:上架销售中],9-[IS_PRE_DELETE:预删除],64-[IS_SALE_ON_TIME:自定义时间上架],6-[IS_SOLD_OUT:售完]',
  `saleAttr` varchar(255) DEFAULT NULL COMMENT 'sku属性',
  `pic` varchar(100) DEFAULT NULL COMMENT '图片地址',
  `stockLocalCode` varchar(50) DEFAULT NULL COMMENT '商品的商家自定义库存id',
  `stockAttr` varchar(255) DEFAULT NULL COMMENT '商品的库存属性串',
  `stockDesc` varchar(255) DEFAULT NULL COMMENT '商品的库存备注',
  `soldCount_TODO` varchar(20) DEFAULT NULL COMMENT '商品的该库存对应的销售数量',
  `stockPrice` varchar(20) DEFAULT NULL COMMENT '商品库存价格',
  `stockCount_TODO` int(5) DEFAULT 0 COMMENT '商品的该库存数量',
  `createTime_TODO` datetime DEFAULT NULL COMMENT '创建时间',
  `lastModifyTime_TODO` datetime DEFAULT NULL COMMENT '最后更新时间',
  `first_insert_time` datetime DEFAULT NULL COMMENT '平台第一次数据下载时间',
  `last_update_time` datetime DEFAULT NULL COMMENT '平台最后一次更新时间',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '数据变更时间戳',
  PRIMARY KEY (`id`),
  UNIQUE KEY `skuId` (`skuId`,`itemCode`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '拍拍商品sku列表';