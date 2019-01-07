DROP TABLE IF EXISTS `api_taobao_itemcats`;
CREATE TABLE `api_taobao_itemcats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cid` varchar(50) NOT NULL COMMENT '商品所属类目ID',
  `parent_cid` varchar(50) NOT NULL DEFAULT '0' COMMENT '父类目ID=0时，代表的是一级的类目',
  `name` varchar(255) NOT NULL COMMENT '类目名称',
  `is_parent` tinyint(1) NOT NULL COMMENT '该类目是否为父类目(即：该类目是否还有子类目)',
  `status` varchar(20) DEFAULT NULL COMMENT '状态。可选值:normal(正常),deleted(删除)',
  `sort_order` tinyint(11) DEFAULT NULL COMMENT '排列序号，表示同级类目的展现次序，如数值相等则按名称次序排列。取值范围:大于零的整数',
  `taosir_cat` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否度量衡类目',
  PRIMARY KEY (`id`),
  UNIQUE KEY `cid` (`cid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
