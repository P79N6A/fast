DROP TABLE IF EXISTS `goods_inv_record`;
CREATE TABLE `goods_inv_record` (
  `inv_record_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) DEFAULT '0' COMMENT 'goods_id',
  `goods_code` varchar(64) DEFAULT '' COMMENT '商品代码',
  `spec1_id` int(11) DEFAULT '0',
  `spec1_code` varchar(64) DEFAULT '' COMMENT '规格1',
  `spec2_id` int(11) DEFAULT '0',
  `spec2_code` varchar(64) DEFAULT '' COMMENT '规格2',
  `sku` varchar(128) DEFAULT '' COMMENT 'sku',
  `lof_no` varchar(128) DEFAULT '' COMMENT '批次号',
  `production_date` date DEFAULT NULL COMMENT '生成日期',
  `store_code` varchar(128) DEFAULT '' COMMENT '仓库代码',
  `occupy_type` tinyint(3) DEFAULT '0' COMMENT '库存变更类型',
  `stock_change_num` int(11) DEFAULT '0' COMMENT '在库变动数量',
  `stock_lof_num_before_change` int(11) DEFAULT '0' COMMENT '批次库存变动前',
  `stock_num_before_change` int(11) DEFAULT '0' COMMENT '在库数量（变动前）',
  `stock_num_after_change` int(11) DEFAULT '0' COMMENT '在库数量（变动后）',
  `stock_lof_num_after_change` int(11) DEFAULT '0' COMMENT '批次库存变更后',
  `lock_change_num` int(11) DEFAULT '0' COMMENT '锁定变动数量',
  `lock_num_before_change` int(11) DEFAULT '0' COMMENT '锁定数量（变动前）',
  `lock_num_after_change` int(11) DEFAULT '0' COMMENT '锁定数量（变动后）',
  `lock_lof_num_before_change` int(11) DEFAULT '0' COMMENT '锁定变动前',
  `lock_lof_num_after_change` int(11) DEFAULT '0' COMMENT '锁定变动后',
  `frozen_change_num` int(11) DEFAULT '0' COMMENT '冻结变动数量',
  `frozen_num_before_change` int(11) DEFAULT '0' COMMENT '冻结数量（变动前）',
  `frozen_num_after_change` int(11) DEFAULT '0' COMMENT '冻结数量（变动后）',
  `road_change_num` int(11) DEFAULT '0' COMMENT '在途变动数量',
  `road_num_before_change` int(11) DEFAULT '0' COMMENT '在途数量（变动前）',
  `road_num_after_change` int(11) DEFAULT '0' COMMENT '在途数量（变动后）',
  `record_time` datetime DEFAULT NULL COMMENT '业务时间',
  `object_code` varchar(30) DEFAULT '' COMMENT '对象代码(根据type,0/1/11填写商店代码，2/3填写供应商代码，4/5填写客户代码，6/7/8/9/10/12/13填写仓库代码)',
  `relation_code` varchar(64) DEFAULT '0' COMMENT '关联单据',
  `relation_type` varchar(128) DEFAULT '' COMMENT '变动类型 purchaser/oms/adjust/',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`inv_record_id`),
  KEY `idx_inv` (`store_code`,`goods_code`,`spec1_code`,`spec2_code`) USING BTREE,
  KEY `index1` (`goods_code`),
  KEY `index2` (`sku`),
  KEY `index3` (`store_code`),
  KEY `index4` (`relation_code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='商品库存流水';
