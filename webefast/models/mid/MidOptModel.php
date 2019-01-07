<?php

require_model('mid/MidBaseModel');

class MidOptModel extends MidBaseModel {

    public $order_type_oms = array(
        'all' => '全部',
        'sell_record' => '销售订单',
        'sell_return' => '销售退单',
        'wbm_store_out' => '批发销货单',
        'wbm_return' => '批发退货单',
        'sell_record_rb' => '销售订单日报',
        'sell_return_rb' => '销售退单日报',
    );

    function mid_order_upload_type($param = array()) {

        $sql = " select id,record_code,record_type,api_product from mid_order where record_type = :record_type  AND upload_request_flag in(0,20) AND cancel_request_flag=0 AND cancel_flag=0 ";
        $sql_values = array(
            ':record_type' => $param['record_type'],
        );
        $data = $this->db->get_all($sql, $sql_values);
        foreach ($data as $val) {
            $this->upload_order($val);
        }
    }

    function mid_order_upload_all() {

        $param = array();
        foreach ($this->flow_type as $record_type => $val) {
            $param['record_type'] = $record_type;
            $this->mid_order_upload_type($param);
        }
    }

    function mid_order_shipping_all() {
        $param = array();
        foreach ($this->flow_type as $record_type => $val) {
            $param['record_type'] = $record_type;
            $this->mid_order_shipping_type($param);
        }
    }

    function mid_order_shipping_type($param) {
        $sql = " select id,record_code,record_type,api_product from mid_order where record_type = :record_type  
                and order_flow_end_flag = 1 and (process_flag = 0 or (process_flag = 20 and process_fail_num<3)) ";
        $sql_values = array(
            ':record_type' => $param['record_type'],
        );
        $data = $this->db->get_all($sql, $sql_values);
        foreach ($data as $val) {
            $this->order_shipping($val);
        }
        //    //    function order_shipping(&$order_info) {
    }

    function order_shipping($order_info) {
        $sql = "select * from  mid_order where id=:id ";
        $sql_values = array(
            ':id' => $order_info['id'],
        );
        $order_data = $this->db->get_row($sql, $sql_values);



        $record_code = $order_data['order_code'];
        if ($order_data['process_flag'] == 30) {
            return $this->format_ret(-1, '', $record_code . '单据已经处理，不能重复处理！');
        }

        $mod = $this->get_mod($order_data['record_type']);
        $ret_shipping = $mod->order_shipping($order_data);
        $this->process_shipping_after($ret_shipping, $order_data);
        return $ret_shipping;
    }

    function opt_order($id, $opt_type = 0) {
        $order_info['id'] = $id;
        if ($opt_type == 0) {
            $ret = $this->upload_order($order_info);
        } else if ($opt_type == 1) {
            $ret = $this->cancel_order($order_info);
        } else if ($opt_type == 2) {
            $ret = $this->order_shipping($order_info);
        } else {
            $ret = $this->format_ret(-1, '', '处理类型异常!');
        }
        return $ret;
    }

