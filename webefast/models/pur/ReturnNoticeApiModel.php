<?php

require_model('api/JxcOptApiModel');

/**
 * 采购退货单相关接口
 * @author WMH
 */
class ReturnNoticeApiModel extends JxcOptApiModel {

    function __construct() {
        parent::__construct('pur_return_notice_record');
        $this->record_type = 'pur_return_notice';
    }

    public function api_record_create($param) {
        return $this->format_ret(1, (object) array());
    }

    /**
     * API-查询采购退货通知单列表
     * @author wmh
     * @date 2017-06-14
     * @param array $param
     * @return array 操作结果
     */
    public function api_record_get($param) {
        //可选字段
        $key_option = array(
            's' => array('start_time', 'end_time', 'store_code', 'supplier_code'),
            'i' => array('page', 'page_size', 'is_check', 'is_finish')
        );
        $arr_option = array();
        //提取可选字段中已赋值数据
        $ret_option = valid_assign_array($param, $key_option, $arr_option);
        unset($param);
        //检查单页数据条数是否超限
        if (isset($arr_option['page_size']) && $arr_option['page_size'] > 100) {
            return $this->format_ret('-1', array('page_size' => $arr_option['page_size']), 'API_RETURN_MESSAGE_PAGE_SIZE_TOO_LARGE');
        }
        if (!isset($arr_option['is_check']) || !in_array($arr_option['is_check'], array(0, 1))) {
            $arr_option['is_check'] = '';
        }
        if (!isset($arr_option['is_finish']) || !in_array($arr_option['is_finish'], array(0, 1))) {
            $arr_option['is_finish'] = '';
        }
        $arr_option['is_sure'] = $arr_option['is_check'];
        unset($arr_option['is_check']);

        $select = ' `record_code`, `order_time`, `store_code`, `supplier_code`, `record_type_code`, `is_sure`,`is_finish`,`num`,`finish_num`';
        $sql_main = " FROM {$this->table} pr WHERE 1";
        $sql_values = array();
        //生成sql条件语句
        $this->get_record_sql_where($arr_option, $sql_main, $sql_values, 'pr.');
        //获取主单据信息
        $ret = $this->get_page_from_sql($arr_option, $sql_main, $sql_values, $select);

        $data = $ret['data'];
        if (empty($data)) {
            return $this->format_ret(-10002, (object) array(), 'API_RETURN_MESSAGE_10002');
        }
        filter_fk_name($data, array('store_code|store', 'supplier_code|supplier', 'record_type_code|record_type'));

        $new_data = array();
        foreach ($data as &$row) {
            $new_data[] = array(
                'record_code' => $row['record_code'],
                'record_time' => $row['order_time'],
                'is_check' => $row['is_sure'],
                'is_finish' => $row['is_finish'],
                'store_code' => $row['store_code'],
                'store_name' => $row['store_code_name'],
                'supplier_code' => $row['supplier_code'],
                'supplier_name' => $row['supplier_code_name'],
                'record_type' => $row['record_type_code_name'],
                'num' => $row['num'],
                'finish_num' => $row['finish_num'],
            );
        }

        $filter = get_array_vars($ret['filter'], array('page', 'page_size', 'page_count', 'record_count'));

        $revert_data = array(
            'filter' => $filter,
            'data' => $new_data,
        );
        return $this->format_ret(1, $revert_data);
    }

    /**
     * API-查询采购退货通知单明细
     * @author wmh
     * @date 2017-10-11
     * @param array $param
     * @return array 操作结果
     */
    public function api_detail_get($param) {
        $k_required = array('s' => array('record_code'),);
        $key_option = array('i' => array('page', 'page_size'));
        $arr_required = array();
        $arr_option = array();

        //提取可选字段中已赋值数据
        $ret_required = valid_assign_array($param, $k_required, $arr_required, TRUE);
        if ($ret_required['status'] !== TRUE) {
            return $this->format_ret(-10001, $ret_required['req_empty'], 'API_RETURN_MESSAGE_10001');
        }
        $record_code = $arr_required['record_code'];

        $ret_option = valid_assign_array($param, $key_option, $arr_option);
        $filter = $arr_option;
        unset($param, $arr_required, $arr_option);

        //检查单页数据条数是否超限
        if (isset($filter['page_size']) && $filter['page_size'] > 100) {
            return $this->format_ret('-1', array('page_size' => $filter['page_size']), 'API_RETURN_MESSAGE_PAGE_SIZE_TOO_LARGE');
        }

        $select = 'rd.`goods_code`, bg.`goods_name`, gs.`spec1_code`, gs.`spec1_name`, gs.`spec2_code`, gs.`spec2_name`, gs.`barcode`, rd.`price`, rd.`rebate`, rd.`money`, rd.`num`, rd.`finish_num`';
        $sql_join = 'INNER JOIN goods_sku AS gs ON rd.sku = gs.sku INNER JOIN base_goods AS bg ON rd.goods_code = bg.goods_code';
        $sql_main = " FROM {$this->detail_table_map[$this->record_type]} AS rd {$sql_join} WHERE rd.record_code=:record_code";
        $sql_values = array(':record_code' => $record_code);
        $ret = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $data = $ret['data'];
        if (empty($data)) {
            return $this->format_ret(-10002, (object) array(), 'API_RETURN_MESSAGE_10002');
        }
        foreach ($data as &$row) {
            $row['price'] = round($row['price'] * $row['rebate'], 3);
            unset($row['rebate']);
        }

        $filter = get_array_vars($ret['filter'], array('page', 'page_size', 'page_count', 'record_count'));

        $revert_data = array(
            'filter' => $filter,
            'data' => $data,
        );
        return $this->format_ret(1, $revert_data);
    }

    public function api_detail_update($param) {
        return $this->format_ret(1, (object) array());
    }

    public function api_record_accept($param) {
        return $this->format_ret(1, (object) array());
    }

    protected function set_opt_log($action_name, $action_note) {
        $log_data = array(
            'pid' => $this->record['return_notice_record_id'],
            'action_name' => $action_name,
            'action_note' => $action_note,
            'sure_status' => $this->record['is_sure'] == 1 ? '已确认' : '未确认',
            'finish_status' => $this->record['is_finish'] == 1 ? '已完成' : '未完成',
        );
        $this->set_log($log_data);
    }

    /**
     * 生成单据查询sql条件语句
     * @param array $filter 参数条件
     * @param string $sql_main sql主体
     * @param string $sql_values sql映射值
     * @param string $ab 表别名
     */
    private function get_record_sql_where($filter, &$sql_main, &$sql_values, $ab) {
        foreach ($filter as $key => $val) {
            if (in_array($key, array('page', 'page_size')) || $val === '') {
                continue;
            }
            if ($key == 'start_time') {
                $sql_main .= " AND {$ab}lastchanged>=:{$key}";
            } else if ($key == 'end_time') {
                $sql_main .= " AND {$ab}lastchanged<=:{$key}";
            } else {
                $sql_main .= " AND {$ab}{$key}=:{$key}";
            }
            $sql_values[":{$key}"] = $val;
        }

        if (!isset($filter['start_time'])) {
            $start_time = date("Y-m-d H:i:s", strtotime("today"));
            $sql_main .= " AND {$ab}lastchanged >= :start_time";
            $sql_values[':start_time'] = $start_time;
        }
        if (!isset($filter['end_time'])) {
            $end_time = date("Y-m-d H:i:s", strtotime("today +1 days"));
            $sql_main .= " AND {$ab}lastchanged <= :end_time";
            $sql_values[':end_time'] = $end_time;
        }
    }

}
