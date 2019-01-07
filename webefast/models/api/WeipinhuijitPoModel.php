<?php

require_model('tb/TbModel');

class WeipinhuijitPoModel extends TbModel {

    protected $table = "api_weipinhuijit_po";

    function get_by_page($filter) {
        $sql_main = "FROM {$this->table}  WHERE 1";

        $sql_values = array();
        //商店权限1
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('shop_code', $filter_shop_code);

        //开始时间
        if (isset($filter['st_time_start']) && $filter['st_time_start'] != '') {
            $sql_main .= " AND (sell_st_time >= :st_time_start )";
            $sql_values[':st_time_start'] = $filter['st_time_start'] . ' 00:00:00';
        }
        if (isset($filter['st_time_end']) && $filter['st_time_end'] != '') {
            $sql_main .= " AND (sell_st_time <= :st_time_end )";
            $sql_values[':st_time_end'] = $filter['st_time_end'] . ' 23:59:59';
        }
        //结束时间
        if (isset($filter['et_time_start']) && $filter['et_time_start'] != '') {
            $sql_main .= " AND (sell_et_time >= :et_time_start )";
            $sql_values[':et_time_start'] = $filter['et_time_start'] . ' 00:00:00';
        }
        if (isset($filter['et_time_end']) && $filter['et_time_end'] != '') {
            $sql_main .= " AND (sell_et_time <= :et_time_end )";
            $sql_values[':et_time_end'] = $filter['et_time_end'] . ' 23:59:59';
        }
        //店铺
        if (isset($filter['shop_code']) && $filter['shop_code'] <> '') {
            $arr = explode(',', $filter['shop_code']);
            $str = $this->arr_to_in_sql_value($arr, 'shop_code', $sql_values);
            $sql_main .= " AND shop_code in ({$str}) ";
        }
        //档期号
        if (isset($filter['po_no']) && $filter['po_no'] != '') {
            $sql_main .= " AND po_no = :po_no ";
            $sql_values[':po_no'] = $filter['po_no'];
        }
        if (isset($filter['notice_record_no']) && $filter['notice_record_no'] != '') {
            $sql_main .= " AND notice_record_no = :notice_record_no ";
            $sql_values[':notice_record_no'] = $filter['notice_record_no'];
        }
        //拣货单所对应的档期
        if (isset($filter['pick_po_no']) && $filter['pick_po_no'] != '') {
            $shop_arr = $this->get_pick_no_shop($filter['pick_po_no']);
            $shop_str = $this->arr_to_in_sql_value($shop_arr, 'pick_shop', $sql_values);
            $sql_main .= " AND shop_code in ({$shop_str}) ";
        }
        //修改出库单,追加档期号,去掉已有档期
        if(!empty($filter['po_no_except'])){
            $po_no_except = explode(',', $filter['po_no_except']);
            $po_no_except_str = $this->arr_to_in_sql_value($po_no_except, 'po_no', $sql_values);
            $sql_main .= " AND po_no NOT IN({$po_no_except_str})";
        }
        $notice_record_num = isset($filter['notice_record_num']) ? $filter['notice_record_num'] : 2;
        if($notice_record_num == '2'){
            $sql_main .= " AND not_pick > 0 ";
        }elseif($notice_record_num == '1'){
            $sql_main .= " AND not_pick = 0 ";
        }

        $select = '*';
        $sql_main .= " order by sell_st_time desc ";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        filter_fk_name($data['data'], array('shop_code|shop',));
        $ret_data = $data;

        return $this->format_ret(1, $ret_data);
    }


    /**
     * 获取档期的未拣货数
     * @param $filter
     * @return array
     */
    function get_unpick_by_page($filter) {
        $sql_main = "FROM api_weipinhuijit_po_unpick AS r1 LEFT JOIN api_weipinhuijit_warehouse AS r2 ON r1.warehouse_code=r2.warehouse_code WHERE 1 AND r2.status=1 ";
        $sql_values = array();
        //开始时间
        if (isset($filter['po_no']) && $filter['po_no'] != '') {
            $sql_main .= " AND r1.po_no=:po_no";
            $sql_values[':po_no'] = $filter['po_no'];
        }
        $select = 'r1.warehouse_code,r2.warehouse_name,r1.warehouse_not_pick,r1.supply_num';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $ret_data = $data;
        return $this->format_ret(1, $ret_data);
    }



