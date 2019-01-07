DROP TABLE IF EXISTS `goods_barcode_rule`;
CREATE TABLE `goods_barcode_rule` (
  `barcode_rule_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `rule_code` varchar(64) DEFAULT '' COMMENT '规则代码',
  `rule_name` varchar(64) DEFAULT '' COMMENT '规则名称',
  `barcode_prefix` varchar(64) DEFAULT '' COMMENT '前缀',
  `barcode_suffix` varchar(64) DEFAULT '' COMMENT '后缀',
  `serial_num` varchar(255) DEFAULT '' COMMENT '流水号',
  `serial_num_length` int(8) DEFAULT '0' COMMENT '流水号长度',
  `project1` int(8) DEFAULT '0' COMMENT '商品:1 规格1:2 规格2:3',
  `split1` varchar(50) DEFAULT '' COMMENT '属性1后缀',
  `project2` int(8) DEFAULT '0' COMMENT '商品:1 规格1:2 规格2:3',
  `split2` varchar(50) DEFAULT '' COMMENT '属性2后缀',
  `project3` int(8) DEFAULT '0' COMMENT '商品:1 规格1:2 规格2:3',
  `split3` varchar(50) DEFAULT '' COMMENT '属性3后缀',
  `sys` int(4) DEFAULT '0' COMMENT '是否是系统值 1：是 0：不是',
  `status` int(4) DEFAULT '1' COMMENT '1：启用 0：停用',
  `is_main` int(4) DEFAULT '0' COMMENT '是否主条码规则 0-否 1-是',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `trd_id` varchar(64) DEFAULT '' COMMENT '第三方来源GUID',
  `trd_type` varchar(32) NOT NULL DEFAULT '' COMMENT '第三方来源类型:erp/3000/efast',
  `trd_time` varchar(64) DEFAULT '' COMMENT '第三方导入或者更新的时间',
  PRIMARY KEY (`barcode_rule_id`),
  UNIQUE KEY `_key` (`rule_code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='商品条码生成规则';

