<?php

/**
 * 用户相关业务
 *
 * @author wzd
 *
 */
require_model('tb/TbModel');

//require_lang('sys');

class ClientorderModel extends TbModel {

    function get_table() {
        return 'osp_client_orders';
    }

    /*
     * 获取岗位信息方法
     */
    function get_client_order_info($filter) {
        $sql_join = "left join osp_kehu kh on c.kh_code=kh.kh_code ";
        $sql_main = "FROM {$this->table} c $sql_join WHERE 1";
        
        //客户名称搜索条件
        if (isset($filter['clientname']) && $filter['clientname'] != '') {
            $sql_main .= " AND kh_name LIKE '%" . $filter['clientname'] . "%'";
        }
        //日期搜索
        if (isset($filter['datestart']) && $filter['datestart'] != ''){
            $sql_main .= " AND c.oRder_date >=" . strtotime($filter['datestart']);
        }
        if (isset($filter['dateend']) && $filter['dateend'] != ''){
             $sql_main .= " AND c.oRder_date <=" . strtotime($filter['dateend']);
        }
        //客户为单位的单量统计
        $sql_main .= " GROUP BY kh.kh_name,c.oRder_date";
        $select = 'c.*,kh.kh_name,sum(oday_order) as totalnum';
        $data = $this->get_page_from_sql($filter, $sql_main,'',$select,true);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

}
