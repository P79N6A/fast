-- ----------------------------
-- Table structure for api_jingdong_refund_detail
-- ----------------------------
DROP TABLE IF EXISTS `api_jingdong_refund_detail`;
CREATE TABLE `api_jingdong_refund_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `afsServiceDetailId` varchar(50) NOT NULL COMMENT '明细ID',
  `afsServiceId` varchar(50) DEFAULT NULL COMMENT '服务单ID',
  `createDate` varchar(50) DEFAULT NULL COMMENT '创建时间',
  `createName` varchar(50) DEFAULT NULL COMMENT '创建人',
  `wareBrand` varchar(50) DEFAULT NULL COMMENT '商品品牌',
  `wareCid1` varchar(50) DEFAULT NULL COMMENT '一级分类',
  `wareCid2` varchar(50) DEFAULT NULL COMMENT '二级分类',
  `wareCid3` varchar(50) DEFAULT NULL COMMENT '三级分类',
  `wareId` varchar(50) DEFAULT NULL COMMENT '商品编号',
  `wareName` varchar(255) DEFAULT NULL COMMENT '商品名称 ',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '数据变更时间戳',
  PRIMARY KEY (`id`),
  UNIQUE KEY `refund_order_sku__item_id` (`afsServiceId`,`afsServiceDetailId`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='京东服务单原始数据明细';