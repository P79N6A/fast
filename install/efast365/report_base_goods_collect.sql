DROP TABLE IF EXISTS report_base_goods_collect;
CREATE TABLE `report_base_goods_collect` (
  `kh_id` int(11) DEFAULT '0' COMMENT '客户ID',
  `biz_date` varchar(10) DEFAULT NULL COMMENT '业务日期',
  `sale_channel_code` varchar(30) DEFAULT NULL COMMENT '销售平台来源',
  `sale_channel_name` varchar(30) DEFAULT NULL COMMENT '销售平台名称',
  `shop_code` varchar(50) DEFAULT NULL COMMENT '店铺代码',
  `shop_name` varchar(50) DEFAULT NULL COMMENT '店铺名称',
  `goods_name` varchar(255) DEFAULT '0' COMMENT '商品名称',
  `goods_code` varchar(100) DEFAULT '0' COMMENT '商品编码',
  `spec1_code` varchar(100) DEFAULT '0' COMMENT '商品规格1代码',
  `spec1_name` varchar(100) DEFAULT '0' COMMENT '商品规格1名称',
  `spec2_code` varchar(100) DEFAULT '0' COMMENT '商品规格2代码',
  `spec2_name` varchar(100) DEFAULT '0' COMMENT '商品规格2名称',
  `goods_barcode` varchar(100) DEFAULT '0' COMMENT '商品条码',
  `sale_count` int(11) DEFAULT '0' COMMENT '销售数量',
  `sale_money` decimal(11,3) DEFAULT '0.000' COMMENT '销售金额',
  `img_url` varchar(150) DEFAULT '0' COMMENT '商品图片',
  `brand_name` varchar(50) DEFAULT '0' COMMENT '商品品牌',
  `cat_name` varchar(50) DEFAULT '0' COMMENT '商品类别',
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '时间戳',
  UNIQUE KEY `kh_id_biz_date_shop_code_barcode` (`kh_id`,`biz_date`,`shop_code`,`goods_barcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;