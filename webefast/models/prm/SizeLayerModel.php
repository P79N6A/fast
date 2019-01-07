<?php

require_model('tb/TbModel');

/**
 * 尺码层
 * @author WMH
 */
class SizeLayerModel extends TbModel {

    public function update_layer($param) {
        //校验尺码数据
        $data = $param['data'];
        $size_arr = [];
        foreach ($data as &$row) {
            $row = array_map(function($v) {
                return trim($v);
            }, $row);

            $size_arr = array_merge($size_arr, $row);
        }
        $size_arr = array_filter(array_unique($size_arr));
        $size_data = load_model('base/ArchiveSearchModel')->get_single_data('spec2', $size_arr, 'spec2_name');
        $size_diff = array_diff($size_arr, array_values($size_data));
        if (!empty($size_diff)) {
            $size_str = implode(',', $size_diff);
            return $this->format_ret(-1, '', "以下尺码在系统中不存在【{$size_str}】");
        }

        $updata = [
            'param_code' => 'size_layer',
            'data' => json_encode($data)
        ];
        $ret = load_model('sys/ParamsModel')->update_param_value($updata);

        return $ret;
    }

    public function get_layer() {
        $sql = 'SELECT value FROM sys_params WHERE param_code="size_layer"';
        $value = $this->db->get_value($sql);
        if ($value <> 1) {
            return $this->format_ret(-2, '', '未开启尺码层参数');
        }
        if (empty($value)) {
            return $this->format_ret(-3, '', '尺码层未设置');
        }

        $data = load_model('sys/ParamsModel')->get_param_set('size_layer');
        $data = json_decode($data);

        return $this->format_ret(1, $data);
    }

    /**
     * 检查商品属于哪层尺码
     * @param string $goods_code
     * @return array
     */
    public function check_goods_layer($goods_code) {
        $sql = 'SELECT DISTINCT spec2_name FROM goods_sku WHERE goods_code=:goods_code';
        $size = $this->db->get_all_col($sql, [':goods_code' => $goods_code]);

        $size_layer = $this->get_layer();
        if ($size_layer < 1) {
            return $size_layer;
        }
        $i = 0;
        $line = '';
        foreach ($size_layer['data'] as $key => $row) {
            $is_exists = array_intersect($size, $row);
            if (!empty($is_exists)) {
                $line = $key;
                $i ++;
            }
        }

        $status = 1;
        $msg = '';
        if ($i === 0) {
            $status = -1;
            $msg = '该商品不存在尺码层规格';
        } else if ($i > 1) {
            $status = -1;
            $msg = '该商品的尺码在多个尺码层中，请维护该商品的尺码信息';
        }
        return $this->format_ret($status, $line, $msg);
    }

    public function get_goods_info($goods_code, $store_code, $layer_line) {
        $data = load_model('sys/ParamsModel')->get_param_set('size_layer');
        $data = json_decode($data);
        $layer_arr = $data[$layer_line];

        $sql = 'SELECT gs.sku,gs.spec1_name,gs.spec2_name,gi.stock_num,gi.lock_num,MD5(CONCAT(gs.spec1_name,\'_\',gs.spec2_name)) AS k,COUNT(1) spec2_count FROM goods_sku gs LEFT JOIN goods_inv gi ON gs.sku=gi.sku AND gi.store_code=:store_code WHERE gs.goods_code=:goods_code GROUP BY k ORDER BY gi.stock_num DESC';
        $goods_inv = $this->db->get_all($sql, [':store_code' => $store_code, ':goods_code' => $goods_code]);
        $spec1_arr = array_values(array_unique(array_column($goods_inv, 'spec1_name')));
        $goods_inv = load_model('util/ViewUtilModel')->get_map_arr($goods_inv, 'k');

        $inv_arr = [];
        foreach ($spec1_arr as $color) {
            $temp = [];
            foreach ($layer_arr as $size) {
                if (empty($size)) {
                    continue;
                }
                $k = md5($color . '_' . $size);
                $inv_type = 1;
                if (!isset($goods_inv[$k])) {
                    $inv_type = -2;
                    $stock_num = '';
                    $tip = '商品无此规格';
                } else if ($goods_inv[$k]['spec2_count'] > 1) {
                    $inv_type = -3;
                    $stock_num = '';
                    $tip = '该商品存在重复的规格名称';
                } else {
                    if (!isset($goods_inv[$k]['stock_num'])) {
                        $inv_type = -1;
                        $stock_num = '';
                        $tip = '无库存记录';
                    } else {
                        $stock_num = $goods_inv[$k]['stock_num'] - $goods_inv[$k]['lock_num'];
                        $tip = '可用库存：' . $stock_num;
                    }
                }

                $temp[] = [
                    'sku' => $goods_inv[$k]['sku'],
                    'inv_type' => $inv_type,
                    'stock_num' => $stock_num,
                    'k' => $k,
                    'tip' => $tip
                ];
            }
            $inv_arr[] = $temp;
        }
        $ret_store = load_model('base/StoreModel')->get_by_code($store_code);
        $allow_negative_inv = isset($ret_store['data']['allow_negative_inv']) ? $ret_store['data']['allow_negative_inv'] : 0;

        $data = [
            'layer' => json_encode($layer_arr),
            'spec1' => json_encode($spec1_arr),
            'goods_inv' => json_encode($inv_arr),
            'negative_inv' => $allow_negative_inv,
            'store' => ['store_name' => $ret_store['data']['store_name']]
        ];
        return $data;
    }

}
