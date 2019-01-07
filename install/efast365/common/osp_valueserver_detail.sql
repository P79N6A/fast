DROP TABLE IF EXISTS `osp_valueserver_detail`;
CREATE TABLE `osp_valueserver_detail` (
  `vd_id` int(11) NOT NULL AUTO_INCREMENT,
  `value_id` int(11) NOT NULL DEFAULT '0' COMMENT '增值服务器ID',
  `vd_busine_id` int(11) NOT NULL DEFAULT '0' COMMENT '业务ID',
  `vd_busine_code` varchar(50) NOT NULL DEFAULT '' COMMENT '业务代码',
  `vd_busine_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '0:菜单功能类型，1自动服务,2店铺类型,3WMS,4特殊',
  PRIMARY KEY (`vd_id`),
  UNIQUE KEY `_index_key` (`value_id`,`vd_busine_code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

