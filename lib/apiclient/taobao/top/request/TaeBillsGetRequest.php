<?php
/**
 * TOP API: taobao.tae.bills.get request
 * 
 * @author auto create
 * @since 1.0, 2017.04.06
 */
class TaeBillsGetRequest
{
	/** 
	 * 页数,建议不要超过100页,越大性能越低,有可能会超时
	 **/
	private $currentPage;
	
	/** 
	 * 传入需要返回的字段,参见Bill结构体
	 **/
	private $fields;
	
	/** 
	 * 科目编号
	 **/
	private $itemId;
	
	/** 
	 * 交易编号
	 **/
	private $pTradeId;
	
	/** 
	 * 每页大小,默认40条,可选范围 ：40~100
	 **/
	private $pageSize;
	
	/** 
	 * 查询条件中的时间类型:1-交易订单完成时间biz_time 2-支付宝扣款时间pay_time 如果不填默认为2即根据支付时间查询,查询的结果会根据该时间倒排序
	 **/
	private $queryDateType;
	
	/** 
	 * 结束时间,限制:结束时间-开始时间不能大于1天(根据order_id或者trade_id查询除外)
	 **/
	private $queryEndDate;
	
	/** 
	 * 开始时间
	 **/
	private $queryStartDate;
	
	/** 
	 * 子订单编号
	 **/
	private $tradeId;
	
	private $apiParas = array();
	
	public function setCurrentPage($currentPage)
	{
		$this->currentPage = $currentPage;
		$this->apiParas["current_page"] = $currentPage;
	}

	public function getCurrentPage()
	{
		return $this->currentPage;
	}

	public function setFields($fields)
	{
		$this->fields = $fields;
		$this->apiParas["fields"] = $fields;
	}

	public function getFields()
	{
		return $this->fields;
	}

	public function setItemId($itemId)
	{
		$this->itemId = $itemId;
		$this->apiParas["item_id"] = $itemId;
	}

	public function getItemId()
	{
		return $this->itemId;
	}

	public function setpTradeId($pTradeId)
	{
		$this->pTradeId = $pTradeId;
		$this->apiParas["p_trade_id"] = $pTradeId;
	}

	public function getpTradeId()
	{
		return $this->pTradeId;
	}

	public function setPageSize($pageSize)
	{
		$this->pageSize = $pageSize;
		$this->apiParas["page_size"] = $pageSize;
	}

	public function getPageSize()
	{
		return $this->pageSize;
	}

	public function setQueryDateType($queryDateType)
	{
		$this->queryDateType = $queryDateType;
		$this->apiParas["query_date_type"] = $queryDateType;
	}

	public function getQueryDateType()
	{
		return $this->queryDateType;
	}

	public function setQueryEndDate($queryEndDate)
	{
		$this->queryEndDate = $queryEndDate;
		$this->apiParas["query_end_date"] = $queryEndDate;
	}

	public function getQueryEndDate()
	{
		return $this->queryEndDate;
	}

	public function setQueryStartDate($queryStartDate)
	{
		$this->queryStartDate = $queryStartDate;
		$this->apiParas["query_start_date"] = $queryStartDate;
	}

	public function getQueryStartDate()
	{
		return $this->queryStartDate;
	}

	public function setTradeId($tradeId)
	{
		$this->tradeId = $tradeId;
		$this->apiParas["trade_id"] = $tradeId;
	}

	public function getTradeId()
	{
		return $this->tradeId;
	}

	public function getApiMethodName()
	{
		return "taobao.tae.bills.get";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->fields,"fields");
		RequestCheckUtil::checkMaxListSize($this->fields,20,"fields");
		RequestCheckUtil::checkNotNull($this->queryEndDate,"queryEndDate");
		RequestCheckUtil::checkNotNull($this->queryStartDate,"queryStartDate");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