    /**
     * 获取档期的店铺
     * @param $pick_po_no
     */
    function get_pick_no_shop($pick_po_no) {
        $sql_values = array();
        $pick_no_arr = explode(',', $pick_po_no);
        $po_str = $this->arr_to_in_sql_value($pick_no_arr, 'po_no', $sql_values);
        $sql = "SELECT DISTINCT (shop_code) FROM api_weipinhuijit_po WHERE po_no IN({$po_str})";
        $ret = $this->db->get_all_col($sql, $sql_values);
        return $ret;
    }


    //根据id查询
    function get_by_id($id) {
        return $this->get_row(array('id' => $id));
    }

    /**
     * 通过field_name查询
     *
     * @param  $ :查询field_name
     * @param  $select ：查询返回字段
     * @return array (status, data, message)
     */
    public function get_by_field($field_name, $value, $select = "*") {

        $sql = "select {$select} from {$this->table} where {$field_name} = :{$field_name}";
        $data = $this->db->get_row($sql, array(":{$field_name}" => $value));

        if ($data) {
            return $this->format_ret('1', $data);
        } else {
            return $this->format_ret('-1', '', 'get_data_fail');
        }
    }

    function relatiion_notice($po_id, $notice_record_id) {
        $notice_recode = load_model('wbm/NoticeRecordModel')->get_by_id($notice_record_id);
        $sql = "update api_weipinhuijit_po set notice_id=$notice_record_id,notice_record_no='{$notice_recode['data']['record_code']}' where id=$po_id";
        $this->db->query($sql);
        //更新通知单关联状态
        $sql = "update wbm_notice_record set is_relation=1 where notice_record_id='{$notice_record_id}'";
        $this->db->query($sql);
        return $this->format_ret(1);
    }

