<?php

require_model('mid/MidApiAbs');
require_lib('util/crm_util');

class DxerpSellRecordModel extends MidApiAbs {
    protected $api_url;
    protected $client;
    public $erp_config = array();

    function __construct($erp_config) {
        parent::__construct();
        $this->erp_config = $erp_config;
        $this->api_url = $erp_config['erp_address'] . '/OnlineOrder?wsdl';
        //$this->create_api($erp_config);
    }

    /**
     * 组装接口参数
     * @param $record
     * @param $record_details
     */
    function set_data_format($record, $record_details) {
        $param = array(
            'deal_code' => $record['deal_code_list'],
            'delivery_time' => $record['delivery_time'],
            'express_name' => oms_tb_val('base_express', 'express_name', array('express_code' => $record['express_code'])),
            'express_no' => $record['express_no'],
            'goods_num' => $record['goods_num'],
            'payable_money' => $record['payable_money'],
            'receiver_mobile' => $record['receiver_mobile'],
            'receiver_name' => $record['receiver_name'],
            'sell_record_code' => $record['sell_record_code'],
            'shop_code' => $record['shop_code'],
            'shop_name' => oms_tb_val('base_shop', 'shop_name', array('shop_code' => $record['shop_code'])),
            'store_code' => $record['store_code'],
            'store_name' => oms_tb_val('base_store', 'store_name', array('store_code' => $record['store_code'])),
        );
        foreach ($record_details as $detail) {
            $key_arr = array('barcode',);
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($detail['sku'], $key_arr);
            $param['detailOnlineOrders'][] = array(
                'avg_money' => $detail['avg_money'],
                'barcode' => $sku_info['barcode'],
                'goods_price' => $detail['goods_price'],
                'num' => $detail['num'],
            );
        }
        return $param;
    }

    /**
     * 创建服务器对象
     * @param $api_conf
     */
    function create_api($erp_config) {
        require_lib('nusoap/nusoap');
        $ret = $this->format_ret(1);
        try {
            $this->client = new SoapClient($this->api_url);
        }catch (Exception $e) {
            $_err = '接口请求报错:' . $e->getMessage();
            $ret = $this->format_ret(-1, '', $_err);
        }
        return $ret;
    }

    /**
     * 单据上传
     * @param $record_code
     * @return array
     */
    function upload($record_code) {
        $sql_value = array();
        //获取主单信息
        $sql = "SELECT * FROM oms_sell_record WHERE sell_record_code=:sell_record_code";
        $sql_value[':sell_record_code'] = $record_code;
        $record = $this->db->get_row($sql, $sql_value);
        if (empty($record)) {
            return $this->format_ret(-1, '', '该单据不存在！');
        }
        if ($record['delivery_time'] < $this->erp_config['online_time']. ' 00:00:00') {
            return $this->format_ret(-1, '', '单据发货时间小于上线时间！');
        }
        if ($record['sale_channel_code'] == 'taobao') {//淘宝平台数据解密
            $record_decrypt_info = load_model('sys/security/OmsSecurityOptModel')->get_sell_record_decrypt_info($record['sell_record_code']);
            if (empty($record_decrypt_info)) {
                return $this->format_ret(-1, '', '数据解密失败，稍后尝试！');
            }
            $record = array_merge($record, $record_decrypt_info);
        }
        //获取明细信息
        $sql = "SELECT SUM(avg_money) AS avg_money,sku,goods_price,SUM(num) AS num FROM oms_sell_record_detail WHERE sell_record_code=:sell_record_code GROUP BY sku";
        $record_details = $this->db->get_all($sql, $sql_value);
        if (empty($record_details)) {
            return $this->format_ret(-1, '', '无单据明细！');
        }
        $api_param = array();
        $api_param['in0'] = $this->set_data_format($record, $record_details);
        //调用接口
        $ret = $this->client->receiveOD($api_param);
        //将obj类型转成数组类型
        $result = object_to_array($ret);
        //记录日志
        $log_params = array(
            'url'=>$this->api_url,
            'request' => $api_param,
            'response' => $result,
        );
        //记录日志
        $this->write_log($log_params,'DAOXUN');
        //插入中间表
        $this->insert_dxerp_trade($result, $record);
        if ($result['out']['STATUS'] != 'OK') {
            return $this->format_ret(-1, '', $result['out']['DATA']);
        } else {
            return $this->format_ret(1);
        }
    }

    /**
     * 插入中间表
     * @param $result
     * @param $record
     */
    function insert_dxerp_trade($result, $record) {
        if (isset($result['out']['STATUS']) && $result['out']['STATUS'] == 'OK') {//成功
            $upload_status = 1;
            $upload_msg = '';
        } else {
            $upload_status = 2;
            $upload_msg = $result['out']['DATA'];
        }
        $insert_params = array(
            'sell_record_code' => $record['sell_record_code'],
            'deal_code' => $record['deal_code'],
            'deal_code_list' => $record['deal_code_list'],
            'order_type' => 1,
            'store_code' => $record['store_code'],
            'shop_code' => $record['shop_code'],
            'upload_status' => $upload_status,
            'upload_time' => date('Y-m-d H:i:s'),
            'upload_msg' => $upload_msg,
        );
        $update_str = "upload_status=VALUES(upload_status),upload_time=VALUES(upload_time),upload_msg=VALUES(upload_msg)";
        $this->insert_multi_duplicate('api_dxerp_trade', array($insert_params), $update_str);
    }

    function cancel($record_code) {

    }

}
