DROP TABLE IF EXISTS `api_schedule_queue`;
CREATE TABLE `api_schedule_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `kh_id` int(11) DEFAULT NULL COMMENT '用户ID',
  `source` varchar(10) DEFAULT 'taobao' COMMENT '平台代码:taobao,jd',
  `sid` varchar(10) DEFAULT 'efast5' COMMENT '业务系统标识:efast5',
  `action` varchar(50) DEFAULT '' COMMENT '动作:api/goods/goods_download,api/goods/inv_upload,api/order/logistics_upload,api/order/order_download',
  `lasttime_changed` datetime COMMENT '任务上次修改时间',
  `start_time` datetime COMMENT '任务开始时间',
  `status` tinyint(1) unsigned COMMENT '任务状态：0,初始状态未开始 1, 启动运行中 2,执行成功 3, 停止 4, 异常',
  `extra_params` varchar(200) DEFAULT '' COMMENT '附加参数,json字符串',
  PRIMARY KEY (`id`),
  key(`kh_id`),
  key(`source`),
  key(`sid`),
  key(`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '队列表';


DROP TRIGGER
IF EXISTS `api_schedule_queue_tg1`;

CREATE TRIGGER api_schedule_queue_tg1 BEFORE INSERT ON api_schedule_queue FOR EACH ROW
BEGIN
IF new.lasttime_changed IS NULL THEN
SET new.lasttime_changed = now();
END
IF;

IF new.start_time IS NULL THEN
SET new.start_time = now();
END
IF;
END;


DROP TRIGGER
IF EXISTS `api_schedule_queue_tg2`;

CREATE TRIGGER api_schedule_queue_tg2 before UPDATE ON api_schedule_queue FOR EACH ROW
BEGIN
SET new.lasttime_changed = now();
END;