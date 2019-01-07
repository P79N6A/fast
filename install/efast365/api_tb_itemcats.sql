
DROP TABLE IF EXISTS `api_tb_itemcats`;
CREATE TABLE `api_tb_itemcats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cid` varchar(50) NOT NULL COMMENT '商品所属类目ID',
  `parent_cid` varchar(50) NOT NULL COMMENT '父类目ID=0时，代表的是一级的类目',
  `name` varchar(50) NOT NULL COMMENT '类目名称',
  `is_parent` varchar(50) NOT NULL COMMENT '该类目是否为父类目(即：该类目是否还有子类目)',
  `status` varchar(50) NOT NULL COMMENT '状态。可选值:normal(正常),deleted(删除)',
  `sort_order` varchar(50) NOT NULL COMMENT '名称次序排列。取值范围:大于零的整数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=406 DEFAULT CHARSET=utf8 COMMENT='商品类目结构(淘宝)';

