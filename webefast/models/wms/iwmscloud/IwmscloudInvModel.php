<?php
/**
* array (
  'status' => 1,
  'data' => 
  array (
    'list' => 
    array (
      0 => 
      array (
        'WarehouseCode' => 'ck1',
        'SkuCode' => 'SP001001002',
        'NormalQuantity' => '作废',
        'DefectiveQuantity' => '作废',
        'Qty' => '596',
        'QtyFreeze' => '0',
        'QtyLockB2C' => '0',
        'QtyLockB2B' => '0',
      ),
      1 => 
      array (
        'WarehouseCode' => 'ck1',
        'SkuCode' => 'SP001002000',
        'NormalQuantity' => '作废',
        'DefectiveQuantity' => '作废',
        'Qty' => '691',
        'QtyFreeze' => '0',
        'QtyLockB2C' => '0',
        'QtyLockB2B' => '0',
      ),
    ),
  ),
  'message' => '操作成功',
)

array (
  'status' => 1,
  'data' => 
  array (
    'bizid' => 'WarehouseCode:ck1,BDate:2010-01-01 00:00:00,EDate:2015-05-22 00:00:00',
    'wmsid' => '',
    'state' => 'NotRecord',
    'msg' => '没有记录',
  ),
  'message' => '操作成功',
)
*/
require_model("wms/WmsInvModel");
class IwmscloudInvModel extends WmsInvModel {
	function __construct()
	{
		parent::__construct();
	}

	function inv_search($efast_store_code,$barcode_arr){
		$this->get_wms_cfg($efast_store_code);
		$barcode_list = join(',',$barcode_arr);		
		$method = 'ewms.stocksearch.list.get';
		$req = array('WarehouseCode'=>$this->wms_store_code,'Skus'=>$barcode_list);
		$ret = $this->biz_req($method,$req);
		//echo '<hr/>$ret<xmp>'.var_export($ret,true).'</xmp>';
		if ($ret['status']>0){
			$result = array();
			foreach($ret['data']['list'] as $sub_ret){
				if (!isset($sub_ret['SkuCode']) || !isset($sub_ret['Qty'])){
					continue;
				}
				$result[] = array('barcode'=>$sub_ret['SkuCode'],'num'=>$sub_ret['Qty']);
			}
			//echo '<hr/>$result<xmp>'.var_export($result,true).'</xmp>';die;
			return $this->format_ret(1,$result);
		}
		return $ret;
	}

	function sync_inv_incr($efast_store_code,$start,$end){
		$this->get_wms_cfg($efast_store_code);
		$method = 'ewms.changeskusearch.get';
		$req = array('WarehouseCode'=>$this->wms_store_code,'BDate'=>$start,'EDate'=>$end);
		$ret = $this->biz_req($method,$req);
		if ($ret['data']['state'] == 'NotRecord'){
			return $this->format_ret(1,'NotRecord');
		}
		if ($ret['data']['ChangeSkuSearchItem']){
			$barcode_arr = array();
			foreach($ret['data']['ChangeSkuSearchItem'] as $sub_row){
				$barcode_arr[] = $sub_row['SkuCode'];
			}
			$ret = $this->inv_search($efast_store_code,$barcode_arr);
			return $ret;
		}
		return $ret;		
	}
	
}