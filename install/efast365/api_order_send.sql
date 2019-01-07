DROP TABLE IF EXISTS `api_order_send`;
CREATE TABLE `api_order_send` (
  `api_order_send_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `status` int(4) DEFAULT '0' COMMENT '0：未上传 1：上传成功 -1：上传失败 -2：上传失败，需重试',
  `source` varchar(64) DEFAULT '' COMMENT '来源 淘宝：taobao，后台：houtai，京东：jingdong，唯品会：weipinhui，当当：dangdang，拍拍：paipai，1号店：yihaodian，亚马逊：yamaxun，凡客：vjia，优购：yougou，聚美优品：jumei，麦网：m18，库巴：coo8，苏宁：suning，名鞋库：scn，阿里巴巴：alibaba，微购物：weigou，口袋通:koudaitong,工行:gonghang,银泰:yintai,走秀网:zouxiu,贝贝网:beibei,蘑菇街:mogujie,拍鞋网:paixie,好乐买:okbuy,乐蜂:lefeng',
  `shop_id` int(11) DEFAULT '0' COMMENT '店铺id',
  `shop_code` varchar(128) DEFAULT '' COMMENT '店铺代码',
  `sell_record_code` varchar(20) NOT NULL DEFAULT '' COMMENT '单据编号(订单号)',
  `tid` varchar(256) DEFAULT '' COMMENT '平台交易号',
  `oid` varchar(256) DEFAULT '' COMMENT '平台交易子订单号',
  `is_split` int(4) DEFAULT '0' COMMENT '表明是否是拆单 1表示拆单 0表示不拆单，默认值0',
  `express_code` varchar(64) DEFAULT '0' COMMENT '配送方式CODE',
  `company_code` varchar(64) DEFAULT '0' COMMENT '物流公司代码',
  `express_no` varchar(128) DEFAULT '' COMMENT '快递单号',
  `error_remark` varchar(255) DEFAULT '' COMMENT '错误原因',
  `send_time` datetime DEFAULT NULL COMMENT '发货时间',
  `upload_time` datetime DEFAULT NULL COMMENT '网单回写时间',
  `last_update_time` datetime DEFAULT NULL COMMENT '最后一次更新订单时间',
  `first_insert_time` datetime DEFAULT NULL COMMENT '第一次插入订单时间',
  PRIMARY KEY (`api_order_send_id`),
  UNIQUE KEY `tid_express_code` (`tid`(255),`express_no`) USING BTREE,
  KEY `index1` (`sell_record_code`,`tid`(255)) USING BTREE,
  KEY `index2` (`status`) USING BTREE,
  KEY `index3` (`shop_code`) USING BTREE,
  KEY `index4` (`status`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='api网单回写表';

