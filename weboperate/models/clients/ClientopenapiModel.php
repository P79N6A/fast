<?php

/**
 * 服务中心中心-客户KEY
 *
 * @author wsc
 *
 */
require_model('tb/TbModel');
class ClientopenapiModel extends TbModel {

    function get_table() {
        return 'osp_kehu_openapikey';
    }

    function get_by_page($filter) {
        
        $sql_join = "";     //用户详细信息关联表
        $sql_main = "FROM {$this->table} rl $sql_join WHERE 1";

        //客户
        if (isset($filter['client']) && $filter['client']!='' ) {
                $sql_main .= " AND (rl.kh_id in (select kh_id from osp_kehu where kh_name LIKE '%". $filter['client'] ."%')) ";
        }
        //连接地址
        if (isset($filter['kh_IP']) && $filter['kh_IP']!='' ) {
                $sql_main .= " AND (rl.kh_IP ='". $filter['kh_IP']."')";
        }
        //构造排序条件
        $sql_main .= " ";

        $select = 'rl.*';

        $data =  $this->get_page_from_sql($filter, $sql_main, "",$select);

        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        //处理关联代码表
        filter_fk_name($ret_data['data'], array('kh_id|osp_kh'));

        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        $params = array('host_id' => $id);
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        $ret_status = $data ? 1 : 'op_no_data';
        //处理关联代码表
        filter_fk_name($data, array('kh_id|osp_kh'));
        return $this->format_ret($ret_status, $data);
    }
}
