<?php

require_model('wms/WmsRecordModel');

class WmsGoodsDiyRecordModel extends WmsRecordModel {

    function __construct() {
        parent::__construct();
    }

    //todo:完善单据增加和取消
    function order_shipping($record_code, $record_time, $order_mx) {
        $sql = "SELECT * FROM stm_goods_diy_record WHERE record_code = :record_code";
        $diy_record = ctx()->db->get_row($sql, array(':record_code' => $record_code));
        if (empty($diy_record)) {
            return $this->format_ret(-1, '', $record_code . '单据不存在');
        }
        if ($diy_record['is_check'] == 0) {
            return $this->format_ret(-1, '', $record_code . '单据未审核');
        }

        if ($diy_record['is_sure'] > 0) {
            return $this->format_ret(-1, '', $record_code . '单据已完成');
        }
        $record_type = $diy_record['record_type'] == 1 ? 'stm_split' : 'stm_diy';
        $ret_check = $this->check_detail($record_code, $record_type);
        if ($ret_check['status'] < 1) {
            return $ret_check;
        }

        $ret = load_model('stm/StmGoodsDiyRecordModel')->wms_create_adjust_by_diy($diy_record, $order_mx);

        return $ret;
    }

    function check_detail($record_code, $record_type) {
        $sql = "select * from wms_b2b_order where record_code=:record_code AND  record_type=:record_type ";
        $sql_values = array(
            ':record_code' => $record_code,
            ':record_type' => $record_type,
        );
        $data = $this->db->get_all($sql, $sql_values);
        if (empty($data)) {
            return $this->format_ret(-1, '', '回传明细异常为空');
        }

        return $this->format_ret(1);
    }

    function get_record_info($record_code) {
        $sql = "select * from stm_goods_diy_record where record_code  = :record_code";
        $info = ctx()->db->get_row($sql, array(':record_code' => $record_code));
        if (empty($info)) {
            return $this->format_ret(-1, '', '找不到商品组装单');
        }
        $record_type = $info['record_type'];
        $sql = "select sku,num from stm_goods_diy_record_detail where record_code  = :record_code";
        $goods = ctx()->db->get_all($sql, array(':record_code' => $record_code));
        if (empty($goods)) {
            return $this->format_ret(-1, '', '找不到商品组装单明细');
        }

        $child = array();
        $p_sku_num = array_column($goods, 'num', 'sku');
        $sku_str = deal_array_with_quote(array_column($goods, 'sku'));
        $sql = "SELECT gs.sku,gd.num,gd.p_sku FROM goods_diy AS gd INNER JOIN goods_sku AS gs ON gd.sku=gs.sku WHERE gd.p_sku IN({$sku_str})";
        $child_temp = ctx()->db->get_all($sql);
        foreach ($child_temp as $c) {
            $c_num = $c['num'] * $p_sku_num[$c['p_sku']];
            $c['num'] = 0 - $c_num;
            $c['is_child'] = 1;
            $sku = $c['sku'];
            if (isset($child[$sku])) {
                $child[$sku]['num'] += $c['num'];
            } else {
                $child[$sku] = $c;
            }
        }

        foreach ($goods as &$g) {
            $g['num'] = $g['num'];
            $g['is_child'] = 0;
        }

        $info['goods'] = array_merge($goods, $child);

        $ret = $this->append_mx_barcode_by_sku($info['goods'], 1, 'is_child');
        if ($ret['status'] < 0) {
            return $ret;
        }
        $info['goods'] = $ret['data'];
        return $this->format_ret(1, $info);
    }

}
