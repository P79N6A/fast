<?php
$u = array();
$u['FSF-1545'] = array(
"DROP TABLE IF EXISTS `op_express_by_buyer_remark`;",
"CREATE TABLE `op_express_by_buyer_remark` (
  `op_express_id` int(11) NOT NULL AUTO_INCREMENT,
  `express_code` varchar(128) NOT NULL DEFAULT '' COMMENT '配送方式代码',
  `key_word` varchar(255) NOT NULL DEFAULT '' COMMENT '关键字',
  PRIMARY KEY (`op_express_id`),
  UNIQUE KEY `_index` (`express_code`)
) ENGINE=InnoDB AUTO_INCREMENT=10040 DEFAULT CHARSET=utf8;",
"INSERT IGNORE INTO `sys_action` VALUES ('3020102', '3020100', 'act', '买家留言匹配', 'crm/express_strategy/get_op_express_by_remark', '2', '1', '0', '1','0');"    ,
        
"INSERT INTO `op_express_by_buyer_remark` VALUES ('1', 'STO', '申通快递，申通，STO');",
"INSERT INTO `op_express_by_buyer_remark` VALUES ('2', 'ZTO', '中通快递，中通，ZTO');",
"INSERT INTO `op_express_by_buyer_remark` VALUES ('3', 'YTO', '圆通速递，圆通，YTO');",
"INSERT INTO `op_express_by_buyer_remark` VALUES ('4', 'SF', '顺丰速运，顺丰，SF');",
"INSERT INTO `op_express_by_buyer_remark` VALUES ('5', 'YUNDA', '韵达快递，韵达，YUNDA');",
"INSERT INTO `op_express_by_buyer_remark` VALUES ('6', 'TTKDEX', '天天快递，天天');",
"INSERT INTO `op_express_by_buyer_remark` VALUES ('7', 'EMS', 'EMS');",
"INSERT INTO `op_express_by_buyer_remark` VALUES ('8', 'EYB', 'EMS经济快递，EYB');",
"INSERT INTO `op_express_by_buyer_remark` VALUES ('9', 'POST', '中国邮政，邮政，POST');",
"INSERT INTO `op_express_by_buyer_remark` VALUES ('10', 'ZJS', '宅急送，ZJS');",
"INSERT INTO `op_express_by_buyer_remark` VALUES ('11', 'BEST', '百世汇通，汇通，百世');",
"INSERT INTO `op_express_by_buyer_remark` VALUES ('12', 'QFKD', '全峰快递，全峰');",
"INSERT INTO `op_express_by_buyer_remark` VALUES ('13', 'FEDEX', '联邦快递，联邦');",
"INSERT INTO `op_express_by_buyer_remark` VALUES ('14', 'GTO', '国通快递，国通');",
"INSERT INTO `op_express_by_buyer_remark` VALUES ('15', 'CRE', '中铁快运，中铁');",
"INSERT INTO `op_express_by_buyer_remark` VALUES ('16', 'FAST', '快捷快递，快捷');",
"INSERT INTO `op_express_by_buyer_remark` VALUES ('17', 'XB', '新邦物流，新邦');",
"INSERT INTO `op_express_by_buyer_remark` VALUES ('18', 'QRT', '增益速递，增益');",
"INSERT INTO `op_express_by_buyer_remark` VALUES ('19', 'UAPEX', '全一快递，全一');",
"INSERT INTO `op_express_by_buyer_remark` VALUES ('20', 'UC', '优速快递，优速');",
    "ALTER TABLE `base_shop`
	MODIFY COLUMN `authorize_date`  datetime NULL DEFAULT NULL COMMENT '授权截止时间' AFTER `authorize_state`;",    
    
  "INSERT INTO `sys_print_templates` VALUES ('30', '申通快递-电子面单', '1', '1', '0', '0', '1000', '1800', '无', '{\"detail\":\"detail:goods_name|detail:spec1_name|detail:spec2_name|detail:num\",\"deteil_row\":\"1\"}', 'LODOP.PRINT_INITA(117,0,378,680,\"\");\r\nLODOP.SET_PRINT_PAGESIZE(1,1000,1800,\"\");\r\nLODOP.ADD_PRINT_RECT(34,19,1,171,0,1);\r\nLODOP.ADD_PRINT_RECT(11,267,1,174,0,1);\r\nLODOP.ADD_PRINT_TEXTA(\"_txt:标准快递\",4,15,116,30,\"标准快递\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",18);\r\nLODOP.SET_PRINT_STYLEA(0,\"Bold\",1);\r\nLODOP.ADD_PRINT_TEXTA(\"_txt:sto\",8,284,67,25,\"sto\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"微软雅黑\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",21);\r\nLODOP.SET_PRINT_STYLEA(0,\"Bold\",1);\r\nLODOP.ADD_PRINT_TEXTA(\"_txt:服务信息\",41,298,72,20,\"服务信息\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.ADD_PRINT_TEXTA(\"_txt:付款方式:\",63,275,100,20,\"付款方式:\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.ADD_PRINT_TEXTA(\"_txt:是否保价:\",82,275,100,20,\"是否保价:\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.ADD_PRINT_TEXTA(\"_txt:签单返还:\",99,275,100,20,\"签单返还:\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.ADD_PRINT_TEXTA(\"_txt:代收货款:\",115,275,100,20,\"代收货款:\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.ADD_PRINT_TEXTA(\"_txt:发件信息\",35,0,19,58,\"发件信息\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.SET_PRINT_STYLEA(0,\"Alignment\",2);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_name\",34,22,125,20,c[\"receiver_name\"]);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_mobile\",34,150,105,20,c[\"receiver_mobile\"]);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_address\",55,20,235,45,c[\"receiver_address\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.ADD_PRINT_TEXTA(\"_txt:寄件信息\",98,0,19,54,\"寄件信息\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.SET_PRINT_STYLEA(0,\"Alignment\",2);\r\nLODOP.ADD_PRINT_TEXTA(\"sender\",102,145,110,20,c[\"sender\"]);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_shop_name\",102,24,116,20,c[\"sender_shop_name\"]);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_address\",132,24,232,50,c[\"sender_address\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.ADD_PRINT_TEXTA(\"_txt:目的地\",164,0,19,41,\"目的地\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.SET_PRINT_STYLEA(0,\"Alignment\",2);\r\nLODOP.ADD_PRINT_TEXTA(\"_txt:始发：\",213,13,53,20,\"始发：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.ADD_PRINT_RECT(235,268,1,65,0,1);\r\nLODOP.ADD_PRINT_RECT(209,157,1,20,0,1);\r\nLODOP.ADD_PRINT_TEXTA(\"_txt:到达：\",211,167,64,20,\"到达：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.ADD_PRINT_TEXTA(\"_txt:收件签收：\",235,273,100,20,\"收件签收：\");\r\nLODOP.ADD_PRINT_TEXTA(\"_txt:  年  月  日  时\",280,280,85,15,\"  年  月  日  时\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.ADD_PRINT_TEXTA(\"_txt:快件到达收件人地址，经收件人或收件人（寄件人）允许的代收人签...(省略)\",307,1,378,25,\"快件到达收件人地址，经收件人或收件人（寄件人）允许的代收人签...(省略)\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.ADD_PRINT_RECT(348,19,1,228,0,1);\r\nLODOP.ADD_PRINT_TEXTA(\"_txt:发件信息\",348,1,18,56,\"发件信息\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.SET_PRINT_STYLEA(0,\"Alignment\",2);\r\nLODOP.ADD_PRINT_RECT(348,269,1,151,0,1);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_name\",349,23,110,20,c[\"receiver_name\"]);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_mobile\",349,152,89,20,c[\"receiver_mobile\"]);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_address\",369,23,218,38,c[\"receiver_address\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.ADD_PRINT_TEXTA(\"print_time\",184,90,166,20,c[\"print_time\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.ADD_PRINT_TEXTA(\"_txt:寄件信息\",410,0,20,55,\"寄件信息\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.SET_PRINT_STYLEA(0,\"Alignment\",2);\r\nLODOP.ADD_PRINT_TEXTA(\"sender\",407,23,110,20,c[\"sender\"]);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_shop_name\",407,141,100,20,c[\"sender_shop_name\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_address\",428,22,219,46,c[\"sender_address\"]);\r\nLODOP.ADD_PRINT_BARCODEA(\"express_no\",242,33,186,41,\"128A\",c[\"express_no\"]);\r\nLODOP.ADD_PRINT_TEXTA(\"_txt:托寄物(商家自定）\",477,1,16,108,\"托寄物(商家自定）\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.SET_PRINT_STYLEA(0,\"Alignment\",2);\r\nLODOP.ADD_PRINT_TEXTA(\"_txt:订单详情:\",476,24,100,20,\"订单详情:\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.ADD_PRINT_BARCODEA(\"express_no\",613,29,202,39,\"128A\",c[\"express_no\"]);\r\nLODOP.ADD_PRINT_TEXTA(\"_txt:服务信息\",352,301,58,20,\"服务信息\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",7);\r\nLODOP.ADD_PRINT_TEXTA(\"_txt:付款方式：\",369,275,100,20,\"付款方式：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.ADD_PRINT_TEXTA(\"_txt:是否保价：\",388,275,100,20,\"是否保价：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.ADD_PRINT_TEXTA(\"_txt:签单返还：\",407,275,100,20,\"签单返还：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.ADD_PRINT_TEXTA(\"_txt:代收货款:\",427,275,100,20,\"代收货款:\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.ADD_PRINT_TEXTA(\"detail:goods_name|detail:spec1_name|detail:spec1_name|detail:num\",500,25,214,86,\"商品名称 规格1 规格2 数量\");\r\nLODOP.ADD_PRINT_TEXTA(\"_txt:寄件日期:\",184,24,63,20,\"寄件日期:\");\r\n', '{\"LODOP.ADD_PRINT_TEXTA(\\\"detail:goods_name|detail:spec1_name|detail:spec1_name|detail:num\\\",500,25,214,86,\\\"\\u5546\\u54c1\\u540d\\u79f0 \\u89c4\\u683c1 \\u89c4\\u683c2 \\u6570\\u91cf\\\");\":\"var detailstr=\\\"\\\";\\nfor(var i in c[\\\"detail\\\"]){\\nvar detail=c[\\\"detail\\\"][i];\\ndetailstr+=\\\"\\\"+detail[\\\"goods_name\\\"]+\\\"  \\\"+detail[\\\"spec1_name\\\"]+\\\"  \\u89c4\\u683c2 \\\"+detail[\\\"num\\\"]+\\\" \\\"\\ndetailstr+=\\\"\\\\n\\\"\\n}\\nLODOP.ADD_PRINT_TEXTA(\\\"detail:goods_name|detail:spec1_name|detail:spec1_name|detail:num\\\",500,25,214,86,detailstr);\"}', '');",
    
    
    );

 
