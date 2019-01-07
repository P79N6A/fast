<?php

require_model('tb/TbModel');
require_lang('sys');
require_lib('util/oms_util', true);

class OrderCheckStrategyModel extends TbModel {

    public function __construct($table = '', $db = '') {
        $table = $this->get_table();
        parent::__construct($table);
    }

    function get_table() {
        return 'order_check_strategy';
    }

    function get_by_page($filter) {
        $sql_values = array();
        $sql_join = '';
        $sql_main = "FROM {$this->table} $sql_join WHERE 1";
        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $obj_detail = load_model('oms/OrderCheckStrategyDetailModel');
        $obj_data = load_model('base/ArchiveSearchModel');
        foreach ($data['data'] as &$row) {
            if ($row['is_active'] == '1') {
                $row['is_active'] = "<button class='print_type_btn' style ='background:#1695ca; color:#FFF;'>开启</button><button class='print_type_btn' onclick='change_status(this,0," . $row['strategy_id'] . ")'>关闭</button>";
            } else {
                $row['is_active'] = "<button class='print_type_btn' onclick='change_status(this,1," . $row['strategy_id'] . ")' >开启</button><button class='print_type_btn' style ='background:#1695ca; color:#FFF;'>关闭</button>";
            }

            $strategy_code = $row['check_strategy_code'];
            $detail = $obj_detail->get_all(['check_strategy_code' => $strategy_code]);
            $detail = $detail['data'];
            if (empty($detail)) {
                $row['content'] = '';
                continue;
            }

            $content = array_column($detail, 'content');
            if ($strategy_code === 'not_auto_confirm_with_goods') {
                $sql_values = [];
                $sku_str = $this->arr_to_in_sql_value($content, 'sku', $sql_values);
                $sql = "SELECT barcode FROM goods_sku WHERE sku IN({$sku_str})";
                $content = $this->db->get_all_col($sql, $sql_values);
                $content = array_merge($content, $content);
            } else if ($strategy_code === 'not_auto_confirm_with_shop') {
                $content = $obj_data->get_archives_map('shop', $content);
            } else if ($strategy_code === 'not_auto_confirm_with_store') {
                $content = $obj_data->get_archives_map('store', $content);
            } else if ($strategy_code === 'protect_time') {
                $row['content'] = $content[0] . ' 分钟';
            } else if ($strategy_code === 'not_auto_confirm_with_money') {
                $money = explode(',', $content[0]);
                if (empty($content[0])) {
                    $row['content'] = '';
                } else if ($money[0] === '') {
                    $row['content'] = "订单金额 <= {$money[1]}";
                } else if ($money[1] === '') {
                    $row['content'] = "{$money[0]} < 订单金额";
                } else {
                    $row['content'] = "{$money[0]} < 订单金额 <= {$money[1]}";
                }
            }

            $row['content'] = empty($row['content']) ? implode('，', $content) : $row['content'];
            $row['content'] = $this->cc_msubstr($row['content'], 100);
        }

        $ret_status = OP_SUCCESS;
        return $this->format_ret($ret_status, $data);
    }

    function cc_msubstr($str, $length, $start = 0, $replace = ' ...', $charset = "utf-8") {
        $str_cut = '';
        $str_len = mb_strwidth($str, $charset);
        if (($length - 3) < $str_len) {
            $str_cut = mb_strimwidth($str, $start, $length, $replace, $charset);
            $str = "<span class='tip' title='{$str}'>{$str_cut}</span>";
        }
        return $str;
    }

    function get_shop_info() {
        $shop_info = load_model('base/ShopModel')->get_list();
        $sale_channel_code = array();
        $new_shop = array();
        $sale_channel_info = array();
        $j = 0;
        foreach ($shop_info as $shop) {
            if (!in_array($shop['sale_channel_code'], $sale_channel_code)) {
                $sale_channel_code[] = $shop['sale_channel_code'];
                $sale_channel_name = oms_tb_val('base_sale_channel', 'sale_channel_name', array('sale_channel_code' => $shop['sale_channel_code']));
                $sale_channel_info[$j]['sale_channel_name'] = $sale_channel_name;
                $sale_channel_info[$j]['sale_channel_code'] = $shop['sale_channel_code'];
                $j++;
            }
        }
        $i = 0;
        foreach ($shop_info as $key => $shop) {
            foreach ($sale_channel_info as $key => $value) {
                if ($shop['sale_channel_code'] == $value['sale_channel_code']) {
                    $new_shop[$value['sale_channel_name']][$i]['shop_code'] = $shop['shop_code'];
                    $new_shop[$value['sale_channel_name']][$i]['shop_name'] = $shop['shop_name'];
                    $i ++;
                }
            }
        }
        return $new_shop;
    }

    function update_active($type, $id) {
        if ($id == 3 && $type == 0) {
            $ret = $this->db->query("update sys_schedule set plan_exec_data=null,plan_exec_time=last_time+loop_time where code='auto_confirm'");
        }
        if ($id == 3 && $type == 1) {
            $ret = load_model('oms/OrderCheckStrategyDetailModel')->set_sys_schedule();
        }
        $ret = parent::update(array('is_active' => $type), array('strategy_id' => $id));
        return $ret;
    }

}
