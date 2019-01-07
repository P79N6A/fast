<?php 
return array (
  //自动服务设置列表
  	//一键开启服务
	'open_service' => array(
						'order_download_cmd' => 1,//订单下载
						'goods_download_cmd' => 1,//商品下载
						'inv_upload_cmd' 	 => 0,//商品库存同步
						'logistics_upload'   => 1,//网单回写
						'refund_download_cmd' => 1,//退单下载
						'alipay_download_cmd' => 0,//支付宝流水下载
						'update_inv_increment' => 1,//系统库存计算
						'auto_record_combine' => 1,//订单自动合并
						'auto_trans_api_refund' => 1,//自动转退单
						'auto_trans_api_order' => 1,//自动转订单
						'auto_record_unpending' => 1,//订单自动解挂
						'cli_batch_remove_short' => 1,//自动解除缺货
						'fx_goods_download_cmd' => 0,//淘宝分销商品下载
						'fx_order_download_cmd' => 0,//淘宝分销订单下载
						'auto_confirm' => 1,//订单自动解锁、确认
						'auto_trans_api_fenxiao_order' => 0,//淘宝分销订单转单
	),
	//一键关闭服务
	'close_service' => array(
						'order_download_cmd' => 0,//订单下载
						'goods_download_cmd' => 0,//商品下载
						'inv_upload_cmd' 	 => 0,//商品库存同步
						'logistics_upload'   => 0,//网单回写
						'refund_download_cmd' => 0,//退单下载
						'alipay_download_cmd' => 0,//支付宝流水下载
						'update_inv_increment' => 0,//系统库存计算
						'auto_record_combine' => 0,//订单自动合并
						'auto_trans_api_refund' => 0,//自动转退单
						'auto_trans_api_order' => 0,//自动转订单
						'auto_record_unpending' => 0,//订单自动解挂
						'cli_batch_remove_short' => 0,//自动解除缺货
						'fx_goods_download_cmd' => 0,//淘宝分销商品下载
						'fx_order_download_cmd' => 0,//淘宝分销订单下载
						'auto_confirm' => 0,//订单自动解锁、确认
						'auto_trans_api_fenxiao_order' => 0,//淘宝分销订单转单
	),
) ;