DROP TABLE IF EXISTS `api_taobao_area`;
CREATE TABLE `api_taobao_area` (
  `id` varchar(60) DEFAULT NULL,
  `type` int(10) DEFAULT NULL,
  `name` varchar(300) DEFAULT NULL,
  `parent_id` varchar(150) DEFAULT NULL,
  `zip` varchar(36) DEFAULT NULL,
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `url` varchar(300) DEFAULT NULL,
  `catch` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='淘宝地址区域表';
