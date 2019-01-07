<?php

require_model('mid/MidApiAbs');

class Bserp2SellReturnRbModel extends MidApiAbs {

    protected $api_mod;
    protected $record_model;
    protected $config;

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

    function upload($record_code) {
        $api_param = array();
        $ret_record = $this->record_model->get_order_info($record_code);
        $record_data = $ret_record['data'];

        $api_param['Order'] = array(
            'OrderCode' => $record_data['record_code'],
            'orderId' => $record_data['record_code'],
            'warehouseCode' => $this->config['join_config']['outside_code'],
            'orderCreateTime' => $record_data['record_date'],
            'orderType' => 'LSTH',
            'Postage' => $record_data['express_amount'], //卖家承担运费+ 赔付金额+ 手工调整金额值
            'ActualQty' => $record_data['quantity'],
            'Amount' => $record_data['amount'],
            'Remark' => $record_data['remark'],
            'CreateEmp' => 'eFAST365',
            'businessType' => $record_data['is_fenxiao']
        );
        //获取客户编码
        $ret_outside = load_model('mid/MidApiConfigModel')->get_mid_api_join_config($this->config['join_config']['mid_code'], $record_data['shop_code'], 0);
        $api_param['Order']['customerCode'] = $ret_outside['data']['outside_code'];

        $api_param['orderLines'] = array();
        $ret_detail = $this->record_model->get_order_detail($record_code);
        $orderLine = array();
        foreach ($ret_detail['data'] as $val) {
            $key_arr = array('spec1_code', 'spec2_code', 'goods_code', 'goods_name', 'sell_price');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($val['sku'], $key_arr);
            $row = array(
                'itemCode' => $val['sku'],
                'itemName' => $sku_info['goods_name'],
                'actualQty' => $val['num'],
                'Amount' => $val['money'],
                'StyleCode' => $sku_info['goods_code'],
                'ColorCode' => $sku_info['spec1_code'],
                'SizeCode' => $sku_info['spec2_code'],
                'Discount' => '1',
                'Stdprice' => $sku_info['sell_price'],
                'orderId' => $val['return_code'],
            );
            $orderLine[$val['sku']] = $row;
        }

         foreach($orderLine as $val){
            $api_param['orderLines'][] = array(
                'orderLine'=>$val,
            );
        }
        $api_data = $this->api_mod->retail_confirm($api_param);
        $ret = array();
        if (isset($api_data['response']) && $api_data['response']['flag'] == 'success') {
            $ret = $this->format_ret(1, $record_code);
        } else {
            if (strpos($api_data['response']['message'], '单据重复') !== false) {
                $ret = $this->format_ret(1, $record_code);
            } else {
                $msg = isset($api_data['response']['message']) ? $api_data['response']['message'] : '接口数据异常';
                $ret = $this->format_ret(-1, $record_code, $msg);
            }
        }
        return $ret;
    }

    function cancel($record_code) {
        
    }

}
