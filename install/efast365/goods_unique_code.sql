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
DROP TABLE IF EXISTS `goods_unique_code`;
CREATE TABLE `goods_unique_code` (
  `unique_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `unique_code` varchar(30) DEFAULT '' COMMENT '唯一码',
  `sku` varchar(30) DEFAULT '' COMMENT '系统sku码',
  `out_time` datetime NOT NULL COMMENT '出库时间',
  `status` tinyint(3) unsigned NOT NULL COMMENT '0未出库 1已出库',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`unique_id`),
  UNIQUE KEY `idxu_unique_code` (`unique_code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='商品唯一码';

-- ----------------------------
-- Records of goods_barcode_child
-- ----------------------------
