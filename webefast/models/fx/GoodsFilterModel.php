<?php

/**
 * 分销订单过滤
 * @author dfr
 */
require_model('tb/TbModel');
require_lang('prm');
require_lib('util/oms_util', true);

class GoodsFilterModel extends TbModel {

    function get_table() {
        return 'api_order_fx_filter';
    }


    function save_filter_code($filter_code, $filter_obj = 'brand', $filter_type = 0) {
        //过滤特殊字符
        $filter_code = str_replace(array("\r\n", "\r", "\n"), '', $filter_code);
        //过滤中文分号
        $filter_code = str_replace(array("；"), ';', $filter_code);
        $data[] = array(
            'filter_code' => $filter_code,
            'filter_obj' => $filter_obj,
            'filter_type' => $filter_type,
        );
        $update_str = "filter_code=VALUES(filter_code)";
        $ret = $this->insert_multi_duplicate('api_order_fx_filter', $data, $update_str);
        if ($ret['status'] != 1) {
            return $this->format_ret('-1', '', '插入失败！');
        }
        return $this->format_ret('1', '', '插入成功！');
    }

    function get_filter_code($filter_obj = 'brand', $filter_type = 0){
        $ret=$this->get_row(array('filter_obj' => $filter_obj,'filter_type'=>$filter_type));
        return $ret;
    }


}

    
        

