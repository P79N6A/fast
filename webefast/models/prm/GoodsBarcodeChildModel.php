<?php

/**
 * 商品子条码 业务
 *
 * @author huanghy
 */
require_lib('util/oms_util', true);
require_model('tb/TbModel');
require_lang('prm');

class GoodsBarcodeChildModel extends TbModel {

    function get_table() {
        return 'goods_barcode_child';
    }

    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        $sql_values = array();
        $sql_main = "FROM {$this->table} c
        LEFT JOIN goods_sku b ON  c.sku= b.sku
        WHERE 1";

        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_main .= " AND c.goods_code = :goods_code";
            $sql_values[':goods_code'] = $filter['goods_code'];
        }


        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sql_main .= " AND c.barcode LIKE :barcode";
//            $sql_main .= " AND c.sku LIKE :barcode";
            $sql_values[':barcode'] = $filter['barcode'] . '%';
        }
        if (isset($filter['p_barcode']) && $filter['p_barcode'] != '') {
            $sql_main .= " AND b.barcode LIKE :p_barcode";
            $sql_values[':p_barcode'] = $filter['p_barcode'] . '%';
        }

        $select = 'c.*,b.barcode as p_barcode';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $ret_status = OP_SUCCESS;

        foreach ($data['data'] as $key => &$value) {

            $value['spec1_name'] = oms_tb_val('base_spec1', 'spec1_name', array('spec1_code' => $value['spec1_code']));
            $value['spec2_name'] = oms_tb_val('base_spec2', 'spec2_name', array('spec2_code' => $value['spec2_code']));
            $value['goods_name'] = oms_tb_val('base_goods', 'goods_name', array('goods_code' => $value['goods_code']));
        }

        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        $arr = $this->get_row(array('barcode_id' => $id));
        return $arr;
    }
    //获取子条码信息
    function get_child_barcode_info ($filter, $where = 'sku', $select = '*') {
        $sql = "SELECT {$select} FROM {$this->table} WHERE {$where} = :{$where} ";
        $ret = $this->db->get_all($sql, array(':' . $where => $filter));
        return $ret;
    }

    /**
     * 删除纪录
     */
    function delete($id) {
        $ret_sku = $this->get_row(array('barcode_id' => $id));
        if (isset($ret_sku['data']['sku'])) {
            $data['lastchanged'] = date('Y-m-d H:i:s');
            $where = " sku = '{$ret_sku['data']['sku']}'";
            $this->db->update('goods_sku', $data, $where);
            $this->db->update('goods_barcode', $data, $where);
        }
        $ret = parent::delete(array('barcode_id' => $id));

        return $ret;
    }

    /**
     * 新增
     * @param $skuCode
     * @param $storeCode
     * @param $shelfCode
     * @param string $batchNumber
     * @return array
     */
    function add($file) {

        //读文件***********************
        $start_line = 0;
        $file = fopen($file, "r");
        $i = 0;
        $header = array();
        $file_str = '';
        $data_arr = array();
        $result_data = array();
        while (!feof($file)) {
            if ($i >= $start_line) {
                $row = fgetcsv($file);
                if (!empty($row)) {
                    $data_arr[] = $row;
                    $i++;
                }
            } else {
                $header[] = fgetcsv($file);
            }
            $i++;
        }
        array_shift($data_arr);

//        array_pop($data_arr);
        fclose($file);

        $faild = array();
        $sku_arr = array();
        $all_check_arr = array(
            'goods_sku' => array(),
            'goods_barcode' => array(),
            'exist1' => array(),
            'exist2' => array(),
            'exist3' => array(),
        );
        $barcode_3 = array();
        $barcode_arr = array();
        $barcode_child = array();
        foreach ($data_arr as &$value) {
            $value[0] = trim($value[0]);
            $value[1] = trim($value[1]);
            $barcode_3[] = $value[1];
            $barcode_arr[$value[0]] = $value[0];
            $barcode_child[$value[1]] = $value[1];
        }
        $barcode_repeat = array_count_values($barcode_3);

        //获取条码、子条码信息
        $barcode_arr = array_chunk($barcode_arr, 2000, TRUE);
        $barcode_child = array_chunk($barcode_child, 2000, TRUE);
        foreach ($barcode_arr as $val) {
            $str = "'" . implode("','", $val) . "'";
            $sql = "select * from goods_sku where barcode in ({$str})";
            $arr = $this->db->get_all($sql);
            $all_check_arr['goods_barcode'] = array_merge($all_check_arr['goods_barcode'], $arr);

            $sql = "select * from goods_sku where sku in ({$str})";
            $arr = $this->db->get_all($sql);
            $all_check_arr['goods_sku'] = array_merge($all_check_arr['goods_sku'], $arr);
        }
        foreach ($barcode_child as $val) {
            $str = "'" . implode("','", $val) . "'";
            $sql = "SELECT * FROM goods_barcode_child WHERE barcode in ({$str})";
            $arr = $this->db->get_all($sql);
            $all_check_arr['exist1'] = array_merge($all_check_arr['exist1'], $arr);

            $sql = "SELECT * FROM goods_barcode WHERE barcode in ({$str})";
            $arr = $this->db->get_all($sql);
            $all_check_arr['exist2'] = array_merge($all_check_arr['exist2'], $arr);

            $sql = "SELECT * FROM goods_combo_barcode WHERE barcode in ({$str})";
            $arr = $this->db->get_all($sql);
            $all_check_arr['exist3'] = array_merge($all_check_arr['exist3'], $arr);
        }
        $this->manipulation_data($all_check_arr);

        $success_num = 0;
        $fail_num = 0;
        foreach ($data_arr as $v) {
            $error = 0;
            $error_info = '';
            if (empty($all_check_arr['goods_barcode'][$v[0]]) && empty($all_check_arr['goods_sku'][$v[0]])) {
                $error_info .= "商品条形码不存在:" . $v[0] . '.';
                $error++;
            }
            $error_msg = '';
            if (isset($all_check_arr['exist1'][$v[1]]) && !empty($all_check_arr['exist1'])) {
                $error_msg .= "商品子条码{$v[1]}系统中已存在.";
            }
            if (isset($all_check_arr['exist2'][$v[1]]) && !empty($all_check_arr['exist2'])) {
                $error_msg .= "商品子条码{$v[1]}与系统中条码重复.";
            }
            if (isset($all_check_arr['exist3'][$v[1]]) && !empty($all_check_arr['exist3'])) {
                $error_msg .= "商品子条码{$v[1]}与系统套餐中条码重复.";
            }
            if ($barcode_repeat[$v[1]] > 1) {
                $error_msg .= "导入商品子条码{$v[1]}存在多个.";
            }
            if (!empty($error_msg)) {
                $error_info .= $error_msg;
                $error++;
            }
            if ($error != 0) {
                $faild[$v[0] . '--' . $v[1]] = $error_info;
                $fail_num++;
            } else {
                if (empty($all_check_arr['goods_barcode'][$v[0]]))
                    $sku = $all_check_arr['goods_sku'][$v[0]];
                else
                    $sku = $all_check_arr['goods_barcode'][$v[0]];
                $sku_arr[] = $sku['sku'];

                $d = array(
                    'goods_code' => $sku['goods_code'],
                    'spec1_code' => $sku['spec1_code'],
                    'spec2_code' => $sku['spec2_code'],
                    'sku' => $sku['sku'],
                    'barcode' => $v[1],
                    'add_time' => date("Y-m-d H:i:s", time()),
                    'lastchanged' => date("Y-m-d H:i:s", time()),
                );
                array_push($result_data, $d);
                $success_num++;
            }
        }
        $result_data_arr = array_chunk($result_data, 2000);
        foreach ($result_data_arr as $v) {
            $ret = $this->insert($v);
        }
        $data['lastchanged'] = date('Y-m-d H:i:s');
        $where = " sku in('" . implode("','", $sku_arr) . "')";
        $this->db->update('goods_sku', $data, $where);
        $this->db->update('goods_barcode', $data, $where);
        $msg = '导入成功' . $success_num . '条';
        if (!empty($faild)) {
            $msg .='，导入失败:' . $fail_num . '条';
            $fail_top = array('条形码和子条码', '错误信息');
            $filename = 'goods_barcode_child_import';
            $file_name = $this->create_import_fail_files($faild, $fail_top, $filename);
//            $msg .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
            $url = set_download_csv_url($file_name,array('export_name'=>'error'));
            $msg .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
            return $this->format_ret(-1, '', $msg);
        } else {
            return $this->format_ret(1, '', '导入成功');
        }
    }

    //处理数据
    function manipulation_data(&$all_check_arr) {
        foreach ($all_check_arr as $key => $val) {
            $a = array();
            if (!empty($val)) {
                foreach ($val as $v) {
                    $a[$v['barcode']] = $v;
                }
                $all_check_arr[$key] = $a;
            }
        }
    }

    function create_import_fail_files($msg, $fail_top, $filename) {
        $file_str = implode(",", $fail_top) . "\n";
        foreach ($msg as $key => $val) {
            $val_data = array($key, $val);
            $file_str .= implode(",", $val_data) . "\r\n";
        }
        $filename = md5($filename . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        return $filename;
    }

    /**
     * 批量添加纪录
     */
    function insert($data) {
        $ret = $this->insert_multi($data, true);
        return $ret;
    }

    public function get_return_barcode_child(&$barcode_data, $barcode_child) {
        foreach ($barcode_child as $key => $val) {
            $val['barcode'] = strtolower($val['barcode']);
            $sql = "select barcode from goods_sku where sku = :sku";
            $goods_barcode = $this->db->get_row($sql, array(":sku" => $val['sku']));
            if (isset($barcode_data[$goods_barcode['barcode']])) {
                $barcode_data[$goods_barcode['barcode']]['num'] +=$barcode_data[$val['barcode']]['num'];
            } else {
                if (isset($barcode_data[$val['barcode']])) {
                    //$barcode_data[$goods_barcode['barcode']]['refund_id'] = $barcode_data[$val['barcode']]['refund_id'];
                    $barcode_data[$goods_barcode['barcode']]['num'] = $barcode_data[$val['barcode']]['num'];
                    $barcode_data[$goods_barcode['barcode']]['tid'] = $barcode_data[$val['barcode']]['tid'];
                    $barcode_data[$goods_barcode['barcode']]['goods_barcode'] = $goods_barcode['barcode'];
                }
            }
            unset($barcode_data[$val['barcode']]);
        }
    }

    /**
     * 已发货分销订单新增
     * @author wmh
     * @date 2017-04-17
     * @param array $params
     * <pre> 
     * @return array 操作结果
     */
    public function api_goods_barcode_child_add($params) {
        $key_required = array(
            's' => array('barcode', 'child_list'),
        );
        $d_required = array();
        $ret_required = valid_assign_array($params, $key_required, $d_required, TRUE);
        if ($ret_required['status'] !== TRUE) {
            return $this->format_ret(-10001, $ret_required['req_empty'], 'API_PRM_MESSAGE_10001');
        }
        $sql = 'SELECT goods_code,spec1_code,spec2_code,sku FROM goods_barcode WHERE barcode=:barcode';
        $barcode_data = $this->db->get_row($sql, array(':barcode' => $d_required['barcode']));
        if (empty($barcode_data)) {
            return $this->format_ret(-10002, array('barcode' => $d_required['barcode']), '条码在系统中不存在');
        }

        $child_list = json_decode($d_required['child_list'], TRUE);
        if (empty($child_list)) {
            return $this->format_ret(-10005, array('child_list'), '参数值解析错误');
        }
        $child_exists = load_model('prm/SkuModel')->convert_barcode($child_list);
        $child_exists = $child_exists['data'];
        if (!empty($child_exists)) {
            $child_exists = array_keys($child_exists);
            $ret_exists = $this->format_ret(-10003, $child_exists, '子条码在系统中存在相同的子条码或条码或国标码');
        }
        $up_child = array();
        foreach ($child_list as $child) {
            if (in_array($child, $child_exists)) {
                continue;
            }
            $barcode_data['barcode'] = $child;
            $up_child[] = $barcode_data;
        }
        if(empty($up_child)){
            return $ret_exists;
        }

        $ret = $this->insert_multi($up_child, TRUE);
        if ($ret['status'] < 1) {
            return $this->format_ret(-1, '', '添加失败');
        }
        if (!empty($child_exists)) {
            return $ret_exists;
        }
        return $this->format_ret(1, '', '添加成功');
    }

}
