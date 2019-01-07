-- ----------------------------
-- Table structure for `api_jingdong_area`
-- ----------------------------
DROP TABLE IF EXISTS `api_jingdong_area`;
CREATE TABLE `api_jingdong_area` (
  `id` int(10) unsigned NOT NULL COMMENT '京东地址ID',
  `type` int(10) NOT NULL COMMENT '类型：省市区 0省 1市 2区县 3乡镇',
  `name` varchar(30) NOT NULL COMMENT '名称',
  `parent_id` varchar(150) NOT NULL COMMENT '父级ID',
  `zip` varchar(36) NOT NULL COMMENT '邮政编码',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `url` varchar(300) DEFAULT NULL,
  `catch` varchar(30) DEFAULT NULL,
  `cod` varchar(10) DEFAULT NULL,
  `is3cod` varchar(10) DEFAULT NULL,
  `sys_area_id` bigint(20) NOT NULL DEFAULT '0',
  `sys_area_level` tinyint(4) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='京东地址区域表';