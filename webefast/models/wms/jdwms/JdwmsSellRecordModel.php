<?php

require_model("wms/WmsSellRecordModel");

class JdwmsSellRecordModel extends WmsSellRecordModel {

    public $jd_shpping_config = array(
         'sf'=>'CYS0000010',//顺丰快递
    );

    public $jd_sale_channel=array(
        'jingdong'=>'1',//京东
        'taobao'=>'2',//天猫
        'suning'=>'3',//苏宁
        'yamaxun'=>'4',//亚马逊中国
    );
    
    public $express_set = array(
        'EMS' => 'CYS4418046511224',
        'YTO' => 'CYS4398046511127',
        'YUNDA' => 'CYS4418046511108',
        'EYB' =>'CYS4418046511228',
        'JD' => 'CYS0000010',
        'JDCOD' => 'CYS0000010',
        'ZTO' => 'CYS4418046511112'
    );
                
    function __construct() {
        parent::__construct();
    }

    function convert_data($record_code) {
        $sql = "select json_data,new_record_code from wms_oms_trade where record_code = :record_code and record_type = :record_type";
       $row = ctx()->db->get_row($sql, array(':record_code' => $record_code, ':record_type' => 'sell_record'));
        $order = json_decode($row['json_data'], true);
        $check_order = $this->get_record_data($order);
        if ($check_order === false) {
            return $this->format_ret(-1, '', '解密失败，稍后再处理...');
        }
        $this->get_wms_cfg($order['store_code']);
        $shop_sql = "SELECT outside_code FROM sys_api_shop_store WHERE shop_store_code=:shop_store_code AND shop_store_type=0 AND p_type=1";
        $outside_code = $this->db->get_value($shop_sql, array('shop_store_code' => $order['shop_code']));
        $data = array();
        $data['isvUUID'] = empty($row['new_record_code']) ? $order['sell_record_code'] : $row['new_record_code'];
        $data['isvSource'] = $this->wms_cfg['isvSource'];
        $data['shopNo'] = $outside_code;
        $data['departmentNo'] = $this->wms_cfg['deptNo'];
        $data['warehouseNo'] = $this->wms_cfg['warehouseNo'];
        
        $deal_code_list_arr = explode(',', $order['deal_code_list']);
        $data['shipperNo'] = isset($this->express_set[$order['express_code']]) && !empty($this->express_set[$order['express_code']]) ? $this->express_set[$order['express_code']] : $order['express_code'];
        $data['salesPlatformOrderNo'] = $deal_code_list_arr[0];
        $data['salePlatformSource'] = $order['sale_channel_code'] == 'jingdong' ? '1' : '6';//京东云仓强制值，6代表其他
        $data['salesPlatformCreateTime'] = $order['record_time'];
        $data['consigneeName'] = $this->html_decode($order['receiver_name']);
        $data['consigneeMobile'] = $order['receiver_mobile'];
        $data['consigneePhone'] =  $order['receiver_phone'];
        $data['consigneeEmail'] = $order['receiver_email'];
        $data['expectDate'] = $order['plan_send_time'];
        
        $data['addressCounty'] = $this->get_area_name($order['receiver_country']);
        $data['addressProvince'] = $this->get_area_name($order['receiver_province']);
        $data['addressCity'] = $this->get_area_name($order['receiver_city']);
        $data['addressTown'] = $this->get_area_name($order['receiver_district']);
        $data['consigneeAddress'] = $this->html_decode($order['receiver_address']);
        $data['consigneePostcode'] = $order['receiver_zip_code'];
        
        if (strtolower($order['pay_type']) == 'cod') {
            $payable_money = $order['payable_money'] - $order['paid_money'];
            $data['receivable'] = number_format($payable_money, 2, '.', '');
            $data['orderMark'] = '1'.str_repeat('0',49);
        } else {
            $data['receivable'] = 0;
            $data['orderMark'] = str_repeat('0',50);
        }
        $data['consigneeRemark'] = $order['seller_remark'];
        $data['thirdWayBill'] = $order['express_no'];//三方运单号 
        $express_data = json_decode($order['__print'], true);
        //四期打印数据
        if(isset($express_data['print_data']) && !empty($express_data['print_data'])){
            $express_data = json_decode($express_data['print_data'], true);
        }
        //集包地名称
        if(isset($express_data['package_center_name']) && !empty($express_data['package_center_name'])) {
            $data['destinationName'] = $express_data['package_center_name'];
        }
        //若四期集包地值为空，取四期大头笔作为目的地名称
        if(isset($express_data['data']['routingInfo']['sortation']['name']) && !empty($express_data['data']['routingInfo']['sortation']['name'])) {
            $data['destinationName'] = $express_data['data']['routingInfo']['sortation']['name'];
        }
        
        if(isset($express_data['data']['routingInfo']['consolidation']['name']) && !empty($express_data['data']['routingInfo']['consolidation']['name'])) {
            $data['destinationName'] = $express_data['data']['routingInfo']['consolidation']['name'];
        }
        
        $i = 0;
        $data['goodsNo'] = array();
        $data['price'] = array();
        $data['quantity'] = array();
        $goods_arr = array();
        foreach ($order['goods'] as $row) {
            if($row['goods_price']=='0.000'){
                 $row['goods_price'] = '0.00';
            }
            $goods_arr[$row['barcode']] = $i;
            $data['goodsNo'][$i] = $row['barcode'];
            $data['price'][$i] = number_format($row['goods_price'], 2, '.', '');;//京东云仓强制保留两位小数传值
            $data['quantity'][$i] = $row['num'];
            $i++;
        }

        $this->set_api_barcode_detail($data['goodsNo'], $goods_arr);
                
        $data['goodsNo'] = implode(",", $data['goodsNo']);
        $data['price'] = implode(",", $data['price']);
        $data['quantity'] = implode(",", $data['quantity']);
        return $this->format_ret(1, $data);
    }



