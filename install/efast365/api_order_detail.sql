DROP TABLE IF EXISTS `api_order_detail`;
CREATE TABLE `api_order_detail` (
  `detail_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `source` varchar(15) DEFAULT '' COMMENT '平台来源标识符,淘宝：taobao，后台：houtai，京东：jingdong，唯品会：weipinhui，当当：dangdang，拍拍：paipai，1号店：yihaodian，亚马逊：yamaxun，凡客：vjia，优购：yougou，聚美优品：jumei，麦网：m18，库巴：coo8，苏宁：suning，名鞋库：scn，阿里巴巴：alibaba，微购物：weigou，口袋通:koudaitong,工行:gonghang,银泰:yintai,走秀网:zouxiu,贝贝网:beibei,蘑菇街:mogujie,拍鞋网:paixie,好乐买:okbuy,乐蜂:lefeng',
  `tid` varchar(30) DEFAULT '' COMMENT '平台交易号',
  `oid` varchar(50) DEFAULT '' COMMENT '平台子订单编号',
  `status` tinyint(1) DEFAULT '1' COMMENT '平台子订单状态',
  `return_status` varchar(20) DEFAULT NULL COMMENT '平台退款状态,淘宝平台：refund_status',
  `title` varchar(200) DEFAULT NULL COMMENT '平台商品标题',
  `price` float(7,2) DEFAULT NULL COMMENT '平台商品价格',
  `num` int(11) DEFAULT NULL COMMENT '平台购买数量',
  `goods_code` varchar(20) DEFAULT NULL COMMENT '平台商品外部编码',
  `sku_id` varchar(20) DEFAULT NULL COMMENT '平台sku_id',
  `goods_barcode` varchar(20) DEFAULT NULL COMMENT '平台SKU外部编码, 淘宝平台：outer_iid',
  `total_fee` float(7,2) DEFAULT NULL COMMENT '平台应付金额,应付金额=（商品价格 * 商品数量 + 手工调整金额 - 子订单级订单优惠金额）。精确到2位小数;单位:元。',
  `payment` float(7,2) DEFAULT NULL COMMENT '平台子订单实付金额',
  `discount_fee` float(7,2) DEFAULT NULL COMMENT '平台子订单级订单优惠金额',
  `adjust_fee` float(7,2) DEFAULT NULL COMMENT '平台子订单级手工调整金额',
  `avg_money` float(7,2) DEFAULT NULL COMMENT '平台子订单均摊金额,需要进行运算，即（order_money-express_money）*（avg_money/SUM（avg_money）），最后一个商品用减法',
  `end_time` datetime DEFAULT NULL COMMENT '平台子订单的交易结束时间',
  `consign_time` datetime DEFAULT NULL COMMENT '平台子订单发货时间',
  `express_code` varchar(20) DEFAULT NULL COMMENT '平台子订单的运送方式',
  `express_company_name` varchar(100) DEFAULT NULL COMMENT '平台子订单发货的快递公司名称,淘宝平台：logistics_company',
  `express_no` varchar(20) DEFAULT NULL COMMENT '平台子订单所在包裹的运单号,淘宝平台：invoice_no',
  `pic_path` text COMMENT '平台子订单商品图片的绝对路径,http开头',
  `sku_properties` varchar(50) DEFAULT NULL COMMENT '平台子订单SKU的值,淘宝平台：sku_properties_name',
  `first_insert_time` datetime DEFAULT NULL COMMENT '第一次插入订单时间,数据在本平台的更新时间',
  `last_update_time` datetime DEFAULT NULL COMMENT '最后一次更新订单时间,数据在本平台的更新时间',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `sale_mode` varchar(10) NOT NULL DEFAULT 'stock' COMMENT '销售模式：现货stock，预售presale',
  PRIMARY KEY (`detail_id`),
  UNIQUE KEY `oid` (`oid`) USING BTREE,
  KEY `source` (`source`) USING BTREE,
  KEY `tid` (`tid`) USING BTREE,
  KEY `goods_barcode` (`goods_barcode`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='平台订单明细表';