/**
 * 赠品策略明细表里加3个字段
 * by dfr 2015.07.28
 */
$u['FSF-1539'] = array(
		"
		ALTER TABLE `op_gift_strategy_detail`
		drop COLUMN  gift_num,buy_num;",
		"ALTER TABLE `op_gift_strategy_detail`
		ADD COLUMN  `gift_num` INT(11) NOT NULL DEFAULT '1' COMMENT '赠送数量';",
		"ALTER TABLE `op_gift_strategy_detail`
		ADD COLUMN  `buy_num` INT(11) NOT NULL DEFAULT '1' COMMENT '购买数量';",
		"ALTER TABLE `op_gift_strategy_detail`
		ADD COLUMN  `give_way` tinyint(3) NOT NULL DEFAULT '1' COMMENT '赠送方式0固定送赠品，1随机送赠品';",
		"ALTER TABLE `op_gift_strategy_detail`
		ADD COLUMN  `goods_condition` tinyint(3) NOT NULL DEFAULT '1' COMMENT '商品条件0固定商品条件，1随机商品条件';",
		"ALTER TABLE `op_gift_strategy_detail`
		ADD COLUMN `is_mutex` tinyint(3) NOT NULL DEFAULT '0' COMMENT '0互斥，1互溶';",
		"ALTER TABLE `op_gift_strategy`
		ADD COLUMN `is_check` INT(4) DEFAULT '0' COMMENT '0未审核 1审核';
		"
);