    function upload_order($order_info) {

        $sql = "select * from  mid_order where id=:id ";
        $sql_values = array(
            ':id' => $order_info['id'],
        );
        $order_data = $this->db->get_row($sql, $sql_values);
        $upload_request_flag = (int) $order_data['upload_request_flag'];

        $upload_response_flag = (int) $order_data['upload_response_flag'];
        $record_code = (string) $order_data['record_code'];
        $upload_request_time = $order_data['upload_request_time'];

        if ($upload_response_flag == 10) {
            return $this->format_ret(-1, '', $record_code . ' 已上传，不能重复上传');
        }
        $cancel_response_flag = (int) $order_data['cancel_response_flag'];
        if ($cancel_response_flag == 10) {
            return $this->format_ret(-1, '', $record_code . ' 已取消，不能上传');
        }
        $cancel_request_flag = (int) $order_data['cancel_request_flag'];
        if ($cancel_request_flag == 10) {
            return $this->format_ret(-1, '', $record_code . ' 取消请求发出成功,不能上传');
        }
        //缺少新单号处理

        $record_type = $order_data['record_type'];
        $api_product = $order_data['api_product'];
        $store_code = $order_data['efast_store_code'];

        if ($upload_request_flag == 10) {
            $upload_request_time_int = strtotime($upload_request_time);
            $time_cha = time() - $upload_request_time_int;
            if ($time_cha > 60) {
                $sql_request = "update mid_order set upload_request_flag=0,upload_request_time='0000-00-00 00:00:00' where  id = '{$order_info['id']}' AND cancel_request_flag=0 AND upload_request_flag=10";
                $this->db->query($sql_request);
            } else {
                return $this->format_ret(-1, '', $record_code . '上传中,不能重复上传');
            }
        }
        $new_upload_request_time = date('Y-m-d H:i:s');
        $sql_request = "update mid_order set upload_request_flag=10,upload_request_time='{$new_upload_request_time}' where  id = '{$order_info['id']}'  AND cancel_request_flag=0 AND upload_request_flag=0";
        $this->db->query($sql_request);
        $num = $this->db->affected_rows();
        if ($num == 0) {
            return $this->format_ret(-1, '', $record_code . '单据状态变化，暂时不能上传.');
        }



        $api_product_mod = $this->get_api_product_mod($api_product, $record_type, $store_code);

        $ret_upload = $api_product_mod->upload($record_code);
        $this->process_upload_after($ret_upload, $order_data);
        return $ret_upload;
    }

    function process_upload_after($ret_upload, $order_data) {
        if ($ret_upload['status'] > 0) {

            $time = date('Y-m-d H:i:s');
            $data['api_record_code'] = $ret_upload['data'];
            $data['upload_response_flag'] = 10;
            $data['upload_response_time'] = $time;

            $data['cancel_response_flag'] = 0;
            $data['cancel_request_flag'] = 0;
            $where = " id = '{$order_data['id']}'";

            $this->update_exp('mid_order', $data, $where);
        } else {

            $upload_response_err_msg = addslashes($ret_upload['message']);
            $time = date('Y-m-d H:i:s');
            $data['upload_request_flag'] = 0;
            $data['upload_request_time'] = $time;
            $data['upload_response_flag'] = 20;
            $data['upload_response_time'] = $time;
            $data['upload_response_err_msg'] = $upload_response_err_msg;

            $where = " id = '{$order_data['id']}' AND upload_response_flag<> 10  ";

            $this->db->update('mid_order', $data, $where);
        }
    }

    function process_shipping_after($ret_shipping, $order_data) {
        if ($ret_shipping['status'] > 0) {

            $time = date('Y-m-d H:i:s');
            $data['process_flag'] = 30;

            $data['process_time'] = $time;

            $where = " id = '{$order_data['id']}'";

            $this->db->update('mid_order', $data, $where);
        } else {

            $err_msg = addslashes($ret_shipping['message']);
            $time = date('Y-m-d H:i:s');
            $data['process_flag'] = 20;
            $data['process_time'] = $time;
            $data['process_fail_num'] = $order_data['process_fail_num'] + 1;
            $data['process_err_msg'] = $err_msg;

            $where = " id = '{$order_data['id']}'  ";

            $this->db->update('mid_order', $data, $where);
        }
    }

    function cancel_order($order_info) {
        $sql = "select * from  mid_order where id=:id ";
        $sql_values = array(
            ':id' => $order_info['id'],
        );
        $order_data = $this->db->get_row($sql, $sql_values);
        $upload_request_flag = (int) $order_data['upload_request_flag'];
        if ($upload_request_flag == 0) {
            $ret = $this->format_ret(1, '', '未上传，取消成功');
            $this->process_cancel_after($ret, $order_data);
            return $ret;
        }

        $record_code = (string) $order_data['record_code'];

        if ($order_data['cancel_response_flag'] == 10) {
            $ret = $this->format_ret(-1, '', $record_code . ' 已经取消,不能重复取消!');
            $this->process_cancel_after($ret, $order_data);
            return $ret;
        }


        if ($order_data['upload_request_flag'] == 10 && $order_data['upload_response_flag'] == 0) {
            $ret = $this->format_ret(-1, '', $record_code . ' 单据上传中，暂时不能取消..,');
            $this->process_cancel_after($ret, $order_data);
            return $ret;
        }

        if ($order_data['order_flow_end_flag'] == 1) {
            $ret = $this->format_ret(-1, '', $record_code . '单据已经回传收货状态，不能取消.');
            return $ret;
        }


        $api_product = $order_data['api_product'];
        $store_code = $order_data['efast_store_code'];
        $record_type = $order_data['record_type'];

        $api_product_mod = $this->get_api_product_mod($api_product, $record_type, $store_code);
        if ($api_product_mod === FALSE) {
            $ret = $this->format_ret(1, '', $record_code . '对应仓库异常.');
        }
        $ret_cancel = $api_product_mod->cancel($record_code);


        $this->process_cancel_after($ret_cancel, $order_data);
        return $ret_cancel;
    }

