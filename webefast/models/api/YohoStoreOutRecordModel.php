<?php

require_model('tb/TbModel');

class YohoStoreOutRecordModel extends TbModel {

    protected $table = "api_youhuo_store_out_record";

    function get_notice_record_by_purchase($purchase_no) {
        $sql = "select b.*,a.delivery_no from api_youhuo_store_out_record as a,wbm_notice_record as b where a.notice_record_code=b.record_code and a.purchase_no='{$purchase_no}'";
        $ret = $this->db->get_all($sql);
        foreach ($ret as &$value) {
            $value['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $value['store_code']));
        }
        return $ret;
    }


    function get_by_out_record_no($record_code) {
        $sql = "select  r.* from  api_youhuo_store_out_record r INNER JOIN api_youhuo_purchase_record p ON r.purchase_no=p.purchase_no
                where r.notice_record_code =:record_code  ";
        $sql_values = array(':record_code' => $record_code);
        $data = $this->db->get_all($sql, $sql_values);
        return $this->format_ret(1, $data);
    }


    //维护配送单号 配送方式
    function update_express_code($store_record_code) {
        $sql = "update api_youhuo_store_out_record a ,wbm_store_out_record b set a.express_no=b.express,a.express_code=b.express_code where a.store_out_record_code=b.record_code and b.record_code='{$store_record_code}'";
        $ret = $this->db->query($sql);
        if ($ret != true) {
            return $this->format_ret('-1', '', '更新失败！');
        }
        $sql = "update api_youhuo_deliver a ,api_youhuo_store_out_record b set a.express_no=b.express_no,a.express_code=b.express_code where a.delivery_no=b.delivery_no and b.store_out_record_code='{$store_record_code}'";
        $ret = $this->db->query($sql);
        if ($ret != true) {
            return $this->format_ret('-1', '', '更新失败！');
        }
        return $this->format_ret('1', '', '更新成功！');
    }

}
