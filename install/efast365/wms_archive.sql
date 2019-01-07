DROP TABLE IF EXISTS `wms_archive`;
CREATE TABLE `wms_archive` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `api_product` varchar(10) NOT NULL COMMENT '对接wms的名称',
  `efast_store_code` varchar(128) NOT NULL,
  `type` varchar(20) NOT NULL COMMENT '档案类型',
  `code` varchar(30) NOT NULL COMMENT '代码',
  `is_success` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否上传成功',
  `msg` varchar(128) NOT NULL COMMENT '上传错误信息',
  `tbl_changed` datetime NOT NULL COMMENT '原表数据最后一次更新时间',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idxu` (`efast_store_code`,`type`,`code`) USING BTREE,
  KEY `idx_last` (`lastchanged`) USING BTREE,
  KEY `idx_tbl_last` (`tbl_changed`) USING BTREE,
  KEY `idx_code` (`code`) USING BTREE,
  KEY `idx_type` (`type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