    function process_cancel_after($ret_cancel, $order_data) {

        if ($ret_cancel['status'] > 0) {

            $time = date('Y-m-d H:i:s');
            $data['wms_record_code'] = $ret_cancel['data'];
            $data['cancel_request_time'] = 10;
            $data['cancel_request_time'] = $time;
            $data['cancel_response_flag'] = 10;
            $data['cancel_response_time'] = $time;

            $data['cancel_flag'] = 1;

            $where = " id = '{$order_data['id']}'";

            $this->db->update('mid_order', $data, $where);
        } else {

            $upload_response_err_msg = addslashes($ret_cancel['message']);
            $time = date('Y-m-d H:i:s');
            $data['cancel_request_flag'] = 0;
            $data['upload_request_time'] = $time;
            $data['cancel_response_flag'] = 20;
            $data['upload_response_time'] = $time;
            $data['cancel_response_err_msg'] = $upload_response_err_msg;

            $where = " id = '{$order_data['id']}' AND upload_response_flag<> 10  ";

            $this->db->update('mid_order', $data, $where);
        }
    }

    function get_api_product_mod($api_product, $record_type, $store_code) {


        static $api_mod_arr = array();

        if (!isset($api_mod_arr[$api_product][$store_code][$record_type])) {

            $ret = load_model('mid/MidApiConfigModel')->get_mid_config_by_sys_code($store_code, $api_product);

            if ($ret['status'] < 1) {
                return false;
            }

            $api_mod_name = ucfirst($api_product);
            $name_arr = explode('_', $record_type);
            $mod_name = '';
            foreach ($name_arr as $name) {
                $mod_name.=ucfirst($name);
            }
            $mod_name .= 'Model';
            $api_mod_name .= $mod_name;
            $mod_path = 'mid/' . $api_product . '/' . $api_mod_name;
            require_model($mod_path);

            $record_model_name = 'Mid' . $mod_name;

            $record_model = load_model('mid/' . $record_model_name);

            $api_mod_arr[$api_product][$store_code][$record_type] = new $api_mod_name($record_model, $ret['data']);
        }

        return $api_mod_arr[$api_product][$store_code][$record_type];
    }

    function do_list_by_page($filter) {
        $filter['ref'] = 'do';
        if (isset($filter['supplier_code']) && $filter['supplier_code'] == 'all') {
            unset($filter['supplier_code']);
        }
        //页面默认只查询显示的仓库的零售单
        if (empty($filter['efast_store_code'])) {
            $store_data = $this->get_mid_store();

            foreach ($store_data as $value) {
                $filter['efast_store_code'][] = $value['store_code'];
            }
            $filter['efast_store_code'] = implode(',', $filter['efast_store_code']);
        }
        return $this->get_by_page($filter);
    }

    function get_mid_store() {
        $sql = "SELECT s.join_sys_code,w.mid_code,b.store_name FROM  mid_api_join s
                INNER JOIN mid_api_config w ON s.mid_code=w.mid_code
                INNER JOIN base_store b ON b.store_code=s.join_sys_code
                WHERE s.join_sys_type = 1 AND s.outside_type = 1";
        $sql .= load_model('base/StoreModel')->get_sql_purview_store('b.store_code', NULL);
        $rs = $this->db->get_all($sql);


        //print_r($rs);
        $ret_data = array();
        foreach ($rs as $r) {
            $store = array('store_code' => $r['join_sys_code'], 'store_name' => $r['store_name']);
            $ret_data[] = $store;
        }
        return $ret_data;
    }

