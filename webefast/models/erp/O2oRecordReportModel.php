<?php
require_model('tb/TbModel');

class O2oRecordReportModel extends TbModel {
    public $detail_table = 'o2o_oms_order';
    public $record_type_name = array(
        'sell_record' => '销售订单',
        'sell_return' => '销售退单',
    );
    function get_table() {
        return 'o2o_oms_trade';
    }


    /**
     * 单据列表
     * @param $filter
     * @return array
     */
    function do_list_by_page($filter) {
        $sql_values = array();
        $sql_main = "FROM {$this->table} AS rl WHERE 1";

        foreach ($filter as $key => $v) {
            $filter[$key] = trim($v);
        }
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = $filter['keyword'];
        }
        //仓库权限
        $filter_store_code = isset($filter['sys_store_code']) ? $filter['sys_store_code'] : null;
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.sys_store_code', $filter_store_code);
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('rl.shop_code', $filter_shop_code);

        //tab标签
        $array_state = array();
        $tab = empty($filter['do_list_tab']) ? 'tabs_wait_upload' : $filter['do_list_tab'];
        switch ($tab) {
            case 'tabs_all'://全部
                break;
            case 'tabs_wait_upload'://待上传
                $array_state[] = 'rl.upload_request_flag=0';
                $array_state[] = 'rl.upload_response_flag=0';
                $array_state[] = 'rl.api_order_flow_end_flag=0';
                $array_state[] = 'rl.cancel_response_flag<>10';
                break;
            case 'tabs_wait_order'://待发货/待收货
                $array_state[] = 'rl.upload_request_flag=10';
                $array_state[] = 'rl.upload_response_flag=10';
                $array_state[] = '(rl.cancel_request_flag=0  OR  rl.cancel_response_flag=20) ';
                $array_state[] = 'rl.api_order_flow_end_flag=0 ';
                break;
            case 'tabs_wait_process'://待处理
                $array_state[] = 'rl.api_order_flow_end_flag=1';
                $array_state[] = '(rl.process_flag = 0 or rl.process_flag = 20)';
                break;
            case 'tabs_ordered'://已发货/已收货
                $array_state[] = 'rl.api_order_flow_end_flag=1';
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
                    break;
                case 'wait_cancel'://未取消
                    $array_state[] = 'rl.upload_request_flag IN (0,20)';
                    $array_state[] = 'rl.upload_response_flag=10';
                    $array_state[] = 'rl.api_order_flow_end_flag=0';
                    break;
                case 'canceling'://取消中
                    $array_state[] = 'cancel_request_flag=10';
                    $array_state[] = 'cancel_response_flag=0';
                    $array_state[] = 'api_order_flow_end_flag=0';
                    break;
                case 'cancel_success'://取消成功
                    $array_state[] = 'cancel_request_flag=10';
                    $array_state[] = 'cancel_response_flag=10';
                    $array_state[] = 'api_order_flow_end_flag=0';
                    break;
                case 'cancel_fail'://取消失败
                    $array_state[] = 'cancel_response_flag=20';
                    $array_state[] = 'api_order_flow_end_flag=0';
                    break;
            }
        }
        if (!empty($array_state)) {
            $new_array_state = array_unique($array_state);
            $join_status_sql = implode(' AND ', $new_array_state);
            $sql_main .= ' AND ' . $join_status_sql;
        }

        //仓库
        if (isset($filter['sys_store_code']) && $filter['sys_store_code'] !== '') {
            $arr = explode(',', $filter['sys_store_code']);
            $str = $this->arr_to_in_sql_value($arr, 'sys_store_code', $sql_values);
            $sql_main .= " AND rl.sys_store_code in ( " . $str . " ) ";
        }
        //销售平台
        if (isset($filter['sale_channel_code']) && $filter['sale_channel_code'] !== '') {
            $arr = explode(',', $filter['sale_channel_code']);
            $str = $this->arr_to_in_sql_value($arr, 'sale_channel_code', $sql_values);
            $sql_main .= " AND rl.sale_channel_code in ( {$str} ) ";
        }
        //店铺
        if (isset($filter['shop_code']) && $filter['shop_code'] !== '') {
            $arr = explode(',', $filter['shop_code']);
            $str = $this->arr_to_in_sql_value($arr, 'shop_code', $sql_values);
            $sql_main .= " AND rl.shop_code in ( " . $str . " ) ";
        }
        //eFAST订单号
        if (isset($filter['record_code']) && $filter['record_code'] !== '') {
            $sql_main .= " AND rl.record_code=:record_code ";
            $sql_values[':record_code'] = $filter['record_code'];
        }
        //单据号
        if (isset($filter['api_record_code']) && $filter['api_record_code'] !== '') {
            $sql_main .= " AND rl.api_record_code = :api_record_code ";
            $sql_values[':api_record_code'] = $filter['api_record_code'];
        }
        //新订单号
        if (isset($filter['new_record_code']) && $filter['new_record_code'] !== '') {
            $sql_main .= " AND rl.new_record_code = :new_record_code ";
            $sql_values[':new_record_code'] = $filter['new_record_code'];
        }
        //订单类型
        if (isset($filter['record_type']) && $filter['record_type'] !== '') {
            $sql_main .= " AND rl.record_type = :record_type ";
            $sql_values[':record_type'] = $filter['record_type'];
        }
        //页面默认只查询显示的仓库的零售单
        if (empty($filter['sys_store_code'])) {
            $store_data = $this->get_o2o_store_all();
            $store_arr = array_column($store_data, 'store_code');
            $store_str = $this->arr_to_in_sql_value($store_arr, 'sys_store_code_priv', $sql_values);
            $sql_main .= " AND rl.sys_store_code IN({$store_str}) ";
        }
        //时间查询
        if (isset($filter['start_time']) && $filter['start_time'] != '') {
            switch ($filter['time_type']) {
                case 'upload_request_time':
                    $sql_main .= " AND rl.upload_request_time >= :start_time ";
                    $sql_values[':start_time'] = $filter['start_time']. ' 00:00:00';
                    break;
                case 'api_order_time':
                    $sql_main .= " AND rl.api_order_time >= :start_time ";
                    $sql_values[':start_time'] = $filter['start_time']. ' 00:00:00';
                    break;
                case 'process_time':
                    $sql_main .= " AND rl.process_time >= :start_time ";
                    $sql_values[':start_time'] = $filter['start_time']. ' 00:00:00';
                    break;
                case 'cancel_request_time':
                    $sql_main .= " AND rl.cancel_request_time >= :start_time ";
                    $sql_values[':start_time'] = $filter['start_time']. ' 00:00:00';
                    break;
            }
        }
        if (isset($filter['end_time']) && $filter['end_time'] != '') {
            switch ($filter['time_type']) {
                case 'upload_request_time':
                    $sql_main .= " AND rl.upload_request_time <= :end_time ";
                    $sql_values[':end_time'] = $filter['end_time']. ' 23:59:59';
                    break;
                case 'api_order_time':
                    $sql_main .= " AND rl.api_order_time <= :end_time ";
                    $sql_values[':end_time'] = $filter['end_time']. ' 23:59:59';
                    break;
                case 'process_time':
                    $sql_main .= " AND rl.process_time <= :end_time ";
                    $sql_values[':end_time'] = $filter['end_time'] . ' 23:59:59';
                    break;
                case 'cancel_request_time':
                    $sql_main .= " AND rl.cancel_request_time <= :end_time ";
                    $sql_values[':end_time'] = $filter['end_time'] . ' 23:59:59';
                    break;
            }
        }
        $select = "rl.*";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as $key => &$value) {
            $status = load_model('o2o/O2oRecordModel')->get_status_exp($value);
            $value['status'] = str_replace('|', "<br>", $status['status_txt_ex']);
            //处理时间显示
            $value['upload_request_time'] = ($value['upload_request_time'] > 0) ? $value['upload_request_time'] : '';
            $value['cancel_request_time'] = ($value['cancel_request_time'] > 0) ? $value['cancel_request_time'] : '';
            $value['api_order_time'] = ($value['api_order_time'] > 0) ? date("Y-m-d H:i:s", $value['api_order_time']) : '';
            $value['process_time'] = ($value['process_time'] > 0) ? $value['process_time'] : '';
            $value['log_err_msg'] = htmlspecialchars($this->getProcessEssMsg($value));
            $value['sale_channel_name'] = (!empty($value['sale_channel_code']))? load_model('oms/SellRecordModel')->get_sale_channel_name_by_code($value['sale_channel_code']) : '';
            $value['shop_name'] = (!empty($value['shop_code'])) ? oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code'])) : '';
            $value['store_name'] = (!empty($value['sys_store_code'])) ? oms_tb_val('base_store', 'store_name', array('store_code' => $value['sys_store_code'])) : '';
            $value['record_type_name']=$this->record_type_name[$value['record_type']];
        }
        return $this->format_ret(1, $data);

    }

    //单据上传（单个），批量的没做
    function o2o_record_upload($id) {
        //调用定时器方法
        $obj = load_model('o2o/O2oMgrModel');
        $ret = $obj->upload($id);
        return $ret;
    }


    /**
     * 获取对接仓库
     * @return array
     */
    function get_o2o_store_all() {
        $sql = "select s.shop_store_code,b.store_name from erp_config e 
            INNER JOIN sys_api_shop_store s ON e.erp_config_id=s.p_id AND s.p_type=0
            INNER JOIN base_store b ON b.store_code=s.shop_store_code
            where s.shop_store_type=1  AND s.outside_type = 1 AND (s.store_type = 1 or s.store_type = 0) ";
        $sql .= load_model('base/StoreModel')->get_sql_purview_store('b.store_code', NULL);
        $data = $this->db->get_all($sql);
        $ret_data = array();
        foreach ($data as $val) {
            $store = array('store_code' => $val['shop_store_code'], 'store_name' => $val['store_name']);
            $ret_data[] = $store;
        }
        return $ret_data;
    }

    /**
     * 组装错误日志
     * @param $value
     * @return string
     */
    function getProcessEssMsg($value) {
        $log_err_msg = '';
        //上传失败
        if ($value['upload_response_flag'] == 20 && $value['api_order_flow_end_flag'] == 0) {
            $log_err_msg .= $value['upload_response_err_msg'] . "<br>";
        }
        //取消失败
        if ($value['cancel_response_flag'] == 20 && $value['api_order_flow_end_flag'] == 0) {
            $log_err_msg .= $value['cancel_response_err_msg'] . "<br>";
        }
        // 处理失败 process_flag = 20
        if ($value['process_flag'] == 20) {
            $log_err_msg .= $value['process_err_msg'];
        }
        return $log_err_msg;
    }

}