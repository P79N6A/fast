<?php

require_model('tb/TbModel');

class WeipinhuijitStoreOutRecordModel extends TbModel {

    protected $table = "api_weipinhuijit_store_out_record";

    function get_by_page($filter) {
        $sql_main = "FROM {$this->table} as a ,wbm_store_out_record as b WHERE a.store_out_record_no=b.record_code ";

        $sql_values = array();
        //商店权限1
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('a.shop_code', $filter_shop_code);

        //批发销货单单号
        if (isset($filter['store_out_record_no']) && $filter['store_out_record_no'] != '') {
            $sql_main .= " AND a.store_out_record_no = :store_out_record_no ";
            $sql_values[':store_out_record_no'] = $filter['store_out_record_no'];
        }
        //出库单
        if (isset($filter['delivery_no']) && $filter['delivery_no'] != '') {
            $sql_main .= " AND a.delivery_no = :delivery_no ";
            $sql_values[':delivery_no'] = $filter['delivery_no'];
        }

        //送货仓库
        if (isset($filter['warehouse']) && $filter['warehouse'] != '') {
            $sql_main .= " AND a.warehouse = :warehouse ";
            $sql_values[':warehouse'] = $filter['warehouse'];
        }

        //店铺
        if (isset($filter['shop_code']) && $filter['shop_code'] <> '') {
            $arr = explode(',', $filter['shop_code']);
            $str = $this->arr_to_in_sql_value($arr, 'shop_code', $sql_values);
            $sql_main .= " AND a.shop_code in ({$str}) ";
        }

        //档期号
        if (isset($filter['po_no']) && $filter['po_no'] != '') {
            $sql_main .= " AND po_no = :po_no ";
            $sql_values[':po_no'] = $filter['po_no'];
        }
        //是否出库
        if (isset($filter['have_delivery']) && $filter['have_delivery'] != '') {
            if ($filter['have_delivery'] == 1) {
                $sql_main .= " AND a.delivery_id != '' ";
            } else {
                $sql_main .= " AND (a.delivery_id = 0 or a.delivery_id is null) ";
            }
        }
        //销货单状态（是否验收）
        if (isset($filter['is_store_out']) && $filter['is_store_out'] != '') {
            $sql_main .= " AND b.is_store_out = :is_store_out ";
            $sql_values[':is_store_out'] = $filter['is_store_out'];
        }


        $select = 'a.shop_code,a.pick_no,a.po_no,a.warehouse,b.*';
        $sql_main .= " order by order_time desc ";
        //echo $sql_main;
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        $warehouse_arr = load_model('api/WeipinhuijitPickModel')->weipinhui_warehouse(0);
        foreach ($data['data'] as &$row) {
            $row['warehouse_name'] = $warehouse_arr[$row['warehouse']]['name'];
        }
        filter_fk_name($data['data'], array('shop_code|shop',));
        $ret_data = $data;
        //print_r($data);die;
        return $this->format_ret(1, $ret_data);
    }

    function get_out_record_by_pick_no($pick_no) {
        $sql = "select b.*,a.delivery_no,a.delivery_id from api_weipinhuijit_store_out_record as a,wbm_store_out_record as b where a.store_out_record_no=b.record_code and a.pick_no='{$pick_no}'";
        $ret = $this->db->get_all($sql);
        return $ret;
    }

    //根据销货单号查询
    function get_by_out_record_no($record_no) {
        //return  $this->get_all(array('store_out_record_no'=>$record_no));
        $sql = "select  r.* from  api_weipinhuijit_store_out_record r
                INNER JOIN api_weipinhuijit_pick p ON r.pick_no=p.pick_no
                where r.store_out_record_no =:record_no  ORDER BY  p.insert_time  ";
        $sql_values = array(':record_no' => $record_no);

        $data = $this->db->get_all($sql, $sql_values);
        return $this->format_ret(1, $data);
    }

    function insert($record) {
        return parent::insert($record);
    }

    //根据拣货单号
    function get_by_pick_no($pick_no) {
        $data = $this->get_all(array('pick_no' => $pick_no));
        return $data;
    }

    //维护配送单号 配送方式
    function update_express_code($store_record_code) {
        $sql = "update api_weipinhuijit_store_out_record a ,wbm_store_out_record b set a.express=b.express,a.express_code=b.express_code where a.store_out_record_no=b.record_code and b.record_code='{$store_record_code}'";
        $this->db->query($sql);
        $sql = "update api_weipinhuijit_delivery a ,api_weipinhuijit_store_out_record b,api_weipinhuijit_store_out_record set a.express=b.express,a.express_code=b.express_code where a.delivery_id=b.delivery_id and b.store_out_record_no='{$store_record_code}'";
        $this->db->query($sql);
    }

}
