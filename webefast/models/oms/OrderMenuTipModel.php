<?php

require_model('tb/TbModel');

class OrderMenuTipModel extends TbModel {

    function get_tip_all($type) {
        $conf = array(
            'order' => array(
                'api/sys/order_refund/do_list' => 'get_fail_refund_num',
                'api/sys/order_send/index' => 'get_fail_order_send_num',
                'oms/sell_record/td_list' => 'get_fail_order_num',
            ),
            'fenxiao' => array(
                'api/api_taobao_fx_order/td_list' => 'get_fail_fx_order_num',
                'api/api_taobao_fx_refund/do_list' => 'get_fail_fx_refund_num',
            ),
            'stm' => array(
                'prm/inv/do_list' => 'get_fail_stm_num',
            ),
        );
        $data = array();
        foreach ($conf[$type] as $act => $fun) {
            $data[$act] = $this->$fun();
        }
        return $this->format_ret(1, $data);
    }

    function get_fail_refund_num() {
        $sql = "select count(1) from api_refund where status=1 AND is_change=-1";
        return $this->db->get_value($sql);
    }

    function get_fail_order_send_num() {
        $sql = "select count(1) from api_order_send where status<0 ";
        return $this->db->get_value($sql);
    }

    function get_fail_order_num() {
        $shop_code_str=load_model('base/ShopModel')->get_sql_purview_shop('shop_code');
        $sql = "select count(1) from api_order where status=1 AND is_change=-1" . $shop_code_str;
        return $this->db->get_value($sql);
    }

    function get_fail_fx_order_num() {
        $sql = "select count(1) from api_taobao_fx_trade where is_change=-1";
        return $this->db->get_value($sql);
    }

    function get_fail_fx_refund_num() {
        $sql = "select count(1) from api_taobao_fx_refund where is_change=-1";
        return $this->db->get_value($sql);
    }

    function get_fail_stm_num() {
        $sql = "select count(1) from goods_inv where stock_num < safe_num";
        return $this->db->get_value($sql);
    }

    //统计发货超时订单个数
    function get_deliver_overtime_num() {
        $sql = "select count(1) FROM oms_sell_record rl WHERE rl.order_status<>3 AND rl.order_status<>5 AND rl.shipping_status<>4 AND rl.plan_send_time < :current_time";
        $sql_values = array();
        $current = date('Y-m-d H:i:s');
        $sql_values[':current_time'] = $current;
        return $this->db->get_value($sql, $sql_values);
    }

}

?>
