<?php

/**
 * 用户相关业务
 *
 * @author wzd
 *
 */
require_model('tb/TbModel');

//require_lang('sys');

class ShoporderModel extends TbModel {

    function get_table() {
        return 'osp_client_orders';
    }

    /*
     * 获取岗位信息方法
     */
    function get_shop_order_info($filter) {
        $sql_join .= "left join osp_kehu kh on c.kh_code=kh.kh_code  ";
        $sql_join .= "left join osp_shangdian sd on c.sd_code=sd.sd_code  ";
        $sql_main = "FROM {$this->table} c $sql_join WHERE 1";
        
        //客户关联产品搜索条件
        if (isset($filter['shopname']) && $filter['shopname'] != '') {
            $sql_main .= " AND sd.sd_name='{$filter['shopname']}'";
        }
        //客户名称搜索条件
        if (isset($filter['clientname']) && $filter['clientname'] != '') {
            $sql_main .= " AND kh.kh_name LIKE '%" . $filter['clientname'] . "%'";
        }
        //日期搜索
        if (isset($filter['datestart']) && $filter['datestart'] != ''){
            $sql_main .= " AND c.oRder_date >=" . strtotime($filter['datestart']);
        }
        if (isset($filter['dateend']) && $filter['dateend'] != ''){
             $sql_main .= " AND c.oRder_date <=" . strtotime($filter['dateend']);
        }
        
        $select = 'c.*,kh.kh_name,sd.sd_name';
        $data = $this->get_page_from_sql($filter, $sql_main, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

}
