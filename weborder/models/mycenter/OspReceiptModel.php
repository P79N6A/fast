<?php

require_model('tb/TbModel');

class OspReceiptModel extends TbModel {

    function get_table() {
        return 'osp_receipt';
    }

    function get_by_kh_id($kh_id) {
        $sql = "SELECT * FROM {$this->table} r
                        LEFT JOIN osp_receipt_apply ra 
                        ON r.receipt_apply_id=ra.apply_id 
                        WHERE r.kh_id=:kh_id";
        $sql_value = array(":kh_id" => $kh_id);
        $data = $this->db->get_all($sql, $sql_value);
        foreach ($data as &$value) {
            switch ($value['status']) {
                case 1:
                    $value['status_name'] = '未审核';
                    break;
                case 2:
                    $value['status_name'] = '已审核';
                    break;
                case 3:
                    $value['status_name'] = '已开票';
                    break;
            }
        }
        return $data;
    }

    /**
     * @todo 删除数据
     */
    function delete_info_by_id($receipt_id) {
        //删除申请发票明细
        $ret = load_model('mycenter/OspReceiptApplyModel')->delete_info_by_receipt_id($receipt_id);
        if ($ret) {
            $res = parent::delete(array('receipt_id' => $receipt_id));
            $ret = ($res) ? array('status' => 1, 'data' => '', 'message' => '删除成功') : array('status' => 1, 'data' => '', 'message' => '删除失败');
            return $ret;
        } else {
            return array('status' => 1, 'data' => '', 'message' => '删除失败');
        }
    }

}
