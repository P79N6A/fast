<?php

require_model('tb/TbModel');

class YohoDeliveryModel extends TbModel {

    protected $table = "api_youhuo_deliver";
    protected $detail_table = "api_youhuo_deliver_detail";

    /**
     * 创建出库单
     * @param $out_params
     * @return array
     */
    function create_delivery($out_params) {
        $notice_record_code = $out_params['notice_record_code'];
        $sql = "select b.shop_code from api_youhuo_store_out_record b where b.notice_record_code=:notice_record_code AND purchase_no=:purchase_no";
        $sql_value = array();
        $sql_value[':notice_record_code'] = $notice_record_code;
        $sql_value[':purchase_no'] = $out_params['purchase_no'];
        $out_ret = $this->db->get_row($sql, $sql_value);
        for ($i = 1; ; $i++) {
            $sql = "SELECT 1 FROM api_youhuo_deliver WHERE delivery_no=:delivery_no";
            $delivery_no = $out_params['purchase_no'] . '-' . $i;
            $sql_value = array();
            $sql_value[':delivery_no'] = $delivery_no;
            $result = $this->db->get_row($sql, $sql_value);
            if (empty($result)) {
                break;
            }
        }
        $purchase = load_model('api/YohoPurchaseModel')->get_row(array('purchase_no' => $out_params['purchase_no']));
        $purchase_info = $purchase['data'];
        $delivery_row['delivery_no'] = $delivery_no; //出库单号
        $delivery_row['purchase_no'] = $out_params['purchase_no']; //采购单号
        $delivery_row['notice_record_code'] = $out_params['notice_record_code']; //通知单号
        $delivery_row['shop_code'] = $out_ret['shop_code']; //店铺
        $delivery_row['numbers'] = 0; //数量
        $delivery_row['express_no'] = $out_params['express_no']; //快递单号
        $delivery_row['express_code'] = $out_params['express_code']; //快递公司
        $delivery_row['insert_time'] = date('Y-m-d H:i:s');
        $delivery_row['brand_id'] = $purchase_info['brand_id'];
        $delivery_ret = $this->insert($delivery_row);
        if ($delivery_ret['status'] != 1) {
            return $this->format_ret('-1', '', '创建出库单失败！');
        }
        //更新关系表
        $ret = $this->update_relation($delivery_no, $out_params['notice_record_code']);
        return $ret;
    }


    //更新关系表
    function update_relation($delivery_no, $notice_record_code) {
        $delivery_ret = $this->get_by_field('delivery_no', $delivery_no);
        $delivery_row = $delivery_ret['data'];
        $sql = "update api_youhuo_store_out_record a set a.delivery_no='{$delivery_no}' where a.notice_record_code='{$notice_record_code}' AND a.purchase_no='{$delivery_row['purchase_no']}';";
        $ret = $this->db->query($sql);
        if ($ret != true) {
            return $this->format_ret(-1, '', '更新关系表失败！');
        }
        return $this->format_ret(1, '', '成功！');
    }

