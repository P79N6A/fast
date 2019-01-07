<?php

require_model('mid/MidApiAbs');

class Bserp2SellReturnModel extends MidApiAbs {
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
        $sys_param = load_model('sys/SysParamsModel')->get_val_by_code(array('update_express_money_to_new_erp'));
        $update_express_money_to_new_erp = isset($sys_param['update_express_money_to_new_erp']) ? $sys_param['update_express_money_to_new_erp'] : 0;
        $ret_record = $this->record_model->get_order_info($record_code);
        $record_data = $ret_record['data'];
        $express_money =  $update_express_money_to_new_erp == 1 ?  $record_data['seller_express_money'] + $record_data['compensate_money'] + $record_data['adjust_money'] : $record_data['compensate_money'] + $record_data['adjust_money'];
        $api_param['Order'] = array(
            'OrderCode' => $record_data['sell_return_code'],
            'orderId' => $record_data['sell_return_code'],
            'warehouseCode' => $this->config['join_config']['outside_code'],
            'orderCreateTime' => $record_data['create_time'],
            'orderType' => 'LSTH',
            'Postage' =>$express_money,
          //  'brandID' => $record_data['sell_record_code'],
            'ActualQty' => 0,
            'Amount' => ($record_data['is_fenxiao'] == 2) ? $record_data['fx_payable_money'] : $record_data['refund_total_fee'],
            'Remark' => $record_data['return_remark'],
            'CreateEmp' => 'eFAST365',
            'businessType' => $record_data['is_fenxiao'] == 0 ? 0 : 1
        );
  
         //获取客户编码
        $ret_outside = load_model('mid/MidApiConfigModel')->get_mid_api_join_config($this->config['join_config']['mid_code'], $record_data['shop_code'], 0) ;
        $api_param['Order']['customerCode'] =   $ret_outside['data']['outside_code'];
        
        $api_param['orderLines'] = array();
        $ret_detail = $this->record_model->get_order_detail($record_code);
        $orderLine = array();
        foreach ($ret_detail['data'] as $val) {
            $avg_money = ($record_data['is_fenxiao'] == 0) ? $val['avg_money'] : $val['fx_amount'];
            if (!isset($orderLine[$val['sku']])) {
                $key_arr = array(
                    'spec1_code', 'spec2_code', 'goods_code', 'goods_name', 'sell_price'
                );
                $sku_info = load_model('goods/SkuCModel')->get_sku_info($val['sku'], $key_arr);
                $row = array(
                    'itemCode' => $val['sku'],
                    'itemName' => $sku_info['goods_name'],
                    'actualQty' => $val['recv_num'],
                    'Amount' => $avg_money,
                    'StyleCode' => $sku_info['goods_code'],
                    'ColorCode' => $sku_info['spec1_code'],
                    'SizeCode' => $sku_info['spec2_code'],
                    'Discount' => '1',
                    'Stdprice' => $sku_info['sell_price'],
                    'orderId' => $val['deal_code'],
                );
                $orderLine[$val['sku']] = $row;
            } else {
                $orderLine[$val['sku']]['actualQty'] += $val['recv_num'];
                $orderLine[$val['sku']]['Amount'] += $avg_money;
            }
            $api_param['Order']['ActualQty']+= $val['recv_num'];
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
            if(strpos($api_data['response']['message'] , '单据重复')!==false){
                $ret =  $this->format_ret(1, $record_code);
            }else{
                $msg = isset($api_data['response']['message']) ? $api_data['response']['message'] : '接口数据异常';
                $ret = $this->format_ret(-1, $record_code, $msg);   
            }
        }
        return $ret;
    }

    function cancel($record_code) {
        
    }

}
