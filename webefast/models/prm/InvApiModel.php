<?php

require_model('tb/TbModel');
require_lib('comm_util');
require_lang('prm');

/**
 * 库存开放接口
 * @author WMH
 */
class InvApiModel extends TbModel {

    /**
     * 获取商品实物库存
     * @author wmh
     * @date 2017-05-10
     * @modify 2017-06-30 wmh 增加入参：store_code
     * @param array $params 接口参数
     * @return array 操作结果
     */
    public function api_goods_inv_get($params) {
        $key_option = array(
            's' => array('start_time', 'end_time', 'barcode', 'store_code'),
            'i' => array('page', 'page_size')
        );
        $r_option = array();
        $ret_option = valid_assign_array($params, $key_option, $r_option);

        $select = "gi.goods_code,gi.sku,gi.lock_num,gi.out_num,gi.road_num,gi.safe_num,gs.barcode,SUM(gi.stock_num) AS num";
        $sql = 'FROM goods_inv AS gi INNER JOIN goods_sku AS gs ON gi.sku=gs.sku WHERE 1=1';
        $sql_values = array();
        if (!empty($r_option['barcode'])) {
//            $sku_data = load_model('prm/SkuModel')->convert_barcode($r_option['barcode']);
//            $sku_data = $sku_data['data'];
//            if (empty($sku_data)) {
//                return $this->format_ret(-10002, array('barcode' => $r_option['barcode']), '商品条形码不存在');
//            }
            $sql .= ' AND gs.barcode=:barcode';
            $sql_values[':barcode'] = $r_option['barcode'];
        }
        if (!empty($r_option['store_code'])) {
            $sql .= ' AND gi.store_code=:store_code';
            $sql_values[':store_code'] = $r_option['store_code'];
        }
        if (!empty($r_option['page'])) {
            if (!check_value_valid($r_option['page'], 'pint')) {
                return $this->format_ret(-10005, array('page' => $r_option['page']), '页码必须为正整数');
            }
        } else {
            $r_option['page'] = 1;
        }
        if (!empty($r_option['page_size'])) {
            if (!check_value_valid($r_option['page_size'], 'pint')) {
                return $this->format_ret(-10005, array('page_size' => $r_option['page_size']), '页数必须为正整数');
            }
            if ($r_option['page_size'] > 100) {
                return $this->format_ret(-10005, array('page_size' => $r_option['page_size']), '每页最多100条');
            }
        } else {
            $r_option['page_size'] = 100;
        }
        if (!empty($r_option['start_time'])) {
            $start_time = strtotime($r_option['start_time']);
            if ($start_time === FALSE) {
                return $this->format_ret(-10005, array('start_time' => $r_option['start_time']), '更新开始时间格式错误');
            }
            $sql .= ' AND gi.lastchanged>=:start_time';
            $sql_values[':start_time'] = date('Y-m-d H:i:s', $start_time);
        }
        if (!empty($r_option['end_time'])) {
            $end_time = strtotime($r_option['end_time']);
            if ($end_time === FALSE) {
                return $this->format_ret(-10005, array('end_time' => $r_option['end_time']), '更新结束时间格式错误');
            }
            $sql .= ' AND gi.lastchanged<=:end_time';
            $sql_values[':end_time'] = date('Y-m-d H:i:s', $end_time);
        }
        
        $sql .= ' GROUP BY gi.sku';
        $data = $this->get_page_from_sql($r_option, $sql, $sql_values, $select,true);
        $temp_data = $data['data'];
        if (empty($temp_data)) {
            return $this->format_ret(-10002, (object) array(), '数据不存在');
        }
        $sql_values = array();
        $sku_arr = array_column($temp_data, 'sku');
        $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
        $sql = "SELECT gs.sku,gs.store_code,gs.shelf_code,bs.shelf_name FROM goods_shelf AS gs,base_shelf AS bs WHERE bs.shelf_code=gs.shelf_code AND bs.store_code=gs.store_code AND gs.sku IN({$sku_str})";
        if (!empty($r_option['store_code'])) {
            $sql .= ' AND gs.store_code=:store_code';
            $sql_values[':store_code'] = $r_option['store_code'];
        }
        $sql .= ' GROUP BY gs.sku,gs.store_code,gs.shelf_code';
        $shelf = $this->db->get_all($sql, $sql_values);
        $shelf_data = array();
        foreach ($shelf as $row) {
            $sku = $row['sku'];
            $store_code = $row['store_code'];
            $shelf_data[$sku][$store_code]['store_code'] = $store_code;
            $shelf_data[$sku][$store_code]['shelf'][] = array('shelf_code' => $row['shelf_code'], 'shelf_name' => $row['shelf_name']);
        }
        unset($shelf);

        foreach ($temp_data as &$row) {
            $sku = $row['sku'];
            $shelf_list = array();
            if (isset($shelf_data[$sku])) {
                $shelf_list = array_values($shelf_data[$sku]);
            }

            $row['shelf_list'] = $shelf_list;
        }

        $filter = get_array_vars($data['filter'], array('page', 'page_size', 'page_count', 'record_count'));

        $revert_data = array(
            'filter' => $filter,
            'data' => $temp_data,
        );
        return $this->format_ret(1, $revert_data);
    }

    /**
     * 批量更新商品库存
     * @author wmh
     * @date 2017-05-10
     * @param array $params 接口参数
     * <pre> 必选 'barcode',store_code',stock_num'
     * <pre> 可选 'lof_no','production_date','lof_price'
     * @return array 操作结果
     */
    public function api_goods_inv_batch_update($params) {
        if (empty($params['detail'])) {
            return $this->format_ret(-10001, array('detail'), 'API_PRM_MESSAGE_10001');
        }
        $detail = json_decode($params['detail'], TRUE);
        if (!is_array($detail) || empty($detail)) {
            return $this->format_ret(-10005, (object) array(), '数据格式有误');
        }

        $return_data = array();
        foreach ($detail as $row) {
            $return_data[] = load_model('prm/InvModel')->api_goods_inv_update($row);
        }

        return $this->format_ret(1, $return_data);
    }

}