    /**
     * 根据条件查询数据<br>
     * 注意: 此方法为核心公共方法, 受多处调用, 请慎重修改.
     * @param $filter
     * @param $onlySql
     * @param $select
     * @return array
     */
    function get_by_page($filter, $select = 'DISTINCT rl.*') {
        foreach ($filter as $key => $v) {
            $filter[$key] = trim($v);
        }
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = $filter['keyword'];
        }
        $sql_values = array();
        $sql_join = "";
        $sql_main = "FROM mid_order rl $sql_join WHERE 1 ";


        $bak_sql_main = $sql_main;
        $sql_one_main_arr = array();
        $sql_one_values = array();

        //商店仓库权限
        $filter_store_code = isset($filter['efast_store_code']) ? $filter['efast_store_code'] : null;

        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.efast_store_code', $filter_store_code);


        $array_state = array();
        $tab = empty($filter['do_list_tab']) ? 'tabs_all' : $filter['do_list_tab'];

        switch ($tab) {
            case 'tabs_all'://全部
                ;
                break;
            case 'tabs_wait_upload'://待上传
                $array_state[] = ' rl.upload_request_flag=0'; //
                $array_state[] = 'rl.upload_response_flag=0';
                $array_state[] = 'rl.order_from_flag=0';
                $array_state[] = 'rl.cancel_response_flag<>10';
                break;
            case 'tabs_have_uploaded'://已上传
                $array_state[] = 'rl.upload_request_flag=10';
                $array_state[] = 'rl.upload_response_flag=10';
                $array_state[] = 'rl.cancel_response_flag<>10';
                $array_state[] = 'rl.process_flag=0';
                $array_state[] = 'rl.order_flow_end_flag=0';
                break;
            case 'tabs_wait_order'://待发货/待收货
                $array_state[] = 'rl.upload_request_flag=10';
                $array_state[] = 'rl.upload_response_flag=10';
                $array_state[] = ' (  rl.cancel_request_flag=0  OR  rl.cancel_response_flag=20   ) ';
                $array_state[] = ' rl.order_flow_end_flag=0 ';
                break;
            case 'tabs_wait_process'://待处理
                //rl.wms_order_flow_end_flag=1 AND rl.process_flag <30
                $array_state[] = ' rl.order_flow_end_flag=1';
                $array_state[] = '( rl.process_flag = 0 or rl.process_flag = 20)';
                break;
            case 'tabs_ordered'://已发货/已收货
                $array_state[] = 'rl.order_flow_end_flag=1';
                $array_state[] = 'rl.process_flag=30';
                break;
            case 'tabs_cancel':
                $array_state[] = 'rl.cancel_request_flag=10';
                $array_state[] = 'rl.cancel_response_flag=10';
                break;
            case 'tabs_fail'://操作失败：   取消失败  处理失败 上传失败
                $array_state[] = '((rl.cancel_response_flag=20) OR (rl.process_flag = 20) OR (rl.upload_response_flag = 20 AND rl.cancel_response_flag<>10))';
                break;
        }
        //取消状态
        if (isset($filter['cancel_request_flag']) && $filter['cancel_request_flag'] !== '') {

            $cancel_status = $filter['cancel_request_flag'];

            switch ($cancel_status) {
                case 'all'://全部
                    ;
                    break;
                case 'wait_cancel'://未取消
                    $array_state[] = 'rl.upload_request_flag in (0,20)';
                    $array_state[] = 'rl.upload_response_flag=10';
                    $array_state[] = 'rl.order_flow_end_flag=0';

                    break;
                case 'canceling'://取消中
                    $array_state[] = 'cancel_request_flag=10';
                    $array_state[] = 'cancel_response_flag=0';
                    $array_state[] = 'order_flow_end_flag=0';
                    break;
                case 'cancel_success'://取消成功
                    $array_state[] = 'cancel_request_flag=10';
                    $array_state[] = 'cancel_response_flag=10';
                    $array_state[] = 'order_flow_end_flag=0';

                    break;
                case 'cancel_fail'://取消失败
                    $array_state[] = 'cancel_response_flag=20';
                    $array_state[] = 'order_flow_end_flag=0';
                    break;
            }
        }

