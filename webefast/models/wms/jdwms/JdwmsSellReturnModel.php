<?php

require_model("wms/WmsSellReturnModel");

class JdwmsSellReturnModel extends WmsSellReturnModel {

    private $err_msg = array();
            
    function __construct() {
        parent::__construct();
    }

    function convert_data($record_code) {
        $sql = "select wms_record_code, json_data from wms_oms_trade where record_code = :record_code and record_type = :record_type";
        $wms_data = $this->db->get_row($sql, array(':record_code' => $record_code, ':record_type' => 'sell_return'));
        $order = json_decode($wms_data['json_data'], true);
        $check_order = $this->get_record_data($order);
        if ($check_order === false) {
            return $this->format_ret(-1, '', '解密失败，稍后再处理...');
        }
        $this->get_wms_cfg($order['store_code']);
        //京东云仓退单上传需要订单的eclpSoNo FBB 2017.5.22
        $record_sql = "SELECT wms_record_code FROM wms_oms_trade WHERE record_code=:record_code AND record_type='sell_record'";
        $wms_record_code = $this->db->get_value($record_sql, array(":record_code" => $order['sell_record_code']));
        $data = array();
        $data['eclpSoNo'] = $wms_record_code;
        $data['isvRtwNum'] = $order['sell_return_code'];
   
        return $this->format_ret(1, $data);
    }

    function upload($record_code) {
        $ret = $this->convert_data($record_code);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $wms_order = $ret['data'];
        $method = 'jingdong.eclp.rtw.transportRtw';
        $result = $this->biz_req($method, $wms_order);
        if ($result['status'] < 0) {
            return $result;
        }
        if ($result['data']['transportrtw_result']['resultCode'] != 1) {
            return $this->format_ret(-1, '', $result['data']['transportrtw_result']['msg']);
        }
        $api_record_code  = isset($ret['data']['transportrtw_result']['eclpRtwNo'])&&!empty($ret['data']['transportrtw_result']['eclpRtwNo'])
                ?$ret['data']['transportrtw_result']['eclpRtwNo']:$record_code;
        
        return $this->format_ret(1, $api_record_code);

    }

    function cancel($record_code, $efast_store_code) {//京东接口不存在
        return $this->format_ret(-1,'','不支持取消');

    }

    function wms_record_info($record_code, $efast_store_code) {
        $this->get_wms_cfg($efast_store_code);
        $method = 'jingdong.eclp.rtw.queryRtw';
        $sql=" select json_data from wms_oms_trade where record_code=:record_code AND record_type=:record_type";
        $sql_values = array(
            ':record_code'=>$record_code,
            ':record_type'=>'sell_return',
        );
        $json_data = $this->db->get_value($sql,$sql_values);
        $order = json_decode($json_data, true);
        //京东云仓获取退单状态需要订单的eclpSoNo FBB 2017.07.05
        $record_sql = "SELECT wms_record_code FROM wms_oms_trade WHERE record_code=:record_code AND record_type='sell_record'";
        $wms_record_code = $this->db->get_value($record_sql, array(":record_code" => $order['sell_record_code']));
        $data = array();
        $data['eclpSoNo'] = $wms_record_code;
        $data['isvRtwNum'] = $order['sell_return_code'];
        $ret = $this->biz_req($method, $data);

        if ($ret['status'] < 0) {
            return $ret;
        }
        $ret = $this->conv_wms_record_info($ret['data'],$order);
        return $ret;
      
    }

    function conv_wms_record_info($result,$order) {
        $api_order_data = $result['queryrtw_result'][0];
        $status_txt_map = array('flow_end'=>'已收发货','upload'=>'已上传','wait_upload'=>'未上传');
        if ($api_order_data['status'] == 200) {
            $ret['order_status'] = 'flow_end';
         
            $ret['efast_record_code'] = $api_order_data['isvRtwNum'];
            $ret['wms_record_code'] = $api_order_data['eclpRtwNo'];
            //发货时间
            $ret['flow_end_time'] = $api_order_data['create_time'];

            foreach ($order['goods']  as $sub_goods) {
                $ret['goods'][] = array('barcode' => $sub_goods['barcode'], 'sl' => $sub_goods['num']);
            }
        }else{
            	$ret['efast_record_code'] = $result['bizid'];
			$ret['efast_record_code'] = $order['sell_return_code'];
			$ret['order_status'] = 'upload';
			$ret['order_status_txt'] = isset($status_txt_map[$ret['order_status']]) ? $status_txt_map[$ret['order_status']] : $ret['order_status'];
			$ret['msg'] = $result['msg'];
	
        }
        return $this->format_ret(1, $ret);
    }
    
