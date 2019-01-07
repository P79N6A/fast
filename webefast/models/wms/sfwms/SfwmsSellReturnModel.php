<?php

require_model("wms/WmsSellReturnModel");

class SfwmsSellReturnModel extends WmsSellReturnModel {

    function __construct() {
        parent::__construct();
    }

    function convert_data($record_code) {
        $sql = "select json_data from wms_oms_trade where record_code = :record_code and record_type = :record_type";
        $json_data = ctx()->db->getOne($sql, array(':record_code' => $record_code, ':record_type' => 'sell_return'));
        $order = json_decode($json_data, true);
        $check_order = $this->get_record_data($order);
        if ($check_order === false) {
            return $this->format_ret(-1, '', '解密失败，稍后再处理...');
        } 
        $this->get_wms_cfg($order['store_code']);
        $wms_order['company'] = $this->wms_cfg['company'];
        $wms_order['source_id'] = $this->wms_cfg['company'];
        $wms_order['warehouse'] = $this->wms_cfg['wms_store_code'];
        $wms_order['erp_order_num'] = $order['sell_return_code'];
        $wms_order['original_order_no'] = $$order[''];
        $wms_order['erp_order_type'] = '退货入库';

        $wms_order['order_date'] = $order['create_time'];
        $wms_order['scheduled_receipt_date'] = date('Y-m-d H:i:s', strtotime('5 day'));
        $sql = "select wms_record_code from wms_oms_trade where record_code='{$order['sell_record_code']}' and record_type='sell_record'";
        $wms_record_code = $this->db->getOne($sql);
        $wms_order['original_order_no'] = $wms_record_code;


        $detailList = array();
        $line_num = 0;
        foreach ($order['goods'] as $row) {
            $t_row = array();
            $t_row['erp_order_line_num'] = $line_num;
            $t_row['item'] = strtoupper($row['barcode']);
            $t_row['total_qty'] = $row['num'];
            $detailList[]= array('item'=>$t_row);
            $line_num++;
        }
        $data = array('header' => $wms_order, 'detailList' => $detailList);
        return $this->format_ret(1, $data);
    }

    function upload($record_code) {
        $ret = $this->convert_data($record_code);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $wms_order = $ret['data'];
        $method = 'wmsPurchaseOrderService';
        $ret = $this->biz_req($method, $wms_order);
        if ($ret['status'] > 0) {
            return $this->format_ret(1, $ret['data']['wmsid']);
        }

        if (strpos($ret['message'], '订单号已存在') !== false){
            return $this->format_ret(1,$ret['data']['wmsid']);
        }
        return $ret;
    }
    function cancel($record_code, $efast_store_code) {
    	return $this->format_ret(-1,'','顺丰仓储不支持入库单的取消');
    }

	function wms_record_info($record_code,$efast_store_code){
		$this->get_wms_cfg($efast_store_code);
		$method = 'wmsPurchaseOrderQueryService';
		
		$data = array('company'=>$this->wms_cfg['company'],'warehouse' => $this->wms_cfg['wms_store_code'],'orderid'=>$record_code);
		$ret = $this->biz_req($method,$data);
	
		if ($ret['status']<0){
			return $ret;
		}
		$ret = $this->conv_wms_record_info($ret['data']);
		return $ret;
	}

	function conv_wms_record_info($result){
		if(isset($result['header'])){
			$order = $result['header'];
			$ret['efast_record_code'] = $order['erp_order_num'];
			//关闭时间
			if (!empty($order['close_date'])) {
				$ret['wms_record_code'] = $order['erp_order_num'];
				$ret['wms_store_code'] = $this->wms_cfg['wms_store_code'];
				$ret['flow_end_time'] = $order['close_date'];
				$ret['order_status'] = 'flow_end';
				$ret['order_status_txt'] = '已收发货';
				if (!isset($result['detailList']['item'][0])) {
					$goods[] = $result['detailList']['item'];
				} else {
					$goods = $result['detailList']['item'];
				}
				$skus = array();
				foreach($goods as $sub_goods){
					$skus[$sub_goods['sku_no']] += $sub_goods['qty'];
				}
				foreach ($skus as $sku_no=>$qty) {
					$ret['goods'][] = array('barcode'=>strtolower($sku_no),'sl'=>$qty);
				}
			} else {
				$ret['efast_record_code'] = $order['erp_order_num'];
				$ret['order_status'] = 'upload';
				$ret['order_status_txt'] = '已上传';
			}
		}
		return $this->format_ret(1,$ret);
	}

}