/**
 * 生成退单时：将原单的状态记录到退单表里
 * by dfr 2015.07.30
 */
$u['FSF-1543'] = array(
    "
    ALTER TABLE `oms_sell_return`   
    ADD COLUMN `relation_shipping_status` TINYINT(11) DEFAULT 0  NOT NULL   COMMENT '原单：配送状态.0-未发货 1-已通知配货 2-拣货中(已分配拣货任务) 3-已完成拣货 4-已发货' AFTER `return_pay_code`;
    ",
    "
    UPDATE `oms_sell_return` ost, `oms_sell_record` osr SET ost.`relation_shipping_status`=osr.shipping_status WHERE ost.sell_record_code=osr.sell_record_code
    "
);

/**
 * FSF-1540[20150807需求]支付宝订购流程实现
 * by hhl 2015.08.03
 */

$u['FSF-1540'] = array(
		"ALTER TABLE `base_shop` ADD COLUMN `alipay_order_status`  tinyint(1) NULL DEFAULT 0 COMMENT '支付宝订购状态 0-未订购，1-订购';",
);

/**
 *FSF-1332[20150814需求]系统api_order表中alipay_no的字段值只有20位,不能满足现有的支付宝交易号位数,需要改长.
 * by hhl 2015.08.05
 */

$u['FSF-1332'] = array(
	"alter table api_order modify column alipay_no varchar(40);",
);

