
DROP TABLE IF EXISTS `base_goods`;
CREATE TABLE `base_goods` (
  `goods_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `platform_type` varchar(16) DEFAULT '1' COMMENT '来源 1:商城 2：会员门户 3:微信',
  `barcode` varchar(64) DEFAULT '' COMMENT '条码对照码',
  `rule_code` varchar(64) DEFAULT '' COMMENT '条码规则代码',
  `goods_code` varchar(64) DEFAULT '' COMMENT '商品代码',
  `goods_outer_code` varchar(64) DEFAULT '' COMMENT '外部编码',
  `goods_in_code` varchar(64) DEFAULT '' COMMENT '内部编码',
  `goods_name` varchar(128) DEFAULT '' COMMENT '商品名称',
  `goods_short_name` varchar(128) DEFAULT '' COMMENT '商品简称',
  `goods_aliases_name` varchar(128) DEFAULT '' COMMENT '商品别名',
  `category_code` varchar(64) DEFAULT '' COMMENT '分类代码',
  `category_name` varchar(128) DEFAULT NULL,
  `brand_code` varchar(64) DEFAULT '' COMMENT '品牌代码',
  `brand_name` varchar(128) DEFAULT NULL,
  `season_code` varchar(64) DEFAULT '' COMMENT '季节代码',
  `season_name` varchar(128) DEFAULT NULL,
  `series_code` varchar(64) DEFAULT '' COMMENT '系列代码',
  `unit_code` varchar(64) DEFAULT '' COMMENT '单位代码',
  `sort_code` varchar(128) DEFAULT '' COMMENT '品类代码',
  `sell_code` int(4) DEFAULT '0' COMMENT '销售类型 0-普通1-赠品2-鞋类3-服装类4-配件(统码)5-包装材料',
  `sell_status` int(4) DEFAULT '0' COMMENT '销售状态 1-新品2-正常销售3-过季销售4-停止销售',
  `supplier_code` varchar(128) DEFAULT '' COMMENT '供应商代码',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
  `weight` varchar(64) DEFAULT '' COMMENT '重量',
  `diy` int(4) DEFAULT '0' COMMENT '是否组装商品  0：否 1：是',
  `stuff` varchar(64) DEFAULT '' COMMENT '材料',
  `status` int(4) DEFAULT '0' COMMENT '0：启用 1：停用',
  `is_add_person` varchar(64) DEFAULT '' COMMENT '添加人',
  `is_add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `is_edit_person` varchar(64) DEFAULT '' COMMENT '修改人',
  `is_edit_time` datetime DEFAULT NULL COMMENT '修改时间',
  `is_start_person` varchar(64) DEFAULT '' COMMENT '启用人',
  `is_start_time` datetime DEFAULT NULL COMMENT '启用时间',
  `is_stop_person` varchar(64) DEFAULT '' COMMENT '停用人',
  `is_stop_time` datetime DEFAULT NULL COMMENT '停用时间',
  `start_num` int(4) DEFAULT '0' COMMENT '启用数量',
  `goods_img` varchar(128) DEFAULT '' COMMENT '主图地址',
  `goods_thumb_img` varchar(255) DEFAULT '' COMMENT '缩略图地址',
  `goods_desc` varchar(255) DEFAULT '' COMMENT '详细描述',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `tmp_is_generate_barcode` int(1) DEFAULT '1' COMMENT '0:不生成,1:生成',
  `trd_id` varchar(64) DEFAULT '' COMMENT '第三方来源GUID',
  `trd_type` varchar(32) DEFAULT '' COMMENT '第三方来源类型:erp/3000/efast',
  `trd_time` varchar(64) DEFAULT '' COMMENT '第三方导入或者更新的时间',
  `goods_produce_name` varchar(100) DEFAULT '' COMMENT '出厂名称',
  `goods_prop` int(4) DEFAULT '0' COMMENT '商品属性',
  `goods_days` int(11) DEFAULT '0' COMMENT '生产周期',
  `year_code` varchar(64) DEFAULT '' COMMENT '年份代码',
  `year_name` varchar(128) DEFAULT NULL,
  `state` int(4) DEFAULT '0' COMMENT '状态 0-在售 1-在库',
  `cost_price` decimal(20,3) DEFAULT '0.000' COMMENT '成本价',
  `sell_price` decimal(20,3) DEFAULT '0.000' COMMENT '标准售价',
  `sell_price1` decimal(20,3) DEFAULT '0.000' COMMENT '售价1',
  `sell_price2` decimal(20,3) DEFAULT '0.000' COMMENT '售价2',
  `sell_price3` decimal(20,3) DEFAULT '0.000' COMMENT '售价3',
  `sell_price4` decimal(20,3) DEFAULT '0.000' COMMENT '售价4',
  `sell_price5` decimal(20,3) DEFAULT '0.000' COMMENT '售价5',
  `sell_price6` decimal(20,3) DEFAULT '0.000' COMMENT '售价6',
  `buy_price` decimal(20,3) DEFAULT '0.000' COMMENT '标准进价',
  `buy_price1` decimal(20,3) DEFAULT '0.000' COMMENT '进价1',
  `buy_price2` decimal(20,3) DEFAULT '0.000' COMMENT '进价2',
  `buy_price3` decimal(20,3) DEFAULT '0.000' COMMENT '进价3',
  `trade_price` decimal(20,3) DEFAULT '0.000' COMMENT '批发价',
  `purchase_price` decimal(20,3) DEFAULT '0.000' COMMENT '进货价',
  `period_validity` varchar(50) DEFAULT '0' COMMENT '有效期',
  `operating_cycles` varchar(50) DEFAULT '0' COMMENT '使用周期',
  PRIMARY KEY (`goods_id`),
  UNIQUE KEY `goods_code` (`goods_code`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `_statsu` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品';
