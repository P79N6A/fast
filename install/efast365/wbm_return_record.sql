DROP TABLE IF EXISTS `wbm_return_record`;
CREATE TABLE `wbm_return_record` (
  `return_record_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `record_code` VARCHAR(64) DEFAULT '' COMMENT '单据编号',
  `record_type_code` VARCHAR(128) DEFAULT '' COMMENT '单据类型代码',
  `distributor_code` VARCHAR(128) DEFAULT '' COMMENT '客户，分销商代码',
  `org_code` VARCHAR(128) DEFAULT '000' COMMENT '渠道代码',
  `user_code` VARCHAR(64) DEFAULT '' COMMENT '业务员代码',
  `order_time` DATETIME DEFAULT NULL COMMENT '下单时间',
  `record_time` DATETIME DEFAULT NULL COMMENT '业务时间',
  `price_type` VARCHAR(64) DEFAULT 'sell_price' COMMENT '价格类型',
  `store_code` VARCHAR(128) DEFAULT '' COMMENT '仓库代码',
  `rebate` DECIMAL(4,3) DEFAULT '1.000' COMMENT '折扣',
  `relation_code` VARCHAR(128) DEFAULT '' COMMENT '关联单号',
  `init_code` VARCHAR(128) DEFAULT '' COMMENT '原单号',
  `refer_time` DATETIME DEFAULT NULL COMMENT '交货时间',
  `pay_time` DATETIME DEFAULT NULL COMMENT '付款期限',
  `brand_code` VARCHAR(128) DEFAULT '' COMMENT '品牌代码',
  `bank_code` VARCHAR(128) DEFAULT '' COMMENT '银行账号代码',
  `num` INT(11) DEFAULT '0' COMMENT '数量',
  `money` DECIMAL(20,3) DEFAULT '0.000' COMMENT '金额',
  `adjust_money` DECIMAL(20,3) DEFAULT '0.000' COMMENT '折让金额',
  `pay_money` DECIMAL(20,3) DEFAULT '0.000' COMMENT '付款金额',
  `is_sure` INT(4) DEFAULT '0' COMMENT '0未确认 1确认',
  `is_cancel` INT(4) DEFAULT '0' COMMENT '0未作废 1作废',
  `is_store_in` INT(4) DEFAULT '0' COMMENT '0未入库 1入库',
  `is_keep_accounts` INT(4) DEFAULT '0' COMMENT '0未记账 1记账',
  `is_settlement` INT(4) DEFAULT '0' COMMENT '0未结算 1结算',
  `is_add_person` VARCHAR(64) DEFAULT '' COMMENT '添加人',
  `is_add_time` DATETIME DEFAULT NULL COMMENT '添加时间',
  `is_edit_person` VARCHAR(64) DEFAULT '' COMMENT '修改人',
  `is_edit_time` DATETIME DEFAULT NULL COMMENT '修改时间',
  `is_sure_person` VARCHAR(64) DEFAULT '' COMMENT '确认人',
  `is_sure_time` DATETIME DEFAULT NULL COMMENT '确认时间',
  `is_store_in_person` VARCHAR(64) DEFAULT '' COMMENT '入库人',
  `is_store_in_time` DATETIME DEFAULT NULL COMMENT '入库时间',
  `is_cancel_person` VARCHAR(64) DEFAULT '' COMMENT '作废人',
  `is_cancel_time` DATETIME DEFAULT NULL COMMENT '作废时间',
  `is_keep_accounts_person` VARCHAR(64) DEFAULT '' COMMENT '记账人',
  `is_keep_accounts_time` DATETIME DEFAULT NULL COMMENT '记账时间',
  `is_settlement_person` VARCHAR(64) DEFAULT '' COMMENT '结算人',
  `is_settlement_time` DATETIME DEFAULT NULL COMMENT '结算时间',
  `is_month_settlement` INT(4) DEFAULT '0' COMMENT '0未月结 1月结',
  `remark` VARCHAR(255) DEFAULT '' COMMENT '备注',
  `lastchanged` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `is_fetch` INT(4) DEFAULT '0' COMMENT '0：正常 1：待处理',
  `trd_id` VARCHAR(64) DEFAULT '' COMMENT '第三方来源GUID',
  `trd_type` VARCHAR(32) DEFAULT '' COMMENT '第三方来源类型:erp/3000/efast',
  `trd_time` VARCHAR(64) DEFAULT '' COMMENT '第三方导入或者更新的时间',
  `trd_upload` TINYINT(4) DEFAULT '0' COMMENT '第三方上传标识位: 0未上传, 1已上传等待处理, 2 上传处理成功 3上传处理失败',
  PRIMARY KEY (`return_record_id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8 COMMENT='商品批发退货单';

