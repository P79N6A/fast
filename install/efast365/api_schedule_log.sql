/*
Navicat MySQL Data Transfer

Source Server         : baison
Source Server Version : 50703
Source Host           : localhost:3306
Source Database       : api

Target Server Type    : MYSQL
Target Server Version : 50703
File Encoding         : 65001

Date: 2015-03-04 16:10:33
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for api_schedule_log
-- ----------------------------
DROP TABLE IF EXISTS `api_schedule_log`;
CREATE TABLE `api_schedule_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT '用户编码',
  `platform` int(11) DEFAULT NULL COMMENT 'platform:平台\r\n',
  `type` varchar(255) DEFAULT NULL COMMENT '接口类型 item/inventory/trades 等',
  `result` int(11) DEFAULT NULL COMMENT '运行结果 0, 失败 1, 成功',
  `starttime` varchar(255) DEFAULT NULL COMMENT '任务开始时间',
  `endtime` varchar(255) DEFAULT NULL COMMENT '任务结束时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of api_schedule_log
-- ----------------------------
