
DROP TABLE IF EXISTS `business_type`;
CREATE TABLE `business_type` (
  `type_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `business_name` varchar(30) DEFAULT '' COMMENT '业务名称',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`type_id`),
  UNIQUE KEY `idx_business_name` (`business_name`) USING BTREE,
  KEY `lastchanged` (`lastchanged`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='业务类型表';

