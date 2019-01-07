<?php

require_model('tb/TbModel');
require_lib('comm_util');

/**
 * 数据看板
 * @author WMH
 */
class OmsCensusBoardModel extends TbModel {

    private $start_time;
    private $end_time;
    private $sales_target;
    private $comb_num = 9;
    private $goods_rank_num = 8;

    function _init_param() {
        $sys_params = load_model('sys/SysParamsModel')->get_val_by_code(['census_board_target', 'census_board_time_start','census_board_time_end']);
        $this->start_time = empty($sys_params['census_board_time_start']) ? date('Y') . '-11-11 00:00:00' : $sys_params['census_board_time_start'];
        $this->end_time = empty($sys_params['census_board_time_end']) ? date('Y-m-d H:i:s') : $sys_params['census_board_time_end'];
        $this->sales_target = empty($sys_params['census_board_target']) ? 0 : $sys_params['census_board_target'];
    }

    public function get_board_data() {
        $this->_init_param();

        $board_data = [];
        //数据更新时间
        $board_data['update_time'] = date('Y-m-d H:i:s');
        //获取柱状图数据
        $board_data['chart_data'] = $this->get_channel_data();
        //获取已付总金额
        $paid_money = array_map(function($val) {
            return $val[1];
        }, $board_data['chart_data']);
        $board_data['pay_money'] = round(array_sum($paid_money));
        $board_data['pay_money'] = $board_data['pay_money'] == 0 ? '00' : $board_data['pay_money'];
        $board_data['pay_money'] = add_zero($board_data['pay_money'], 9);
        //获取单品排名数据
        $board_data['goods_rank_data'] = $this->get_goods_rank_data();
        //销售目标
        $board_data['suc_money'] = $this->sales_target;
        //计算目标达成率
        $board_data['suc_ratio'] = round($board_data['pay_money'] / $board_data['suc_money'], 4) * 100 . ' %';

        return $board_data;
    }

    /**
     * 获取平台销售数据
     * @return array
     */
    private function get_channel_data() {
        $sql = 'SELECT source,SUM(order_money) AS order_money FROM api_order WHERE (status=1 OR (status=0 AND is_change=1)) AND order_first_insert_time>=:start_time AND order_first_insert_time<=:end_time GROUP BY source ORDER BY order_money DESC';
        $data = $this->db->get_all($sql, [':start_time' => $this->start_time, ':end_time' => $this->end_time]);

        if (!empty($data)) {
            //匹配平台名称
            $sql_values = [];
            $channle_code_arr = array_column($data, 'source');
            $channle_code_str = $this->arr_to_in_sql_value($channle_code_arr, 'source', $sql_values);
            $sql = "SELECT sale_channel_code,sale_channel_name FROM base_sale_channel WHERE sale_channel_code IN({$channle_code_str})";
            $channle_arr = $this->db->get_all($sql, $sql_values);
            $channle_arr = array_column($channle_arr, 'sale_channel_name', 'sale_channel_code');
        }
        //排序
        /*
          $sort_money = array_column($data, 'order_money');
          $sort_num = array_column($data, 'record_num');
          array_multisort($sort_money, SORT_DESC, SORT_NUMERIC, $sort_num, SORT_DESC, SORT_NUMERIC, $data);
         */

        $i = 1;
        $chart_data = [];
        foreach ($data as $row) {
            $row['order_money'] = round($row['order_money']);
            if ($i > $this->comb_num) {
                if (isset($chart_data['other'])) {
                    $chart_data['other'][2] += $row['order_money'];
                } else {
                    $chart_data['other'] = [
                        '其他', $row['order_money']
                    ];
                }
                $i ++;
                continue;
            }
            $chart_data[$row['source']] = [
                $channle_arr[$row['source']], $row['order_money']
            ];

            $i ++;
        }
        $chart_data = array_values($chart_data);
        return $chart_data;
    }

    /**
     * 获取单品销售额前十商品
     * @return array
     */
    private function get_goods_rank_data() {
        $sql = 'SELECT gs.goods_code,od.goods_barcode,SUM(od.avg_money) AS goods_money FROM api_order AS rr INNER JOIN api_order_detail AS od ON rr.tid=od.tid INNER JOIN goods_sku AS gs ON od.goods_barcode=gs.barcode WHERE (rr.status=1 OR (rr.status=0 AND rr.is_change=1)) AND rr.order_first_insert_time>=:start_time GROUP BY gs.goods_code ORDER BY goods_money DESC LIMIT ' . $this->goods_rank_num;
        $data = $this->db->get_all($sql, [':start_time' => $this->start_time]);
        if (!empty($data)) {
            //匹配商品名称
            $sql_values = [];
            $barcode_arr = array_column($data, 'goods_code');
            $barcode_str = $this->arr_to_in_sql_value($barcode_arr, 'goods_code', $sql_values);
            $sql = "SELECT goods_code,goods_name FROM base_goods WHERE goods_code IN({$barcode_str})";
            $goods_arr = $this->db->get_all($sql, $sql_values);
            $goods_arr = array_column($goods_arr, 'goods_name', 'goods_code');
            foreach ($data as &$row) {
                $row['goods_name'] = empty($goods_arr[$row['goods_code']]) ? $row['goods_barcode'] : $goods_arr[$row['goods_code']];
                $row['goods_money'] = round($row['goods_money']);
            }
        }

        return $data;
    }

}
