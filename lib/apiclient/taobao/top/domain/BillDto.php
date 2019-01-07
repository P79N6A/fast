<?php

/**
 * 账单结构
 * @author auto create
 */
class BillDto
{
	
	/** 
	 * 科目编号
	 **/
	public $account_id;
	
	/** 
	 * 支付宝账户编号
	 **/
	public $alipay_id;
	
	/** 
	 * 支付宝账户名称
	 **/
	public $alipay_mail;
	
	/** 
	 * 支付宝交易号,暂不提供
	 **/
	public $alipay_no;
	
	/** 
	 * 支付宝备注
	 **/
	public $alipay_notice;
	
	/** 
	 * 支付宝商户订单号
	 **/
	public $alipay_outno;
	
	/** 
	 * 账单金额,退款时返回的是负数
	 **/
	public $amount;
	
	/** 
	 * 账单编号
	 **/
	public $bid;
	
	/** 
	 * 业务时间,订单交易完成的时间
	 **/
	public $biz_time;
	
	/** 
	 * 记账时间
	 **/
	public $book_time;
	
	/** 
	 * 创建时间
	 **/
	public $gmt_create;
	
	/** 
	 * 修改时间
	 **/
	public $gmt_modified;
	
	/** 
	 * 所属商品编号
	 **/
	public $num_iid;
	
	/** 
	 * 目标支付宝账户编号
	 **/
	public $obj_alipay_id;
	
	/** 
	 * 目标支付宝账户名称
	 **/
	public $obj_alipay_mail;
	
	/** 
	 * 交易子订单编号
	 **/
	public $order_id;
	
	/** 
	 * 支付时间,收取佣金时支付宝的扣款时间
	 **/
	public $pay_time;
	
	/** 
	 * 0-未支付,1-支付成功,2-支付失败
	 **/
	public $status;
	
	/** 
	 * 账单金额,退款时返回的是负数
	 **/
	public $total_amount;
	
	/** 
	 * 交易订单编号
	 **/
	public $trade_id;	
}
?>