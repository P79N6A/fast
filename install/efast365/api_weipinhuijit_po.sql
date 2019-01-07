DROP TABLE IF EXISTS `api_weipinhuijit_po`;
CREATE TABLE `api_weipinhuijit_po` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `shop_code` varchar(100) NOT NULL COMMENT '店铺code',
  `notice_id` int(10) DEFAULT '0' COMMENT '绑定的批发通知单id',
  `notice_record_no` varchar(150) DEFAULT NULL COMMENT '绑定的批发通知单单号',
  `po_no` varchar(255) NOT NULL COMMENT 'po编号',
  `co_mode` varchar(255) NOT NULL COMMENT '合作模式编码',
  `sell_st_time` varchar(50) DEFAULT NULL COMMENT '档期开始销售时间',
  `sell_et_time` varchar(50) DEFAULT NULL COMMENT '档期结束销售时间',
  `stock` varchar(50) DEFAULT NULL COMMENT '虚拟总库存',
  `sales_volume` varchar(50) DEFAULT NULL COMMENT '销售数',
  `not_pick` varchar(50) DEFAULT NULL COMMENT '未拣货数',
  `insert_time` varchar(50) NOT NULL COMMENT '插入时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `po_no` (`po_no`),
  KEY `notice_record_no` (`notice_record_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;