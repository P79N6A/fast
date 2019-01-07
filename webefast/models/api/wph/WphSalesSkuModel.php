<?php

require_model('tb/TbModel');

/**
 * 唯品会专场商品管理业务
 */
class WphSalesSkuModel extends TbModel {

    function __construct() {
        parent::__construct('api_wph_sales_sku');
    }

    public function get_by_page($filter) {
        if (isset($filter['keyword_goods_value']) && $filter['keyword_goods_value'] != '') {
            $filter[$filter['keyword_goods']] = trim($filter['keyword_goods_value']);
        }
        if (isset($filter['keyword_sales_value']) && $filter['keyword_sales_value'] != '') {
            $filter[$filter['keyword_sales']] = trim($filter['keyword_sales_value']);
        }

        $sql_join = ' INNER JOIN `api_wph_sales_sku_relation` AS sr ON sr.`barcode`=ss.`barcode`
                      INNER JOIN `api_wph_sales` AS ws ON ws.`sales_no`=sr.`sales_no` AND ws.`shop_code`=ss.`shop_code`';

        $sql_main = "FROM `{$this->table}` AS ss {$sql_join} WHERE 1";
        $sql_values = array();
        $select = 'ss.`id`,ss.`shop_code`,ss.`barcode`,ss.`sku`,ss.`goods_code`,ss.`product_name`,ss.`brand_id`,ss.`brand_name`,ss.`init_num`,ss.`pt_sale_num`,ss.`diff_num`,ss.`last_sync_num`,ss.`sales_st`,ss.`last_sync_time`,ss.`is_allow_sync`,ss.`sync_status`,ss.`fail_info`';
        //店铺代码
        if (isset($filter['shop_code']) && $filter['shop_code'] != '') {
            $sql_main .= ' AND ss.`shop_code`=:shop_code ';
            $sql_values[':shop_code'] = $filter['shop_code'];
        }
        //专场ID
        if (isset($filter['sales_no']) && $filter['sales_no'] != '') {
            $sql_main .= ' AND ws.`sales_no`=:sales_no ';
            $sql_values[':sales_no'] = $filter['sales_no'];
        }
        //专场名称
        if (isset($filter['name']) && $filter['name'] != '') {
            $sql_main .= ' AND ws.`name` LIKE :name ';
            $sql_values[':name'] = "%{$filter['name']}%";
        }
        //商品上线时间-起始
        if (isset($filter['sales_st_start']) && !empty($filter['sales_st_start'])) {
            $sql_main .= ' AND ss.`sales_st`>=:sales_st_start ';
            $sql_values[':sales_st_start'] = strtotime($filter['sales_st_start']);
        }
        //商品上线时间-结束
        if (isset($filter['sales_st_end']) && !empty($filter['sales_st_end'])) {
            $sql_main .= ' AND ss.`sales_st`<=:sales_st_end ';
            $sql_values[':sales_st_end'] = strtotime($filter['sales_st_end']);
        }
        //最后同步时间-起始
        if (isset($filter['last_sync_time_start']) && !empty($filter['last_sync_time_start'])) {
            $sql_main .= ' AND ss.`last_sync_time`>=:last_sync_time_start ';
            $sql_values[':last_sync_time_start'] = strtotime($filter['last_sync_time_start']);
        }
        //最后同步时间-结束
        if (isset($filter['last_sync_time_end']) && !empty($filter['last_sync_time_end'])) {
            $sql_main .= ' AND ss.`last_sync_time`<=:last_sync_time_end ';
            $sql_values[':last_sync_time_end'] = strtotime($filter['last_sync_time_end']);
        }
        //商品条形码
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sql_main .= ' AND ss.`barcode` LIKE :barcode ';
            $sql_values[':barcode'] = "%{$filter['barcode']}%";
        }
        //商品名称
        if (isset($filter['product_name']) && $filter['product_name'] != '') {
            $sql_main .= ' AND ss.`product_name` LIKE :product_name ';
            $sql_values[':product_name'] = "%{$filter['product_name']}%";
        }
        //商品品牌
        if (isset($filter['brand_name']) && $filter['brand_name'] != '') {
            $sql_main .= ' AND ss.`brand_name` LIKE :brand_name ';
            $sql_values[':brand_name'] = "%{$filter['brand_name']}%";
        }
        //同步状态
        if (isset($filter['sync_status']) && $filter['sync_status'] != '' && $filter['sync_status'] != 'all') {
            $sync_status = array('no_sync' => 0, 'synced' => 1, 'sync_fail' => 2);
            $sql_main .= ' AND ss.`sync_status`=:sync_status ';
            $sql_values[':sync_status'] = $sync_status[$filter['sync_status']];
            if ($filter['sync_status'] == 'no_sync') {
                $sql_main .= ' AND ss.`is_allow_sync`=1 ';
            }
        }
        //是否允许同步
        if (isset($filter['is_allow_sync']) && $filter['is_allow_sync'] != '') {
            $sql_main .= ' AND ss.`is_allow_sync`=:is_allow_sync ';
            $sql_values[':is_allow_sync'] = $filter['is_allow_sync'];
        }

