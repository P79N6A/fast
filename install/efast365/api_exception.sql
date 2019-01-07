DROP TABLE IF EXISTS `api_exception`;
CREATE TABLE `api_exception` (
  `id` int(11) unsigned AUTO_INCREMENT COMMENT '主键',
  `queue_id` int(11) unsigned COMMENT '队列ID',
  `kh_id` int(11) DEFAULT NULL COMMENT '客户ID',
  `source` varchar(10) DEFAULT 'taobao' COMMENT '平台代码:taobao,jd',
  `sid` varchar(10) DEFAULT 'efast5' COMMENT '业务系统标识:efast5',
  `action` varchar(50) DEFAULT '' COMMENT '动作:api/goods/goods_download,api/goods/inv_upload,api/order/logistics_upload,api/order/order_download',
  `code` varchar(10) COMMENT '异常代码',
  `msg` varchar(200) COMMENT '异常消息',
  `request_params` text DEFAULT '' COMMENT '请求参数,json字符串',
  `time` datetime COMMENT '发生时间',
  PRIMARY KEY (`id`),
  key(`queue_id`),
  key(`kh_id`),
  key(`source`),
  key(`sid`),
  key(`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '异常日志';