    function upload($record_code) {
        $ret = $this->convert_data($record_code);
        if ($ret['status'] < 1) {
            return $ret;
        }
        $method = 'jingdong.eclp.order.addOrder';
        $result = $this->biz_req($method, $ret['data']);
        if ($result['status'] < 0) {
            return $result;
        }
        $wms_record_code = isset($result['data']['eclpSoNo']) ? $result['data']['eclpSoNo'] : '';
        return $this->format_ret(1, $wms_record_code);
    }

    //状态回传
    function wms_record_info($record_code, $efast_store_code) {
        $this->wms_cfg = array();
        $this->get_wms_cfg($efast_store_code);
        $sql=" select record_code,wms_record_code from wms_oms_trade where (record_code=:record_code OR new_record_code=:record_code)  AND record_type=:record_type";
        $sql_values = array(
            ':record_code'=>$record_code,
            ':record_type'=>'sell_record',
        );
        $row = $this->db->get_row($sql,$sql_values);
        $sys_record_code = $row['record_code'];
        $wms_record_code = $row['wms_record_code'];
        $data = array();

        $data['eclpSoNo'] = !empty($wms_record_code)?$wms_record_code:$sys_record_code;
        $ret = $this->biz_req('jingdong.eclp.order.queryOrder', $data);
        if ($ret['status'] < 0) {
            return $ret;
        }
        if(!empty($ret['data'])){
            $ret = $this->conv_wms_record_info($ret['data'], $efast_store_code,$sys_record_code);
        }
        return $ret;
    }

