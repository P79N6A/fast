<?php
/**
 * TOP API: taobao.tae.accounts.get request
 * 
 * @author auto create
 * @since 1.0, 2015.03.09
 */
class TaeAccountsGetRequest
{
	/** 
	 * 需要获取的科目ID
	 **/
	private $aids;
	
	/** 
	 * 需要返回的字段
	 **/
	private $fields;
	
	private $apiParas = array();
	
	public function setAids($aids)
	{
		$this->aids = $aids;
		$this->apiParas["aids"] = $aids;
	}

	public function getAids()
	{
		return $this->aids;
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

	public function getApiMethodName()
	{
		return "taobao.tae.accounts.get";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkMaxListSize($this->aids,20,"aids");
		RequestCheckUtil::checkNotNull($this->fields,"fields");
		RequestCheckUtil::checkMaxListSize($this->fields,20,"fields");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
