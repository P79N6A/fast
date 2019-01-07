
DROP TABLE IF EXISTS `sys_goods_rule`;
CREATE TABLE `sys_goods_rule` (
  `goods_rule_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` int(4) DEFAULT '0' COMMENT '0：商品行列规格 1：商品附加属性 2：商品进价名称 3：售价名称 4：会员附加属性',
  `init_name` varchar(255) DEFAULT '' COMMENT '内置名称',
  `code` varchar(255) DEFAULT '' COMMENT '代码',
  `name` varchar(255) DEFAULT '' COMMENT '名称',
  `status` int(4) DEFAULT '1' COMMENT '1：启用 0：停用',
  `row_num` int(4) DEFAULT '1' COMMENT '行数',
  `show_width` int(4) DEFAULT '1' COMMENT '显示宽度',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`goods_rule_id`)
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8 COMMENT='商品规约';

