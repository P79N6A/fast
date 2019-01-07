<?php

require_model('tb/TbModel');
require_lang('api');

/**
 * 定义进销存业务接口
 * @author WMH
 */
abstract class JxcOptApiModel extends TbModel {

    protected $record_type = ''; //单据类型
    protected $record_code = ''; //单据编号
    protected $record = array(); //单据主信息
    //映射关系：单据类型=>业务名称
    protected $record_map = array(
        'pur_notice' => '采购通知单',
        'purchase' => '采购入库单',
        'pur_return_notice' => '采购退货通知单',
        'pur_return' => '采购退货单',
        'take_stock' => '盘点单',
        'wbm_store_out' => '批发销货单',
        'wbm_return_notice' => '批发退货通知单',
        'wbm_return' => '批发退货单',
        'box_record' => '装箱单',
    );
    //映射关系：单据类型=>单据明细表
    protected $detail_table_map = array(
        'pur_notice' => 'pur_order_record_detail',
        'purchase' => 'pur_purchaser_record_detail',
        'pur_return_notice' => 'pur_return_notice_record_detail',
        'pur_return' => 'pur_return_record_detail',
        'take_stock' => 'stm_take_stock_record_detail',
        'wbm_store_out' => 'wbm_store_out_record_detail',
        'wbm_return_notice' => 'wbm_return_notice_detail_record',
        'wbm_return' => 'wbm_return_record_detail',
        'box_record' => 'b2b_box_record_detail',
    );
    protected $pur = array(
        'pur_notice', 'purchase', 'pur_return', 'wbm_return_notice'
    );
    protected $stm = array(
        'take_stock', 'wbm_store_out', 'wbm_return', 'pur_return_notice'
    );

    /**
     * 单据创建
     */
    abstract function api_record_create($param);

    /**
     * 列表查询
     */
    abstract function api_record_get($param);

    /**
     * 明细查询
     */
    abstract function api_detail_get($param);

    /**
     * 明细更新
     */
    abstract function api_detail_update($param);

    /**
     * 单据确认
     */
    abstract function api_record_accept($param);

    /**
     * 添加日志
     */
    abstract protected function set_opt_log($action_name, $action_note);

    /**
     * 检查订单状态
     * @return array
     */
    protected function check_record() {
        $record = $this->get_record_by_code();
        $msg = $this->record_map[$this->record_type];
        $err_data = array('record_code' => $this->record_code);
        if (empty($record)) {
            return $this->format_ret(-10002, $err_data, $msg . '不存在');
        }
        if (in_array($this->record_type, $this->pur)) {
            $sure_fld = 'is_check';
        }
        if (in_array($this->record_type, $this->stm)) {
            $sure_fld = 'is_sure';
        }
        if (isset($sure_fld) && $record[$sure_fld] == 1) {
            return $this->format_ret(-1, $err_data, $msg . '已确认');
        }

        return $this->format_ret(1);
    }

    /**
     * 获取单据信息
     * @return array 数据集
     */
    protected function get_record_by_code($fld = '*') {
        $sql = "SELECT {$fld} FROM {$this->table} WHERE record_code=:record_code";
        $record = $this->db->get_row($sql, array(":record_code" => $this->record_code));
        $this->record = $record;
        return $record;
    }

    protected function get_detail_by_code($fld = '*') {
        $table = $this->detail_table_map[$this->record_type];
        $sql = "SELECT {$fld} FROM {$table} WHERE record_code=:record_code";
        $detail = $this->db->get_all($sql, array(":record_code" => $this->record_code));
        return $detail;
    }

    protected function set_log($log_data) {
        $record_type_trans = array(
            'pur_notice' => 'order_record',
            'purchase' => 'purchase_record',
            'pur_return_notice' => 'pur_return_notice_record',
            'pur_return' => 'return_record',
            'take_stock' => 'take_stock_record',
            'wbm_store_out' => 'store_out_record',
            'wbm_return_notice' => 'wbm_return_notice_record',
            'wbm_return' => 'wbm_return_record',
            'box_record' => 'box_record',
        );
        $log = array(
            'user_id' => 1,
            'user_code' => 'OPENAPI',
            'add_time' => date('Y-m-d H:i:s'),
            'sure_status' => '',
            'finish_status' => '',
            'action_name' => '',
            'module' => $record_type_trans[$this->record_type],
            'action_note' => ''
        );
        $log = array_merge($log, $log_data);
        load_model('pur/PurStmLogModel')->insert($log);
    }

    /**
     * 检查数据是否存在
     * @param string $table 表名
     * @param string $field 字段名
     * @param string $value 字段值
     * @return int
     */
    protected function check_data_exists($table, $field, $value) {
        $sql = "SELECT COUNT(1) FROM {$table} WHERE {$field}=:{$field}";
        $exists_count = $this->db->get_value($sql, array(":{$field}" => $value));
        if ($exists_count < 1) {
            return $this->format_ret(-10002, array($field => $value), 'API_RETURN_MESSAGE_10002');
        }
        return $this->format_ret(1);
    }

    /**
     * 生成单据号
     * @return string 单据编号
     */
    protected function produce_record_code() {
        $obj_map = array(
            'pur_notice' => 'pur/OrderRecordModel',
            'purchase' => 'pur/PurchaseRecordModel',
            'take_stock' => 'stm/TakeStockRecordModel',
            'wbm_store_out' => 'wbm/StoreOutRecordModel',
            'wbm_return' => 'wbm/ReturnRecordModel',
            'pur_return' => 'pur/ReturnRecordModel',
        );

        return load_model($obj_map[$this->record_type])->create_fast_bill_sn();
    }

}
