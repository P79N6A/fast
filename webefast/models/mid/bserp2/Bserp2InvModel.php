<?php

class Bserp2InvModel extends TbModel {

    protected $api_mod;
    protected $record_model;
    protected $config;
    private $api_record = array();

    function __construct(&$record_model, $config) {
        parent::__construct();
        $this->record_model = $record_model;
        $this->config = $config;

        $this->create_api($config['api_config']);
    }

    function create_api($api_conf) {
        require_lib('apiclient/BserpClient');
        $this->api_mod = new BserpClient($api_conf);
    }

    function sys_inv($param) {
        $api_param = $this->get_api_param($param);
        $api_data = $this->api_mod->inventory_query($api_param);
        $ret = array();
        $sku_arr = array();
        if (isset($api_data['response']) && $api_data['response']['flag'] == 'success') {
            
            if( (int)$api_data['response']['total'] ==0 ){
                 return $this->format_ret(-1, array(),'空数据');
            }
            
            $items_data = &$api_data['response']['items']['item'];

            if (isset($items_data['qtyplan'])) {//一条数据处理
                $items_data = array($items_data);
            }
            $data = array();
            $mid_code = $this->config['join_config']['mid_code'];

            foreach ($items_data as $val) {
                $row = array(
                    'mid_code' => $mid_code,
                    'api_code' => $val['sku'],
                    'sku' => $val['sku'],
                    'store_code' => $param['sys_store_code'],
                    'goods_code' => $val['stylecode'],
                    'spec1_code' => $val['colorcode'],
                    'spec2_code' => $val['sizecode'],
                    'num' => $val['qtyplan'],
                    'is_sync'=>1,
                    'down_time' => date('Y-m-d H:i:s'),
                    'api_json_data' => json_encode($val),
                );
                $key = $this->get_sku_key($row);
                $data[$key] = $row;
                $sku_arr[] = "  ( goods_code = '{$row['goods_code']}' AND spec1_code = '{$row['spec1_code']}' AND spec2_code = '{$row['spec2_code']}' ) ";
            }

            $this->set_sku_data($data, $sku_arr);
            //保存本次下载时间
            $totel = (int) $api_data['response']['total'];
            $items_num = count($items_data);
            $down_num = ($param['page'] - 1) * $param['page_size'] + $items_num;
            if ($param['page_size'] > $items_num || $totel == $down_num) {
                $this->save_api_record($api_data['response']['lastchanged']);
            }

            $ret = $this->format_ret(1, $data);
        } else {
            $msg = isset($api_data['response'][' message']) ? $api_data['response'][' message'] : '接口数据异常';
            $ret = $this->format_ret(-1, $api_data, $msg);
        }
        return $ret;
    }

    function set_sku_data(&$data, &$sku_arr) {
        $sql = "select sku,goods_code,spec1_code,spec2_code from goods_sku where ";
        $sql .= implode(' OR ', $sku_arr);
        $sku_data = $this->db->get_all($sql);
        foreach ($sku_data as $val) {
            $key = $this->get_sku_key($val);
            $data[$key]['sku'] = $val['sku'];
        }
    }

    function get_sku_key($row) {
        return $row['goods_code'] . $row['spec1_code'] . $row['spec2_code'];
    }

    private function get_api_param($param) {

        $api_param = array(
            'page' => $param['page'],
            'pageSize' => $param['page_size'],
            'warehouseCode' => $param['api_store_code'],
        );
        //todo:如果出现很大时间差 可以用 档案表的 api_update_time
        if (empty($this->api_record)) {
            $api_name = 'get.Goods';
            $this->api_record = load_model('mid/MidBaseModel')->get_api_record($this->config['join_config']['mid_code'], $api_name);
            if (!empty($this->api_record) && !empty($this->api_record['end_time'])) {
                $api_param['lastChanged'] = $this->api_record['last_api_time'];
            } else {
                $this->api_record = array(
                    'mid_code' => $this->config['join_config']['mid_code'],
                    'api_product' => 'bserp',
                    'api_name' => $api_name,
                    'start_time' => '',
                    'end_time' => '',
                    'request_data' => '',
                    'api_request_time' => time(),
                );
            }
        }
        return $api_param;
    }

    private function save_api_record($lastchanged) {
        $this->api_record['last_api_time'] = $lastchanged;
        load_model('mid/MidBaseModel')->save_api_record($this->api_record);
    }
    
    /**
     * @todo 销售订单锁定库存上传到ERP
     */
    function upload_lock_inv($inv_data, $outside_code) {
        $inv_data_arr = array_chunk($inv_data, 500);
        foreach ($inv_data_arr as $data) {
            $i = 0;
            $orderId = 'Lock' . sprintf("%.0f", (date('Ymd') . '0001' + $i));
            $this->upload_lock_inv_act($data, $orderId, $outside_code);
            $i++;
        }
        return $this->format_ret(1);
    }
    
    function upload_lock_inv_act($inv_data, $orderId, $outside_code) {
        $api_param = $api_order = $api_detail = $orderLine = array();
        $api_order['orderId'] = $orderId;
        $api_order['warehouseCode'] = $outside_code;
        $api_order['orderCreateTime'] = date('Y-m-d H:i:s');
        $api_order['orderType'] = 'LOCK';
        $api_order['Amount'] = '0.00';
        
        foreach($inv_data as $data) {
            $api_detail['itemId'] = $data['sku'];
            $api_detail['itemName'] = $data['goods_name'];
            $api_detail['actualQty'] = $data['lock_num'];
            $api_detail['Amount'] = '0.00';
            $api_detail['StyleCode'] = $data['goods_code'];
            $api_detail['ColorCode'] = $data['spec1_code'];
            $api_detail['SizeCode'] = $data['spec2_code'];
            $api_detail['Discount'] = 1;
            $api_detail['Stdprice'] = '0.00';
            $orderLine[] = array('orderLine'=>$api_detail);
            $api_order['ActualQty'] += $data['lock_num'];
        }
        $api_param['Order'] = $api_order;
        $api_param['orderLines'] = $orderLine;//['orderLine']
        $api_data = $this->api_mod->update_pf_asn($api_param);

        if (isset($api_data['response']) && $api_data['response']['flag'] == 'success') {
            $ret = $this->format_ret(1);
        } else {
            if(strpos($api_data['response'][' message'] , '单据重复') !== false){
                $ret =  $this->format_ret(1);
            }else{
                $msg = isset($api_data['response']['message']) ? $api_data['response']['message'] : '接口数据异常';
                $ret = $this->format_ret(-1, '',  $msg);   
            }
        }
        return $ret;
    }
}
