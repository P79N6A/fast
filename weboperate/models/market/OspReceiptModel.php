<?php

require_model('tb/TbModel');

class OspReceiptModel extends TbModel {

    function get_table() {
        return 'osp_receipt';
    }

    function get_by_page($filter) {
        $sql_main = " FROM {$this->table} r LEFT JOIN osp_kehu k ON r.kh_id=k.kh_id WHERE 1=1  ";
        //开票时间
        if (isset($filter['applied_time_start']) && !empty($filter['applied_time_start'])) {
            $sql_main .= " AND r.applied_time >= :applied_time_start ";
            $sql_values[':applied_time_start'] = $filter['applied_time_start'] . ' 00:00:00';
        }
        if (isset($filter['applied_time_end']) && !empty($filter['applied_time_end'])) {
            $sql_main .= " AND r.applied_time <= :applied_time_end ";
            $sql_values[':applied_time_end'] = $filter['applied_time_end'] . ' 23:59:59';
        }
        if (empty($filter['receipt_list_tab'])) {
            $sql_main .= "AND r.status = 1 ";
        }
        if (isset($filter['receipt_list_tab']) && ($filter['receipt_list_tab'] == 'tabs_all')) {
            $sql_main .= "AND r.status IN(1,2,3) ";
        }
        if (isset($filter['receipt_list_tab']) && ($filter['receipt_list_tab'] == 'tabs_check')) {
            $sql_main .= "AND r.status = 1 ";
        }
        if (isset($filter['receipt_list_tab']) && ($filter['receipt_list_tab'] == 'tabs_confirm')) {
            $sql_main .= "AND r.status < 3 ";
        }
        $sql_main .= " ORDER BY receipt_id ASC";
        $select = "r.*,k.kh_name";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$value) {
            $value['is_check'] = ($value['status'] > '1') ? 0 : 1;
            $value['is_draw'] = ($value['status'] == '2') ? 1 : 0;
            $value['receipt_money'] = sprintf("%.2f", $value['receipt_money']);
            switch ($value['status']) {
                case 1:
                    $value['status'] = '未审核';
                    break;
                case 2:
                    $value['status'] = '已审核';
                    break;
                case 3:
                    $value['status'] = '已开票';
                    break;
            }
        }
        $ret_status = OP_SUCCESS;
        return $this->format_ret($ret_status, $data);
    }

    /**
     * @todo 通过receipt_id获取发票资料详情
     */
    function get_info_by_receipt_id($receipt_id) {
        $sql = "SELECT * FROM osp_receipt_apply ra
                        LEFT JOIN osp_receipt r ON ra.apply_id=r.receipt_apply_id 
                        WHERE receipt_id=:receipt_id";
        $sql_value = array(":receipt_id" => $receipt_id);
        $ret = $this->db->get_row($sql, $sql_value);
        return $ret;
    }

    /**
     * @todo 审核发票
     */
    function check_receipt($receipt_id) {
        $data = array('status' => 2);
        $where = array('receipt_id' => $receipt_id);
        $ret = $this->db->update($this->table, $data, $where);
        if ($ret) {
            $ret = array('status' => 1, 'data' => '', 'message' => '审核成功');
        } else {
            $ret = array('status' => -1, 'data' => '', 'message' => '审核失败');
        }
        return $ret;
    }

    /**
     * @todo 审核发票
     */
    function draw_receipt($receipt_id) {
        $data = array('status' => 3, 'check_time' => date('Y-m-d H:i:s', time()));
        $where = array('receipt_id' => $receipt_id);
        $ret = $this->db->update($this->table, $data, $where);
        if ($ret) {
            $ret = array('status' => 1, 'data' => '', 'message' => '开票成功');
        } else {
            $ret = array('status' => -1, 'data' => '', 'message' => '开票失败');
        }
        return $ret;
    }
    
    /**
     * @todo 获取区域
     */
    function get_area($parent_id) {
        $rs = array();
        if (strlen($parent_id) == 6) {
            $p = $parent_id . '000000';
            $sql = "SELECT * FROM base_area WHERE parent_id = '$parent_id' or parent_id = '$p'  ";
            $rs = $this->db->get_all($sql);
        } else {
            $sql = "SELECT * FROM base_area WHERE parent_id = '$parent_id' ";
            $rs = $this->db->get_all($sql);
        }
        return $rs;
    }

}
