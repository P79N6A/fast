<?php

require_model("wms/WmsWbmNoticeModel");

class JdwmsPurReturnNoticeModel extends WmsWbmNoticeModel {

    function __construct() {
        parent::__construct();
    }

    function convert_data($record_code) {
        $sql = "select json_data,new_record_code from wms_b2b_trade where record_code = :record_code and record_type = :record_type";
        $row = $this->db->get_row($sql, array(':record_code' => $record_code, ':record_type' => 'pur_return_notice'));
        if(empty($row)) {
            return $this->format_ret(-1,'no record');
        }
        $order = json_decode($row['json_data'], true);
        $this->get_wms_cfg($order['store_code']);

        $data = array();
        $_receiver_ids = array();
        $region_data = array();
        $sql_values = array();
        $_receiver_ids[] = $order['supplier']['province'];
        $_receiver_ids[] = $order['supplier']['city'];
        $_receiver_ids[] = $order['supplier']['district'];
        $_receiver_ids[] = $order['supplier']['street'];
        $str = $this->arr_to_in_sql_value(array_unique($_receiver_ids), 'id', $sql_values);
        $sql = "SELECT id AS region_id, name AS region_name FROM base_area WHERE id IN ($str)";
        $_region_data = $this->db->get_all($sql, $sql_values);
        foreach ($_region_data as $_region) {
            $region_data[$_region['region_id']] = $_region['region_name'];
        }
        $data['isvRtsNum'] = $data['eclpRtsNo'] = empty($row['new_record_code']) ? $order['record_code'] : $row['new_record_code'];
        $data['deptNo'] = $this->wms_cfg['deptNo'];
        $data['deliveryMode'] = 2;  //1、商家自提。2、京东配送
        $data['warehouseNo'] = $this->wms_cfg['warehouseNo'];
        $data['province'] = $region_data[$order['supplier']['province']];
        $data['city'] = $region_data[$order['supplier']['city']];
        $data['county'] = $region_data[$order['supplier']['district']];
        $data['town'] = $region_data[$order['supplier']['street']];
        $data['address'] = $order['supplier']['address'];
        $line_num = 1;
        $goods_arr = array();
        foreach ($order['goods'] as $row) {
            $goods_arr[$row['barcode']] = $line_num;
            $data['deptGoodsNo'][$line_num] = $row['barcode'];
            $data['quantity'][$line_num] = $row['num'];
            //$data['goodsStatus'][$line_num] = 1;//商品状态(良品；残品；样品) 
            $line_num++;
        }
        $this->set_goods_barcode_detail($data['deptGoodsNo'],$goods_arr);
        
        $data['deptGoodsNo'] = implode(",", $data['deptGoodsNo']);
        $data['quantity'] = implode(",", $data['quantity']);

        return $this->format_ret(1, $data);
    }

    function upload($record_code) {
        $ret = $this->convert_data($record_code);
        if ($ret['status'] < 1) {
            return $ret;
        }
        $method = 'jingdong.eclp.rts.isvRtsTransfer';
        $result = $this->biz_req($method, $ret['data']);
        if ($result['status'] < 0) {
            return $result;
        }
        if ($result['data']['rtsResult']['resultCode'] <>1) {
            return $this->format_ret(-1, '', $result['data']['rtsResult']['failMsg']);
        }

        return $this->format_ret(1, $result['data']['rtsResult']['eclpRtsNo']);
    }

    function cancel($record_code, $efast_store_code) {
        $this->get_wms_cfg($efast_store_code);
        $sql=" select wms_record_code from wms_b2b_trade where (record_code=:record_code OR new_record_code=:record_code)   AND record_type=:record_type";
        $sql_values = array(
            ':record_code'=>$record_code,
            ':record_type'=>'pur_return_notice',
        );
        $wms_record_code = $this->db->get_value($sql,$sql_values);
        
        
        
        $method = 'jingdong.eclp.rts.isvRtsCancel';
        $req = array();
        $req['eclpRtsNo'] = $wms_record_code;
        $ret = $this->biz_req($method, $req);
        if ($ret['status'] < 0) {
            return $ret;
        }
        if ($ret['data']['rtsResult']['resultCode'] <>1) {
            return $this->format_ret(-1, '', $ret['data']['rtsResult']['msg']);
        }
        return $this->format_ret(1, $ret['data']['rtsResult']['eclpRtsNo']);
    }

    //状态回传
    function wms_record_info($record_code, $efast_store_code) {
        $this->get_wms_cfg($efast_store_code);
                      
        $sql=" select wms_record_code from wms_b2b_trade where (record_code=:record_code OR new_record_code=:record_code)  AND record_type=:record_type";
        $sql_values = array(
            ':record_code'=>$record_code,
            ':record_type'=>'pur_return_notice',
        );
        $wms_record_code = $this->db->get_value($sql,$sql_values);
        $method = 'jingdong.eclp.rts.isvRtsQuery';
        $data = array();
        $data['eclpRtsNo'] = $wms_record_code;
        $ret = $this->biz_req($method, $data);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $ret = $this->conv_wms_record_info($ret['data'], $efast_store_code);
        return $ret;
    }

    function conv_wms_record_info($result, $efast_store_code) {
        $status_txt_map = array('flow_end' => '已收发货', 'upload' => '已上传', 'wait_upload' => '未上传');
        
        $api_data =  $result['rtsResultList'][0];
        //根据顺丰返回的操作日志 解析当前订单所处的订单状态
        $order_status = $api_data['rtsOrderStatus']; //70 200
        //
        //是否已出库
        if($order_status!='200'){
             $ret['order_status'] = 'upload';
        }else if ($order_status =='200' ) {
            $ret['order_status'] = 'flow_end';
        } 
        $ret['order_status_txt'] = isset($status_txt_map[$ret['order_status']]) ? $status_txt_map[$ret['order_status']] : $ret['order_status'];
        if ($ret['order_status'] == 'flow_end') {

            $ret['efast_record_code'] = $api_data['isvRtsNum'];
            $ret['wms_record_code'] = $api_data['eclpRtsNo'];
            $ret['wms_store_code'] = $efast_store_code;

            //发货时间
            $ret['flow_end_time'] = isset($api_data['operatorTime'])?$api_data['operatorTime']:'';
            $goods_ret = $api_data['rtsDetailList'];

            $goods_data = array();
            foreach ($goods_ret as $sub_val) {
                //$sub_goods = $sub_val['rtsDetail'];
				 $sub_goods = $sub_val;
                $goods_data[$sub_goods['deptGoodsNo']] = array('barcode' => $sub_goods['deptGoodsNo'], 'sl' => $sub_goods['realQuantity']);
            }
            $ret['goods'] = $this->set_barcode_detail($goods_data);
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
            $api_goods_detail[$key] = $val['api_code'];
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
