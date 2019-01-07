/*
Navicat MySQL Data Transfer

Source Server         : EFAST5正式
Source Server Version : 50518
Source Host           : jconn45enky5i.mysql.rds.aliyuncs.com:3306
Source Database       : sysdb

Target Server Type    : MYSQL
Target Server Version : 50518
File Encoding         : 65001

Date: 2015-05-27 16:53:08
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `osp_rdsextmanage_db`
-- ----------------------------
DROP TABLE IF EXISTS `osp_rdsextmanage_db`;
CREATE TABLE `osp_rdsextmanage_db` (
`rem_db_id`  int(11) NOT NULL AUTO_INCREMENT COMMENT '数据库主键ID' ,
`rem_db_pid`  int(11) NOT NULL COMMENT '所属RDS的ID' ,
`rem_db_name`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '数据库名称' ,
`rem_db_version`  varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '数据库所属版本' ,
`rem_cluster_id`  int(11) NULL DEFAULT NULL COMMENT '所在集群id' ,
`rem_db_version_ip`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '运营版本IP' ,
`rem_db_is_bindkh`  varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '是否绑定客户' ,
`rem_try_kh`  int(10) NULL DEFAULT 0 COMMENT '是否试用客户' ,
`rem_db_bindtype`  int(11) NULL DEFAULT 0 COMMENT '绑定类型' ,
`rem_db_khid`  int(11) NULL DEFAULT NULL COMMENT '所属客户' ,
`rem_db_createdate`  datetime NULL DEFAULT NULL COMMENT '创建时间' ,
`rem_db_bz`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '备注' ,
`lastchanged`  timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间' ,
PRIMARY KEY (`rem_db_id`),
UNIQUE INDEX `kh_db` (`rem_db_name`, `rem_db_khid`) USING BTREE 
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
AUTO_INCREMENT=10

;

-- ----------------------------
-- Table structure for `osp_valueorder`
-- ----------------------------
DROP TABLE IF EXISTS `osp_valueorder`;
CREATE TABLE `osp_valueorder` (
`val_num`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '增值订购编号' ,
`val_channel_id`  int(20) NULL DEFAULT NULL COMMENT '销售渠道' ,
`val_kh_id`  int(20) NULL DEFAULT NULL COMMENT '客户id和osp_kehu表关联' ,
`val_cp_id`  int(20) NULL DEFAULT NULL COMMENT '关联产品ID' ,
`val_pt_version`  int(10) NULL DEFAULT NULL COMMENT '产品版本，1标准版、2企业版、3旗舰版' ,
`val_serverid`  int(20) NULL DEFAULT NULL COMMENT '增值服务id,和osp_valueserver表关联' ,
`val_standard_price`  decimal(10,0) NULL DEFAULT NULL COMMENT '标准价格' ,
`val_cheap_price`  decimal(10,0) NULL DEFAULT NULL COMMENT '让利' ,
`val_actual_price`  decimal(10,0) NULL DEFAULT NULL COMMENT '实际售价' ,
`val_hire_limit`  int(11) NULL DEFAULT 0 COMMENT '租用周期—单位月' ,
`val_seller`  int(11) NULL DEFAULT NULL COMMENT '销售经理' ,
`val_pay_status`  int(10) NULL DEFAULT 0 COMMENT '付款状态' ,
`val_paydate`  datetime NULL DEFAULT NULL COMMENT '付款日期' ,
`val_check_status`  int(10) NULL DEFAULT 0 COMMENT '审核状态' ,
`val_checkdate`  datetime NULL DEFAULT NULL COMMENT '审核日期' ,
`val_orderdate`  datetime NULL DEFAULT NULL COMMENT '订购日期' ,
`val_desc`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '描述' ,
PRIMARY KEY (`val_num`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci

;

-- ----------------------------
-- Table structure for `osp_valueorder_auth`
-- ----------------------------
DROP TABLE IF EXISTS `osp_valueorder_auth`;
CREATE TABLE `osp_valueorder_auth` (
`vra_id`  int(11) NOT NULL AUTO_INCREMENT COMMENT '增值授权ID' ,
`vra_kh_id`  int(11) NULL DEFAULT NULL COMMENT '关联客户ID' ,
`vra_cp_id`  int(11) NULL DEFAULT NULL COMMENT '产品ID' ,
`vra_pt_version`  int(10) NULL DEFAULT NULL COMMENT '产品版本，1标准版、2企业版、3旗舰版' ,
`vra_server_id`  int(11) NULL DEFAULT NULL COMMENT '增值服务id' ,
`vra_startdate`  datetime NULL DEFAULT NULL COMMENT '开始时间' ,
`vra_enddate`  datetime NULL DEFAULT NULL COMMENT '结束时间' ,
`vra_state`  int(11) NULL DEFAULT NULL COMMENT '授权状态' ,
`vra_bz`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '备注' ,
PRIMARY KEY (`vra_id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
AUTO_INCREMENT=2

;

-- ----------------------------
-- Table structure for `osp_valueserver`
-- ----------------------------
DROP TABLE IF EXISTS `osp_valueserver`;
CREATE TABLE `osp_valueserver` (
`value_id`  int(20) NOT NULL AUTO_INCREMENT COMMENT 'id' ,
`value_code`  varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '增值服务code' ,
`value_name`  varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '增值服务名称' ,
`value_cat`  varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '所属增值类别' ,
`value_price`  varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '增值服务价格' ,
`value_cycle`  int(11) NULL DEFAULT 0 COMMENT '周期—单位月' ,
`value_cp_id`  int(20) NULL DEFAULT NULL COMMENT '增值服务产品id,和产品表里面的cp_code关联' ,
`value_cp_version`  varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '产品版本' ,
`value_require_version`  varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '最低版本要求' ,
`value_enable`  int(10) NULL DEFAULT 0 COMMENT '是否启用' ,
`value_desc`  varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '描述' ,
PRIMARY KEY (`value_id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
AUTO_INCREMENT=60

;

-- ----------------------------
-- Table structure for `osp_valueserver_category`
-- ----------------------------
DROP TABLE IF EXISTS `osp_valueserver_category`;
CREATE TABLE `osp_valueserver_category` (
`vc_id`  int(11) NOT NULL AUTO_INCREMENT COMMENT '增值类别ID' ,
`vc_code`  varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '增值类别代码' ,
`vc_name`  varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '增值类别名称' ,
`vc_order`  varchar(11) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '增值优先级' ,
`vc_bz`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '增值类别备注' ,
`vc_enable`  int(10) NULL DEFAULT 0 COMMENT '状态' ,
PRIMARY KEY (`vc_id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
AUTO_INCREMENT=10

;

-- ----------------------------
-- Table structure for `osp_valueserver_detail`
-- ----------------------------
DROP TABLE IF EXISTS `osp_valueserver_detail`;
CREATE TABLE `osp_valueserver_detail` (
`vd_id`  int(11) NOT NULL AUTO_INCREMENT ,
`value_id`  int(11) NOT NULL DEFAULT 0 COMMENT '增值服务器ID' ,
`vd_busine_id`  int(11) NOT NULL DEFAULT 0 COMMENT '业务ID' ,
`vd_busine_code`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '业务代码' ,
`vd_busine_type`  tinyint(3) NOT NULL DEFAULT 0 COMMENT '0:菜单功能类型，1自动服务,2店铺类型' ,
PRIMARY KEY (`vd_id`),
UNIQUE INDEX `_index_key` (`value_id`, `vd_busine_code`) USING BTREE 
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
AUTO_INCREMENT=22

;

-- ----------------------------
-- Table structure for `osp_vmextmanage_ver`
-- ----------------------------
DROP TABLE IF EXISTS `osp_vmextmanage_ver`;
CREATE TABLE `osp_vmextmanage_ver` (
`vem_id`  int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID' ,
`vem_vm_id`  int(11) NULL DEFAULT NULL COMMENT '所属VM的ID' ,
`vem_cp_version`  varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '关联产品版本' ,
`vem_cp_version_ip`  varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '版本所在IP地址服务' ,
`vem_cp_path`  varchar(256) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '产品所在服务器目录' ,
`vem_cp_id`  varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '关联产品' ,
`vem_status`  tinyint(3) NULL DEFAULT 1 COMMENT '1启用，0暂停' ,
`vem_cp_web_path`  varchar(256) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'efast/' ,
`vem_cluster_id`  int(11) NULL DEFAULT NULL COMMENT '所在集群ID' ,
`vem_createdate`  datetime NULL DEFAULT NULL COMMENT '创建日期' ,
`lastchanged`  timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间' ,
PRIMARY KEY (`vem_id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
AUTO_INCREMENT=3

;

-- ----------------------------
-- Table structure for `sys_action_extend`
-- ----------------------------
DROP TABLE IF EXISTS `sys_action_extend`;
CREATE TABLE `sys_action_extend` (
`action_id`  int(11) NOT NULL ,
`extend_code`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'efast5_Standard' COMMENT 'efast5_Standard普通版本，efast5_Ultimate旗舰版本，efast5_Enterprise企业版本' ,
PRIMARY KEY (`action_id`, `extend_code`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
COMMENT='版本不包含功能'

;

-- ----------------------------
-- Table structure for `sys_action_valueserver`
-- ----------------------------
DROP TABLE IF EXISTS `sys_action_valueserver`;
CREATE TABLE `sys_action_valueserver` (
`id`  int(11) NOT NULL ,
`value_id`  int(11) NOT NULL DEFAULT 0 COMMENT '增值服务器ID' ,
`action_id`  int(11) NOT NULL DEFAULT 0 COMMENT '业务ID' ,
`action_code`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '业务代码' ,
`type`  tinyint(3) NOT NULL DEFAULT 0 COMMENT '0:菜单功能类型，1自动服务' ,
PRIMARY KEY (`id`),
UNIQUE INDEX `_index_key` (`value_id`, `action_code`) USING BTREE 
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci

;

-- ----------------------------
-- Table structure for `sys_efast_config`
-- ----------------------------
DROP TABLE IF EXISTS `sys_efast_config`;
CREATE TABLE `sys_efast_config` (
`id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'id' ,
`param_code`  varchar(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '参数代码' ,
`parent_code`  varchar(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '上级参数代码' ,
`param_name`  varchar(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '参数名称' ,
`type`  varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' ,
`value`  varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '参数值' ,
`sort`  decimal(8,2) NOT NULL DEFAULT 0.00 COMMENT '参数界面显示排序号' ,
`remark`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '说明' ,
`lastchanged`  timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间' ,
PRIMARY KEY (`id`),
UNIQUE INDEX `idx_param_code` (`param_code`) USING BTREE 
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
COMMENT='efast产品配置表'
AUTO_INCREMENT=1

;

-- ----------------------------
-- Table structure for `sys_message`
-- ----------------------------
DROP TABLE IF EXISTS `sys_message`;
CREATE TABLE `sys_message` (
`id`  int(20) NOT NULL AUTO_INCREMENT ,
`code`  varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '消息代码:task定时任务，upgrade 升级' ,
`data`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
`log`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
`type`  tinyint(4) NULL DEFAULT 0 COMMENT '0普通消息，1警告' ,
`create_time`  int(11) NULL DEFAULT 0 ,
PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
AUTO_INCREMENT=34392

;

-- ----------------------------
-- Table structure for `sys_task_main`
-- ----------------------------
DROP TABLE IF EXISTS `sys_task_main`;
CREATE TABLE `sys_task_main` (
`id`  int(11) NOT NULL AUTO_INCREMENT ,
`customer_code`  varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' ,
`code`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '任务编码' ,
`task_sn`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '任务编码' ,
`request`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '运行参数' ,
`is_over`  tinyint(3) NULL DEFAULT 0 COMMENT '1开始，2完成，3暂停，4异常' ,
`task_type`  tinyint(3) NULL DEFAULT 0 COMMENT '0普通定时任务，1分发进程任务,2shell命令' ,
`msg`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '异常信息' ,
`path`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '执行地址' ,
`is_set_over`  int(11) NULL DEFAULT 0 COMMENT '进程创建是否结束1未结束' ,
`process_num`  int(11) NULL DEFAULT 3 COMMENT '子任务数量' ,
`error_num`  int(11) NULL DEFAULT 2 ,
`create_time`  int(11) NULL DEFAULT 0 ,
`create_over_time`  int(11) NULL DEFAULT 0 ,
`over_time`  int(11) NULL DEFAULT 0 ,
`start_time`  int(11) NULL DEFAULT 0 COMMENT '任务开始时间' ,
`process`  int(11) NULL DEFAULT 1 COMMENT '子任务并发进程数' ,
`is_auto`  tinyint(3) NULL DEFAULT 1 COMMENT '是否自动服务1自动服务，0手动服务' ,
`is_sys`  tinyint(3) NULL DEFAULT 0 COMMENT '是否系统任务0不是，1是' ,
`child_task_id`  varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' ,
`loop_time`  int(11) NULL DEFAULT 0 COMMENT '循环时间间隔' ,
`check_time`  int(11) NULL DEFAULT 0 ,
`plan_exec_time`  int(11) NULL DEFAULT 0 COMMENT '任务计划执行时间' ,
`plan_over_time`  int(11) NULL DEFAULT 0 COMMENT '计划结束时间' ,
`exec_ip`  varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' ,
`plan_exec_ip`  varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' ,
`log_path`  varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '日志路径' ,
PRIMARY KEY (`id`),
INDEX `_index_check` (`is_over`, `task_type`, `exec_ip`, `check_time`) USING BTREE ,
INDEX `_index_wait` (`is_over`, `task_type`, `plan_exec_time`, `plan_exec_ip`) USING BTREE ,
INDEX `_is_auto` (`is_auto`) USING BTREE 
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
AUTO_INCREMENT=156967

;

-- ----------------------------
-- Table structure for `sys_task_process`
-- ----------------------------
DROP TABLE IF EXISTS `sys_task_process`;
CREATE TABLE `sys_task_process` (
`id`  int(11) NOT NULL AUTO_INCREMENT ,
`process_id`  int(11) NULL DEFAULT 0 ,
`process_sn`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' ,
`create_time`  int(11) NOT NULL ,
`request`  varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' ,
`path`  text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`is_over`  tinyint(3) NULL DEFAULT 0 COMMENT '0未开始 1开始 2完成 3异常' ,
`start_time`  int(11) NULL DEFAULT 0 ,
`run_time`  int(11) NOT NULL DEFAULT 0 COMMENT '运行次数' ,
`task_id`  int(11) NOT NULL DEFAULT 0 COMMENT '主任务id' ,
`exec_num`  tinyint(3) NULL DEFAULT 1 ,
`error_num`  tinyint(3) NULL DEFAULT 3 COMMENT '允许错误次数' ,
`is_get_over`  tinyint(3) NULL DEFAULT 1 COMMENT '0进程不回写完成，1进程完成处理，2回写完成' ,
`over_time`  int(11) NULL DEFAULT 0 COMMENT '结束时间' ,
`check_time`  int(11) NULL DEFAULT 0 ,
`msg`  varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' ,
`is_auto`  tinyint(3) NULL DEFAULT 0 COMMENT '是否自动' ,
PRIMARY KEY (`id`),
INDEX `index_over_task` (`is_over`, `task_id`) USING BTREE ,
INDEX `index_over` (`is_over`, `is_auto`) USING BTREE ,
INDEX `index_process_sn` (`is_over`, `process_sn`) USING BTREE 
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
AUTO_INCREMENT=270

;

-- ----------------------------
-- Auto increment value for `osp_rdsextmanage_db`
-- ----------------------------
ALTER TABLE `osp_rdsextmanage_db` AUTO_INCREMENT=10;

-- ----------------------------
-- Auto increment value for `osp_valueorder_auth`
-- ----------------------------
ALTER TABLE `osp_valueorder_auth` AUTO_INCREMENT=2;

-- ----------------------------
-- Auto increment value for `osp_valueserver`
-- ----------------------------
ALTER TABLE `osp_valueserver` AUTO_INCREMENT=60;

-- ----------------------------
-- Auto increment value for `osp_valueserver_category`
-- ----------------------------
ALTER TABLE `osp_valueserver_category` AUTO_INCREMENT=10;

-- ----------------------------
-- Auto increment value for `osp_valueserver_detail`
-- ----------------------------
ALTER TABLE `osp_valueserver_detail` AUTO_INCREMENT=22;

-- ----------------------------
-- Auto increment value for `osp_vmextmanage_ver`
-- ----------------------------
ALTER TABLE `osp_vmextmanage_ver` AUTO_INCREMENT=3;

-- ----------------------------
-- Auto increment value for `sys_efast_config`
-- ----------------------------
ALTER TABLE `sys_efast_config` AUTO_INCREMENT=1;

-- ----------------------------
-- Auto increment value for `sys_message`
-- ----------------------------
ALTER TABLE `sys_message` AUTO_INCREMENT=34392;

-- ----------------------------
-- Auto increment value for `sys_task_main`
-- ----------------------------
ALTER TABLE `sys_task_main` AUTO_INCREMENT=156967;

-- ----------------------------
-- Auto increment value for `sys_task_process`
-- ----------------------------
ALTER TABLE `sys_task_process` AUTO_INCREMENT=270;




INSERT INTO `sys_action_extend` VALUES ('1010701', 'efast5_Standard');
INSERT INTO `sys_action_extend` VALUES ('1010702', 'efast5_Standard');
INSERT INTO `sys_action_extend` VALUES ('1010703', 'efast5_Standard');
INSERT INTO `sys_action_extend` VALUES ('1010704', 'efast5_Standard');
INSERT INTO `sys_action_extend` VALUES ('1010705', 'efast5_Standard');
INSERT INTO `sys_action_extend` VALUES ('1010706', 'efast5_Standard');
INSERT INTO `sys_action_extend` VALUES ('1010900', 'efast5_Standard');
INSERT INTO `sys_action_extend` VALUES ('1030106', 'efast5_Standard');
INSERT INTO `sys_action_extend` VALUES ('7020100', 'efast5_Standard');


INSERT INTO `sys_task_main` VALUES ('1', '', 'start_customer_task', 'eb41f19f42fd5a1150e4b86d8cfec792', '{\'act\':\'start_customer_task\'}', '0', '0', '', 'common/crontab/task.php act=start_customer_task', '0', '0', '2', '0', '0', '1432716742', '1432716741', '1', '1', '1', '', '60', '1432716741', '1432716801', '0', '121.41.166.13', '', 'logs/crontab/task_1_1.log');
INSERT INTO `sys_task_main` VALUES ('2', '', 'clear_task', '8b1345c99ab6f83bbbee46638f99f43e', '{\'act\':\'clear_task\'}', '0', '0', '', 'common/crontab/task.php act=clear_task', '0', '0', '1', '0', '0', '1432630558', '1432630558', '1', '1', '1', '', '86400', '1432630558', '1432716958', '0', '121.41.166.13', '', 'logs/crontab/task_1_2.log');
INSERT INTO `sys_task_main` VALUES ('3', '', 'monitor_task', '7b16f5fa5d4eee101093117fa79573da', '{\'act\':\'monitor_task\'}', '0', '0', '', 'common/crontab/task.php act=monitor_task', '0', '0', '1', '0', '0', '1432716122', '1432716122', '1', '1', '1', '', '900', '1432716122', '1432717022', '0', '121.41.166.13', '', 'logs/crontab/task_1_3.log');