<?php

/**
 * 商品批次库存帐管理相关业务
 *
 * @author wqian
 *
 */
require_model('tb/TbModel');
require_lang('prm');
require_model('prm/InvLogModel');

class InvOpLofModel extends TbModel {
    private $record_time = '';
    function get_table() {
        return 'goods_inv_lof';
    }

    private $lof_log;

    function get_lof_data_by_num($store_code,$sku, $num) {
        $sql = "select lof_no,production_date,stock_num,lock_num from goods_inv_lof where store_code=:store_code AND sku = :sku and stock_num>(lock_num+{$num}) ";
        $data = $this->db->get_all($sql, array(':sku' => $sku,':store_code'=>$store_code));
        if (!empty($data)) {
            foreach ($data as &$val) {
                $val['num'] = $val['stock_num'] - $val['lock_num'];
            }
        }
        return $this->format_ret(1, $data);
    }

    /*
     * 订单扫描批次库存调配    
     * @$oms_detail_lof 批次数据
     * @$new_lof_info 库存数据
     */
    function change_lof_inv($oms_detail_lof, $new_lof_info) {

        //日志
        $this->record_time = date('Y-m-d H:i:s');

        $new_oms_detail_lof = array_merge($oms_detail_lof, $new_lof_info);

        $this->init_log($oms_detail_lof, 0);
        $this->init_log($new_oms_detail_lof, 1);

        $status = $this->set_lof_inv($oms_detail_lof, $new_lof_info);
        $ret = $this->format_ret(1);
        if ($status) {
            $status = $this->cancel_lof_inv($oms_detail_lof);
            if (!$status) {
                $ret = $this->format_ret(-1, '', '库存异常，锁定库存为负');
            }
            $this->change_oms_detail_lof($oms_detail_lof, $new_lof_info);
        } else {
            $ret = $this->format_ret(-1, '', '批次库存不足');
        }
        if ($ret['status'] > 0) {
            $this->save_log(0);
            $this->save_log(1);
        }


        return $ret;
    }

    function cancel_lof_inv(&$oms_detail_lof) {
        $lock_num = $oms_detail_lof['num'];
        $sql = "update goods_inv_lof set lock_num=lock_num-{$lock_num} where 1 ";
        $key_arr = array('store_code',  'sku', 'lof_no', 'production_date');
        foreach ($key_arr as $key) {
            $sql .= " AND {$key} = '{$oms_detail_lof[$key]}'  ";
        }

        $sql .= " AND lock_num>={$lock_num} ";
        $this->db->query($sql);
        $run_num = $this->affected_rows();
        return ($run_num > 0) ? TRUE : FALSE;
    }

    function set_lof_inv(&$oms_detail_lof, $new_lof_info) {
        $lock_num = $oms_detail_lof['num'];
        $sql = "update goods_inv_lof set lock_num=lock_num+{$lock_num} where 1 ";
        $key_arr = array('store_code','sku');
        foreach ($key_arr as $key) {
            $sql .= " AND {$key} = '{$oms_detail_lof[$key]}'  ";
        }

        $sql .= " AND lof_no = '{$new_lof_info['lof_no']}'  ";
        $sql .= " AND production_date = '{$new_lof_info['production_date']}'  ";

        $sql .= " AND stock_num>=lock_num+{$lock_num} ";
        $this->db->query($sql);
        $run_num = $this->affected_rows();
        return ($run_num > 0) ? TRUE : FALSE;
    }

    function change_oms_detail_lof(&$oms_detail_lof, $new_lof_info) {
        $tb = 'oms_sell_record_lof';
        $sql = "update {$tb} set num=num-{$oms_detail_lof['num']} where  ";
        $key_arr = array('record_code', 'record_type', 'sku', 'lof_no', 'production_date');
        $where = "1 ";
        foreach ($key_arr as $key) {
            $where .= " AND {$key} = '{$oms_detail_lof[$key]}'  ";
        }
        $sql .=$where;
        $this->db->query($sql);

        //数量为0清除
        $sql = "delete from oms_sell_record_lof where {$where} AND num=0";
        $this->db->query($sql);

        $oms_detail_lof['lof_no'] = $new_lof_info['lof_no'];
        $oms_detail_lof['production_date'] = $new_lof_info['production_date'];
        unset($oms_detail_lof['id']);

        $this->insert_multi_duplicate($tb, array($oms_detail_lof), 'num = num+VALUES(num) ');
    }

    function get_lof_info($sku, $lof_no) {

        $sql = "select lof_no,production_date from goods_lof where sku='{$sku}' AND lof_no='{$lof_no}'  ";
        return $this->db->get_row($sql);
    }

    function init_log($record_detail, $occupy_type = 0) {

        $this->lof_log[$occupy_type] = new InvLogModel();
        $log_info = array('relation_code' => $record_detail['record_code'], 'relation_type' => 'oms', 'occupy_type' => $occupy_type,'record_time'=>$this->record_time);
        
        
        
        $this->lof_log[$occupy_type]->init($log_info, array($record_detail));
    }

    function save_log($occupy_type) {
        $this->lof_log[$occupy_type]->save_log();
    }

}
