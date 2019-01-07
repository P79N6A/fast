<?php
/**
 * TOP API: taobao.tae.book.bill.get request
 * 
 * @author auto create
 * @since 1.0, 2015.04.09
 */
class TaeBookBillGetRequest
{
	/** 
	 * 虚拟账户科目编号
	 **/
	private $accountId;
	
	/** 
	 * 虚拟账户流水编号
	 **/
	private $bid;
	
	/** 
	 * 需要返回的字段:参见BookBill结构体
	 **/
	private $fields;
	
	/** 
	 * 虚拟账户流水编号
	 **/
	private $id;
	
	private $apiParas = array();
	
	public function setAccountId($accountId)
	{
		$this->accountId = $accountId;
		$this->apiParas["account_id"] = $accountId;
	}

	public function getAccountId()
	{
		return $this->accountId;
	}

	public function setBid($bid)
	{
		$this->bid = $bid;
		$this->apiParas["bid"] = $bid;
	}

	public function getBid()
	{
		return $this->bid;
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

	public function setId($id)
	{
		$this->id = $id;
		$this->apiParas["id"] = $id;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getApiMethodName()
	{
		return "taobao.tae.book.bill.get";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->accountId,"accountId");
		RequestCheckUtil::checkNotNull($this->fields,"fields");
		RequestCheckUtil::checkMaxListSize($this->fields,20,"fields");
		RequestCheckUtil::checkNotNull($this->id,"id");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
