<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CarryAjustModel
 *
 * @author wq
 */
require_model('sys/carry/CarryBaseModel');

class CarryAdjustModel extends CarryBaseModel {

    private $store_data = array();
    private $adjust_record = array();

    function exec(&$param) {
        $is_init = $this->init_action($param);
        if ($is_init === false) {
            return $this->format_ret(1);
        }

        $this->get_store();
        $this->create_adjust();
        $this->update_status(5);
        $this->check_task('adjust');
        return $this->format_ret(1);
    }

    function get_store() {

        $sql = "select DISTINCT store_code from oms_sell_record_lof where order_date<='{$this->end_date}' AND (occupy_type=2 OR occupy_type=3)";
        $data = $this->db->get_all($sql);
        foreach ($data as $val) {
            $this->store_data[] = $val['store_code'];
        }
    }

    function create_adjust() {
        foreach ($this->store_data as $store_code) {
            $this->create_adjust_by_store($store_code);
            $this->create_adjust_detail($store_code, $this->adjust_record[$store_code]['id'], $this->adjust_record[$store_code]['record_code']);
            $this->set_ajust_accept($this->adjust_record[$store_code]['record_code']);
        }
    }

    function create_adjust_by_store($store_code) {

        $stock_adjust['record_code'] = load_model('stm/StockAdjustRecordModel')->create_fast_bill_sn();
        //   $stock_adjust['relation_code'] = $record_code;
        $stock_adjust['init_code'] = '';
        $stock_adjust['store_code'] = $store_code;
        $stock_adjust['record_time'] = $this->end_date;
        $stock_adjust['adjust_type'] = 800;
        $stock_adjust['remark'] = '单据结转创建';
        $ret = load_model('stm/StockAdjustRecordModel')->insert($stock_adjust);
        if ($ret['status'] < 1) {
            return $ret;
        }
        $this->adjust_record[$store_code] = array('id' => $ret['data'], 'record_code' => $stock_adjust['record_code']);
    }

    function create_adjust_detail($store_code, $id, $record_code) {

        $sql = "insert into b2b_lof_datail (goods_code,spec1_code,spec2_code,store_code,sku,lof_no,production_date,num,order_type,order_code,pid,order_date)";
        $sql_2 = " select  goods_code,spec1_code,spec2_code,store_code,sku,lof_no,production_date,sum(if(occupy_type=2,-num,num)) as num,
                    'adjust' as order_type,'{$record_code}' as order_code,{$id} as pid,'{$this->end_date}' as order_date
                    from oms_sell_record_lof 
                    where store_code='{$store_code}' AND (occupy_type=2 or occupy_type=3)  AND order_date<='{$this->end_date}'   GROUP BY sku,lof_no";
        $this->db->query($sql . $sql_2);
//
//        $sql_3 = " select  goods_code,spec1_code,spec2_code,store_code,sku,lof_no,production_date,sum(num) as num,
//                    'adjust' as order_type,'{$record_code}' as order_code,{$id} as pid,'{$this->end_date}' as order_date
//                    from oms_sell_record_lof 
//                    where store_code='{$store_code}' AND occupy_type=3 AND order_date<='{$this->end_date}'   GROUP BY sku,lof_no";
//        $this->db->query($sql . $sql_3);


        $sql_detail = "insert into  stm_stock_adjust_record_detail(pid,record_code,goods_code,spec1_code,spec2_code,sku,num,refer_price,price,rebate,money)";

        $sql_detail .= " select b.pid,b.order_code,s.goods_code,s.spec1_code,s.spec2_code,s.sku,sum(b.num),g.sell_price,g.sell_price,1 as rebate,g.sell_price*sum(b.num) as money from b2b_lof_datail b
        inner join  goods_sku s ON  s.sku=b.sku
        inner join  base_goods g ON  g.goods_code=s.goods_code
        where  b.order_code='{$record_code}'
        group by b.sku";
        //   echo $sql_detail;die;
        $this->db->query($sql_detail);
        load_model('stm/StmStockAdjustRecordDetailModel')->mainWriteBack($id);
        return $this->format_ret(1);
    }

    function set_ajust_accept($record_code) {

        $data['is_check_and_accept'] = 1;
        $where = " record_code='{$record_code}' ";
        $this->db->update('stm_stock_adjust_record', $data, $where);

        $lof_data['occupy_type'] = 3;
        $where = " order_code='{$record_code}' AND  order_type='adjust' ";
        $this->db->update('b2b_lof_datail', $lof_data, $where);
    }

}
