<?php
require_model('tb/TbModel');
require_lib("comm_util");

class ValueorderLogModel extends TbModel {
    /**
     * 表名
     */
    public function get_table() {
        return 'osp_valueorder_log';
    }
    
    /**
     * 日志记录
     * @param string $num    订购编号
     * @param string $action 操作名称
     * @param string $status 订单状态
     * @param string $remark 订单备注
     */
    public function log($num, $action, $status, $remark = '')
    {
        $this->insert(array(
            'val_num'      => $num,
            'val_operator' => CTX()->get_session("user_id"),
            'val_action'   => $action,
            'val_status'   => $status,
            'val_remark'   => $remark,
            'val_time'     => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'])
        ));
    }
    
    public function getLogByPage($filter)
    {
        $select = "`m`.`val_action`, `m`.`val_status`, `m`.`val_remark`, `m`.`val_time`, `u`.`user_code`";
        $sql_main = " FROM `{$this->table}` AS `m` "
                  . "LEFT JOIN `sys_user` AS `u` ON `m`.`val_operator` = `u`.`user_id` "
                  . "WHERE `val_num` = :val_num ORDER BY `val_time` DESC";
        $sql_value = array(':val_num' => $filter['val_num']);
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_value, $select);
        array_walk($data['data'], function (&$row) {
            $status = array('已下单', '已付款', '已审核', '已作废');
            $row['val_status'] = $status[$row['val_status']];
        });
        return $this -> format_ret(OP_SUCCESS, $data);
    }
}
