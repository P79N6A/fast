-- ----------------------------
-- Table structure for api_youzan_img 有赞商品图片列表
-- ----------------------------
DROP TABLE IF EXISTS `api_youzan_img`;
CREATE TABLE `api_youzan_img` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '商品图片的ID',
  `num_iid` int(10) DEFAULT NULL COMMENT '商品数字编号',
  `created` datetime DEFAULT NULL COMMENT '图片创建时间，时间格式：yyyy-MM-dd HH:mm:ss',
  `url` varchar(50) DEFAULT NULL COMMENT '图片链接地址',
  `thumbnail` varchar(255) DEFAULT NULL COMMENT '图片缩略图链接地址',
  `sd_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `num_iid` (`num_iid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '有赞商品图片列表';

