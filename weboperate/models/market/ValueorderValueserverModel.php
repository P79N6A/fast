<?php
require_model('tb/TbModel');
require_lib("comm_util");

class ValueorderValueserverModel extends TbModel {

    /**
     * 表名
     */
    public function get_table() {
        return 'osp_valueorder_valueserver';
    }
    
    /**
     * 判断是否已经添加增值服务
     */
    public function isAdded($request)
    {
        if (isset($request['vs_val_num']) && isset($request['vs_value_id'])) {
            $ret = $this->get_row(array('vs_val_num' => $request['vs_val_num'], 'vs_value_id' => $request['vs_value_id']));
        } elseif(isset($request['vs_val_num']) && !isset($request['vs_value_id'])) {
            $ret = $this->get_row(array('vs_val_num' => $request['vs_val_num']));
        } else{
            $ret['data'] = array();
        }
        return count($ret['data']) > 0;
    }

    /*
     * 获取增值服务订购查询
     */
    public function valueorderList($id) {
        // 增值服务类型 value_name
        // 增值服务描述 value_desc
        // 金额 value_price
        // 明细优惠 vs_cheap_price
        // 实际成交金额 vs_actual_price
        $sql = "SELECT `vs_value_id`, `value_name`, `value_desc`, `value_price`, `vs_cheap_price`, `vs_actual_price` "
             . "FROM `{$this->table}` AS `os` "
             . "LEFT JOIN `osp_valueserver` AS `vs` "
             . "ON `os`.`vs_value_id` = `vs`.`value_id` "
             . "WHERE `os`.`vs_val_num` = '{$id}'";
        return $this->db->getAll($sql);
    }
}