        $join_status_sql = '';

        if (!empty($array_state) && $array_state[0] != '') {
            $new_array_state = array_unique($array_state);
            $join_status_sql = implode(' and ', $new_array_state);
            $join_status_sql = ' AND ' . $join_status_sql;
        }


        if (isset($filter['deal_code']) && $filter['deal_code'] !== '') {
            $sql_main .= " AND rl.deal_code like :deal_code ";
            $sql_values[':deal_code'] = "%" . $filter['deal_code'] . "%";
        }

        if (isset($filter['record_code']) && $filter['record_code'] !== '') {
            $sql_main .= " AND rl.record_code like :record_code ";
            $sql_values[':record_code'] = "%" . $filter['record_code'] . "%";
        }

        if (isset($filter['api_record_code']) && $filter['api_record_code'] !== '') {
            $sql_main .= " AND rl.api_record_code like :api_record_code ";
            $sql_values[':api_record_code'] = "%" . $filter['api_record_code'] . "%";
        }

        //仓库
        if (isset($filter['store_code']) && $filter['store_code'] !== '') {
            $arr = explode(',', $filter['store_code']);
            $str = $this->arr_to_in_sql_value($arr, 'store_code', $sql_values);
            $sql_main .= " AND rl.efast_store_code in ( " . $str . " ) ";
        }


        //订单类型
        if (isset($filter['supplier_code']) && $filter['supplier_code'] !== '') {
            $sql_main .= " AND rl.record_type = :supplier_code ";
            $sql_values[':supplier_code'] = $filter['supplier_code'];
        }

        //时间处理
        if (isset($filter['time_type']) && $filter['time_type'] !== '') {
            if ($filter['time_type'] == 'order_time') {
                if (isset($filter['time_start']) && $filter['time_start'] !== '') {
                    $time_start = strtotime($filter['time_start'] . ' 00:00:00');
                    $sql_main .= " AND rl.order_time >= :time_start ";
                    $sql_values[':time_start'] = $time_start;
                }
                if (isset($filter['time_end']) && $filter['time_end'] !== '') {
                    $time_end = strtotime($filter['time_end'] . ' 23:59:59');
                    $sql_main .= " AND rl.order_time <= :time_end ";
                    $sql_values[':time_end'] = $time_end;
                }
            } else {
                if (isset($filter['time_start']) && $filter['time_start'] !== '') {
                    $sql_main .= " AND rl." . $filter['time_type'] . " >= :time_start ";
                    $sql_values[':time_start'] = $filter['time_start'] . ' 00:00:00';
                }
                if (isset($filter['time_end']) && $filter['time_end'] !== '') {
                    $sql_main .= " AND rl." . $filter['time_type'] . " <= :time_end ";
                    $sql_values[':time_end'] = $filter['time_end'] . ' 23:59:59';
                }
            }
        }

        $group_by = '';
        if (!empty($filter['group_by'])) {
            $group_by = " group by " . $filter['group_by'] . " ";
        }
        $order_by = " ORDER BY id DESC";



        $sql_main .= $join_status_sql;

        $sql_main .= $group_by;
        $sql_main .= $order_by;
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $tbl_cfg = array(
            'base_sale_channel' => array('fld' => 'sale_channel_code,sale_channel_name', 'relation_fld' => 'sale_channel_code+sale_channel_code'),
            'base_shop' => array('fld' => 'shop_name', 'relation_fld' => 'shop_code+shop_code'),
            'base_store' => array('fld' => 'store_name', 'relation_fld' => 'store_code+efast_store_code'),
            'base_pay_type' => array('fld' => 'pay_type_name', 'relation_fld' => 'pay_type_code+pay_code'),
            'base_express' => array('fld' => 'express_name', 'relation_fld' => 'express_code+express_code'),
            'base_area' => array('fld' => 'name as receiver_province_txt', 'relation_fld' => 'id+receiver_province'),
            'base_area#1' => array('fld' => 'name as receiver_city_txt', 'relation_fld' => 'id+receiver_city'),
            'base_area#2' => array('fld' => 'name as receiver_district_txt', 'relation_fld' => 'id+receiver_district'),
        );

