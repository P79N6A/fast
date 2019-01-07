
DROP TABLE IF EXISTS `mailer_queue`;
CREATE TABLE `mailer_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kh_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL COMMENT '邮件标题',
  `cont_body` text COMMENT '邮件内容',
  `cont_json` text NOT NULL COMMENT '邮件内容json格式',
  `send_to` varchar(255) NOT NULL COMMENT '接收人',
  `create_time` datetime DEFAULT NULL COMMENT '插入时间',
  `is_send` tinyint(255) DEFAULT '0' COMMENT '是否发送 0：未发送 1已发送',
  `send_time` datetime DEFAULT NULL COMMENT '发送时间',
  `send_msg` varchar(255) DEFAULT NULL COMMENT '描述',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

