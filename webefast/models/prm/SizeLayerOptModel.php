<?php

require_model('tb/TbModel');

/**
 * 尺码层数据操作
 * @author WMH
 */
class SizeLayerOptModel extends TbModel {

    public function add_detail($param) {
        $data = array_column($param['data'], 'num', 'sku');
        $sku_arr = array_keys($data);
        $sql_values = [];
        $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
        $sql = "SELECT gs.goods_code,gs.sku,gs.barcode,gs.spec1_code,gs.spec2_code,bg.trade_price,bg.purchase_price,bg.sell_price,gs.price,bg.cost_price,gs.cost_price AS cost_price_sku FROM goods_sku gs INNER JOIN base_goods bg ON gs.goods_code=bg.goods_code WHERE gs.sku IN({$sku_str})";
        $detail = $this->db->get_all($sql, $sql_values);
        foreach ($detail as &$row) {
            $row['num'] = $data[$row['sku']];
            $row['sell_price'] = empty($row['price']) ? $row['sell_price'] : $row['price'];
            $row['cost_price'] = empty($row['cost_price_sku']) ? $row['cost_price'] : $row['cost_price_sku'];
            unset($row['price'], $row['cost_price_sku']);
        }

        $record_type = $param['model'];
        $folder = substr($record_type, 0, 3);
        if (strpos($record_type, 'wbm_') === 0 || strpos($record_type, 'pur_') === 0 || strpos($record_type, 'stm_') === 0) {
            $model = implode('', array_map('ucfirst', explode('_', substr($record_type, 4)))) . 'RecordModel';
        }
        $param = [
            'record_id' => $param['record_id'],
            'store_code' => $param['store_code'],
            'detail' => $detail
        ];

        $ret = load_model($folder . '/' . $model)->add_detail($param);

        return $ret;
    }

}
