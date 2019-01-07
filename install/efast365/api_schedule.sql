DROP TABLE
IF EXISTS `api_schedule`;

CREATE TABLE `api_schedule` (
	`id` INT (11) NOT NULL AUTO_INCREMENT COMMENT '主键',
	`kh_id` INT (11) DEFAULT NULL COMMENT '用户ID',
	`kh_code` VARCHAR (255) DEFAULT NULL COMMENT '用户其它编码',
	`kh_name` VARCHAR (255) DEFAULT NULL COMMENT '用户名称',
	`source` VARCHAR (10) DEFAULT 'taobao' COMMENT '平台代码:taobao,jd',
	`sid` VARCHAR (10) DEFAULT 'efast5' COMMENT '业务系统标识:efast5',
	`action` VARCHAR (50) DEFAULT '' COMMENT '动作:api/goods/goods_download,api/goods/inv_upload,api/order/logistics_upload,api/order/order_download',
	`intervalnum` MEDIUMINT UNSIGNED DEFAULT 300 COMMENT '定时间隔,单位秒',
	`lasttime_changed` datetime COMMENT '任务上次修改时间',
	`start_time` datetime COMMENT '任务开始时间',
	`switch` TINYINT (1) UNSIGNED COMMENT '是否开启:0关闭,1开启',
	`is_periodical` TINYINT (1) UNSIGNED DEFAULT 0 COMMENT '是否需要周期执行: 0否，1是',
	`extra_params` VARCHAR (200) DEFAULT '' COMMENT '附加参数,json字符串',
	PRIMARY KEY (`id`),
	UNIQUE KEY (
		`kh_id`,
		`source`,
		`sid`,
		`action`
	)
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT '任务表';


DROP TRIGGER
IF EXISTS `api_schedule_tg1`;

CREATE TRIGGER api_schedule_tg1 BEFORE INSERT ON api_schedule FOR EACH ROW
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
IF EXISTS `api_schedule_tg2`;

CREATE TRIGGER api_schedule_tg2 before UPDATE ON api_schedule FOR EACH ROW
BEGIN
SET new.lasttime_changed = now();
END;