<?php
require_model('mid/MidApiAbs');
class MesSellReturnModel extends MidApiAbs {

    protected $api_mod;
    protected $record_model;
    protected $config;
    protected $pub_model;

    function __construct(&$record_model, $config) {
        parent::__construct();
        $this->record_model = $record_model;
        $this->config = $config;

        $this->create_api($config['api_config']);
        $this->pub_model = load_model('mid/mes/MidMesPubModel');
    }

    function create_api($api_conf) {
        require_model('api/mes/MesClientModel');
        $this->api_mod = new MesClientModel($api_conf);
    }

    function upload($record_code) {
        $ret_record = $this->record_model->get_order_info($record_code);
        $record_data = $ret_record['data'];
        $param = array(
            'order_code' => $record_data['sell_return_code'],
            'kh_code' => $record_data['shop_code'],
            'kh_name' => $this->pub_model->get_shop_name($record_data['shop_code']),
        );
        $param['goods_list'] = array();
        $ret_detail = $this->record_model->get_order_detail($record_code);
        $WarehouseCode = $this->config['join_config']['outside_code'];
        foreach ($ret_detail['data'] as $val) {

            if (isset($param['goods_list'][$val['sku']])) {
                $param['goods_list'][$val['sku']]['Qty']+=$val['note_num'];
            } else {
                $sku_info = load_model('goods/SkuCModel')->get_sku_info($val['sku'], array('barcode'));
                $Unit = $this->pub_model->get_goods_unit($val['goods_code']);
                
                $param['goods_list'][$val['sku']] = 
                    array('ItemCode' => $sku_info['barcode'], 'Unit' => $Unit, 'Qty' => $val['note_num'], 'WarehouseCode' => $WarehouseCode);
       
            }
        }
        $param['goods_list'] = array_values($param['goods_list']);

        $ret_api = $this->api_mod->record_return($param);
        if(isset($ret_api['Success'])&&$ret_api['Success']===true){
            $ret = $this->format_ret(1, $record_data['sell_record_code']);  
        }else{
               $ret = $this->format_ret(-1,'','接口反映异常：'.$ret_api['Message']);  
        }
        return $ret;
    }

    function cancel($record_code) {

        $param['order_code'] = $record_code;
        $ret_api = $this->api_mod->cancel_return($param);
        
        if(isset($ret_api['Success'])&&$ret_api['Success']===true){
            $ret = $this->format_ret(1);  
        }else{
               $ret = $this->format_ret(-1,'','接口反映异常：'.$ret_api['Message']);  
        }


        return $ret;
    }

}
