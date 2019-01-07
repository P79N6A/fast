<?php

/**
 * 在途库存操作
 *
 * @author wq
 *
 * @example
 * 
 * //确认采购订单加在途
 * load_model('prm/InvOpRoadModel')->set_road_inv('采购订单号',1);
 *  //取消采购订单确认，减在途
 * load_model('prm/InvOpRoadModel')->set_road_inv('采购订单号',-1);
 *  *
 *  //取消采购订单确认，强制完成
 * load_model('prm/InvOpRoadModel')->set_road_inv('采购订单号',0);
 * 
 *  //采购单入库，减在途
 * load_model('prm/InvOpRoadModel')->update_road_inv('采购入库单单号');
 * 
 */
require_model('tb/TbModel');

class InvOpRoadModel extends TbModel {

    function get_table() {
        return 'goods_inv';
    }

    /*
     * 采购订单增加在途库存
     * @$type 1 增加， -1全部取消，0强制完成
     */

    function set_road_inv($record_code, $type = 1) {
//        $ret = $this->get_record($record_code,$type);
//        if(empty($ret)){
//            return $this->format_ret(-1,'','没找到对应单据数据');
//        }
//        $record_data = &$ret['data'];
//        
//       return $this->save_road_data($record_data['detail']);

        $row = $this->get_record_data($record_code);
        return $this->inv_maintain_road($row['store_code']);
    }

    /*
     * 更新在途库存
     */

    function update_road_inv($purchaser_record_code) {

//        $ret = $this->get_purchaser_detail_data($purchaser_record_code);
//        if ($ret['status'] > 0 && !empty($ret['data'])) {
//
//            return $this->save_road_data($ret['data']);
//        }
//        return $ret;
        $row = $this->get_purchaser_record($purchaser_record_code);
        return $this->inv_maintain_road($row['store_code']);
    }

    function get_purchaser_detail_data($purchaser_record_code) {
        $row = $this->get_purchaser_record($purchaser_record_code);
        if (empty($row)) {
            return $this->format_ret(-1, '', '没找到对应单据');
        }

        $relation_code = $row['relation_code'];
        $detail = array();
        if ($this->is_road_record($relation_code)) {
            $store_code = $row['store_code'];
            $detail = $this->get_purchaser_record_detail($purchaser_record_code, $store_code);
        }
        return $this->format_ret(1, $detail);
    }

    private function save_road_data($data) {
        $update_str = " road_num = VALUES(road_num)"; //,record_time='{$this->sys_record_time}'
        $tb_inv = "goods_inv";
        $ret = $this->insert_multi_duplicate($tb_inv, $data, $update_str);
        return $this->format_ret(1);
    }

    function is_road_record($relation_code) {
        $sql = "select 1 from pur_planned_record where record_code in(
                    select relation_code from  pur_order_record where  record_code = :record_code)
                ";
        $sql_values = array(':record_code' => $relation_code);
        $num = $this->db->get_value($sql, $sql_values);
        return ($num > 0) ? TRUE : FALSE;
    }

    function get_purchaser_record($purchaser_record_code) {

        $sql = "select * from pur_purchaser_record where record_code=:record_code";
        $sql_values = array(':record_code' => $purchaser_record_code);
        return $this->db->get_row($sql, $sql_values);
    }

    function get_purchaser_record_detail($purchaser_record_code, $store_code) {

        $sql = "select goods_code,spec1_code,spec2_code,sku, if(num>notice_num,-notice_num,-num) as road_num, '{$store_code}' as store_code 
                    from pur_purchaser_record_detail where record_code=:record_code";
        $sql_values = array(':record_code' => $purchaser_record_code);
        return $this->db->get_all($sql, $sql_values);
    }

    function get_record_data($record_code) {
        $sql = "select * from pur_planned_record where record_code=:record_code";
        $sql_values = array(':record_code' => $record_code);
        return $this->db->get_row($sql, $sql_values);
    }

    function get_record($record_code, $type) {
        $data = array();
        $row = $this->get_record_data($record_code);
        if (!empty($row)) {
            if ($type == -1) {
                $sql = "select goods_code,spec1_code,spec2_code,sku,-num as road_num,'{$row['store_code']}' as store_code";
            } else if ($type == 0) {
                $sql = "select goods_code,spec1_code,spec2_code,sku,finish_num-num as road_num ,'{$row['store_code']}'as store_code";
            } else if ($type == 1) {
                $sql = "select goods_code,spec1_code,spec2_code,sku,num as road_num,'{$row['store_code']}' as store_code";
            }


            $data['record'] = &$row;

            $sql .=" from pur_planned_record_detail where record_code=:record_code";
            $sql_values = array(':record_code' => $row['record_code']);
            $detail = $this->db->get_all($sql, $sql_values);
            $data['detail'] = &$detail;
        }
        return $this->format_ret(1, $data);
    }

    //维护在途库存
    function inv_maintain_road($store_code) {
        $this->begin_trans();
        $sql = "update goods_inv set road_num = 0 where store_code='{$store_code}'";
        $this->db->query($sql);

        $sql = " insert into goods_inv (store_code,goods_code,spec1_code,spec2_code,sku,road_num)
              select r.store_code,d.goods_code,d.spec1_code,d.spec2_code,d.sku, if((d.num-d.finish_num)<0,0,d.num-d.finish_num) as num from pur_planned_record r
                          INNER JOIN pur_planned_record_detail d  ON r.record_code=d.record_code
                    where r.is_check=1 AND r.is_finish<>1 AND r.store_code='{$store_code}'
                    ON DUPLICATE KEY UPDATE road_num = road_num +VALUES(road_num);      ";

        $this->db->query($sql);
        $this->commit();
        return $this->format_ret(1);
    }

}
