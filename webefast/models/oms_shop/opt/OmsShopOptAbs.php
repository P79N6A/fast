<?php

require_model('tb/TbModel');

abstract class OmsShopOptAbs extends TbModel {

    protected $sell_record = array();
    protected static $sell_record_log = array();
    protected $new_sell_record = array();
    protected $sell_record_detail = array();
    protected $params = array();

    abstract function opt(&$params);

    abstract function check(&$sell_record, &$sell_record_detail);

    abstract protected function set_opt_log($desc = '');

    abstract function check_params(&$params);

    function set_log($log_data) {
        //todo action_code  判断标识
        $log_data['action_desc'] = isset($log_data['action_desc']) ? $log_data['action_desc'] : '';
        $log_data['record_code'] = $this->sell_record['record_code'];
        $log_data['action_time'] = date('Y-m-d H:i:s');

        $log_data['user_code'] = CTX()->get_session('user_code');
        $log_data['user_name'] = CTX()->get_session('user_name');
        $log_data['pay_status'] = isset($this->new_sell_record['pay_status']) ? $this->new_sell_record['pay_status'] : $this->sell_record['pay_status'];
        $log_data['send_status'] = isset($this->new_sell_record['send_status']) ? $this->new_sell_record['send_status'] : $this->sell_record['send_status'];

        self::$sell_record_log[] = $log_data;
        $this->save_opt_log();
    }

    function save_opt_log() {
        if (!empty(self::$sell_record_log)) {
            $this->insert_multi_exp('oms_shop_sell_record_log', self::$sell_record_log);
        }
        self::$sell_record_log = array();
    }

    function save_record($where = '') {
        $status = FALSE;
        if (!empty($this->new_sell_record)) {
            $where = " record_code = '{$this->sell_record['record_code']}' AND " . $where;
            $this->update_exp('oms_shop_sell_record', $this->new_sell_record, $where);
            $num = $this->affected_rows();
            $status = ($num == 1) ? TRUE : FALSE;
        }
        return $status;
    }

    protected function get_record_data() {
        return array('record_data' => &$this->sell_record, 'record_detail' => &$this->sell_record_detail);
    }

    protected function get_record_new_data() {
        return array('record_data' => &$this->sell_record, 'record_detail' => &$this->sell_record_detail);
    }

    protected function opt_other($opt, $params, $is_new_data = 0) {
        if ($is_new_data == 0) {
            $record_data = $this->get_record_data();
        } else {
            $record_data = load_model('oms_shop/OmsShopOptModel')->get_record_info($this->sell_record['record_code']);
        }
        return load_model('oms_shop/OmsShopOptModel')->opt_action($record_data, $opt, $params);
    }

}
