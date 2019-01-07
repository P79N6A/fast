<?php

require_lib('util/oms_util', true);
require_model('tb/TbModel');

class OrderExpressDzDetailModel extends TbModel {

    protected $table = 'order_express_dz_detail';

    /**
     * @todo 运费数据详情查询
     */
    function get_detail_by_page($filter) {
        $sql_main = "FROM {$this->table} dd INNER JOIN oms_sell_record sr ON dd.sell_record_code = sr.sell_record_code WHERE 1";
        $sql_values = array();
        // 对账编号
        if (!empty($filter['dz_code'])) {
            $sql_main .= ' AND dd.dz_code=:dz_code';
            $sql_values[':dz_code'] = $filter['dz_code'];
        }
        // 快递单号或者订单号
        if (!empty($filter['code_name'])) {
            $sql_main .= ' AND (dd.sell_record_code=:code_name OR dd.express_no=:code_name)';
            $sql_values[':code_name'] = $filter['code_name'];
        }
        // 快递编号
        if (!empty($filter['express_code'])) {
            $sql_main .= " AND dd.express_code=:express_code";
            $sql_values[':express_code'] = $filter['express_code'];
        }
        // 账期
        if (!empty($filter['dz_code'])) {
            $sql_main .= " AND dd.dz_code=:dz_code";
            $sql_values[':dz_code'] = $filter['dz_code'];
        }
        //核销状态
        if (isset($filter['hx_status']) && $filter['hx_status'] !== '') {
            $sql_main .= " AND dd.hx_status=:hx_status";
            $sql_values[':hx_status'] = $filter['hx_status'];
        }
        //省份
        if (!empty($filter['receiver_province'])) {
            $sql_main .= " AND dd.receiver_province=:receiver_province";
            $sql_values[':receiver_province'] = $filter['receiver_province'];
        }

        $select = ' dd.detail_dz_id,dd.dz_code,dd.store_code,dd.sell_record_code,dd.express_code,dd.express_no,dd.real_weigh,dd.weigh_express_money,dd.express_money,dd.receiver_address,dd.hx_status,dd.hx_time,dd.delivery_time,dd.receiver_province, sr.receiver_city,sr.receiver_district,sr.receiver_street,sr.receiver_addr ';

        //导出
        if ($filter['type'] == 'export') {
            $sql = 'SELECT ' . $select . $sql_main;
            $data = $this->db->get_all($sql, $sql_values);
            $temp_data = &$data;
        } else {
            $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
            $temp_data = &$data['data'];
        }

        if (empty($temp_data)) {
            return $this->format_ret(1, $data);
        }
        $obj_archive = load_model('base/ArchiveSearchModel');
        $store_arr = array_unique(array_column($temp_data, 'store_code'));
        $store_arr = $obj_archive->get_archives_map('store',$store_arr);

        $express_arr = array_unique(array_column($temp_data, 'express_code'));
        $express_arr = $obj_archive->get_archives_map('express',$express_arr);

        $province_arr = array_unique(array_column($temp_data, 'receiver_province'));
        $city_arr = array_unique(array_column($temp_data, 'receiver_city'));
        $district_arr = array_unique(array_column($temp_data, 'receiver_district'));
        $street_arr = array_unique(array_column($temp_data, 'receiver_street'));
        $area_arr = array_merge($province_arr, $city_arr, $district_arr, $street_arr);
        $area_arr = $obj_archive->get_archives_map('area',$area_arr);
        unset($province_arr, $city_arr, $district_arr, $street_arr);

        foreach ($temp_data as &$row) {
            $row['hx_status_type'] = $row['hx_status'] == 1 ? "已核销" : "未核销";
            $row['store_name'] = $store_arr[$row['store_code']];
            $row['express_name'] = $express_arr[$row['express_code']];
            $row['province_name'] = $area_arr[$row['receiver_province']];
            $row['city_name'] = $area_arr[$row['receiver_city']];
            $row['district_name'] = $area_arr[$row['receiver_district']];
            $row['street_name'] = $area_arr[$row['receiver_street']];
        }

        return $this->format_ret(1, $data);
    }

    /**
     * @todo 快递运费汇总数据查询
     */
    function get_by_page($filter) {
        $sql_main = "FROM {$this->table} WHERE 1 ";
        $sql_values = array();
        // 对账编号
        if (isset($filter['dz_code']) && $filter['dz_code'] != '') {
            $sql_main .= " AND (dz_code=:dz_code)";
            $sql_values[':dz_code'] = $filter['dz_code'];
        }
        $sql_main .= " group by express_code ";
        $select = ' express_code,sum(weigh_express_money) weigh_express_money';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        if (empty($data)) {
            return $this->format_ret(1, $data);
        }

        $express_arr = array_column($data['data'], 'express_code');
        $express_arr = load_model('base/ArchiveSearchModel')->get_archives_map('express',$express_arr, 2, 'company_code');
        $company_arr = array_column($express_arr, 'company_code');
        $company_arr = load_model('base/ArchiveSearchModel')->get_archives_map('express_company',$company_arr);

        //总快递费用
        $sum_cost = array_sum(array_column($data['data'], 'weigh_express_money'));

        foreach ($data['data'] as &$row) {
            $row['express_name'] = $express_arr[$row['express_code']]['_name'];
            $row['company_name'] = $company_arr[$express_arr[$row['express_code']]['company_code']];
            $row['express_percent'] = sprintf("%.2f", $row['weigh_express_money'] / $sum_cost * 100) . "%";
        }

        return $this->format_ret(1, $data);
    }

