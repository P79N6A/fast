<?php

require_model('mid/MidApiAbs');

class Bserp2SellRecordModel extends MidApiAbs {
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
        if($record_data['is_fenxiao'] == 0) {
            $express_money =  $update_express_money_to_new_erp == 1 ?  $record_data['express_money'] : 0;
        } else {
            $express_money =  $update_express_money_to_new_erp == 1 ?  $record_data['fx_express_money'] : 0;
        }


		$kh_id = CTX()->saas->get_saas_key();

		if($kh_id == 2535)
		{
			$record_data['order_remark'] = $record_data['order_remark'].';手机号:'.$record_data['receiver_mobile'];
		}

        $api_param['Order'] = array(
            'OrderCode' => $record_data['sell_record_code'],
            'orderId' => $record_data['sell_record_code'],
            'warehouseCode' => $this->config['join_config']['outside_code'],
            'orderCreateTime' => $record_data['record_time'],
            'orderType' => 'LSCK',
            'Postage' => $express_money,
         //   'ChannelCode' => $record_data['sale_channel_code'],
          //  'brandID' => $record_data['sell_record_code'],
            'ActualQty' => $record_data['goods_num'],
            'Amount' => ($record_data['is_fenxiao'] == 0) ? $record_data['payable_money'] : $record_data['fx_payable_money'],
            'Remark' => $record_data['order_remark'],
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
                    'actualQty' => $val['num'],
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
                $orderLine[$val['sku']]['actualQty'] += $val['num'];
                $orderLine[$val['sku']]['Amount'] += $avg_money;
            }
        }
        foreach($orderLine as $val){
            $api_param['orderLines'][] = array(
                'orderLine'=>$val,
            );
        }

        $api_data = $this->api_mod->retail_confirm($api_param);

        if (isset($api_data['response']) && $api_data['response']['flag'] == 'success') {
            $ret = $this->format_ret(1, $record_code);
        } else {
            if(strpos($api_data['response']['message'] , '单据重复')!==false){
                 $ret = $this->format_ret(1, $record_code);
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
