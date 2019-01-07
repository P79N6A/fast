DROP TABLE IF EXISTS `api_kehu`;
CREATE TABLE `api_kehu` (
  `id` int(11) unsigned AUTO_INCREMENT,
  `kh_id` int(11) unsigned COMMENT '用户ID',
  `kh_code` varchar(20) DEFAULT NULL COMMENT '用户其它编码',
  `kh_name` varchar(100) DEFAULT NULL COMMENT '用户名称',
  `sid` varchar(10) DEFAULT 'efast5' COMMENT '业务系统标识:efast5',
  `basic` text DEFAULT '' COMMENT '基本信息,json字符串',
  `rds` text DEFAULT '' COMMENT 'RDS信息,json字符串',
  `auth` text DEFAULT '' COMMENT '授权信息,json字符串',
  `lastchanged` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '运营平台的客户相关信息';