<?php

require_model('tb/TbModel');

class StrategyLogModel extends TbModel {
    function get_table() {
        return 'op_strategy_log';
    }
    function get_by_page($filter) {
        
    }
    
    function get_strategy_log_by_customer_code($type,$strategy_code,$customer_code){
       
        $sql_values = array(
            ':type'=>$type,
            ':strategy_code'=>$strategy_code,
            ':customer_code'=>$customer_code,
        );
        $sql = "select 1 as num from op_strategy_log
        where type = :type AND strategy_code=:strategy_code  AND customer_code = :customer_code  AND is_success=1";

       $num = $this->db->get_value($sql,$sql_values);
       $num = empty($num)?0:$num;
       return $this->format_ret(1,$num);
       
       
    }
    
    function check_is_sended($data){
        $sql_values = array(
            ':type'=>$data['type'],
            ':strategy_code'=>$data['strategy_code'],
            ':customer_code'=>$data['customer_code'],
            ':strategy_detail_id'=>$data['strategy_detail_id'],
            ':sell_record_code'=>$data['sell_record_code'],
        );
        $sql = "select count(1) as num from op_strategy_log
        where type = :type AND strategy_code=:strategy_code  AND customer_code = :customer_code  AND strategy_detail_id = :strategy_detail_id and sell_record_code=:sell_record_code  and is_success=1";
        $row = $this->db->get_row($sql,$sql_values);
        return $this->format_ret(1,$row['num']);
    }


    /**
     * 日志详情信息
     * @param $filter
     * @return array
     */
    function get_by_record_page($filter) {
        if(isset($filter['keyword_type'])&&$filter['keyword_type']!==''){
            $filter[$filter['keyword_type']]=trim($filter['keyword']);
        }
        $sql_main = "FROM {$this->table} ogs inner join oms_sell_record osr on ogs.sell_record_code = osr.sell_record_code WHERE 1=1 ";
        $sql_values = array();
        if (isset($filter['deal_code']) && $filter['deal_code'] != '') {
            $sql_main .= " AND ogs.deal_code = :deal_code ";
            $sql_values[':deal_code'] = $filter['deal_code'] ;
        }
        if (isset($filter['sell_record_code']) && $filter['sell_record_code'] != '') {
            $sql_main .= " AND ogs.sell_record_code = :sell_record_code ";
            $sql_values[':sell_record_code'] = $filter['sell_record_code'] ;
        }
        $select = 'ogs.is_success,ogs.strategy_content,ogs.deal_code,ogs.sell_record_code,osr.buyer_name,osr.shop_code,osr.sku_num,osr.goods_num,osr.payable_money,osr.express_money ';
        $sql_main .= " order by ogs.lastchanged desc";

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        filter_fk_name($data['data'], array('shop_code|shop'));
        foreach ($data['data'] as &$v){
            if($v['is_success']){
                $content_arr = json_decode($v['strategy_content'],true);
                $detail_arr = array();
                foreach ($content_arr as $key=>$val){
                    $detail_arr = array_merge($detail_arr,array_keys($val));
                }
                $detail_values = array();
                $detail_str = $this->arr_to_in_sql_value($detail_arr,'op_gift_strategy_detail_id',$detail_values);
                if($detail_str == ''){
                    $v['strategy_content'] = '';
                }else{
                    $content_str = '';
                    $detail_sql = 'select ogsd.name,ogs.strategy_name,ogsd.op_gift_strategy_detail_id from op_gift_strategy_detail ogsd INNER  JOIN  op_gift_strategy ogs on ogs.strategy_code = ogsd.strategy_code where ogsd.op_gift_strategy_detail_id in ('.$detail_str.')';
                    $detail_ret = $this->db->get_all($detail_sql,$detail_values);
                    $detail_ret = load_model('util/ViewUtilModel')->get_map_arr($detail_ret, 'op_gift_strategy_detail_id');
                    foreach ($content_arr as $key=>$val){
                        foreach ($val as $detail_key=>$value){
                            if(isset($detail_ret[$detail_key])){
                                $content_str .= $detail_ret[$detail_key]['strategy_name'].'策略下使用规则'.$detail_ret[$detail_key]['name'].':<br/>';
                                foreach ($value as $name_val){
                                    foreach ($name_val as $sku_info){
                                        $content_str .='&nbsp;&nbsp;sku为'.$sku_info['sku'].',数量为'.$sku_info['num'].';';
                                    }
                                }
                            }else{
                                $content_str .= $key.'策略下使用规则'.$detail_key.':<br/>';
                                foreach ($value as $name_val){
                                    foreach ($name_val as $sku_info){
                                        $content_str .='&nbsp;&nbsp;sku为'.$sku_info['sku'].',数量为'.$sku_info['num'].';';
                                    }
                                }
                            }
                        }
                    }
                }
                $v['strategy_content'] = $content_str.'<br/>';
            }
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);

    }
}
?>
