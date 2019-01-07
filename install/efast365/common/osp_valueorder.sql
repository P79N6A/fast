
DROP TABLE IF EXISTS `osp_valueorder`;
CREATE TABLE `osp_valueorder` (
  `val_num` varchar(50) NOT NULL COMMENT '增值订购编号',
  `val_channel_id` int(20) DEFAULT NULL COMMENT '销售渠道',
  `val_kh_id` int(20) DEFAULT NULL COMMENT '客户id和osp_kehu表关联',
  `val_cp_id` int(20) DEFAULT NULL COMMENT '关联产品ID',
  `val_serverid` int(20) DEFAULT NULL COMMENT '增值服务id,和osp_valueserver表关联',
  `val_standard_price` decimal(10,0) DEFAULT NULL COMMENT '标准价格',
  `val_cheap_price` decimal(10,0) DEFAULT NULL COMMENT '让利',
  `val_actual_price` decimal(10,0) DEFAULT NULL COMMENT '实际售价',
  `val_hire_limit` int(11) DEFAULT '0' COMMENT '租用周期—单位月',
  `val_seller` int(11) DEFAULT NULL COMMENT '销售经理',
  `val_pay_status` int(10) DEFAULT '0' COMMENT '付款状态',
  `val_paydate` datetime DEFAULT NULL COMMENT '付款日期',
  `val_check_status` int(10) DEFAULT '0' COMMENT '审核状态',
  `val_checkdate` datetime DEFAULT NULL COMMENT '审核日期',
  `val_orderdate` datetime DEFAULT NULL COMMENT '订购日期',
  `val_desc` varchar(255) DEFAULT NULL COMMENT '描述',
  PRIMARY KEY (`val_num`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

