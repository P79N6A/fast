<?php

$u = array();
$u['560'] = array(
    "INSERT INTO sys_action (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('7010112', '7010001', 'url', '待发货订单列表', 'oms/sell_record/wait_shipped_list', '21', '1', '0', '1', '0');"
);

$u['545'] = array(
    "
ALTER TABLE `oms_sell_record`
ADD COLUMN `sign_time_int`  int(11) NULL AFTER `fx_express_money`,
ADD COLUMN `embrace_time`  int(11) NULL DEFAULT 0 COMMENT '揽件时间' AFTER `sign_time_int`;",
"update sys_params set memo='开启后，可查询已发货快递单号物流跟踪信息。此功能由快递鸟免费提供，系统不保证信息的准确性、及时性和完整性'   where param_code='kdniao_enable' ",
);
$u['557'] = array(
    "DELETE FROM base_question_label WHERE question_label_code = 'REFUND';",
    "INSERT INTO base_question_label (question_label_code, `question_label_name`, `is_active`, `is_sys`, `content`, `remark`) VALUES ('FULL_REFUND', '买家申请退款(整单退)', '1', '1', NULL, '订单A在未发货前，平台退单列表有订单A的退单(整单退)。平台退单转单时，系统将自动设问订单A');",
    "INSERT INTO `base_question_label` (`question_label_code`, `question_label_name`, `is_active`, `is_sys`, `content`, `remark`, `lastchanged`) VALUES ('REFUND', '买家申请退款(部分退)', '1', '1', NULL, '订单A在未发货前，平台退单列表有订单A的退单(部分退)。平台退单转单时，系统将自动设问订单A', '2016-08-17 14:03:36');",
);

$u['556'] = array(
    "INSERT INTO `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `memo`) VALUES ('', 'fast_return', 'oms_property', 'S001_110 售后服务单快速入库', 'radio', '[\"关闭\",\"开启\"]', '0', '15', '1-开启 0-关闭', '适用场景：系统发货快递揽货前客户发起退款，仓库可以通过此操作快速入库，开启后售后服务单显示’快速入库‘按钮，点击后售后服务单自动确认且自动收货入库');",
    "UPDATE sys_params SET parent_code='oms_property',param_name='S001_108 发货前订单整单退款，作废订单' WHERE param_code='refund_all_cancel_order';",
    "UPDATE sys_params SET parent_code='oms_property',param_name='S001_107 订单发货，金额存在已付>应付，生成退款类型售后服务单' WHERE param_code='delivery_create_return';",
    "UPDATE sys_params SET parent_code='oms_property',param_name='S001_106 作废订单生成退款类型售后服务单' WHERE param_code='direct_cancel';",
);

$u['558'] = array(
    "INSERT INTO `sys_params` (`param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`) VALUES ('oms_common', '0', '通用', 'group', '通用平台参数', '', '0');",
    "INSERT INTO `sys_params` (`param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `memo`) VALUES ('inventory_sync', 'oms_common', 'TY001  平台商品初始化允许库存同步', 'radio', '[\"关闭\",\"开启\"]', '0', '0.00', '1-开启 0-关闭', '开启参数，系统存在商品库存即会进行库存同步');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('4010304', '4010300', 'act', '删除', 'api/sys/goods/delete', '2', '1', '0', '1', '0');",
);

$u['550'] = array(
    "UPDATE `order_combine_strategy` SET `rule_desc`='已打印的订单参与合并（快递单打印或发货单打印，系统均认为为已打印）' WHERE `rule_code`='order_outo_combine';",
    "INSERT INTO `order_combine_strategy` (`rule_code`, `rule_status_value`, `rule_desc`, `rule_scene_value`) VALUES ('order_combine_is_taofx', '0', '淘分销订单参与合并（同分销商同收货人）', '0');"
);

