DROP PROCEDURE IF EXISTS pro_report_base_goods_collect;
CREATE  PROCEDURE `pro_report_base_goods_collect`(kh_id INT,biz_date varchar(30) , OUT out_respones INT)
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
	WHERE ((oms_sell_record.pay_time > start_date AND oms_sell_record.pay_time < end_date) OR oms_sell_record.pay_type = 'cod') AND oms_sell_record.order_status<>3 
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
	
	SELECT * FROM report_base_goods_collect;
	
	END