    /**
     * 通过field_name查询
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

    /**
     * 列表查询
     * @param $filter
     * @return array
     */
    function get_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql_join = '';
        //导出
        if ($filter['ctl_type'] == 'export') {
            $sql_join = " LEFT JOIN {$this->detail_table} AS r2 ON r1.delivery_no=r2.delivery_no";
        }
        $sql_main = "FROM {$this->table} AS r1 {$sql_join} WHERE 1";
        $sql_values = array();
        //店铺
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('r1.shop_code', $filter_shop_code);
        //出库状态
        if (isset($filter['is_delivery']) && $filter['is_delivery'] != '') {
            $sql_main .= " AND r1.is_delivery =:is_delivery ";
            $sql_values[':is_delivery'] = $filter['is_delivery'];
        }
        //出库单号
        if (isset($filter['delivery_no']) && $filter['delivery_no'] != '') {
            $sql_main .= " AND r1.delivery_no = :delivery_no ";
            $sql_values[':delivery_no'] = $filter['delivery_no'];
        }
        //采购单号
        if (isset($filter['purchase_no']) && $filter['purchase_no'] != '') {
            $sql_main .= " AND r1.purchase_no = :purchase_no ";
            $sql_values[':purchase_no'] = $filter['purchase_no'];
        }
        //快递单号
        if (isset($filter['express_no']) && $filter['express_no'] != '') {
            $sql_main .= " AND r1.express_no = :express_no ";
            $sql_values[':express_no'] = $filter['express_no'];
        }
        //店铺
        if (isset($filter['shop_code']) && $filter['shop_code'] <> '') {
            $arr = explode(',', $filter['shop_code']);
            $str = $this->arr_to_in_sql_value($arr, 'shop_code', $sql_values);
            $sql_main .= " AND r1.shop_code in ({$str}) ";
        }
        if (isset($filter['start_time']) && $filter['start_time'] !== '') {
            switch ($filter['time_type']) {
                case 'insert_time':
                    $sql_main .= " AND r1.insert_time >=:start_time ";
                    $sql_values[':start_time'] = $filter['start_time'];
                    break;
                case 'delivery_time':
                    $sql_main .= " AND r1.delivery_time >=:start_time ";
                    $sql_values[':start_time'] = $filter['start_time'];
                    break;
            }
        }
        if (isset($filter['end_time']) && $filter['end_time'] !== '') {
            switch ($filter['time_type']) {
                case 'insert_time':
                    $sql_main .= " AND r1.insert_time <=:end_time ";
                    $sql_values[':end_time'] = $filter['end_time'];
                    break;
                case 'delivery_time':
                    $sql_main .= " AND r1.delivery_time <=:end_time ";
                    $sql_values[':end_time'] = $filter['end_time'];
                    break;
            }
        }
        //导出
        if ($filter['ctl_type'] == 'export') {
            $select = 'r1.*,r2.sku,r2.factory_code,r2.numbers AS detail_num';
            return $this->get_export_data($filter, $sql_main, $sql_values, $select);
        } else {
            $select = 'r1.*';
        }
        $sql_main .= " order by r1.insert_time desc ";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$row) {
            $row['express_name'] = oms_tb_val('base_express', 'express_name', array("express_code" => $row['express_code']));
        }
        $match = array('shop_code|shop',);
        filter_fk_name($data['data'], $match);
        $ret_data = $data;
        return $this->format_ret(1, $ret_data);
    }

    /**
     * 导出
     * @param $filter
     * @param $sql_main
     * @param $sql_values
     * @param $select
     * @return array
     */
    function get_export_data($filter, $sql_main, $sql_values, $select) {
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$row) {
            $row['express_name'] = oms_tb_val('base_express', 'express_name', array("express_code" => $row['express_code']));
            $row['delivery_status'] = ($row['is_delivery'] == 0) ? '未回写' : '已回写';
        }
        $match = array('shop_code|shop',);
        filter_fk_name($data['data'], $match);
        $ret_data = $data;
        return $this->format_ret(1, $ret_data);
    }

    function get_goods_by_page($filter) {
        $sql_main = "FROM {$this->detail_table}  WHERE 1";
        $sql_values = array();
        //出库单号
        if (isset($filter['delivery_no']) && $filter['delivery_no'] != '') {
            $sql_main .= " AND delivery_no = :delivery_no ";
            $sql_values[':delivery_no'] = $filter['delivery_no'];
        }
        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        return $this->format_ret(1, $data);
    }

    /**
     * @param $delivery_no
     */
    function confirm($delivery_no_str) {
        $delivery_no_arr = explode(',', $delivery_no_str);
        $error=array();
        foreach ($delivery_no_arr as $delivery_no) {
            $ret = $this->confirm_action($delivery_no);
            if ($ret['status'] != 1) {
                $error[] = array($delivery_no => $ret['message']);
            }
        }
        if (!empty($error)) {
            $sum = count($delivery_no_arr);
            $err_num = count($error);
            $success_num = $sum - $err_num;
            $msg = "回写成功：{$success_num},回写失败：{$err_num}";
            $msg .= $this->create_fail_file($error);
            return $this->format_ret(-1, '', $msg);
        }
        return $this->format_ret(1, '', '回写成功！');
    }


    function confirm_action($delivery_no) {
        $delivery = $this->get_row(array('delivery_no' => $delivery_no));
        if ($delivery['status'] != 1) {
            return $this->format_ret('-1', '', '无单据信息');
        }
        $delivery_main = $delivery['data'];
        if ($delivery_main['is_delivery'] == 1) {
            return $this->format_ret('-1', '', '单据已回写！');
        }
        $sql = "SELECT * FROM {$this->detail_table} WHERE delivery_no=:delivery_no";
        $sql_value[':delivery_no'] = $delivery_no;
        $delivery_detail = $this->db->get_all($sql, $sql_value);
        if (empty($delivery_detail)) {
            return $this->format_ret('-1', '', '无出库明细！');
        }
        if (empty($delivery_main['express_code'])) {
            return $this->format_ret('-1', '', '无配送方式！');
        }
        if (empty($delivery_main['express_no'])) {
            return $this->format_ret('-1', '', '无快递单号！');
        }
        $params = array();
        $params['shop_code'] = $delivery_main['shop_code'];
        $params['purchase_no'] = $delivery_main['purchase_no'];
        $params['express_code'] = $delivery_main['express_code'];
        $params['brand_id'] = $delivery_main['brand_id'];
        $details = array();
        foreach ($delivery_detail as $detail) {
            $details[] = array(
                'express_no' => $delivery_main['express_no'],
                'factory_code' => $detail['factory_code'],
                'numbers' => $detail['numbers'],
            );
        }
        $params['details'] = json_encode($details);
        //调用接口
        $result = load_model('sys/EfastApiModel')->request_api('yoho_api/syn_express', $params);
        if ($result['resp_data']['code'] == '0') {
            //更新状态
            $this->update(array('is_delivery' => 1, 'delivery_time' => date('Y-m-d H:i:s')), array('delivery_no' => $delivery_no));
            $ret = $this->format_ret('1', '', '回写成功！');
        } else {
            $ret = $this->format_ret('-1', '', '回写失败！' . $result['resp_data']['msg']);
        }
        //日志
        $delivery_log = "(时间：" . date('Y-m-d H:i:s') . "，结果：{$ret['message']})";
        $this->save_delivery_log($delivery_no, $delivery_log);
        return $ret;

    }


    /**
     * 回写日志
     * @param $delivery_no
     * @param $delivery_log
     * @return array
     */
    function save_delivery_log($delivery_no, $delivery_log) {
        $ret = $this->update(array('delivery_log' => $delivery_log), array('delivery_no' => $delivery_no));
        return $ret;
    }


    function create_fail_file($error_msg) {
        $fail_top = array('出库单号', '错误信息');
        $file_name = load_model('wbm/StoreOutRecordModel')->create_import_fail_files($fail_top, $error_msg);
        $message = '';
//        $message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
        $url = set_download_csv_url($file_name,array('export_name'=>'error'));
        $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        return $message;
    }
}
