<?php

require_lang('api');
require_model('tb/TbModel');

/**
 * ERP接口
 *
 * @author Master
 */
class ErpApiModel extends TbModel {

    /**
     * 单据打标
     * @param array $param
     * @return array
     */
    public function api_record_marking($param) {
        $k_required = array(
            's' => array('record_code', 'record_type'),
        );
        $arr_required = array();
        $ret_required = valid_assign_array($param, $k_required, $arr_required, TRUE);
        if ($ret_required['status'] !== TRUE) {
            return $this->format_ret(-10001, $ret_required['req_empty'], 'API_RETURN_MESSAGE_10001');
        }
        $record_code = $arr_required['record_code'];
        $record_type = $arr_required['record_type'];
        if (!in_array($record_type, array(1, 2))) {
            return $this->format_ret(-10005, array('record_type' => $record_type), '参数值有误');
        }
        if ($record_type == 1) {
            $sql = 'SELECT shop_code,order_status,deal_code,deal_code_list,shop_code,store_code FROM oms_sell_record WHERE sell_record_code=:code';
        } else {
            $sql = 'SELECT shop_code,return_order_status AS order_status,deal_code,deal_code_list,shop_code,store_code FROM oms_sell_return WHERE sell_return_code=:code';
        }

        $record = $this->db->get_row($sql, array(':code' => $record_code));
        if (empty($record)) {
            return $this->format_ret(-10002, array('record_code' => $record_code), '单据不存在');
        }
        if ($record['order_status'] == 3) {
            return $this->format_ret(-10006, array('record_code' => $record_code), '单据已作废');
        }
        $erp_type = $this->get_erp_product($record['shop_code']);
        if ($erp_type === FALSE) {
            return $this->format_ret(-10002, (object) array(), '单据店铺未关联erp配置');
        }
        $table = $erp_type == 1 ? 'api_bs3000j_trade' : 'api_bserp_trade';
        $erp_data = array();
        $erp_data['sell_record_code'] = $record_code;
        $erp_data['deal_code_list'] = $record['deal_code_list'];
        $erp_data['deal_code'] = $record['deal_code'];
        $erp_data['order_type'] = $record_type;
        $erp_data['shop_code'] = $record['shop_code'];
        $erp_data['store_code'] = $record['store_code'];
        $erp_data['upload_time'] = date('Y-m-d H:i:s');
        $erp_data['upload_status'] = 1;
        $ret = $this->insert_multi_duplicate($table, array($erp_data), 'upload_status=VALUES(upload_status)');
        if ($ret['status'] < 1) {
            return $this->format_ret(-1, array('record_code' => $record_code), '单据打标失败');
        }
        return $this->format_ret(1, (object) array(), '单据打标成功');
    }

    private function get_erp_product($shop_code) {
        $sql = 'SELECT ec.erp_system FROM erp_config AS ec INNER JOIN sys_api_shop_store AS ss ON ec.erp_config_id=ss.p_id WHERE ss.shop_store_type=0 AND ss.shop_store_code=:code';
        return $this->db->get_value($sql, array(':code' => $shop_code));
    }

}
