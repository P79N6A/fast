DROP PROCEDURE IF EXISTS pro_report_base_order_collect;
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
	WHERE ((pay_time >= start_date AND pay_time < end_date) OR (pay_type = 'cod' AND create_time >= start_date AND create_time < end_date)) AND order_status<>3
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
	
	END