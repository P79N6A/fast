<?php
/**
 * TOP API: taobao.rds.db.create request
 * 
 * @author auto create
 * @since 1.0, 2017.03.29
 */
class RdsDbCreateRequest
{
	/** 
	 * 已存在账号名
	 **/
	private $accountName;
	
	/** 
	 * 数据库名
	 **/
	private $dbName;
	
	/** 
	 * rds的实例名
	 **/
	private $instanceName;
	
	private $apiParas = array();
	
	public function setAccountName($accountName)
	{
		$this->accountName = $accountName;
		$this->apiParas["account_name"] = $accountName;
	}

	public function getAccountName()
	{
		return $this->accountName;
	}

	public function setDbName($dbName)
	{
		$this->dbName = $dbName;
		$this->apiParas["db_name"] = $dbName;
	}

	public function getDbName()
	{
		return $this->dbName;
	}

	public function setInstanceName($instanceName)
	{
		$this->instanceName = $instanceName;
		$this->apiParas["instance_name"] = $instanceName;
	}

	public function getInstanceName()
	{
		return $this->instanceName;
	}

	public function getApiMethodName()
	{
		return "taobao.rds.db.create";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkMaxLength($this->accountName,13,"accountName");
		RequestCheckUtil::checkNotNull($this->dbName,"dbName");
		RequestCheckUtil::checkMaxLength($this->dbName,64,"dbName");
		RequestCheckUtil::checkNotNull($this->instanceName,"instanceName");
		RequestCheckUtil::checkMaxLength($this->instanceName,30,"instanceName");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
