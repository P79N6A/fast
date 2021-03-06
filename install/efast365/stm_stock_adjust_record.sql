
DROP TABLE IF EXISTS `stm_stock_adjust_record`;
CREATE TABLE `stm_stock_adjust_record` (
  `stock_adjust_record_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `record_code` varchar(64) DEFAULT '' COMMENT '单据编号',
  `relation_code` varchar(128) DEFAULT '' COMMENT '关联单号',
  `init_code` varchar(128) DEFAULT '' COMMENT '原单号',
  `store_code` varchar(128) DEFAULT '' COMMENT '仓库代码',
  `org_code` varchar(128) DEFAULT '000' COMMENT '渠道代码',
  `user_code` varchar(64) DEFAULT '' COMMENT '业务员代码',
  `record_time` date DEFAULT '0000-00-00' COMMENT '业务时间',
  `order_time` datetime DEFAULT NULL COMMENT '下单日期',
  `adjust_type` varchar(64) DEFAULT '' COMMENT '调整类型',
  `price_type` varchar(64) DEFAULT 'sell_price' COMMENT '价格类型',
  `rebate` decimal(4,3) DEFAULT '1.000' COMMENT '折扣',
  `brand_code` varchar(64) DEFAULT '' COMMENT '品牌代码',
  `num` int(10) DEFAULT '0' COMMENT '调整数',
  `money` decimal(20,3) DEFAULT '0.000' COMMENT '调整金额',
  `is_add_person` varchar(64) DEFAULT '' COMMENT '添加人',
  `is_add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `is_edit_person` varchar(64) DEFAULT '' COMMENT '修改人',
  `is_edit_time` datetime DEFAULT NULL COMMENT '修改时间',
  `is_sure` int(4) DEFAULT '0' COMMENT '是否确认 0未确认 1确认',
  `is_sure_person` varchar(64) DEFAULT '' COMMENT '确认人',
  `is_sure_time` datetime DEFAULT NULL COMMENT '确认时间',
  `is_cancel` int(4) DEFAULT '0' COMMENT '0未作废 1作废',
  `is_cancel_person` varchar(64) DEFAULT '' COMMENT '作废人',
  `is_cancel_time` datetime DEFAULT NULL COMMENT '作废时间',
  `is_check_and_accept` int(4) DEFAULT '0' COMMENT '0未验收 1验收',
  `is_check_and_accept_person` varchar(64) DEFAULT '' COMMENT '验收人',
  `is_check_and_accept_time` datetime DEFAULT NULL COMMENT '验收时间',
  `is_month_settlement` int(4) DEFAULT '0' COMMENT '0未月结 1月结',
  `is_entity_shop` tinyint(4) DEFAULT '0' COMMENT '是否为门店 1:门店',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `is_fetch` int(4) DEFAULT '0' COMMENT '0：正常 1：待处理',
  `trd_id` varchar(64) DEFAULT '' COMMENT '第三方来源GUID',
  `trd_type` varchar(32) DEFAULT '' COMMENT '第三方来源类型:erp/3000/efast',
  `trd_time` varchar(64) DEFAULT '' COMMENT '第三方导入或者更新的时间',
  `is_lock` tinyint(3) DEFAULT '0' COMMENT '0未锁定，1锁定',
  `is_lock_time` int(11) DEFAULT '0' COMMENT '锁定时间',
  `trd_upload` tinyint(4) DEFAULT '0' COMMENT '第三方上传标识位: 0未上传, 1已上传等待处理, 2 上传处理成功 3上传处理失败',
  PRIMARY KEY (`stock_adjust_record_id`),
  UNIQUE KEY `_key` (`record_code`) USING BTREE,
  KEY `_index1` (`relation_code`) USING BTREE,
  KEY `_index2` (`store_code`) USING BTREE,
  KEY `_index3` (`is_sure`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=207 DEFAULT CHARSET=utf8 COMMENT='库存调整单';

