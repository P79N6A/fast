<?php
$u = array();
/**
 * 增加运营>运营罗盘>运营分析报表
 * by zdd 2015.07.16
 */
$u['FSF-1510'] = array(
	"CREATE  TABLE report_base_goods_collect(
		kh_id INT DEFAULT 0 COMMENT '客户ID',			
		biz_date VARCHAR (10) COMMENT '业务日期',                 
		sale_channel_code VARCHAR (30) COMMENT '销售平台来源',
		sale_channel_name VARCHAR (30) COMMENT '销售平台名称',
		shop_code VARCHAR (50) COMMENT '店铺代码',
		shop_name VARCHAR (50) COMMENT '店铺名称', 
		goods_name VARCHAR (255) DEFAULT 0 COMMENT '商品名称',       
		goods_code VARCHAR (100) DEFAULT 0 COMMENT '商品编码',
		spec1_code VARCHAR (100) DEFAULT 0 COMMENT '商品规格1代码',  
		spec1_name VARCHAR (100) DEFAULT 0 COMMENT '商品规格1名称',       
		spec2_code VARCHAR (100) DEFAULT 0 COMMENT '商品规格2代码',        
		spec2_name VARCHAR (100) DEFAULT 0 COMMENT '商品规格2名称', 
		goods_barcode VARCHAR(100) DEFAULT '0' COMMENT '商品条码',    
		sale_count INT DEFAULT 0 COMMENT '销售数量',                
		sale_money decimal(11,3) DEFAULT '0' COMMENT '销售金额',
		img_url VARCHAR(150) DEFAULT 0 COMMENT '商品图片',     
		brand_name VARCHAR(50) DEFAULT '0' COMMENT '商品品牌',                     
		cat_name VARCHAR(50) DEFAULT 0 COMMENT '商品类别',                                     
		modified timestamp DEFAULT CURRENT_TIMESTAMP COMMENT '时间戳',
		UNIQUE KEY `kh_id_biz_date_shop_code_barcode` (`kh_id`,`biz_date`,shop_code,goods_barcode) 
	);",
	"CREATE  TABLE  report_base_order_collect(
		kh_id INT DEFAULT 0 COMMENT '客户ID',			
		biz_date VARCHAR (10) COMMENT '业务日期',                 
		sale_channel_code VARCHAR (30) COMMENT '销售平台来源',
		sale_channel_name VARCHAR (30) COMMENT '销售平台名称',
		shop_code VARCHAR (50) COMMENT '店铺代码',
		shop_name VARCHAR (50) COMMENT '店铺名称',
		order_sale_count INT DEFAULT 0 COMMENT '订单成交笔数',
		order_sale_money VARCHAR(50) DEFAULT '0' COMMENT '订单成交金额',                    
		order_sale_express_money VARCHAR(50) DEFAULT '0' COMMENT '运费金额',
		goods_sale_count INT DEFAULT 0 COMMENT '商品成交数量',
		refund_apply_count INT DEFAULT 0 COMMENT '退单申请笔数',                    
		refund_apply_money VARCHAR(50) DEFAULT '0' COMMENT '退单申请退款金额',                    
		order_shipping_count INT DEFAULT 0 COMMENT '发货订单笔数',   
		order_shipping_money VARCHAR(50) DEFAULT '0' COMMENT '发货订单金额', 
		order_shipping_goods_count VARCHAR(50) DEFAULT '0' COMMENT '发货商品数量',                                                             
		refund_return_goods_order_count INT DEFAULT 0 COMMENT '已收货退单笔数',
		refund_return_goods_count INT DEFAULT 0 COMMENT '已收货退单商品数',                    
		refund_actual_money VARCHAR(50) DEFAULT '0' COMMENT '退款金额',                    
		order_un_shipping_count INT DEFAULT '0' COMMENT '未发货订单笔数',   
		order_un_shipping_money VARCHAR(50) DEFAULT '0' COMMENT '未发货订单金额',                     
		order_un_shipping_goods_count INT DEFAULT 0 COMMENT '未发货商品数量',        
		modified timestamp DEFAULT CURRENT_TIMESTAMP COMMENT '未发货商品数量',
		UNIQUE KEY `kh_id_biz_date_shop_code` (`kh_id`,`biz_date`,shop_code) 
	); ",
	
	"CREATE  PROCEDURE `pro_report_base_goods_collect`(kh_id INT,biz_date varchar(30) , OUT out_respones INT)
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
	
	END;",
	
	"CREATE PROCEDURE `pro_report_base_order_collect`(kh_id INT,biz_date varchar(30) , OUT out_respones INT)
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
	
	END;",
); 