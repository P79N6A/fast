
DROP TABLE IF EXISTS `goods_generate_tmp`;
CREATE TABLE `goods_generate_tmp` (
  `goods_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `barcode` varchar(64) DEFAULT '' COMMENT '条码对照码',
  `goods_code` varchar(64) DEFAULT '' COMMENT '商品代码',
  `goods_outer_code` varchar(64) DEFAULT '' COMMENT '外部编码',
  `goods_in_code` varchar(64) DEFAULT '' COMMENT '内部编码',
  `goods_name` varchar(128) DEFAULT '' COMMENT '商品名称',
  `goods_short_name` varchar(128) DEFAULT '' COMMENT '商品简称',
  `category_code` varchar(64) DEFAULT '' COMMENT '分类代码',
  `brand_code` varchar(64) DEFAULT '' COMMENT '品牌代码',
  `season_code` varchar(64) DEFAULT '' COMMENT '季节代码',
  `series_code` varchar(64) DEFAULT '' COMMENT '系列代码',
  `unit_code` varchar(64) DEFAULT '' COMMENT '单位代码',
  `sell_code` int(4) DEFAULT '0' COMMENT '销售类型 0-普通1-赠品2-鞋类3-服装类4-配件(统码)5-包装材料',
  `sell_status` int(4) DEFAULT '0' COMMENT '销售状态 1-新品2-正常销售3-过季销售4-停止销售',
  `supplier_code` varchar(128) DEFAULT '' COMMENT '供应商代码',
  `price` varchar(64) DEFAULT '' COMMENT '价格',
  `year` varchar(64) DEFAULT '' COMMENT '年度',
  `weight` varchar(64) DEFAULT '' COMMENT '重量',
  `stuff` varchar(64) DEFAULT '' COMMENT '材料',
  `status` int(4) DEFAULT '1' COMMENT '0：停用 1：启用',
  `is_check` int(4) DEFAULT '1' COMMENT '0：未审核 1：审核',
  `goods_img` varchar(128) DEFAULT '' COMMENT '主图地址',
  `goods_desc` varchar(255) DEFAULT '' COMMENT '详细描述',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`goods_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='生成条码临时商品表';

