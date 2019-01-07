<?php

require_model("wms/WmsInvModel");

class JdwmsInvModel extends WmsInvModel {

    function __construct() {
        parent::__construct();
    }

    function inv_search($efast_store_code, $barcode_arr) {
        $this->get_wms_cfg($efast_store_code);

        $result = array();
        $error = array();
        foreach ($barcode_arr as $barcode) {
            $method = 'jingdong.eclp.stock.queryStock';
            $params = $this->set_params($barcode);
            $ret = $this->biz_req($method, $params);
            if ($ret['status'] > 0) {//total_page
                foreach ($ret['data']['querystock_result'] as $val) {
                    $goodsNo = $val['goodsNo']; //totalNum 
                    $num = $val['totalNum']; //totalNum 
                    $result[$goodsNo] = array('num' => $num);
                }
            } else {
                $error[] = $ret['data'];
            }
        }

        if (!empty($result)) {
            $result = $this->set_inv($result);
        }

        if (!empty($result)) {
            return $this->format_ret(1, $result);
        } else {
            return $this->format_ret(1, $error);
        }
    }

    private function set_inv($result) {
        $goods_no_arr = array_keys($result);
        $sql = "select api_code,sys_code from wms_archive where type=:type AND wms_config_id=:wms_config_id  ";
        $sql_values = array(
            ':type' => 'goods_barcode',
            ':wms_config_id' => $this->wms_cfg['wms_config_id'],
        );
        $str = $this->arr_to_in_sql_value($goods_no_arr, 'api_code', $sql_values);
        $sql .= " AND api_code IN ($str);";
        $data = $this->db->get_all($sql, $sql_values);
        $new_result = array();
        foreach ($data as $val) {
            $new_val = $result[$val['api_code']];
            $new_val['barcode'] = $val['sys_code'];
            $new_result[] = $new_val;
        }
        return $new_result;
    }

    function set_params($barcode) {

        $sql = "select sys_code,api_code from wms_archive where wms_config_id=:wms_config_id AND  sys_code =:sys_code   ";
        $sql_values = array(
            ':wms_config_id' => $this->wms_cfg['wms_config_id'],
            ':sys_code' => $barcode,
        );
        $row = $this->db->get_row($sql, $sql_values);
        $data = array(
            'deptNo' => $this->wms_cfg['deptNo'],
            'warehouseNo' => $this->wms_cfg['warehouseNo'],
            'goodsNo' => $row['api_code'],
//            'currentPage' => $params['page'],
//            'pagesize' => $params['pageSize'],
        );

        return $data;
    }

}
