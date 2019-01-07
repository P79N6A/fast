DROP TABLE IF EXISTS `api_taobao_record_detail`;
CREATE TABLE `api_taobao_record_detail` (
  `api_taobao_record_detail_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `record_id` int(11) DEFAULT '0' COMMENT '',
  `tid` varchar(255) DEFAULT '' COMMENT '主订单编号',
  `oid` varchar(255) DEFAULT '' COMMENT '子订单编号',
  `status` varchar(255) DEFAULT '' COMMENT '订单状态',
  `title` varchar(255) DEFAULT '' COMMENT '商品标题',
  `price` varchar(255) DEFAULT '' COMMENT '商品价格。精确到2位小数;单位:元。如:200.07，表示:200元7分',
  `num` varchar(255) DEFAULT '' COMMENT '商品购买数量',
  `num_iid` varchar(255) DEFAULT '' COMMENT '商品数字ID',
  `item_meal_id` varchar(255) DEFAULT '' COMMENT '套餐ID',
  `sku_id` varchar(255) DEFAULT '' COMMENT '商品的最小库存单位Sku的id.可以通过taobao.item.sku.get获取详细的Sku信息',
  `outer_sku_id` varchar(255) DEFAULT '' COMMENT '外部网店自己定义的Sku编号',
  `total_fee` varchar(255) DEFAULT '' COMMENT '应付金额（商品价格 * 商品数量 + 手工调整金额 - 子订单级订单优惠金额）。精确到2位小数;单位:元。如:200.07，表示:200元7分',
  `payment` varchar(255) DEFAULT '' COMMENT '子订单实付金额',
  `discount_fee` varchar(255) DEFAULT '' COMMENT '子订单级订单优惠金额。精确到2位小数;单位:元。如:200.07，表示:200元7分',
  `adjust_fee` varchar(255) DEFAULT '' COMMENT '手工调整金额.格式为:1.01;单位:元;精确到小数点后两位',
  `modified` varchar(255) DEFAULT '' COMMENT '订单修改时间，目前只有taobao.trade.ordersku.update会返回此字段。',
  `sku_properties_name` varchar(255) DEFAULT '' COMMENT 'SKU的值。如：机身颜色:黑色;手机套餐:官方标配',
  `refund_id` varchar(255) DEFAULT '' COMMENT '最近退款ID',
  `is_service_order` varchar(255) DEFAULT '' COMMENT '是否是服务订单，是返回true，否返回false。',
  `end_time` varchar(255) DEFAULT '' COMMENT '子订单的交易结束时间',
  `consign_time` varchar(255) DEFAULT '' COMMENT '子订单发货时间，当卖家对订单进行了多次发货，子订单的发货时间和主订单的发货时间可能不一样了，那么就需要以子订单的时间为准',
  `shipping_type` varchar(255) DEFAULT '' COMMENT '子订单的运送方式',
  `bind_oid` varchar(255) DEFAULT '' COMMENT '捆绑的子订单号，表示该子订单要和捆绑的子订单一起发货，用于卖家子订单捆绑发货',
  `logistics_company` varchar(255) DEFAULT '' COMMENT '子订单发货的快递公司名称',
  `invoice_no` varchar(255) DEFAULT '' COMMENT '子订单所在包裹的运单号',
  `is_daixiao` varchar(255) DEFAULT '' COMMENT '表示订单交易是否含有对应的代销采购单',
  `divide_order_fee` varchar(255) DEFAULT '' COMMENT '分摊之后的实付金额',
  `part_mjz_discount` varchar(255) DEFAULT '' COMMENT '优惠分摊',
  `ticket_outer_id` varchar(255) DEFAULT '' COMMENT '对应门票有效期的外部id',
  `ticket_expdate_key` varchar(255) DEFAULT '' COMMENT '门票有效期的key',
  `item_meal_name` varchar(255) DEFAULT '' COMMENT '套餐的值。如：M8原装电池:便携支架:M8专用座充:莫凡保护袋',
  `pic_path` varchar(255) DEFAULT '' COMMENT '商品图片的绝对路径',
  `seller_nick` varchar(255) DEFAULT '' COMMENT '卖家昵称',
  `buyer_nick` varchar(255) DEFAULT '' COMMENT '买家昵称',
  `refund_status` varchar(255) DEFAULT '' COMMENT '退款状态',
  `outer_iid` varchar(255) DEFAULT '' COMMENT '商家外部编码(可与商家外部系统对接)',
  `buyer_rate` varchar(255) DEFAULT '' COMMENT '买家是否已评价。可选值：true(已评价)，false(未评价)',
  `seller_rate` varchar(255) DEFAULT '' COMMENT '卖家是否已评价。可选值：true(已评价)，false(未评价)',
  `seller_type` varchar(255) DEFAULT '' COMMENT '卖家类型，可选值为：B（商城商家），C（普通卖家）',
  `cid` varchar(255) DEFAULT '' COMMENT '交易商品对应的类目ID',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`api_taobao_record_detail_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='淘宝订单详情';


ALTER TABLE `api_taobao_record_detail` ADD KEY `tid` (`tid`);
ALTER TABLE `api_taobao_record_detail` ADD UNIQUE KEY `oid` (`oid`);