
DROP TABLE IF EXISTS `jxc_info_group`;
CREATE TABLE `jxc_info_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tbl_mx` varchar(20) DEFAULT '' COMMENT '已汇总的明细表',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `_idxu_key` (`tbl_mx`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='进销存已统计过的年月';