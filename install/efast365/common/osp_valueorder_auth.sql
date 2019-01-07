
DROP TABLE IF EXISTS `osp_valueorder_auth`;
CREATE TABLE `osp_valueorder_auth` (
  `vra_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '增值授权ID',
  `vra_kh_id` int(11) DEFAULT NULL COMMENT '关联客户ID',
  `vra_cp_id` int(11) DEFAULT NULL COMMENT '产品ID',
  `vra_server_id` int(11) DEFAULT NULL COMMENT '增值服务id',
  `vra_startdate` datetime DEFAULT NULL COMMENT '开始时间',
  `vra_enddate` datetime DEFAULT NULL COMMENT '结束时间',
  `vra_state` int(11) DEFAULT NULL COMMENT '授权状态',
  `vra_bz` varchar(255) DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`vra_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

