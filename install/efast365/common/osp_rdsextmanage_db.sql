
DROP TABLE IF EXISTS `osp_rdsextmanage_db`;
CREATE TABLE `osp_rdsextmanage_db` (
  `rem_db_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '数据库主键ID',
  `rem_db_pid` int(11) NOT NULL COMMENT '所属RDS的ID',
  `rem_db_name` varchar(100) NOT NULL COMMENT '数据库名称',
  `rem_db_version` varchar(10) DEFAULT NULL COMMENT '数据库所属产品系统版本',
  `rem_cluster_id` int(11) DEFAULT NULL COMMENT '所在集群id',
  `rem_db_version_ip` varchar(50) DEFAULT NULL COMMENT '运营版本IP',
  `rem_db_version_patch` varchar(10) DEFAULT NULL COMMENT '产品系统版本补丁号',
  `rem_db_is_bindkh` varchar(10) DEFAULT '0' COMMENT '是否绑定客户',
  `rem_try_kh` int(10) DEFAULT '0' COMMENT '是否试用客户',
  `rem_db_bindtype` int(11) DEFAULT '0' COMMENT '绑定类型',
  `rem_db_khid` int(11) DEFAULT NULL COMMENT '所属客户',
  `rem_db_createdate` datetime DEFAULT NULL COMMENT '创建时间',
  `rem_db_bz` varchar(255) DEFAULT NULL COMMENT '备注',
  `lastchanged` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  `rem_db_sys` int(11) DEFAULT NULL COMMENT '是否为管理库',
  `rem_db_sys_version` varchar(10) DEFAULT NULL COMMENT '数据库所属产品版本(标准版，企业版，旗舰版)',
  PRIMARY KEY (`rem_db_id`),
  UNIQUE KEY `kh_db` (`rem_db_name`,`rem_db_khid`) COMMENT '客户数据库唯一'
) ENGINE=InnoDB AUTO_DEFAULT CHARSET=utf8;


