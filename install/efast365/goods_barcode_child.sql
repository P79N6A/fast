/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50516
Source Host           : localhost:3306
Source Database       : efast5.0.1

Target Server Type    : MYSQL
Target Server Version : 50516
File Encoding         : 65001

Date: 2015-03-31 14:35:39
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `goods_barcode_child`
-- ----------------------------
DROP TABLE IF EXISTS `goods_barcode_child`;
CREATE TABLE `goods_barcode_child` (
  `barcode_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `goods_code` varchar(64) DEFAULT '' COMMENT '商品代码',
  `spec1_code` varchar(64) DEFAULT '' COMMENT '规格1',
  `spec2_code` varchar(64) DEFAULT '' COMMENT '规格2',
  `sku` varchar(128) DEFAULT '' COMMENT 'sku',
  `barcode` varchar(255) DEFAULT '' COMMENT '条码',
  `is_main` int(5) DEFAULT '1' COMMENT '1为主子条码',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`barcode_id`),
  UNIQUE KEY `goods_code_spec` (`sku`,`barcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品子条码';

-- ----------------------------
-- Records of goods_barcode_child
-- ----------------------------
