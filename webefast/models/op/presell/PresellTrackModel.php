<?php

require_model('tb/TbModel');

/**
 * 预售跟踪
 * @author WMH
 */
class PresellTrackModel extends TbModel {

    /**
     * 获取预售跟踪列表
     * @param array $filter 筛选条件
     * @return array 数据集
     */
    public function get_presell_track_by_page($filter) {
        if (isset($filter['keyword_value']) && $filter['keyword_value'] != '') {
            $filter[$filter['keyword']] = trim($filter['keyword_value']);
        }
        $sql_join = '';
        if (isset($filter['shop_code']) && $filter['shop_code'] != '') {
            $sql_join = ' INNER JOIN `op_presell_plan_shop` AS ps ON pp.`plan_code`=ps.`plan_code` ';
        }
        $sql_join .= 'INNER JOIN `op_presell_plan_detail` AS pd ON pp.`plan_code`=pd.`plan_code` 
                    INNER JOIN `goods_sku` AS gs ON pd.`sku`=gs.`sku`';
        $sql_main = "FROM `op_presell_plan` AS pp  {$sql_join} WHERE 1 AND pp.exit_status = 0";
        $sql_values = array();
        $select = 'pp.`plan_code`,pp.`plan_name`,pp.`start_time`,pd.`id`,pd.`sku`,pd.`presell_num`,pd.`sell_num`,gs.`goods_code`,gs.`barcode`,gs.`spec1_name`,gs.`spec2_name`';
        //计划编码
        if (isset($filter['plan_code']) && $filter['plan_code'] != '') {
            $sql_main .= ' AND pp.`plan_code`=:plan_code ';
            $sql_values[':plan_code'] = $filter['plan_code'];
        }
        //计划名称
        if (isset($filter['plan_name']) && $filter['plan_name'] != '') {
            $sql_main .= ' AND pp.`plan_name` LIKE :plan_name ';
            $sql_values[':plan_name'] = "%{$filter['plan_name']}%";
        }
        //预售状态
        if (!empty($filter['presell_status'])) {
            $status = $filter['presell_status'];
            $curr_time = time();
            switch ($status) {
                case 'no_start':
                    $sql_main .= " AND pp.`start_time`>{$curr_time}";
                    break;
                case 'starting':
                    $sql_main .= " AND pp.`start_time`<={$curr_time} && pp.`end_time`>{$curr_time}";
                    break;
                default:
                    break;
            }
        }
        //预售店铺
        if (isset($filter['shop_code']) && $filter['shop_code'] != '') {
            $shop_arr = explode(',', $filter['shop_code']);
            $shop_str = $this->arr_to_in_sql_value($shop_arr, 'shop_code', $sql_values);
            $sql_main .= " AND ps.`shop_code` IN({$shop_str}) ";
        }
        //商品条形码
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sql_main .= ' AND gs.`barcode`=:barcode ';
            $sql_values[':barcode'] = $filter['barcode'];
        }
        //商品编码
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_main .= ' AND gs.`goods_code`=:goods_code ';
            $sql_values[':goods_code'] = $filter['goods_code'];
        }

        $sql_main .= ' GROUP BY pp.`plan_code`,pd.`sku`';
        $sql_main .= ' ORDER BY pp.`start_time`,gs.`goods_code`,gs.`barcode` DESC';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, TRUE);

        if (!empty($data['data'])) {
            $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code(array('goods_spec1', 'goods_spec2'));
            $spec1_rename = isset($ret_arr['goods_spec1']) ? $ret_arr['goods_spec1'] : '';
            $spec2_rename = isset($ret_arr['goods_spec2']) ? $ret_arr['goods_spec2'] : '';

            $plan_code_arr = array_unique(array_column($data['data'], 'plan_code'));
            $shop_arr = load_model('op/presell/PresellModel')->get_plan_shop_by_code($plan_code_arr);
        }
        $curr_time = time();
        foreach ($data['data'] as &$row) {
            $row['plan_start_time'] = empty($row['start_time']) ? '' : date('Y-m-d H:i:s', $row['start_time']);
            $row['spec'] = "{$spec1_rename}：{$row['spec1_name']}；{$spec2_rename}：{$row['spec2_name']}";

            $row['plan_shop'] = $shop_arr[$row['plan_code']];
        }
        filter_fk_name($data['data'], array('goods_code|goods_code'));

        $ret_data = $data;

        return $this->format_ret(1, $ret_data);
    }

}
