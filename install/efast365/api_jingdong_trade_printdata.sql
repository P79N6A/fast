-- ----------------------------
-- Table structure for api_jingdong_trade_printdata 京东订单打印数据表
-- ----------------------------
DROP TABLE IF EXISTS `api_jingdong_trade_printdata`;
CREATE TABLE `api_jingdong_trade_printdata` (
  `api_print_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(30) DEFAULT '' COMMENT '订单编号',
  `shop_code` varchar(50) DEFAULT '' COMMENT '店铺代码',
  `out_bound_date` varchar(30) DEFAULT '' COMMENT '出库时间',
  `bf_deli_good_glag` varchar(40) DEFAULT '' COMMENT '是否送货前通知',
  `cod_time_name` varchar(30) DEFAULT '' COMMENT '送货时间',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `cky2_name` varchar(30) DEFAULT '' COMMENT '配送中心名称（适用于SOPL、LBP）',
  `sorting_code` varchar(30) DEFAULT '' COMMENT '分拣代码（适用于SOPL、LBP）',
  `create_date` varchar(30) DEFAULT '' COMMENT '订购时间',
  `should_pay` varchar(30) DEFAULT '' COMMENT '支付金额',
  `payment_typeStr` varchar(30) DEFAULT '' COMMENT '支付方式(中文名称)',
  `partner` varchar(255) DEFAULT '' COMMENT '配送站点名称（适用于SOPL、LBP）',
  `generade` varchar(255) DEFAULT '' COMMENT '条形码(base64编码)（适用于SOPL、LBP）',
  `items_count` varchar(30) DEFAULT '' COMMENT '商品总数',
  `cons_name` varchar(30) DEFAULT '' COMMENT '客户姓名',
  `cons_phone` varchar(30) DEFAULT '' COMMENT '客户电话',
  `cons_address` varchar(255) DEFAULT '' COMMENT '客户地址',
  `cons_handset` varchar(30) DEFAULT '' COMMENT '客户手机',
  `freight` varchar(30) DEFAULT '' COMMENT '运费（适用于SOP）',
  `invoice_title` varchar(255) DEFAULT '' COMMENT '发票抬头（适用于SOP）',
  `invoice_type` varchar(30) DEFAULT '' COMMENT '发票类型（适用于SOP）',
  `invoice_content` varchar(255) DEFAULT '' COMMENT '发票内容 （适用于SOP）',
  `pickUpSign_type` varchar(30) DEFAULT '' COMMENT '自提类型（0:非自提 1:地铁自提3:好邻居自提4：社区自提）（适用于SOPL、LBP）',
  `orderLevel_Type` varchar(30) DEFAULT '' COMMENT '高新贵类型(2:贵重 3:高价值 4:贵重且高价值 5: 新用户)（适用于SOPL、LBP）',
  PRIMARY KEY (`api_print_id`),
  UNIQUE KEY `order_id` (`order_id`) USING BTREE,
  KEY `shop_code` (`shop_code`),
  KEY `cky2_name` (`cky2_name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='api京东订单打印数据表';