        require_model('util/GetDataBySqlRelModel');
        $obj = new GetDataBySqlRelModel();
        $obj->tbl_cfg = $tbl_cfg;
        $data['data'] = $obj->get_data_by_cfg(null, $data['data']);
        // $sys_user = load_model("oms/SellRecordOptModel")->sys_user();
        $new_order_type = $this->order_type_oms;
        foreach ($data['data'] as $key => &$value) {
            $status = $this->get_status_exp($value);

            $value['status'] = str_replace('|', "<br>", $status['status_txt_ex']);

            //单据类型
            $value['record_order_type'] = $new_order_type[$value['record_type']];

            //处理时间显示
            $value['upload_request_time'] = ($value['upload_request_time'] > 0) ? $value['upload_request_time'] : '';
            $value['cancel_request_time'] = ($value['cancel_request_time'] > 0) ? $value['cancel_request_time'] : '';


            $value['order_time'] = ($value['order_time'] > 0) ? date("Y-m-d H:i:s", $value['order_time']) : '';
            $value['process_time'] = ($value['process_time'] > 0) ? $value['process_time'] : '';
            $value['log_err_msg'] = htmlspecialchars($this->getProcessEssMsg($value));
            if ($value['api_product'] == 'bserp2') {
                $value['order_time'] = '';
                $value['cancel_response_time'] = '';
            }
        }
        return $this->format_ret(1, $data);
    }

    function get_status_exp($info) {
        if (!is_array($info)) {
            $ret = array('status' => 'no_found', 'status_txt' => '未找到任务', 'status_txt_ex' => '未找到任务');
            return $ret;
        }
        /*
          echo "<xmp>";debug_print_backtrace();echo "</xmp>";
          echo '<hr/>info<xmp>'.var_export($info,true).'</xmp>';
          die; */
        $upload_request_flag = $info['upload_request_flag'];
        $upload_response_flag = $info['upload_response_flag'];
        $cancel_request_flag = $info['cancel_request_flag'];
        $cancel_response_flag = $info['cancel_response_flag'];
        $order_flow_end_flag = $info['order_flow_end_flag'];
        $process_flag = $info['process_flag'];
        $ret = null;

        $upload_status = '未上传';
        if ($upload_request_flag == '10') {
            $upload_status = '上传中';
        }
        if ($upload_response_flag == '10') {
            $upload_status = '上传成功';
        }
        if ($upload_response_flag == '20') {
            $upload_status = '上传失败';
        }

        $cancel_status = '未取消';
        if ($cancel_request_flag == '10') {
            $cancel_status = '取消中';
        }
        if ($cancel_response_flag == '10') {
            $cancel_status = '取消成功';
        }
        if ($cancel_response_flag == '20') {
            $cancel_status = '取消失败';
        }

        if ($process_flag == 20) {
            $ret = array('status' => 'order_status_sync_fail', 'status_txt' => '处理失败', 'status_txt_ex' => "{$upload_status} | {$cancel_status} | 已收发货 | 处理失败");
        }
        if ($process_flag == 30 && is_null($ret)) {
            $ret = array('status' => 'order_status_sync_success', 'status_txt' => '处理成功', 'status_txt_ex' => "{$upload_status} | {$cancel_status} | 已收发货 | 处理成功");
        }
        if ($order_flow_end_flag == 1 && is_null($ret)) {
            $ret = array('status' => 'order_end', 'status_txt' => '已收发货', 'status_txt_ex' => "{$upload_status} | {$cancel_status} | 已收发货 | 未处理");
        }
        if ($order_flow_end_flag == 2 && is_null($ret)) {
            $ret = array('status' => 'order_close', 'status_txt' => '已关闭', 'status_txt_ex' => "{$upload_status} | {$cancel_status} | 已关闭 | 未处理");
        }
        if ($cancel_response_flag == 10 && is_null($ret)) {
            $ret = array('status' => 'cancel_success', 'status_txt' => '取消成功', 'status_txt_ex' => "{$upload_status} | {$cancel_status} | 未收发货 | 未处理");
        }
        if ($cancel_response_flag == 20 && is_null($ret)) {
            $ret = array('status' => 'cancel_fail', 'status_txt' => '取消失败', 'status_txt_ex' => "{$upload_status} | {$cancel_status} | 未收发货 | 未处理");
        }
        if ($cancel_request_flag == 10 && is_null($ret)) {
            $ret = array('status' => 'canceling', 'status_txt' => '取消中', 'status_txt_ex' => "{$upload_status} | {$cancel_status} | 未收发货 | 未处理");
        }
        if ($upload_response_flag == 10 && is_null($ret)) {
            $ret = array('status' => 'upload_success', 'status_txt' => '上传成功', 'status_txt_ex' => "{$upload_status} | {$cancel_status} | 未收发货 | 未处理");
        }
        if ($upload_response_flag == 20 && is_null($ret)) {
            $ret = array('status' => 'upload_fail', 'status_txt' => '上传失败', 'status_txt_ex' => "{$upload_status} | {$cancel_status} | 未收发货 | 未处理");
        }
        if ($upload_request_flag == 10 && is_null($ret)) {
            $ret = array('status' => 'uploading', 'status_txt' => '上传中', 'status_txt_ex' => "{$upload_status} | {$cancel_status} | 未收发货 | 未处理");
        }
        if ($upload_request_flag == 0 && is_null($ret)) {
            $ret = array('status' => 'wait_upload', 'status_txt' => '未上传', 'status_txt_ex' => "{$upload_status} | {$cancel_status} | 未收发货 | 未处理");
        }
        return $ret;
    }

    /**
     * 单据同步到中间表
     */
    function sys_to_mid() {
        $flow_arr = array(
            'sell_record' => 'send',
            'sell_return' => 'receiving',
            'wbm_store_out' => 'send',
            'wbm_return' => 'receiving',
        );
        foreach ($flow_arr as $record_type => $flow_type) {
            $flow_row = load_model('mid/MidBaseModel')->check_flow($flow_type, $record_type);
            if (!empty($flow_row)) {
                $api_product = $flow_row['api_product'];
                $data = $this->get_api_product_info_all($api_product);
                foreach ($data as $api_product_info) {
                    $this->sys_to_mid_type($record_type, $api_product_info);
                }
            }
        }
    }

    /**
     * 数据同步到中间表
     * @param type $record_type
     * @param type $api_product_info
     */
    function sys_to_mid_type($record_type, $api_product_info) {
        $record_type_arr = array('sell_record','sell_return');
        
        if (in_array($record_type, $record_type_arr)&&$api_product_info['api_product'] == 'bserp2' && $api_product_info['api_config']['connection_mode'] == 2) {//日报模式
            return $this->format_ret(1);
        }
        $type_code = 'to_mid_' . $record_type;
        $exec_record = $this->get_mid_api_record($api_product_info['mid_code'], $type_code);
        $exec_time = $api_product_info['online_time'] . ' 00:00:00';


        $now_time = date('Y-m-d H:i:s');
        $api_product = $api_product_info['api_product'];


        $mid_code = $api_product_info['mid_code'];
        $store_arr = array();
        foreach ($api_product_info['api_join_data'] as $val) {
            $store_arr[] = $val['join_sys_code'];
        }

        $shop_arr = array();
        foreach ($api_product_info['api_join_shop'] as $val) {
            $shop_arr[] = $val['join_sys_code'];
        }
        $shop_where = '';
        if (!empty($shop_arr)) {
            $shop_where = " AND shop_code in(" . implode("','", $shop_arr) . " ) ";
        }


        if (!empty($exec_record)) {
            $last_api_time = $exec_record['last_api_time'];
            $exec_record_time = date('Y-m-d H:i:s', strtotime($last_api_time) - 60);
            $exec_time = strtotime($exec_record_time) < strtotime($exec_time) ? $exec_time : $exec_record_time;
        } else {
            $exec_record = array(
                'mid_code' => $mid_code,
                'api_product' => $api_product,
                'api_name' => $type_code,
                'last_api_time' => $now_time,
            );
        }

        $sql_select = "";
        //对应仓库
        $store_str = "'" . implode("','", $store_arr) . "'";
        if ($record_type == 'sell_record') {
            $sql_select = " select 
            sell_record_code AS record_code,'sell_record' as record_type , '{$api_product}' as api_product, '{$mid_code}' as mid_code,
           store_code as efast_store_code,'{$now_time}' as create_time ,  delivery_time  as order_time,deal_code_list as deal_code
      
            from oms_sell_record  where order_status=1 AND shipping_status =4 and delivery_time>='{$exec_time}' AND store_code in({$store_str})  {$shop_where} ";
        } else if ($record_type == 'sell_return') {
            $sql_select = "select sell_return_code AS record_code,'sell_return' as record_type ,'{$api_product}' as api_product,'{$mid_code}' as mid_code,
                store_code as efast_store_code, '{$now_time}' as create_time ,  receive_time  as order_time,deal_code as deal_code
               from oms_sell_return WHERE return_order_status=1 AND return_shipping_status=1 AND  receive_time>='{$exec_time}' AND store_code in({$store_str}) {$shop_where}  ";
        }else if($record_type == 'wbm_store_out'){
               $sql_select = "select record_code AS record_code,'wbm_store_out' as record_type ,'{$api_product}' as api_product,'{$mid_code}' as mid_code,
                store_code as efast_store_code, '{$now_time}' as create_time ,  order_time  as order_time,relation_code as deal_code
               from wbm_store_out_record WHERE is_sure=1 AND is_store_out=1 AND  order_time>='{$exec_time}' AND store_code in({$store_str})  ";    
        }else if($record_type == 'wbm_return'){
                          $sql_select = "select record_code AS record_code,'wbm_return' as record_type ,'{$api_product}' as api_product,'{$mid_code}' as mid_code,
                store_code as efast_store_code, '{$now_time}' as create_time ,  order_time  as order_time,relation_code as deal_code
               from wbm_return_record WHERE is_sure=1 AND is_store_in=1 AND  order_time>='{$exec_time}' AND store_code in({$store_str})  ";    
        }
        
        
        
        if (!empty($sql_select)) {
            $sql_insert = " INSERT  IGNORE INTO  mid_order (record_code,record_type,api_product,mid_code,efast_store_code,create_time,order_time,deal_code) ";
            $sql = $sql_insert . $sql_select;
            $this->db->query($sql);
        }
        
        
        
        $this->save_api_record($exec_record);
    }

    function get_mid_api_record($mid_code, $api_name) {
        $sql = "select * from mid_api_record where mid_code=:mid_code AND api_name =:api_name";
        $values = array(
            ':mid_code' => $mid_code,
            ':api_name' => $api_name,
        );
        return $this->db->get_row($sql, $values);
    }

    function save_api_record($data) {
        $update_arr = array();
        $key_arr = array(
            'start_time', 'end_time', 'request_data', 'last_api_time', 'api_request_time'
        );
        foreach ($key_arr as $key) {
            if (isset($data[$key])) {
                $update_arr[] = " {$key} = VALUES({$key}) ";
            }
        }

        $update_str = implode(",", $update_arr);
        $this->insert_multi_duplicate('mid_api_record', array($data), $update_str);
    }

    function get_api_product_info_all($api_product) {
        static $api_product_data = null;

        if (!isset($api_product_data[$api_product])) {
            $api_product_info = load_model('mid/MidApiConfigModel')->get_mid_api_config_by_api_product($api_product);
            foreach ($api_product_info as $key => &$val) {
                $mid_join_data = load_model('mid/MidApiConfigModel')->get_join_data($val['mid_code']);
                $val['api_join_data'] = $mid_join_data;

                if ($api_product_info['api_product'] == 'bserp2') {
                    $mid_join_data_shop = load_model('mid/MidApiConfigModel')->get_join_data($val['mid_code'], 0);
                    $val['api_join_shop'] = $mid_join_data_shop;
                }

                $api_product_info[$key] = $val;
            }
            $api_product_data[$api_product] = $api_product_info;
        }
        return $api_product_data[$api_product];
    }

}
