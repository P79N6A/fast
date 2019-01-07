<?php

/**
 * 店铺监控
 *
 * @author wq
 *
 */
ini_set('memory_limit', '800M');
set_time_limit(0);
require_model('moniter/MoBaseModel');

class MoKhModel extends MoBaseModel {

    protected $param = array();
    protected $ret_data = array();

    //删除客户日志
    function del_sys_log_cli() {
        $func = 'remove_kh_sys_log';
        $this->exec_kh_db($func);
        return array();
    }

    function remove_kh_sys_log(&$db) {
        $conf = require_conf('kh_db/del_log');
        foreach ($conf as $v) {
            $is_exec = TRUE;
            $del_row = $v['del_row'];
            while ($is_exec) {
                $db->query($v['sql'], $v['sql_value']);
                $num = $db->affected_rows();
                if ($num < $del_row) {
                    $is_exec = FALSE;
                }
            }
        }
    }

    function get_11_data($filer = array()) {
        set_time_limit(0);
        $func = 'get_select_data';
        $sql = "select count(1) as num,sum(order_money) AS all_money ,shop_code,seller_nick from api_order where 1 ";

        if (isset($filer['type'])&&!empty($filer['type'])) {
            $sql .=" AND source='{$filer['type']}' ";
        }
        if (empty($filer['date_start']) || empty($filer['date_end'])) {
            $sql .= " AND 1=2 ";
        } else {
            $date_start = $filer['date_start'] . ' 00:00:00';
            $date_end = $filer['date_end'] . ' 23:59:59';
            $sql .=" AND  ( ( pay_time>='{$date_start}' AND pay_time<='{$date_end}') OR ( order_first_insert_time>='{$date_start}' AND order_first_insert_time<='{$date_end}' AND status=1 )) ";
        }


        $sql.=" group by shop_code";

        $this->param['sql'] = $sql;
        $this->exec_kh_db($func);
        $data = $this->ret_data;
        $new_data = array();
        $num_all = 0;
        $money_all = 0;
        foreach ($data as $kh_id => $kh_data) {
            if (!empty($kh_data)) {
                $kh_name = $this->get_kh_name($kh_id);
                foreach ($kh_data as $val) {
                    $val['kh_id'] = $kh_id;
                    $val['kh_name'] = $kh_name;
                    $val['num'] = (int) $val['num'];
                    $val['all_money'] = (float) $val['all_money'];
                    $new_data[] = $val;
                    $num_all = bcadd($num_all, $val['num']);
                    $money_all = bcadd($money_all, $val['all_money']);
                }
            }
        }
        $ret_data = array(
            'data' => $new_data,
            'data_all' => array(
                'order_num' => $num_all,
                'order_money' => $money_all,
            ),
        );
        return $this->format_ret(1, $ret_data);
    }

    function get_kh_name($kh_id) {
        $sql = "select kh_name from osp_kehu where kh_id = :kh_id";
        return $this->db->get_value($sql, array(':kh_id' => $kh_id));
    }

    function select_data($sql) {
        $func = 'get_select_data';
        $this->param['sql'] = $sql;
        $this->exec_kh_db($func);
        $data = $this->ret_data;
        $csv_data = array();
        $key_arr = array();
        $content_csv = array();

        foreach ($data as $kh_id => $kh_data) {
            if (!empty($kh_data)) {
                foreach ($kh_data as $val) {
                    $key_arr = array_keys($val);
                    $v_data = array_values($val);
                    $kh_arr = array($kh_id);
                    $csv_data = array_merge($kh_arr, $v_data);
                    $content_csv[] = implode(",", $csv_data);
                }
            }
        }
        //   var_dump(        $content_csv,$data);die;
        $csv_str = "kh_id," . implode(",", $key_arr) . "\r\n";
        $csv_str .= join("\r\n", $content_csv) . "\r\n";
        $filename = ROOT_PATH . 'weboperate/temp/kh_info' . date('Y_m_d_H_i_s') . '.csv';
        file_put_contents($filename, $csv_str, FILE_APPEND);
        return $this->format_ret();
    }

    function get_select_data(&$db) {
        $sql = $this->param['sql'];
        try {


            $data = $db->get_all($sql);

            if (!empty($data)) {
                $this->ret_data[$this->kh_id] = $data;
            }
        } catch (Exception $ex) {
            
        }
    }

}
