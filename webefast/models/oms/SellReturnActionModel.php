<?php
/**
 * 退单日志相关业务
 */
require_model('tb/TbModel');

class SellReturnActionModel extends TbModel {
    /**
     * @var string 退单日志表
     */
    protected $table = 'oms_sell_return_action';
    //订单状态
    public $return_order_status = array(
        0 => '未确认',
        1 => '已确认',
        3 => '已作废'
    );

    public $return_shipping_status = array(
        0 => '待收货',
        1 => '已确认收货',
        2 => '待收货'
    );

    public $finance_check_status = array(
        0 => '待财审',
        1 => '已财审',
        2 => '待财审',
        3 => '财务退回'
    );
    function get_by_page($filter) {
        $sql_main = " FROM {$this->table} r1 WHERE 1 ";
        if(isset($filter['sell_return_code']) && !empty($filter['sell_return_code'])){
            $sql_main .= " AND sell_return_code = :sell_return_code ";
            $sql_values[':sell_return_code'] = $filter['sell_return_code'];
        }
        $sql_main .= " ORDER BY r1.sell_return_action_id DESC";
        $select =" * ";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$action){
            $action['return_order_status'] = $this->return_order_status[$action['return_order_status']];
                $action['return_shipping_status'] = $this->return_shipping_status[$action['return_shipping_status']];
                $action['finance_check_status'] = $this->finance_check_status[$action['finance_check_status']];
        }
        return $this->format_ret(1, $data);
    }
    
    /**
     * 获取沟通日志
     */
        function get_communication_log($sell_return_code) {
        $sql = "select user_name,action_note from {$this->table} where  sell_return_code ='{$sell_return_code}' AND action_name='沟通日志' ORDER BY create_time DESC";
        $value = $this->db->get_all($sql);
        $result['user_name']=$value[0]['user_name'];
        $action_note=' ';
        foreach($value as $v){
            if(!empty($v['action_note'])){
                 $action_note.=' '. $v['action_note'];
            }
        }
        $result['action_note']= $action_note;
        return $result;
    }

}
?>