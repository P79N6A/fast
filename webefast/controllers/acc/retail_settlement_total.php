<?php

/*
 * 零售结算汇总查询
 */

/**
 * Description of retail_settlement_total
 *
 * @author user
 */
class retail_settlement_total {
    //put your code here
    function do_list(array & $request, array & $response, array & $app){
        
    }
    
    function detail(array & $request, array & $response, array & $app){
        $this->get_spec_rename($response);
        $response['deal_code'] = $request['deal_code'];
    }
    
    private function get_spec_rename(array &$response){
        //spec别名
        $arr = array('goods_spec1','goods_spec2');
        $arr_spec = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['goods_spec1_rename'] = isset($arr_spec['goods_spec1']) ? $arr_spec['goods_spec1'] : '';
        $response['goods_spec2_rename'] = isset($arr_spec['goods_spec2']) ? $arr_spec['goods_spec2'] : '';
    }
}
