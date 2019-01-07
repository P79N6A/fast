<?php

/**
 * 费用科目
 * @author auto create
 */
class TopAccountDto
{
	
	/** 
	 * 费用科目编码
	 **/
	public $account_code;
	
	/** 
	 * 费用科目编号
	 **/
	public $account_id;
	
	/** 
	 * 费用科目名称
	 **/
	public $account_name;
	
	/** 
	 * 费用科目类型:1-虚拟账户 2-交易 3-交易佣金 4-服务费 5-天猫积分 6-其他
	 **/
	public $account_type;
	
	/** 
	 * 创建时间
	 **/
	public $gmt_create;
	
	/** 
	 * 修改时间
	 **/
	public $gmt_modified;
	
	/** 
	 * 是否订单相关:0-订单无关 1-订单相关
	 **/
	public $related_order;
	
	/** 
	 * 状态:0-不可用 1-可用
	 **/
	public $status;	
}
?>