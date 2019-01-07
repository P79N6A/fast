<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of OrderSendModel
 *
 * @author wq
 */
require_model('tb/TbModel');
class OrderSendModel extends TbModel {

    function exec_send_api($source = 'houtai', $type = 'hlhdj',$where = "") {
        $sql = "select * from api_order_send where status<>1 AND source=:source AND shop_code='ht000'  AND send_time>'2017-05-01 00:00:00'";
        $sql_values = array(':source' => $source);
        $data = $this->db->get_all($sql, $sql_values);
        
        $type_mod_name = ucfirst($type) . "Model";
        $mod = load_model('api/pushapi/api/' . $type_mod_name);
        if ($mod === false) {
            echo '执行类型异常!'; 
            return ;
        }

        foreach ($data as $val) {
            $ret = $mod->send_order($val);
            $update_data = array(
                'status' => 1
            );
            if ($ret['status'] < 1) {
                $update_data['status'] = '-2';
                $update_data['fail_num'] = $val['fail_num'] + 1;
                $update_data['error_remark'] = $ret['message'];
            }
            $where = " api_order_send_id={$val['api_order_send_id']} AND status='{$val['status']}' ";
            $this->update_exp('api_order_send', $update_data, $where);
        }
    }

}
