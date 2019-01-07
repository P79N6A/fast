<?php

/**
 * 短信模板 相关业务
 *
 * @author dfr
 */
require_model('tb/TbModel');

class SapAdjustRecordModel extends TbModel {

    function get_table() {
        return 'sap_adjust_record';
    }

    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql_values = array();
        $sql_main = "FROM {$this->table} rl WHERE 1";
        //是否处理
        if ($filter['ex_list_tab'] == 'ok_handle') {
            $sql_main .= " AND rl.status = 1 ";
        } else {
            $sql_main .= " AND rl.status in (0,2) ";
        }
        //sap单号
        if (isset($filter['sap_record']) && $filter['sap_record'] != '') {
            $sql_main .= " AND rl.mblnr LIKE :sap_record";
            $sql_values[':sap_record'] = $filter['sap_record'] . '%';
        }
        //系统调整单单号
        if (isset($filter['stm_record_code']) && $filter['stm_record_code'] != '') {
            $sql_main .= " AND rl.stm_record_code LIKE :stm_record_code";
            $sql_values[':stm_record_code'] = $filter['stm_record_code'] . '%';
        }
        //仓库
        if (isset($filter['store_code']) && $filter['store_code'] !== '') {
     $arr = explode(',', $filter['store_code']);
            $str = $this->arr_to_in_sql_value($arr, 'store_code', $sql_values);
            $sql_main .= " AND rl.store_code in ( " . $str . " ) ";
        }
        //下载时间
        if (isset($filter['download_date_start']) && $filter['download_date_start'] != '') {
            $sql_main .= " AND rl.download_date >= :download_date_start";
            $sql_values[':download_date_start'] = $filter['download_date_start'];
        }
        if (isset($filter['download_date_end']) && $filter['download_date_end'] != '') {
            $sql_main .= " AND rl.download_date <= :download_date_end";
            $sql_values[':download_date_end'] = $filter['download_date_end'];
        }
        //处理时间
        if (isset($filter['handle_date_start']) && $filter['handle_date_start'] != '') {
            $sql_main .= " AND rl.handle_date >= :handle_date_start";
            $sql_values[':handle_date_start'] = $filter['handle_date_start'];
        }
        if (isset($filter['handle_date_end']) && $filter['handle_date_end'] != '') {
            $sql_main .= " AND rl.handle_date <= :handle_date_end";
            $sql_values[':handle_date_end'] = $filter['handle_date_end'];
        }
        if ($filter['ex_list_tab'] == 'ok_handle') {
            $sql_main .= " ORDER BY rl.handle_date DESC";
        } else {
            $sql_main .= " ORDER BY rl.download_date DESC";
        }

