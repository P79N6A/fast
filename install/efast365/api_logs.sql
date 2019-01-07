
DROP TABLE IF EXISTS `api_logs`;
CREATE TABLE `api_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL COMMENT '接口类别：taobao,jingdong,paipai',
  `method` varchar(150) DEFAULT NULL COMMENT '接口名称',
  `url` text NOT NULL COMMENT '请求地址',
  `params` mediumtext COMMENT '请求参数',
  `post_data` mediumtext COMMENT '请求业务参数',
  `return_data` text COMMENT '返回的数据',
  `add_time` datetime DEFAULT NULL COMMENT '记录时间',
  `is_err` tinyint(4) NOT NULL COMMENT 'http请求出错',
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `method` (`method`),
  KEY `add_time` (`add_time`),
  KEY `is_err` (`is_err`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

