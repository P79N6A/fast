<?php

require_model('tb/TbModel');

/**
 * 金蝶日报明细业务
 */
class KisdeeDetailModel extends TbModel {

    private $detail_table = array(
        'sell_record' => 'kisdee_trade_record_detail',
        'sell_return' => 'kisdee_trade_return_detail',
    );

    /**
     * 销售日报查询
     * @param array $filter 过滤条件
     * @return array 查询结果集
     */
    function get_sell_daily_detail($filter) {
        $ret = load_model('kis/KisdeeModel')->check_params($filter, array('record_code' => '单据编号', 'record_type' => '单据类型'));
        if ($ret['status'] != 1) {
            return $ret;
        }
        $detail_table = $this->detail_table[$filter['record_type']];
        $sql_main = "FROM {$detail_table} AS rd
                    INNER JOIN base_goods AS bg ON rd.goods_code=bg.goods_code
                    INNER JOIN goods_sku AS gs ON rd.sku=gs.sku
                    WHERE 1 ";
        $select = 'rd.detail_no,rd.goods_code,bg.goods_name,gs.barcode,gs.spec1_name,gs.spec2_name,rd.num,rd.money';
        $sql_values = array();

        $sql_main .= " AND rd.record_code = :record_code ";
        $sql_values[':record_code'] = $filter['record_code'];

        if (isset($filter['goods_search']) && $filter['goods_search'] <> '') {
            $sql_main .= " AND (rd.goods_code LIKE :goods_search OR bg.goods_name LIKE :goods_search OR gs.barcode LIKE :goods_search) ";
            $filter['goods_search'] = trim($filter['goods_search']);
            $sql_values[':goods_search'] = "%{$filter['goods_search']}%";
        }

        $sql_main .= " ORDER BY detail_no ";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$row) {
            $row['spec'] = "颜色：{$row['spec1_name']}；尺码：{$row['spec2_name']}";
        }

        return $this->format_ret(1, $data);
    }

    function get_detail($params) {
        $detail_table = $this->detail_table[$params['record_type']];
        $sql = "SELECT td.detail_no,td.num,td.money,gs.barcode FROM {$detail_table} AS td
            INNER JOIN goods_sku AS gs ON td.sku=gs.sku
            WHERE td.record_code=:record_code ORDER BY td.detail_no";
        return $this->db->get_all($sql, array(':record_code' => $params['record_code']));
    }

}
