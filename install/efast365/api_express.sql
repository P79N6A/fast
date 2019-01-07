DROP TABLE IF EXISTS `api_express`;
CREATE TABLE `api_express` (
  `api_express_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `source` varchar(64) DEFAULT '' COMMENT '来源 淘宝：taobao，后台：houtai，京东：jingdong，唯品会：weipinhui，当当：dangdang，拍拍：paipai，1号店：yihaodian，亚马逊：yamaxun，凡客：vjia，优购：yougou，聚美优品：jumei，麦网：m18，库巴：coo8，苏宁：suning，名鞋库：scn，阿里巴巴：alibaba，微购物：weigou，口袋通:koudaitong,工行:gonghang,银泰:yintai,走秀网:zouxiu,贝贝网:beibei,蘑菇街:mogujie,拍鞋网:paixie,好乐买:okbuy,乐蜂:lefeng',
  `code` varchar(128) DEFAULT '' COMMENT '代码',
  `name` varchar(128) DEFAULT '' COMMENT '名称',
  `reg` varchar(128) DEFAULT '' COMMENT '正则',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`api_express_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='api淘宝';

alter table api_express add UNIQUE source_and_code(source,code);