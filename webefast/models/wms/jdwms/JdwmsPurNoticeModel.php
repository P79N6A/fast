<?php

require_model("wms/WmsPurNoticeModel");

class JdwmsPurNoticeModel extends WmsPurNoticeModel {

    function __construct() {
        parent::__construct();
    }

    function convert_data($record_code) {
        $sql = "select json_data,new_record_code from wms_b2b_trade where record_code = :record_code and record_type = :record_type";
        $row = ctx()->db->get_row($sql, array(':record_code' => $record_code, ':record_type' => 'pur_notice'));
        $order = json_decode($row['json_data'], true);
        $this->get_wms_cfg($order['store_code']);
     
        
        $data = array();
        $data['spPoOrderNo'] = empty($row['new_record_code']) ? $order['record_code'] : $row['new_record_code'];
        $data['deptNo'] = $this->wms_cfg['deptNo'];
        $data['whNo'] = $this->wms_cfg['whNo'];
        $data['supplierNo'] = $this->wms_cfg['eclpSupplierNo'];
        $line_num = 1;
        $goods_arr = array();
        foreach ($order['goods'] as $row) {
            $goods_arr[$row['barcode']] = $line_num;
            $data['deptGoodsNo'][$line_num] = $row['barcode'];
            $data['numApplication'][$line_num] = $row['num'];
            $data['goodsStatus'][$line_num] = 1;
            $line_num++;
        }

        $this->set_goods_barcode_detail($data['deptGoodsNo'],$goods_arr);
   
        $data['deptGoodsNo'] = implode(",", $data['deptGoodsNo']);
        $data['numApplication'] = implode(",", $data['numApplication']);
        $data['goodsStatus'] = implode(",", $data['goodsStatus']);
        
        
        return $this->format_ret(1, $data);
    }
   
    
    function upload($record_code) {
        $ret = $this->convert_data($record_code);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $wms_order = $ret['data'];
        $method = 'jingdong.eclp.po.addPoOrder';
        $result = $this->biz_req($method, $wms_order);
        if ($result['status'] < 0) {
            return $result;
        }

        return $this->format_ret(1, $result['data']['poOrderNo']);
    }

    function cancel($record_code, $efast_store_code) {
        $this->get_wms_cfg($efast_store_code);
        
        $sql=" select wms_record_code from wms_b2b_trade where (record_code=:record_code OR new_record_code=:record_code)   AND record_type=:record_type";
        $sql_values = array(
            ':record_code'=>$record_code,
            ':record_type'=>'pur_notice',
        );
        $wms_record_code = $this->db->get_value($sql,$sql_values);
       
        $data = array();
        $data['poOrderNo'] = $wms_record_code;
        $method = 'jingdong.eclp.po.cancalPoOrder';
        $result = $this->biz_req($method, $data);
        if($result['status'] < 0){
             return $result;
        }
        if ($result['data']['poResult']['code'] == 1) {
            return $this->format_ret(1, '');
        }
        return $this->format_ret(-1, $result['data']['poResult']['msg']);
        
    }

    function wms_record_info($record_code, $efast_store_code) {
        $this->get_wms_cfg($efast_store_code);
        $method = 'jingdong.eclp.po.queryPoOrder';
        $sql=" select wms_record_code from wms_b2b_trade where  (record_code=:record_code OR new_record_code=:record_code) AND record_type=:record_type";
        $sql_values = array(
            ':record_code'=>$record_code,
            ':record_type'=>'pur_notice',
        );
        $wms_record_code = $this->db->get_value($sql,$sql_values);
        $data = array();
        $data['poOrderNo'] = $wms_record_code;
        $ret = $this->biz_req($method, $data);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $ret = $this->conv_wms_record_info($ret['data'], $efast_store_code);
        return $ret;
    }

    function conv_wms_record_info($result, $efast_store_code) {
        $api_data =  $result['queryPoModelList'][0];
        

        $status_txt_map = array('flow_end'=>'已收发货','upload'=>'已上传','wait_upload'=>'未上传');
  
      
      
        $storageStatus = $api_data['poOrderStatus'];

        if ($storageStatus == '70') {//
            $ret['order_status'] = 'flow_end';
            $ret['efast_record_code'] = $api_data['isvPoOrderNo'];
            $ret['wms_record_code'] = $api_data['poOrderNo'];
            $ret['wms_store_code'] = $efast_store_code;

            //发货时间
            $ret['flow_end_time'] = '';
            $goods_ret = $api_data['poItemModelList'];

            $goods_data = array();
            foreach ($goods_ret as $sub_goods) {
              //  $sub_goods = $sub_val['poItemModel'];
                $goods_data[$sub_goods['goodsNo']] = array('barcode' => $sub_goods['goodsNo'], 'sl' => $sub_goods['realInstoreQty']);
            }
            $ret['goods'] = $this->set_barcode_detail($goods_data);
            
        }else{
                        $ret['efast_record_code'] = $api_data['poOrderNo'];
			$order_status = 'upload';
			$ret['order_status'] = $order_status;
                        $ret['order_status_txt'] = $status_txt_map[$order_status];
	
		

        }
        return $this->format_ret(1, $ret);
    }
    
  
    private function set_goods_barcode_detail(&$api_goods_detail,$goods_arr){
        $barcode_arr = array_keys($goods_arr);
        $sql = "select sys_code,api_code from wms_archive where wms_config_id=:wms_config_id  ";
        $sql_values = array(
            ':wms_config_id'=>$this->wms_cfg['wms_config_id'],
        );
        $str = $this->arr_to_in_sql_value($barcode_arr, 'sys_code', $sql_values);
        $sql.=" AND sys_code IN ({$str}) ";
        $data = $this->db->get_all($sql,$sql_values);
        foreach($data as $val){
            $key = $goods_arr[$val['sys_code']];
            $api_goods_detail[$key] = !empty($val['api_code']) ? $val['api_code'] : $val['sys_code'];
        }
        
    }
           
    private function set_barcode_detail($goods_data){
        $goods_arr = array_keys($goods_data);
        $sql = "select sys_code,api_code from wms_archive where wms_config_id=:wms_config_id  ";
        $sql_values = array(
            ':wms_config_id'=>$this->wms_cfg['wms_config_id'],
        );
        $str = $this->arr_to_in_sql_value($goods_arr, 'api_code', $sql_values);
        $sql.=" AND api_code IN ({$str}) ";
        $data = $this->db->get_all($sql,$sql_values);
		$new_goods = array();
        foreach($data as $val){
            $goods_val = $goods_data[$val['api_code']];
			$goods_val['barcode'] = $val['sys_code'];
            $new_goods[] = $goods_val;
        }   
		return $new_goods;
    }
    
}
