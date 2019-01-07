<?php

require_model('tb/TbModel');

class InvMonthModel extends TbModel {

    private $inv_yj_mx_tbls;

    function __construct() {
        @ini_set('memory_limit', '1024M');
        parent::__construct();
        $this->inv_yj_mx_tbls = $this->db->get_all_col("show TABLES like 'inv_yj_mx_%'"); //获取所有库存月结单明细表
        rsort($this->inv_yj_mx_tbls); //倒序排列
    }

    /**
     * 创建库存月结单明细表，格式示例：inv_yj_mx_2016_04
     * @param string $year 年份
     * @param string $month 月份
     * @return string 返回表名
     */
    function init_inv_month_mx_tbl($year, $month) {
        $cur_tbl = 'inv_yj_mx_' . $year . '_' . $month; //新表名
        //判断表是否已存在
        if (in_array($cur_tbl, $this->inv_yj_mx_tbls)) {
            return $this->format_ret(1, $cur_tbl, $cur_tbl . '已存在');
        }
        $sql = "CREATE TABLE `{$cur_tbl}` (
                    `inv_yj_mx_id` int(11) NOT NULL AUTO_INCREMENT,
                    `store_code` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '仓库代码',
                    `goods_code` varchar(30) DEFAULT '' COMMENT '商品编码',
                    `sku` varchar(30) DEFAULT '' COMMENT 'sku',
                    `begin_num` int(11) DEFAULT '0' COMMENT '期初数',
                    `purchase` int(11) DEFAULT '0' COMMENT '采购进货数',
                    `pur_return` int(11) DEFAULT '0' COMMENT '采购退货数',
                    `oms_sell_record` int(11) DEFAULT '0' COMMENT '网络销售数',
                    `oms_sell_return` int(11) DEFAULT '0' COMMENT '网络退货数',
                    `wbm_store_out` int(11) DEFAULT '0' COMMENT '批发销货单数',
                    `wbm_return` int(11) DEFAULT '0' COMMENT '批发退货数',
                    `adjust` int(11) DEFAULT '0' COMMENT '库存调整数',
                    `shift_in` int(11) DEFAULT '0' COMMENT '移仓出库数',
                    `shift_out` int(11) DEFAULT '0' COMMENT '移仓入库数',
                    `pos_sell` int(11) DEFAULT '0' COMMENT '门店销售数',
                    `pos_sell_return` int(11) DEFAULT '0' COMMENT '门店退货数',
                    `end_num` int(11) DEFAULT '0' COMMENT '期末数',
                    `pur_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '采购金额',
                    `begin_cost` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '期初成本单价(上月期末调整后成本单价)',
                    `end_cost` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '期末成本单价',
                    `end_adjust_cost` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '期末调整后成本单价',
                    `is_change` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '库存变动：0-未变动，1-已变动',
                    `record_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '单据创建时间',
                    `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
                    `remark` varchar(255) DEFAULT '' COMMENT '备注',
                    PRIMARY KEY (`inv_yj_mx_id`),
                    UNIQUE KEY `idxu_ss` (`store_code`,`sku`) USING BTREE,
                    KEY `ix_store_code` (`store_code`) USING BTREE,
                    KEY `ix_goods_code` (`goods_code`) USING BTREE,
                    KEY `ix_sku` (`sku`) USING BTREE,
                    KEY `ix_begin_num` (`begin_num`),
                    KEY `ix_purchase` (`purchase`),
                    KEY `ix_pur_return` (`pur_return`),
                    KEY `ix_oms_sell_record` (`oms_sell_record`),
                    KEY `ix_oms_sell_return` (`oms_sell_return`),
                    KEY `ix_wbm_store_out` (`wbm_store_out`),
                    KEY `ix_wbm_return` (`wbm_return`),
                    KEY `ix_adjust` (`adjust`),
                    KEY `ix_shift_in` (`shift_in`),
                    KEY `ix_shift_out` (`shift_out`),
                    KEY `ix_pos_sell` (`pos_sell`),
                    KEY `ix_pos_sell_return` (`pos_sell_return`),
                    KEY `ix_end_num` (`end_num`),
                    KEY `ix_pur_money` (`pur_money`),
                    KEY `ix_begin_cost` (`begin_cost`),
                    KEY `ix_end_cost` (`end_cost`),
                    KEY `ix_end_adjust_cost` (`end_adjust_cost`),
                    KEY `ix_is_change` (`is_change`),
                    KEY `ix_record_time` (`record_time`) USING BTREE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='库存月结单明细单据';";
        $ret = ctx()->db->query($sql);
        if ($ret != true) {
            return $this->format_ret(-1, '', '新建表' . $cur_tbl . '失败');
        }
        $this->inv_yj_mx_tbls[] = $cur_tbl;
        rsort($this->inv_yj_mx_tbls);
        return $this->format_ret(1, $cur_tbl, '新建表' . $cur_tbl . '成功');
    }

    /**
     * @todo 变更数组键，返回新数组
     */
    private function deal_array($array) {
        $arr = array();
        foreach ($array as $value) {
            $arr[$value['key']] = array_splice($value, 1);
        }
        return $arr;
    }

    /**
     * @todo 获取信息
     * @param array $param
     * @param int $type 0-有库存记录的商品信息;1-采购入库记录;2-历史库存流水;3-本月库存流水
     * @return array 记录
     */
    private function get_info($param, $type) {
        $sql = '';
        switch ($type) {
            case 0:
                $sql = "SELECT CONCAT(gi.store_code,'_',gi.sku) AS `key`,bg.goods_code,gi.store_code,gi.sku,gi.stock_num,bg.cost_price AS begin_cost
                        FROM goods_inv gi
                        LEFT JOIN goods_sku gs ON gi.sku=gs.sku
                        LEFT JOIN base_goods bg ON gi.goods_code=bg.goods_code
                        WHERE gi.store_code in({$param['store_code']}) ";
                break;
            case 1:
                $sql = "SELECT CONCAT(pr.store_code,'_',rd.sku) AS `key`,sum(rd.money) AS pur_money,sum(rd.num) AS pur_num
                        FROM pur_purchaser_record_detail rd
                        LEFT JOIN pur_purchaser_record pr ON (pr.record_code=rd.record_code)
                        WHERE pr.record_time>='{$param['date_start']}' AND pr.record_time<='{$param['date_end']}' AND pr.is_check_and_accept=1
                        AND pr.store_code in({$param['store_code']}) GROUP BY rd.sku,pr.store_code";
                break;
            default :
                $sql_wh = " WHERE occupy_type in (2, 3) AND store_code in({$param['store_code']}) AND order_date>='{$param['date_start']}' ";
                if ($type == 3) {
                    $sql_wh .=" AND order_date<='{$param['date_end']}' ";
                }
                $select = "CONCAT(store_code,'_',sku) AS `key`,sum(num) AS num, occupy_type";
                $sql_group = ' GROUP BY order_type,store_code,sku';
                $sql = "SELECT order_type,{$select} FROM b2b_lof_datail bl" . $sql_wh . $sql_group;
                $sql .= ' UNION ALL ';
                $sql .= "SELECT record_type AS order_type,{$select} FROM oms_sell_record_lof sr" . $sql_wh . $sql_group;
        }
        return $this->db->get_all($sql);
    }

    /*
      public function create_inv_month($ymonth, $store_code) {
      $store_code_arr = explode(',', $store_code);
      $order_date_start = $this->get_cur_month_first_day($ymonth);
      $order_date_end = $this->get_cur_month_last_day($ymonth);
      $param = array('date_start' => $order_date_start, 'date_end' => $order_date_end, 'ymonth' => $ymonth);
      foreach ($store_code_arr as $code) {
      $param['store_code'] = $code;
      $ret = $this->first_create_inv_month($param);
      if ($ret['status'] != 1) {
      return ret;
      }
      }
      } */

    /**
     * 创建库存月结单
     */
    public function first_create_inv_month($ymonth, $store_code) {
        $store_code_str = deal_strs_with_quote($store_code);
        $order_date_start = $this->get_cur_month_first_day($ymonth);
        $order_date_end = $this->get_cur_month_last_day($ymonth);
        $param = array('store_code' => $store_code_str, 'date_start' => $order_date_start, 'date_end' => $order_date_end);
        //获取有库存记录的商品信息
        $inv_detail = $this->get_info($param, 0);
        $inv_detail = $this->deal_array($inv_detail);

        //获取采购入库记录
        $purchase = $this->get_info($param, 1);
        $purchase = $this->deal_array($purchase);

        $order_type_rk = array('purchase', 'oms_sell_return', 'wbm_return', 'adjust', 'shift_in');
        $order_type_ck = array('pur_return', 'oms_sell_record', 'wbm_store_out', 'shift_out');

        //获取历史库存流水
        $stream_info = $this->get_info($param, 2);
        $stream_arr = array();
        foreach ($stream_info as $val) {
            if ($val['order_type'] == 1) {
                $val['order_type'] = 'oms_sell_record';
            }
            if ($val['order_type'] == 2) {
                $val['order_type'] = 'oms_sell_return';
            }
            $k = $val['key'];

            if (in_array($val['order_type'], $order_type_rk)) {
                $stream_arr[$k]['total_num'] += $val['num'];
            }
            if (in_array($val['order_type'], $order_type_ck)) {
                $stream_arr[$k]['total_num'] -= $val['num'];
            }
        }
        unset($stream_info);
        $detail = array_merge_recursive($inv_detail, $stream_arr);

        //计算本月期初库存
        foreach ($detail as $k => $v) {
            $v['stock_num'] = empty($v['stock_num']) ? 0 : $v['stock_num'];
            $v['total_num'] = empty($v['total_num']) ? 0 : $v['total_num'];
            $detail[$k]['begin_num'] = $v['stock_num'] - $v['total_num'];
            unset($detail[$k]['stock_num'], $detail[$k]['total_num']);
        }

        //获取本月库存流水
        $stream_info = $this->get_info($param, 3);
        $stream_arr = array();
        foreach ($stream_info as $val) {
            if ($val['order_type'] == 1) {
                $val['order_type'] = 'oms_sell_record';
            }
            if ($val['order_type'] == 2) {
                $val['order_type'] = 'oms_sell_return';
            }
            $k = $val['key'];
            $stream_arr[$k][$val['order_type']] = (int) $val['num'];

            if (in_array($val['order_type'], $order_type_rk)) {
                $stream_arr[$k]['total_num'] += $val['num'];
            }
            if (in_array($val['order_type'], $order_type_ck)) {
                $stream_arr[$k]['total_num'] -= $val['num'];
            }
        }
        $detail1 = array_merge_recursive($detail, $purchase, $stream_arr);
        $base_detail = array(
            'purchase' => 0,
            'pur_return' => 0,
            'wbm_store_out' => 0,
            'wbm_return' => 0,
            'adjust' => 0,
            'shift_in' => 0,
            'shift_out' => 0,
            'oms_sell_record' => 0,
            'oms_sell_return' => 0,
            'pos_sell_record' => 0,
            'pos_sell_return' => 0,
            'pur_money' => 0.00,
        );
        foreach ($detail1 as $k3 => $v3) {
            $detail1[$k3]['pur_money'] = empty($v3['pur_money']) ? 0 : $v3['pur_money'];
            $detail1[$k3]['end_num'] = $v3['begin_num'] + $v3['total_num'];
            $inv_num = $v3['begin_num'] + $v3['pur_num'];
            $inv_num = $inv_num == 0 ? 1 : $inv_num;
            $detail1[$k3]['end_cost'] = ($v3['begin_cost'] * $v3['begin_num'] + $detail1[$k3]['pur_money']) / $inv_num;
            $detail1[$k3]['end_adjust_cost'] = $detail1[$k3]['end_cost'];
            $detail1[$k3]['record_time'] = date('Y-m-d h:i:s');
            $detail1[$k3] = array_merge($base_detail, $detail1[$k3]);
            unset($detail1[$k3]['total_num'], $detail1[$k3]['pur_num']);
        }

        $date = explode('-', $ymonth);
        $ret = $this->init_inv_month_mx_tbl($date[0], $date[1]); //创建新库存月结单明细表
        if ($ret['status'] < 0) {
            return $this->format_ret(-1, '', $ret['message']);
        }

        $new_detail = array_chunk($detail1, 3000, true);
        foreach ($new_detail as $val) {
            $update_str = "begin_num = VALUES(begin_num),purchase = VALUES(purchase),pur_return = VALUES(pur_return),oms_sell_record = VALUES(oms_sell_record),oms_sell_return = VALUES(oms_sell_return),wbm_store_out = VALUES(wbm_store_out),wbm_return = VALUES(wbm_return),adjust = VALUES(adjust),shift_in = VALUES(shift_in),shift_out = VALUES(shift_out),end_num = VALUES(end_num),pur_money = VALUES(pur_money),begin_cost = VALUES(begin_cost),end_cost = VALUES(end_cost),end_adjust_cost = VALUES(end_adjust_cost)";
            $this->insert_multi_duplicate($ret['data'], $val, $update_str); //批量插入数据
        }
        return $this->format_ret(1, $ret['data']);
    }

    /**
     * 获取指定库存月结单详情表名
     */
    public function get_inv_detail_tbl($ymonth, $type = FALSE) {
        $ymonth_arr = explode('-', $ymonth);
        $detail_tbl = 'inv_yj_mx_' . $ymonth_arr[0] . '_' . $ymonth_arr[1]; //新表名
        //判断表是否已存在
        if (in_array($detail_tbl, $this->inv_yj_mx_tbls)) {
            if ($type == TRUE) {
                $key = array_search($detail_tbl, $this->inv_yj_mx_tbls);
                unset($this->inv_yj_mx_tbls[$key]);
            }
            return $this->format_ret(1, $detail_tbl, '明细表已存在');
        }

        return $this->format_ret(-1, $detail_tbl, '明细表不存在');
    }

    //给定一个日期，获取其本月的第一天和最后一天
    function get_cur_month_first_day($date) {
        return date('Y-m-01', strtotime($date));
    }

    function get_cur_month_last_day($date) {
        return date('Y-m-d', strtotime(date('Y-m-01', strtotime($date)) . ' +1 month -1 day'));
    }

    //给定一个日期，获取其下月的第一天
    function get_next_month_first_day($date) {
        return date('Y-m-d', strtotime(date('Y-m-01', strtotime($date)) . ' +1 month'));
    }

    //给定一个日期，获取其上月的第一天和最后一天
    function get_prev_month_first_day($date) {
        return date('Y-m-d', strtotime(date('Y-m-01', strtotime($date)) . ' -1 month'));
    }

    function get_prev_month_last_day($date) {
        return date('Y-m-d', strtotime(date('Y-m-01', strtotime($date)) . ' -1 day'));
    }

    //给定一个日期，获取其上月 和 下月
    function get_prev_month($date) {
        return date('Y-m', strtotime(date('Y-m-01', strtotime($date)) . ' -1 month'));
    }

    function get_next_month($date) {
        return date('Y-m', strtotime(date('Y-m-01', strtotime($date)) . ' +1 month'));
    }

}
