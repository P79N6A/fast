DROP TABLE IF EXISTS `api_shop`;
CREATE TABLE `api_shop` (
  `id` int(11) unsigned AUTO_INCREMENT,
  `kh_id` int(11) unsigned COMMENT '用户ID',
  `shop_code` varchar(20) COMMENT '商店代码',
  `shop_name` varchar(100) COMMENT '商店名称',
  `nick` varchar(30) COMMENT '卖家NICK',
  `app_key` varchar(64) COMMENT '应用key',
  `app_secret` varchar(50) COMMENT '应用secret',
  `session_key` varchar(512) COMMENT '店铺session',
  `extra_params` text COMMENT '额外信息,json字符串',
  `source`  varchar(20) COMMENT '商店平台代码，例如taobao',
  `lastchanged` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
  PRIMARY KEY (`id`),
  KEY(`kh_id`),
  KEY(`shop_code`),
  KEY(`nick`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '店铺API信息';