        $select = '*';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        foreach ($data['data'] as $key => $val) {
            $data['data'][$key]['shkzg'] = strtolower($val['shkzg']) == 'h' ? '减' : '增';
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    function insert($data, $download_date, $id = '') {
        $sql = "SELECT * FROM sys_api_shop_store WHERE outside_code = '1024' AND p_type = 3";
        $store_data = $this->db->get_row($sql);
        $store_code = $store_data['shop_store_code'];
        foreach ($data as &$val) {
            unset($val['menge']);
            if((($val['shkzg'] == 'H' && $val['umlgo'] != '1024') || empty($val['lgort'])) || (($val['shkzg'] == 'S' && $val['lgort'] != '1024') || empty($val['lgort']))) {
                unset($val);
                continue;
            }
        }
//            print_r($data);die;

        $this->begin_trans();
        try {
            //查询未处理的单据
            if (empty($id)) {
                //添加sap中间表
                $ret = $this->insert_dup($data, 'UPDATE', 'num');
                if ($ret['status'] != 1) {
                    $this->rollback();
                    return $ret;
                }
                $sql = "SELECT * FROM sap_adjust_record WHERE `status` = 0 OR `status` = 2";
                $data = $this->db->get_all($sql);
            }

            //组合明细数据
            $stm_adjust_arr = array();
            foreach ($data as $val) {
                if (array_key_exists($val['matnr'], $stm_adjust_arr)) {
                    if (strtolower($val['shkzg']) == 'h') {
                        $stm_adjust_arr[$val['matnr']]['num'] -= $val['num'];
                    } else {
                        $stm_adjust_arr[$val['matnr']]['num'] += $val['num'];
                    }
                } else {
                    if (strtolower($val['shkzg']) == 'h') {
                        $stm_adjust_arr[$val['matnr']]['num'] = -$val['num'];
                    } else {
                        $stm_adjust_arr[$val['matnr']]['num'] = $val['num'];
                    }
                }
            }
            $stm_detail = array();
            $error = array();
            $message = '';
           $lof_data = load_model("prm/GoodsLofModel")->get_sys_lof();
            foreach ($stm_adjust_arr as $key => $val) {
                $sql = "SELECT rl.*,r2.sell_price FROM goods_sku AS rl LEFT JOIN base_goods AS r2 ON rl.goods_code = r2.goods_code WHERE rl.barcode = '{$key}'";
                $sku_arr = $this->db->get_row($sql);
                if (empty($sku_arr)) {
                    $ret = $this->update(array('handle_info' => '条码不存在', 'status' => 2), array('matnr' => $key, 'download_date' => $download_date));
                    if (!empty($id)) {
                        $message = '条码不存在';
                        break;
                    }
                    continue;
                }
                $stm_detail[] = array(
                    'sku' => $sku_arr['sku'],
                    'lof_no' => $lof_data['lof_no'],
                    'production_date' => $lof_data['production_date'],
                    'spec1_code' => $sku_arr['spec1_code'],
                    'spec2_code' => $sku_arr['spec2_code'],
                    'goods_code' => $sku_arr['goods_code'],
                    'num' => $val['num'],
                    'barcode' => $key,
                    'price' => $sku_arr['sell_price'],
                    'money' => $sku_arr['sell_price'] * $val['num']
                );
            }
            if(!empty($message)) {
                return $this->format_ret('-1','',$message);
            }
            if (!empty($stm_detail)) {
                //生成调整主单
                $params['record_code'] = load_model('stm/StockAdjustRecordModel')->create_fast_bill_sn();
                $params['record_time'] = date('Y-m-d');
                $params['is_add_time'] = date('Y-m-d');
                $params['adjust_type'] = '802';
                $params['store_code'] = $store_code;
                $params['remark'] = 'SAP调整数量';
                $stm_info = M('stm_stock_adjust_record')->insert($params);
                if ($stm_info['status'] != 1) {
                    $this->rollback();
                    return $stm_info;
                }
                $stm_adjust_id = $stm_info['data'];
                //添加日志
                if (isset($stm_adjust_id) && $stm_adjust_id <> '') {
                    $ret = $this->add_adjust_log($stm_adjust_id,'创建','未验收');
                }
                //批次档案维护
                $ret = load_model('prm/GoodsLofModel')->add_detail_action($stm_info['data'], $stm_detail);
                //单据批次添加
                $ret = load_model('stm/GoodsInvLofRecordModel')->add_detail_action($stm_info['data'], $store_code, 'adjust', $stm_detail);
                if ($ret['status'] != '1') {
                    return $ret;
                }
                //调整单明细添加
                foreach ($stm_detail as $val) {
                    $val['pid'] = $stm_info['data'];
                    $val['record_code'] = $params['record_code'];
//                    $ret = M('stm_stock_adjust_record_detail')->insert($val);
                    $ret = $this->insert_exp('stm_stock_adjust_record_detail', $val);
                    //添加日志
                    if (isset($ret['data']) && $ret['data'] <> '') {
                        $ret = $this->add_adjust_log($stm_adjust_id, '增加明细','未验收');
                    }
                    $date = date('Y-m-d H:i:s');
                    $where = array('matnr' => $val['barcode'], 'download_date' => $download_date);
                    if (!empty($id)) {
                        $where['sap_adjust_record_id'] = $id;
                    }
                    $ret = $this->update(array('stm_record_code' => $params['record_code'], 'status' => 1, 'handle_date' => $date, 'handle_info' => ''), $where);
                }
                //回写调整单主单价格
                load_model('stm/StmStockAdjustRecordDetailModel')->mainWriteBack($stm_info['data']);
                //调整单验收
                $ret = load_model('stm/StockAdjustRecordModel')->checkin($stm_adjust_id);
                //添加日志
                if ($ret['status'] == 1) {
                    $ret = $this->add_adjust_log($stm_adjust_id, '验收', '已验收');
                }
            } else {
                return $this->format_ret('-1', '', '没有明细数据');
            }

            $this->commit();
            return $ret;
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', '保存失败:' . $e->getMessage());
        }
    }
    //添加调整单日志
    function add_adjust_log($id, $action_name,$finish_status) {
        //日志
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "", 'finish_status' => "{$finish_status}", 'action_name' => "{$action_name}", 'module' => "stock_adjust_record", 'pid' => $id);
        $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        $num = $this->affected_rows();
        return $ret1;
    }

    function get_by_id($id) {
        $sql = "SELECT * FROM sap_adjust_record WHERE sap_adjust_record_id = '{$id}'";
        $ret = $this->db->get_row($sql);
        return $ret;
    }

}
