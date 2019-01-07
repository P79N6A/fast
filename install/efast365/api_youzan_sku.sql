-- ----------------------------
-- Table structure for api_youzan_sku 有赞商品sku列表
-- ----------------------------
DROP TABLE IF EXISTS `api_youzan_sku`;
CREATE TABLE `api_youzan_sku` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `outer_id` varchar(50) DEFAULT NULL COMMENT '商家编码（商家为Sku设置的外部编号）',
  `sku_id` int(10) DEFAULT '0' COMMENT 'Sku的数字ID',
  `num_iid` int(10) DEFAULT '0' COMMENT 'Sku所属商品的数字编号',
  `quantity` int(10) DEFAULT NULL COMMENT '属于这个Sku的商品的数量',
  `properties_name` varchar(50) DEFAULT NULL COMMENT 'Sku所对应的销售属性的中文名字串，格式如：pid1:vid1:pid_name1:vid_name1;pid2:vid2:pid_name2:vid_name2',
  `properties_name_json` varchar(255) DEFAULT NULL COMMENT 'Sku所对应的销售属性的Json字符串（需另行解析）， 该字段内容与properties_name字段除了格式不一样，内容完全一致',
  `with_hold_quantity` int(10) DEFAULT NULL COMMENT '商品在付款减库存的状态下，该Sku上未付款的订单数量',
  `price` varchar(20) DEFAULT NULL COMMENT '商品的这个Sku的价格；精确到2位小数；单位：元',
  `created` datetime DEFAULT NULL COMMENT 'Sku创建日期，时间格式：yyyy-MM-dd HH:mm:ss',
  `modified` datetime DEFAULT NULL COMMENT 'Sku最后修改日期，时间格式：yyyy-MM-dd HH:mm:ss',
  `is_synckc` tinyint(1) DEFAULT '1' COMMENT '是否库存同步：0 否，1是',
  `sd_id` int(10) DEFAULT NULL,
  `is_relation` int(11) NOT NULL DEFAULT '0',
  `first_insert_time` datetime DEFAULT NULL COMMENT '平台第一次数据下载时间',
  `last_update_time` datetime DEFAULT NULL COMMENT '平台最后一次更新时间',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '数据变更时间戳',
  PRIMARY KEY (`id`),
  KEY `outer_id` (`outer_id`) USING BTREE,
  KEY `sku_id` (`sku_id`) USING BTREE,
  KEY `num_iid` (`num_iid`) USING BTREE,
  KEY `sd_id` (`sd_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '有赞商品sku列表';