
DROP TABLE IF EXISTS `osp_valueserver_category`;
CREATE TABLE `osp_valueserver_category` (
  `vc_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '增值类别ID',
  `vc_code` varchar(20) NOT NULL COMMENT '增值类别代码',
  `vc_name` varchar(200) NOT NULL COMMENT '增值类别名称',
  `vc_order` varchar(11) DEFAULT NULL COMMENT '增值优先级',
  `vc_bz` varchar(255) DEFAULT NULL COMMENT '增值类别备注',
  `vc_enable` int(10) DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`vc_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;


