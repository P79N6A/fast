<?php

require_model('tb/TbModel');

class OrderSendModel extends TbModel {

    function get_table() {
        return 'api_order_send';
    }

    function get_by_page($filter = array()) {
        $sql_values = array();

        $sql_main = "FROM api_order_send rl WHERE 1";

        $select = 'rl.*';

        //去掉空格
        foreach ($filter as $key => $v) {
            $filter[$key] = trim($v);
        }
        //过滤店铺权限
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('rl.shop_code', $filter_shop_code);
        /*
          if(!empty($filter['tid'])){
          $sql_main .= " and t1.tid = :tid";
          $sql_values[':tid'] = $filter['tid'];
          } */
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = $filter['keyword'];
        }

        //配送方式
        if (isset($filter['express_code']) && !empty($filter['express_code'])) {
            $express_arr = explode(',', $filter['express_code']);
            $str = $this->arr_to_in_sql_value($express_arr, 'express_code', $sql_values);
            $sql_main .= " and rl.express_code in ({$str}) ";
        }
        //回写状态
        if (isset($filter['status']) && $filter['status'] != '') {
            $status_arr = explode(',', $filter['status']);
            $str = $this->arr_to_in_sql_value($status_arr, 'status', $sql_values);
            $sql_main .=" and rl.status in ({$str}) ";
        }
        //销售平台
        if (isset($filter['source']) && !empty($filter['source'])) {
            $source_arr = explode(',', $filter['source']);
           $str = $this->arr_to_in_sql_value($source_arr, 'source', $sql_values);
            $sql_main .= " and rl.source in ({$str})";
            //$sql_main .= " AND rl.source = :sale_channel_code ";
            //$sql_values[':sale_channel_code'] = $filter['source'];
        }

        //下单时间
        if (isset($filter['send_time_start']) && $filter['send_time_start'] != '') {
            $sql_main .= " AND (rl.send_time >= :send_time_start )";
            $sql_values[':send_time_start'] = $filter['send_time_start'];
        }
        if (isset($filter['send_time_end']) && $filter['send_time_end'] != '') {
            $sql_main .= " AND (rl.send_time <= :send_time_end )";
            $sql_values[':send_time_end'] = date("Y-m-d", strtotime($filter['send_time_end'] . " +1 day"));
        }


        //交易号
        if (isset($filter['tid']) && $filter['tid'] != '') {
            //$sql_values = array();
            //$sql_main = $sql_main1;
            $sql_main .= " AND rl.tid LIKE :tid ";
            $sql_values[':tid'] = $filter['tid'] . '%';
        }
        //订单号
        if (isset($filter['sell_record_code']) && $filter['sell_record_code'] != '') {
            //$sql_values = array();
            //$sql_main = $sql_main1;
            $sql_main .= " AND rl.sell_record_code LIKE :sell_record_code ";
            $sql_values[':sell_record_code'] = $filter['sell_record_code'] . '%';
        }

        //快递单号
        if (isset($filter['express_no']) && $filter['express_no'] != '') {
            //$sql_values = array();
            //$sql_main = $sql_main1;
            $sql_main .= " AND rl.express_no LIKE :express_no ";
            $sql_values[':express_no'] = $filter['express_no'] . '%';
        }
        //增值服务
        $sql_main .= load_model('base/SaleChannelModel')->get_values_where('rl.source');


        $sql_main .= 'order by rl.send_time desc ';
        //echo $sql_main;
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        filter_fk_name($data['data'], array('express_code|express_company',));
        $ret_status = OP_SUCCESS;
        return $this->format_ret($ret_status, $data);
    }

    function update_by_id($id) {
        return $this->update_exp("api_order_send", array("status" => 1), array("api_order_send_id" => $id));
    }

    /*
     * 添加新纪录
     */

    function insert($data) {
        //$ret = $this->is_exists($data['sell_record_code']);
        //if (!empty($ret['data'])) return $this->format_ret(BRAND_ERROR_UNIQUE_CODE);

        return parent::insert($data);
    }

    //是否存在
    function is_exists($value, $field_name = 'sell_record_code') {
        $ret = parent::get_row(array($field_name => $value));

        return $ret;
    }

    //获取回写失败订单数量
    function get_count_by_status($status) {
        $sql = "select count(*) from api_order_send where status = :status";
        $sql_value[':status'] = $status;
        return $num = $this->db->get_value($sql, $sql_value);
    }

    /**
     * @param $id
     */
    function get_by_id($id) {
        $data = $this->get_row(array('api_order_send_id' => $id));
        return $data;
    }

    //发货回写
    function delivery_send($id, $force_send = 0) {
        $api_ret = $this->get_by_id($id);
        $api_order = $api_ret['data'];
        if ($api_order['source'] == 'mogujie' || $api_order['source'] == 'meilishuo') {
            $fun = 'xiaodian_api/trade_shipping_sync';
        } else if ($api_order['source'] == 'fenxiao') {
            $fun = 'taobao_api/trade_shipping_sync';
        } else {
            $fun = $api_order['source'] . '_api/trade_shipping_sync';
        }
        $params = array('shop_code' => $api_order['shop_code'], 'tid' => $api_order['tid'], 'force_send' => $force_send, 'user_name'=>CTX()->get_session('user_name'), 'user_code'=>CTX()->get_session('user_code'));
        $result = load_model('sys/EfastApiModel')->request_api($fun, $params);
        if ($result['resp_data']['code'] == '0') {
            $ret['status'] = '1';
            $ret['message'] = '回写成功';
        } else {
            $ret['status'] = '-1';
            $ret['message'] = $result['resp_data']['msg'];
        }
        return $ret;
    }

    /**
     * 回写
     * @param string $value
     * @param int $force_send 0-普通回写，1-强制回写
     * @param int $type 0-单条回写，1-批量回写
     */
    function send_order($value, $force_send = 0, $type = 0) {
        $data = $this->get_by_id($value);
        if (empty($data['data'])) {
            return $this->format_ret(-1, '', '回写失败');
        } else {
            return $this->delivery_send($data['data']['api_order_send_id'], $force_send);
        }
    }

    /**
     * 本地回写
     * @param string $id
     * @param string $type batch_send-批量回写 one-单条回写
     * @return array
     */
    function send_local($id, $type) {
        if (!load_model('sys/PrivilegeModel')->check_priv('api/sys/order_send/callback1')) {
            return $this->return_value(-1, "无权访问");
        }
        if (empty($id)) {
            return $this->format_ret(-1, '', '数据参数有误,请刷新页面重试');
        }

        $sql = 'SELECT `sell_record_code`,`status` FROM api_order_send WHERE api_order_send_id=:id';
        $order_data = $this->db->get_row($sql, [':id' => $id]);
        if (empty($order_data)) {
            return $this->format_ret(-1, '', '单据不存在');
        }
        if (in_array($order_data['status'], [1, 2])) {
            return $this->format_ret(1, '', '该订单已回写成功,不能再次操作');
        }

        $time = date("Y-m-d H:i:s");
        $this->update(['status' => 2, 'error_remark' => '', 'upload_time' => $time], ['api_order_send_id' => $id]);
        $this->update_exp('oms_sell_record', ['is_back' => 2, 'is_back_time' => $time], ['sell_record_code' => $order_data['sell_record_code']]);

        $action = $type == 'batch' ? '批量本地回写' : '本地回写';
        $message = $type == 'batch' ? '批量本地回写成功' : '本地回写成功';
        //添加日志
        load_model('oms/SellRecordActionModel')->add_action($order_data['sell_record_code'], $action, $message);


        return $this->format_ret(1, '', '回写成功');
    }

}
