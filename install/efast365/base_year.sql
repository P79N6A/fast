
DROP TABLE IF EXISTS `base_year`;
CREATE TABLE `base_year` (
  `year_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `year_code` varchar(30) DEFAULT '' COMMENT '代码',
  `year_name` varchar(30) DEFAULT '' COMMENT '名称',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`year_id`),
  UNIQUE KEY `idx_year_code` (`year_code`) USING BTREE,
  UNIQUE KEY `idx_year_name` (`year_name`) USING BTREE,
  KEY `lastchanged` (`lastchanged`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='年份表';

