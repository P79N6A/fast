
DROP TABLE IF EXISTS `osp_vmextmanage_ver`;
CREATE TABLE `osp_vmextmanage_ver` (
  `vem_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `vem_vm_id` int(11) DEFAULT NULL COMMENT '所属VM的ID',
  `vem_cp_version` varchar(10) DEFAULT '' COMMENT '关联产品版本',
  `vem_cp_version_ip` varchar(128) DEFAULT '' COMMENT '版本所在IP地址服务',
  `vem_cp_path` varchar(256) DEFAULT '' COMMENT '产品所在服务器目录',
  `vem_cp_id` varchar(10) DEFAULT NULL COMMENT '关联产品',
  `vem_status` tinyint(3) DEFAULT '1' COMMENT '1启用，0暂停',
  `vem_cp_web_path` varchar(256) DEFAULT NULL COMMENT 'efast/',
  `vem_cluster_id` int(11) DEFAULT NULL COMMENT '所在集群ID',
  `vem_createdate` datetime DEFAULT NULL COMMENT '创建日期',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`vem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

