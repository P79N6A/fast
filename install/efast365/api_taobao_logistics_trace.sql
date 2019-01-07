-- ----------------------------
-- Table structure for api_taobao_logistics_trace 淘宝订单物流中转信息
-- ----------------------------
DROP TABLE IF EXISTS `api_taobao_logistics_trace`;
CREATE TABLE `api_taobao_logistics_trace` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `shop_code` varchar(200) NOT NULL COMMENT '店铺code',
        `tid` varchar(30) NOT NULL COMMENT '交易号',
        `company_name` varchar(30) NOT NULL COMMENT '物流公司名称 ',
        `out_sid` varchar(30) NOT NULL COMMENT '物流单号',
        `action` varchar(100) DEFAULT NULL COMMENT '流转节点',
        `status` varchar(50) DEFAULT NULL COMMENT '订单的物流状态',
        `step_info` text COMMENT '流转信息列表',
        `status_time` datetime DEFAULT NULL COMMENT '最后状态更新时间',
        `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后更新时间',
        PRIMARY KEY (`id`),
        UNIQUE KEY `tid_out_sid` (`tid`,`out_sid`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT '淘宝订单物流中转信息';