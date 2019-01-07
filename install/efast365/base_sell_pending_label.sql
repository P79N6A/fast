DROP TABLE IF EXISTS `base_sell_pending_label`;
CREATE TABLE `base_sell_pending_label` (
  `sell_psending_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sell_psending_code` varchar(128) NOT NULL DEFAULT '' COMMENT '标签代码',
  `sell_psending_name` varchar(128) NOT NULL DEFAULT '' COMMENT '标签名称',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`sell_psending_id`),
  UNIQUE KEY `idxu_return_label_code` (`sell_psending_code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;