<?php

/**
 * 虚拟账户账单结构
 * @author auto create
 */
class TopAcctCashJourDto
{
	
	/** 
	 * 虚拟账户科目编号
	 **/
	public $account_id;
	
	/** 
	 * 操作金额
	 **/
	public $amount;
	
	/** 
	 * 虚拟账户流水编号
	 **/
	public $bid;
	
	/** 
	 * 记账时间
	 **/
	public $book_time;
	
	/** 
	 * 备注
	 **/
	public $description;
	
	/** 
	 * 创建时间
	 **/
	public $gmt_create;
	
	/** 
	 * 流水类型:101-可用金充值;102-可用金扣除;103-冻结;104-解冻;105-冻结金充值;106-冻结金扣除
	 **/
	public $journal_type;
	
	/** 
	 * 流水的商家支付宝id
	 **/
	public $other_alipay_id;
	
	/** 
	 * 流水的淘宝支付宝id
	 **/
	public $taobao_alipay_id;	
}
?>