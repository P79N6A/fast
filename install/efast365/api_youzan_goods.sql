-- ----------------------------
-- Table structure for api_youzan_goods 有赞商品信息
-- ----------------------------
DROP TABLE IF EXISTS `api_youzan_goods`;
CREATE TABLE `api_youzan_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sd_id` int(10) NOT NULL COMMENT '关联的商店ID',
  `shop_code` varchar(30) NOT NULL COMMENT 'efast商店代码',
  `num_iid` int(10) DEFAULT NULL COMMENT '商品数字编号',
  `alias` varchar(50) DEFAULT NULL COMMENT '商品别名',
  `title` varchar(50) DEFAULT NULL COMMENT '商品标题',
  `cid` int(10) DEFAULT NULL COMMENT '商品分类的叶子类目id',
  `promotion_cid` int(10) DEFAULT NULL COMMENT '商品推广栏目id',
  `tag_ids` varchar(50) DEFAULT NULL COMMENT '商品标签id串',
  `desc` varchar(255) DEFAULT NULL COMMENT '商品描述',
  `origin_price` varchar(20) DEFAULT NULL COMMENT '显示在“原价”一栏中的信息',
  `outer_id` varchar(50) DEFAULT NULL COMMENT '商品货号（商家为商品设置的外部编号，可与商家外部系统对接）',
  `outer_buy_url` varchar(255) DEFAULT '' COMMENT '商品外部购买链接',
  `buy_quota` int(10) DEFAULT NULL COMMENT '每人限购多少件。0代表无限购，默认为0',
  `created` datetime DEFAULT NULL COMMENT '商品的发布时间',
  `is_virtual` varchar(11) DEFAULT '-1' COMMENT '是否为虚拟商品',
  `is_listing` varchar(10) DEFAULT NULL COMMENT '商品上架状态。true 为已上架，false 为已下架',
  `is_lock` varchar(10) DEFAULT NULL COMMENT '商品是否锁定。true 为已锁定，false 为未锁定',
  `is_used` int(10) DEFAULT 0 COMMENT '是否为二手商品',
  `auto_listing_time` datetime DEFAULT NULL COMMENT '商品定时上架（定时开售）的时间',
  `detail_url` varchar(255) DEFAULT NULL COMMENT '适合wap应用的商品详情url',
  `pic_url` varchar(255) DEFAULT NULL COMMENT '商品主图片地址',
  `pic_thumb_url` varchar(255) DEFAULT NULL COMMENT '商品主图片缩略图地址',
  `num` int(10) DEFAULT NULL COMMENT '商品数量',
  `sold_num` int(10) DEFAULT NULL COMMENT '商品销量',
  `price` varchar(20) DEFAULT NULL COMMENT '商品价格，格式：5.00',
  `post_fee` varchar(20) DEFAULT NULL COMMENT '运费，格式：5.00；',
  `item_qrcodes` varchar(255) DEFAULT NULL COMMENT '商品二维码列表(json)',
  `item_tags` varchar(255) DEFAULT NULL COMMENT '商品标签数据结构(json)',
  `item_type` int(10) DEFAULT 0 COMMENT '商品类型',
  `first_insert_time` datetime DEFAULT NULL COMMENT '平台第一次数据下载时间',
  `last_update_time` datetime DEFAULT NULL COMMENT '平台最后一次更新时间',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '数据变更时间戳',
  PRIMARY KEY (`id`),
  UNIQUE KEY `num_iid` (`num_iid`) USING BTREE,
  KEY `outer_id` (`outer_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '有赞商品信息';