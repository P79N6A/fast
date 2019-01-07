
DROP TABLE IF EXISTS `goods_auto_code`;
CREATE TABLE `goods_auto_code` (
  `auto_code_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `status` int(4) DEFAULT '0' COMMENT '保留暂时不用 0不启用 1启用',
  `prefix` varchar(64) DEFAULT '' COMMENT '前缀',
  `type` int(4) DEFAULT '0' COMMENT '0:未选择 ,1：品牌 2：分类 3：系列 4：季节',
  `length` varchar(64) DEFAULT '' COMMENT '长度',
  `is_default` int(4) DEFAULT '0' COMMENT '0：不缺省 1：缺省为0',
  `serial_num` varchar(64) DEFAULT '' COMMENT '流水号',
  `serial_index` varchar(64) DEFAULT '' COMMENT '流水号索引',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`auto_code_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='商品自动编码';