    private function set_api_barcode_detail(&$api_goods_detail,$goods_arr){
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
			$goods_val['goods_barcode'] = $val['sys_code'];
            $new_goods[] = $goods_val;
        }   
		return $new_goods;
    }
    
    function sync_jdwms_return_action($efast_store_code, $start_time, $end_time,  $page_no = 1, $page_size = 40) {
        $this->get_wms_cfg($efast_store_code);
        $return_data = $this->sync_jdwms_return_request($start_time, $end_time, $page_no, $page_size);
        if($return_data['status'] < 0) {
            return $return_data;
        }
        if(empty($return_data['data'])) {
            return $this->format_ret(1);
        }
        $this->handle_return_data($return_data['data']);
        return $this->format_ret(1);
    }
    
    function sync_jdwms_return_request($start_time, $end_time,  $page_no, $page_size) {
        $param = array();
        $param['deptNo'] = $this->wms_cfg['deptNo'];
        $param['soStatus'] = '10037';
        $param['pageNo'] = $page_no;
        $param['pageSize'] = $page_size;
        $param['startDate'] = $start_time;
        $param['endDate'] = $end_time;
        $method = 'jingdong.eclp.order.queryOrderListByStatus';
        $ret = $this->biz_req($method, $param);
        if ($ret['data']['orderQueryResult']['resultCode'] != 1) {
            return $this->format_ret(-1, '', $ret['data']['orderQueryResult']['errMsg']);
        }
        return $this->format_ret(1, $ret['data']['orderQueryResult']['soNoList']);
    }
    
    function handle_return_data($soNoList) {
        $refunds = $refund = $sql_values = $sql_api_values = $retund_sql_values = array();
        $str = $this->arr_to_in_sql_value($soNoList, 'wms_record_code', $sql_values);
        $sql = "SELECT wms_record_code, json_data,shop_code FROM wms_oms_trade WHERE wms_record_code IN ($str) AND api_product='jdwms' AND record_type='sell_record'";
        $wms_data = $this->db->get_all($sql, $sql_values);
        if(empty($wms_data)) {
            return $this->format_ret(1);
        }
        $this->begin_trans();
        foreach ($wms_data as $wms) {
            $json_data = json_decode($wms['json_data'], true);
            $refund['source'] = $json_data['sale_channel_code'];
            $refund['shop_code'] = $json_data['shop_code'];
            $refund['refund_id'] = $wms['wms_record_code'];
            $refund['tid'] = $json_data['deal_code_list'];
            $refund['status'] = 1;
            $refund['is_change'] = 0;
            $refund['buyer_nick'] = $json_data['buyer_name'];
            $refund['has_good_return'] = 1;
            $refund['refund_fee'] = $json_data['payable_money'];
            $refund['refund_desc'] = '京东逆向发货订单';
            $refund['order_last_update_time'] = date('Y-m-d H:i:s');
            $refund['order_first_insert_time'] = date('Y-m-d H:i:s');
            $refund['first_insert_time'] = date('Y-m-d H:i:s');
            if ($json_data['pay_type'] == 'cod' && $json_data['sale_channel_code'] == 'jingdong') {
                $payable_money = load_model('oms/DeliverRecordModel')->get_jd_cod_payable_money($json_data['deal_code_list']);
                if ($payable_money !== false) {
                    $refund['refund_fee'] = $payable_money;
                }
            }
            $tid_arr[] = $json_data['deal_code_list'];
            $refunds[$json_data['deal_code_list']] = $refund;
        }
        $tid_str = $this->arr_to_in_sql_value($tid_arr, 'tid', $sql_api_values);
        $api_sql = "SELECT tid, seller_nick FROM api_order WHERE tid IN({$tid_str}) AND source='jingdong'";
        $seller_nick_arr = $this->db->get_all($api_sql, $sql_api_values);
        foreach ($seller_nick_arr as $value) {
            $refunds[$value['tid']]['seller_nick'] = $value['seller_nick'];
        }
        //写入api_refund表
        $update_str = " refund_fee = VALUES(refund_fee), order_last_update_time = VALUES(order_last_update_time) ";
        $ret = $this->insert_multi_duplicate('api_refund', $refunds, $update_str);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $ret;
        }
        $this->commit();
        $refund_str = $this->arr_to_in_sql_value($soNoList, 'refund_id', $retund_sql_values);
        $refund_sql = "SELECT id, refund_id FROM api_refund WHERE refund_id IN ($refund_str) AND source='jingdong' AND refund_id NOT IN (SELECT refund_id FROM oms_sell_return WHERE sale_channel_code = 'jingdong' AND refund_id IN ($refund_str))";
        $refund_data = $this->db->get_all($refund_sql, $retund_sql_values);
        if(empty($refund_data)) {
            return true;
        }
        //退单转单
        foreach ($refund_data as $refund) {
            $ret = load_model('oms/TranslateRefundModel')->translate_refund_api($refund['id']);
            if ($ret['status'] < 0) {
                $this->err_msg[$refund['refund_id']][] = $ret['message'];
            } else {
                $sell_return_code_arr[] = $ret['data'];
            }
        }
        //退单快速入库
        foreach ($sell_return_code_arr as $sell_refund_code) {
            $ret = load_model('oms/SellReturnOptModel')->opt_confirm_return_shipping($sell_refund_code);
            if ($ret['status'] < 0) {
                $this->err_msg[$sell_refund_code][] = $ret['message'];
            }
        }
    }

}