    /**绑定库存锁定dam
     * @param $po_id
     * @param $notice_record_id
     * @return array
     */
    function relation_lock($po_id, $lock_record_id) {
        $lock_recode = load_model('stm/StockLockRecordModel')->get_by_id($lock_record_id);
        $po = $this->get_row(array('id' => $po_id));
        $this->begin_trans();
        $sql = "update api_weipinhuijit_po set relation_type=1,notice_id=$lock_record_id,notice_record_no='{$lock_recode['data']['record_code']}' where id=$po_id";
        $ret = $this->db->query($sql);
        if (!$ret) {
            $this->rollback();
            return $this->format_ret('-1', '', '绑定失败！');
        }
        //添加绑定日志
        $log_meg = "档期：{$po['data']['po_no']}，绑定锁定单";
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => $lock_recode['data']['status'], 'action_name' => '档期绑定', 'module' => "stock_lock_record", 'pid' => $lock_recode['data']['stock_lock_record_id'], 'action_note' => $log_meg);
        $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        //更新锁定单关联状态
        $data = array(
            'record_code' => $lock_recode['data']['record_code'],
            'relation_code' => $po['data']['po_no'],
            'relation_type' => 'po_no',
        );
        $ret = $this->insert_exp('stm_stock_lock_relation_record', $data);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $this->format_ret('-1', '', '绑定失败！');
        }
        $this->commit();
        return $this->format_ret(1);
    }

    function unrelation_notice($po_id) {
        $po_row = $this->get_by_id($po_id);
        $sql = "update api_weipinhuijit_po set notice_record_no='',notice_id=0 where id=$po_id";
        $this->db->query($sql);
        //更新通知单关联状态
        $sql = "update wbm_notice_record set is_relation=0 where notice_record_id='{$po_row['data']['notice_id']}'";
        $this->db->query($sql);
        return $this->format_ret(1);
    }

    /**解绑锁定单
     * @param $po_id
     * @return array
     */
    function unrelation_lock($po_id) {
        $po_row = $this->get_by_id($po_id);
        $this->begin_trans();
        $sql = "update api_weipinhuijit_po set notice_record_no='',notice_id=0 where id=$po_id";
        $ret = $this->db->query($sql);
        if (!$ret) {
            $this->rollback();
            return $this->format_ret('-1', '', '解绑失败！');
        }
        //更新锁定单关联状态
        $where = array(
            'record_code' => $po_row['data']['notice_record_no'],
            'relation_code' => $po_row['data']['po_no'],
            'relation_type' => 'po_no'
        );
        $ret = $this->delete_exp('stm_stock_lock_relation_record', $where);
        if (!$ret) {
            $this->rollback();
            return $this->format_ret('-1', '', '解绑失败！');
        }
        //添加解绑日志
        $lock_recode = load_model('stm/StockLockRecordModel')->get_by_id($po_row['data']['notice_id']);
        $log_meg = "档期：{$po_row['data']['po_no']}，解除绑定";
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '', 'finish_status' => $lock_recode['data']['status'], 'action_name' => '档期解绑', 'module' => "stock_lock_record", 'pid' => $lock_recode['data']['stock_lock_record_id'], 'action_note' => $log_meg);
        $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        $this->commit();
        return $this->format_ret(1);
    }

    //创建拣货单
    function create_pick($po_id, $warehouse_code='') {
        $po_row = $this->get_by_id($po_id);
        $params = array('shop_code' => $po_row['data']['shop_code'], 'po_no' => $po_row['data']['po_no'], 'warehouse_code' => $warehouse_code);
        $result = load_model('sys/EfastApiModel')->request_api('weipinhuijit_api/get_pick', $params);
        if ($result['resp_data']['code'] == '0') {
            $ret['status'] = '1';
            $ret['message'] = '成功';
        } else {
            $ret['status'] = '-1';
            $ret['message'] = '失败,' . $result['resp_data']['msg'];
        }
        return $ret;
    }

    //获取档期
    function get_po() {
        $ret['status'] = '1';
        $ret['message'] = '成功';
        $shop_arr = load_model('base/ShopModel')->get_wepinhuijit_shop();
        $error_arr = array();

        foreach ($shop_arr as $shop_row) {
            //传入时间则返回时间段内的所有档期
            //$params = array('shop_code' => $shop_row['shop_code'], 'start_time' => date('Y-m-d H:i:s', strtotime('-1 years')), 'end_time' => date('Y-m-d H:i:s'));
            //不传入时间，仅返回供应商需要拣货的PO
            $params = array('shop_code' => $shop_row['shop_code'], 'start_time' => '', 'end_time' => '');
            $result = load_model('sys/EfastApiModel')->request_api('weipinhuijit_api/po_sync', $params);
            if ($result['resp_data']['code'] == '0') {
                continue;
            } else {
                //$ret['status'] = '-1';
                $error_arr[] = '店铺' . $shop_row['shop_name'] . '获取档期失败,' . $result['resp_data']['msg'];
                //return $ret;
            }
        }
        if (!empty($error_arr)) {
            $ret['status'] = '-1';
            $ret['message'] = implode("<br/>", $error_arr);
        }
        return $ret;
    }

    /**
     * 更新档期未拣货数
     * @param array/string $id 档期表ID
     * @return array 更新结果
     */
    function update_po($id) {
        if (empty($id)) {
            return $this->format_ret(-1, '', '未选择档期');
        }
        if (!is_array($id)) {
            $id = array($id);
        }
        $sql_values = array();
        $id_str = $this->arr_to_in_sql_value($id, 'id', $sql_values);
        $sql = "SELECT po_no,shop_code FROM {$this->table} WHERE id IN({$id_str})";
        $po_data = $this->db->get_all($sql, $sql_values);
        if (empty($po_data)) {
            return $this->format_ret(-1, '', '档期数据不存在');
        }
        $status = 1;
        $msg = array();
        foreach ($po_data as $val) {
            //不传入时间，仅返回供应商需要拣货的PO
            $params = array('shop_code' => $val['shop_code'], 'po_no' => $val['po_no']);
            $result = load_model('sys/EfastApiModel')->request_api('weipinhuijit_api/update_po', $params);
            if ($result['resp_data']['code'] == '0') {
                continue;
            } else {
                $status = -1;
                $msg[] = "档期{$val['po_no']}更新失败," . $result['resp_data']['msg'];
            }
        }
        $msg = implode('<br>', $msg);
        return $this->format_ret($status, '', $msg);
    }

    //获取档期下的所有拣货单
    function get_pick($po_id) {
        $po_row = $this->get_by_id($po_id);
        $params = array('shop_code' => $po_row['data']['shop_code'], 'po_no' => $po_row['data']['po_no']);
        $result = load_model('sys/EfastApiModel')->request_api('weipinhuijit_api/sync_pick', $params);
        if ($result['resp_data']['code'] == '0') {
            $ret['status'] = '1';
            $ret['message'] = '获取成功';
        } else {
            $ret['status'] = '-1';
            $ret['message'] = '获取失败,' . $result['resp_data']['msg'];
        }
        return $ret;
    }

    /**
     * 多PO创建拣货单(同时获取拣货单信息) 2.0
     * @param array $param 参数po_no
     */
    function multi_po_create_pick($param) {
        if (!isset($param['po_no']) || empty($param['po_no'])) {
            return $this->format_ret(-1, '', '未知错误，请刷新页面重试');
        }
        $po_no = $param['po_no'];
        $shop_code = '';
        $check = $this->check_shop($po_no, $shop_code);
        if ($check['status'] != 1) {
            return $check;
        }

        $params = array('shop_code' => $shop_code, 'po_no' => $po_no);
        $result = load_model('sys/EfastApiModel')->request_api('weipinhuijit_api/create_get_pick', $params);
        if ($result['resp_data']['code'] == '0') {
            $ret['status'] = '1';
            $ret['message'] = '成功';
        } else {
            $ret['status'] = '-1';
            $ret['message'] = '失败,' . $result['resp_data']['msg'];
        }
        return $ret;
    }

    /**
     * 批量获取拣货单信息-更新 2.0
     * @param array $param 参数(po_no:档期编码)
     */
    function batch_sync_pick($param) {
        if (!isset($param['po_no']) || empty($param['po_no'])) {
            return $this->format_ret(-1, '', '未知错误，请刷新页面重试');
        }
        $po_no = $param['po_no'];
        $shop_code = '';
        $check = $this->check_shop($po_no, $shop_code);
        if ($check['status'] != 1) {
            return $check;
        }

        $params = array('shop_code' => $shop_code, 'po_no' => $po_no);
        $result = load_model('sys/EfastApiModel')->request_api('weipinhuijit_api/sync_pick2', $params);
        if ($result['resp_data']['code'] == '0') {
            $ret['status'] = '1';
            $ret['message'] = '获取成功';
        } else {
            $ret['status'] = '-1';
            $ret['message'] = '获取失败,' . $result['resp_data']['msg'];
        }
        return $ret;
    }

    /**
     * 检查是否为同一店铺的档期
     * @param array/string $po_no 档期编码
     * @param string $shop_code 店铺代码
     */
    function check_shop(&$po_no, &$shop_code) {
        if (is_array($po_no)) {
            $po_no = implode(',', $po_no);
        }
        $po_no_str = deal_strs_with_quote($po_no);

        $sql = "SELECT shop_code FROM {$this->table} WHERE po_no IN({$po_no_str})";
        $shop_code_arr = $this->db->get_all($sql);
        $shop_code_arr = array_column($shop_code_arr, 'shop_code');
        $shop_code = $shop_code_arr[0];
        foreach ($shop_code_arr as $key => $val) {
            if ($key != 0 && $val != $shop_code) {
                return $this->format_ret(-1, '', '请选择相同店铺的档期进行操作');
            }
        }

        return $this->format_ret(1);
    }

}