    function conv_wms_record_info($result, $efast_store_code,$record_code) {
        $status_txt_map = array('flow_end' => '已收发货', 'upload' => '已上传', 'wait_upload' => '未上传');
        //根据顺丰返回的操作日志 解析当前订单所处的订单状态
        $api_data  = $result['queryorder_result'];
        $api_data_status = $api_data['orderStatusList'];
        
        $ret['order_status'] = 'upload';
        foreach($api_data_status as $status_info){

           if($status_info['soStatusCode']=='10019'){ 
               $ret['order_status'] = 'flow_end';
      
            //发货时间
            $ret['flow_end_time'] = isset($status_info['operateTime'])?$status_info['operateTime']:'';
            break;
           }
        }
        
        $filp_express_set = array_flip($this->express_set);//翻转快递代码配置
        $ret['order_status_txt'] = isset($status_txt_map[$ret['order_status']]) ? $status_txt_map[$ret['order_status']] : $ret['order_status'];
        if ($ret['order_status'] == 'flow_end') {
            $ret['efast_record_code'] = $record_code;
            $ret['wms_record_code'] = $api_data['eclpSoNo'];
            $ret['wms_store_code'] = $efast_store_code;
            $ret['express_code'] = isset($filp_express_set[$api_data['shipperNo']]) && !empty($filp_express_set[$api_data['shipperNo']]) ? $filp_express_set[$api_data['shipperNo']] : $api_data['shipperNo'];//快递公司
          
            if (isset($api_data['wayBill']) && !empty($api_data['wayBill'])) {
                $ret['express_no'] = str_replace('-1-1-', '', $api_data['wayBill']);
            } else {
                $ret['express_no'] = str_replace('-1-1-', '', $api_data['orderPackageList'][0]['packageNo']);
            }

            $goods_ret = $api_data['orderDetailList'];
            $goods_data = array();
            foreach ($goods_ret as $sub_goods) {
              $goods_data[$sub_goods['goodsNo']] = array('barcode' => $sub_goods['goodsNo'], 'sl' => $sub_goods['quantity']);
            }
            $ret['goods'] = $this->set_barcode_detail($goods_data);
        }
        return $this->format_ret(1, $ret);
    }

    function cancel($record_code, $efast_store_code) {
        $this->get_wms_cfg($efast_store_code);
                      
        $sql=" select wms_record_code from wms_oms_trade where  (record_code=:record_code OR new_record_code=:record_code) AND record_type=:record_type";
        $sql_values = array(
            ':record_code'=>$record_code,
            ':record_type'=>'sell_record',
        );
        $wms_record_code = $this->db->get_value($sql,$sql_values);
        $method = 'jingdong.eclp.order.cancelOrder';
        $req['eclpSoNo'] = $wms_record_code;
        $ret = $this->biz_req($method, $req);
        if ($ret['status'] < 0) {
            return $ret;
        }
        if ( $ret['data']['cancelorder_result']['code']!=1) {
            return $this->format_ret(-1, '', $ret['data']['cancelorder_result']['msg']);
        }
        return $this->format_ret(1,$wms_record_code);
    }

    private function get_area_name($id) {
        $sql = "select name from base_area where id=:id";
        return $this->db->get_value($sql, array(':id' => $id));
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
			$goods_val['barcode'] = $val['sys_code'];
            $new_goods[] = $goods_val;
        }   
	return $new_goods;
    }
    
    
    //查询单据状态
    function get_record_flow($record_code, $efast_store_code) {
        $sql = "SELECT wms_record_code FROM wms_oms_trade WHERE (record_code=:record_code OR new_record_code=:record_code) and record_type = :record_type";
        $wms_record_code = $this->db->get_value($sql, array(':record_code' => $record_code, ':record_type' => 'sell_record'));
        $this->get_wms_cfg($efast_store_code);
        $method = 'jingdong.eclp.order.queryOrderStatus';
        $req = array('eclpSoNo' => $wms_record_code);
        $status_ret = $this->biz_req($method, $req);
        $status_arr = array();
        $opdate_arr = array();
        $ret_data = array();
        if ($status_ret['status'] == 1) {
            foreach ($status_ret['data']['queryorderstatus_result']['orderStatusList'] as $status) {
                $status_arr[] = $status['soStatusCode'];
                $opdate_arr[] = $status['operateTime'];
            }
            array_multisort($status_arr, SORT_DESC, $opdate_arr, $status_ret['data']);
            $ret_data = $status_ret['data']['queryorderstatus_result']['orderStatusList'];
        }
        return $this->format_ret($status_ret['status'], $ret_data);
    }
    //{"jingdong_eclp_master_queryShipper_responce":{"code":"0","queryshipper_result":[{"contacts":"任娟","phone":"13919192929","shipperName":"顺丰","shipperNo":"001"},{"contacts":"刘华","phone":"13819192929","shipperName":"圆通","shipperNo":"002"}]}}'
}