        $sql_main .= ' GROUP BY ss.`shop_code`,ss.`barcode`';
        $sql_main .= ' ORDER BY ss.`sales_st` DESC';
        $ret = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, TRUE);
        $data = &$ret['data'];
        //获取barcode关联的专场ID
        $shop_barcode = array();
        foreach ($data as $val) {
            $shop_barcode[$val['shop_code']][] = $val['barcode'];
        }
        $sales_no_arr = $this->get_sales_by_shop_barcode($shop_barcode);

        foreach ($data as &$row) {
            $row['sale_no_txt'] = isset($sales_no_arr[$row['shop_code']][$row['barcode']]) ? $sales_no_arr[$row['shop_code']][$row['barcode']] : '';
            $row['sales_st'] = empty($row['sales_st']) ? '' : date('Y-m-d H:i:s', $row['sales_st']);
            $row['last_sync_time'] = empty($row['last_sync_time']) ? '' : date('Y-m-d H:i:s', $row['last_sync_time']);
        }
        filter_fk_name($data, array('shop_code|shop', 'sku|barcode'));
        $ret_data = $ret;

        return $this->format_ret(1, $ret_data);
    }

    /**
     * 获取barcode关联的专场ID
     * @param array $shop_barcode 店铺-条码
     * @return type
     */
    public function get_sales_by_shop_barcode($shop_barcode) {
        $data = array();
        foreach ($shop_barcode as $shop_code => $barcode_arr) {
            $sql_values = array(':shop_code' => $shop_code);
            $sql_str = $this->arr_to_in_sql_value($barcode_arr, 'barcode', $sql_values);
            $sql = "SELECT GROUP_CONCAT(sales_no) AS sales_no,barcode FROM api_wph_sales_sku_relation WHERE shop_code=:shop_code AND barcode IN({$sql_str}) GROUP BY barcode";
            $ret = $this->db->get_all($sql, $sql_values);
            $ret = array_column($ret, 'sales_no', 'barcode');
            $data[$shop_code] = $ret;
        }

        return $data;
    }

    /**
     * 保存专场SKU信息
     * @param string $shop_code 店铺代码
     * @param string $sales_no 专场ID
     * @param array $data 保存数据
     * @return array 保存结果
     */
    public function save_sales_sku_list($shop_code, $sales_no, $data) {
        if (empty($data)) {
            return $this->format_ret(-1, '', '数据为空');
        }
        $barcode_arr = array_unique(array_column($data, 'barcode'));
        $sql_values = array();
        $sql_str = $this->arr_to_in_sql_value($barcode_arr, 'barcode', $sql_values);
        $sql = "SELECT bg.goods_code,gs.sku,gs.barcode FROM base_goods AS bg INNER JOIN goods_sku AS gs ON bg.goods_code=gs.goods_code WHERE gs.barcode IN({$sql_str})";
        $goods = $this->db->get_all($sql, $sql_values);

        $goods_arr = array();
        foreach ($goods as $g) {
            $barcode = $g['barcode'];
            unset($g['barcode']);
            $goods_arr[$barcode] = $g;
        }
        unset($goods, $barcode);

        $relation_data = array();
        $sku_data = array();
        foreach ($data as $val) {
            $r = array();
            $r['shop_code'] = $shop_code;
            $r['sales_no'] = $sales_no;
            $r['barcode'] = $val['barcode'];
            $relation_data[] = $r;

            $barcode = $val['barcode'];
            if (isset($sku_data[$barcode])) {
                continue;
            }

            $s = $val;
            $s['shop_code'] = $shop_code;
            $s['insert_time'] = time();
            if (isset($goods_arr[$barcode])) {
                $s['sku'] = $goods_arr[$barcode]['sku'];
                $s['goods_code'] = $goods_arr[$barcode]['goods_code'];
            } else {
                $s['sku'] = '';
                $s['goods_code'] = '';
            }
            $sku_data[$barcode] = $s;
        }

        $ret = $this->insert_multi_exp('api_wph_sales_sku_relation', $relation_data, TRUE);

        $update_str = 'sku=VALUES(sku),goods_code=VALUES(goods_code),sales_st=0';
        $ret = $this->insert_multi_duplicate($this->table, $sku_data, $update_str);

        return $ret;
    }

    /**
     * 保存待售专场SKU信息
     * @param string $shop_code 店铺代码
     * @param array $data 保存数据
     * @return array 保存结果
     */
    public function save_upcoming_sales_sku_list($shop_code, $data) {
        if (empty($data)) {
            return $this->format_ret(-1, '', '数据为空');
        }

        foreach ($data as &$val) {
            $val['shop_code'] = $shop_code;
            $val['is_allow_sync'] = 1;
            $val['insert_time'] = time();
        }

        $update_str = 'sales_st=VALUES(sales_st),is_allow_sync=VALUES(is_allow_sync)';
        $ret = $this->insert_multi_duplicate($this->table, $data, $update_str);
        return $ret;
    }

    /**
     * 更新库存数据
     * @param string $shop_code 店铺代码
     * @param array $barcode_inv 条码库存数据
     * @param array $update_fld 需要更新的字段
     * @param int 更新库存字段类型
     */
    public function update_inv($shop_code, $barcode_inv, $update_fld) {
        $sql_values = array(':shop_code' => $shop_code);
        $sql = "UPDATE {$this->table} SET ";
        $sql_arr = array();
        $barcode_str = array();
        foreach ($update_fld as $fld) {
            $i = 1;
            $sql_temp = " {$fld} = CASE barcode ";
            foreach ($barcode_inv as $val) {
                $b_str = ":barcode_{$i}";
                $s_str = ":{$fld}_{$i}";
                $sql_temp .= " WHEN {$b_str} THEN {$s_str} ";
                $barcode_str[$val['barcode']] = $b_str;
                $sql_values[$b_str] = $val['barcode'];
                $sql_values[$s_str] = $val[$fld];
                $i++;
            }
            $sql_temp .= ' END';
            $sql_arr[] = $sql_temp;
        }
        $sql .= implode(',', $sql_arr);
        $barcode_str = implode(',', $barcode_str);
        $sql .= " WHERE shop_code=:shop_code AND barcode IN({$barcode_str})";
        $ret = $this->query($sql, $sql_values);
        return $ret;
    }

    /**
     * 全量更新库存
     * @param array $ids 待售商品id
     */
    public function full_inv_sync($ids) {
        $sql_values = array();
        $id_str = $this->arr_to_in_sql_value($ids, 'id', $sql_values);
        $sql = "SELECT shop_code,barcode FROM {$this->table} WHERE id IN({$id_str})";
        $sales_sku = $this->db->get_all($sql, $sql_values);
        $shop_code = $sales_sku[0]['shop_code'];

        $barcode_arr = array_column($sales_sku, 'barcode');
        //计算系统库存
        $inv_data = load_model('api/BaseInvModel')->wph_sales_inv_update($shop_code, $barcode_arr);
        if ($inv_data['status'] != 1) {
            return $inv_data;
        }
        //更新初始化库存
        $init_num_data = $this->convert_field($inv_data['data'], 'init_num', 'num');
        $ret = $this->update_inv($shop_code, $init_num_data, array('init_num'));
        if ($ret['status'] != 1) {
            return $this->format_ret(-1, '', '初始化库存失败');
        }

        //全量更新唯品会专场商品库存
        $inventories = $this->convert_field($inv_data['data'], 'quantity', 'num');
        $sync_result = load_model('api/wph/WphSalesApiModel')->update_sales_skus_inventory($shop_code, $inventories, 1);
//        $sync_result = array('success_list' => array(), 'failed_list' => array(array('barcode' => '0000002034', 'quantity' => 600, 'message' => '失败')));
        if (isset($sync_result['status']) && $sync_result['status'] != 1) {
            return $sync_result;
        }

        //处理全量同步回传结果数据
        $ret = $this->deal_sync_result($shop_code, $sync_result, $inv_data['data']);
        if ($ret['status'] != 1) {
            return $this->format_ret(-1, '', '处理回传数据失败');
        }

        return $this->format_ret(1, '', '同步完成');
    }

    private function deal_sync_result($shop_code, $return_data, $up_data) {
        $success_data = array();
        $fail_data = array();
        if (!empty($return_data['success_list'])) {
            $up_data = array_column($up_data, 'num', 'barcode');
            $success_data = $this->convert_inv_data($return_data['success_list'], 1, $up_data);
        }
        if (!empty($return_data['failed_list'])) {
            $fail_data = $this->convert_inv_data($return_data['failed_list'], 0);
        }
        $data = array_merge($success_data, $fail_data);

        $ret = $this->update_inv($shop_code, $data, array('pt_sale_num', 'last_sync_time', 'fail_info', 'is_allow_sync', 'sync_status', 'last_sync_num'));

        return $ret;
    }

    /**
     * 转换更新表库存数据
     * @param array $inv_data 库存数据
     * @param string $fld 更新字段
     * @param string $fld_val 字段值键
     * @return array 数据集
     */
    private function convert_field($inv_data, $fld, $fld_val) {
        $data = array();
        foreach ($inv_data as $val) {
            $data[] = array(
                'barcode' => $val['barcode'],
                $fld => $val[$fld_val]
            );
        }
        return $data;
    }

    /**
     * 接口回传数据转换
     * @param array $inv_data 库存数据
     * @param int $is_success 是否成功
     * @return array 数据集
     */
    private function convert_inv_data($return_data, $is_success, $up_data = array()) {
        $inventories = array();
        $last_sync_time = time();
        foreach ($return_data as $val) {
            $temp = array();
            $temp['barcode'] = $val['barcode'];
            $temp['pt_sale_num'] = $val['quantity'];
            $temp['last_sync_time'] = $last_sync_time;
            if ($is_success == 1) {
                $temp['last_sync_num'] = $up_data[$val['barcode']];
                $temp['fail_info'] = '';
                $temp['is_allow_sync'] = 0;
                $temp['sync_status'] = 1;
            } else {
                $temp['last_sync_num'] = 0;
                $temp['fail_info'] = $val['message'];
                $temp['is_allow_sync'] = 1;
                $temp['sync_status'] = 2;
            }
            $inventories[] = $temp;
        }
        return $inventories;
    }

    /**
     * 导入调整平台库存明细
     * @param string $shop_code 店铺代码
     * @param string $file 导入文件地址
     * @return array 导入结果
     */
    public function import_adjust_data($shop_code, $file) {
        if (empty($shop_code)) {
            return $this->format_ret(-1, '', '请先选择同步店铺');
        }

        $detail = array();
        $num = $this->read_csv($file, $detail);

        $sql_values = array(':shop_code' => $shop_code);
        $barcode_arr = array_unique(array_column($detail, 'barcode'));
        $barcode_str = $this->arr_to_in_sql_value($barcode_arr, 'barcode', $sql_values);
        $sql = "SELECT LOWER(barcode) FROM {$this->table} WHERE sales_st<>0 AND is_allow_sync=0 AND sync_status=1 AND shop_code=:shop_code AND barcode IN({$barcode_str})";
        $exists_barcode = $this->db->get_col($sql, $sql_values);

        $err_msg = array();
        $adjust_data = array();
        foreach ($detail as $val) {
            $barcode = strtolower($val['barcode']);
            $adjust_num = $val['num'];
            $error = array();
            if (!isset($exists_barcode[$barcode])) {
                $error[] = '条码在该专场店铺中不存在';
            }
            if (!(is_int($adjust_num) && $adjust_num > 0)) {
                $error[] = '预售数量必须为正整数';
            }
            if (!empty($error)) {
                $val['error'] = implode('；', $error);
                $err_msg[] = $val;
                continue;
            }
            $adjust_data[$val['barcode']] = array(
                'barcode' => $val['barcode'],
                'quantity' => $adjust_num,
            );
        }

        $status = 1;
        if (!empty($adjust_data)) {
            //同步调整平台库存
            $sync_result = load_model('api/wph/WphSalesApiModel')->update_sales_skus_inventory($shop_code, $adjust_data, 1);
            if (isset($sync_result['status']) && $sync_result['status'] != 1) {
                return $sync_result;
            }

            if (!empty($sync_result['success_list'])) {
                $up_data = array();
                $adjust_data = array_column($adjust_data, 'quantity', 'barcode');
                $last_sync_time = time();
                foreach ($sync_result['success_list'] as $val) {
                    $up_data[] = array(
                        'shop_code' => $shop_code,
                        'barcode' => $val['barcode'],
                        'diff_num' => $adjust_data[$val['barcode']],
                        'pt_sale_num' => $val['quantity'],
                        'last_sync_num' => $adjust_data[$val['barcode']],
                        'last_sync_time' => $last_sync_time
                    );
                }
                $ret = $this->insert_multi_duplicate($this->table, $up_data, 'diff_num=VALUES(diff_num),pt_sale_num=VALUES(pt_sale_num),last_sync_time=VALUES(last_sync_time)');
            }
            if (!empty($sync_result['failed_list'])) {
                $adjust_data = array_column($detail, 'num', 'barcode');
                foreach ($sync_result['failed_list'] as $val) {
                    $err_msg[] = array(
                        'barcode' => $val['barcode'],
                        'num' => $adjust_data[$val['barcode']],
                        'error' => $val['message']
                    );
                }
            }
        } else {
            $status = -1;
        }

        $err_num = count($err_msg);
        $success_num = count($up_data);

        $message = '导入成功：' . $success_num;
        if ($err_num > 0) {
            $message .= '；' . '失败数量：' . $err_num;
            $message .= $this->create_fail_file($err_msg);
        }

        return $this->format_ret($status, '', $message);
    }

    /**
     * 读取文件，保存到数组中
     */
    function read_csv($file, &$detail) {
        $file = fopen($file, "r");
        $i = 0;
        while (!feof($file)) {
            $row = fgetcsv($file);
            if ($i >= 1) {
                $this->tran_csv($row);
                if (!empty($row[0])) {
                    $d = array();
                    $d['barcode'] = $row[0];
                    $d['num'] = $row[1];
                    $detail[$i] = $d;
                }
            }
            $i++;
        }
        fclose($file);
        return $i;
    }

    /**
     * 创建导出信息csv文件
     * @param array $msg 信息数组
     * @return string 文件地址
     */
    private function create_fail_file($msg) {
        $fail_top = array('商品条形码', '调整数量');
        require_lib('csv_util');
        $csv_obj = new execl_csv();
        $file_name = $csv_obj->create_fail_csv_files($fail_top, $msg, 'presell_detail_error');
//        $message = "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
        $url = set_download_csv_url($file_name,array('export_name'=>'error'));
        $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        return $message;
    }

}
