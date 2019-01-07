
DROP TABLE IF EXISTS `sys_sms_supplier`;
CREATE TABLE `sys_sms_supplier` (
  `supplier_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_code` varchar(20) NOT NULL DEFAULT '' COMMENT '供应商代码',
  `supplier_name` varchar(20) NOT NULL COMMENT '供应商名称',
  `unit_price` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '结算单价',
  `server_ip` varchar(20) NOT NULL DEFAULT '' COMMENT '服务器ip',
  `server_port` varchar(20) NOT NULL DEFAULT '' COMMENT '服务器端口',
  `is_active` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否启用',
  `remark` varchar(255) NOT NULL DEFAULT '描述',
  PRIMARY KEY (`supplier_id`),
  UNIQUE KEY `idxu_supplier_code` (`supplier_code`) USING BTREE,
  UNIQUE KEY `idxu_supplier_name` (`supplier_name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='短信供应商';
