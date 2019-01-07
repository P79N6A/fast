<?php
return array(
	'trade'=>array(
		'oms.order.search.get'=>array(
			'params'=>array(
				'page'=>array('def'=>'1', 'require'=>false, 'desc'=>'页码'),
				'page_size'=>array('def'=>'20', 'require'=>false, 'desc'=>'每页条数'),
				'store_code'=>array('def'=>'', 'require'=>false, 'desc'=>'仓库代码'),
				'order_status'=>array('def'=>'', 'require'=>false, 'desc'=>'订单状态'),
				'shipping_status'=>array('def'=>'', 'require'=>false, 'desc'=>'配送状态'),
				'start_time'=>array('def'=>'', 'require'=>false,'desc'=>'订单创建开始时间'),
				'end_time'=>array('def'=>'', 'require'=>false,'desc'=>'订单创建结束时间'),
				'start_notice_time'=>array('def'=>'', 'require'=>false,'desc'=>'通知配货开始时间'),
				'end_notice_time'=>array('def'=>'', 'require'=>false,'desc'=>'通知配货结束时间'),
	           'start_delivery_time'=>array('def'=>'', 'require'=>false, 'desc'=>'发货开始时间'),
				'end_delivery_time'=>array('def'=>'', 'require'=>false, 'desc'=>'发货结束时间'),	
	           'start_lastchanged'=>array('def'=>'', 'require'=>false,'desc'=>'更新开始时间'),
				'end_lastchanged'=>array('def'=>'', 'require'=>false,'desc'=>'更新结束时间'),
	         'shop_code'=>array('def'=>'', 'require'=>false, 'desc'=>'商店代码'),
			 'shop_code'=>array('def'=>'', 'require'=>false, 'desc'=>'商店代码'),
            'sell_record_code'=>array('def'=>'', 'require'=>false, 'desc'=>'efast退单号'),
				'is_get_shelf'=>array('def'=>'', 'require'=>false, 'desc'=>'是否获取库位'),
			 'deal_code'=>array('def'=>'', 'require'=>false, 'desc'=>'平台交易号'),
			 'receiver_mobile'=>array('def'=>'', 'require'=>false, 'desc'=>'收件人手机号'),
			)
		),
       
		//创建订单
		'oms.api.order.add'=>array(
			'params'=>array(
				'shop_code'=>array('def'=>'', 'require'=>true, 'desc'=>'商店代码'),
				'status'=>array('def'=>'', 'require'=>true, 'desc'=>'是否可以转单'),
				'receiver_country'=>array('def'=>'', 'require'=>false, 'desc'=>'收货人国家'),
				'receiver_province'=>array('def'=>'', 'require'=>true, 'desc'=>'收货人省'),
				'receiver_city'=>array('def'=>'', 'require'=>true, 'desc'=>'收货人市'),
				'receiver_district'=>array('def'=>'', 'require'=>false, 'desc'=>'收货人区'),
				'receiver_street'=>array('def'=>'', 'require'=>false, 'desc'=>'收货人街道'),
				'receiver_addr'=>array('def'=>'', 'require'=>true, 'desc'=>'收货人地址'),
				'receiver_name'=>array('def'=>'', 'require'=>true, 'desc'=>'收货人'),
				'pay_type'=>array('def'=>'', 'require'=>true, 'desc'=>'支付类型'),
               'pay_code'=>array('def'=>'', 'require'=>false, 'desc'=>'支付方式'),
				'alipay_no'=>array('def'=>'', 'require'=>false, 'desc'=>'支付宝交易号'),
				'receiver_mobile'=>array('def'=>'', 'require'=>true, 'desc'=>'收货人手机号'),
				'receiver_phone'=>array('def'=>'', 'require'=>false, 'desc'=>'收货人电话'),
				'deal_code'=>array('def'=>'', 'require'=>true, 'desc'=>'平台交易号'),
				'buyer_remark'=>array('def'=>'', 'require'=>false, 'desc'=>'买家留言'),
				'seller_remark'=>array('def'=>'', 'require'=>false, 'desc'=>'商家备注'),
				'seller_flag'=>array('def'=>'', 'require'=>false, 'desc'=>'订单旗帜'),
				'seller_nick'=>array('def'=>'', 'require'=>false, 'desc'=>'平台卖家昵称'),
				'buyer_name'=>array('def'=>'', 'require'=>true, 'desc'=>'购买人名称'), 
               'record_time'=>array('def'=>'', 'require'=>true, 'desc'=>'下单时间'),
				'order_money'=>array('def'=>'', 'require'=>true, 'desc'=>'实付金额'),
				'goods_num'=>array('def'=>'', 'require'=>true, 'desc'=>'商品数量'),
				'pay_time'=>array('def'=>'', 'require'=>true, 'desc'=>'支付时间'),
				'express_money'=>array('def'=>'', 'require'=>false, 'desc'=>'运费'),
				'buy_money'=>array('def'=>'', 'require'=>false, 'desc'=>'买家已付款'),
				'invoice_title'=>array('def'=>'', 'require'=>false, 'desc'=>'发票抬头'),
				'invoice_content'=>array('def'=>'', 'require'=>false, 'desc'=>'发票内容'),
				'detail'=>array('def'=>'', 'require'=>true,'dese'=>'商品明细'),
			)
		),
       
		//订单拦截
       'oms.order.intercep'=>array(
			'params'=>array(
				'sell_record_code'=>array('def'=>'', 'require'=>true, 'desc'=>'订单号'),
				'desc'=>array('def'=>'', 'require'=>false, 'desc'=>'拦截说明'),
			)
		),

       //订单发货
       'oms.order.send'=>array(
			'params'=>array(
				'sell_record_code'=>array('def'=>'', 'require'=>true, 'desc'=>'订单号'),
	              'express_code'=>array('def'=>'', 'require'=>true, 'desc'=>'快递公司代码'),
				'express_no'=>array('def'=>'', 'require'=>true, 'desc'=>'快递单号'),
			)
		),

       //订单流水通知
      'oms.orderprocess.report'=>array(
			'params'=>array(
				'sell_record_code'=>array('def'=>'', 'require'=>true, 'desc'=>'订单号'),
	              'processstatus'=>array('def'=>'', 'require'=>true, 'desc'=>'单据状态'),
				'operatetime'=>array('def'=>'', 'require'=>true, 'desc'=>'操作时间'),
				'remark'=>array('def'=>'', 'require'=>false, 'desc'=>'备注'),
			)
		),
     //已发货普通订单新增
           'oms.shipped.order.add'=>array(
			'params'=>array(
				'order'=>array('def'=>'', 'require'=>true,'dese'=>'订单数据'),
			)
		),

         //已发货分销订单新增
           'oms.shipped.fxorder.add'=>array(
			'params'=>array(
				'order'=>array('def'=>'', 'require'=>true,'dese'=>'订单数据'),
			)
		),
		'oms.invoice.info.get'=>array(
			'params'=>array(
				'page'=>array('def'=>'1', 'require'=>false, 'desc'=>'页码'),
				'page_size'=>array('def'=>'10', 'require'=>false, 'desc'=>'每页条数'),
				'start_delivery_time'=>array('def'=>'', 'require'=>false, 'desc'=>'发货时间-开始'),
				'end_delivery_time'=>array('def'=>'', 'require'=>false, 'desc'=>'发货时间-结束'),
				'sale_channel_code'=>array('def'=>'', 'require'=>false, 'desc'=>'平台代码'),
				'shop_code'=>array('def'=>'', 'require'=>false,'desc'=>'店铺代码'),
				'nsrmc'=>array('def'=>'', 'require'=>false,'desc'=>'开票主体企业名称'),
				'invoice_sell_status'=>array('def'=>'', 'require'=>false,'desc'=>'0：未开票1：开票中2：已开票'),
				'sell_record_code'=>array('def'=>'', 'require'=>false,'desc'=>'系统订单号'),
	           'deal_code'=>array('def'=>'', 'require'=>false, 'desc'=>'平台交易号'),
				'invoice_nature'=>array('def'=>'', 'require'=>false, 'desc'=>'0：正票1：红票'),	
			)
		),
		 //订单取消
		'oms.order.cancel'=>array(
			'params'=>array(
				'sell_record_code'=>array('def'=>'', 'require'=>true, 'desc'=>'订单号'),
				'cancel_flag'=>array('def'=>'', 'require'=>true, 'desc'=>'取消成功与否(0不能取消，1可以取消)'),
				'desc'=>array('def'=>'', 'require'=>false, 'desc'=>'备注'),
			)
		),
		/*
		'efast.trade.detail.get'=>array(
			'params'=>array(
				'oid'=>array('def'=>'', 'require'=>true, 'desc'=>'交易号或订单号'),
				'feilds'=>array('def'=>'order_sn,shipping_status', 'require'=>true, 'desc'=>'字段列表'),
				'type'=>array('def'=>'0', 'require'=>false, 'desc'=>'不传或0则oid为交易号，1表示oid为订单号'),
			)
		),
		*/
		'catecory'=>'B2C订单类'
	),
	'refund'=>array(

	//退单查询
		'oms.order.return.list'=>array(
			'params'=>array(
				'page'=>array('def'=>'1', 'require'=>false, 'desc'=>'页码'),
				'page_size'=>array('def'=>'20', 'require'=>false, 'desc'=>'每页条数'),
				'store_code'=>array('def'=>'', 'require'=>false, 'desc'=>'仓库代码'),
				'return_type'=>array('def'=>'', 'require'=>false, 'desc'=>'退单类型'),
				'return_order_status'=>array('def'=>'', 'require'=>false, 'desc'=>'退单状态'),
				'return_shipping_status'=>array('def'=>'', 'require'=>false, 'desc'=>'收货状态'),	
				'start_time'=>array('def'=>'', 'require'=>false,'desc'=>'退单开始时间'),
				'end_time'=>array('def'=>'', 'require'=>false,'desc'=>'退单结束时间'),
	           'start_confirm_time'=>array('def'=>'', 'require'=>false, 'desc'=>'确认开始时间'),
				'end_confirm_time'=>array('def'=>'', 'require'=>false, 'desc'=>'确认结束时间'),	

	        'start_refund_time'=>array('def'=>'', 'require'=>false,'desc'=>'退款开始时间'),
				'end_refund_time'=>array('def'=>'', 'require'=>false,'desc'=>'退款结束时间'),
	           'start_lastchanged'=>array('def'=>'', 'require'=>false, 'desc'=>'更新开始时间'),
				'end_lastchanged'=>array('def'=>'', 'require'=>false, 'desc'=>'更新结束时间'),
			 'shop_code'=>array('def'=>'', 'require'=>false, 'desc'=>'商店代码'),
        'sell_return_code'=>array('def'=>'', 'require'=>false, 'desc'=>'efast退单号'),
				'refund_id'=>array('def'=>'', 'require'=>false, 'desc'=>'平台退单号'),
			 'deal_code'=>array('def'=>'', 'require'=>false, 'desc'=>'平台交易号'),
			 'start_receive_time'=>array('def'=>'', 'require'=>false, 'desc'=>'退货入库开始时间'),
			 'end_receive_time'=>array('def'=>'', 'require'=>false, 'desc'=>'退货入库结束时间'),
			)
		),
     //创建退单
       'oms.order.return.add'=>array(
			'params'=>array(
				'shop_code'=>array('def'=>'', 'require'=>true, 'desc'=>'商店代码'),
				'status'=>array('def'=>'', 'require'=>true, 'desc'=>'是否可以转单'),
				'refund_id'=>array('def'=>'', 'require'=>true, 'desc'=>'平台退单号'),
				'deal_code'=>array('def'=>'', 'require'=>true, 'desc'=>'平台交易号'),
				'seller_nick'=>array('def'=>'', 'require'=>false, 'desc'=>'平台卖家昵称'),
				'buyer_nick'=>array('def'=>'', 'require'=>false, 'desc'=>'平台买家昵称'), 
               'has_good_return'=>array('def'=>'', 'require'=>true, 'desc'=>'买家是否需要退货'),
				'refund_fee'=>array('def'=>'', 'require'=>true, 'desc'=>'退还金额'),
				'refund_reason'=>array('def'=>'', 'require'=>true, 'desc'=>'退款原因'),
				'refund_desc'=>array('def'=>'', 'require'=>false, 'desc'=>'退款说明'),
				'refund_express_code'=>array('def'=>'', 'require'=>false, 'desc'=>'物流公司代码'),
				'refund_express_no'=>array('def'=>'', 'require'=>false, 'desc'=>'退货运单号'),
				'create_time'=>array('def'=>'', 'require'=>true, 'desc'=>'申请时间'),
				'return_detail'=>array('def'=>'', 'require'=>true,'dese'=>'商品明细'),
			)
		),
  
     //退单收货
      'oms.return.shipping'=>array(
			'params'=>array(
				'sell_return_code'=>array('def'=>'', 'require'=>true, 'desc'=>'退单号'),
	              'barcode_list'=>array('def'=>'', 'require'=>false,'dese'=>'明细级信息'),
			)
		),

    //异常收货通知
     'oms.package.create'=>array(
			'params'=>array(
				'init_code'=>array('def'=>'', 'require'=>true, 'desc'=>'原单号'),
				 'shop_id'=>array('def'=>'', 'require'=>false, 'desc'=>'店铺id'),
				'buyer_name'=>array('def'=>'', 'require'=>false, 'desc'=>'买家昵称'),
	              'store_code'=>array('def'=>'', 'require'=>true, 'desc'=>'仓库代码'),
				  'return_address'=>array('def'=>'', 'require'=>false, 'desc'=>'退货地址'),
				'express_code'=>array('def'=>'', 'require'=>true, 'desc'=>'物流公司'),
				'express_no'=>array('def'=>'', 'require'=>true, 'desc'=>'快递单号'),
				'sell_record_code'=>array('def'=>'', 'require'=>false, 'desc'=>'原订单号'),
				'return_name'=>array('def'=>'', 'require'=>false, 'desc'=>'退货人'),
				'return_mobile'=>array('def'=>'', 'require'=>false, 'desc'=>'退货人手机'), 
               'return_memo'=>array('def'=>'', 'require'=>false, 'desc'=>'退货原因'),
				'remark'=>array('def'=>'', 'require'=>false, 'desc'=>'备注'),
				'barcode_list'=>array('def'=>'', 'require'=>true,'dese'=>'明细级信息'),
			)
		),
          


		/*
		'efast.trade.detail.get'=>array(
			'params'=>array(
				'oid'=>array('def'=>'', 'require'=>true, 'desc'=>'交易号或订单号'),
				'feilds'=>array('def'=>'order_sn,shipping_status', 'require'=>true, 'desc'=>'字段列表'),
				'type'=>array('def'=>'0', 'require'=>false, 'desc'=>'不传或0则oid为交易号，1表示oid为订单号'),
			)
		),
		*/
		'catecory'=>'B2C退单类'
	),
	'item'=>array(
		'prm.goods.list'=>array(
			'params'=>array(
				'page_no'=>array('def'=>'1', 'require'=>false),
				'page_size'=>array('def'=>'20', 'require'=>false),
				'lastchanged_start'=>array('def'=>'', 'require'=>false, 'desc'=>'更新开始时间'),
				'lastchanged_end'=>array('def'=>'', 'require'=>false, 'desc'=>'更新结束时间'),
				'category_code'=>array('def'=>'', 'require'=>false, 'desc'=>'分类代码'),
				'brand_code'=>array('def'=>'', 'require'=>false, 'desc'=>'品牌代码'),
				'year_code'=>array('def'=>'', 'require'=>false, 'desc'=>'年份代码'),
				'season_code'=>array('def'=>'', 'require'=>false, 'desc'=>'季节代码'),
				'goods_code'=>array('def'=>'', 'require'=>false, 'desc'=>'商品代码'),
				'barcode'=>array('def'=>'', 'require'=>false, 'desc'=>'商品条码'),
				'goods_name'=>array('def'=>'', 'require'=>false, 'desc'=>'商品名称'),
			)
		),
		'prm.goods.add'=>array(
			'params'=>array(
				'goods_code'=>array('def'=>'', 'require'=>true, 'desc'=>'商品编码'),
				'goods_name'=>array('def'=>'', 'require'=>true, 'desc'=>'商品名称'),
				'goods_short_name'=>array('def'=>'', 'require'=>false, 'desc'=>'商品简称'),
				'goods_produce_name'=>array('def'=>'', 'require'=>false, 'desc'=>'出厂名称'),
				'diy'=>array('def'=>'', 'require'=>true, 'desc'=>'是否组装商品0:否|1:是'),
				'category_code'=>array('def'=>'', 'require'=>true, 'desc'=>'分类代码'),
				'category_name'=>array('def'=>'', 'require'=>false, 'desc'=>'分类名称'),
				'brand_code'=>array('def'=>'', 'require'=>true, 'desc'=>'品牌代码'),
				'brand_name'=>array('def'=>'', 'require'=>false, 'desc'=>'品牌名称'),
				'season_code'=>array('def'=>'', 'require'=>false, 'desc'=>'季节代码'),
				'season_name'=>array('def'=>'', 'require'=>false, 'desc'=>'季节名称'),
				'year_code'=>array('def'=>'', 'require'=>false, 'desc'=>'年份代码'),
				'year_name'=>array('def'=>'', 'require'=>false, 'desc'=>'年份名称'),
				'goods_prop'=>array('def'=>'', 'require'=>true, 'desc'=>'商品属性(0:普通|1:补邮|2:赠品)'),
				'state'=>array('def'=>'', 'require'=>true, 'desc'=>'状态(0:在售|1:在库)'),
				'weight'=>array('def'=>'', 'require'=>false, 'desc'=>'重量'),
				'validity_date'=>array('def'=>'', 'require'=>false, 'desc'=>'保质期(月)'),
				'goods_days'=>array('def'=>'', 'require'=>false, 'desc'=>'生产周期(月)'),
				'goods_desc'=>array('def'=>'', 'require'=>false, 'desc'=>'详细描述'),
				'status'=>array('def'=>'', 'require'=>false, 'desc'=>'是否停用(0:停用|1:启用)'),
				'is_add_person'=>array('def'=>'', 'require'=>false, 'desc'=>'添加人'),
				'is_add_time'=>array('def'=>'', 'require'=>false, 'desc'=>'添加时间'),
			)
		),
	
		'prm.goods.update'=>array(
			'params'=>array(
				'goods_code'=>array('def'=>'', 'require'=>true, 'desc'=>'商品编码'),
				'goods_name'=>array('def'=>'', 'require'=>false, 'desc'=>'商品名称'),
				'goods_short_name'=>array('def'=>'', 'require'=>false, 'desc'=>'商品简称'),
				'goods_produce_name'=>array('def'=>'', 'require'=>false, 'desc'=>'出厂名称'),
				'diy'=>array('def'=>'', 'require'=>false, 'desc'=>'是否组装商品0:否|1:是'),
				'category_code'=>array('def'=>'', 'require'=>false, 'desc'=>'分类代码'),
				'brand_code'=>array('def'=>'', 'require'=>false, 'desc'=>'品牌代码'),
				'season_code'=>array('def'=>'', 'require'=>false, 'desc'=>'季节代码'),
				'year_code'=>array('def'=>'', 'require'=>false, 'desc'=>'年份代码'),
				'goods_prop'=>array('def'=>'', 'require'=>false, 'desc'=>'商品属性(0:普通|1:补邮|2:赠品)'),
				'state'=>array('def'=>'', 'require'=>false, 'desc'=>'状态(0:在售|1:在库)'),
				'weight'=>array('def'=>'', 'require'=>false, 'desc'=>'重量'),
				'validity_date'=>array('def'=>'', 'require'=>false, 'desc'=>'保质期(月)'),
				'goods_days'=>array('def'=>'', 'require'=>false, 'desc'=>'生产周期(月)'),
				'goods_desc'=>array('def'=>'', 'require'=>false, 'desc'=>'详细描述'),
				'status'=>array('def'=>'', 'require'=>false, 'desc'=>'是否停用(0:停用|1:启用)'),
				'property_val1'=>array('def'=>'', 'require'=>false, 'desc'=>'扩展属性001'),
				'property_val2'=>array('def'=>'', 'require'=>false, 'desc'=>'扩展属性002'),
			)
		),

		'prm.goods.barcode.add'=>array(
			'params'=>array(
				'goods_code'=>array('def'=>'', 'require'=>true,'desc'=>'商品编码'),
				'spec1_code'=>array('def'=>'', 'require'=>true,'desc'=>'颜色代码'),
				'spec2_code'=>array('def'=>'', 'require'=>true,'desc'=>'尺寸代码'),
				'barcode'=>array('def'=>'', 'require'=>true,'desc'=>'商品条码'),
				'weight'=>array('def'=>'', 'require'=>false,'desc'=>'重量'),
				'price'=>array('def'=>'', 'require'=>false,'desc'=>'吊牌价'),
				'gb_code'=>array('def'=>'', 'require'=>false,'desc'=>'国标码'),
			)
		),
		'prm.goods.sku.update'=>array(
			'params'=>array(
				'goods_code'=>array('def'=>'', 'require'=>true,'desc'=>'商品编码'),
				'spec1_code'=>array('def'=>'', 'require'=>true,'desc'=>'颜色代码'),
				'spec2_code'=>array('def'=>'', 'require'=>true,'desc'=>'尺寸代码'),
				'weight'=>array('def'=>'', 'require'=>false,'desc'=>'重量'),
				'price'=>array('def'=>'', 'require'=>false,'desc'=>'吊牌价'),
				'gb_code'=>array('def'=>'', 'require'=>false,'desc'=>'国标码'),
			)
		),
		'prm.goods.price.update'=>array(
			'params'=>array(
				'goods_code'=>array('def'=>'', 'require'=>true,'desc'=>'商品编码'),
				'sell_price'=>array('def'=>'', 'require'=>true,'desc'=>'标准售价'),
				'cost_price'=>array('def'=>'', 'require'=>false,'desc'=>'成本价'),
				'purchase_price'=>array('def'=>'', 'require'=>false,'desc'=>'进货价'),
				'trade_price'=>array('def'=>'', 'require'=>false,'desc'=>'批发价'),
			)
		),
		'prm.goods.diy.update'=>array(
			'params'=>array(
				'p_barcode'=>array('def'=>'', 'require'=>true,'desc'=>'父商品条码'),
				'barcode'=>array('def'=>'', 'require'=>true,'desc'=>'商品条码'),
				'num'=>array('def'=>'', 'require'=>true,'desc'=>'数量'),
			)
		),
		'prm.goods.spec1.update'=>array(
			'params'=>array(
				'spec1_code'=>array('def'=>'', 'require'=>true,'desc'=>'规格1代码'),
				'spec1_name'=>array('def'=>'', 'require'=>true,'desc'=>'规格1名称'),
				'remark'=>array('def'=>'', 'require'=>false,'desc'=>'备注'),
			)
		),
		'prm.goods.spec2.update'=>array(
			'params'=>array(
				'spec2_code'=>array('def'=>'', 'require'=>true,'desc'=>'规格2代码'),
				'spec2_name'=>array('def'=>'', 'require'=>true,'desc'=>'规格2名称'),
				'remark'=>array('def'=>'', 'require'=>false,'desc'=>'备注'),
			)
		),
		'prm.goods.category.update'=>array(
			'params'=>array(
				'category_code'=>array('def'=>'', 'require'=>true,'desc'=>'分类代码'),
				'category_name'=>array('def'=>'', 'require'=>true,'desc'=>'分类名称'),
				'p_code'=>array('def'=>'', 'require'=>false,'desc'=>'上级分类代码'),
				'remark'=>array('def'=>'', 'require'=>false,'desc'=>'备注'),
			)
		),
		'prm.goods.brand.update'=>array(
			'params'=>array(
				'brand_code'=>array('def'=>'', 'require'=>true,'desc'=>'品牌代码'),
				'brand_name'=>array('def'=>'', 'require'=>true,'desc'=>'品牌名称'),
				'remark'=>array('def'=>'', 'require'=>false,'desc'=>'备注'),
			)
		),
		'base.season.update'=>array(
			'params'=>array(
				'season_code'=>array('def'=>'', 'require'=>true,'desc'=>'季节代码'),
				'season_name'=>array('def'=>'', 'require'=>true,'desc'=>'季节名称'),
			)
		),
		'base.year.update'=>array(
			'params'=>array(
				'year_code'=>array('def'=>'', 'require'=>true,'desc'=>'年份代码'),
				'year_name'=>array('def'=>'', 'require'=>true,'desc'=>'年份名称'),
			)
		),
		'prm.goods.inv.update'=>array(
			'params'=>array(
				'barcode'=>array('def'=>'', 'require'=>true,'desc'=>'商品条码'),
				'store_code'=>array('def'=>'', 'require'=>true,'desc'=>'仓库代码'),
				'stock_num'=>array('def'=>'', 'require'=>true,'desc'=>'实物库存'),
				'lof_no'=>array('def'=>'', 'require'=>false,'desc'=>'批次号'),
				'production_date'=>array('def'=>'', 'require'=>false,'desc'=>'生产日期'),
				'lof_price'=>array('def'=>'', 'require'=>false,'desc'=>'批次价格'),
			)
		),
		'prm.goods.inv.batch.update'=>array(
			'params'=>array(
				'detail'=>array('def'=>'', 'require'=>true,'desc'=>'商品条码'),
			)
		),
		'prm.goods.shop.inv'=>array(
			'params'=>array(
				'page'=>array('def'=>'1', 'require'=>false, 'desc'=>'页码'),
				'page_size'=>array('def'=>'10', 'require'=>false, 'desc'=>'每页条数'),
				'shop_code'=>array('def'=>'', 'require'=>true,'desc'=>'店铺代码'),
				'barcode'=>array('def'=>'', 'require'=>false,'desc'=>'商品条码'),
				'start_time'=>array('def'=>'', 'require'=>false,'desc'=>'更新开始时间'),
				'end_time'=>array('def'=>'', 'require'=>false,'desc'=>'更新结束时间'),
			)
		),
		'prm.goods.inv.get'=>array(
			'params'=>array(
				'page'=>array('def'=>'1', 'require'=>false, 'desc'=>'页码'),
				'page_size'=>array('def'=>'10', 'require'=>false, 'desc'=>'每页条数'),
				'store_code'=>array('def'=>'', 'require'=>false,'desc'=>'仓库代码'),
				'barcode'=>array('def'=>'', 'require'=>false,'desc'=>'商品条码'),
				'start_time'=>array('def'=>'', 'require'=>false,'desc'=>'更新开始时间'),
				'end_time'=>array('def'=>'', 'require'=>false,'desc'=>'更新结束时间'),
			)
		),
		'base.shipping.list.get'=>array(
			'params'=>array(
				'page'=>array('def'=>'1', 'require'=>false, 'desc'=>'页码'),
				'page_size'=>array('def'=>'10', 'require'=>false, 'desc'=>'每页条数'),
			)
		),
		'base.shop.get'=>array(
			'params'=>array(
				'source'=>array('def'=>'', 'require'=>false, 'desc'=>'销售平台'),
				'page'=>array('def'=>'1', 'require'=>false, 'desc'=>'页码'),
				'page_size'=>array('def'=>'10', 'require'=>false, 'desc'=>'每页条数'),
			)
		),
		'base.custom.get'=>array(
			'params'=>array(
				'custom_type'=>array('def'=>'', 'require'=>false, 'desc'=>'分销类型'),
				'start_time'=>array('def'=>'', 'require'=>false, 'desc'=>'创建开始时间'),
				'end_time'=>array('def'=>'', 'require'=>false, 'desc'=>'创建结束时间'),
				'start_lastchanged'=>array('def'=>'', 'require'=>false, 'desc'=>'修改开始时间'),
				'end_lastchanged'=>array('def'=>'', 'require'=>false, 'desc'=>'修改结束时间'),
				'page'=>array('def'=>'1', 'require'=>false, 'desc'=>'页码'),
				'page_size'=>array('def'=>'10', 'require'=>false, 'desc'=>'每页条数'),
			)
		),
		'base.supplier.get'=>array(
			'params'=>array(
				'start_lastchanged'=>array('def'=>'', 'require'=>false, 'desc'=>'修改开始时间'),
				'end_lastchanged'=>array('def'=>'', 'require'=>false, 'desc'=>'修改结束时间'),
				'page'=>array('def'=>'1', 'require'=>false, 'desc'=>'页码'),
				'page_size'=>array('def'=>'10', 'require'=>false, 'desc'=>'每页条数'),
				'pull_mode'=>array('def'=>'', 'require'=>false, 'desc'=>'获取模式（1-全量；2-增量）'),
			)
		),
		/*
		'prm.goods.combo.add'=>array(
			'params'=>array(
				'combo_code'=>array('def'=>'', 'require'=>true, 'desc'=>'套餐编码'),
				'combo_name'=>array('def'=>'', 'require'=>true, 'desc'=>'套餐名称'),
				'combo_desc'=>array('def'=>'', 'require'=>false, 'desc'=>'套餐描述'),
				'price'=>array('def'=>'', 'require'=>false, 'desc'=>'套餐价格'),
				'status'=>array('def'=>'1', 'require'=>false, 'desc'=>'套餐状态'),
				'is_add_time'=>array('def'=>'', 'require'=>false, 'desc'=>'创建时间'),
				'combo_list'=>array('def'=>'', 'require'=>false, 'desc'=>'套餐条码信息[]'),
				'spce1_code'=>array('def'=>'', 'require'=>false, 'desc'=>'套餐规格1'),
				'spce2_code'=>array('def'=>'', 'require'=>false, 'desc'=>'套餐规格2'),
				'combo_barcode'=>array('def'=>'', 'require'=>true, 'desc'=>'套餐条形码'),
				'combo_list'=>array('def'=>'', 'require'=>false, 'desc'=>'套餐规格1'),

			)
		),
		*/
		'goods.barcode.child.add'=>array(
			'params'=>array(
				'barcode'=>array('def'=>'', 'require'=>true, 'desc'=>'商品条形码'),
				'child_barcode_list'=>array('def'=>'', 'require'=>true, 'desc'=>'商品子条码列表'),
			)
		),
		'prm.goods.unique.log.get'=>array(
			'params'=>array(
				'page'=>array('def'=>'1', 'require'=>false, 'desc'=>'页码'),
				'page_size'=>array('def'=>'10', 'require'=>false, 'desc'=>'每页条数'),
                'deal_code_list'=>array('def'=>'', 'require'=>false, 'desc'=>'交易号'),
				'shop_code'=>array('def'=>'', 'require'=>false, 'desc'=>'店铺编码'),
				'record_code'=>array('def'=>'', 'require'=>false, 'desc'=>'单据编号'),
				'unique_code'=>array('def'=>'', 'require'=>false, 'desc'=>'唯一码'),
				'start_time'=>array('def'=>'', 'require'=>false, 'desc'=>'操作时间_开始'),
				'end_time'=>array('def'=>'', 'require'=>false, 'desc'=>'操作时间_结束'),
			)
		),
	'base.brand.get'=>array(
			'params'=>array(
				'page'=>array('def'=>'1', 'require'=>false, 'desc'=>'页码'),
				'page_size'=>array('def'=>'10', 'require'=>false, 'desc'=>'每页显示条数'),
				'start_lastchanged'=>array('def'=>'', 'require'=>false,'desc'=>'更新开始时间'),
				'end_lastchanged'=>array('def'=>'', 'require'=>false,'desc'=>'更新结束时间'),
				'brand_code'=>array('def'=>'', 'require'=>false,'desc'=>'品牌代码'),
				'brand_name'=>array('def'=>'', 'require'=>false,'desc'=>'品牌名称'),
			)
		),
		'catecory'=>'档案类'
	),

	'B2B'=>array(
		'stm.adjust.create'=>array(
			'params'=>array(
				'init_code'=>array('def'=>'', 'require'=>true, 'desc'=>'原单号'),
				'wms_type'=>array('def'=>'', 'require'=>true, 'desc'=>'仓储类型'),
				'store_code'=>array('def'=>'', 'require'=>true, 'desc'=>'仓库代码'),
				'detail'=>array('def'=>'', 'require'=>true, 'desc'=>'明细信息'),
			)
		),
		'pur.record.detail.get'=>array(
			'params'=>array(
				'page'=>array('def'=>'1', 'require'=>false, 'desc'=>'页码'),
				'page_size'=>array('def'=>'10', 'require'=>false, 'desc'=>'每页条数'),
				'record_code'=>array('def'=>'', 'require'=>true, 'desc'=>'采购入库单号'),
			)
		),
		'pur.record.create'=>array(
			'params'=>array(
				'init_code'=>array('def'=>'', 'require'=>false, 'desc'=>'原单号'),
				'relation_code'=>array('def'=>'', 'require'=>true, 'desc'=>'采购通知单号（wms接入时必填）'),
				'record_time'=>array('def'=>'', 'require'=>false, 'desc'=>'业务日期'),
				'store_code'=>array('def'=>'', 'require'=>true, 'desc'=>'仓库代码'),
				'remark'=>array('def'=>'', 'require'=>false, 'desc'=>'备注'),
			)
		),
		'pur.record.produce'=>array(
			'params'=>array(
				'init_code'=>array('def'=>'', 'require'=>false, 'desc'=>'原单号'),
				'relation_code'=>array('def'=>'', 'require'=>false, 'desc'=>'采购通知单号（wms接入时必填）'),
				'produce_mode'=>array('def'=>'', 'require'=>false, 'desc'=>'通知单生成入库单模式（1-按未完成数生成；2-生成空白单，默认为1）'),
				'record_time'=>array('def'=>'', 'require'=>false, 'desc'=>'业务日期'),
				'record_type'=>array('def'=>'', 'require'=>false, 'desc'=>'采购类型代码(默认000-采购进货)'),
				'supplier_code'=>array('def'=>'', 'require'=>true, 'desc'=>'供应商代码'),
				'store_code'=>array('def'=>'', 'require'=>true, 'desc'=>'仓库代码'),
				'rebate'=>array('def'=>'', 'require'=>false, 'desc'=>'折扣(默认为1，值大于0小于等于1)'),
				'remark'=>array('def'=>'', 'require'=>false, 'desc'=>'备注'),
			)
		),
		'pur.return.produce'=>array(
			'params'=>array(
				'relation_code'=>array('def'=>'', 'require'=>false, 'desc'=>'采购退货通知单号'),
				'produce_mode'=>array('def'=>'', 'require'=>false, 'desc'=>'通知单生成退货单模式（1-按未完成数生成；2-生成空白单，默认为1）'),
				'record_time'=>array('def'=>'', 'require'=>false, 'desc'=>'业务日期'),
				'record_type'=>array('def'=>'', 'require'=>false, 'desc'=>'退货类型代码(默认100-采购退货)'),
				'supplier_code'=>array('def'=>'', 'require'=>true, 'desc'=>'供应商代码'),
				'store_code'=>array('def'=>'', 'require'=>true, 'desc'=>'仓库代码'),
				'rebate'=>array('def'=>'', 'require'=>false, 'desc'=>'折扣(默认为1，值大于0小于等于1)'),
				'remark'=>array('def'=>'', 'require'=>false, 'desc'=>'备注'),
			)
		),
		'pur.return.get'=>array(
			'params'=>array(
				'page'=>array('def'=>'1', 'require'=>false, 'desc'=>'页码'),
				'page_size'=>array('def'=>'10', 'require'=>false, 'desc'=>'每页条数'),
				'start_time'=>array('def'=>'', 'require'=>false, 'desc'=>'最后修改时间-开始'),
				'end_time'=>array('def'=>'', 'require'=>false, 'desc'=>'最后修改时间-结束'),
				'relation_code'=>array('def'=>'', 'require'=>false, 'desc'=>'采购退货通知单号'),
				'store_code'=>array('def'=>'', 'require'=>false, 'desc'=>'仓库代码'),
				'supplier_code'=>array('def'=>'', 'require'=>false, 'desc'=>'供应商代码'),
				'is_check'=>array('def'=>'', 'require'=>false, 'desc'=>'单据状态（0未验收 1已验收 2全部）'),
			)
		),
		'stm.stock.adjust.create'=>array(
			'params'=>array(
				'store_code'=>array('def'=>'', 'require'=>true, 'desc'=>'仓库代码'),
				'init_code'=>array('def'=>'', 'require'=>false, 'desc'=>'原单号'),
				'adjust_type'=>array('def'=>'', 'require'=>false, 'desc'=>'调整类'),
				'record_time'=>array('def'=>'', 'require'=>false, 'desc'=>'业务日期'),
				'remark'=>array('def'=>'', 'require'=>false, 'desc'=>'备注'),
			)
		),
		'stm.stock.adjust.update'=>array(
			'params'=>array(
				'record_code'=>array('def'=>'', 'require'=>true, 'desc'=>'调整单号'),
				'barcode_list'=>array('def'=>'', 'require'=>true, 'desc'=>'明细参数'),
			)
		),
		'stm.stock.adjust.accept'=>array(
			'params'=>array(
				'record_code'=>array('def'=>'', 'require'=>true, 'desc'=>'调整单号'),
			)
		),
		'pur.return.notice.detail.get'=>array(
			'params'=>array(
				'page'=>array('def'=>'1', 'require'=>false, 'desc'=>'页码'),
				'page_size'=>array('def'=>'10', 'require'=>false, 'desc'=>'每页条数'),
				'record_code'=>array('def'=>'', 'require'=>true, 'desc'=>'采购退货通知单号'),
			)
		),
		'pur.return.produce'=>array(
			'params'=>array(
				'relation_code'=>array('def'=>'', 'require'=>false, 'desc'=>'采购退货通知单号'),
				'produce_mode'=>array('def'=>'1', 'require'=>false, 'desc'=>'通知单生成退货单模式[1-按未完成数生成；2-生成空白单，默认为1]'),
				'record_type'=>array('def'=>'', 'require'=>false, 'desc'=>'退货类型代码(默认100-采购退货)'),
				'record_time'=>array('def'=>'', 'require'=>false, 'desc'=>'业务日期(默认当天)'),
				'supplier_code'=>array('def'=>'', 'require'=>true, 'desc'=>'供应商代码'),
				'store_code'=>array('def'=>'', 'require'=>true, 'desc'=>'仓库代码'),
				'rebate'=>array('def'=>'1', 'require'=>false, 'desc'=>'折扣(默认为1，值大于0小于等于1)'),
				'remark'=>array('def'=>'', 'require'=>false, 'desc'=>'备注'),
			)
		),
		'pur.return.detail.update'=>array(
			'params'=>array(
				'record_code'=>array('def'=>'', 'require'=>true, 'desc'=>'采购退货单号'),
				'update_mode'=>array('def'=>'1', 'require'=>false, 'desc'=>'明细数量更新方式（0-覆盖；1-累加）'),
				'detail'=>array('def'=>'', 'require'=>true, 'desc'=>'明细'),
			)
		),
		'pur.return.accept'=>array(
			'params'=>array(
				'record_code'=>array('def'=>'', 'require'=>true, 'desc'=>'采购退货单号'),
			)
		),
		'stm.stock.create'=>array(
			'params'=>array(
				'store_code'=>array('def'=>'', 'require'=>true, 'desc'=>'盘点仓库'),
				'record_time'=>array('def'=>'', 'require'=>false, 'desc'=>'盘点时间'),
				'remark'=>array('def'=>'', 'require'=>false, 'desc'=>'备注'),
				'stock_detail'=>array('def'=>'', 'require'=>true, 'desc'=>'明细'),
			)
		),

		'wbm.return.record.get'=>array(
			'params'=>array(
				'page'=>array('def'=>'1', 'require'=>false, 'desc'=>'页码'),
				'page_size'=>array('def'=>'10', 'require'=>false, 'desc'=>'每页条数'),
				'start_time'=>array('def'=>'', 'require'=>false, 'desc'=>'最后修改时间-开始'),
				'end_time'=>array('def'=>'', 'require'=>false, 'desc'=>'最后修改时间-结束'),
				'relation_code'=>array('def'=>'', 'require'=>false, 'desc'=>'批发退货通知单号'),
				'store_code'=>array('def'=>'', 'require'=>false, 'desc'=>'仓库代码'),
				'distributor_code'=>array('def'=>'', 'require'=>false, 'desc'=>'分销商代码'),
				'is_check'=>array('def'=>'', 'require'=>false, 'desc'=>'单据状态（0未验收 1已验收 2全部）'),
			)
		),
     'wbm.record.get'=>array(
			'params'=>array(
				'page'=>array('def'=>'1', 'require'=>false, 'desc'=>'页码'),
				'page_size'=>array('def'=>'10', 'require'=>false, 'desc'=>'每页条数'),
				'start_time'=>array('def'=>'', 'require'=>false, 'desc'=>'最后修改时间-开始'),
				'end_time'=>array('def'=>'', 'require'=>false, 'desc'=>'最后修改时间-结束'),
				'relation_code'=>array('def'=>'', 'require'=>false, 'desc'=>'批发销货通知单号'),
				'store_code'=>array('def'=>'', 'require'=>false, 'desc'=>'仓库代码'),
				'distributor_code'=>array('def'=>'', 'require'=>false, 'desc'=>'分销商代码'),
				'is_check'=>array('def'=>'', 'require'=>false, 'desc'=>'单据状态（0未验收 1已验收 2全部）'),
			)
		),
		'wbm.order.create'=>array(
			'params'=>array(
				'init_code'=>array('def'=>'', 'require'=>false, 'desc'=>'原单号'),
				'relation_code'=>array('def'=>'', 'require'=>true, 'desc'=>'批发通知单号'),
				'record_time'=>array('def'=>'', 'require'=>false, 'desc'=>'业务日期'),
				'store_code'=>array('def'=>'', 'require'=>true, 'desc'=>'仓库代码'),
				'remark'=>array('def'=>'', 'require'=>false, 'desc'=>'备注'),
				'express_code'=>array('def'=>'', 'require'=>false, 'desc'=>'快递公司code'),
				'express_no'=>array('def'=>'', 'require'=>false, 'desc'=>'快递单号'),
				'express_money'=>array('def'=>'', 'require'=>false, 'desc'=>'运费'),
			)
		),
		'wbm.record.produce'=>array(
			'params'=>array(
				'init_code'=>array('def'=>'', 'require'=>false, 'desc'=>'原单号'),
				'relation_code'=>array('def'=>'', 'require'=>false, 'desc'=>'批发通知单号'),
				'produce_mode'=>array('def'=>'', 'require'=>false, 'desc'=>'通知单生成入库单模式（1-按未完成数生成；2-生成空白单，默认为1）'),
				'record_time'=>array('def'=>'', 'require'=>false, 'desc'=>'业务日期'),
				'record_type'=>array('def'=>'', 'require'=>false, 'desc'=>'批发类型(200-JIT发货，系统业务类型中维护)'),
				'distributor_code'=>array('def'=>'', 'require'=>true, 'desc'=>'分销商代码'),
				'store_code'=>array('def'=>'', 'require'=>true, 'desc'=>'仓库代码'),
				'rebate'=>array('def'=>'', 'require'=>false, 'desc'=>'折扣(默认为1，值大于0小于等于1)'),
				'remark'=>array('def'=>'', 'require'=>false, 'desc'=>'备注'),
				'express_code'=>array('def'=>'', 'require'=>false, 'desc'=>'快递公司code'),
				'express_no'=>array('def'=>'', 'require'=>false, 'desc'=>'快递单号'),
				'express_money'=>array('def'=>'', 'require'=>false, 'desc'=>'运费'),
			)
		),
		'wbm.return.create'=>array(
			'params'=>array(
				'init_code'=>array('def'=>'', 'require'=>false, 'desc'=>'原单号'),
				'relation_code'=>array('def'=>'', 'require'=>true, 'desc'=>'批发退货单号'),
				'record_time'=>array('def'=>'', 'require'=>false, 'desc'=>'业务日期'),
				'store_code'=>array('def'=>'', 'require'=>true, 'desc'=>'仓库代码'),
				'remark'=>array('def'=>'', 'require'=>false, 'desc'=>'备注'),
			)
		),
		'pur.notice.list.get'=>array(
			'params'=>array(
				'page'=>array('def'=>'1', 'require'=>false, 'desc'=>'页码'),
				'page_size'=>array('def'=>'10', 'require'=>false, 'desc'=>'每页条数'),
				'start_time'=>array('def'=>'', 'require'=>false, 'desc'=>'最后修改时间-开始'),
				'end_time'=>array('def'=>'', 'require'=>false, 'desc'=>'最后修改时间-结束'),
				'store_code'=>array('def'=>'', 'require'=>false, 'desc'=>'仓库代码'),
				'is_check'=>array('def'=>'', 'require'=>false, 'desc'=>'单据状态（0-未审核；1-已审核）'),
				'is_finish'=>array('def'=>'', 'require'=>false, 'desc'=>'单据状态（0-未完成；1-已完成；2-全部）'),
				'supplier_code'=>array('def'=>'', 'require'=>false, 'desc'=>'供应商代码'),	
			)
		),

	'wbm.order.detail.update'=>array(
			'params'=>array(
				'record_code'=>array('def'=>'', 'require'=>true, 'desc'=>'批发销货单号'),
				'barcode_list'=>array('def'=>'', 'require'=>true, 'desc'=>'明细'),
				
			)
		),
      'wbm.record.detail.update'=>array(
			'params'=>array(
				'record_code'=>array('def'=>'', 'require'=>true, 'desc'=>'批发销货单号'),
				'update_mode'=>array('def'=>'', 'require'=>true, 'desc'=>'明细数量更新方式（0-覆盖；1-累加）'),
				'detail'=>array('def'=>'', 'require'=>true, 'desc'=>'明细'),	
			)
		),

		'catecory'=>'B2B单据类'
	),

	'PDA'=>array(
		'oms.wave.order.get'=>array(
			'params'=>array(
				'page'=>array('def'=>'1', 'require'=>false, 'desc'=>'页码'),
				'page_size'=>array('def'=>'10', 'require'=>false, 'desc'=>'每页条数'),
				'start_time'=>array('def'=>'', 'require'=>false, 'desc'=>'波次单开始时间'),
				'end_time'=>array('def'=>'', 'require'=>false, 'desc'=>'波次单结束时间'),
				'record_code'=>array('def'=>'', 'require'=>false, 'desc'=>'波次单号'),
				'is_accept'=>array('def'=>'', 'require'=>false, 'desc'=>'波次单状态'),
				'store_code'=>array('def'=>'', 'require'=>false, 'desc'=>'波次单仓库代码'),
				'record_type'=>array('def'=>'', 'require'=>false, 'desc'=>'波次单类型'),
				'picker_code'=>array('def'=>'', 'require'=>false, 'desc'=>'拣货员'),
			)
		),
		'base.sotre.list.get'=>array(
			'params'=>array(
				'page'=>array('def'=>'1', 'require'=>false, 'desc'=>'页码'),
				'page_size'=>array('def'=>'10', 'require'=>false, 'desc'=>'每页条数'),
				'user_code'=>array('def'=>'', 'require'=>true, 'desc'=>'用户名'),
				'store_code'=>array('def'=>'', 'require'=>false, 'desc'=>'仓库代码'),
				'store_name'=>array('def'=>'', 'require'=>false, 'desc'=>'仓库名称'),
				'store_type_code'=>array('def'=>'', 'require'=>false, 'desc'=>'仓库类别代码'),
				'lastchanged_start'=>array('def'=>'', 'require'=>false, 'desc'=>'最近更新开始时间'),
				'lastchanged_end'=>array('def'=>'', 'require'=>false, 'desc'=>'最近更新结束时间'),
			)
		),
		'print.struct.get'=>array(
			'params'=>array(
				'struct_type'=>array('def'=>'', 'require'=>true, 'desc'=>'类型'),
				
			)
		),

		'catecory'=>'PDA仓库助手接口'
	),
	'B2C财务'=>array(
		'crm.customer.get'=>array(
			'params'=>array(
				'page'=>array('def'=>'1', 'require'=>false, 'desc'=>'页码'),
				'page_size'=>array('def'=>'10', 'require'=>false, 'desc'=>'每页条数'),
				'buyer_name'=>array('def'=>'', 'require'=>false, 'desc'=>'购买人昵称'),
				'receiver_name'=>array('def'=>'', 'require'=>false, 'desc'=>'收货人'),
				'receiver_mobile'=>array('def'=>'', 'require'=>false, 'desc'=>'收货人手机'),
				'start_lastchanged'=>array('def'=>'', 'require'=>true, 'desc'=>'更新开始时间（默认从当天0点开始）'),
				'end_lastchanged'=>array('def'=>'', 'require'=>true, 'desc'=>'更新结束时间（默认当天24点结束）'),
			)
		),//会员获取
		'catecory'=>'B2C财务类'
	),
);