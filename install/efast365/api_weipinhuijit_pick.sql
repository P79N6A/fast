DROP TABLE IF EXISTS `api_weipinhuijit_pick`;
CREATE TABLE `api_weipinhuijit_pick` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `total` int(11) NOT NULL COMMENT '记录总条数',
  `shop_code` varchar(100) NOT NULL COMMENT '店铺code',
  `delivery_no` varchar(50) NOT NULL COMMENT '出库单号',
  `pick_no` varchar(50) NOT NULL COMMENT '拣货单编号',
  `pick_type` varchar(50) NOT NULL COMMENT '拣货单类别',
  `notice_record_no` varchar(50) NOT NULL COMMENT '通知单号',
  `po_no` varchar(50) NOT NULL COMMENT 'PO单编号',
  `pick_num` int(10) NOT NULL COMMENT '商品拣货数量',
  `notice_num` int(10) DEFAULT '0' COMMENT '通知数',
  `delivery_num` int(10) DEFAULT '0' COMMENT '发货数',
  `sell_st_time` int(10) NOT NULL COMMENT '档期开始销售时间',
  `sell_et_time` int(10) NOT NULL COMMENT '档期结束销售时间',
  `export_time` int(10) NOT NULL COMMENT '导出时间',
  `export_num` int(11) NOT NULL COMMENT '导出次数',
  `warehouse` varchar(50) NOT NULL COMMENT '送货仓库',
  `order_cate` varchar(50) NOT NULL COMMENT '订单类别',
  `delivery_id` int(11) DEFAULT NULL COMMENT '出库单外键ID',
  `insert_time` varchar(50) DEFAULT NULL COMMENT '插入时间',
  `is_execute` TINYINT(1) DEFAULT '0' COMMENT '是否生成销货单',
  PRIMARY KEY (`id`),
  UNIQUE KEY `pick_no` (`pick_no`),
  KEY `po_no` (`po_no`),

  KEY `delivery_no` (`delivery_no`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;