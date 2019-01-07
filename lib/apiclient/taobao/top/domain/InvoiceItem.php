<?php

/**
 * 电子发票明细
 * @author auto create
 */
class InvoiceItem
{
	
	/** 
	 * 价税合计。(等于sumPrice和tax之和) <span style="color:red;font-weight: bold;">当开红票时，该字段为负数</span>
	 **/
	public $amount;
	
	/** 
	 * 商品IMIE号(不用传，将废弃)
	 **/
	public $imei;
	
	/** 
	 * 发票项目名称（或商品名称）
	 **/
	public $item_name;
	
	/** 
	 * 发票项目编号（或商品编号）
	 **/
	public $item_no;
	
	/** 
	 * 单价，格式：100.00。新版电子发票，折扣行此参数不能传，非折扣行必传；红票、蓝票都为正数
	 **/
	public $price;
	
	/** 
	 * 数量。新版电子发票，折扣行此参数不能传，非折扣行必传； <span style="color:red;font-weight: bold;">当开红票时，该字段需为负数</span>
	 **/
	public $quantity;
	
	/** 
	 * 发票行性质。0表示正常行，1表示折扣行，2表示被折扣行。比如充电器单价100元，折扣10元，则明细为2行，充电器行性质为2，折扣行性质为1。如果充电器没有折扣，则值应为0
	 **/
	public $row_type;
	
	/** 
	 * 规格型号,可选
	 **/
	public $specification;
	
	/** 
	 * 总价，格式：100.00； <span style="color:red;font-weight: bold;">当开红票时，该字段为负数</span>
	 **/
	public $sum_price;
	
	/** 
	 * 税额； <span style="color:red;font-weight: bold;">当开红票时，该字段为负数</span>
	 **/
	public $tax;
	
	/** 
	 * 税率。税率只能为0或0.03或0.04或0.06或0.11或0.13或0.17
	 **/
	public $tax_rate;
	
	/** 
	 * 单位。新版电子发票，折扣行不能传，非折扣行必传
	 **/
	public $unit;
	
	/** 
	 * 0税率标识，只有税率为0的情况才有值，0=出口零税率，1=免税，2=不征收，3=普通零税率
	 **/
	public $zero_rate_flag;	
}
?>