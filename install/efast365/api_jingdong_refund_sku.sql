/*
Navicat MySQL Data Transfer

Source Server         : 192.168.164.205
Source Server Version : 50518
Source Host           : 192.168.164.205:3306
Source Database       : efast3.0.2b

Target Server Type    : MYSQL
Target Server Version : 50518
File Encoding         : 65001

Date: 2015-03-26 15:27:49
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for api_jingdong_refund_sku
-- ----------------------------
DROP TABLE IF EXISTS `api_jingdong_refund_sku`;
CREATE TABLE `api_jingdong_refund_sku` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `return_id` varchar(10) NOT NULL,
  `return_type` varchar(10) NOT NULL,
  `order_id` varchar(20) NOT NULL COMMENT '订单ID',
  `sku_id` varchar(20) NOT NULL COMMENT '商品SKU数字编号',
  `price` varchar(10) NOT NULL COMMENT '价格',
  `return_reason` varchar(200) DEFAULT NULL COMMENT '退货原因',
  `sku_name` varchar(250) DEFAULT NULL COMMENT '商品名称',
  `modifid_time` varchar(20) DEFAULT NULL COMMENT '更新时间',
  `return_item_id` varchar(20) NOT NULL,
  `attachment_code` varchar(50) DEFAULT NULL,
  `status` int(5) NOT NULL DEFAULT '0' COMMENT '操作状态：0未执行1执行',
  `error_code` int(11) DEFAULT NULL COMMENT '错误代码',
  `error_msg` varchar(255) DEFAULT NULL,
  `sd_id` int(11) NOT NULL COMMENT '店铺Id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `refund_order_sku__item_id` (`return_id`,`order_id`,`sku_id`,`return_item_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=195 DEFAULT CHARSET=utf8;

