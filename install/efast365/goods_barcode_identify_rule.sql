
DROP TABLE IF EXISTS `goods_barcode_identify_rule`;
CREATE TABLE `goods_barcode_identify_rule` (
  `rule_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `rule_name` varchar(64) DEFAULT '' COMMENT '规则名称(暂不用)',
  `rule_type` int(8) DEFAULT '1' COMMENT '1:通用型，2:特殊型',
  `rule_sort` INT(8) DEFAULT '1' COMMENT '方案类别  1:方案1. 2:方案2',
  `priority` int(8) DEFAULT '1' COMMENT '优先级1-20',
  `rule_content1` TEXT COMMENT '方案1规则内容',
  `rule_content2` LONGTEXT COMMENT '方案2规则内容 格式为length1,length2|length3,length4|',
  `status` int(4) DEFAULT '1' COMMENT '1：启用 0：停用(暂不用)',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `is_add_person` VARCHAR(64) DEFAULT '' COMMENT '添加人',
  `is_add_time` DATETIME DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`rule_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='商品条码识别规则';