$u['bug_453'] = array(
    "UPDATE oms_sell_record AS sr,oms_waves_record AS wr,oms_deliver_record as dr SET sr.shipping_status = 3
WHERE sr.sell_record_code = dr.sell_record_code AND dr.waves_record_id = wr.waves_record_id AND sr.shipping_status = 2 AND wr.is_accept = 1 AND wr.is_cancel = 0;",
    "UPDATE `sys_action` SET `action_name`='待验货订单列表' WHERE (`action_code`='oms/sell_record/wait_shipped_list');"
);
$u['543'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('22020100', '22020000', 'url', '宝贝上新', 'api/tb_issue/new_do_list', '1', '1', '0', '1', '0');",
    "ALTER TABLE `api_tb_goods_issue` DROP COLUMN `success_status`;",
    "ALTER TABLE `api_tb_goods_issue` MODIFY COLUMN `issue_status` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '商品发布状态：0-未发布;1-成功;2-失败';",
    "ALTER TABLE `api_tb_goods_issue` ADD COLUMN `is_base_full` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '基础信息是否完整：0-不完整;1-完整' AFTER `desc`;",
    "ALTER TABLE `api_tb_goods_issue` ADD COLUMN `is_item_full` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '类目信息是否完整：0-不完整;1-完整' AFTER `is_base_full`;",
    "ALTER TABLE `api_tb_goods_issue` ADD COLUMN `is_spec_full` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '规格信息是否完整：0-不完整;1-完整' AFTER `is_item_full`;"
);
$u['900'] = array(
    "UPDATE oms_sell_return AS sr, oms_return_package rp  SET sr.sell_return_package_code = rp.return_package_code WHERE   sr.sell_return_code=rp.sell_return_code AND rp.return_order_status<>2;"
);
$u['bug_467'] = array(
    "drop PROCEDURE `pro_report_base_goods_collect`;",
    "


CREATE PROCEDURE `pro_report_base_goods_collect`(kh_id INT,biz_date varchar(30) , OUT out_respones INT)
mypro:BEGIN
	    /* 每日经营商品数据

	        主要记录了每天的订单成交、退款申请、发货、收货以及未发货等情况

	    */

	    declare start_date varchar(30);
	    declare end_date varchar(30);

	    SET start_date = CONCAT(biz_date,' 00:00:00');
	    SET end_date = CONCAT(biz_date,' 23:59:59');

	    SET out_respones = -1;

	/*销售数据插入*/
	INSERT IGNORE INTO report_base_goods_collect
	(kh_id,biz_date,sale_channel_code,shop_code,goods_code,spec1_code,spec2_code,goods_barcode,sale_count,sale_money)
	SELECT kh_id,biz_date,sale_channel_code,shop_code,goods_code,spec1_code,spec2_code,barcode,SUM(num) AS sale_count,SUM(avg_money) AS sale_money FROM
	(
	SELECT tmp.sale_channel_code,tmp.shop_code,oms_sell_record_detail.num,oms_sell_record_detail.goods_code,oms_sell_record_detail.barcode,
	oms_sell_record_detail.avg_money,oms_sell_record_detail.spec1_code,oms_sell_record_detail.spec2_code
	FROM oms_sell_record_detail ,
	(
	SELECT shop_code,sell_record_code,sale_channel_code
	FROM oms_sell_record
	WHERE ((oms_sell_record.pay_time > start_date AND oms_sell_record.pay_time < end_date)

OR ( oms_sell_record.pay_type = 'cod' AND oms_sell_record.record_time > start_date AND oms_sell_record.record_time < end_date )

) AND oms_sell_record.order_status<>3
	) as tmp
	WHERE tmp.sell_record_code = oms_sell_record_detail.sell_record_code
	) AS tmp2 GROUP BY tmp2.shop_code,tmp2.barcode
	ON DUPLICATE KEY UPDATE sale_count = values(sale_count),sale_money = values(sale_money);

	UPDATE report_base_goods_collect,base_goods,base_brand,base_category
	SET
	report_base_goods_collect.goods_name = base_goods.goods_name ,
	report_base_goods_collect.cat_name = base_category.category_name ,
	report_base_goods_collect.brand_name = base_brand.brand_name
	WHERE report_base_goods_collect.goods_code = base_goods.goods_code AND
	base_goods.category_code = base_category.category_code AND
	base_goods.brand_code = base_brand.brand_code AND report_base_goods_collect.kh_id = kh_id AND report_base_goods_collect.biz_date = biz_date;

	END",

    "drop PROCEDURE `pro_report_base_order_collect`;",
    "

CREATE PROCEDURE `pro_report_base_order_collect`(kh_id INT,biz_date varchar(30) , OUT out_respones INT)
mypro:BEGIN

	    declare start_date varchar(30);
	    declare end_date varchar(30);

	    SET start_date = CONCAT(biz_date,' 00:00:00');
	    SET end_date = CONCAT(biz_date,' 23:59:59');

	    SET out_respones = -1;

	/*销售数据插入*/
	INSERT IGNORE INTO report_base_order_collect
	(kh_id,biz_date,sale_channel_code,sale_channel_name,shop_code,shop_name,order_sale_count,order_sale_money,order_sale_express_money)
	SELECT kh_id,biz_date,base_sale_channel.sale_channel_code,base_sale_channel.sale_channel_name,tmp.shop_code,tmp.shop_code,tmp.order_count,tmp.order_sale_money,order_sale_express_money
	FROM base_sale_channel,
	(
	SELECT oms_sell_record.sale_channel_code,oms_sell_record.shop_code,COUNT(1) as order_count,SUM(oms_sell_record.payable_money) as order_sale_money,
	SUM(express_money) as order_sale_express_money
	FROM oms_sell_record
	WHERE ((pay_time >= start_date AND pay_time < end_date) OR (pay_type = 'cod' AND record_time >= start_date AND record_time < end_date)) AND order_status<>3
	GROUP BY oms_sell_record.shop_code
	) as tmp
	WHERE  base_sale_channel.sale_channel_code = tmp.sale_channel_code
	ON DUPLICATE KEY UPDATE order_sale_count = values(order_sale_count),order_sale_money = values(order_sale_money),order_sale_express_money = values(order_sale_express_money);

	/*更新具体商品销售数量*/
	UPDATE report_base_order_collect ,
	(
	SELECT tmp.shop_code,SUM(oms_sell_record_detail.num) as num FROM oms_sell_record_detail ,
	(
	SELECT shop_code,sell_record_code
	FROM oms_sell_record
	WHERE (oms_sell_record.pay_time >= start_date AND oms_sell_record.pay_time < end_date) AND oms_sell_record.order_status<>3
	) as tmp
	WHERE tmp.sell_record_code = oms_sell_record_detail.sell_record_code GROUP BY tmp.shop_code
	) as tmp2
	SET report_base_order_collect.goods_sale_count = tmp2.num
	WHERE report_base_order_collect.kh_id = kh_id AND report_base_order_collect.biz_date = biz_date AND report_base_order_collect.shop_code = tmp2.shop_code;

	/*退单申请笔数与金额*/
	INSERT IGNORE INTO report_base_order_collect
	(kh_id,biz_date,sale_channel_code,sale_channel_name,shop_code,shop_name,refund_apply_count,refund_apply_money)
	SELECT kh_id,biz_date,tmp.sale_channel_code,tmp.sale_channel_code,tmp.shop_code,tmp.shop_code,tmp.refund_apply_count,tmp.refund_apply_money FROM
	(
	SELECT sale_channel_code,shop_code,
	COUNT(1) AS refund_apply_count,SUM(refund_total_fee) AS refund_apply_money FROM oms_sell_return WHERE return_order_status <> 3 AND (create_time > start_date AND create_time < end_date)
	GROUP BY shop_code
	) as tmp
	ON DUPLICATE KEY UPDATE report_base_order_collect.refund_apply_count = values(refund_apply_count),report_base_order_collect.refund_apply_money = values(refund_apply_money)
	;

	/*发货订单笔数和发货金额*/
	INSERT IGNORE INTO report_base_order_collect
	(kh_id,biz_date,sale_channel_code,sale_channel_name,shop_code,shop_name,order_shipping_count,order_shipping_money,order_shipping_goods_count)
	SELECT kh_id,biz_date,base_sale_channel.sale_channel_code,base_sale_channel.sale_channel_name,tmp.shop_code,tmp.shop_code,tmp.order_shipping_count,tmp.order_shipping_money,tmp.order_shipping_goods_count
	FROM base_sale_channel,
	(
	SELECT oms_sell_record.sale_channel_code,oms_sell_record.shop_code,COUNT(1) as order_shipping_count,SUM(oms_sell_record.payable_money) as order_shipping_money,SUM(goods_num) AS order_shipping_goods_count
	FROM oms_sell_record
	WHERE (delivery_time >= start_date AND delivery_time < end_date) AND order_status<>3 AND shipping_status = 4
	GROUP BY oms_sell_record.shop_code
	) as tmp
	WHERE  base_sale_channel.sale_channel_code = tmp.sale_channel_code
	ON DUPLICATE KEY UPDATE order_shipping_count = values(order_shipping_count),order_shipping_money = values(order_shipping_money),order_shipping_goods_count = values(order_shipping_goods_count);

	/*未发货订单笔数和未发货金额*/
	INSERT IGNORE INTO report_base_order_collect
	(kh_id,biz_date,sale_channel_code,sale_channel_name,shop_code,shop_name,order_un_shipping_count,order_un_shipping_money,order_un_shipping_goods_count)
	SELECT kh_id,biz_date,base_sale_channel.sale_channel_code,base_sale_channel.sale_channel_name,tmp.shop_code,tmp.shop_code,tmp.order_un_shipping_count,tmp.order_un_shipping_money,tmp.order_un_shipping_goods_count
	FROM base_sale_channel,
	(
	SELECT oms_sell_record.sale_channel_code,oms_sell_record.shop_code,COUNT(1) as order_un_shipping_count,SUM(oms_sell_record.payable_money) as order_un_shipping_money,SUM(goods_num) AS order_un_shipping_goods_count
	FROM oms_sell_record
	WHERE order_status<>3 AND shipping_status <> 4
	GROUP BY oms_sell_record.shop_code
	) as tmp
	WHERE  base_sale_channel.sale_channel_code = tmp.sale_channel_code
	ON DUPLICATE KEY UPDATE order_un_shipping_count = values(order_un_shipping_count),order_un_shipping_money = values(order_un_shipping_money),order_un_shipping_goods_count = values(order_un_shipping_goods_count);

	/*仓库实际收到包裹数*/
	INSERT IGNORE INTO report_base_order_collect
	(kh_id,biz_date,sale_channel_code,sale_channel_name,shop_code,shop_name,refund_return_goods_order_count)
	SELECT kh_id,biz_date,tmp.sale_channel_code,tmp.sale_channel_code,tmp.shop_code,tmp.shop_code,tmp.refund_return_goods_order_count FROM
	(
	SELECT sale_channel_code,shop_code,
	COUNT(1) AS refund_return_goods_order_count,SUM(refund_total_fee) AS refund_apply_money FROM oms_sell_return
	WHERE return_order_status <> 3 AND return_shipping_status=1 AND (receive_time >= start_date AND receive_time < end_date)
	GROUP BY shop_code
	) as tmp
	ON DUPLICATE KEY UPDATE report_base_order_collect.refund_return_goods_order_count = values(refund_return_goods_order_count)
	;

	/*仓库实际收到包裹数的商品数量*/
	UPDATE report_base_order_collect ,
	(
	SELECT
	oms_sell_return.sale_channel_code,oms_sell_return.shop_code,SUM(oms_sell_return_detail.recv_num) AS refund_return_goods_count FROM oms_sell_return , oms_sell_return_detail
	WHERE oms_sell_return.sell_return_code = oms_sell_return_detail.sell_return_code
	AND oms_sell_return.return_order_status <> 3 AND (oms_sell_return.receive_time > start_date AND oms_sell_return.receive_time < end_date) AND oms_sell_return.return_shipping_status = 1
	GROUP BY shop_code
	) as tmp SET report_base_order_collect.refund_return_goods_count = tmp.refund_return_goods_count
	WHERE report_base_order_collect.kh_id = kh_id AND report_base_order_collect.biz_date = biz_date AND report_base_order_collect.shop_code = tmp.shop_code;

	/*财务退款金额*/
	INSERT IGNORE INTO report_base_order_collect
	(kh_id,biz_date,sale_channel_code,sale_channel_name,shop_code,shop_name,refund_actual_money)
	SELECT kh_id,biz_date,tmp.sale_channel_code,tmp.sale_channel_code,tmp.shop_code,tmp.shop_code,tmp.refund_actual_money FROM
	(
	SELECT sale_channel_code,shop_code,
	SUM(compensate_money + buyer_express_money + adjust_money + return_avg_money) AS refund_actual_money FROM oms_sell_return
	WHERE return_order_status <> 3 AND finance_check_status = 1 AND (finance_confirm_time >= start_date AND finance_confirm_time < end_date)
	GROUP BY shop_code
	) as tmp
	ON DUPLICATE KEY UPDATE report_base_order_collect.refund_actual_money = values(refund_actual_money);

	SET out_respones = 0;

	/*

	SELECT * FROM
	(
	SELECT biz_date,
	SUM(order_sale_count) AS order_sale_count,
	SUM(report_base_order_collect.order_sale_money) AS order_sale_money,
	SUM(report_base_order_collect.order_shipping_goods_count) AS order_shipping_goods_count,
	SUM(report_base_order_collect.order_shipping_count) AS order_shipping_count,
	SUM(report_base_order_collect.order_shipping_money) AS order_shipping_money,
	SUM(report_base_order_collect.refund_apply_count) AS refund_apply_count,
	SUM(report_base_order_collect.refund_apply_money) AS refund_apply_money,
	SUM(report_base_order_collect.refund_return_goods_order_count) AS refund_return_goods_order_count,
	SUM(report_base_order_collect.refund_return_goods_count) AS refund_return_goods_count,
	SUM(report_base_order_collect.refund_actual_money) AS refund_actual_money,
	SUM(report_base_order_collect.order_un_shipping_count) AS order_un_shipping_count,
	SUM(report_base_order_collect.order_un_shipping_goods_count) AS order_un_shipping_goods_count,
	SUM(report_base_order_collect.order_un_shipping_money) AS order_un_shipping_money
	FROM report_base_order_collect
	WHERE biz_date BETWEEN  '2015-07-12' AND '2015-07-13'
	GROUP BY biz_date
	) AS tmp ORDER BY tmp.biz_date DESC

	*/

	END",

);

$u['bug_462'] = array(
 "update order_combine_strategy set rule_status_value=1-rule_status_value  where rule_code='order_outo_combine' AND (rule_status_value=0 or rule_status_value=1);",
 "update order_combine_strategy set rule_scene_value=1-rule_scene_value  where rule_code='order_outo_combine' AND (rule_scene_value=0 or rule_scene_value=1);"
);