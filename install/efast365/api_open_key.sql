DROP TABLE IF EXISTS `api_open_key`;
CREATE TABLE `api_open_key` (
  `id` int(11) unsigned AUTO_INCREMENT COMMENT '主键',
  `key` varchar(50) not null COMMENT 'KEY',
  `secret` varchar(32) not null COMMENT '密钥',
  `kh_id` varchar(10) DEFAULT '0' COMMENT '绑定的客户ID，0代表全部客户可用',
  `status` int(4) DEFAULT 1 COMMENT '状态：1启用，0停用',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '数据变更时间戳',
  `name` varchar(255) DEFAULT 'key的名称或说明' COMMENT '',
  PRIMARY KEY (`id`),
  key(`key`),
  key(`kh_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '对外接口的KEY和密钥';