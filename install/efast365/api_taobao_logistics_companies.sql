
DROP TABLE IF EXISTS `api_taobao_logistics_companies`;
CREATE TABLE `api_taobao_logistics_companies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) DEFAULT NULL COMMENT '物流公司代码',
  `taobao_id` int(11) DEFAULT NULL COMMENT '淘宝上的ID',
  `name` varchar(100) DEFAULT NULL COMMENT '物流公司名称',
  `reg_mail_no` text COMMENT '物流单号校验规则（正则表达）',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1781 DEFAULT CHARSET=utf8;

