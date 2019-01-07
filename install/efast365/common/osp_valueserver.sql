
DROP TABLE IF EXISTS `osp_valueserver`;
CREATE TABLE `osp_valueserver` (
  `value_id` int(20) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `value_code` varchar(20) DEFAULT NULL COMMENT '增值服务code',
  `value_name` varchar(20) DEFAULT NULL COMMENT '增值服务名称',
  `value_cat` varchar(20) DEFAULT NULL COMMENT '所属增值类别',
  `value_price` varchar(20) DEFAULT NULL COMMENT '增值服务价格',
  `value_cycle` int(11) DEFAULT '0' COMMENT '周期—单位月',
  `value_cp_id` int(20) DEFAULT NULL COMMENT '增值服务产品id,和产品表里面的cp_code关联',
  `value_cp_version` varchar(10) DEFAULT NULL COMMENT '产品版本',
  `value_require_version` varchar(20) DEFAULT NULL COMMENT '最低版本要求',
  `value_enable` int(10) DEFAULT '0' COMMENT '是否启用',
  `value_desc` varchar(20) DEFAULT NULL COMMENT '描述',
  PRIMARY KEY (`value_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;