    /**
     * @todo 新增快递对账单时生成明细数据
     */
    function create_detail($data) {
        $delivery_time_start = date("Y-m-d", strtotime($data['dz_month'])) . " 00:00:00";
        $delivery_time_end = date('Y-m-d', strtotime("$delivery_time_start +1 month -1 day")) . " 23:59:59";
        $sql = "SELECT express_code,weigh_express_money,real_weigh,store_code,sell_record_code,express_no,receiver_address,delivery_time,receiver_province FROM oms_sell_record_cz where store_code=:store_code AND delivery_time>=:delivery_time_start AND delivery_time<=:delivery_time_end";
        $sql_value = array(":store_code" => $data['store_code'], ":delivery_time_start" => $delivery_time_start, ":delivery_time_end" => $delivery_time_end);
        $res = $this->db->get_all($sql, $sql_value);
        foreach ($res as &$val) {
            $val['dz_code'] = $data['dz_code'];
            $val['hx_status'] = ($val['express_money'] == $val['weigh_express_money']) ? "1" : "0";
        }
        $update_str = "dz_code = VALUES(dz_code),express_code = VALUES(express_code),express_no = VALUES(express_no),store_code = VALUES(store_code),weigh_express_money = VALUES(weigh_express_money),real_weigh = VALUES(real_weigh),sell_record_code = VALUES(sell_record_code),receiver_address = VALUES(receiver_address),delivery_time = VALUES(delivery_time)";
        $ret = $this->insert_multi_duplicate($this->table, $res, $update_str); //批量插入数据，存在则更新
        //回写主单据
        load_model('acc/OrderExpressDzModel')->main_write_back($data['dz_code'], $data['store_code']);
        $date = date('Y年m月', strtotime($data['dz_month']));
        if ($ret['status'] != 1) {
            return $this->format_ret(-1, '', "{$date}该仓库不存在快递明细,生成快递对账单明细失败");
        }
        return $ret;
    }

    /**
     * @todo 通过对账编号查询明细信息
     */
    function get_detail_data_by_code($dz_code) {
        $res = [];

        $sql_main = "FROM {$this->table} WHERE dz_code=:dz_code";
        $sql_values = [':dz_code' => $dz_code];
        //总快递费用
        $sql = 'SELECT SUM(weigh_express_money) weigh_express_money ' . $sql_main;
        $res['sum_cost'] = $this->db->get_value($sql, $sql_values);
        //快递
        $sql = 'SELECT DISTINCT express_code ' . $sql_main;
        $express_arr = $this->db->get_all_col($sql, $sql_values);
        $res['express'] = load_model('base/ArchiveSearchModel')->get_archives_map('express',$express_arr);
        //省
        $sql = 'SELECT DISTINCT receiver_province ' . $sql_main;
        $province_arr = $this->db->get_all_col($sql, $sql_values);
        $res['province'] = load_model('base/ArchiveSearchModel')->get_archives_map('area',$province_arr);

        return $res;
    }

    /**
     * @todo 刷新称重数据
     */
    function refresh_record($data) {
        $this->begin_trans();
        $sql = "DELETE FROM {$this->table} WHERE dz_code=:dz_code AND store_code=:store_code";
        $sql_value = array(":dz_code" => $data['dz_code'], ":store_code" => $data['store_code']);
        $ret = $this->db->query($sql, $sql_value);
        if (!$ret) {
            $this->rollback();
            return $this->format_ret(-1, '', '刷新称重数据失败');
        }
        $res = $this->create_detail($data);
        if ($res['status'] != 1) {
            $this->rollback();
            return $this->format_ret(-1, '', '刷新称重数据失败');
        }
        $this->commit();
        return $ret;
    }

    /**
     * @todo 通过对账编号获取快递名称
     */
    function get_express_name_by_dz_code($dz_code) {
        $sql = "SELECT express_code FROM {$this->table} WHERE dz_code = :dz_code";
        $sql_value = array(":dz_code" => $dz_code);
        $data = $this->db->get_all($sql, $sql_value);
        foreach ($data as $express_code) {
            $express_arr[$express_code['express_code']] = oms_tb_val('base_express', 'express_name', array('express_code' => $express_code['express_code']));
        }
        return array_unique($express_arr);
    }

    function get_detail_by_id($detail_dz_id) {
        $sql_detail = "SELECT detail_dz_id,express_no,weigh_express_money FROM {$this->table} WHERE detail_dz_id = :detail_dz_id";
        $sql_value = array(':detail_dz_id' => $detail_dz_id);
        return $this->db->get_row($sql_detail, $sql_value);
    }

