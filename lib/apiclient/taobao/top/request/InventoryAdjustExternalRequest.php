<?php
/**
 * TOP API: taobao.inventory.adjust.external request
 * 
 * @author auto create
 * @since 1.0, 2017.04.18
 */
class InventoryAdjustExternalRequest
{
	/** 
	 * 外部订单类型, BALANCE:盘点、NON_TAOBAO_TRADE:非淘宝交易、ALLOCATE:调拨、OTHERS:其他
	 **/
	private $bizType;
	
	/** 
	 * 商家外部定单号
	 **/
	private $bizUniqueCode;
	
	/** 
	 * 商品库存预留信息： [{"scItemId":"商品后端ID，如果有传scItemCode,参数可以为0","scItemCode":"商品商家编码","inventoryType":"库存类型  1：正常,2：损坏,3：冻结,10：质押",11-20:商家自定义,”inventoryTypeName”:”库存类型名称,可选”,"occupyQuantity":"数量"}]
	 **/
	private $items;
	
	/** 
	 * test
	 **/
	private $occupyOperateCode;
	
	/** 
	 * 操作时间
	 **/
	private $operateTime;
	
	/** 
	 * 操作类型  BALANCE 盘点  OUTBOUND出库  INBOUND 入库
	 **/
	private $operateType;
	
	/** 
	 * test
	 **/
	private $reduceType;
	
	/** 
	 * 商家仓库编码
	 **/
	private $storeCode;
	
	private $apiParas = array();
	
	public function setBizType($bizType)
	{
		$this->bizType = $bizType;
		$this->apiParas["biz_type"] = $bizType;
	}

	public function getBizType()
	{
		return $this->bizType;
	}

	public function setBizUniqueCode($bizUniqueCode)
	{
		$this->bizUniqueCode = $bizUniqueCode;
		$this->apiParas["biz_unique_code"] = $bizUniqueCode;
	}

	public function getBizUniqueCode()
	{
		return $this->bizUniqueCode;
	}

	public function setItems($items)
	{
		$this->items = $items;
		$this->apiParas["items"] = $items;
	}

	public function getItems()
	{
		return $this->items;
	}

	public function setOccupyOperateCode($occupyOperateCode)
	{
		$this->occupyOperateCode = $occupyOperateCode;
		$this->apiParas["occupy_operate_code"] = $occupyOperateCode;
	}

	public function getOccupyOperateCode()
	{
		return $this->occupyOperateCode;
	}

	public function setOperateTime($operateTime)
	{
		$this->operateTime = $operateTime;
		$this->apiParas["operate_time"] = $operateTime;
	}

	public function getOperateTime()
	{
		return $this->operateTime;
	}

	public function setOperateType($operateType)
	{
		$this->operateType = $operateType;
		$this->apiParas["operate_type"] = $operateType;
	}

	public function getOperateType()
	{
		return $this->operateType;
	}

	public function setReduceType($reduceType)
	{
		$this->reduceType = $reduceType;
		$this->apiParas["reduce_type"] = $reduceType;
	}

	public function getReduceType()
	{
		return $this->reduceType;
	}

	public function setStoreCode($storeCode)
	{
		$this->storeCode = $storeCode;
		$this->apiParas["store_code"] = $storeCode;
	}

	public function getStoreCode()
	{
		return $this->storeCode;
	}

	public function getApiMethodName()
	{
		return "taobao.inventory.adjust.external";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->bizType,"bizType");
		RequestCheckUtil::checkNotNull($this->bizUniqueCode,"bizUniqueCode");
		RequestCheckUtil::checkNotNull($this->items,"items");
		RequestCheckUtil::checkNotNull($this->operateTime,"operateTime");
		RequestCheckUtil::checkNotNull($this->operateType,"operateType");
		RequestCheckUtil::checkNotNull($this->storeCode,"storeCode");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