    /**
     * @todo 手动核销
     */
    function opt_hx($request) {
        $data = array("hx_status" => 1, "express_money" => $request['express_money'], 'hx_time' => date('Y-m-d H:i:s', time()));
        $where = array("detail_dz_id" => $request['detail_dz_id']);
        $ret = $this->db->update($this->table, $data, $where);
        $res = (!$ret) ? array('status' => -1, 'data' => '', 'message' => '手动核销失败') : array('status' => 1, 'data' => '', 'message' => '手动核销成功');
        return $res;
    }

    function import_detail($request) {
        $sql_detail = "SELECT express_no,weigh_express_money,express_money,hx_status FROM {$this->table} WHERE dz_code=:dz_code AND express_code=:express_code";
        $sql_value = array(':dz_code' => $request['dz_code'], ':express_code' => $request['express_type']);
        $detail = $this->db->get_all($sql_detail, $sql_value);
        $express = array();
        $this->read_csv_express($request['url'], $express);
        $import_count = count($express);
        $error_msg = array();
        $err_num = 0;

        foreach ($express as $express_no => $express_money) {
            $res = $this->deep_in_array($express_no, $detail);
            if (is_array($res)) {
                if ($res['express_no'] == $express_no) {
                    $d[$express_no] = ($res['weigh_express_money'] == $express_money) ? '1' : '0';
                    continue;
                }
            } else {
                unset($express[$express_no]);
            }
            $error_msg[] = array($express_no => '不存在此快递的快递单号');
            $err_num++;
        }
        //拼接批量更新sql语句
        $cur_time = date("Y-m-d H:i:s", time());
        $sql = "UPDATE {$this->table} SET  hx_time = '{$cur_time}',express_money = CASE express_no ";
        foreach ($express as $express_no => $express_money) {
            $sql .= sprintf("WHEN '%s' THEN '%s' ", $express_no, $express_money);
        }
        $sql .= "END,hx_status = CASE express_no ";
        foreach ($express as $express_no => $express_money) {
            //当称重运费和快递运费相等时才为已核销
            $hx_status = (isset($d[$express_no]) && !empty($d[$express_no])) ? $d[$express_no] : 0;
            $sql .= sprintf("WHEN '%s' THEN '%s' ", $express_no, $hx_status);
        }
        $express_no_str = "'" . join("','", array_keys($express)) . "'";
        $sql .= " END WHERE dz_code='{$request['dz_code']}' AND express_code='{$request['express_type']}' AND express_no IN($express_no_str)";
        $ret = $this->query($sql);
        $success_num = $import_count - $err_num;
        $message = '导入成功' . $success_num . "条数据";
        if ($err_num > 0 || !empty($error_msg)) {
            $message .= ',' . '失败数量:' . $err_num;
            $fail_top = array('快递单号', '错误信息');
            $file_name = $this->create_import_fail_files($fail_top, $error_msg);
//            $message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
            $url = set_download_csv_url($file_name, array('export_name' => 'error'));
            $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        }
        $ret['message'] = $message;
        return $ret;
    }

    function create_import_fail_files($fail_top, $error_msg) {
        $file_str = implode(",", $fail_top) . "\n";
        foreach ($error_msg as $key => $val) {
            $key = array_keys($val);
            $val_data = array($key[0], $val[$key[0]]);
            $file_str .= implode(",", $val_data) . "\r\n";
        }
        $filename = md5("order_express_dz" . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        return $filename;
    }

    function read_csv_express($file, &$express) {
        $file = fopen($file, "r");
        $i = 0;
        while (!feof($file)) {
            $row = fgetcsv($file);
            if ($i >= 1) {
                $this->tran_csv($row);
                if (!empty($row[0])) {
                    $express[trim($row[0])] = $row[1];
                }
            }
            $i++;
        }
        fclose($file);
    }

    private function tran_csv(&$row) {
        if (!empty($row)) {
            foreach ($row as &$val) {
                $val = iconv('gbk', 'utf-8', $val);
                $val = str_replace('"', '', $val);
            }
        }
    }

    /**
     * @todo 判断值在多维数组中是否存在
     */
    function deep_in_array($value, $array) {
        foreach ($array as $item) {
            if (!is_array($item)) {
                if ($item == $value) {
                    return true;
                } else {
                    continue;
                }
            }
            if (in_array($value, $item)) {
                return $item;
            } else if ($this->deep_in_array($value, $item)) {
                return true;
            }
        }
        return false;
    }

    function get_express_by_dz_code($params) {
        $sql = "SELECT receiver_province province_id,SUM(weigh_express_money) money FROM {$this->table} WHERE dz_code=:dz_code AND express_code=:express_code GROUP BY receiver_province";
        $sql_values = [':dz_code' => $params['dz_code'], ':express_code' => $params['express_code']];
        $data = $this->db->get_all($sql, $sql_values);
        if (!empty($data)) {
            $province_arr = array_column($data, 'province_id');
            $province_data = load_model('base/ArchiveSearchModel')->get_archives_map('area',$province_arr);
            foreach ($data as &$row) {
                $row['province_name'] = isset($province_data[$row['province_id']]) ? $province_data[$row['province_id']] : '';
            }
        }

        return $data;
    }

}
