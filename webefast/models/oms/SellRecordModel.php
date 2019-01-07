<?php

require_lib('util/oms_util', true);
require_model('tb/TbModel');
require_lang('oms');
require_lib('apiclient/TaobaoClient');

class SellRecordModel extends TbModel {

    /**
     * @var string 表名
     */
    protected $table = 'oms_sell_record';
    protected $detail_table = 'oms_sell_record_detail';
    //转单状态explode
    public $tran_status = array(
        0 => '未转单',
        1 => '已转单',
        -1 => '转单失败'
    );
    //订单状态
    public $order_status = array(
        0 => '未确认',
        1 => '已确认',
        3 => '已作废',
        5 => '已完成',
    );
    //付款状态
    public $pay_status = array(
        0 => '未付款',
        2 => '已付款',
    );
    //发货状态
    public $shipping_status = array(
        0 => '未发货',
        1 => '已通知配货',
        2 => '拣货中',
        3 => '已完成拣货',
        4 => '已发货',
    );
    //支付类型
    public $pay_type = array(
        'secured' => '担保交易',
        'nosecured' => '在线支付',
        'cod' => '货到付款',
    );
    public $is_back = array(
        -1 => '回写失败',
        0 => '未回写',
        1 => '回写成功',
        2 => '本地回写',
    );
    public $page_size = 300;
    public $sale_channel_data_map;

    function get_sale_channel_name_by_code($code) {
        if (!isset($this->sale_channel_data_map)) {
            $this->sale_channel_data_map = load_model('base/SaleChannelModel')->get_data_map();
        }
        $ret_v = isset($this->sale_channel_data_map[$code]) ? $this->sale_channel_data_map[$code] : '';
        return $ret_v;
    }

    function get_select_is_back() {
        $is_back_t = $this->is_back;
        $is_back = array();
        foreach ($is_back_t as $k => $back_status) {
            $is_back[] = array($k, $back_status);
        }
        return $is_back;
    }

    function td_list_by_page($filter) {
        $filter['is_problem'] = '1';
        return $this->get_by_page($filter);
    }

    function do_list_by_page($filter) {
        $filter['ref'] = 'do';
        //$filter['is_problem'] = '0';
        if (isset($filter['pay_status']) && $filter['pay_status'] == 'all') {
            unset($filter['pay_status']);
        }
        if (isset($filter['order_status']) && $filter['order_status'] == 'all') {
            unset($filter['order_status']);
        }
        if (isset($filter['notice_flag']) && $filter['notice_flag'] == 'all') {
            unset($filter['notice_flag']);
        }
        if (isset($filter['shipping_flag']) && $filter['shipping_flag'] == 'all') {
            unset($filter['shipping_flag']);
        }
        if (isset($filter['cancel_flag']) && $filter['cancel_flag'] == 'all') {
            unset($filter['cancel_flag']);
        }
        if (isset($filter['is_fx_settlement']) && $filter['is_fx_settlement'] == 'all') {
            unset($filter['is_fx_settlement']);
        }
        if (isset($filter['custom_type']) && $filter['custom_type'] == 'all') {
            unset($filter['custom_type']);
        }
        if (isset($filter['is_gift']) && $filter['is_gift'] === '') {
            unset($filter['is_gift']);
        }

        $filter['no_sort'] = 1;


        return $this->get_by_page($filter);
    }

    function get_by_eait_shipped_page($filter) {
        return $this->get_by_page($filter);
    }

    function shipped_list_by_page($filter) {
        $filter['ref'] = 'do';
        $filter['shipping_status'] = 4;
        return $this->get_by_page($filter);
    }

    function shipped_count($filter) {
        $filter['ref'] = 'do';
        $filter['shipping_status'] = 4;

        // 汇总
        $sqlArr = $this->get_by_page($filter, true);
        $sqlArr = $sqlArr['data'];

        $sql = " select sum(rl.paid_money) paid_money
        , sum(rl.express_money) express_money
        , sum(rl.goods_num) goods_num
        , count(rl.sell_record_code) record_count ";
        $sql .= $sqlArr['from'];
        return $this->db->get_row($sql, $sqlArr['params']);
    }

    function ex_list_by_page($filter) {
        $filter['ref'] = 'ex';
        if (isset($filter['exist_fenxiao']) && $filter['exist_fenxiao'] == '1') {
            $tab = empty($filter['ex_list_tab']) ? 'tabs_settlement' : $filter['ex_list_tab'];
        } else {
            $tab = empty($filter['ex_list_tab']) ? 'tabs_settlement' : $filter['ex_list_tab'];
        }

        switch ($tab) {
            case 'tabs_all'://全部
                break;
            case 'tabs_pay'://待付款
                $filter['order_status'] = 0;
                $filter['pay_status'] = 0;
                $filter['pay_type_td'] = 'cod';
                break;
            case 'tabs_settlement'://待结算
                $filter['is_fx_settlement'] = 0;
                $filter['pay_status_or_cod'] = 'yes';
                $filter['order_status'] = 0;
                $filter['exist_fenxiao'] = 1;
                break;
            case 'tabs_confirm'://待确认
                $filter['order_status'] = 0;
                $filter['must_occupy_inv'] = 1;
                $filter['pay_status'] = 2;
                $filter['pay_status_type'] = "cod";
                $filter['check_fenxiao_code'] = 1;
                break;
            case 'tabs_notice_shipping'://待通知配货
                $filter['order_status'] = 1;
                $filter['shipping_status'] = 0;
                break;
            case 'tabs_send_fx'://待供应商发货
                $filter['order_status'] = 1;
                $filter['is_fx_settlement'] = 1;
                $filter['shipping_flag'] = 0;
                $filter['pay_status'] = 2;
                break;
            case 'tabs_send'://待发货
                $filter['order_status'] = 1;
                $filter['shipping_flag'] = 0;
                break;
            case 'tabs_confirm_fx'://分销待确认
                $filter['is_fx_settlement'] = 1;
                $filter['pay_status_or_cod'] = 'yes';
                $filter['order_status'] = 0;
                $filter['exist_fenxiao'] = 1;
                break;
        }
        if (isset($filter['is_gift']) && $filter['is_gift'] === '') {
            unset($filter['is_gift']);
        }
        return $this->get_by_page($filter);
    }

    private function is_join_detail($filter) {
        $key_arr = array('action_type', 'barcode', 'combo_barcode', 'goods_name', 'goods_code', 'exact_code', 'is_gift');
        $check = FALSE;
        foreach ($key_arr as $key) {
            if (isset($filter[$key]) && $filter[$key] != '') {
                $check = TRUE;
                break;
            }
        }

        $key_val_arr = array('ctl_type' => 'export',);
        foreach ($key_val_arr as $k => $v) {
            if (isset($filter[$k]) && $filter[$k] == $v && isset($filter['ctl_export_conf']) && $filter['ctl_export_conf'] != 'search_record_list') {
                $check = TRUE;
                break;
            }
        }

        return $check;
    }

    /**
     * @todo 从订单详情表中获取条形码对应的行单号
     * @param array 条形码
     * @return 订单号字串
     */
    private function get_record_code_by_barcode($barcode) {
        $sql_values = array();
        $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($barcode);
        $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
        $sql = "SELECT sell_record_code FROM oms_sell_record_detail WHERE sku IN ($sku_str) GROUP BY sell_record_code";
        $sell_record_code_arr = $this->db->get_all($sql, $sql_values);
        if (!empty($sell_record_code_arr)) {
            $sell_record_code_str = array();
            foreach ($sell_record_code_arr as $val) {
                $sell_record_code_str[] = "'" . $val['sell_record_code'] . "'";
            }
            $sell_record_code_str = implode(',', $sell_record_code_str);
            return $sell_record_code_str;
        }
        return $sell_record_code_arr;
    }

    /**
     * 根据条件查询数据<br>
     * 注意: 此方法为核心公共方法, 受多处调用, 请慎重修改.
     * @param $filter
     * @param $onlySql
     * @param $select
     * @return array
     */
    function get_by_page($filter, $onlySql = false, $select = 'rl.*') {
        $un_order_export = $filter['ctl_type'] == 'export' && isset($filter['ctl_export_conf']) && $filter['ctl_export_conf'] != 'search_record_list' && $filter['ctl_export_conf'] != 'fx_search_record_list';
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql_values = array();
        //模糊查询参数控制
        $param = load_model('sys/SysParamsModel')->get_val_by_code(array('fuzzy_search'));
        $sql_join = "";
        $is_join_detail = $is_join = $this->is_join_detail($filter);
        if ($is_join === TRUE) {
            $sql_join = " LEFT JOIN {$this->detail_table} rr ON rl.sell_record_code = rr.sell_record_code ";
        }
        if (isset($filter['exact_code']) && $filter['exact_code'] !== '') {
            $sql_join .= " LEFT JOIN goods_sku gs ON rr.sku = gs.sku ";
        }

        //订单标签，组装表连接
        if (isset($filter['order_tag']) && $filter['order_tag'] !== '') {
            $is_join = TRUE;
            $tag_arr = explode(',', $filter['order_tag']);
            $tag_join = in_array('no_label_code', $tag_arr) ? ' LEFT JOIN ' : ' INNER JOIN ';
            $sql_join .= $tag_join . " oms_sell_record_tag AS rt ON rl.sell_record_code = rt.sell_record_code AND rt.tag_type='order_tag' ";
        }
        $sql_main = "FROM {$this->table} rl $sql_join WHERE 1 ";

        //订单标签的条件查询
        if (isset($filter['order_tag']) && $filter['order_tag'] !== '') {
            $tag_arr = explode(',', $filter['order_tag']);
            $tag_sql = array();
            if (in_array('no_label_code', $tag_arr)) {
                $key = array_search('no_label_code', $tag_arr);
                unset($tag_arr[$key]);
                $tag_sql[] = " rt.tag_v IS NULL ";
            }
            if (!empty($tag_arr)) {
                $tag_str = $this->arr_to_in_sql_value($tag_arr, 'tag', $sql_values);
                $tag_sql[] = " rt.tag_v IN ({$tag_str}) ";
            }
            $tag_sql_main = implode(' OR ', $tag_sql);
            $sql_main .= " AND ({$tag_sql_main})";
        }

        $bak_sql_main = $sql_main;
        $sql_one_main_arr = array();
        $sql_one_values = array();
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        //商店仓库权限
        $login_type = load_model('base/CustomModel')->get_session_data('login_type');
        if ($login_type == 2) {
            $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code', $filter_store_code, 'get_fx_store');
            //获取当前登录的分销商code，根据code查询分销商code
            $user_code = load_model('base/CustomModel')->get_session_data('user_code');
            $custom = load_model('base/CustomModel')->get_custom_by_user_code($user_code);
            if (!empty($custom)) {
                $sql_main .= " AND rl.fenxiao_code = :fenxiao_code";
                $sql_values[':fenxiao_code'] = $custom['custom_code'];
            } else {
                $sql_main .= " AND 1 != 1";
            }
        } else {
            $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code', $filter_store_code);
            $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
            $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('rl.shop_code', $filter_shop_code);
        }

        if (isset($filter['pay_status_or_cod']) && $filter['pay_status_or_cod'] == 'yes') {
            $sql_main .= " AND (rl.pay_status = 2 OR rl.pay_type = 'cod') ";
        }

        if (isset($filter['is_gift']) && $filter['is_gift'] == 1 && !$un_order_export) {
            $sql_main .= ' AND rr.is_gift = 1 ';
        }
        if (isset($filter['sale_mode']) && $filter['sale_mode'] != '') {
            $sql_main .= " AND rl.sale_mode = :sale_mode ";
            $sql_values[':sale_mode'] = $filter['sale_mode'];
        }
        //验收员
        if (isset($filter['delivery_person']) && $filter['delivery_person'] != '') {
            $user_code = $this->db->get_row("SELECT user_code FROM sys_user WHERE user_name = :user_name ", array(':user_name' => $filter['delivery_person']));
            if (!empty($user_code)) {
                $sql_main .= "AND rl.delivery_person = :delivery_person";
                $sql_values[':delivery_person'] = $user_code;
            } else {
                $sql_main .= " AND 1=2 ";
            }
        }
        if (isset($filter['sell_record_attr']) && $filter['sell_record_attr'] !== '') {
            $sell_record_attr_arr = explode(',', $filter['sell_record_attr']);
            $sql_attr_arr = array();
            foreach ($sell_record_attr_arr as $attr) {
                if ($attr == 'attr_lock') {
                    $sql_attr_arr[] = " rl.is_lock = 1";
                }
                if ($attr == 'attr_pending') {
                    $sql_attr_arr[] = " rl.is_pending = 1";
                }
                if ($attr == 'attr_problem') {
                    $sql_attr_arr[] = " rl.is_problem = 1";
                }
                if ($attr == 'attr_bf_quehuo') {
                    $sql_attr_arr[] = " (rl.must_occupy_inv = 1 and rl.lock_inv_status = 2)";
                }
                if ($attr == 'attr_all_quehuo') {
                    $sql_attr_arr[] = " (rl.must_occupy_inv = 1 and rl.lock_inv_status = 3)";
                }
                if ($attr == 'attr_combine') {
                    $sql_attr_arr[] = " rl.is_combine_new = 1";
                }
                if ($attr == 'attr_split') {
                    $sql_attr_arr[] = " rl.is_split_new = 1";
                }
                if ($attr == 'attr_change') {
                    $sql_attr_arr[] = " rl.is_change_record = 1";
                }
                if ($attr == 'attr_handwork') {
                    $sql_attr_arr[] = " rl.is_handwork = 1";
                }
                if ($attr == 'attr_copy') {
                    $sql_attr_arr[] = " rl.is_copy = 1";
                }
                if ($attr == 'attr_presale') {
                    $sql_attr_arr[] = " rl.sale_mode = 'presale'";
                }
                if ($attr == 'attr_fenxiao') {
                    $sql_attr_arr[] = " (rl.is_fenxiao = 1 OR rl.is_fenxiao = 2) ";
                }
                if ($attr == 'is_rush') {
                    $sql_attr_arr[] = " rl.is_rush = 1";
                }
                if ($attr == 'is_replenish') {
                    $sql_attr_arr[] = " rl.is_replenish = 1 ";
                }
                if ($attr == 'is_problem') {
                    $sql_attr_arr[] = " (rl.must_occupy_inv = '1' AND rl.lock_inv_status = '1' AND rl.is_pending = '0' AND rl.is_problem = '0') ";
                }
            }
            $sql_main .= ' and (' . join(' or ', $sql_attr_arr) . ')';
        }

        if (isset($filter['order_status']) && $filter['order_status'] !== '') {
            $sql_main .= " AND rl.order_status = :order_status ";
            $sql_values[':order_status'] = $filter['order_status'];
        }
        if (isset($filter['pay_status']) && $filter['pay_status'] !== '') {
            if (isset($filter['pay_status_type']) && $filter['pay_status_type'] !== '') {
                $sql_main .= " AND (rl.pay_status = :pay_status  or rl.pay_type = :pay_status_type)";
                $sql_values[':pay_status'] = $filter['pay_status'];
                $sql_values[':pay_status_type'] = $filter['pay_status_type'];
            } else {
                $sql_main .= " AND rl.pay_status = :pay_status ";
                $sql_values[':pay_status'] = $filter['pay_status'];
            }
        }
        if (isset($filter['check_fenxiao_code']) && $filter['shipping_status'] !== '') {
            //显示订单淘、分销订单和普通分销已结算订单
            $sql_main .= " AND (rl.is_fenxiao in (1,0) OR (rl.is_fenxiao = 2 AND rl.is_fx_settlement = 1)) ";
        }
        if (isset($filter['shipping_status']) && $filter['shipping_status'] !== '') {
            $sql_main .= " AND rl.shipping_status = :shipping_status ";
            $sql_values[':shipping_status'] = $filter['shipping_status'];
        }
        // 待发货
        if (isset($filter['ex_list_tab']) && $filter['ex_list_tab'] == 'tabs_send') {
            $sql_main .= " AND rl.shipping_status IN(1,2,3) AND rl.order_status = 1";
        }
        //是否已通知配货
        if (isset($filter['notice_flag'])) {
            if ($filter['notice_flag'] == '0') {
                $sql_main .= " AND rl.shipping_status = 0 ";
            } elseif ($filter['notice_flag'] == '1') {
                $sql_main .= " AND rl.shipping_status >= 1 ";
            }
        }
        //是否已发货
        if (isset($filter['shipping_flag'])) {
            if ($filter['shipping_flag'] == '0') {
                $sql_main .= " AND rl.shipping_status < 4 ";
            } elseif ($filter['shipping_flag'] == '1') {
                $sql_main .= " AND rl.shipping_status = 4 ";
            }
        }
        //是否打印发票
        if (isset($filter['is_print_invoice'])) {
            if ($filter['is_print_invoice'] == '0') {
                $sql_main .= " AND rl.is_print_invoice = 0 ";
            } elseif ($filter['is_print_invoice'] == '1') {
                $sql_main .= " AND rl.is_print_invoice = 1 ";
            }
        }
        //是否作废
        if (isset($filter['cancel_flag'])) {
            if ($filter['cancel_flag'] == '0') {
                $sql_main .= " AND rl.order_status <> 3 ";
            } elseif ($filter['cancel_flag'] == '1') {
                $sql_main .= " AND rl.order_status = 3 ";
            }
        }
        //换货单
        if (isset($filter['is_change_record']) && $filter['is_change_record'] !== '') {
            $sql_main .= " AND rl.is_change_record = :is_change_record ";
            $sql_values[':is_change_record'] = $filter['is_change_record'];
        }
        //唯一码 // 开启唯一码之后 唯一码查询条件
        if (isset($filter['unique_code']) && $filter['unique_code'] !== '') {

            $sell_record_arr = load_model('prm/GoodsUniqueCodeLogModel')->get_goods_unique_code($filter['unique_code'], 'sell_record');
            if (empty($sell_record_arr)) {
                $sql_main .= ' AND 1 != 1 ';
            } else {
                $sell_record_str = $this->arr_to_in_sql_value($sell_record_arr, 'sell_record_code', $sql_values);
                $sql_main .= " AND rl.sell_record_code in ( {$sell_record_str} ) ";
            }
        }
        //是否结算
        if (isset($filter['is_fx_settlement']) && $filter['is_fx_settlement'] !== '') {
            $sql_main .= " AND rl.is_fx_settlement = :is_fx_settlement";
            $sql_values[':is_fx_settlement'] = $filter['is_fx_settlement'];
        }
        //分销类型
        if (isset($filter['custom_type']) && $filter['custom_type'] !== '') {
            $custom_arr = load_model('base/CustomModel')->get_custom_by_custom_type($filter['custom_type']);
            $custom_arr = $this->arr_to_in_sql_value($custom_arr, 'fenxiao_code', $sql_values);
            $sql_main .= " AND rl.fenxiao_code in ( {$custom_arr} ) ";
        }
        //分销商
        if (isset($filter['fenxiao_name']) && $filter['fenxiao_name'] !== '') {
            $sql_main .= " AND rl.fenxiao_name LIKE :fenxiao_name ";
            $sql_values[':fenxiao_name'] = '%' . $filter['fenxiao_name'] . '%';
        }

        //订单号
        if (isset($filter['sell_record_code']) && $filter['sell_record_code'] !== '') {
            $filter['sell_record_code'] = str_replace('，', ',', $filter['sell_record_code']);
            $sell_record_arr = explode(',', $filter['sell_record_code']);
            $sell_record_arr = array_map(function ($val) {
                return trim($val);
            }, $sell_record_arr);
            if ($param['fuzzy_search'] == 1) {
                $sell_record_code_where = $this->arr_to_like_sql_value($sell_record_arr, 'sell_record_code', $sql_values, 'rl.');
                $sql_main .= ' AND ' . $sell_record_code_where;
            } else {
                $sell_record_code_where = $this->arr_to_in_sql_value($sell_record_arr, 'sell_record_code', $sql_values);
                $sql_main .= " AND   rl.sell_record_code in ({$sell_record_code_where}) ";
            }
        }
        //正常单或非正常单
        if (isset($filter['is_normal']) && $filter['is_normal'] !== '') {
            $is_normal = '';
            if ($filter['is_normal'] == '2') {//非正常
                //缺货
                $is_normal .= " (rl.must_occupy_inv = '1' AND (rl.lock_inv_status <> '1' ) AND (rl.lock_inv_status <> 0 ))";
                //挂起
                $is_normal .= " OR rl.is_pending = '1' ";
                //设问
                $is_normal .= " OR rl.is_problem = '1' ";
            }
            if ($filter['is_normal'] == '1') {//正常
                //缺货
                $is_normal .= " (rl.must_occupy_inv = '1' AND rl.lock_inv_status = '1') ";
                //挂起
                $is_normal .= " AND rl.is_pending = '0' ";
                //设问
                $is_normal .= " AND rl.is_problem = '0' ";
            }
            $sql_main .= " AND ({$is_normal})";
        }
        //is_my_lock
        //是否是我锁定的
        if (isset($filter['is_my_lock']) && $filter['is_my_lock'] == '1') {
            $sys_user = load_model("oms/SellRecordOptModel")->sys_user();
            $sql_main .= " AND rl.is_lock = 1 AND rl.is_lock_person = :user_code";
            $sql_values[':user_code'] = $sys_user['user_code'];
        }
        //交易号
        if (isset($filter['deal_code_list']) && $filter['deal_code_list'] !== '') {
            $filter['deal_code_list'] = str_replace('，', ',', $filter['deal_code_list']);
            $deal_code_list_arr = explode(',', $filter['deal_code_list']);
            $deal_code_list_arr = array_map(function ($val) {
                return trim($val);
            }, $deal_code_list_arr);
            $sql_d_values = array();
            $sql_deal = 'select sell_record_code from oms_sell_record_detail';
            if ($param['fuzzy_search'] == 1) {
                $deal_code_str = $this->arr_to_like_sql_value($deal_code_list_arr, 'deal_code', $sql_d_values);
                $sql_deal .= ' WHERE ' . $deal_code_str;
            } else {
                $deal_code_str = $this->arr_to_in_sql_value($deal_code_list_arr, 'deal_code', $sql_d_values);
                $sql_deal .= " WHERE deal_code IN({$deal_code_str})";
            }
            $sell_record_data = $this->db->get_all($sql_deal, $sql_d_values);
            if (!empty($sell_record_data)) {
                $sell_record_arr = array_column($sell_record_data, 'sell_record_code');
                $sell_record_code_where = $this->arr_to_in_sql_value($sell_record_arr, 'sell_record_code', $sql_values);
                $sql_main .= " AND rl.sell_record_code in ({$sell_record_code_where}) ";
            } else {
                $filter['deal_code_list'] = $this->get_sql_for_search($filter['deal_code_list'], 'rl.deal_code_list');
                $sql_main .= " AND {$filter['deal_code_list']} ";
            }
        }

        //快递号
        if (isset($filter['express_no']) && $filter['express_no'] !== '') {
            if ($param['fuzzy_search'] == 1) {
                $sql_one_main_arr['express_no'] = " AND rl.express_no like :express_no ";
                $sql_one_values[':express_no'] = "%" . $filter['express_no'] . "%";
            }else{
                $sql_one_main_arr['express_no'] = " AND rl.express_no = :express_no ";
                $sql_one_values[':express_no'] = $filter['express_no'];
            }
        }
        //支付方式
        if (isset($filter['pay_type']) && $filter['pay_type'] !== '') {
            $arr = explode(',', $filter['pay_type']);
            $str = $this->arr_to_in_sql_value($arr, 'pay_type', $sql_values);
            $sql_main .= " AND rl.pay_code in ( " . $str . " ) ";
        }

        //支付方式
        if (isset($filter['pay_type_td']) && $filter['pay_type_td'] !== '') {
            $sql_main .= " AND rl.pay_type <> :pay_type_td ";
            $sql_values[':pay_type_td'] = $filter['pay_type_td'];
        }
        //销售平台
        if (isset($filter['sale_channel_code']) && $filter['sale_channel_code'] !== '') {
            $arr = explode(',', $filter['sale_channel_code']);
            $str = $this->arr_to_in_sql_value($arr, 'sale_channel_code', $sql_values);
            $sql_main .= " AND rl.sale_channel_code in ( " . $str . " ) ";
        }
        //店铺
        if (isset($filter['shop_code']) && $filter['shop_code'] !== '') {
            $arr = explode(',', $filter['shop_code']);
            $str = $this->arr_to_in_sql_value($arr, 'shop_code', $sql_values);
            $sql_main .= " AND rl.shop_code in ( " . $str . " ) ";
        }
        //仓库
        if (isset($filter['store_code']) && $filter['store_code'] !== '') {
            $arr = explode(',', $filter['store_code']);
            $str = $this->arr_to_in_sql_value($arr, 'store_code', $sql_values);
            $sql_main .= " AND rl.store_code in ( " . $str . " ) ";
        }

        //外部仓库订单
        if (isset($filter['store_code_outside']) && $filter['store_code_outside'] !== '') {
            $store_arr = load_model('sys/ShopStoreModel')->get_store_for_wms();
            $store_str = implode(",", $store_arr);
            if ($filter['store_code_outside'] == '1') {
                $sql_main .= " AND rl.store_code in (:store_code) ";
                $sql_values[':store_code'] = $store_str;
            } else {
                $sql_main .= " AND rl.store_code not in (:store_code) ";
                $sql_values[':store_code'] = $store_str;
            }
        }
        //支付宝交易号
        if (isset($filter['alipay_no']) && $filter['alipay_no'] !== '') {
            if ($filter['alipay_no'] == '0') {
                $sql_main .= " AND rl.alipay_no = '' ";
            } else {
                $sql_main .= " AND rl.alipay_no <> '' ";
            }
        }
        //买家昵称
        if (isset($filter['buyer_name']) && $filter['buyer_name'] !== '') {
            //参数开启模糊查询，关闭精确查询
            if ($param['fuzzy_search'] != 1) {
                $customer_code_arr = load_model('crm/CustomerOptModel')->get_customer_code_with_search($filter['buyer_name']);
                if (!empty($customer_code_arr)) {
                    $customer_code_str = "'" . implode("','", $customer_code_arr) . "'";
                    $sql_main .= " AND ( rl.customer_code in ({$customer_code_str}) ) ";
                } else {
                    $sql_main .= " AND rl.buyer_name = :buyer_name ";
                    $sql_values[':buyer_name'] = $filter['buyer_name'];
                }
            } else {
                $customer_code_arr = load_model('crm/CustomerOptModel')->get_customer_code_with_search($filter['buyer_name']);
                if (!empty($customer_code_arr)) {
                    $customer_code_str = "'" . implode("','", $customer_code_arr) . "'";
                    $sql_main .= " AND (rl.buyer_name LIKE :buyer_name  OR rl.customer_code in ({$customer_code_str}) ) ";
                    $sql_values[':buyer_name'] = '%' . $filter['buyer_name'] . '%';
                } else {
                    $sql_main .= " AND rl.buyer_name LIKE :buyer_name ";
                    $sql_values[':buyer_name'] = '%' . $filter['buyer_name'] . '%';
                }
            }
        }

        //买家留言
        if (isset($filter['is_buyer_remark']) && $filter['is_buyer_remark'] != '') {
            if ($filter['is_buyer_remark'] == '1') {
                if (isset($filter['buyer_remark']) && $filter['buyer_remark'] !== '') {
                    $sql_main .= " AND rl.buyer_remark LIKE :buyer_remark ";
                    $sql_values[':buyer_remark'] = "%" . $filter['buyer_remark'] . "%";
                } else {
                    $sql_main .= " AND rl.buyer_remark <> ''";
                }
            } else if ($filter['is_buyer_remark'] == 'all') {
                if (isset($filter['buyer_remark']) && $filter['buyer_remark'] !== '') {
                    $sql_main .= " AND rl.buyer_remark LIKE :buyer_remark ";
                    $sql_values[':buyer_remark'] = "%" . $filter['buyer_remark'] . "%";
                }
            } else if ($filter['is_buyer_remark'] == '0') {
                $sql_main .= " AND rl.buyer_remark = ''";
            }
        }
        //商家留言
        if (isset($filter['is_seller_remark']) && $filter['is_seller_remark'] != '') {
            if ($filter['is_seller_remark'] == '1') {
                if (isset($filter['seller_remark']) && $filter['seller_remark'] !== '') {
                    $sql_main .= " AND rl.seller_remark LIKE :seller_remark ";
                    $sql_values[':seller_remark'] = "%" . $filter['seller_remark'] . "%";
                } else {
                    $sql_main .= " AND rl.seller_remark <> ''";
                }
            } else if ($filter['is_seller_remark'] == 'all') {
                if (isset($filter['seller_remark']) && $filter['seller_remark'] !== '') {
                    $sql_main .= " AND rl.seller_remark LIKE :seller_remark ";
                    $sql_values[':seller_remark'] = "%" . $filter['seller_remark'] . "%";
                }
            } else if ($filter['is_seller_remark'] == '0') {
                $sql_main .= " AND rl.seller_remark = ''";
            }
        }
        //仓库留言
        if (isset($filter['store_remark']) && $filter['store_remark'] !== '') {
            $sql_main .= " AND rl.store_remark LIKE :store_remark ";
            $sql_values[':store_remark'] = "%" . $filter['store_remark'] . "%";
        }
        //商品编码
        if (isset($filter['goods_code']) && $filter['goods_code'] !== '') {
            $sql_main .= " AND rr.goods_code LIKE :goods_code ";
            $sql_values[':goods_code'] = "%" . $filter['goods_code'] . "%";
        }
        //商品名称转换成商品编码查询
        if (isset($filter['goods_name']) && $filter['goods_name'] !== '') {
            $sql = "SELECT goods_code FROM base_goods WHERE goods_name LIKE :goods_name";
            $goods_code_arr = $this->db->get_all_col($sql, [':goods_name' => "%{$filter['goods_name']}%"]);
            if(!empty($goods_code_arr)){
                $goods_code_str = $this->arr_to_in_sql_value($goods_code_arr, 'goods_code', $sql_values);
                $sql_main .= " AND rr.goods_code IN({$goods_code_str})";
            }else{
                return $this->format_ret(1,['filter' => ['record_count' => 0], 'data' => []]);
            }
        }
        //商品条形码-查询
        if (isset($filter['barcode']) && $filter['barcode'] !== '' && $filter['ctl_type'] !== 'export') {
            $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
            if (empty($sku_arr)) {
                $sql_main .= " AND  1=2 ";
            } else {
                $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
                $sql_main .= " AND  rr.sku in({$sku_str})   ";
            }
        }

        //商品条形码（转成订单号查询数据）-导出
        if (isset($filter['barcode']) && $filter['barcode'] !== '' && $filter['ctl_type'] === 'export') {
            $sell_record_code = $this->get_record_code_by_barcode($filter['barcode']);
            if (empty($sell_record_code)) {
                $sql_main .= " AND  1=2 ";
            } else {

                $sql_one_main_arr['sell_record_code'] = " AND rl.sell_record_code in ({$sell_record_code}) ";
            }
        }

        //换货单
        if (isset($filter['is_change_record']) && $filter['is_change_record'] !== '') {
            $sql_main .= " AND rl.is_change_record = :is_change_record ";
            $sql_values[':is_change_record'] = $filter['is_change_record'];
        }
        //订单备注
        if (isset($filter['order_remark']) && $filter['order_remark'] !== '') {
            $sql_main .= " AND rl.order_remark LIKE :order_remark ";
            $sql_values[':order_remark'] = "%" . $filter['order_remark'] . "%";
        }
        //收货人
        if (isset($filter['receiver_name']) && $filter['receiver_name'] !== '') {

            $customer_address_id = load_model('crm/CustomerOptModel')->get_customer_address_id_with_search($filter['receiver_name'], 'name');
            if (!empty($customer_address_id)) {
                $customer_address_id_str = implode(",", $customer_address_id);
                $sql_main .= " AND ( rl.receiver_name LIKE :receiver_name  OR rl.customer_address_id in ({$customer_address_id_str}) ) ";
                $sql_values[':receiver_name'] = '%' . $filter['receiver_name'] . '%';
            } else {
                $sql_main .= " AND rl.receiver_name LIKE :receiver_name ";
                $sql_values[':receiver_name'] = '%' . $filter['receiver_name'] . '%';
            }
        }

        //确认人
        if (isset($filter['confirm_person']) && $filter['confirm_person'] !== '') {
            $sql_main .= " AND rl.confirm_person LIKE :confirm_person ";
            $sql_values[':confirm_person'] = '%' . $filter['confirm_person'] . '%';
        }
        //通知配货人
        if (isset($filter['notice_person']) && $filter['notice_person'] !== '') {
            $sql_main .= " AND rl.notice_person LIKE :notice_person ";
            $sql_values[':notice_person'] = '%' . $filter['notice_person'] . '%';
        }
        //手机号
        if (isset($filter['receiver_mobile']) && $filter['receiver_mobile'] !== '') {
            //参数开启模糊查询，关闭精确查询
            if ($param['fuzzy_search'] != 1) {
                $customer_address_id = load_model('crm/CustomerOptModel')->get_customer_address_id_with_search($filter['receiver_mobile'], 'tel');
                if (!empty($customer_address_id)) {
                    $customer_address_id_str = implode(",", $customer_address_id);
                    $sql_one_main_arr['receiver_mobile'] = " AND ( rl.receiver_mobile = :receiver_mobile OR rl.customer_address_id in ({$customer_address_id_str}) ) ";
                    $sql_one_values[':receiver_mobile'] = $filter['receiver_mobile'];
                } else {
                    $sql_one_main_arr['receiver_mobile'] = " AND rl.receiver_mobile = :receiver_mobile ";
                    $sql_one_values[':receiver_mobile'] = $filter['receiver_mobile'];
                }
            } else {
                $customer_address_id = load_model('crm/CustomerOptModel')->get_customer_address_id_with_search($filter['receiver_mobile'], 'tel');
                if (!empty($customer_address_id)) {
                    $customer_address_id_str = implode(",", $customer_address_id);
                    $sql_one_main_arr['receiver_mobile'] = " AND ( rl.receiver_mobile LIKE :receiver_mobile OR rl.customer_address_id in ({$customer_address_id_str}) ) ";
                    $sql_one_values[':receiver_mobile'] = '%' . $filter['receiver_mobile'] . '%';
                } else {
                    $sql_one_main_arr['receiver_mobile'] = " AND rl.receiver_mobile LIKE :receiver_mobile ";
                    $sql_one_values[':receiver_mobile'] = '%' . $filter['receiver_mobile'] . '%';
                }
            }
        }
        //配送方式
        if (isset($filter['express_code']) && $filter['express_code'] !== '') {
            $arr = explode(',', $filter['express_code']);
            $str = $this->arr_to_in_sql_value($arr, 'express_code', $sql_values);
            $sql_main .= " AND rl.express_code in ( " . $str . " ) ";
        }
        //快递单号
        /*if (isset($filter['express_no']) && $filter['express_no'] !== '') {
            $sql_one_main_arr['express_no'] = " AND rl.express_no LIKE :express_no ";
            $sql_one_values[':express_no'] = '%' . $filter['express_no'] . '%';
        }*/
        //国家
        if (isset($filter['country']) && $filter['country'] !== '') {
            $sql_main .= " AND rl.receiver_country = :country ";
            $sql_values[':country'] = $filter['country'];
        }
        //省
        if (isset($filter['province']) && $filter['province'] !== '') {
            $sql_main .= " AND rl.receiver_province = :province ";
            $sql_values[':province'] = $filter['province'];
        }
        // 省（多选）
        if (isset($filter['province_multi']) && $filter['province_multi'] !== '') {
            $filter['province_multi'] = explode(',', $filter['province_multi']);
            $stand = '';
            for ($i = 1, $cnt = count($filter['province_multi']); $i <= $cnt; $i++) {
                $stand .= ',:province' . $i;
            }
            $stand = ltrim($stand, ',');
            $sql_main .= ' AND rl.receiver_province IN (' . $stand . ') ';
            $i = 1;
            foreach ((array) $filter['province_multi'] as $province) {
                $sql_values[':province' . ($i++)] = $province;
            }
        }
        //城市
        if (isset($filter['city']) && $filter['city'] !== '') {
            $sql_main .= " AND rl.receiver_city = :city ";
            $sql_values[':city'] = $filter['city'];
        }
        //地区
        if (isset($filter['district']) && $filter['district'] !== '') {
            $sql_main .= " AND rl.receiver_district = :district ";
            $sql_values[':district'] = $filter['district'];
        }
        //详细地址

        if (isset($filter['receiver_addr']) && $filter['receiver_addr'] !== '') {
            $filter['receiver_addr'] = str_replace('，', ',', $filter['receiver_addr']);
            $_addr_like_arr = explode(',', $filter['receiver_addr']);
            $_addr_sql = array();
            foreach ($_addr_like_arr as $k => $_like_v) {
                $_addr_sql[] = "rl.receiver_address LIKE :receiver_addr" . $k;
                $sql_values[':receiver_addr' . $k] = '%' . trim($_like_v) . '%';
            }
            $sql_main .= " and (" . join(' or ', $_addr_sql) . ")";
        }

        //缺货状态
        if (isset($filter['is_stock_out']) && $filter['is_stock_out'] !== '') {
            $sql_main .= " AND rl.must_occupy_inv=1 AND rl.lock_inv_status in (:is_stock_out) ";
            $sql_values[':is_stock_out'] = $filter['is_stock_out'];
        }
        //预售单
        if (isset($filter['sale_mode']) && ($filter['sale_mode'] == '0' || $filter['sale_mode'] == '1')) {
            $sale_mode = 'stock';
            if ($filter['sale_mode'] == '1') {
                $sale_mode = 'presale';
            } elseif ($filter['sale_mode'] == '0') {
                $sale_mode = 'stock';
            }
            $sql_main .= " AND rl.sale_mode = :sale_mode ";
            $sql_values[':sale_mode'] = $sale_mode;
        }
        //锁定人
        if (isset($filter['is_lock_person']) && $filter['is_lock_person'] !== '') {
            $s_sql = "select user_code from sys_user where user_name = :user_name";
            $is_lock_person = ctx()->db->getOne($s_sql, array(':user_name' => $filter['is_lock_person']));
            if (empty($is_lock_person)) {
                $sql_main .= " and 1 != 1";
            } else {
                $sql_main .= " AND rl.is_lock_person = :is_lock_person ";
                $sql_values[':is_lock_person'] = $is_lock_person;
            }
        }
        //锁定单
        if (isset($filter['is_lock']) && $filter['is_lock'] !== '') {
            $sql_main .= " AND rl.is_lock = :is_lock ";
            $sql_values[':is_lock'] = $filter['is_lock'];
        }
        //挂起单
        if (isset($filter['is_pending']) && $filter['is_pending'] !== '') {
            $sql_main .= " AND rl.is_pending = :is_pending ";
            $sql_values[':is_pending'] = $filter['is_pending'];
        }
        //问题单
        if (isset($filter['is_problem']) && $filter['is_problem'] !== '') {
            $sql_main .= " AND rl.is_problem = :is_problem ";
            $sql_values[':is_problem'] = $filter['is_problem'];
        }
        //手工单
        if (isset($filter['is_handwork']) && $filter['is_handwork'] !== '') {
            $sql_main .= " AND rl.is_handwork = :is_handwork ";
            $sql_values[':is_handwork'] = $filter['is_handwork'];
        }
        //合并单
        if (isset($filter['is_combine']) && $filter['is_combine'] !== '') {
            $sql_main .= " AND rl.is_combine = :is_combine ";
            $sql_values[':is_combine'] = $filter['is_combine'];
        }
        //拆分单
        if (isset($filter['is_split']) && $filter['is_split'] !== '') {
            $sql_main .= " AND rl.is_split = :is_split ";
            $sql_values[':is_split'] = $filter['is_split'];
        }
        //复制单
        if (isset($filter['is_copy']) && $filter['is_copy'] !== '') {
            $sql_main .= " AND rl.is_copy = :is_copy ";
            $sql_values[':is_copy'] = $filter['is_copy'];
        }
        //商品数量
        if (isset($filter['num_start']) && $filter['num_start'] !== '') {
            $sql_main .= " AND rl.goods_num >= :num_start ";
            $sql_values[':num_start'] = $filter['num_start'];
        }
        if (isset($filter['num_end']) && $filter['num_end'] !== '') {
            $sql_main .= " AND rl.goods_num <= :num_end ";
            $sql_values[':num_end'] = $filter['num_end'];
        }
        //有无发票
        if (isset($filter['is_invoice']) && ($filter['is_invoice'] == '0' || $filter['is_invoice'] == '1')) {
            if ($filter['is_invoice'] == '0') {
                $sql_main .= " AND rl.invoice_title = '' ";
            } else {
                $sql_main .= " AND rl.invoice_title <> '' ";
            }
        }
        //发票抬头
        if (isset($filter['invoice_title']) && $filter['invoice_title'] !== '') {
            $sql_main .= " AND rl.invoice_title LIKE :invoice_title ";
            $sql_values[':invoice_title'] = '%' . $filter['invoice_title'] . '%';
        }
        //发票号
        if (isset($filter['invoice_number']) && $filter['invoice_number'] !== '') {
            $sql_main .= " AND rl.invoice_number = :invoice_number ";
            $sql_values[':invoice_number'] = $filter['invoice_number'];
        }
        //是否含运费
        $contain_express_money = 0;
        if (isset($filter['contain_express_money']) && $filter['contain_express_money'] == '1') {
            $contain_express_money = 1;
        }
        //订单价格
        if (isset($filter['money_start']) && $filter['money_start'] !== '') {
            if ($contain_express_money == 1) {
                $sql_main .= " AND rl.payable_money>= :money_start";
            } else {
                $sql_main .= " AND rl.payable_money - rl.express_money >= :money_start";
            }
            $sql_values[':money_start'] = $filter['money_start'];
        }
        if (isset($filter['money_end']) && $filter['money_end'] !== '') {
            if ($contain_express_money == 1) {
                $sql_main .= " AND rl.payable_money<= :money_end";
            } else {
                $sql_main .= " AND rl.payable_money - rl.express_money <= :money_end";
            }
            $sql_values[':money_end'] = $filter['money_end'];
        }
        //下单时间
        if (isset($filter['record_time_start']) && $filter['record_time_start'] !== '') {
            $sql_main .= " AND rl.record_time >= :record_time_start ";
            $record_time_start = strtotime(date("Y-m-d", strtotime($filter['record_time_start'])));
            if ($record_time_start == strtotime($filter['record_time_start'])) {
                $sql_values[':record_time_start'] = $filter['record_time_start'];
            } else {
                $sql_values[':record_time_start'] = $filter['record_time_start'];
            }
        }
        if (isset($filter['record_time_end']) && $filter['record_time_end'] !== '') {
            $sql_main .= " AND rl.record_time <= :record_time_end ";
            $record_time_end = strtotime(date("Y-m-d", strtotime($filter['record_time_end'])));
            if ($record_time_end == strtotime($filter['record_time_end'])) {
                $sql_values[':record_time_end'] = $filter['record_time_end'] . ' 23:59:59';
            } else {
                $sql_values[':record_time_end'] = $filter['record_time_end'];
            }
        }
        //支付时间
        if (!empty($filter['pay_time_start'])) {
            $sql_main .= " AND rl.pay_time >= :pay_time_start ";
            $pay_time_start = strtotime(date("Y-m-d", strtotime($filter['pay_time_start'])));
            if ($pay_time_start == strtotime($filter['pay_time_start'])) {//只有年月日的情况
                $sql_values[':pay_time_start'] = date("Y-m-d", strtotime($filter['pay_time_start'])) . ' 00:00:00';
            } else {
                $sql_values[':pay_time_start'] = $filter['pay_time_start'];
            }
        }
        if (!empty($filter['pay_time_end'])) {
            $sql_main .= " AND rl.pay_time <= :pay_time_end ";
            $pay_time_end = strtotime(date("Y-m-d", strtotime($filter['pay_time_end'])));
            if ($pay_time_end == strtotime($filter['pay_time_end'])) {
                $sql_values[':pay_time_end'] = $filter['pay_time_end'] . ' 23:59:59';
            } else {
                $sql_values[':pay_time_end'] = $filter['pay_time_end'];
            }
        }
        //发货时间
        if (!empty($filter['delivery_time_start'])) {
            $sql_main .= " AND rl.delivery_time >= :send_time_start ";
            $sql_values[':send_time_start'] = $filter['delivery_time_start'];
        }
        if (!empty($filter['delivery_time_end'])) {
            $sql_main .= " AND rl.delivery_time <= :send_time_end ";
            $sql_values[':send_time_end'] = $filter['delivery_time_end'];
        }
        //计划发货时间
        if (!empty($filter['plan_send_time_start'])) {
            $sql_main .= " AND rl.plan_send_time >= :plan_send_time_start ";
            $sql_values[':plan_send_time_start'] = $filter['plan_send_time_start'] . ' 00:00:00';
        }
        if (!empty($filter['plan_send_time_end'])) {
            $sql_main .= " AND rl.plan_send_time <= :plan_send_time_end ";
            $sql_values[':plan_send_time_end'] = $filter['plan_send_time_end'] . ' 23:59:59';
        }
        //作废时间
        if (!empty($filter['cancel_time_start'])) {
            $sql_main .= " AND rl.cancel_time >= :cancel_time_start ";
            $sql_values[':cancel_time_start'] = $filter['cancel_time_start'];
        }
        if (!empty($filter['cancel_time_end'])) {
            $sql_main .= " AND rl.cancel_time <= :cancel_time_end ";
            $sql_values[':cancel_time_end'] = $filter['cancel_time_end'];
        }
        //确认时间
        if (!empty($filter['check_time_start'])) {
            $sql_main .= " AND rl.check_time >= :check_time_start ";
            $sql_values[':check_time_start'] = $filter['check_time_start'];
        }
        if (!empty($filter['check_time_end'])) {
            $sql_main .= " AND rl.check_time <= :check_time_end ";
            $sql_values[':check_time_end'] = $filter['check_time_end'];
        }

        //通知配货时间
        if (!empty($filter['is_notice_time_start'])) {
            $sql_main .= " AND rl.is_notice_time >= :is_notice_time_start ";
            $sql_values[':is_notice_time_start'] = $filter['is_notice_time_start'];
        }
        if (!empty($filter['is_notice_time_end'])) {
            $sql_main .= " AND rl.is_notice_time <= :is_notice_time_end ";
            $sql_values[':is_notice_time_end'] = $filter['is_notice_time_end'];
        }

        //订单重量
        if (isset($filter['weight_start']) && $filter['weight_start'] !== '') {
            $sql_main .= " AND rl.goods_weigh >= :weight_start ";
            $sql_values[':weight_start'] = $filter['weight_start'];
        }
        if (isset($filter['weight_end']) && $filter['weight_end'] !== '') {
            $sql_main .= " AND rl.goods_weigh <= :weight_end ";
            $sql_values[':weight_end'] = $filter['weight_end'];
        }
        //淘宝卖家备注旗帜
        if (isset($filter['seller_flag']) && $filter['seller_flag'] !== '') {
            $_seller_flag = array_map("intval", explode(',', $filter['seller_flag']));
            $sell_flag_list = join(',', $_seller_flag);
            $sql_main .= " AND rl.seller_flag in({$sell_flag_list})";
        }
        //是否为分销单
        if (isset($filter['exist_fenxiao']) && ($filter['exist_fenxiao'] == '0' || $filter['exist_fenxiao'] == '1')) {
            if ($filter['exist_fenxiao'] == '1') {
                $sql_main .= " AND (rl.is_fenxiao = 1 OR rl.is_fenxiao = 2) ";
                $user_code = load_model('sys/UserTaskModel')->get_user_code();
                $login_type = $this->db->get_value("select login_type from sys_user where user_code = :user_code", array(":user_code" => $user_code));
                if ($login_type == 3) {
                    $fenxiao_code = $this->db->get_value("select custom_code from base_custom where user_code = :user_code", array(":user_code" => $user_code));
                    $sql_main .= " AND rl.fenxiao_code ='{$fenxiao_code}'";
                }
            } else {
                $sql_main .= " AND rl.is_fenxiao = 0 ";
            }
        }

        //增加商品编码和条形码精确查询
        if (isset($filter['exact_code']) && $filter['exact_code'] != '') {
            $arr = explode(',', $filter['exact_code']);
            $exact_code = $this->arr_to_in_sql_value($arr, 'goods_code', $sql_values);
            $exact_barcode = $this->arr_to_in_sql_value($arr, 'barcode', $sql_values);
            $sql_main .= " AND (rr.goods_code IN({$exact_code}) OR gs.barcode IN({$exact_barcode})) ";
        }

        //增加套餐查询
        if (isset($filter['combo_barcode']) && $filter['combo_barcode'] != '') {
            $combo_sku_arr = load_model('prm/GoodsComboModel')->get_combo_sku_by_barcode(trim($filter['combo_barcode']));

            if (!empty($combo_sku_arr)) {
                //$arr = explode(',',"$combo_sku_arr");
                $str = $this->arr_to_in_sql_value($combo_sku_arr, 'combo_sku', $sql_values);
                $sql_main .= " AND rr.combo_sku in ({$str}) ";
            } else {
                $sql_main .= " AND 1=2 ";
            }
        }
        if (isset($filter['no_barcode']) && $filter['no_barcode'] != '') {
            $sku_row = load_model('prm/GoodsBarcodeModel')->get_data_by_barcode($filter['no_barcode']);
            if (!empty($sku_row)) {
                $sql_no_barcode = "select sell_record_code from oms_sell_record_detail where  sku='{$sku_row['sku']}' ";
                $sql_main .= " AND  rl.sell_record_code not in ({$sql_no_barcode}) ";
            }
        }

        //增值服务
        $sql_main .= load_model('base/SaleChannelModel')->get_values_where('rl.sale_channel_code');
        $is_distinct = false;
        $order_by = '';
        $group_by = '';
        if (!empty($filter['barcode']) || !empty($filter['combo_goods_code']) || $is_join === true) {
            $group_by = " group by rl.sell_record_code ";
            if (isset($filter['is_gift']) && $filter['is_gift'] == 0 && $is_join_detail === true) {
                $group_by .= " having sum_gift = 0 or sum_gift is null ";
            }
            $is_distinct = TRUE;
        }
        if (!empty($filter['group_by'])) {
            $group_by = " group by " . $filter['group_by'] . " ";
        }
        if (isset($filter['exact_code']) && !empty($filter['exact_code'])) {
            $group_by = " GROUP BY rl.sell_record_code ";
        }
        if (isset($filter['is_sort']) && $filter['is_sort'] != '') {
            switch ($filter['is_sort']) {
                case 'paid_money_desc':
                    $order_by = " ORDER BY rl.paid_money DESC,rl.sell_record_code DESC ";
                    break;
                case 'paid_money_asc':
                    $order_by = " ORDER BY rl.paid_money ASC,rl.sell_record_code ASC ";
                    break;
                case 'record_time_asc':
                    $order_by = " ORDER BY rl.record_time ASC,rl.sell_record_code ASC ";
                    break;
                case 'record_time_desc':
                    $order_by = " ORDER BY rl.record_time DESC,rl.sell_record_code DESC ";
                    break;
                case 'pay_time_asc':
                    $order_by = " ORDER BY rl.pay_time ASC,rl.sell_record_code ASC ";
                    break;
                case 'pay_time_desc':
                    $order_by = " ORDER BY rl.pay_time DESC,rl.sell_record_code DESC ";
                    break;
                case 'plan_send_time_asc':
                    $order_by = " ORDER BY rl.plan_send_time ASC,rl.sell_record_code ASC ";
                    break;
                case 'plan_send_time_desc':
                    $order_by = " ORDER BY rl.plan_send_time DESC,rl.sell_record_code DESC ";
                    break;
            }
        } else {
            $order_by = " ORDER BY rl.pay_time asc,rl.plan_send_time desc";
        }

        if (!empty($sql_one_main_arr)) {
            foreach ($sql_one_main_arr as $k => $v) {
                $sql_one_main_first = $v;
                $sql_one_main_kk = $k;
                break;
            }
            $bak_sql_main = $sql_main . ' ';
            $sql_main = $bak_sql_main . $sql_one_main_first;
            if (isset($sql_one_values[':' . $sql_one_main_kk])) {
                $sql_values[':' . $sql_one_main_kk] = $sql_one_values[':' . $sql_one_main_kk];
            }
            if (isset($filter['ex_list_tab']) && $filter['ex_list_tab'] !== '') {
                if ($filter['ex_list_tab'] == 'tabs_pay') {
                    $sql_main .= " and rl.order_status = 0 and rl.pay_status = 0";
                }
                if ($filter['ex_list_tab'] == 'tabs_confirm') {
                    $sql_main .= " and rl.order_status = 0 and rl.must_occupy_inv = 1";
                }
                if ($filter['ex_list_tab'] == 'tabs_notice_shipping') {
                    $sql_main .= " and rl.order_status = 1 and rl.shipping_status = 0";
                }
            }
        }

        if ($onlySql) {
            $sql = array('select' => $select, 'from' => $sql_main, 'params' => $sql_values);
            return array('status' => '1', 'data' => $sql, 'message' => '仅返回SQL');
        }

        //对外接口 增量下载
        if (isset($filter['lastchanged']) && $filter['lastchanged'] !== '') {
            $sql_main .= " AND rl.lastchanged > :lastchanged ";
            $sql_values[':lastchanged'] = $filter['lastchanged'];
        }
        if ($un_order_export) {
            $order_by = $order_by === '' ? ' ORDER BY ' : $order_by . ',';
            $order_by .= 'rl.sell_record_id ASC,rr.sell_record_detail_id ASC';
            return $this->sell_record_search_csv($sql_main, $sql_values, $filter, $order_by);
        }

        $sql_main .= $group_by;
        $sql_main .= $order_by;
        if ($is_distinct === TRUE) {
            $select = " DISTINCT " . $select;
            if ($is_join === true && $filter['is_gift'] == 0 && $is_join_detail === true) {
                $select .= ",SUM(rr.is_gift) sum_gift ";
            }
        }

        if (isset($filter['action_type']) && $filter['action_type'] !== '') {
            $select = ' rl.*,rr.* ';

            $sql = "select " . $select . $sql_main;
            $data = load_model('common/BaseModel')->get_all($sql, $sql_values);
        } else {
            $group_by_status = empty($group_by) ? FALSE : TRUE;
            $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, $group_by_status);
        }

        $tbl_cfg = array(
            'base_sale_channel' => array('fld' => 'sale_channel_code,sale_channel_name', 'relation_fld' => 'sale_channel_code+sale_channel_code'),
            'base_shop' => array('fld' => 'shop_name', 'relation_fld' => 'shop_code+shop_code'),
            'base_store' => array('fld' => 'store_name', 'relation_fld' => 'store_code+store_code'),
            'base_pay_type' => array('fld' => 'pay_type_name', 'relation_fld' => 'pay_type_code+pay_code'),
            'base_express' => array('fld' => 'express_name', 'relation_fld' => 'express_code+express_code'),
            'base_area' => array('fld' => 'name as receiver_province_txt', 'relation_fld' => 'id+receiver_province'),
            'base_area#1' => array('fld' => 'name as receiver_city_txt', 'relation_fld' => 'id+receiver_city'),
            'base_area#2' => array('fld' => 'name as receiver_district_txt', 'relation_fld' => 'id+receiver_district'),
        );
        require_model('util/GetDataBySqlRelModel');
        $obj = new GetDataBySqlRelModel();
        $obj->tbl_cfg = $tbl_cfg;
        $data['data'] = $obj->get_data_by_cfg(null, $data['data']);
        $sys_user = load_model("oms/SellRecordOptModel")->sys_user();
        $sell_record_key_arr = array();
        foreach ($data['data'] as $key => &$value) {
            if ($value['is_fenxiao'] == 1 || $value['is_fenxiao'] == 2) {
                $value['fx_express_money'] = $value['fx_express_money'];
            } else {
                $value['fx_express_money'] = '';
            }
            $sell_record_key_arr[$value['sell_record_code']] = $key;
            $value['status_text'] = $this->get_sell_record_tag_img($value, $sys_user);
            $url = "?app_act=oms/sell_record/view&sell_record_code={$value['sell_record_code']}&ref={$filter['ref']}";
            $value['sell_record_code_href'] = "<a href=\"{$url}\">" . $value['sell_record_code'] . "</a>";
            $value['goods_weigh'] = $value['goods_weigh'];
            if ($value['seller_flag'] > 0 && $value['sale_channel_code'] == 'taobao') {
                $value['seller_remark'] = $value['seller_remark'] . " <img src='assets/img/taobao/op_memo_" . $value['seller_flag'] . ".png'/>";
            }

            safe_data($value, 0);
            $value['is_company'] = '';
            if($value['invoice_status'] != '0'){//开票状态
               $value['is_company'] =  empty($value['taxpayers_code'])?'个人':'企业';
               $value['invoice_status'] = '是';
                if ($value['invoice_type'] == 'pt_invoice') {
                    $value['invoice_type'] = '电子发票';
                } elseif ($value['invoice_type'] == 'vat_invoice') {
                    $value['invoice_type'] = '纸质发票';
                }
            }else{
                $value['is_company'] = '';
                $value['invoice_status'] = '否';
                $value['invoice_type'] = '';
                $value['invoice_title'] = '';
                $value['invoice_content'] = '';
                $value['taxpayers_code'] = '';
                $value['invoice_number'] = '';
            }
            
            


            $value['paid_money'] = (double) ($value['paid_money']);
            $value['payable_money'] = (double) ($value['payable_money']);
            //整理订单状态
            $value['status'] = $this->order_status[$value['order_status']];
            $value['status'] .= ' ' . $this->shipping_status[$value['shipping_status']];
            $value['status'] .= ' ' . $this->pay_status[$value['pay_status']];
        }
        if (!empty($sell_record_key_arr)) {
            $this->add_order_tag_by_data($data['data'], $sell_record_key_arr);
            $this->add_order_return_tag_img($data['data'], $sell_record_key_arr);
        }

        if ($filter['ctl_type'] == 'export' && isset($filter['ctl_export_conf']) && $filter['ctl_export_conf'] == 'search_record_list' && !empty($filter['__t_user_code'])) {
            $is_security_role = load_model('sys/UserModel')->is_security_role($filter['__t_user_code']);
            if ($is_security_role === true) {


                $data['data'] = load_model('sys/security/OmsSecurityOptModel')->get_sell_record_decrypt_list($data['data']);
                $log = array('user_id' => 0, 'user_code' => $filter['__t_user_code'], 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => '网络订单', 'yw_code' => '', 'operate_type' => '导出', 'operate_xq' => '订单查询导出解密数据');
                load_model('sys/OperateLogModel')->insert($log);
            }
        }
        //御城河日志
        load_model('common/TBlLogModel')->set_log_multi($data['data'], 'search');
        return $this->format_ret(1, $data);
    }

    private function add_order_return_tag_img(&$data, $sell_record_key_arr) {
        $sell_record_arr = array_keys($sell_record_key_arr);
        $sql_values = array();
        $sell_record_str = $this->arr_to_in_sql_value($sell_record_arr, 'sell_record_code', $sql_values);
        $sql = "select DISTINCT sell_record_code,return_type from oms_sell_return where sell_record_code in({$sell_record_str}) AND return_order_status<>3";
        $return_data = $this->db->get_all($sql, $sql_values);
        $return_type = array();
        foreach ($return_data as $val) {
            $key = $sell_record_key_arr[$val['sell_record_code']];
            if ($val['return_type'] == 1) {
                // $data[$key]['status_text'] .= "<img src='assets/img/state_icon/tui_icon.png' title='存在退款' />";
                $return_type[$key][] = '存在退款';
            } else if ($val['return_type'] == 2) {
                //  $data[$key]['status_text'] .= "<img src='assets/img/state_icon/tui_icon.png' title='存在退货' />";
                $return_type[$key][] = '存在退货';
            } else {
                // $data[$key]['status_text'] .= "<img src='assets/img/state_icon/tui_icon.png' title='存在退款退货' />";
                $return_type[$key][] = '存在退款退货';
            }
//            if($val['return_order_status']==3){
//                 $data[$key]['status_text'] .= "<img src='assets/img/state_icon/fei_icon.png' title='这是飞弹' />"; 
//            }
        }
        if (!empty($return_type)) {
            foreach ($return_type as $key => $type) {
                $type = array_unique($type);
                $return_title = implode(';', $type);
                $data[$key]['status_text'] .= "<img src='assets/img/state_icon/tui_icon.png' title='{$return_title}' />";
            }
        }
    }

    private function add_order_tag_by_data(&$data, $sell_record_key_arr) {
        $sell_record_arr = array_keys($sell_record_key_arr);
        $ret_data = load_model("oms/SellRecordTagModel")->get_tag_by_sell_record($sell_record_arr, 'order_tag', 'sell_record_code,tag_desc');
        if (!empty($ret_data['data'])) {
            foreach ($ret_data['data'] as $val) {
                $key = $sell_record_key_arr[$val['sell_record_code']];
                $data[$key]['order_tag'][] = $val['tag_desc'];
            }
        }
    }

    private function sell_record_search_csv($sql_main, $sql_values, $filter, $order_by) {
        $select = "
    				rl.shop_code,
    			 	rl.deal_code,
    				rl.deal_code_list,
                    rl.sell_record_code,
    				rl.order_status,
    				rl.shipping_status,
    				rl.pay_status,
    				rl.buyer_name,
    				rl.receiver_name,
    				rl.receiver_mobile,
                    rl.receiver_address,
                    rl.receiver_country,
                    rl.receiver_province,
                    rl.receiver_city,
                    rl.receiver_district,
                    rl.receiver_street,
    				rl.store_code,
    				rl.express_code,
                                rl.payable_money,
    				rl.express_no,
    				rl.express_money,
    				rl.fx_express_money,
    				rl.paid_money,
    				rl.invoice_title,
    				rl.seller_remark,
                    rl.buyer_remark,
                    rl.invoice_status,
                                rl.pay_time,
                                rl.confirm_person,
    				rl.store_remark,
    				rl.record_time,
    				rl.delivery_time,
    				rl.settlement_time,
    				rl.is_change_record,
    				rl.is_pending_code,
    				rl.is_pending_memo,
    				rl.sale_channel_code,
        			rl.goods_num,
        			rl.order_remark,
                                rl.is_notice_time,
                                rl.is_fenxiao,
                                rl.fenxiao_name,
                              rl.customer_address_id,
                    rr.goods_code,
                    rr.barcode,
                    rr.sku,
                    rr.spec1_code,
                    rr.spec2_code,

                    rr.num,
    				rr.goods_price,
    				rr.avg_money,
    				rl.real_weigh,
                                rr.fx_amount,rr.is_gift
    		 ";
//     	var_dump($filter);
        if (isset($filter['is_gift']) && $filter['is_gift'] != '') {
            if ($filter['is_gift'] == 0) {
                $master_record_sql = 'select rl.sell_record_code,sum(rr.is_gift) as gift_sum ' . $sql_main . ' group by rl.sell_record_code having gift_sum = 0 ';
            } elseif ($filter['is_gift'] == 1) {
                $master_record_sql = 'select rl.sell_record_code,sum(rr.is_gift) as gift_sum ' . $sql_main . ' group by rl.sell_record_code having gift_sum > 0 ';
            }
            $slave_record_sql = 'select ' . $select . $sql_main . $order_by;
            $sql_main = 'from (' . $master_record_sql . ') as master_record
                        inner join(' . $slave_record_sql . ') as slave_record on slave_record.sell_record_code = master_record.sell_record_code';
            $select = ' slave_record.* ';
        }

        $ret_data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

//     	$ret['data'] = load_model('util/ViewUtilModel')->record_detail_append_goods_info($ret['data']);
//     	var_dump($ret['data']);exit;
        // 	$str = "店铺,交易号,订单号,订单状态,买家昵称,收货人,手机,收货地址,仓库,配送方式,快递单号,快递费,已付款,发票抬头,商家留言,买家留言,仓库留言,下单时间,发货时间,有无退单,换货单,挂起原因,问题原因,商品名称,商品编码,商品条形码,规格1,规格2,数量,吊牌价,均摊金额,重量\n";
        $sell_key_arr = array();
        foreach ($ret_data['data'] as $key => &$value) {
            if ($value['is_fenxiao'] == 1 || $value['is_fenxiao'] == 2) {
                $value['share_money'] = sprintf("%.2f", $value['fx_amount']);
                //分销订单取结算运费
                $value['fx_express_money'] = $value['fx_express_money'];
            } else {
                $value['share_money'] = sprintf("%.2f", $value['avg_money']);
                $value['fx_express_money'] = '';
            }
            $value['delivery_time']=$value['delivery_time']!="0000-00-00 00:00:00"?$value['delivery_time']:"";
            $value['settlement_time']=$value['settlement_time']!="0000-00-00 00:00:00"?$value['settlement_time']:"";
            $value['avg_money'] = sprintf("%.2f", $value['avg_money']);
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
            $value['sale_channel_name'] = oms_tb_val('base_sale_channel', 'sale_channel_name', array('sale_channel_code' => $value['sale_channel_code']));
            $value['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $value['store_code']));
            if ($filter['ctl_export_conf'] !== 'search_record_detail') {
                $value['receiver_province'] = oms_tb_val('base_area', 'name', array('id' => $value['receiver_province']));
                $value['receiver_city'] = oms_tb_val('base_area', 'name', array('id' => $value['receiver_city']));
                $value['receiver_district'] = oms_tb_val('base_area', 'name', array('id' => $value['receiver_district']));
            }
            $value['sale_channel_code'] = oms_tb_val('base_sale_channel', 'sale_channel_name', array('sale_channel_code' => $value['sale_channel_code']));
            $value['order_status'] = $this->order_status[$value['order_status']];
//            $value['spec1_name'] = oms_tb_val('base_spec1', 'spec1_name', array('spec1_code' => $value['spec1_code']));
//            $value['spec2_name'] = oms_tb_val('base_spec2', 'spec2_name', array('spec2_code' => $value['spec2_code']));
            $value['express_name'] = oms_tb_val('base_express', 'express_name', array('express_code' => $value['express_code']));
            //  $value['goods_name'] = oms_tb_val('base_goods', 'goods_name', array('goods_code' => $value['goods_code']));
            $key_arr = array('spec1_name', 'spec2_name', 'goods_name', 'barcode', 'goods_short_name');

            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
            /** 获取spec1_name, spec2_name */
            $value['spec1_name'] = $sku_info['spec1_name'];
            $value['spec2_name'] = $sku_info['spec2_name'];
            $value = array_merge($value, $sku_info);
            $ret_data['data'][$key] = $value;

            //整理订单状态
            $status = $value['order_status'];
            $status .= ' ' . $this->shipping_status[$value['shipping_status']];
            $status .= ' ' . $this->pay_status[$value['pay_status']];
            $value['status'] = $status;

            $is_change_record = ($value['is_change_record'] == 1) ? "是" : "否";
            $value['is_change_record'] = $is_change_record;
//            $sell_return_code = $this->get_return_code_by_sell_record_code($value['sell_record_code']);
//            $is_return = !empty($sell_return_code) ? '有' : "无";
            $sell_key_arr[$value['sell_record_code']][] = $key;
            $value['is_return'] = '无';


            //$deal_code_list = implode(";", explode(",", $value['deal_code_list']));
            //$deal_code_list = iconv('utf-8', 'gbk', $deal_code_list);
            //  $question_reason = $this->get_question_reason($value['sell_record_code']);
            $value['question_reason'] = '';
            //有无退单 问题原因 商品编码
            if($value['invoice_status'] == '0'){
                $value['invoice_title'] = '';
            }
        }
        
        $this->sell_record_search_csv_key_data($sell_key_arr, $ret_data['data']);
        if ($filter['ctl_type'] == 'export' && isset($filter['ctl_export_conf']) && $filter['ctl_export_conf'] == 'search_record_detail' && !empty($filter['__t_user_code'])) {
            $is_security_role = load_model('sys/UserModel')->is_security_role($filter['__t_user_code']);
            if ($is_security_role === true) {
                $ret_data['data'] = load_model('sys/security/OmsSecurityOptModel')->get_sell_record_decrypt_list($ret_data['data']);
                $log = array('user_id' => 0, 'user_code' => $filter['__t_user_code'], 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => '网络订单', 'yw_code' => '', 'operate_type' => '导出明细', 'operate_xq' => '订单查询导出解密数据');
                load_model('sys/OperateLogModel')->insert($log);
            }
        }
        return $this->format_ret(1, $ret_data);
    }

    private function sell_record_search_csv_key_data(&$sell_key_arr, &$ret_data) {
        $sql_values = array();
        $sell_arr = array_keys($sell_key_arr);
        $sell_code_str = $this->arr_to_in_sql_value($sell_arr, 'sell_record_code', $sql_values);
        $sql = "select  sell_record_code from oms_sell_return where sell_record_code in ({$sell_code_str})";
        $return_data = $this->db->get_all($sql, $sql_values);

        foreach ($return_data as $val) {
            $key_arr = $sell_key_arr[$val['sell_record_code']];
            foreach ($key_arr as $key) {
                $ret_data[$key]['is_return'] = '有';
            }
        }
        $label_data = load_model('base/QuestionLabelModel')->get_data();

        $sql_question = "select tag_v,sell_record_code from oms_sell_record_tag where tag_type='problem' AND sell_record_code in ({$sell_code_str})";
        $question_data = $this->db->get_all($sql_question, $sql_values);
        foreach ($question_data as $val) {
            $key_arr = $sell_key_arr[$val['sell_record_code']];
            foreach ($key_arr as $key) {
                $ret_data[$key]['question_reason'] .= isset($label_data[$val['tag_v']]) ? $label_data[$val['tag_v']] : '';
            }
        }
    }

    function get_question_reason($sell_record_code) {
        $sql = "select tag_v from oms_sell_record_tag where tag_type='problem' and sell_record_code='$sell_record_code'";
        $tag_v_arr = $this->db->get_all($sql);
        $question_reason = '';
        if (!empty($tag_v_arr)) {
            foreach ($tag_v_arr as $tag_v) {
                $question_sql = "select question_label_name from base_question_label where question_label_code = '" . $tag_v['tag_v'] . "'";
                $question_label_name = $this->db->get_row($question_sql);
                $question_reason .= isset($question_label_name['question_label_name']) ? $question_label_name['question_label_name'] : '';
            }
            return $question_reason;
        } else {
            return null;
        }
    }

    public function get_return_code_by_sell_record_code($sell_record_code) {
        $sql = "select sell_return_code from oms_sell_return where sell_record_code='$sell_record_code'";
        $sell_return_code = $this->db->get_all($sql);
        return $sell_return_code;
    }

//    public function get_return_order_status($sell_record_code) {
//        $sql = "select return_order_status from oms_sell_return where sell_record_code='$sell_record_code'";
//        $return_order_status = $this->db->get_all($sql);
//        return $return_order_status;
//    }

    public function get_record_by_condition($filter, $select_arr = array()) {
        $sql_values = array();
        $sql_main = " FROM {$this->table} rl  WHERE 1 "; //AND rl.is_fenxiao=0
        $is_normal = '';
        if ($filter['is_normal'] == '1') {//正常
            //缺货
            $is_normal .= " (rl.must_occupy_inv = '1' AND rl.lock_inv_status = '1') ";
            //挂起
            $is_normal .= " AND rl.is_pending = '0' ";
            //设问
            $is_normal .= " AND rl.is_problem = '0' ";
        }
        $sql_main .= " AND ({$is_normal})";

        if (isset($filter['order_status']) && $filter['order_status'] !== '') {
            $sql_main .= " AND rl.order_status = :order_status ";
            $sql_values[':order_status'] = $filter['order_status'];
        }
        if (isset($filter['shipping_status']) && $filter['shipping_status'] !== '') {
            $sql_main .= " AND rl.shipping_status = :shipping_status ";
            $sql_values[':shipping_status'] = $filter['shipping_status'];
        }


        if (isset($filter['pay_status']) && $filter['pay_status'] !== '') {
            if ($filter['pay_status'] != 2) {
                $sql_main .= " AND rl.pay_status = :pay_status ";
                $sql_values[':pay_status'] = $filter['pay_status'];
            } else {
                $sql_main .= " AND (rl.pay_status = :pay_status OR pay_type='cod' )";
                $sql_values[':pay_status'] = $filter['pay_status'];
            }
        }


        if (empty($select_arr)) {
            $select = "rl.*";
        } else {

            $select = "rl." . implode(",rl.", $select_arr);
        }
        $sql_main = "select " . $select . $sql_main;
        $data = $this->db->get_all($sql_main, $sql_values);
        return $data;
    }

    /**
     * Conditional Counting
     * @param $t
     * @return array
     */
    public function count_by($t) {
        $w = ' 1 ';

        switch ($t) {
            case 'all': //Orders recently created within 24 hours
                $time = time() - 60 * 60 * 24;
                $w .= " AND is_problem = 0 AND record_time >= $time";
                break;
            case 'pay':
                $w .= " AND order_status = 0 AND is_problem = 0 AND pay_status = 0 ";
                break;
            case 'confirm':
                $w .= " AND order_status = 0 AND is_problem = 0 AND pay_status = 2 ";
                break;
            case 'print':
                $w .= " AND order_status = 1 AND is_problem = 0 AND shipping_status = 2 AND is_print_express = 0 ";
                break;
            case 'send':
                $w .= " AND order_status = 1 AND is_problem = 0 AND shipping_status = 2 AND is_print_express = 1 AND is_print_sellrecord = 1 ";
                break;
            case 'back':
                $w .= " AND order_status = 1 AND is_problem = 0 AND shipping_status = 7 ";
                break;
        }

        return $this->db->get_value("select count(*) from oms_sell_record where $w");
    }

    /**
     * 保存平台订单外部编码
     * @param $b
     * @return array
     */
    public function td_save($b) {
        $this->begin_trans();
        try {
            foreach ($b as $id => $barcode) {
                $sql = "select * from goods_sku where barcode = :barcode";
                $sku = $this->db->get_row($sql, array('barcode' => $barcode));
                if (empty($sku)) {
                    $sql = "select * from goods_sku where sku = :sku";
                    $sku = $this->db->get_row($sql, array('sku' => $barcode));
                }
                if (empty($sku)) {
                    throw new Exception('保存失败,条码不存在: ' . $barcode);
                }

                $r = $this->db->update('oms_sell_record_detail', array('barcode' => $barcode), array('sell_record_detail_id' => $id));
                if ($r !== true) {
                    throw new Exception('保存失败');
                }
            }

            $this->commit();
            return array('status' => 1, 'message' => '更新成功');
        } catch (Exception $e) {
            $this->rollback();
            return array('status' => -1, 'message' => $e->getMessage());
        }
    }

    public function component($sellRecordCode, $types) {
        $response = array();
        //读取订单
        $response['record'] = $this->get_record_by_code($sellRecordCode);
        if (empty($response['record'])) {
            return $response = array();
        }
        if ($response['record']['invoice_status'] == 1) {
            $response['invoice_data'] = load_model('oms/invoice/OmsSellInvoiceModel')->get_sell_invoice($sellRecordCode);
        }
        $response['record']['goods_num'] = $response['record']['goods_num'] ? $response['record']['goods_num'] : 0;
        //整理订单状态
        if (isset($response['record']['is_pending']) && $response['record']['is_pending'] == 1) {
            $response['record']['status'] = '已挂起';
        } else {
            $response['record']['status'] = $this->order_status[$response['record']['order_status']];
        }

        $response['record']['status'] .= ' ' . $this->shipping_status[$response['record']['shipping_status']];
        $response['record']['status'] .= ' ' . $this->pay_status[$response['record']['pay_status']];
        $response['record']['is_back_txt'] = ' ' . $this->is_back[$response['record']['is_back']];
        $response['record']['sale_channel_name'] = $this->get_sale_channel_name_by_code($response['record']['sale_channel_code']);
        $response['record']['pay_type_name'] = $this->pay_type[$response['record']['pay_type']];
        $response['record']['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $response['record']['shop_code']));
        $response['record']['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $response['record']['store_code']));
        $response['record']['pay_name'] = oms_tb_val('base_pay_type', 'pay_type_name', array('pay_type_code' => $response['record']['pay_code']));
        $response['record']['express_name'] = oms_tb_val('base_express', 'express_name', array('express_code' => $response['record']['express_code']));
        $response['record']['real_weigh'] = $response['record']['real_weigh'] . "（Kg）";
        $response['record']['goods_weigh'] = $response['record']['goods_weigh'] . "（Kg）";
        $ql_arr = load_model('base/QuestionLabelModel')->get_map_data();
        if ($response['record']['is_problem'] > 0) {
            $problem_ret = $this->get_problem_desc($response['record']['sell_record_code']);
            if (isset($problem_ret['tag_v'])) {
                foreach ($problem_ret['tag_v'] as $v) {
                    $problem_ret['tag_name'][] = $ql_arr[$v];
                }
                $response['record']['problem_desc'] = join(' | ', $problem_ret['tag_name']);
            }
        }
        if ($response['record']['is_pending'] > 0) {
            $response['record']['is_pending_desc'] = oms_tb_val("base_suspend_label", "suspend_label_name", array('suspend_label_code' => $response['record']['is_pending_code']));
        }
        if ($response['record']['is_lock'] > 0) {
            $sql = "select user_name from sys_user where user_code = '{$response['record']['is_lock_person']}'";
            $response['record']['is_lock_person_name'] = ctx()->db->getOne($sql);
        }

        //关联的退单
        $response['record']['sell_return_codes'] = array();
        $sql = "select sell_return_code from oms_sell_return where return_order_status<>3 and sell_record_code = :sell_record_code";
        $sell_return_data = ctx()->db->get_all($sql, array(':sell_record_code' => $response['record']['sell_record_code']));
        if (!empty($sell_return_data)) {
            $response['record']['sell_return_codes'] = array_column($sell_return_data, 'sell_return_code');
        }


        //取商品明细时, 读取详情数据
        if (array_search('detail', $types) !== false || array_search('goods_detail', $types) !== false) {
            $response['detail_list'] = $this->get_detail_by_sell_record_code($sellRecordCode, 1);
            if (!empty($response['detail_list'])) {
                foreach ($response['detail_list'] as $key => &$value) {
                    $value['lof_no'] = load_model('oms/DeliverRecordModel')->get_lof_no($value['sell_record_code'], $value['sku']);
                    $value['spec1_arr'] = $this->get_spec1_arr_by_goods_code($value['goods_code']);
                    $value['spec2_arr'] = $this->get_spec2_arr_by_goods_code($value['goods_code']);
                    $key_arr = array('goods_name', 'spec1_code', 'spec2_code', 'spec1_name', 'spec2_name', 'barcode');
                    $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
                    $value = array_merge($value, $sku_info);
                    if (!empty($value['combo_sku'])) {
                        $value['combo_barcode'] = load_model('prm/GoodsComboModel')->get_combo_barcode_by_sku($value['combo_sku']);
                    }
                    $response['detail_list'][$key] = $value;
                }
            }

            $sys_param_arr = load_model('sys/SysParamsModel')->get_val_by_code(array('goods_spec1', 'goods_spec2', 'lof_status'));
            $response['spec_name']['spec1_rename'] = isset($sys_param_arr['goods_spec1']) ? $sys_param_arr['goods_spec1'] : '';
            $response['spec_name']['spec2_rename'] = isset($sys_param_arr['goods_spec2']) ? $sys_param_arr['goods_spec2'] : '';
            $response['lof_status'] = $sys_param_arr['lof_status'];
        }

        //增值税发票，取发票信息
        //    if(array_search('invoice_info', $types) !== false) {
        $sql = "SELECT * FROM oms_sell_invoice WHERE sell_record_code = :sell_record_code ";
        $vat_invoice_arr = $this->db->get_row($sql, array(':sell_record_code' => $sellRecordCode));
        //判断是否已开票
        $response['edit_type']= '';
        if(!empty($vat_invoice_arr)){
            $edit_type = '';
            if ($vat_invoice_arr['is_invoice'] == 1 || ($vat_invoice_arr['is_invoice'] == 2 && $vat_invoice_arr['is_red'] != 2)) {
                $edit_type = 'not_modify';
            }
                $response['invoice_data'] = $vat_invoice_arr;
                $response['edit_type'] = $edit_type;
        }
        

        //订单开票金额
        if ($response['record']['invoice_money'] == 0) {
            $invoice_amount = round($response['record']['paid_money'] - $response['record']['point_fee'] - $response['record']['alipay_point_fee'] - $response['record']['coupon_fee'], 2);
        } else {
            $invoice_amount = $response['record']['invoice_money'];
        }

        $response['record']['invoice_money'] = $invoice_amount;

        //   }
        //var_dump($vat_invoice_arr);die;
        if ($response['record']['is_fenxiao'] == 2) {
            $response['fx_payment_money_detail'] = load_model('fx/PayMoneyDetailModel')->get_fx_money_handle_detail($sellRecordCode);
        }

        $status_info = array();
        //订单流转数据
        $status_info = array();
        //下单时间
        $status_info[1] = array('time' => explode(" ", $response['record']['record_time']));
        //已付款
        if ($response['record']['pay_status'] > 0 && $response['record']['pay_type'] != 'cod') {
            $status_info[2] = array('time' => explode(" ", $response['record']['pay_time']));
        }
        //货到付款
        if ($response['record']['pay_type'] == 'cod') {
            $response['is_cashon'] = 1;
        }
        if ($response['record']['order_status'] == 1 || $response['record']['order_status'] == 5 || ($response['record']['order_status'] == 3 && '0000-00-00 00:00:00' != $response['record']['check_time'])) {
            $status_info[3] = array('time' => explode(" ", $response['record']['check_time']));
        }
        //通知配货
        if ($response['record']['shipping_status'] > 0 && '0000-00-00 00:00:00' != $response['record']['is_notice_time']) {
            //排除手工发货
            $status_info[4] = array('time' => explode(" ", $response['record']['is_notice_time']));
        }
        //已拣货
        $deliver_record_data = array();
        $deliver_record_data = $this->db->get_row("select deliver_record_id,is_deliver from oms_deliver_record where sell_record_code='{$response['record']['sell_record_code']}' and waves_record_id='{$response['record']['waves_record_id']}' and is_cancel=0 ");
        if ($response['record']['waves_record_id'] > 0 && !empty($deliver_record_data)) {
            $ret_waves = load_model('oms/WavesRecordModel')->get_record_by_id($response['record']['waves_record_id']);
            $status_info[5] = array('time' => explode(" ", $ret_waves['record_time']), 'type' => 1);
        }

        //已上传WMS|手工发货|已发货|
        //检测是否“已上传WMS”
        $isWms = FALSE;
        $isWms = load_model('sys/ShopStoreModel')->is_wms_store($response['record']['store_code']);
        if (FALSE !== $isWms) {
            if (strpos($response['record']['order_remark'], '历史订单') !== FALSE) {
                $status_info[7] = array();
                $status_info[9] = array();
            }

            $response['is_wms'] = 1;
            $response['wms_system_code'] = $isWms;
            if ($response['record']['wms_request_time'] != 0) {
                $status_info[7] = array('time' => explode(" ", date('Y-m-d H:i:s', $response['record']['wms_request_time'])));
            }
            //检查“WMS已发货”
            if ($response['record']['shipping_status'] == 4) {
                $status_info[9] = array('time' => explode(" ", $response['record']['delivery_time']));
                if(empty($status_info[7])){
                    $status_info[7] = array('time'=>['0000-00-00','00:00:00']);
                }
            }

            $process = load_model('sys/SysParamsModel')->get_val_by_code(array('order_store_process'));
            $response['order_process'] = $process['order_store_process'];
        } else {
            if ($response['record']['shipping_status'] == 4) {
                if (isset($deliver_record_data['is_deliver']) && $deliver_record_data['is_deliver'] == 1) {
                    $status_info[8] = array('time' => explode(" ", $response['record']['delivery_time']));
                } else {
                    $status_info[6] = array('time' => explode(" ", $response['record']['delivery_time']));
                }
            }
        }
        //网单回写
        if ($response['record']['is_back'] == 1 || $response['record']['is_back'] == 2) {
            $status_info[10] = array('time' => explode(" ", $response['record']['is_back_time']));
        }
        //订单作废
        if ($response['record']['order_status'] == 3) {
            $sql = "SELECT lastchanged FROM oms_sell_record_action WHERE sell_record_code = :sell_record_code AND order_status = :order_status ";
            $cancel_date = $this->db->get_row($sql, array(':sell_record_code' => $sellRecordCode, ':order_status' => $response['record']['order_status']));
            $status_info[11] = array('time' => explode(" ", $cancel_date['lastchanged']));
            $response['data_invalid'] = array('is_invalid' => 1, 'time' => explode(" ", $f_time));
        }
        ksort($status_info);
        $response['status_info'] = $status_info;
        return $response;
    }

    private function compare_arr($arr1, $arr2, $key_arr) {
        $check = true;
        foreach ($key_arr as $key) {
            if ($arr1[$key] != $arr2[$key]) {
                $check = false;
                break;
            }
        }

        return $check;
    }

    public function get_combo_barcode_by_sell_code($sku) {
        if (!empty($sku)) {
            $sql = "select barcode from goods_combo_barcode where sku=:combo_sku";
            $combo_barcode = $this->db->get_value($sql, array(':combo_sku' => $sku));
            return $combo_barcode;
        } else {
            return '';
        }
    }

    public function get_spec1_arr_by_goods_code($goods_code) {
        $sql = "select spec1_code,spec1_name from goods_sku  where goods_code = '$goods_code'  GROUP BY spec1_code";
        return $this->db->get_all($sql);
    }

    public function get_spec2_arr_by_goods_code($goods_code) {
        $sql = "select spec2_code,spec2_name from goods_sku where goods_code = '$goods_code'  GROUP BY spec2_code";
        return $this->db->get_all($sql);
    }

    //保存收货地址
    public function save_component_ship($sell_record_code, $type, $req_data = array()) {
        /*
          print_r($sell_record_code);
          print_r($type);
          print_r($req_data);
         */
        unset($req_data['express_code'], $req_data['express_no'], $req_data['store_code'], $req_data['order_remark'], $req_data['store_remark']);
        $sysuser = load_model("oms/SellRecordOptModel")->sys_user();
        $sql = "select is_lock,is_lock_person,order_status,shipping_status,waves_record_id from oms_sell_record where sell_record_code = :sell_record_code";
        $record = ctx()->db->get_row($sql, array(':sell_record_code' => $sell_record_code));
        if ($record['shipping_status'] >= 4) {
            return $this->format_ret(-1, '', '已发货的订单不能操作');
        }
        $this->begin_trans();
        if (isset($req_data['receiver_country'])) {
            $country = oms_tb_val('base_area', 'name', array('id' => $req_data['receiver_country']));
            $province = oms_tb_val('base_area', 'name', array('id' => $req_data['receiver_province']));
            $city = oms_tb_val('base_area', 'name', array('id' => $req_data['receiver_city']));
            $district = oms_tb_val('base_area', 'name', array('id' => $req_data['receiver_district']));
            $street = oms_tb_val('base_area', 'name', array('id' => $req_data['receiver_street']));

            $req_data['receiver_address'] = $country . ' ' . $province . ' ' . $city . ' ' . $district . ' ' . $street . ' ' . $req_data['receiver_addr'];
        }
        //订单‘确认’、或‘通知配货’后，除了‘订单备注’、‘仓库留言’可编辑外，其它项都不能编辑

        $ret = $this->update($req_data, array('sell_record_code' => $sell_record_code));

        if (!empty($record['waves_record_id'])) {
            $param_deliver = array(':sell_record_code' => $sell_record_code, ':waves_record_id' => $record['waves_record_id']);
            $record_deliver = $this->db->get_row("select deliver_record_id from oms_deliver_record where sell_record_code = :sell_record_code AND waves_record_id=:waves_record_id ", $param_deliver);
            if (!empty($record_deliver)) {
                $this->db->update('oms_deliver_record', $req_data, array('sell_record_code' => $sell_record_code));

                //修改云栈信息地址
                $filer = array('deliver_record_id' => $id);
                $ret = $this->fullupdate_tb_wlb_waybil($filer);
                if ($ret['status'] < 0) {
                    $this->rollback();
                    return $ret;
                }
            }
        }
        if (!empty($req_data['receiver_address']) && $req_data['receiver_address'] !== $record['receiver_address']) {
            //记录订单修改地址日志
            $this->add_action($sell_record_code, "修改地址", $record['receiver_address'] . " 修改为 " . $req_data['receiver_address']);
        }
        $this->commit();

        return $ret;
    }

    public function save_component($sell_record_code, $type, $req_data = array()) {
        $sysuser = load_model("oms/SellRecordOptModel")->sys_user();
//        $sql = "select is_lock,is_lock_person,order_status,shipping_status from oms_sell_record where sell_record_code = :sell_record_code";
//        $record = ctx()->db->get_row($sql, array(':sell_record_code' => $sell_record_code));
        $record = $this->get_record_by_code($sell_record_code);
        //修改订单备注不用考虑锁定状态
        if ($type != 'order_remark') {
            if ($record['is_lock'] == 1 && $sysuser['user_code'] != $record['is_lock_person']) {
                return $this->format_ret(-1, '', '订单已锁定不能操作');
            }
        }
        if ($record['order_status'] == 3) {
            return $this->format_ret(-1, '', '已作废的订单不能操作');
        }
        if ($record['shipping_status'] >= 4) {
            return $this->format_ret(-1, '', '已发货的订单不能操作');
        }
        // $record = $this->get_record_by_code($sell_record_code);

        $this->begin_trans();
        if ($type == 'money') {
            $req_data['express_money'] = empty($req_data['express_money']) ? 0.00 : $req_data['express_money'];
            if ($record['express_money'] != $req_data['express_money']) {
                $upd = array('express_money' => $req_data['express_money']);
                $ret = $this->update($upd, array('sell_record_code' => $sell_record_code));
                if ($ret['status'] == -1) {
                    $this->rollback();
                    return $ret;
                }
                $ret = $this->refresh_record_price($sell_record_code);

                $sql = "select payable_money,paid_money,pay_type,pay_status from oms_sell_record where sell_record_code = :sell_record_code";
                $pay_record = ctx()->db->get_row($sql, array(':sell_record_code' => $sell_record_code));
                if ($pay_record['payable_money'] > $pay_record['paid_money'] && $pay_record['pay_type'] != 'code' && $pay_record['pay_status'] == 2) {
                    $ret = load_model("oms/SellRecordOptModel")->opt_unpay($sell_record_code);
//                    $problem_remark = '修改金额，导致已付款小于应付款，自动设问换货单';
//                    $ret = load_model("oms/SellRecordOptModel")->set_problem_order('CHANGE_GOODS_MAKEUP', $problem_remark, $sell_record_code);
                    if ($ret['status'] < 0) {
                        $this->rollback();
                        return $ret;
                    }
                }
                if ($record['express_money'] != $req_data['express_money']) {
                    $this->add_action($sell_record_code, "修改订单金额", '运费： 运费' . $record['express_money'] . " 修改为  " . $req_data['express_money'] . '元');
                }
                $this->commit();
                return $ret;
            }
        }
        if ($type == 'fx_money') {
            if ($record['fx_express_money'] != $req_data['fx_express_money']) {
                $upd = array('fx_express_money' => $req_data['fx_express_money']);
                $ret = $this->update($upd, array('sell_record_code' => $sell_record_code));
                if ($ret['status'] == -1) {
                    $this->rollback();
                    return $ret;
                }
                if (!empty($req_data['fx_express_money'])) {
                    $this->add_action($sell_record_code, "修改订单分销金额", '订单分销运费： 由' . $record['fx_express_money'] . " 修改为  " . $req_data['fx_express_money'] . '元');
                }
                $this->commit();
                return $ret;
            }
        }
        $key_send_arr = array(
            'receiver_addr' => 'address',
            'receiver_country' => 'country',
            'receiver_province' => 'province',
            'receiver_city' => 'city',
            'receiver_district' => 'district',
            'receiver_street' => 'street',
            'receiver_mobile' => 'tel',
            'receiver_phone' => 'home_tel',
            'receiver_name' => 'name',);


        if (isset($req_data['receiver_country']) || isset($req_data['receiver_province']) || isset($req_data['receiver_addr']) || isset($req_data['receiver_mobile']) || isset($req_data['receiver_name'])) {

            $record_decrypt_info = load_model('sys/security/OmsSecurityOptModel')->get_sell_record_decrypt_info($sell_record_code);
            $new_record_data = array_merge($record, $record_decrypt_info);
            foreach ($key_send_arr as $s_key => $s_val) {
                $customer_address_array[$s_val] = isset($req_data[$s_key]) ? $req_data[$s_key] : $new_record_data[$s_key];
            }
//               
//                $customer_address_array['address'] = $req_data['receiver_addr'];
//                $customer_address_array['country'] = $req_data['receiver_country'];
//                $customer_address_array['province'] = $req_data['receiver_province'];
//                $customer_address_array['city'] = $req_data['receiver_city'];
//                $customer_address_array['district'] = $req_data['receiver_district'];
//                $customer_address_array['street'] = $req_data['receiver_street'];
//                $customer_address_array['tel'] = $req_data['receiver_mobile'];
//                $customer_address_array['home_tel'] = $req_data['receiver_phone'];
//                $customer_address_array['name'] = $req_data['receiver_name'];

            $customer_address_array['customer_code'] = $record['customer_code'];

            $buyer_name = load_model('crm/CustomerOptModel')->get_buyer_name_by_code($record['customer_code'], $record['customer_address_id']);
            if ($buyer_name === false) {
                return $this->format_ret(-1, '', '暂时不能修改，安全解密异常！');
            }
            $customer_address_array['buyer_name'] = $buyer_name;
            $customer_address_array['shop_code'] = $record['shop_code'];
            $ret_create = load_model('crm/CustomerOptModel')->create_customer_address($customer_address_array);
            if ($ret_create['status'] < 1) {
                return $ret_create;
            }
            $req_data['customer_address_id'] = $ret_create['data']['customer_address_id'];

            $customer_address = load_model('crm/CustomerOptModel')->get_customer_address($req_data['customer_address_id']);
            $req_data['receiver_addr'] = $customer_address['address'];
            $req_data['receiver_phone'] = $customer_address['home_tel'];
            $req_data['receiver_name'] = $customer_address['name'];
            $req_data['receiver_mobile'] = $customer_address['tel'];

            $country = oms_tb_val('base_area', 'name', array('id' => $req_data['receiver_country']));
            $province = oms_tb_val('base_area', 'name', array('id' => $req_data['receiver_province']));
            $city = oms_tb_val('base_area', 'name', array('id' => $req_data['receiver_city']));
            $district = oms_tb_val('base_area', 'name', array('id' => $req_data['receiver_district']));
            $street = oms_tb_val('base_area', 'name', array('id' => $req_data['receiver_street']));
            $req_data['receiver_address'] = $country . ' ' . $province . ' ' . $city . ' ' . $district . ' ' . $street . ' ' . $req_data['receiver_addr'];
        }

        //  $ret = $this->update($req_data, array('sell_record_code' => $sell_record_code));
//        if (!empty($req_data['receiver_address']) && $req_data['receiver_address'] !== $record['receiver_address']) {
//            //记录订单修改地址日志
//            $this->add_action($sell_record_code, "修改地址", $record['receiver_address'] . " 修改为 " . $req_data['receiver_address']);
//        }
        //订单‘确认’、或‘通知配货’后，除了‘订单备注’、‘仓库留言’可编辑外，其它项都不能编辑
        if ($type == 'shipping' && ($record['order_status'] == '1' || $record['shipping_status'] >= 1)) {
            $req_data = array(
                "order_remark" => $req_data['order_remark'],
                "store_remark" => $req_data['store_remark'],
            );

            //更新到发货单
            $deliver_record = load_model("oms/DeliverRecordModel")->get_row(array("sell_record_code" => $sell_record_code));
            if ($deliver_record['status'] == '1') {
                $ret_deliver = load_model("oms/DeliverRecordModel")->update($req_data, array("sell_record_code" => $sell_record_code));
            }
        } else {
            if (isset($req_data['receiver_country']) || isset($req_data['receiver_province'])) {
                $country = oms_tb_val('base_area', 'name', array('id' => $req_data['receiver_country']));
                $province = oms_tb_val('base_area', 'name', array('id' => $req_data['receiver_province']));
                $city = oms_tb_val('base_area', 'name', array('id' => $req_data['receiver_city']));
                $district = oms_tb_val('base_area', 'name', array('id' => $req_data['receiver_district']));
                $street = oms_tb_val('base_area', 'name', array('id' => $req_data['receiver_street']));

                $req_data['receiver_address'] = $country . ' ' . $province . ' ' . $city . ' ' . $district . ' ' . $street . ' ' . $req_data['receiver_addr'];
            }

            // $ret = $this->update($req_data, array('sell_record_code' => $sell_record_code));
            if (!empty($req_data['receiver_address']) && $req_data['receiver_address'] !== $record['receiver_address']) {
                //记录订单修改地址日志
                $this->add_action($sell_record_code, "修改地址", $record['receiver_address'] . " 修改为 " . $req_data['receiver_address']);
            }
        }

        $detail = $this->get_detail_list_by_code($sell_record_code);
        //是否重新锁定 如果原先是 需要锁定的 在改仓库的情况 下要重新锁定
        $is_relock_lof = 0;
        if (!empty($req_data['store_code']) && $req_data['store_code'] != $record['store_code'] && $record['must_occupy_inv'] == 1 && $record['order_status'] != 3) {
            $is_relock_lof = 1;
        }
        //$is_relock_lof = 1;


        if ($is_relock_lof) {
            //强制设置状态
            $ret_1 = load_model("oms/SellRecordOptModel")->lock_detail($record, $detail, 0, 1); //释放锁定
            if ($ret_1['status'] < 1) {
                $this->rollback();
                return $ret_1;
            }
        }


        if (isset($req_data['seller_remark'])) {
            $req_data['is_seller_remark'] = empty($req_data['seller_remark']) ? 0 : 1;
        }
        $where = " sell_record_code='{$sell_record_code}'  ";
        $check_update = 0;
        if ($is_relock_lof === 1 || isset($req_data['receiver_phone'])) {
            $where .= " AND order_status = 0"; //未确认
            $check_update = 1;
        }
        $ret = $this->update($req_data, $where);

        if ($ret['status'] < 1) {
            $this->rollback();
            return $ret;
        }
        if ($check_update == 1) {
            $run_num = $this->affected_rows();
            if ($run_num < 1) {
                $c_record = $this->get_record_by_code($sell_record_code, 'order_status');
                if ($c_record['order_status'] <> 0) {
                    return $this->format_ret(-1, '', '单据' . $sell_record_code . '已经确认，不能修改');
                }
            }
        }

        if ($is_relock_lof) {
            $old_store = get_store_name_by_code($record['store_code']);
            $record['store_code'] = $req_data['store_code'];
            foreach ($detail as &$dd) {
                $dd['lock_num'] = 0;
            }
            load_model("oms/SellRecordOptModel")->set_sell_record_is_lock($sell_record_code, false);
            $ret_2 = load_model("oms/SellRecordOptModel")->lock_detail($record, $detail, 1, 1); //重新锁定
            if ($ret_2['status'] < 1) {
                $this->rollback();
                return $ret_2;
            }
            if ($type != "store_code") {
                $this->add_action($sell_record_code, "修改仓库", $old_store . "修改为" . get_store_name_by_code($req_data['store_code']));
            }
        }


        if ($type == 'baseinfo' && ($req_data['seller_remark'] != $record['seller_remark']) && isset($req_data['seller_remark'])) {
            $this->add_action($sell_record_code, "修改商家备注", "修改为" . $req_data['seller_remark']);
        }

        if ($type == 'shipping' && ($req_data['receiver_name'] != $record['receiver_name']) && isset($req_data['receiver_name'])) {
            $this->add_action($sell_record_code, "修改收货人", $record['receiver_name'] . " 修改为 " . $req_data['receiver_name']);
        }

        if ($type == 'shipping' && ($req_data['receiver_mobile'] != $record['receiver_mobile']) && isset($req_data['receiver_mobile'])) {
            $this->add_action($sell_record_code, "修改手机", $record['receiver_mobile'] . " 修改为 " . $req_data['receiver_mobile']);
        }
        if ($type == 'shipping' && ($req_data['receiver_phone'] != $record['receiver_phone']) && isset($req_data['receiver_phone'])) {
            $this->add_action($sell_record_code, "修改固定电话", $record['receiver_phone'] . " 修改为 " . $req_data['receiver_phone']);
        }
        if ($type == 'shipping' && ($req_data['receiver_zip_code'] != $record['receiver_zip_code']) && isset($req_data['receiver_zip_code'])) {
            $this->add_action($sell_record_code, "修改邮编", $record['receiver_zip_code'] . " 修改为 " . $req_data['receiver_zip_code']);
        }

        if ($type == 'shipping' && ($req_data['express_code'] != $record['express_code']) && isset($req_data['express_code'])) {
            $old_express_name = oms_tb_val('base_express', 'express_name', array('express_code' => $record['express_code']));
            $new_express_name = oms_tb_val('base_express', 'express_name', array('express_code' => $req_data['express_code']));
            $this->add_action($sell_record_code, "修改快递方式", $old_express_name . " 修改为 " . $new_express_name);
        }
        if ($type == 'shipping' && $req_data['express_no'] != $record['express_no'] && isset($req_data['express_no'])) {
            $this->add_action($sell_record_code, "修改快递单号", $record['express_no'] . " 修改为 " . $req_data['express_no']);
        }
        if ($type == 'shipping' && $req_data['order_remark'] != $record['order_remark'] && isset($req_data['order_remark'])) {
            $this->add_action($sell_record_code, "修改订单备注", $record['order_remark'] . " 修改为 " . $req_data['order_remark']);
        }
        if ($type == 'shipping' && $req_data['store_remark'] != $record['store_remark'] && isset($req_data['store_remark'])) {
            $this->add_action($sell_record_code, "修改仓库留言", $record['store_remark'] . " 修改为 " . $req_data['store_remark']);
        }
        $this->commit();
        return $ret;
    }

    /**
     * 读取单个订单, 根据订单ID
     * @param $sellRecordCode
     * @return mixed
     */
    public function get_record_list_by_ids($ids) {
        $str = implode(',', $ids);
        if (empty($str)) {
            return array();
        }

        return $this->db->get_all("select * from oms_sell_record where sell_record_code in ($str)");
    }

    /**
     * 更新订单 物流信息
     * @param $sell_record_code
     * @return
     */
    public function update_express($sell_record_code) {

        $data = array('express_no' => '', 'is_print_express' => '0');
        $ret = $this->update($data, array('sell_record_code' => $sell_record_code));
    }

    /**
     * 读取单个订单, 根据订单ID
     * @param $sellRecordCode
     * @return mixed
     */
    public function get_detail_by_id($sellRecordDetailId) {
        return $this->db->get_row("select * from oms_sell_record_detail where sell_record_detail_id = :id", array('id' => $sellRecordDetailId));
    }

    /**
     * 读取订单所有明细, 根据订单ID
     * @param $sellRecordCode
     * @param bool $is_td
     * @param int $source
     * @return mixed
     */
    public function get_detail_list_by_sell_record_code($sellRecordCode, $is_td = false, $sale_channel_code = '') {
        return $this->format_ret(-1, '', '此方法已作废');
        $data = $this->db->get_all("select * from oms_sell_record_detail where sell_record_code = :sell_record_code", array('sell_record_code' => $sellRecordCode));

        if ($is_td) { //根据当前设计, 通过is_problem来判断是否平台订单列表
            switch ($sale_channel_code) {
                case 'taobao': //淘宝
                    foreach ($data as &$detail) {
                        $sql = "select * from oms_taobao_record_detail where tid = :tid and oid = :oid";
                        $d = $this->db->get_row($sql, array('tid' => $detail['deal_code'], 'oid' => $detail['sub_deal_code']));
                        if (!empty($d)) {
                            $detail['pic_path'] = $d['pic_path'];
                            $detail['goods_name'] = $d['title'];
                            $detail['sku_properties_name'] = $d['sku_properties_name'];
                            $detail['num_iid'] = $d['num_iid'];
                        }
                    }
                    break;
            }
        }


        return $data;
    }

    /**
     * 读取订单所有操作日志, 根据订单ID
     * @param $sellRecordCode
     * @return mixed
     */
    public function get_action_list_by_sell_record_code($sellRecordCode) {
        return $this->db->get_all("select * from oms_sell_record_action where sell_record_code = :sell_record_code order by sell_record_action_id desc", array('sell_record_code' => $sellRecordCode));
    }

    /**
     * 重新处理订单价格
     * @param $sell_record_code
     * @return array
     * @throws Exception
     */
    public function refresh_record_price($sell_record_code) {

        $record = $this->get_record_by_code($sell_record_code);

        if (empty($record)) {
            throw new Exception('执行refresh_order_price失败:查询订单信息失败');
        }

        $sql = "SELECT SUM(goods_price*num) AS goods_money";
        $sql .= ",SUM(num-return_num) AS num";
        $sql .= ",COUNT(DISTINCT(sku)) AS sku_num";
        $sql .= ",SUM(goods_weigh*(num-return_num)) AS goods_weigh";
        $sql .= ",SUM(avg_money) AS avg_money";
        $sql .= " FROM oms_sell_record_detail";
        $sql .= " WHERE sell_record_code= :sell_record_code and num>return_num";

        $record_new = $this->db->get_row($sql, array(':sell_record_code' => $sell_record_code));

        //订单应付款
        $info['payable_money'] = $record_new['avg_money'] + $record['express_money'] + $record['delivery_money'];
        //商品总额
        $info['goods_money'] = $record_new['goods_money'];
        //商品总数量
        $info['num'] = $record_new['num'];
        //商品SKU数量
        $info['sku_num'] = $record_new['sku_num'];
        //商品重量
        $info['goods_weigh'] = $record_new['goods_weigh'];

        $result = $this->db->update('oms_sell_record', $info, array("sell_record_code" => $sell_record_code));

        return $this->format_ret(1, $info);
    }

    /**
     * 计算订单中商品的均摊金额
     * @param $sell_record_code
     * @param $order_item
     * @param $goods_item
     * @param $cal_item
     */
    public function _cal_order_info_share_price($sell_record_code, $order_item, $goods_item, $cal_item) {

        //获取总的均摊金额
        $sql = "SELECT `{$order_item}` FROM oms_sell_record WHERE sell_record_code= :id";
        $item_amount = $this->db->get_value($sql, array(':id' => $sell_record_code));

        $sql = "SELECT sell_record_detail_id, sell_record_code,`{$goods_item}`,`{$cal_item}` FROM oms_sell_record_detail WHERE sell_record_code= :id AND is_gift=0";
        $goods = $this->db->get_all($sql, array(':id' => $sell_record_code));

        $share_amount = $rest_share_amount = 0;

        //计算均摊比率
        foreach ((array) $goods as $_goods) {
            $share_amount += $_goods[$goods_item];
        }
        $rest_share_amount = $item_amount;


        foreach ((array) $goods as $_key => $_goods) {
            if ($share_amount > 0)
                $_goods[$cal_item] = round(($_goods[$goods_item] / $share_amount * $item_amount), 2);
            else
                $_goods[$cal_item] = 0;
            $rest_share_amount -= $_goods[$cal_item];
            $goods[$_key] = $_goods;
        }
        if ($i = count($goods) > 0) {
            $goods[$i - 1][$cal_item] += $rest_share_amount;
            $goods[$i - 1][$cal_item] = round($goods[$i - 1][$cal_item], 2);
        }

        foreach ((array) $goods as $_goods) {
            $sql = "UPDATE oms_sell_record_detail SET `{$cal_item}`='{$_goods[$cal_item]}' WHERE sell_record_detail_id='{$_goods['sell_record_detail_id']}'";
            $this->db->query($sql);
        }
    }

    function return_value($status, $message = '', $data = '') {
        $message = $status == 1 && $message == '' ? '操作成功' : $message;

        return array('status' => $status, 'message' => $message, 'data' => $data);
    }

    function add_time() {
        return date("Y-m-d H:i:s");
    }

    /**
     * create new sell_record code
     * @return string
     */
    function new_code() {
        $num = $this->db->get_seq_next_value('oms_sell_record_seq');
        $time = date('ymd', time());

        $num = sprintf('%06s', $num);
        $length = strlen($num);
        $num = substr($num, $length - 6, 6);
        $str = $time . $num;

        $str = $this->barcode_check_code($str);
        return $str;
    }

    /**
     * 获得13位barcode的校验码
     * @param string $code
     * @return string|string
     */
    function barcode_check_code($code) {
        $ncode = $code;
        $length = strlen($ncode);
        $lsum = $rsum = 0;
        for ($i = 0; $i < $length; $i++) {
            if ($i % 2) {
                $lsum += intval($ncode[$i]);
            } else {
                $rsum += intval($ncode[$i]);
            }
        }
        $tsum = $lsum * 3 + $rsum;
        $code .= (10 - ($tsum % 10)) % 10;
        return $code;
    }

    /**
     * 写入订单日志
     * @param $sellRecordCode
     * @param $actionName
     * @param string $actionNote
     * @param bool $isDeamon
     * @throws Exception
     */
    function add_action($sellRecordCode, $actionName, $actionNote = '', $isDeamon = false) {
        load_model('oms/SellRecordActionModel')->add_action($sellRecordCode, $actionName, $actionNote, $isDeamon);
    }

    function add_action_to_api($channel, $shopCode, $dealCode, $status) {
        load_model('oms/SellRecordActionModel')->add_action_to_api($channel, $shopCode, $dealCode, $status);
    }

    /**
     * 验证快递单号合法性
     * @param $express_code
     * @param $express_no
     * @return bool
     * @throws Exception
     */
    function check_express_no($express_code, $express_no) {
        $rule = '';

        $sql = "select * from base_express where express_code = :code";
        $express = $this->db->get_row($sql, array(":code" => $express_code));
        if (empty($express)) {
            return true;
        }

        if (empty($express['reg_mail_no'])) {
            $sql = "select rule from base_express_company where company_code = :company_code";
            $rule = $this->db->get_value($sql, array(":company_code" => $express['company_code']));
            if (empty($rule)) {
                return true;
            }
        } else {
            $rule = $express['reg_mail_no'];
        }

        if (preg_match('/' . $rule . '/', $express_no)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 快递单号连续匹配计算
     */
    function get_next_express_no($express_no, $express_code) {
        $first_word = '';

        if (strtolower($express_code) == 'yto') {   //圆通
            //如果是圆通 判断是否已字母开头 进行特殊处理
            $tmp_str = substr($express_no, 0, 1); //	第一位字符
            if (ord($tmp_str) >= 65 && ord($first_word) <= 122) {
                //	是字母
                $express_no = substr($express_no, 1);
                $first_word = $tmp_str;
            }
        } else if (strtolower($express_code) == 'sf') { //顺丰
            if (strlen($express_no) > 0) {
                $num4 = 0;
                $num = trim($express_no + 10);
                //单号开头为0的情况
                if (strlen($num) == 11) {
                    $num = '0' . $num;
                }
                for ($ii = 1; $ii <= 8; $ii++) {
                    $num1 = substr($num, 12 - $ii - 1, 1);
                    $num2 = $num1 * ($ii * 2 - 1);
                    $num3 = (int) ($num2 / 10) + ($num2 % 10);
                    $num4 = $num4 + $num3;
                }

                $num5 = -floor(-$num4 / 10) * 10 - $num4;
                //$num5 =10 - substr($num4,strlen($num4)-1,1);
                // $num5 =substr($num5,strlen($num5)-1,1);
                $num6 = ($num5 % 10);
                $express_no = substr($num, 0, 11) . ($num6);
                return $express_no;
            }
            return '';
        } else if (in_array(strtolower($express_code), array('ems', 'eyb', 'postb'))) { //EMS
            $first = substr($express_no, 0, 2);
            if (strtolower($express_code) == 'postb' && (int) $first == 96) {
                $str = substr($express_no, 2, 11);
                $str += 1;
                return $first . $str;
            } else {
                $str = substr($express_no, 2, 8);
                $lsst = substr($express_no, 11, 2);
                $str = $str + 1;

                while (strlen($str) < 8) {
                    $str = '0' . $str;
                }

                $f1 = substr($str, 0, 1) * 8 + substr($str, 1, 1) * 6 + substr($str, 2, 1) * 4 + substr($str, 3, 1) * 2 + substr($str, 4, 1) * 3 + substr($str, 5, 1) * 5 + substr($str, 6, 1) * 9 + substr($str, 7, 1) * 7;

                if (11 - ($f1 % 11) == 11) {
                    $check_no = 5;
                } else {
                    if (11 - ($f1 % 11) == 10) {
                        $check_no = 0;
                    } else {
                        $check_no = 11 - ($f1 % 11);
                    }
                }

                return $first . $str . $check_no . $lsst;
            }
        } else if (strtolower($express_code) == 'gzlt') {   //飞远快递
            if (strlen($express_no) > 0) {
                $str_1 = substr($express_no, 0, 1); //	第一位字符
                $str_2 = substr($express_no, 1, 10);
                $str_3 = $str_2 + 1;

                $str = $str_1 . $str_3;
                return $str;
            }
            return '';
        } else if (strtolower($express_code) == 'qfkd') {   //全峰快递
            if (strlen($express_no) > 0) {
                $str_3 = $express_no + 1;
                return strval($str_3);
            }
            return '';
        } else if (strtolower($express_code) == 'zjs') {  //宅急送
            if (strlen($express_no) > 0) {
                $str_1 = substr($express_no, -1, 1); //	最后一位数字
                if ($str_1 == 6 || $str_1 == 7 || $str_1 == 8 || $str_1 == 9) {
                    $str_1 = 0;
                } else {
                    $str_1 = $str_1 + 1;
                }

                $str_2 = substr($express_no, 0, -1);
                $str_2 = $str_2 + 1;

                $str = $str_2 . $str_1;

                return $str;
            }
            return '';
        }

        $length = strlen($express_no);
        //排除已经使用的快单单号
        do {
            $ret = number_format(++$express_no, 0, '', '');
            $is_exist = $this->check_express_no_exist($express_code, $ret);
        } while ($is_exist === true);

        $ret_len = strlen($ret);
        if ($ret_len < $length) {
            // 比原来位数少(转型计算后 前置0消失) 自动布足0
            for ($ii = 1; $ii <= $length - $ret_len; $ii++) {
                $ret = '0' . $ret;
            }
        }

        return $first_word . $ret;
    }

    /**
     * 检查快递单号是否重复
     * @param $express_code
     * @param $express_no
     * @param string $id
     * @return bool
     * @throws Exception
     */
    function check_express_no_exist($express_code, $express_no, $id = '') {
        $sql = "select sell_record_code from oms_sell_record where express_code = :express_code and express_no = :express_no";
        $arr = array(':express_code' => $express_code, ':express_no' => $express_no);
        if ($id !== '') {
            $sql .= ' and sell_record_code != :sell_record_code';
            $arr[':sell_record_code'] = $id;
        }
        $data = $this->db->get_value($sql, $arr);
        if ($data) {
            return true;
        } else {
            return false;
        }
    }

    //新增订单
    function add($request) {
        $shop = $this->db->get_row("select * from base_shop where shop_code = :code", array('code' => $request['shop_code']));
        if (empty($shop)) {
            return $this->format_ret(-1, '', '商店不存在, 请完善商店api参数nick字段');
        }

//        $request['deal_code'] = load_model("oms/SellRecordOptModel")->get_guid_deal_code($request['deal_code']);

        $country = oms_tb_val('base_area', 'name', array('id' => $request['receiver_country']));
        $province = oms_tb_val('base_area', 'name', array('id' => $request['receiver_province']));
        $city = oms_tb_val('base_area', 'name', array('id' => $request['receiver_city']));
        $district = oms_tb_val('base_area', 'name', array('id' => $request['receiver_district']));
        $street = oms_tb_val('base_area', 'name', array('id' => $request['receiver_street']));

        //会员信息
        $customer = array(
            'customer_name' => $request['buyer_name'],
            'shop_code' => $request['shop_code'],
            'source' => $shop['sale_channel_code'],
            'address' => $request['receiver_addr'],
            'country' => $request['receiver_country'],
            'province' => $request['receiver_province'],
            'city' => $request['receiver_city'],
            'district' => $request['receiver_district'],
            'street' => $request['receiver_street'],
            'zipcode' => $request['receiver_zip_code'],
            'tel' => $request['receiver_mobile'],
            'home_tel' => $request['receiver_phone'],
            'customer_sex' => 3,
            'name' => $request['receiver_name'],
            'is_add_time' => date('Y-m-d H:i:s'),
        );
        $deal_code = load_model('oms/SellRecordOptModel')->get_guid_deal_code($request['deal_code']);
        $new_sell_record_code = $this->new_code();

        $data = array(
            'sell_record_code' => $new_sell_record_code,
            'deal_code' => $deal_code,
            'deal_code_list' => $request['deal_code'],
            'sale_channel_code' => $request['sale_channel_code'],
            'order_status' => '0',
            'shipping_status' => '0',
            'store_code' => $request['store_code'],
            'shop_code' => $request['shop_code'],
            'pay_type' => $request['pay_type'],
            'must_occupy_inv' => $request['pay_type'] == 'cod' ? 1 : 0,
            'lock_inv_status' => $request['pay_type'] == 'cod' ? 1 : 0,
            'pay_code' => $request['pay_code'],
            'pay_status' => '0',
            'customer_code' => $request['buyer_code'],
            'buyer_name' => $request['buyer_name'],
            'receiver_name' => $request['receiver_name'],
            'receiver_country' => $request['receiver_country'],
            'receiver_province' => $request['receiver_province'],
            'receiver_city' => $request['receiver_city'],
            'receiver_district' => $request['receiver_district'],
            'receiver_street' => $request['receiver_street'],
            //  'receiver_address' => $country . ' ' . $province . ' ' . $city . ' ' . $district . ' ' . $street . ' ' . $request['receiver_addr'],
            'receiver_addr' => $request['receiver_addr'],
            'receiver_zip_code' => $request['receiver_zip_code'],
            'receiver_mobile' => $request['receiver_mobile'],
            'receiver_phone' => $request['receiver_phone'],
            'express_code' => $request['express_code'],
            'express_money' => $request['express_money'],
            'order_remark' => $request['order_remark'],
            'record_time' => $request['record_time'],
            'create_time' => date('Y-m-d H:i:s'),
            'is_handwork' => '1',
            'is_lock' => 1, //建手工单后，默认，订单自动锁定；通知配货后，自动解锁
            'is_lock_person' => ctx()->get_session('user_code'),
            'payable_money' => $request['express_money'],
            'store_remark' => $request['store_remark'],
        );
        if (isset($request['fx_or_oms_sell']) && $request['fx_or_oms_sell'] == 'fx_sell_record') {
            if ($request['fenxiao_code'] == '') {
                if ($request['fenxiao_name'] != '') {
                    $custom_arr = load_model('base/CustomModel')->get_by_name($request['fenxiao_name']);
                    if (empty($custom_arr['data'])) {
                        return $this->format_ret('-1', '', '请输入正确的供应商');
                    }
                    $request['fenxiao_code'] = $custom_arr['data']['custom_code'];
                } else {
                    return $this->format_ret('-1', '', '请输入正确的供应商');
                }
            }
            $data['fenxiao_name'] = $request['fenxiao_name'];
            $data['fenxiao_code'] = $request['fenxiao_code'];
            $data['is_fenxiao'] = 2;
            $data['fx_express_money'] = $request['express_money'];
            $data['payable_money'] -= $request['express_money'];
            $data['express_money'] = 0;
//            $data['fenxiao_power'] = $this->check_fenxiao_power($data);
//            if (empty($request['express_money'])) {
//                $data['fx_express_money'] = $this->get_fx_express_money($data);
//            }
        }
        $this->begin_trans();
        try {
            /*
              $result = $this->db->get_row(
              "SELECT deal_code FROM oms_sell_record WHERE deal_code = :deal_code",
              array('deal_code' => $request['deal_code'])
              );
              if(!empty($result)){
              return $this->format_ret(-1,'','已存在相同交易号的订单');
              } */
            //添加用户信息
            $ret = load_model("crm/CustomerOptModel")->handle_customer($customer);
            if ($ret['status'] < 1) {
                $this->rollback();
                return $ret;
            }
            $customer_address = load_model('crm/CustomerOptModel')->get_customer_address($ret['data']['customer_address_id']);
            $data['receiver_addr'] = $customer_address['address'];
            $data['receiver_phone'] = $customer_address['home_tel'];
            $data['receiver_name'] = $customer_address['name'];
            $data['receiver_mobile'] = $customer_address['tel'];
            $data['buyer_name'] = load_model('crm/CustomerOptModel')->get_customer_name($ret['data']['customer_code']);


//                $country = oms_tb_val('base_area', 'name', array('id' => $data['receiver_country']));
//                $province = oms_tb_val('base_area', 'name', array('id' => $data['receiver_province']));
//                $city = oms_tb_val('base_area', 'name', array('id' => $data['receiver_city']));
//                $district = oms_tb_val('base_area', 'name', array('id' => $data['receiver_district']));
//                $street = oms_tb_val('base_area', 'name', array('id' => $data['receiver_street']));
            $data['receiver_address'] = $country . ' ' . $province . ' ' . $city . ' ' . $district . ' ' . $street . ' ' . $data['receiver_addr'];

            $data['customer_address_id'] = $ret['data']['customer_address_id'];
            $data['customer_code'] = $ret['data']['customer_code'];

            $result = $this->db->insert('oms_sell_record', $data);
            if ($result !== true) {
                $this->rollback();
                return $this->format_ret(-1, '', '保存订单出错');
            }
            $sell_record_id = $this->db->insert_id();
            $sell_record_code = $data['sell_record_code'];

            //记录订单转入日志
            $this->add_action($sell_record_code, '新增');
            $this->add_action_to_api($data['sale_channel_code'], $data['shop_code'], $data['deal_code'], 'convert');


            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
        //设问
        //load_model('oms/SellProblemModel')->set_problem($data);

        $ret_plan_send_time = load_model('oms/SellRecordOptModel')->set_sell_plan_send_time($sell_record_code, 0);
        if ($ret_plan_send_time['status'] < 0) {
            return $ret_plan_send_time;
        }

        return $this->format_ret(1, $new_sell_record_code);
    }

    public function spec_list_by_goods($sellRecordCode, $goodsCode) {
        $spec1List = array();
        $spec2List = array();
        $skuList = array();

        $sql = "select * from base_goods where goods_code = :code";
        $goods = $this->db->get_row($sql, array('code' => $goodsCode));
        if (empty($goods)) {
            return array('spec1_list' => $spec1List, 'spec2_list' => $spec2List, 'sku_list' => $skuList, 'message' => '商品不存在');
        }

        $sql = "select * from goods_sku where goods_code = :code";
        $skus = $this->db->get_all($sql, array('code' => $goodsCode));
        if (empty($skus)) {
            return array('spec1_list' => $spec1List, 'spec2_list' => $spec2List, 'sku_list' => $skuList, 'message' => 'SKU不存在');
        }

        foreach ($skus as $sku) {
            $sql = "select * from base_spec1 where spec1_code = :code";
            $spec1 = $this->db->get_row($sql, array('code' => $sku['spec1_code']));
            $spec1Name = empty($spec1) ? $sku['spec1_code'] : $spec1['spec1_name'];
            $spec1List[$sku['spec1_code']] = $spec1Name;

            $sql = "select * from base_spec2 where spec2_code = :code";
            $spec2 = $this->db->get_row($sql, array('code' => $sku['spec2_code']));
            $spec2Name = empty($spec2) ? $sku['spec2_code'] : $spec2['spec2_name'];
            $spec2List[$sku['spec2_code']] = $spec2Name;

            $skuList[$sku['spec1_code'] . '-' . $sku['spec2_code']] = $sku['sku'];
        }

        return array('spec1_list' => $spec1List, 'spec2_list' => $spec2List, 'sku_list' => $skuList);
    }

    /**
     * 读取单个订单, 根据订单sn
     * @param $sellRecordCode
     * @return mixed
     */
    public function get_record_by_code($sell_record_code, $fld = '*') {
        $result = $this->db->get_row("select {$fld} from oms_sell_record where sell_record_code = :sell_record_code", array('sell_record_code' => $sell_record_code));
        return $result;
    }

    /**
     * 读取多个订单，根据订单sn
     * @param $sell_record_arr
     * @param string $id_map
     * @param string $fld
     * @return array|bool
     */
    public function get_record_by_code_list($sell_record_arr, $fld = '*') {
        $sql_str = $this->arr_to_in_sql_value($sell_record_arr, 'sell_record', $sql_values);
        $result = $this->db->get_all("select {$fld} from oms_sell_record where sell_record_code in({$sql_str})", $sql_values);
        return $result;
    }

    public function get_detail_list_by_code($sell_record_code, $id_map = '', $fld = '*') {
        $result = $this->db->get_all("select {$fld} from oms_sell_record_detail where is_delete =0 and sell_record_code = :sell_record_code", array('sell_record_code' => $sell_record_code));
        $result_2 = array();
        if ($id_map != '') {
            $id_map_arr = explode(',', $id_map);
            foreach ($result as $sub_result) {
                $_tk_arr = array();
                foreach ($id_map_arr as $t_id) {
                    $_tk_arr[] = $sub_result[$t_id];
                }
                $_tk = join(',', $_tk_arr);
                $result_2[$_tk] = $sub_result;
            }
            return $result_2;
        }
        return $result;
    }

    //获取商品明细（与赠品合并）
    public function get_detail_list_group_by_code($sell_record_code, $is_fenxiao = NULL, $source = '') {
        $result = $this->db->get_all("select * from oms_sell_record_detail where is_delete =0 and sell_record_code = :sell_record_code", array('sell_record_code' => $sell_record_code));
        $detail = array();
        foreach ($result as $row) {
            $key = $row['deal_code'] . '_' . $row['sku'];
            //分销订单的均摊金额取分销结算金额
            if ($source == '') {
                $row['avg_money'] = ($is_fenxiao == 1 || $is_fenxiao == 2) ? $row['fx_amount'] : $row['avg_money'];
            }
            if (!isset($detail[$key])) {
                $key_arr = array('spec1_code', 'spec1_name', 'spec2_code', 'spec2_name', 'barcode', 'goods_name');
                $sku_info = load_model('goods/SkuCModel')->get_sku_info($row['sku'], $key_arr);
                $row = array_merge($row, $sku_info);
                $detail[$key] = $row;
            } else {
                $detail[$key]['num'] += $row['num'];
                $detail[$key]['avg_money'] += $row['avg_money'];
                if ($source == 'create_return') {
                    $detail[$key]['fx_amount'] += $row['fx_amount'];
                }
            }
        }
        return $detail;
    }

    //刷新商家备注
    function seller_remark_flush($sell_record_code) {
        $record = $this->get_record_by_code($sell_record_code);
        require_model('biz_api/BizTaobaoModel');
        $o_biz = new BizTaobaoModel($record['shop_code']);
        $ret = $o_biz->taobao_trade_get($record['deal_code']);
        if ($ret === false) {
            $err = $o_biz->get_error();
            return $this->format_ret(-1, '', $err['msg']);
        }
        if ($record['seller_remark'] <> $ret['seller_memo'] && isset($ret['seller_memo'])) {
            M('oms_sell_record')->update(array('seller_remark' => $ret['seller_memo']), array('sell_record_code' => $sell_record_code));
        }
        return $this->format_ret(1, $ret['seller_memo']);
    }

    //上传商家备注
    function seller_remark_upload($sell_record_code, $seller_remark) {
        $record = $this->get_record_by_code($sell_record_code);
        require_model('biz_api/BizTaobaoModel');
        $o_biz = new BizTaobaoModel($record['shop_code']);
        $ret = $o_biz->taobao_trade_memo_update($record['deal_code'], $seller_remark);
        if ($ret === false) {
            $err = $o_biz->get_error();
            return $this->format_ret(-1, '', $err['msg']);
        }
        return $this->format_ret(1, $seller_remark);
    }

    //刷新客户留言
    function buyer_remark_flush($sell_record_code) {
        $record = $this->get_record_by_code($sell_record_code);
        require_model('biz_api/BizTaobaoModel');
        $o_biz = new BizTaobaoModel($record['shop_code']);
        $ret = $o_biz->taobao_trade_get($record['deal_code']);
        if ($ret === false) {
            $err = $o_biz->get_error();
            return $this->format_ret(-1, '', $err['msg']);
        }
        if ($record['buyer_remark'] <> $ret['buyer_memo'] && isset($ret['buyer_memo'])) {
            M('oms_sell_record')->update(array('buyer_remark' => $ret['buyer_memo']), array('sell_record_code' => $sell_record_code));
        }
        return $this->format_ret(1, $ret['seller_memo']);
    }

    //根据问题类型获取数量
    function get_count_by_problem_type($problem_type) {
        $sql = "select count(*) from oms_sell_record_tag t1,oms_sell_record t2 where t1.sell_record_code = t2.sell_record_code AND  t2.is_problem=1 and t1.tag_type='problem' and t2.order_status<>3 and t1.tag_v = :problem_type";
        //过滤店铺权限
        $filter_shop_code = null;
        $sql .= load_model('base/ShopModel')->get_sql_purview_shop('t2.shop_code', $filter_shop_code);
        //过滤仓库权限
        $filter_store_code = null;
        $sql .= load_model('base/StoreModel')->get_sql_purview_store('t2.store_code', $filter_store_code);
        $sql_value[':problem_type'] = $problem_type;
        return $num = $this->db->get_value($sql, $sql_value);
    }

    //获取列表(问题列表、缺货列表、合并列表、已发货列表共用)
    function get_list_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        //print_r($filter);
        $detail_table = "oms_sell_record_detail";
        $sql_values = array();
        $sql_join = "";
        // var_dump($detail_table : oms_sell_record_detail,$this->table    oms_sell_record);die;
        $sub_sql = "select sell_record_code from {$detail_table} rr where 1";
        $sub_values = array();
        $sql_main = "FROM {$this->table} rl WHERE 1 ";
        //模糊查询参数控制
        $param = load_model('sys/SysParamsModel')->get_val_by_code(array('fuzzy_search'));

        //商店仓库权限
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code', $filter_store_code);
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('rl.shop_code', $filter_shop_code);
        //缺货单
        $que_huo_where = "";
        if (isset($filter['stock_out_status']) && $filter['stock_out_status'] == '0') {
            //已付款未确认的缺货单
            $sql_main .= " AND rl.lock_inv_status in (2,3) and rl.must_occupy_inv = 1 ";
            $sub_sql .= " AND  rr.lock_num<rr.num ";
        }

        if (isset($filter['search_mode']) && $filter['search_mode'] == 'problem_order') {
            $sql_main .= " AND rl.order_status !=3 and is_problem=1";
        }
        //是否签收
        if (isset($filter['order_sign_status']) && $filter['order_sign_status'] !== '') {
            $sql_main .= " AND rl.order_sign_status = :order_sign_status ";
            $sql_values[':order_sign_status'] = $filter['order_sign_status'];
        }
        //是否揽件
        if (isset($filter['embrace_status']) && $filter['embrace_status'] !== '') {
            if ($filter['embrace_status'] == 0) {
                $sql_main .= " AND (rl.embrace_time = 0 OR rl.embrace_time = '') ";
            } else {
                $sql_main .= " AND (rl.embrace_time != 0 OR rl.embrace_time != '') ";
            }
        }
        //商品数量
        if (isset($filter['num_start']) && $filter['num_start'] !== '') {
            $sql_main .= " AND rl.goods_num >= :num_start ";
            $sql_values[':num_start'] = $filter['num_start'];
        }
        if (isset($filter['num_end']) && $filter['num_end'] !== '') {
            $sql_main .= " AND rl.goods_num <= :num_end ";
            $sql_values[':num_end'] = $filter['num_end'];
        }
        //验收员
        if (isset($filter['delivery_person']) && $filter['delivery_person'] !== '') {
            $user_code = $this->db->get_row("SELECT user_code FROM sys_user WHERE user_name = :user_name ", array(':user_name' => $filter['delivery_person']));
            if (!empty($user_code)) {
                $sql_main .= "AND rl.delivery_person = :delivery_person";
                $sql_values[':delivery_person'] = $user_code;
            } else {
                $sql_main .= " AND 1=0 ";
            }
        }
        if (isset($filter['question_list']) && $filter['question_list'] !== '') {
            $sell_record_attr_arr = explode(',', $filter['question_list']);
            $sql_attr_arr = array();
            foreach ($sell_record_attr_arr as $attr) {
                if ($attr == 'attr_lock') {
                    $sql_attr_arr[] = " rl.is_lock = 1";
                }
                if ($attr == 'attr_pending') {
                    $sql_attr_arr[] = " rl.is_pending = 1";
                }
                if ($attr == 'attr_combine') {
                    $sql_attr_arr[] = " rl.is_combine_new = 1";
                }
                if ($attr == 'attr_split') {
                    $sql_attr_arr[] = " rl.is_split_new = 1";
                }
                if ($attr == 'attr_change') {
                    $sql_attr_arr[] = " rl.is_change_record = 1";
                }
                if ($attr == 'attr_handwork') {
                    $sql_attr_arr[] = " rl.is_handwork = 1";
                }
                if ($attr == 'attr_copy') {
                    $sql_attr_arr[] = " rl.is_copy = 1";
                }
                if ($attr == 'is_replenish') {
                    $sql_attr_arr[] = " rl.is_replenish = 1";
                }
                if ($attr == 'attr_presale') {
                    $sql_attr_arr[] = " rl.sale_mode = 'presale'";
                }
                if ($attr == 'attr_fenxiao') {
                    $sql_attr_arr[] = " (rl.is_fenxiao = 1 OR rl.is_fenxiao = 2) ";
                }
                if ($attr == 'out_of_stock') {
                    $sql_attr_arr[] = " (rl.lock_inv_status IN (2,3) AND rl.must_occupy_inv = 1) ";
                }
            }
            $sql_main .= ' and (' . join(' or ', $sql_attr_arr) . ')';
        }
        //增加订单性质判断
        if (isset($filter['sell_record_attr']) && $filter['sell_record_attr'] !== '') {
            $sell_record_attr_arr = explode(',', $filter['sell_record_attr']);
            $sql_attr_arr = array();
            foreach ($sell_record_attr_arr as $attr) {
                if ($attr == 'attr_lock') {
                    $sql_attr_arr[] = " rl.is_lock = 1";
                }
                if ($attr == 'attr_pending') {
                    $sql_attr_arr[] = " rl.is_pending = 1";
                }
                if ($attr == 'attr_problem') {
                    $sql_attr_arr[] = " rl.is_problem = 1";
                }
                if ($attr == 'attr_bf_quehuo') {
                    $sql_attr_arr[] = " (rl.must_occupy_inv = 1 and rl.lock_inv_status = 2)";
                }
                if ($attr == 'attr_all_quehuo') {
                    $sql_attr_arr[] = " (rl.must_occupy_inv = 1 and rl.lock_inv_status = 3)";
                }
                if ($attr == 'attr_combine') {
                    $sql_attr_arr[] = " rl.is_combine_new = 1";
                }
                if ($attr == 'attr_split') {
                    $sql_attr_arr[] = " rl.is_split_new = 1";
                }
                if ($attr == 'attr_change') {
                    $sql_attr_arr[] = " rl.is_change_record = 1";
                }
                if ($attr == 'attr_handwork') {
                    $sql_attr_arr[] = " rl.is_handwork = 1";
                }
                if ($attr == 'attr_copy') {
                    $sql_attr_arr[] = " rl.is_copy = 1";
                }
                if ($attr == 'attr_presale') {
                    $sql_attr_arr[] = " rl.sale_mode = 'presale'";
                }
                if ($attr == 'attr_fenxiao') {
                    $sql_attr_arr[] = " (rl.is_fenxiao = 1 OR rl.is_fenxiao = 2) ";
                }
                if ($attr == 'is_rush') {
                    $sql_attr_arr[] = " rl.is_rush = 1";
                }
                if ($attr == 'is_replenish') {
                    $sql_attr_arr[] = " rl.is_replenish = 1 ";
                }
                if ($attr == 'is_problem') {
                    $sql_attr_arr[] = " (rl.must_occupy_inv = '1' AND rl.lock_inv_status = '1' AND rl.is_pending = '0' AND rl.is_problem = '0') ";
                }
            }
            $sql_main .= ' and (' . join(' or ', $sql_attr_arr) . ')';
        }
        //问题单
        if (isset($filter['is_problem'])) {
            $sql_main .= " AND rl.is_problem = :is_problem ";
            $sql_values[':is_problem'] = $filter['is_problem'];
        }
        //预售状态
        if (isset($filter['is_persale']) && $filter['is_persale'] != 'all') {
            $sql_main .= " AND rl.sale_mode = :sale_mode ";
            $sql_values[':sale_mode'] = $filter['is_persale'];
        }
        //合并单
        if (isset($filter['merge_status']) && $filter['merge_status'] == '1') {
            $sql_main .= " and (select count(*) from {$this->table} t where t.receiver_name = rl.receiver_name and t.receiver_mobile = rl.receiver_mobile and t.pay_status = 2 and t.order_status = 0)>1 and rl.pay_status = 2 and rl.order_status = 0";
        }
        //已发货订单
        if (isset($filter['shipping_status']) && !empty($filter['shipping_status'])) {
            $sql_main .= " and rl.shipping_status = :shipping_status";
            $sql_values[':shipping_status'] = $filter['shipping_status'];
        }
        ###############################################################################
        //缺货状态
        if (isset($filter['is_stock_out']) && $filter['is_stock_out'] != 'all') {
            if ($filter['is_stock_out'] == 0) {
                $sql_main .= " AND rl.lock_inv_status = 3 ";
            } else {
                $sql_main .= " AND rl.lock_inv_status = :is_stock_out ";
                $sql_values[':is_stock_out'] = $filter['is_stock_out'];
            }
        }

        //cici
        //是否分销单
        if (isset($filter['is_fenxiao']) && $filter['is_fenxiao'] != 'all') {
//            $sql_main .= " AND (rl.is_fenxiao = 1 OR rl.is_fenxiao = 2) ";
            if ($filter['is_fenxiao'] == 1) {
                $sql_main .= " AND (rl.is_fenxiao = 1 OR rl.is_fenxiao = 2) ";
            } else if ($filter['is_fenxiao'] == 0) {
                $sql_main .= " AND (rl.is_fenxiao = 0) ";
            }
        }
        //是否快递交接
        if (isset($filter['receive_status']) && $filter['receive_status'] != '') {
            if ($filter['receive_status'] == 0) {
                $sql_main .= " AND rl.is_receive = 0 ";
            } else if ($filter['receive_status'] == 1) {
                $sql_main .= " AND rl.is_receive = 1 ";
            } else {
                $sql_main .= " AND rl.is_receive = -1 ";
            }
        }
        //是否已拆单
        if (isset($filter['is_split_new']) && $filter['is_split_new'] != 'all') {
            $sql_main .= " AND rl.is_split_new = :is_split_new ";
            $sql_values[':is_split_new'] = $filter['is_split_new'];
        }
        //买家留言
        if (isset($filter['is_buyer_remark']) && $filter['is_buyer_remark'] != 'all') {
            $sql_main .= " AND rl.is_buyer_remark = :is_buyer_remark ";
            $sql_values[':is_buyer_remark'] = $filter['is_buyer_remark'];
        }
        //卖家留言
        if (isset($filter['is_seller_remark']) && $filter['is_seller_remark'] != 'all') {
            $sql_main .= " AND rl.is_seller_remark = :is_seller_remark ";
            $sql_values[':is_seller_remark'] = $filter['is_seller_remark'];
        }

        //已生成采购计划单
        if (isset($filter['is_purchase']) && $filter['is_purchase'] != 'all') {
            //TODO
        }
        ################################################################################
        //锁定人
        if (isset($filter['is_lock_person']) && $filter['is_lock_person'] !== '') {
            $s_sql = "select user_code from sys_user where user_name = :user_name";
            $is_lock_person = ctx()->db->getOne($s_sql, array(':user_name' => $filter['is_lock_person']));
            if (empty($is_lock_person)) {
                $sql_main .= " and 1 != 1";
            } else {
                $sql_main .= " AND rl.is_lock_person = :is_lock_person ";
                $sql_values[':is_lock_person'] = $is_lock_person;
            }
        }

        //标签查询 order_tag
        if (isset($filter['order_tag']) && $filter['order_tag'] !== '') {
            $tag_arr = explode(',', $filter['order_tag']);
            $tag_record = load_model('oms/SellRecordTagModel')->get_sell_record_by_tag($tag_arr);
            if (!empty($tag_record)) {
                $tag_record_str = $this->arr_to_in_sql_value($tag_record, 'sell_record_code', $sql_values);
                $sql_main .= " AND rl.sell_record_code in ({$tag_record_str}) ";
            } else {
                $sql_main .= " AND 1=2 ";
            }
        }

        $is_sku = false;
        //订单号
        if (isset($filter['sell_record_code']) && $filter['sell_record_code'] !== '') {
//            $filter['sell_record_code'] =preg_replace("/\s|　/","",$filter['sell_record_code']);
//      	    $arr = explode(',',$filter['sell_record_code']);
//            $str = $this->arr_to_in_sql_value($arr, 'sell_record_code', $sql_values);
//            $sql_main .= " AND rl.sell_record_code in ( " . $str. " ) ";
            $filter['sell_record_code'] = str_replace('，', ',', $filter['sell_record_code']);
            $sell_record_arr = explode(',', $filter['sell_record_code']);
            $sell_record_arr = array_map(function ($val) {
                return trim($val);
            }, $sell_record_arr);
            $sell_record_code_where = $this->arr_to_in_sql_value($sell_record_arr, 'sell_record_code', $sql_values);
            $sql_main .= " AND   rl.sell_record_code in ({$sell_record_code_where}) ";
        } else {
            //交易号
            if (isset($filter['deal_code_list']) && !empty($filter['deal_code_list'])) {
//                $sql_main .= " AND rl.deal_code_list like :deal_code_list ";
//                $sql_values[':deal_code_list'] = "%" . $filter['deal_code_list'] . "%";
                $filter['deal_code_list'] = str_replace('，', ',', $filter['deal_code_list']);
                $deal_code_list_arr = explode(',', $filter['deal_code_list']);
                $deal_code_list_arr = array_map(function ($val) {
                    return trim($val);
                }, $deal_code_list_arr);
                $sql_d_values = array();
                $sql_deal = 'select sell_record_code from oms_sell_record_detail';
                if ($param['fuzzy_search'] == 1) {
                    $deal_code_str = $this->arr_to_like_sql_value($deal_code_list_arr, 'deal_code', $sql_d_values);
                    $sql_deal .= ' WHERE ' . $deal_code_str;
                } else {
                    $deal_code_str = $this->arr_to_in_sql_value($deal_code_list_arr, 'deal_code', $sql_d_values);
                    $sql_deal .= " WHERE deal_code IN({$deal_code_str})";
                }
                $sell_record_data = $this->db->get_all($sql_deal, $sql_d_values);
                if (!empty($sell_record_data)) {
                    $sell_record_arr = array_column($sell_record_data, 'sell_record_code');
                    $sell_record_code_where = $this->arr_to_in_sql_value($sell_record_arr, 'sell_record_code', $sql_values);
                    $sql_main .= " AND rl.sell_record_code in ({$sell_record_code_where}) ";
                } else {
                    $filter['deal_code_list'] = $this->get_sql_for_search($filter['deal_code_list'], 'rl.deal_code_list');
                    $sql_main .= " AND {$filter['deal_code_list']} ";
                }
            } else {
                //销售平台
                if (isset($filter['sale_channel_code']) && !empty($filter['sale_channel_code'])) {
                    $arr = explode(',', $filter['sale_channel_code']);
                    $str = $this->arr_to_in_sql_value($arr, 'sale_channel_code', $sql_values);
                    $sql_main .= " AND rl.sale_channel_code in (" . $str . ") ";
                }
                //物流单号
                if (isset($filter['express_no']) && !empty($filter['express_no'])) {
                    $sql_main .= " AND rl.express_no like :express_no ";
                    $sql_values[':express_no'] = "%" . $filter['express_no'] . "%";
                }
                //问题类型
                if (isset($filter['is_problem_type']) && !empty($filter['is_problem_type'])) {
                    $arr = explode(',', $filter['is_problem_type']);
                    $str = $this->arr_to_in_sql_value($arr, 'is_problem_type', $sql_values);
                    $sql_main .= " AND rl.sell_record_code in (select rt.sell_record_code from oms_sell_record_tag rt where rt.tag_v in (" . $str . ") and rt.tag_type='problem') ";
                }
                //订单问题描述
                if (isset($filter['is_problem_reason']) && !empty($filter['is_problem_reason'])) {
                    $sql_main .= " AND rl.sell_record_code in (select rt.sell_record_code from oms_sell_record_tag rt where rt.tag_desc like :is_problem_reason and rt.tag_type='problem') ";
                    $sql_values[':is_problem_reason'] = '%' . $filter['is_problem_reason'] . '%';
                }
                //订单标签
                if (isset($filter['order_tag']) && !empty($filter['order_tag'])) {
                    $arr = explode(',', $filter['order_tag']);
                    $str = $this->arr_to_in_sql_value($arr, 'order_tag', $sql_values);
                    $sql_main .= " AND rl.sell_record_code in (select rt.sell_record_code from oms_sell_record_tag rt where rt.tag_v in (" . $str . ") and rt.tag_type='order_tag') ";
                }
                //买家申请退款
                if (isset($filter['apply_refund']) && $filter['apply_refund'] != 'all') {
                    if ($filter['apply_refund'] == 1) {
                        $sql_main .= " AND rl.sell_record_code IN (select rt.sell_record_code from oms_sell_record_tag rt where rt.tag_v in ('FULL_REFUND','REFUND') and rt.tag_type='problem') ";
                    } else if ($filter['apply_refund'] == 0) {
                        $sql_main .= " AND rl.sell_record_code NOT IN (select rt.sell_record_code from oms_sell_record_tag rt where rt.tag_v in ('FULL_REFUND','REFUND') and rt.tag_type='problem') ";
                    }
                }
                //淘宝卖家备注旗帜
                if (isset($filter['seller_flag']) && $filter['seller_flag'] !== '') {
                    $_seller_flag = array_map("intval", explode(',', $filter['seller_flag']));
                    $sell_flag_list = join(',', $_seller_flag);
                    $sql_main .= " AND rl.seller_flag in({$sell_flag_list})";
                }
                //下单时间
                if (isset($filter['record_time_start']) && !empty($filter['record_time_start'])) {
                    $sql_main .= " AND rl.record_time >= :record_time_start ";
                    $record_time_start = strtotime(date("Y-m-d", strtotime($filter['record_time_start'])));
                    if ($record_time_start == strtotime($filter['record_time_start'])) {
                        $sql_values[':record_time_start'] = $filter['record_time_start'];
                    } else {
                        $sql_values[':record_time_start'] = $filter['record_time_start'];
                    }
                }
                if (isset($filter['record_time_end']) && !empty($filter['record_time_end'])) {
                    $sql_main .= " AND rl.record_time <= :record_time_end ";
                    $record_time_end = strtotime(date("Y-m-d", strtotime($filter['record_time_end'])));
                    if ($record_time_end == strtotime($filter['record_time_end'])) {
                        $sql_values[':record_time_end'] = $filter['record_time_end'];
                    } else {
                        $sql_values[':record_time_end'] = $filter['record_time_end'];
                    }
                }
                //付款时间
                if (isset($filter['pay_time_start']) && !empty($filter['pay_time_start'])) {
                    $sql_main .= " AND rl.pay_time >= :pay_time_start ";
                    $pay_time_start = strtotime(date("Y-m-d", strtotime($filter['pay_time_start'])));
                    if ($pay_time_start == strtotime($filter['pay_time_start'])) {
                        $sql_values[':pay_time_start'] = $filter['pay_time_start'];
                    } else {
                        $sql_values[':pay_time_start'] = $filter['pay_time_start'];
                    }
                }
                if (isset($filter['pay_time_end']) && !empty($filter['pay_time_end'])) {
                    $sql_main .= " AND rl.pay_time <= :pay_time_end ";
                    $pay_time_end = strtotime(date("Y-m-d", strtotime($filter['pay_time_end'])));
                    if ($pay_time_end == strtotime($filter['pay_time_end'])) {
                        $sql_values[':pay_time_end'] = $filter['pay_time_end'];
                    } else {
                        $sql_values[':pay_time_end'] = $filter['pay_time_end'];
                    }
                }
                //计划发货时间
                if (isset($filter['plan_send_time_start']) && !empty($filter['plan_send_time_start'])) {
                    $sql_main .= " AND rl.plan_send_time >= :plan_send_time_start ";
                    $pay_time_start = strtotime(date("Y-m-d", strtotime($filter['plan_send_time_start'])));
                    if ($pay_time_start == strtotime($filter['plan_send_time_start'])) {
                        $sql_values[':plan_send_time_start'] = $filter['plan_send_time_start'] . ' 00:00:00';
                    } else {
                        $sql_values[':plan_send_time_start'] = $filter['plan_send_time_start'];
                    }
                }
                if (isset($filter['plan_send_time_end']) && !empty($filter['plan_send_time_end'])) {
                    $sql_main .= " AND rl.plan_send_time <= :plan_send_time_end ";
                    $pay_time_end = strtotime(date("Y-m-d", strtotime($filter['plan_send_time_end'])));
                    if ($pay_time_end == strtotime($filter['plan_send_time_end'])) {
                        $sql_values[':plan_send_time_end'] = $filter['plan_send_time_end'] . ' 23:59:59';
                    } else {
                        $sql_values[':plan_send_time_end'] = $filter['plan_send_time_end'];
                    }
                }
                //通知配货时间
                if (isset($filter['is_notice_time_start']) && !empty($filter['is_notice_time_start'])) {
                    $sql_main .= " AND rl.is_notice_time >= :is_notice_time_start ";
                    $is_notice_time_start = strtotime(date("Y-m-d", strtotime($filter['is_notice_time_start'])));
                    if ($is_notice_time_start == strtotime($filter['is_notice_time_start'])) {//出现年月日情况
                        $sql_values[':is_notice_time_start'] = date("Y-m-d", strtotime($filter['is_notice_time_start'])) . ' 00:00:00';
                    } else {
                        $sql_values[':is_notice_time_start'] = $filter['is_notice_time_start'];
                    }
                }
                if (isset($filter['is_notice_time_end']) && !empty($filter['is_notice_time_end'])) {
                    $sql_main .= " AND rl.is_notice_time <= :is_notice_time_end ";
                    $is_notice_time_end = strtotime(date("Y-m-d", strtotime($filter['is_notice_time_end'])));
                    if ($is_notice_time_end == strtotime($filter['is_notice_time_end'])) {
                        $sql_values[':is_notice_time_end'] = $filter['is_notice_time_end'] . ' 23:59:59';
                    } else {
                        $sql_values[':is_notice_time_end'] = $filter['is_notice_time_end'];
                    }
                }
                //发货时间
                if (isset($filter['send_time_start']) && !empty($filter['send_time_start'])) {
                    $sql_main .= " AND rl.delivery_time >= :send_time_start ";
                    $send_time_start = strtotime(date("Y-m-d", strtotime($filter['send_time_start'])));
                    if ($send_time_start == strtotime($filter['send_time_start'])) {
                        $sql_values[':send_time_start'] = $filter['send_time_start'];
                    } else {
                        $sql_values[':send_time_start'] = $filter['send_time_start'];
                    }
                }
                if (isset($filter['send_time_end']) && !empty($filter['send_time_end'])) {
                    $sql_main .= " AND rl.delivery_time <= :send_time_end ";
                    $send_time_end = strtotime(date("Y-m-d", strtotime($filter['send_time_end'])));
                    if ($send_time_end == strtotime($filter['send_time_end'])) {
                        $sql_values[':send_time_end'] = $filter['send_time_end'] . ' 23:59:59';
                    } else {
                        $sql_values[':send_time_end'] = $filter['send_time_end'];
                    }
                }
                //揽件时间
                if (isset($filter['embrace_time_start']) && !empty($filter['embrace_time_start'])) {
                    $filter['embrace_time_start'] = strtotime($filter['embrace_time_start']);
                    $sql_main .= " AND rl.embrace_time >= :embrace_time_start ";
                    $sql_values[':embrace_time_start'] = $filter['embrace_time_start'];
                }
                if (isset($filter['embrace_time_end']) && !empty($filter['embrace_time_end'])) {
                    $filter['embrace_time_end'] = strtotime($filter['embrace_time_end']);
                    $sql_main .= " AND rl.embrace_time <= :embrace_time_end ";
                    $sql_values[':embrace_time_end'] = $filter['embrace_time_end'];
                }
                //签收时间
                if (isset($filter['sign_time_start']) && !empty($filter['sign_time_start'])) {
                    $sql_main .= " AND rl.sign_time >= :sign_time_start ";
                    $send_time_start = strtotime(date("Y-m-d", strtotime($filter['sign_time_start'])));
                    if ($send_time_start == strtotime($filter['sign_time_start'])) {
                        $sql_values[':sign_time_start'] = date("Y-m-d", strtotime($filter['sign_time_start'])) . ' 00:00:00';
                    } else {
                        $sql_values[':sign_time_start'] = $filter['sign_time_start'];
                    }
                }
                if (isset($filter['sign_time_end']) && !empty($filter['sign_time_end'])) {
                    $sql_main .= " AND rl.sign_time <= :sign_time_end ";
                    $send_time_end = strtotime(date("Y-m-d", strtotime($filter['sign_time_end'])));
                    if ($send_time_end == strtotime($filter['sign_time_end'])) {
                        $sql_values[':sign_time_end'] = $filter['sign_time_end'] . ' 23:59:59';
                    } else {
                        $sql_values[':sign_time_end'] = $filter['sign_time_end'];
                    }
                }
                //收货人
                if (isset($filter['receiver_name']) && !empty($filter['receiver_name'])) {
                    $customer_address_id = load_model('crm/CustomerOptModel')->get_customer_address_id_with_search($filter['receiver_name'], 'name');
                    if (!empty($customer_address_id)) {
                        $customer_address_id_str = implode(",", $customer_address_id);
                        $sql_main .= " AND ( rl.receiver_name LIKE :receiver_name  OR rl.customer_address_id in ({$customer_address_id_str}) ) ";
                        $sql_values[':receiver_name'] = '%' . $filter['receiver_name'] . '%';
                    } else {
                        $sql_main .= " AND rl.receiver_name LIKE :receiver_name ";
                        $sql_values[':receiver_name'] = '%' . $filter['receiver_name'] . '%';
                    }
//                    $sql_main .= " AND rl.receiver_name LIKE :receiver_name ";
//                    $sql_values[':receiver_name'] = '%' . $filter['receiver_name'] . '%';
                }
                //手机号
                if (isset($filter['receiver_mobile']) && $filter['receiver_mobile'] !== '') {
                    $customer_address_id = load_model('crm/CustomerOptModel')->get_customer_address_id_with_search($filter['receiver_mobile'], 'tel');
                    if (!empty($customer_address_id)) {
                        $customer_address_id_str = implode(",", $customer_address_id);
                        $sql_main .= " AND ( rl.receiver_mobile = :receiver_mobile OR rl.customer_address_id in ({$customer_address_id_str}) ) ";
                        $sql_values[':receiver_mobile'] = $filter['receiver_mobile'];
                    } else {
                        $sql_main .= " AND rl.receiver_mobile = :receiver_mobile ";
                        $sql_values[':receiver_mobile'] = $filter['receiver_mobile'];
                    }
//                    $sql_main .= " AND rl.receiver_mobile LIKE :receiver_mobile ";
//                    $sql_values[':receiver_mobile'] = '%' . $filter['receiver_mobile'] . '%';
                }
                //国家
                if (isset($filter['country']) && $filter['country'] !== '') {
                    $sql_main .= " AND rl.receiver_country = :country ";
                    $sql_values[':country'] = $filter['country'];
                }
                //省
                if (isset($filter['province']) && $filter['province'] !== '') {
                    $sql_main .= " AND rl.receiver_province = :province ";
                    $sql_values[':province'] = $filter['province'];
                }
                //城市
                if (isset($filter['city']) && $filter['city'] !== '') {
                    $sql_main .= " AND rl.receiver_city = :city ";
                    $sql_values[':city'] = $filter['city'];
                }
                //地区
                if (isset($filter['district']) && $filter['district'] !== '') {
                    $sql_main .= " AND rl.receiver_district = :district ";
                    $sql_values[':district'] = $filter['district'];
                }
                //详细地址
                if (isset($filter['receiver_addr']) && $filter['receiver_addr'] !== '') {
                    $sql_main .= " AND rl.receiver_addr LIKE :receiver_addr ";
                    $sql_values[':receiver_addr'] = '%' . $filter['receiver_addr'] . '%';
                }
                //收货地址
                if (isset($filter['receiver_address']) && !empty($filter['receiver_address'])) {
                    $sql_main .= " AND rl.receiver_address LIKE :receiver_address ";
                    $sql_values[':receiver_address'] = '%' . $filter['receiver_address'] . '%';
                }
                //仓库
                if (isset($filter['store_code']) && !empty($filter['store_code'])) {
                    $arr = explode(',', $filter['store_code']);
                    $str = $this->arr_to_in_sql_value($arr, 'store_code', $sql_values);
                    $sql_main .= " AND rl.store_code in (" . $str . ") ";
                }
                //快递公司
                if (isset($filter['express_company']) && !empty($filter['express_company'])) {
                    $sql_main .= " AND rl.express_code in (select express_code from base_express where company_code in (:express_company)) ";
                    $sql_values[':express_company'] = $filter['express_company'];
                }
                //配送方式
                if (isset($filter['express_code']) && !empty($filter['express_code'])) {
                    $arr = explode(',', $filter['express_code']);
                    $str = $this->arr_to_in_sql_value($arr, 'express_code', $sql_values);
                    $sql_main .= " AND rl.express_code in (" . $str . ") ";
                }
                //客户留言
                if (isset($filter['buyer_remark']) && !empty($filter['buyer_remark'])) {
                    $sql_main .= " AND rl.buyer_remark LIKE :buyer_remark ";
                    $sql_values[':buyer_remark'] = '%' . $filter['buyer_remark'] . '%';
                }
                //买家昵称
                if (isset($filter['buyer_name']) && !empty($filter['buyer_name'])) {

                    $customer_code_arr = load_model('crm/CustomerOptModel')->get_customer_code_with_search($filter['buyer_name']);
                    if (!empty($customer_code_arr)) {

                        $customer_code_str = "'" . implode("','", $customer_code_arr) . "'";
                        $sql_main .= " AND ( rl.customer_code in ({$customer_code_str}) ) ";
                    } else {
                        $sql_main .= " AND rl.buyer_name = :buyer_name ";
                        $sql_values[':buyer_name'] = $filter['buyer_name'];
                    }
                }
                //商家留言
                if (isset($filter['seller_remark']) && !empty($filter['seller_remark'])) {
                    $sql_main .= " AND rl.seller_remark LIKE :seller_remark ";
                    $sql_values[':seller_remark'] = '%' . $filter['seller_remark'] . '%';
                }
                //商品编码
                if (isset($filter['goods_code']) && !empty($filter['goods_code'])) {
                    $sub_sql .= " AND rr.goods_code LIKE :goods_code ";
                    $sub_values[':goods_code'] = '%' . $filter['goods_code'] . '%';
                }
                //有商品名称
                if (isset($filter['goods_name']) && $filter['goods_name'] !== '') {
                    $sql_g = "select goods_code from base_goods where goods_name like '%{$filter['goods_name']}%' ";
                    $goods_name_ret = $this->db->get_all_col($sql_g);
                    if (empty($goods_name_ret)) {
                        $sql_main .= " and 1=2 ";
                    } else {
                        $str_goods_code = $this->arr_to_in_sql_value($goods_name_ret, 'goods_code', $sql_values);
                        $sub_sql .= " AND rr.goods_code in ( " . $str_goods_code . " ) ";
                        // echo  $sql_main;die;
                        $is_sku = true;
                    }
                }
                //条码

                if (isset($filter['barcode']) && !empty($filter['barcode'])) {
//
//
//                    $sub_sql .= " AND rr.barcode like :barcode ";
//                    $sub_values[':barcode'] = "%" . $filter['barcode'] . "%";
                    $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
                    if (empty($sku_arr)) {
                        $sub_sql .= " AND 1=2 ";
                    } else {
                        $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sub_values);
                        $sub_sql .= " AND rr.sku in({$sku_str}) ";
                    }
                    $is_sku = true;
                }
                //平台规则
                if (isset($filter['platform_spec']) && !empty($filter['platform_spec'])) {
                    $sub_sql .= " AND rr.platform_spec LIKE :platform_spec ";
                    $sub_values[':platform_spec'] = '%' . $filter['platform_spec'] . '%';
                }
                //支付方式
                if (isset($filter['pay_code']) && !empty($filter['pay_code'])) {
                    $arr = explode(',', $filter['pay_code']);
                    $str = $this->arr_to_in_sql_value($arr, 'pay_code', $sql_values);
                    $sql_main .= " AND rl.pay_code in (" . $str . ") ";
                }
                //货到付款
                if (isset($filter['pay_type']) && !empty($filter['pay_type'])) {
                    if ($filter['pay_type'] == '1') {
                        $sql_main .= " AND rl.pay_type = 1";
                    } else {
                        $sql_main .= " AND rl.pay_type <> 1";
                    }
                }
                //换货单
                if (isset($filter['is_change_record']) && $filter['is_change_record'] !== '') {
                    $sql_main .= " AND rl.is_change_record = :is_change_record ";
                    $sql_values[':is_change_record'] = $filter['is_change_record'];
                }
                //发票
                if (isset($filter['invoice_status']) && $filter['invoice_status'] !== '') {
                    if ($filter['invoice_status'] == '1') {
                        $sql_main .= " AND rl.invoice_status <> 0";
                    } elseif ($filter['invoice_status'] == '0') {
                        $sql_main .= " AND rl.invoice_status = 0";
                    }
                }
                //快递交接
                if (isset($filter['is_e_handover']) && !empty($filter['is_e_handover'])) {
                    $sql_main .= " AND rl.is_e_handover = :is_e_handover ";
                    $sql_values[':is_e_handover'] = $filter['is_e_handover'];
                }
                //回写状态
                if (isset($filter['is_back']) && !empty($filter['is_back'])) {
                    $sql_main .= " AND rl.is_back = :is_back ";
                    $sql_values[':is_back'] = $filter['is_back'];
                }
                //快递号
                if (!empty($filter['express_no'])) {
                    $sql_main .= " AND rl.express_no LIKE :express_no ";
                    $sql_values[':express_no'] = '%' . $filter['express_no'] . '%';
                }
                //订单性质
                if (isset($filter['order_nature']) && $filter['order_nature'] !== '') {
                    $order_nature = $filter['order_nature'];
                    if ($order_nature != 'sale_mode') {
                        $sql_main .= " AND rl.$order_nature = 1";
                    } else {
                        $sql_main .= " AND rl.$order_nature = 'presale'";
                    }
                }
            }
        }

        //排序
        if (isset($filter['is_sort']) && $filter['is_sort'] != '') {
            switch ($filter['is_sort']) {
                case 'pay_time_asc':  //付款时间升序
                    $order_by = "  ORDER BY rl.pay_time ASC ";
                    break;
                case 'pay_time_desc':  //付款时间降序
                    $order_by = " ORDER BY rl.pay_time  DESC ";
                    break;
                case 'record_time_asc':  //下单时间升序
                    $order_by = " ORDER BY rl.record_time ASC ";
                    break;
                case 'record_time_desc':  //下单时间降序
                    $order_by = " ORDER BY rl.record_time DESC ";
                    break;
            }
        } else {
            $order_by = " ORDER BY rl.plan_send_time asc,rl.pay_time asc ";
        }


        if ($filter['ctl_type'] == 'export') {
            if ($filter['ctl_export_conf'] == 'sell_record_short_goods_list') {
                return $this->get_quehuo_goods($sql_main, $sql_values, $filter);
            }
            if ($filter['ctl_export_conf'] == 'sell_record_short_goods_count') {
                return $this->get_quehuo_goods_count($sql_main, $sql_values, $filter);
            } else {
                return $this->sell_record_quehuo_csv($sql_main, $sql_values, $filter, $order_by);
            }
        }
        //子查询合并
        if (!empty($sub_values) || $is_sku === TRUE) {
            $sql_main .= " AND rl.sell_record_code in ({$sub_sql})";
            $sql_values = array_merge($sql_values, $sub_values);
        }
        //增值服务
        $sql_main .= load_model('base/SaleChannelModel')->get_values_where('rl.sale_channel_code');
        $select = 'rl.*';
        // $sql_main .= " ORDER BY sell_record_code DESC ";

        $sql_main .= $order_by;
        $cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('safety_control'));
        /* echo $select.'======';
          echo $sql_main;
          print_r($sql_values);die; */
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as $key => &$value) {
            $value['status_text'] = $this->get_status_text($value);
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
            $value['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $value['store_code']));
            $value['express_name'] = oms_tb_val('base_express', 'express_name', array('express_code' => $value['express_code']));
//            $value['is_problem_type'] = oms_tb_val('base_record_type', 'record_type_name', array('record_type_code' => $value['is_problem_type']));
            $value['sale_channel_code'] = oms_tb_val('base_sale_channel', 'sale_channel_name', array('sale_channel_code' => $value['sale_channel_code']));
            $value['express_code_name'] = oms_tb_val('base_express', 'express_name', array('express_code' => $value['express_code']));
            if ($value['embrace_time'] != 0 && $value['embrace_time'] != '') {
                $value['embrace_time'] = date('Y-m-d H:i:s', $value['embrace_time']);
            } else {
                $value['embrace_time'] = '0000-00-00 00:00:00';
            }
            //快递交接状态
            if ($value['is_receive'] == 0) {
                $value['receive_status'] = '未交接';
            } elseif ($value['is_receive'] == 1) {
                $value['receive_status'] = '交接成功';
            } else {
                $value['receive_status'] = '交接失败';
            }
            if ($value['is_back'] == 1 || $value['is_back'] == 2) {
                $value['is_back_html'] = '是';
            } else {
                $value['is_back_html'] = '否';
            }
            if ($value['pay_type'] == 'cod') {
                $value['pay_type_html'] = '是';
            } else {
                $value['pay_type_html'] = '否';
            }
            $tag_data = load_model("oms/SellRecordTagModel")->get_list_by_code(array('sell_record_code' => $value['sell_record_code'], 'tag_type' => 'order_tag'));
            $value['order_tag'] = '';
            foreach ($tag_data['data']['data'] as $k => $v) {
                $value['order_tag'] .= ($v['tag_desc']) ? $v['tag_desc'] . ';' : '';
            }
            if (isset($value['is_problem']) && $value['is_problem'] == 1) {
                $tag = load_model("oms/SellRecordTagModel")->get_all(array('sell_record_code' => $value['sell_record_code'], 'tag_type' => 'problem'));
                $value['problem_html'] = '';
                $value['ddesc'] = '';
                if ($tag['status'] == '1') {
                    $value['problem_html'] = '';
                    $_t_pm = array();
                    foreach ($tag['data'] as $t) {
                        if ($t['tag_desc'] != '') {
                            $_t_pm[] = $t['tag_desc'];
                        } else {
                            $_t_pm[] = oms_tb_val('base_question_label', 'question_label_name', array('question_label_code' => $t['tag_v']));
                        }
                        if ($t['tag_v'] == 'REFUND') {
                            $value['desc'] = $t['desc'] == '' ? '无说明信息' : $t['desc'];
                        } else {
                            $value['desc'] = '';
                        }
                    }
                    //当问题数多于三条时，隐藏多余问题
                    if (count($_t_pm) > 3) {
                        $_t_pm1 = array_slice(array_unique($_t_pm), 0, 3);
                        $value['problem_html'] = join('；', $_t_pm1) . "；<span>●●●</span><br/>";
                    } else {
                        $value['problem_html'] = join('；', array_unique($_t_pm)) . "；<br/>";
                    }
                    $value['ddesc'] = join('；', array_unique($_t_pm));
                }
            }
            //如果不开票，过滤发票内容
            if($value['invoice_status'] == '0'){
                $value['invoice_title'] = '';
                $value['invoice_content'] = '';
                $value['invoice_type'] = '';
            }
            if ($filter['ctl_type'] == 'view') {
//                $value['receiver_mobile'] = $this->phone_hidden($value['receiver_mobile']);
//                $value['receiver_name'] = $this->name_hidden($value['receiver_name']);
//                $value['buyer_name'] = $this->name_hidden($value['buyer_name']);
//                $value['receiver_address'] = $this->address_hidden($value['receiver_address']);
                safe_data($value, 0);
            }
        }
        load_model('common/TBlLogModel')->set_log_multi($data['data'], 'search');
        return $this->format_ret(1, $data);
    }

    function get_quehuo_goods_count($sql_main, $sql_values, $filter) {
        $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
        if (!empty($sku_arr)) {
            $sku_str = implode("','", $sku_arr);
        }
        $sql = "SELECT
                    rl.goods_num,
                    r2.num,
                    r2.sku,
                    r2.platform_spec,
                    r2.lock_num,
                    r2.goods_code,
                    sum(r2.num) as num_total,
                    sum(r2.num-r2.lock_num) as short_num_total";
        $sql_where = " FROM
                            oms_sell_record rl,
                            oms_sell_record_detail r2
                      WHERE
                      rl.sell_record_code = r2.sell_record_code";

        $sql_where .= " AND  r2.lock_num<r2.num ";
        if (isset($filter['goods_code']) && $filter['goods_code'] !== '') {
            $sql_where .= " AND r2.goods_code LIKE :goods_code ";
            $sql_values[':goods_code'] = '%' . $filter['goods_code'] . '%';
        }

        if (isset($filter['barcode']) && $filter['barcode'] !== '') {
            $sql_where .= " AND r2.sku IN ('{$sku_str}') ";
        }
        //商品名称
        if (isset($filter['goods_name']) && $filter['goods_name'] !== '') {
            $sql_g = "select goods_code from base_goods where goods_name like '%{$filter['goods_name']}%' ";
            $goods_name_ret = $this->db->get_all_col($sql_g);
            if (empty($goods_name_ret)) {
                $sql_where .= " and 1=2 ";
            } else {
                $str_goods_code = $this->arr_to_in_sql_value($goods_name_ret, 'goods_code', $sql_values);
                $sql_where .= " AND r2.goods_code in ( " . $str_goods_code . " ) ";
            }
        }
        $sql .= $sql_where;
        $sql = substr_replace($sql_main, $sql, 0, 32);
        $sql .= "group by r2.sku";
        $data['data'] = $this->db->get_all($sql, $sql_values);
        foreach ($data['data'] as $key => $value) {
            if ($value['short_num_total'] == 0) {
                unset($data['data'][$key]);
                continue;
            }
            $key_arr = array('barcode', 'goods_name', 'spec1_name', 'spec2_name', 'gb_code');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
            $data['data'][$key] = array_merge($data['data'][$key], $sku_info);
            $data['data'][$key]['spec1_name'] = $sku_info['spec1_name'];
            $data['data'][$key]['spec2_name'] = $sku_info['spec2_name'];
        }
        return $this->format_ret(1, $data);
    }

    function sell_record_quehuo_csv($sql_main, $sql_values, $filter, $order_by = '') {
        $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
        if (!empty($sku_arr)) {
            $sku_str = implode("','", $sku_arr);
        }
        $sql = "SELECT
                    rl.sell_record_code,
                    rl.customer_code,
                    rl.customer_address_id,
                    rl.sale_channel_code,
                    rl.shop_code,
                    rl.deal_code_list,
                    rl.buyer_name,
                    rl.receiver_name,
                    rl.receiver_address,
                    rl.store_code,
                    rl.paid_money,
                    rl.receiver_country,
                    rl.receiver_province,
                    rl.receiver_city,
                    rl.receiver_district,
                    rl.receiver_street,
        			rl.pay_time,
        			rl.record_time,
        			rl.delivery_time,
                                rl.is_notice_time,
                                rl.embrace_time,
        			rl.pay_type,
        			rl.express_code,
        			rl.express_no,
        			rl.receiver_mobile,
        			rl.payable_money,
                    rl.seller_remark,
                    rl.buyer_remark,
                    rl.buyer_name,
        			rl.is_back,
        			rl.is_problem,
	        		rl.is_pending,
	        		rl.must_occupy_inv,
	        		rl.lock_inv_status,
                     	rl.goods_num,
                    rl.goods_weigh,
                    rl.real_weigh,
                    rl.weigh_express_money,
                    rl.express_money,
                    rl.order_remark,
                    rl.is_fenxiao,
                    rl.fx_payable_money,
                    rl.fx_express_money,
                    rl.order_sign_status,
                    rl.sign_time,
                    rl.is_receive,
                    rl.invoice_title,
                    rl.invoice_content,
                    rl.invoice_status,
                    r2.num,
                    r2.sku,
                    r2.platform_spec,
        		r2.goods_price,
        		r2.avg_money,
        		r2.fx_amount,
                    r2.lock_num";
        $sql_where = " FROM
                            oms_sell_record rl,
                            oms_sell_record_detail r2
                      WHERE
                      rl.sell_record_code = r2.sell_record_code";
        if ($filter['ctl_export_conf'] == 'sell_record_quehuo') {
            $sql_where .= " AND  r2.lock_num<r2.num ";
        }
        $sql_count = "select count(*)";
        if ($filter['ctl_export_conf'] != 'sell_record_wait_shipping_list') {
            if (isset($filter['goods_code']) && $filter['goods_code'] !== '') {
                $sql_where .= " AND r2.goods_code LIKE :goods_code ";
                $sql_values[':goods_code'] = '%' . $filter['goods_code'] . '%';
            }

            if (isset($filter['barcode']) && $filter['barcode'] !== '') {
                $sql_where .= " AND r2.sku IN ('{$sku_str}') ";
            }
            //商品名称
            if (isset($filter['goods_name']) && $filter['goods_name'] !== '') {
                $sql_g = "select goods_code from base_goods where goods_name like '%{$filter['goods_name']}%' ";
                $goods_name_ret = $this->db->get_all_col($sql_g);
                if (empty($goods_name_ret)) {
                    $sql_where .= " and 1=2 ";
                } else {
                    $str_goods_code = $this->arr_to_in_sql_value($goods_name_ret, 'goods_code', $sql_values);
                    $sql_where .= " AND r2.goods_code in ( " . $str_goods_code . " ) ";
                }
            }
        }
        /*
          if (empty($filter['page_count'])) {
          $sql_count .= $sql_where;
          $sql_count = substr_replace($sql_main, $sql_count, 0, 32);
          $count = CTX()->db->getOne($sql_count, $sql_values);
          $filter['page'] = $this->page_size;
          $filter['page_count'] = ceil($sql_count / $filter['page']);
          } */
        $sql .= $sql_where;
        $sql = substr_replace($sql_main, $sql, 0, 32);
        // $ret['filter']['page_count'] = $filter['page_count'];
        $sql .= $order_by;
        $ret['data'] = CTX()->db->getAll($sql, $sql_values);
        $base_shop_arr = array();
        $base_store_arr = array();
        $base_sale_channel_arr = array();
        $base_express = array();
        $ret['data'] = load_model('util/ViewUtilModel')->record_detail_append_goods_info($ret['data']);
        foreach ($ret['data'] as $key => $value) {
            $ret['data'][$key]['short_num'] = $value['num'] - $value['lock_num'];
            $ret['data'][$key]['sell_record_code'] = "\t" . $value['sell_record_code'];
            $ret['data'][$key]['deal_code_list'] = "\t" . $value['deal_code_list'];
            //签收状态
            $ret['data'][$key]['order_sign_status'] = $value['order_sign_status'] == 0 ? '否' : '是';
            //店铺
            if (!isset($base_shop_arr[$value['shop_code']])) {
                $shop_name = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
                $base_shop_arr[$value['shop_code']] = $shop_name;
            }
            //快递交接状态
            if ($value['is_receive'] == 0) {
                $ret['data'][$key]['receive_status'] = '未交接';
            } elseif ($value['is_receive'] == 1) {
                $ret['data'][$key]['receive_status'] = '交接成功';
            } else {
                $ret['data'][$key]['receive_status'] = '交接失败';
            }
            //仓库
            if (!isset($base_store_arr[$value['store_code']])) {
                $store_name = oms_tb_val('base_store', 'store_name', array('store_code' => $value['store_code']));
                $base_store_arr[$value['store_code']] = $store_name;
            }
            //销售平台
            if (!isset($base_sale_channel_arr[$value['sale_channel_code']])) {
                $sale_channel_name = oms_tb_val('base_sale_channel', 'sale_channel_name', array('sale_channel_code' => $value['sale_channel_code']));
                $base_sale_channel_arr[$value['sale_channel_code']] = $sale_channel_name;
            }
            //揽件时间
            if ($value['embrace_time'] != 0 && $value['embrace_time'] != '') {
                $value['embrace_time'] = date('Y-m-d H:i:s', $value['embrace_time']);
            } else {
                $value['embrace_time'] = '0000-00-00 00:00:00';
            }

            //配送方式
            /*
              if (!isset($base_express[$value['express_code']])){
              $express_name = oms_tb_val('base_express', 'express_name', array('express_code' => $value['express_code']));
              $base_express[$value['express_code']] = $express_name;
              } */
            // $ret['data'][$key]['goods_name'] = oms_tb_val('base_goods', 'goods_name', array('goods_code' => $value['goods_code']));
            $key_arr = array('barcode', 'goods_name', 'spec1_code', 'spec2_code');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
            $ret['data'][$key] = array_merge($ret['data'][$key], $sku_info);

            $ret['data'][$key]['shop_name'] = $base_shop_arr[$value['shop_code']];
            $ret['data'][$key]['embrace_time'] = $value['embrace_time'];
            $ret['data'][$key]['store_name'] = $base_store_arr[$value['store_code']];
            $ret['data'][$key]['sale_channel_code'] = $base_sale_channel_arr[$value['sale_channel_code']];
            $ret['data'][$key]['pay_type_html'] = $this->pay_type[$value['pay_type']];
            //$ret['data'][$key]['express_name'] = $base_express[$value['express_code']];
            $ret['data'][$key]['is_back_html'] = $value['is_back'] == 1 ? '是' : '否';
            $ret['data'][$key]['status_text'] = $this->get_status_text($value);
            $ret['data'][$key]['express_code_name'] = oms_tb_val('base_express', 'express_name', array('express_code' => $value['express_code']));
            //获取问题类型
            $ret['data'][$key]['problem_html'] = '';
            if (isset($value['is_problem']) && $value['is_problem'] == 1) {
                $tag = load_model("oms/SellRecordTagModel")->get_all(array('sell_record_code' => $value['sell_record_code'], 'tag_type' => 'problem'));
                if ($tag['status'] == '1') {
                    $_t_pm = array();
                    foreach ($tag['data'] as $t) {
                        if ($t['tag_desc'] != '') {
                            $_t_pm[] = $t['tag_desc'];
                        } else {
                            $_t_pm[] = oms_tb_val('base_question_label', 'question_label_name', array('question_label_code' => $t['tag_v']));
                        }
                    }
                    $ret['data'][$key]['problem_html'] = implode(';', $_t_pm);
                }
            }
            //分销订单处理
            if ($value['is_fenxiao'] > 0) {
                $ret['data'][$key]['express_money'] = $value['fx_express_money'];
                $ret['data'][$key]['paid_money'] = $value['fx_payable_money'];
                $ret['data'][$key]['avg_money'] = $value['fx_amount'];
            }
            //如果不开票，过滤发票数据
            if($value['invoice_status'] == '0'){
                $ret['data'][$key]['invoice_title'] = '';
                $ret['data'][$key]['invoice_content'] = '';
            }
        }
        //导出解密
        if ($filter['ctl_type'] == 'export' && isset($filter['ctl_export_conf']) && $filter['ctl_export_conf'] == 'sell_record_quehuo' && !empty($filter['__t_user_code'])) {
            $is_security_role = load_model('sys/UserModel')->is_security_role($filter['__t_user_code']);
            if ($is_security_role === true) {
                $ret['data'] = load_model('sys/security/OmsSecurityOptModel')->get_sell_record_decrypt_list($ret['data']);
                $log = array('user_id' => 0, 'user_code' => $filter['__t_user_code'], 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => '网络订单', 'yw_code' => '', 'operate_type' => '导出', 'operate_xq' => '缺货订单列表导出解密数据');
                load_model('sys/OperateLogModel')->insert($log);
            }
        }
        return $this->format_ret(1, $ret);
    }

    function get_quehuo_goods($sql_main, $sql_values, $filter) {
        $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
        if (!empty($sku_arr)) {
            $sku_str = implode("','", $sku_arr);
        }
        $sql = "SELECT
                    rl.store_code,
                    rl.goods_num,
                    rl.shop_code,
                    r2.num,
                    r2.sku,
                    r2.platform_spec,
                    r2.lock_num,
                    r2.goods_code,
                    sum(r2.num) as num_total,
                    sum(r2.num-r2.lock_num) as short_num_total";
        $sql_where = " FROM
                            oms_sell_record rl,
                            oms_sell_record_detail r2
                      WHERE
                      rl.sell_record_code = r2.sell_record_code";

        $sql_where .= " AND  r2.lock_num<r2.num ";
        if (isset($filter['goods_code']) && $filter['goods_code'] !== '') {
            $sql_where .= " AND r2.goods_code LIKE :goods_code ";
            $sql_values[':goods_code'] = '%' . $filter['goods_code'] . '%';
        }
        if (isset($filter['barcode']) && $filter['barcode'] !== '') {
            $sql_where .= " AND r2.sku IN ('{$sku_str}') ";
        }
        //商品名称
        if (isset($filter['goods_name']) && $filter['goods_name'] !== '') {
            $sql_g = "select goods_code from base_goods where goods_name like '%{$filter['goods_name']}%' ";
            $goods_name_ret = $this->db->get_all_col($sql_g);
            if (empty($goods_name_ret)) {
                $sql_where .= " and 1=2 ";
            } else {
                $str_goods_code = $this->arr_to_in_sql_value($goods_name_ret, 'goods_code', $sql_values);
                $sql_where .= " AND r2.goods_code in ( " . $str_goods_code . " ) ";
            }
        }

        $sql .= $sql_where;
        $sql = substr_replace($sql_main, $sql, 0, 32);
        $sql .= "group by r2.sku,rl.store_code,rl.shop_code order by rl.store_code ";
        $data['data'] = $this->db->get_all($sql, $sql_values);
        foreach ($data['data'] as $key => $value) {
            if ($value['short_num_total'] == 0) {
                unset($data['data'][$key]);
                continue;
            }
            $key_arr = array('barcode', 'goods_name', 'spec1_name', 'spec2_name', 'gb_code');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
            $data['data'][$key] = array_merge($data['data'][$key], $sku_info);
            $data['data'][$key]['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $value['store_code']));
            ;
            $data['data'][$key]['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
            $data['data'][$key]['spec1_name'] = $sku_info['spec1_name'];
            $data['data'][$key]['spec2_name'] = $sku_info['spec2_name'];
        }
        return $this->format_ret(1, $data);
    }

    //发货订单列表
    function get_deliver_by_page($filter) {
        //去掉空格
        foreach ($filter as $key => $v) {
            $filter[$key] = trim($v);
        }
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = $filter['keyword'];
        }
        $detail_table = "oms_sell_record_detail";
        $sql_values = array();
        $sql_join = "";
        $sql_join .= " left join  {$detail_table} rr on  r1.sell_record_code = rr.sell_record_code ";
        $sub_sql = "select sell_record_code from {$detail_table} r2
                    inner join base_goods r3 on r3.goods_code = r2.goods_code
                    where 1";
        $sub_values = array();
        $sql_main = "FROM {$this->table} r1 $sql_join WHERE r1.is_fenxiao = 0 and r1.order_status = 1 and r1.shipping_status > 0 ";

        //商店仓库权限
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('r1.store_code', $filter_store_code);
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('r1.shop_code', $filter_shop_code);

        $wms_store = load_model('sys/WmsConfigModel')->get_wms_store_all();
        $o2o_store = load_model('o2o/O2oEntryModel')->get_o2o_store_all();
        $no_select_store = array_merge($wms_store, $o2o_store);

        if (!empty($no_select_store)) {
            $no_select_store = array_unique($no_select_store);
            $no_store_str = $this->arr_to_in_sql_value($no_select_store, 'store_code', $sql_values);
            $sql_main .= " AND r1.store_code not in($no_store_str) ";
        }


        //是否称重
        if (isset($filter['is_weigh']) && $filter['is_weigh'] != 'all') {
            $sql_main .= " AND r1.is_weigh = :is_weigh ";
            $sql_values[':is_weigh'] = $filter['is_weigh'];
        }
        //生产波次
        if (isset($filter['waves_record_id']) && $filter['waves_record_id'] != 'all') {
            if ($filter['waves_record_id'] == '0') {
                $sql_main .= " AND r1.waves_record_id < 1";
            } else {
                $sql_main .= " AND r1.waves_record_id > 0";
            }
        }
        if (!isset($filter['waves_record_id'])) {
            $sql_main .= " AND r1.waves_record_id <= 1";
        }
        //发货状态
        if (!empty($filter['shipping_status']) && $filter['shipping_status'] != 'all') {
            $sql_main .= " AND r1.shipping_status in (:shipping_status) ";
            $sql_values[':shipping_status'] = explode(',', $filter['shipping_status']);
        }
        if (!isset($filter['shipping_status'])) {
            $sql_main .= " and r1.shipping_status in (1,2,3) ";
        }

        //订单号
        if (!empty($filter['sell_record_code'])) {
            $sql_main .= " AND r1.sell_record_code LIKE :sell_record_code ";
            $sql_values[':sell_record_code'] = '%' . $filter['sell_record_code'] . '%';
        }
        //交易号
        if (!empty($filter['deal_code'])) {
            $sql_main .= " AND r1.deal_code_list LIKE :deal_code ";
            $sql_values[':deal_code'] = '%' . $filter['deal_code'] . '%';
        }
        //仓库
        if (isset($filter['store_code']) && $filter['store_code'] != '') {
            $sql_main .= " AND r1.store_code in (:store_code) ";
            $sql_values[':store_code'] = explode(',', $filter['store_code']);
        }

        //配送方式
        if (isset($filter['express_code']) && $filter['express_code'] != '') {
            $sql_main .= " AND r1.express_code in (:express_code) ";
            $sql_values[':express_code'] = explode(',', $filter['express_code']);
        }
        //商品编码
        if (!empty($filter['goods_code'])) {
            $sub_sql .= " AND r2.goods_code LIKE :goods_code";
            $sub_values[':goods_code'] = '%' . $filter['goods_code'] . '%';
        }
        //商品条形码
        $is_sku = FALSE;
        if (!empty($filter['barcode'])) {

//
//            $sub_sql .= " AND r2.barcode LIKE :barcode ";
//            $sub_values[':barcode'] = '%' . $filter['barcode'] . '%';

            $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
            if (empty($sku_arr)) {
                $sub_sql .= " AND 1=2 ";
            } else {
                $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
                $sub_sql .= " AND r2.sku in({$sku_str}) ";
            }
            $is_sku = TRUE;
        }
        //套餐条形码
        if (isset($filter['combo_barcode']) && $filter['combo_barcode'] != '') {
            $combo_sku_arr = load_model('prm/GoodsComboModel')->get_combo_sku_by_barcode(trim($filter['combo_barcode']));
            if (!empty($combo_sku_arr)) {
                $combo_sku_str = $this->arr_to_in_sql_value($combo_sku_arr, 'combo_sku', $sql_values);
                $sql_main .= " AND rr.combo_sku in ({$combo_sku_str}) ";
            } else {
                $sql_main .= " AND 1=2 ";
            }
        }
        //包含SKU
        if (!empty($filter['sku'])) {
            $sub_sql .= " AND r2.sku LIKE :sku";
            $sub_values[':sku'] = '%' . $filter['sku'] . '%';
        }
        //排除商品编码
        if (!empty($filter['goods_code_exp'])) {
            $sub_sql .= " AND r2.goods_code NOT LIKE :goods_code_exp";
            $sub_values[':goods_code_exp'] = '%' . $filter['goods_code_exp'] . '%';
        }
        //付款类型
        if (!empty($filter['pay_type'])) {
            $sql_main .= " AND r1.pay_type = :pay_type";
            $sql_values[':pay_type'] = $filter['pay_type'];
        }

        //sku种类数
        if (!empty($filter['sku_num'])) {
            $sql_main .= " AND r1.sku_num = :sku_num ";
            $sql_values[':sku_num'] = $filter['sku_num'];
        }
        //商品数量
        if (!empty($filter['num_start'])) {
            $sql_main .= " AND r1.goods_num >= :num_start ";
            $sql_values[':num_start'] = $filter['num_start'];
        }
        if (!empty($filter['num_end'])) {
            $sql_main .= " AND r1.goods_num <= :num_end ";
            $sql_values[':num_end'] = $filter['num_end'];
        }
        //发票
        if (!empty($filter['invoice_type'])) {
            if ($filter['invoice_type'] == '1') {
                $sql_main .= " AND r1.invoice_type <> ''";
            } else {
                $sql_main .= " AND r1.invoice_type = ''";
            }
        }
        //销售平台
        if (!empty($filter['source'])) {
            $sql_main .= " AND r1.sale_channel_code in (:source) ";
            $sql_values[':source'] = explode(',', $filter['source']);
        }
        //店铺
        if (!empty($filter['shop_code'])) {
            $sql_main .= " AND r1.shop_code in (:shop_code) ";
            $sql_values[':shop_code'] = explode(',', $filter['shop_code']);
        }
        //买家留言
        if (isset($filter['buyer_remark']) && $filter['buyer_remark'] != '') {
            if ($filter['buyer_remark'] == '1') {
                $sql_main .= " AND r1.buyer_remark <> ''";
            } else {
                $sql_main .= " AND r1.buyer_remark = ''";
            }
        }

        //商家留言
        if (isset($filter['seller_remark']) && $filter['seller_remark'] != '') {
            if ($filter['seller_remark'] == '1') {
                $sql_main .= " AND r1.seller_remark <> ''";
            } else {
                $sql_main .= " AND r1.seller_remark = ''";
            }
        }
        //是否加急单
        if (isset($filter['is_rush']) && $filter['is_rush'] != '') {
            if ($filter['is_rush'] == '1') {
                $sql_main .= " AND r1.is_rush = '1'";
            } else {
                $sql_main .= " AND r1.is_rush = '0'";
            }
        }
        //仓库留言
        if (isset($filter['store_remark']) && $filter['store_remark'] != '') {
            if ($filter['store_remark'] == '1') {
                $sql_main .= " AND r1.store_remark <> ''";
            } else {
                $sql_main .= " AND r1.store_remark = ''";
            }
        }
        //品牌
        if (isset($filter['brand_code']) && $filter['brand_code'] != '') {
            $sub_sql .= " AND r3.brand_code in (:brand_code) ";
            $sub_values[':brand_code'] = explode(',', $filter['brand_code']);
        }
        //季节
        if (isset($filter['season_code']) && $filter['season_code'] != '') {
            $sub_sql .= " AND r3.season_code in (:season_code) ";
            $sub_values[':season_code'] = explode(',', $filter['season_code']);
        }

        //付款时间
        if (!empty($filter['pay_time_start'])) {
            $sql_main .= " AND r1.pay_time >= :pay_time_start ";
            $pay_time_start = strtotime(date("Y-m-d", strtotime($filter['pay_time_start'])));
            if ($pay_time_start == strtotime($filter['pay_time_start'])) {
                $sql_values[':pay_time_start'] = $filter['pay_time_start'];
            } else {
                $sql_values[':pay_time_start'] = $filter['pay_time_start'];
            }
        }
        if (!empty($filter['pay_time_end'])) {
            $sql_main .= " AND r1.pay_time <= :pay_time_end ";
            $pay_time_end = strtotime(date("Y-m-d", strtotime($filter['pay_time_end'])));
            if ($pay_time_end == strtotime($filter['pay_time_end'])) {
                $sql_values[':pay_time_end'] = $filter['pay_time_end'];
            } else {
                $sql_values[':pay_time_end'] = $filter['pay_time_end'];
            }
        }

        //通知配货时间
        if (!empty($filter['is_notice_time_start'])) {
            $sql_main .= " AND r1.is_notice_time >= :is_notice_time_start ";
            $is_notice_time_start = strtotime(date("Y-m-d", strtotime($filter['is_notice_time_end'])));
            if ($is_notice_time_start == strtotime($filter['is_notice_time_start'])) {
                $sql_values[':is_notice_time_start'] = $filter['is_notice_time_start'];
            } else {
                $sql_values[':is_notice_time_start'] = $filter['is_notice_time_start'];
            }
        }
        if (!empty($filter['is_notice_time_end'])) {
            $sql_main .= " AND r1.is_notice_time <= :is_notice_time_end ";
            $is_notice_time_end = strtotime(date("Y-m-d", strtotime($filter['is_notice_time_end'])));
            if ($is_notice_time_end == strtotime($filter['is_notice_time_end'])) {
                $sql_values[':is_notice_time_end'] = $filter['is_notice_time_end'];
            } else {
                $sql_values[':is_notice_time_end'] = $filter['is_notice_time_end'];
            }
        }
        //计划发货时间
        if (!empty($filter['plan_time_start'])) {
            $sql_main .= " AND r1.plan_send_time >= :plan_time_start ";
            $sql_values[':plan_time_start'] = $filter['plan_time_start'];
        }
        if (!empty($filter['plan_time_end'])) {
            $sql_main .= " AND r1.plan_send_time <= :plan_time_end ";
            $sql_values[':plan_time_end'] = $filter['plan_time_end'];
        }
        //下单时间
        if (!empty($filter['record_time_start'])) {
            $sql_main .= " AND r1.record_time >= :record_time_start ";
            $record_time_start = strtotime(date("Y-m-d", strtotime($filter['record_time_start'])));
            if ($record_time_start == strtotime($filter['record_time_start'])) {
                $sql_values[':record_time_start'] = $filter['record_time_start'];
            } else {
                $sql_values[':record_time_start'] = $filter['record_time_start'];
            }
        }
        if (!empty($filter['record_time_end'])) {
            $sql_main .= " AND r1.record_time <= :record_time_end ";
            $record_time_end = strtotime(date("Y-m-d", strtotime($filter['record_time_end'])));
            if ($record_time_end == strtotime($filter['record_time_end'])) {
                $sql_values[':record_time_end'] = $filter['record_time_end'];
            } else {
                $sql_values[':record_time_end'] = $filter['record_time_end'];
            };
        }
        //省
        if (isset($filter['province']) && $filter['province'] !== '') {
            $sql_main .= " AND r1.receiver_province in({$filter['province']})";
        }
        //订单标签
        if (isset($filter['order_tag']) && $filter['order_tag'] !== '') {
            $tag_arr = explode(',', $filter['order_tag']);
            $tag_record = load_model('oms/SellRecordTagModel')->get_sell_record_by_tag($tag_arr);
            if (!empty($tag_record)) {
                $tag_record_str = $this->arr_to_in_sql_value($tag_record, 'sell_record_code', $sql_values);
                $sql_main .= " AND r1.sell_record_code in ({$tag_record_str}) ";
            } else {
                $sql_main .= " AND 1=2 ";
            }
        }
        //子查询合并
        if (!empty($sub_values) || $is_sku) {
            $sql_main .= " AND r1.sell_record_code in ({$sub_sql})";
            $sql_values = array_merge($sql_values, $sub_values);
        }

        $select = 'r1.*';
        $sql_main .= " GROUP BY  ";

        if (isset($filter['is_sort']) && $filter['is_sort'] != '') {
            $sql_main .= "r1." . $filter['is_sort'] . " DESC ";
            $sql_main .= ",r1.sell_record_code DESC  ";
        } else {
            $sql_main .= "r1.sell_record_code DESC  ";
        }
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, $group_by = TRUE);
        foreach ($data['data'] as $key => &$value) {
            $value['paid_money'] = sprintf("%.2f", $value['paid_money']);
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
            $value['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $value['store_code']));
            $value['express_name'] = oms_tb_val('base_express', 'express_name', array('express_code' => $value['express_code']));
        }

        load_model('common/TBlLogModel')->set_log_multi($data['data'], 'search');

        return $this->format_ret(1, $data);
    }

    function get_detail_by_sell_record_code($sell_record_code, $process_refund = 0, $keep_key = 0) {
        $sql = "select * from oms_sell_record_detail where sell_record_code = :sell_record_code";
        $data = $this->db->get_all($sql, array("sell_record_code" => $sell_record_code));
        //检查是否WMS缺货
        $ret = load_model("oms/SellRecordTagModel")->is_exists_question($sell_record_code, 'WMS_SHORT_ORDER');
        $quhuo_barcode = array();
        if (!empty($ret)) {
            $sql = 'SELECT barcode FROM wms_trade_quehuo_mx WHERE sell_record_code=:code';
            $quhuo_barcode = $this->db->get_all($sql, array(':code' => $sell_record_code));
            $quhuo_barcode = array_column($quhuo_barcode, 'barcode');
        }

        $result = array();
        $order_status_text = require_conf("sys/order_status_text");
        foreach ($data as $sub_data) {
            if ($sub_data['api_refund_num'] > 0) {
                $sub_data['goods_status'] = $order_status_text['mai_return'];
            }

            $key_arr = array('spec1_code', 'spec1_name', 'spec2_code', 'spec2_name', 'barcode', 'goods_name', 'goods_thumb_img');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($sub_data['sku'], $key_arr);
            $sub_data = array_merge($sub_data, $sku_info);
            if (in_array($sub_data['barcode'], $quhuo_barcode)) {
                $sub_data['goods_status'] .= $order_status_text['short'];
            }

            if (!empty($sub_data['pic_path'])) {
                $html_arr = array();
                $html_arr[] = "<img width='50px' height='50px' src='{$sub_data['pic_path']}' />";
                $sub_data['pic_path'] = join('', $html_arr);
            } else {
                $html_arr = array();
                if (!empty($sku_info['goods_thumb_img'])) {
                    $html_arr[] = "<img width='50px' height='50px' src='{$sku_info['goods_thumb_img']}' />";
                } else {
                    $html_arr[] = "";
                }
                $sub_data['pic_path'] = join('', $html_arr);
            }
            if ($sub_data['is_gift'] > 0) {
                $sub_data['avg_money'] = 0;
            }
            $ks = $sub_data['deal_code'] . ',' . $sub_data['sku'] . ',' . $sub_data['is_gift'];
            $result[$ks] = $sub_data;
        }
        //echo '<hr/>$process_refund<xmp>'.var_export($process_refund,true).'</xmp>';
        if ($process_refund == 0) {
            if ($keep_key == 0) {
                return array_values($result);
            } else {
                return $result;
            }
        }

        foreach ($result as $ks => $sub_result) {
            //原始订单数量
            $result[$ks]['source_num'] = $sub_result['num'];
            //已退数量
            $result[$ks]['refund_num'] = $sub_result['return_num'];
            $result[$ks]['returnable_num'] = $sub_result['num'] - $sub_result['return_num'];
        }
        //echo '<hr/>$result<xmp>'.var_export($result,true).'</xmp>';die;
        if ($keep_key == 0) {
            return array_values($result);
        } else {
            return $result;
        }
    }

    function get_return_detail_by_sell_record_code($sell_record_code, $process_refund = 0, $keep_key = 0) {
        $sql = "select * from oms_sell_record_detail where sell_record_code = :sell_record_code";
        $data = $this->db->get_all($sql, array("sell_record_code" => $sell_record_code));
        // filter_fk_name($data, array('spec1_code|spec1_code', 'spec2_code|spec2_code', 'sku|barcode', 'goods_code|goods_code'));
        $result = array();
        //$_del_mx = array();
        foreach ($data as $sub_data) {
            $key_arr = array('spec1_code', 'spec1_name', 'spec2_code', 'spec2_name', 'barcode', 'goods_name');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($sub_data['sku'], $key_arr);
            $sub_data = array_merge($sub_data, $sku_info);
            if (!empty($sub_data['pic_path'])) {
                $html_arr = array();
                $html_arr[] = "<img width='50px' height='50px' src='{$sub_data['pic_path']}' />";
                $sub_data['pic_path'] = join('', $html_arr);
            }
            if ($sub_data['is_gift'] > 0) {
                $sub_data['avg_money'] = 0;
            }
//            $ks = $sub_data['deal_code'] . ',' . $sub_data['sku'] . ',' . $sub_data['is_gift'];
            $ks = $sub_data['deal_code'] . ',' . $sub_data['sku'];
            if (array_key_exists($ks, $result)) {
                $result[$ks]['num'] = $result[$ks]['num'] + $sub_data['num'];
            } else {
                $result[$ks] = $sub_data;
                $result[$ks]['gift_num'] = 0;
            }

            if ($sub_data['is_gift'] == 1) {
                $result[$ks]['gift_num'] += $sub_data['num'];
            }
        }
        //echo '<hr/>$process_refund<xmp>'.var_export($process_refund,true).'</xmp>';
        if ($process_refund == 0) {
            if ($keep_key == 0) {
                return array_values($result);
            } else {
                return $result;
            }
        }

        foreach ($result as $ks => $sub_result) {
            //原始订单数量
            $result[$ks]['source_num'] = $sub_result['num'];
            $result[$ks]['real_num'] = $sub_result['num'] - $sub_result['gift_num'];
        }
        //echo '<hr/>$result<xmp>'.var_export($result,true).'</xmp>';die;
        if ($keep_key == 0) {
            return array_values($result);
        } else {
            return $result;
        }
    }

    //退单列表单个展开
    function get_return_detail_by_sell_return_code($sell_return_code, $sell_record_code, $process_refund = 0, $keep_key = 0) {
        $sql = "select * from oms_sell_return_detail where sell_return_code = :sell_return_code";
        $data = $this->db->get_all($sql, array(":sell_return_code" => $sell_return_code));
        $sql_2 = " SELECT sell_record_code,sku,num FROM oms_sell_record_detail WHERE sell_record_code = :sell_record_code ";
        $r = $this->db->get_all($sql_2, array(":sell_record_code" => $sell_record_code));
        foreach ($r as $value) {
            //原单商品数量
            $relation_num[$value['sell_record_code'] . ',' . $value['sku']] = $value['num'];
        }
        $result = array();
        foreach ($data as $sub_data) {
            $sub_data['relation_num'] = empty($relation_num[$sub_data['sell_record_code'] . ',' . $sub_data['sku']]) ? 0 : $relation_num[$sub_data['sell_record_code'] . ',' . $sub_data['sku']];
            $key_arr = array('spec1_code', 'spec1_name', 'spec2_code', 'spec2_name', 'barcode', 'goods_name');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($sub_data['sku'], $key_arr);
            $sub_data = array_merge($sub_data, $sku_info);
            $ks = $sub_data['deal_code'] . ',' . $sub_data['sku'];
            $sub_data['goods_price'] = sprintf('%.2f', $sub_data['goods_price']);
            $sub_data['avg_money'] = sprintf('%.2f', $sub_data['avg_money']);
            $result[$ks] = $sub_data;
        }
        if ($process_refund == 0) {
            if ($keep_key == 0) {
                return array_values($result);
            } else {
                return $result;
            }
        }
        if ($keep_key == 0) {
            return array_values($result);
        } else {
            return $result;
        }
    }

    function remove_short($sell_record_code, $is_skip_priv = 0, $force = 0) {
        //#############权限
        if ($is_skip_priv == 0 && $force == 0) {
            if (!load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/remove_short')) {
                return $this->format_ret(-1, '', "无权访问");
            }
        }

        //#############权限 强制接触缺货
        if ($is_skip_priv == 0 && $force == 1) {
            if (!load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/force_remove_short')) {
                return $this->format_ret(-1, '', "无权访问");
            }
        }

        //###########
        $record = $this->get_record_by_code($sell_record_code);
        $detail = $this->get_detail_list_by_code($sell_record_code);
        if ($is_skip_priv == 1) {

            $this->check_is_skip_remove_short($record, $detail);


            if (empty($detail)) {
                $this->add_action($sell_record_code, '解除缺货', '解除缺货失败,库存不足');
                return $this->format_ret(-1, '', "解除缺货失败,之前解除缺货未成功。");
            }
        }
        if ($record['order_status'] > 0) {
            return $this->format_ret(-1, '', '解除缺货失败,单据信息变化');
        }
        $this->begin_trans();
        $ret = load_model('oms/SellRecordOptModel')->lock_detail($record, $detail, 1, 0, $force);
//        if( CTX()->saas->get_saas_key()=='2259' &&$sell_record_code =='1706175140686' ){
//            //var_dump($ret,$record, $detail, 1, 0, $force);die;
//        }

        $is_all_lock = load_model('oms/SellRecordOptModel')->is_all_lock;
        if ($ret['status'] != 1 || empty($ret['data'])) {
            $this->rollback();
            $this->add_action($sell_record_code, '解除缺货', '解除缺货失败,库存不足');

            if ($is_skip_priv == 1) {
                $this->set_fail_remove_short_sku($record, $detail);
            }
            return $this->format_ret(-1, '', "解除缺货失败,库存不足。");
        } else {
            $record = $this->get_record_by_code($sell_record_code);
            if ($record['order_status'] > 0) {
                $this->rollback();
                return $this->format_ret(-1, '', '解除缺货失败,单据信息变化');
            }

            $this->commit();
            if ($is_skip_priv == 1) {
                $this->set_fail_remove_short_sku($record);
            }
            //var_dump($is_all_lock);
            if ($is_all_lock == 2) {
                $this->add_action($sell_record_code, '解除缺货', '解除缺货成功');
                return $this->format_ret(1, '', '解除缺货成功');
            } else {
                $this->add_action($sell_record_code, '解除缺货', '解除缺货失败');
                return $this->format_ret(-1, '', '解除缺货失败');
            }
        }
        //echo '<hr/>$ret<xmp>'.var_export($ret,true).'</xmp>';
    }

    private $short_record_sku = array();

    function set_fail_remove_short_sku($record) {

        $detail = $this->db->get_all("select * from oms_sell_record_detail where sell_record_code =:sell_record_code AND  num>lock_num ", array(':sell_record_code' => $record['sell_record_code']));

        if (!empty($detail)) {
            foreach ($detail as $val) {
                $this->short_record_sku[$record['store_code']][$val['sku']] = $record['sell_record_code'];
            }
        }
    }

    function check_is_skip_remove_short($record, &$detail) {
        foreach ($detail as $key => $val) {
            //之前未解除成功 ，所以跳过
            if (isset($this->short_record_sku[$record['store_code']][$val['sku']])) {
                unset($detail[$key]);
            }
        }
    }

    function cli_batch_remove_short() {
        $sql_inv = "SELECT sku,store_code from goods_inv where out_num>0 AND stock_num>lock_num ";
        $data = $this->db->get_all($sql_inv);
        if (empty($data)) {
            echo "没有可处理的商品";
            return;
        }
        $sku_where = array();
        foreach ($data as $val) {
            $sku_where[] = "( d.sku = '{$val['sku']}' AND r.store_code = '{$val['store_code']}' AND d.lock_num<d.num  ) ";
        }
        $sku_where_str = implode(" OR ", $sku_where);

        $sql = "select DISTINCT r.sell_record_code from oms_sell_record r INNER JOIN
           oms_sell_record_detail d ON r.sell_record_code=d.sell_record_code
           where r.order_status=0 AND r.shipping_status=0  AND  r.must_occupy_inv = 1  and r.lock_inv_status in(0,2,3)
           and ({$sku_where_str}) order by r.plan_send_time";

        $db_sell = ctx()->db->get_all($sql);
        if (empty($db_sell)) {
            echo "没有可处理的缺货单";
            return;
        }
        foreach ($db_sell as $sub_sell) {
            $ret = $this->remove_short($sub_sell['sell_record_code'], 1);
            echo $sub_sell['sell_record_code'] . $ret['message'] . "\n";
        }
        echo "批量解除缺货 处理完成。\n";
        return $this->format_ret(1);
    }

    //一键解除缺货
    function remove_a_key() {
        //从库存表获取有缺货信息的商品
        $inv_record_list = load_model("prm/InvModel")->get_short_record();
        if ($inv_record_list['status'] == '1') {
            $ids = array();
            foreach ($inv_record_list['data'] as $inv_record) {
                //如果有多的库存且缺货单正好缺此商品则解除缺货
                if ($inv_record['stock_num'] - $inv_record['lock_num'] > 0) {
                    $sql = "select t1.sell_record_code from oms_sell_record t1 left join oms_sell_record_detail t2 on t1.sell_record_code = t2.sell_record_code where t2.is_real_stock_out>0 and t2.sku = :sku and t1.store_code = :store_code group by t1.sell_record_code";
                    $sql_value[':sku'] = $inv_record['sku'];
                    $sql_value[':store_code'] = $inv_record['store_code'];
                    $sell_record_code = $this->db->get_all($sql, $sql_value);
                    foreach ($sell_record_code as $id) {
                        $ids[] = $id['sell_record_code'];
                    }
                }
            }
            array_flip(array_flip($ids));
            return $this->remove_short($ids);
        } else {
            return $this->format_ret(1);
        }
    }

    function get_sell_problem_map() {
        $sql = "select sell_problem_code,sell_problem_name from base_sell_problem where is_active = 1";
        $db_arr = ctx()->db->getAll($sql);
        $arr = array();
        foreach ($db_arr as $sub_arr) {
            $arr[$sub_arr['sell_problem_code']] = $sub_arr['sell_problem_name'];
        }
        return $arr;
    }

    function get_problem_desc($sell_record_codes) {
        if (is_array($sell_record_codes)) {
            $sell_record_code_list = "'" . join("','", $sell_record_codes) . "'";
        } else {
            $sell_record_code_list = "'" . $sell_record_codes . "'";
        }
        $sql = "select sell_record_code,tag_v,tag_desc from oms_sell_record_tag where tag_type='problem' and sell_record_code in($sell_record_code_list)";
        $db_arr = ctx()->db->getAll($sql, array(':sell_record_code' => $sell_record_codes));
        $desc_arr = array();
        foreach ($db_arr as $sub_arr) {
            $desc_arr[$sub_arr['sell_record_code']]['tag_v'][] = $sub_arr['tag_v'];
            $desc_arr[$sub_arr['sell_record_code']]['tag_desc'][] = $sub_arr['tag_desc'];
        }
        if (is_array($sell_record_codes)) {
            return $desc_arr;
        } else {
            return @$desc_arr[$sell_record_codes];
        }
    }

    function get_problem_desc1($sell_record_codes) {
        if (is_array($sell_record_codes)) {
            $sell_record_code_list = "'" . join("','", $sell_record_codes) . "'";
        } else {
            $sell_record_code_list = "'" . $sell_record_codes . "'";
        }
        $sql = "select sell_record_code,tag_v,tag_desc from oms_sell_record_tag where sell_record_code in($sell_record_code_list)";
        $db_arr = ctx()->db->getAll($sql, array(':sell_record_code' => $sell_record_codes));
        $desc_arr = array();
        foreach ($db_arr as $sub_arr) {
            $desc_arr[$sub_arr['sell_record_code']]['tag_v'][] = $sub_arr['tag_v'];
            $desc_arr[$sub_arr['sell_record_code']]['tag_desc'][] = $sub_arr['tag_desc'];
        }
        if (is_array($sell_record_codes)) {
            return $desc_arr;
        } else {
            return @$desc_arr[$sell_record_codes];
        }
    }

    function get_pending_list($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql_values = array();
        $sql_join = "";
        $sub_sql = "select sell_record_code from {$this->detail_table} rr where 1";
        $sub_values = array();
        $sql_main = "FROM {$this->table} rl $sql_join WHERE is_pending = 1 and order_status<>3 ";

        //店铺仓库权限
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code', $filter_store_code);
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('rl.shop_code', $filter_shop_code);
        //是否是我锁定的
        if (isset($filter['is_my_lock']) && $filter['is_my_lock'] == '1') {
            $sys_user = load_model("oms/SellRecordOptModel")->sys_user();
            $sql_main .= " AND rl.is_lock = 1 AND rl.is_lock_person = :user_code";
            $sql_values[':user_code'] = $sys_user['user_code'];
        }
        //订单号
        if (!empty($filter['sell_record_code'])) {
            $sql_main .= " AND rl.sell_record_code LIKE :sell_record_code ";
            $sql_values[':sell_record_code'] = '%' . $filter['sell_record_code'] . '%';
        }
        //交易号
        if (!empty($filter['deal_code_list'])) {
            $sql_main .= " AND rl.deal_code_list LIKE :deal_code_list ";
            $sql_values[':deal_code_list'] = '%' . $filter['deal_code_list'] . '%';
        }
        //销售平台
        if (!empty($filter['sale_channel_code'])) {
            $arr = explode(',', $filter['sale_channel_code']);

            $str = $this->arr_to_in_sql_value($arr, 'sale_channel_code', $sql_values);
            $sql_main .= " AND rl.sale_channel_code IN ({$str}) ";
        }
        //店铺
        if (!empty($filter['shop_code'])) {
            $arr = explode(',', $filter['shop_code']);

            $str = $this->arr_to_in_sql_value($arr, 'shop_code', $sql_values);
            $sql_main .= " AND rl.shop_code IN ({$str}) ";
        }
        //挂起原因
        if (!empty($filter['is_pending_code'])) {
            $arr = explode(',', $filter['is_pending_code']);
            $str = $this->arr_to_in_sql_value($arr, 'is_pending_code', $sql_values);
            $sql_main .= " AND rl.is_pending_code IN ({$str}) ";
        }
        //挂起备注
        if (!empty($filter['is_pending_memo'])) {
            $sql_main .= " AND rl.is_pending_memo LIKE :is_pending_memo ";
            $sql_values[':is_pending_memo'] = '%' . $filter['is_pending_memo'] . '%';
        }
        //商品编码
        if (!empty($filter['goods_code'])) {
            $sub_sql .= " AND rr.goods_code LIKE :goods_code";
            $sub_values[':goods_code'] = '%' . $filter['goods_code'] . '%';
        }
        //商品条形码
        if (!empty($filter['barcode'])) {
//            $sub_sql .= " AND rr.barcode LIKE :barcode ";
//            $sub_values[':barcode'] = '%' . $filter['barcode'] . '%';
//
            $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
            if (empty($sku_arr)) {
                $sub_sql .= " AND 1=2 ";
            } else {
                $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
                $sub_sql .= " AND rr.sku in({$sku_str}) ";
            }
        }
        //订单标签
        if (isset($filter['order_tag']) && $filter['order_tag'] !== '') {
            $tag_arr = explode(',', $filter['order_tag']);
            $_tag_str = "'" . implode("','", $tag_arr) . "'";
            if (in_array('none', $tag_arr)) {
                if (count($tag_arr) > 1) {
                    $sql_tag = "SELECT rn.sell_record_code,rt.tag_v FROM    oms_sell_record rn LEFT JOIN oms_sell_record_tag rt ON rn.sell_record_code = rt.sell_record_code AND rt.tag_type = 'order_tag' having tag_v  in({$_tag_str}) or tag_v is null";
                } else {
                    $sql_tag = "SELECT rn.sell_record_code,rt.tag_v FROM    oms_sell_record rn LEFT JOIN oms_sell_record_tag rt ON rn.sell_record_code = rt.sell_record_code AND rt.tag_type = 'order_tag' having  tag_v is null";
                }
                $tag_record_data = $this->db->get_all($sql_tag);
                if (!empty($tag_record_data)) {
                    $tag_record = array_column($tag_record_data, 'sell_record_code');
                    $tag_record_str = $this->arr_to_in_sql_value($tag_record, 'sell_record_code', $sql_values);
                    $sql_main .= " AND rl.sell_record_code  in ({$tag_record_str}) ";
                } else {
                    $sql_main .= "AND 1=2";
                }
            } else {
                $tag_record = load_model('oms/SellRecordTagModel')->get_sell_record_by_tag($tag_arr);
                if (!empty($tag_record)) {
                    $tag_record_str = $this->arr_to_in_sql_value($tag_record, 'sell_record_code', $sql_values);
                    $sql_main .= " AND rl.sell_record_code in ({$tag_record_str}) ";
                } else {
                    $sql_main .= " AND 1=2 ";
                }
            }
        }
        //配送方式
        if (!empty($filter['express_code'])) {
            $arr = explode(',', $filter['express_code']);
            $str = $this->arr_to_in_sql_value($arr, 'express_code', $sql_values);
            $sql_main .= " AND rl.express_code IN ({$str}) ";
        }
        //客户留言
        if (!empty($filter['buyer_remark'])) {
            $sql_main .= " AND rl.buyer_remark LIKE :buyer_remark ";
            $sql_values[':buyer_remark'] = '%' . $filter['buyer_remark'] . '%';
        }
        //商家留言
        if (!empty($filter['seller_remark'])) {
            $sql_main .= " AND rl.seller_remark LIKE :seller_remark ";
            $sql_values[':seller_remark'] = '%' . $filter['seller_remark'] . '%';
        }
        //换货单
        if (isset($filter['is_change_record']) && $filter['is_change_record'] !== '') {
            $sql_main .= " AND rl.is_change_record = :is_change_record ";
            $sql_values[':is_change_record'] = $filter['is_change_record'];
        }
        //收货人
        if (!empty($filter['receiver_name'])) {

            $customer_address_id = load_model('crm/CustomerOptModel')->get_customer_address_id_with_search($filter['receiver_name'], 'name');
            if (!empty($customer_address_id)) {
                $customer_address_id_str = implode(",", $customer_address_id);
                $sql_main .= " AND ( rl.receiver_name LIKE :receiver_name  OR rl.customer_address_id in ({$customer_address_id_str}) ) ";
                $sql_values[':receiver_name'] = '%' . $filter['receiver_name'] . '%';
            } else {
                $sql_main .= " AND rl.receiver_name LIKE :receiver_name ";
                $sql_values[':receiver_name'] = '%' . $filter['receiver_name'] . '%';
            }
//            $sql_main .= " AND rl.receiver_name LIKE :receiver_name ";
//            $sql_values[':receiver_name'] = '%' . $filter['receiver_name'] . '%';
        }
        //手机号码
        if (!empty($filter['receiver_mobile'])) {
            $customer_address_id = load_model('crm/CustomerOptModel')->get_customer_address_id_with_search($filter['receiver_mobile'], 'tel');
            if (!empty($customer_address_id)) {
                $customer_address_id_str = implode(",", $customer_address_id);
                $sql_main .= " AND ( rl.receiver_mobile = :receiver_mobile OR rl.customer_address_id in ({$customer_address_id_str}) ) ";
                $sql_values[':receiver_mobile'] = $filter['receiver_mobile'];
            } else {
                $sql_main .= " AND rl.receiver_mobile = :receiver_mobile ";
                $sql_values[':receiver_mobile'] = $filter['receiver_mobile'];
            }
//            $sql_main .= " AND rl.receiver_mobile LIKE :receiver_mobile ";
//            $sql_values[':receiver_mobile'] = '%' . $filter['receiver_mobile'] . '%';
        }
        //国家
        if (isset($filter['country']) && $filter['country'] !== '') {
            $sql_main .= " AND rl.receiver_country = :country ";
            $sql_values[':country'] = $filter['country'];
        }
        //省
        if (isset($filter['province']) && $filter['province'] !== '') {
            $sql_main .= " AND rl.receiver_province = :province ";
            $sql_values[':province'] = $filter['province'];
        }
        //城市
        if (isset($filter['city']) && $filter['city'] !== '') {
            $sql_main .= " AND rl.receiver_city = :city ";
            $sql_values[':city'] = $filter['city'];
        }
        //地区
        if (isset($filter['district']) && $filter['district'] !== '') {
            $sql_main .= " AND rl.receiver_district = :district ";
            $sql_values[':district'] = $filter['district'];
        }
        //详细地址
        if (isset($filter['receiver_addr']) && $filter['receiver_addr'] !== '') {
            $sql_main .= " AND rl.receiver_addr LIKE :receiver_addr ";
            $sql_values[':receiver_addr'] = '%' . $filter['receiver_addr'] . '%';
        }
        //发票
        if (isset($filter['is_invoice']) && ($filter['is_invoice'] == '0' || $filter['is_invoice'] == '1')) {
            if ($filter['is_invoice'] == '0') {
                $sql_main .= " AND rl.invoice_title = '' ";
            } else {
                $sql_main .= " AND rl.invoice_title <> '' ";
            }
        }
        //仓库
        if (!empty($filter['store_code'])) {
            $arr = explode(',', $filter['store_code']);
            $str = $this->arr_to_in_sql_value($arr, 'store_code', $sql_values);
            $sql_main .= " AND rl.store_code IN ({$str}) ";
        }
        //下单时间
        if (!empty($filter['record_time_start'])) {
            $sql_main .= " AND rl.record_time >= :record_time_start ";
            $sql_values[':record_time_start'] = $filter['record_time_start'] . ' 00:00:00';
        }
        if (!empty($filter['record_time_end'])) {
            $sql_main .= " AND rl.record_time <= :record_time_end ";
            $sql_values[':record_time_end'] = $filter['record_time_end'] . ' 23:59:59';
        }
        //挂起时间
        if (!empty($filter['is_pending_time_start'])) {
            $sql_main .= " AND rl.is_pending_time >= :is_pending_time_start ";
            $sql_values[':is_pending_time_start'] = $filter['is_pending_time_start'] . ' 00:00:00';
        }
        if (!empty($filter['is_pending_time_end'])) {
            $sql_main .= " AND rl.is_pending_time <= :is_pending_time_end ";
            $sql_values[':is_pending_time_end'] = $filter['is_pending_time_end'] . ' 23:59:59';
        }
        //支付时间
        if (!empty($filter['pay_time_start'])) {
            $sql_main .= " AND rl.pay_time >= :pay_time_start ";
            $sql_values[':pay_time_start'] = $filter['pay_time_start'] . ' 00:00:00';
        }
        if (!empty($filter['pay_time_end'])) {
            $sql_main .= " AND rl.pay_time <= :pay_time_end ";
            $sql_values[':pay_time_end'] = $filter['pay_time_end'] . ' 23:59:59';
        }
        //解挂时间
        if (!empty($filter['unpsending_time_start'])) {
            $sql_main .= " AND rl.is_unpending_time >= :unpsending_time_start ";
            $sql_values[':unpsending_time_start'] = $filter['unpsending_time_start'] . ' 00:00:00';
        }
        if (!empty($filter['unpsending_time_end'])) {
            $sql_main .= " AND rl.is_unpending_time <= :unpsending_time_end ";
            $sql_values[':unpsending_time_end'] = $filter['unpsending_time_end'] . ' 23:59:59';
        }
        //子查询合并
        if (!empty($sub_sql) && (!empty($filter['goods_code']) || !empty($filter['barcode']))) {
            $sql_main .= " AND rl.sell_record_code in ({$sub_sql})";
            $sql_values = array_merge($sql_values, $sub_values);
        }
        //增值服务
        $sql_main .= load_model('base/SaleChannelModel')->get_values_where('rl.sale_channel_code');

        $select = 'rl.*';
        $sql_main .= " ORDER BY sell_record_id DESC ";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('safety_control'));
        foreach ($data['data'] as $key => &$value) {
            $value['status_text'] = $this->get_status_text($value);
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
            $value['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $value['store_code']));
            $value['express_name'] = oms_tb_val('base_express', 'express_name', array('express_code' => $value['express_code']));
            $value['sale_channel_name'] = oms_tb_val('base_sale_channel', 'sale_channel_name', array('sale_channel_code' => $value['sale_channel_code']));
            $value['is_pending_name'] = oms_tb_val("base_suspend_label", "suspend_label_name", array('suspend_label_code' => $value['is_pending_code']));
            $value['is_unpending_time'] = $value['is_unpending_time'] == '0000-00-00 00:00:00' ? '' : $value['is_unpending_time'];
            $value['tag_desc'] = load_model('oms/SellRecordModel')->get_sell_record_tag_desc($value);
            if ($filter['ctl_type'] == 'view') {
//                $value['receiver_name'] = $this->name_hidden($value['receiver_name']);
//                $value['receiver_address'] = $this->address_hidden($value['receiver_address']);
                safe_data($value, 0);
            }
        }
        load_model('common/TBlLogModel')->set_log_multi($data['data'], 'search');
        return $this->format_ret(1, $data);
    }

    /**
     * 转单时新增订单到oms_sell_record
     * @param type $record 主单据
     * @param type $detail 商品
     */
    function add_api_order($record, $detail, $is_add_action_to_api = 0, $log_msg = '', $type = '') {
        $is_tao_fx = 0;
        $this->db->begin_trans();
        try {
            //添加用户信息
            //添加分销商信息
            if (!empty($record['is_fenxiao']) && $record['is_fenxiao'] == 1) { //淘分销
                $fenxiao_code = !empty($type) && $type == 'import_fx' ? $record['fenxiao_code'] : $record['fenxiao_name'];
                $distributor_row = load_model('base/CustomModel')->get_by_code($fenxiao_code);
                if (empty($distributor_row['data'])) {
                    $distributor = array(
                        'custom_code' => $record['fenxiao_name'],
                        'custom_name' => $record['fenxiao_name'],
                        'shop_code' => $record['shop_code'],
                        'custom_type' => 'tb_fx',
                        'custom_rebate' => 1,
                        'custom_price_type' => 2,
                        'is_effective' => 1,
                        'create_time' => date('Y-m-d H:i:s'),
                    );

                    $ret = load_model('base/CustomModel')->insert($distributor);
                    if ($ret['status'] < 1) {
                        $this->rollback();
                        return $ret;
                    }
                    $fenxiao_id = $ret['data'];
                } else {
                    $fenxiao_id = $distributor_row['data']['custom_id'];
                }
                $record['fenxiao_code'] = !empty($type) && $type == 'import_fx' ? $record['fenxiao_code'] : $record['fenxiao_name'];
                $record['fenxiao_id'] = $fenxiao_id;
                //淘分销转单
                if (($record['sale_channel_code'] == 'taobao' || $record['sale_channel_code'] == 'fenxiao') && $record['is_fenxiao'] == 1) {
                    $is_tao_fx = 1;
                }
            } else if (!empty($record['is_fenxiao']) && $record['is_fenxiao'] == 2) { //普通分销
                //是否开启资金账户
                $fx_finance_account_manage = load_model('sys/SysParamsModel')->get_val_by_code('fx_finance_account_manage');
                //是否开启自动结算（不包括淘宝、淘分销）
                $fx_automatic_settlement = load_model('sys/SysParamsModel')->get_val_by_code('fx_automatic_settlement');
                if ($fx_finance_account_manage['fx_finance_account_manage'] == 1 && $fx_automatic_settlement['fx_automatic_settlement'] == 1 && $record['is_fenxiao'] == 2) {
                    $is_tao_fx = 1;
                }
            }

            $result = $this->db->insert('oms_sell_record', $record);

            if ($result !== true) {
                $this->rollback();
                return $this->format_ret(-1, '', 'SELL_RECORD_SAVE_ERROR');
            }
            //记录订单转入日志
            if ($type == 'import') {
                $this->add_action($record['sell_record_code'], '新增', '导入新增:' . $log_msg);
            } else if ($type == 'import_fx') {
                $this->add_action($record['sell_record_code'], '新增', '导入新增:' . $log_msg);
            } else {
                $this->add_action($record['sell_record_code'], '新增', '转单新增:' . $log_msg);
            }


            if ($is_add_action_to_api == 1) {
                $this->add_action_to_api($record['sale_channel_code'], $record['shop_code'], $record['deal_code'], 'convert');
            }

            //添加商品明细

            $ret = $this->add_detail($detail);

            if ($ret['status'] < 1) {
                $this->rollback();
                return $ret;
            }
            if ($is_add_action_to_api == 0) {
                //计算计划发货时
                $ret = load_model("oms/SellRecordOptModel")->set_sell_plan_send_time($record['sell_record_code']);
            }
            if ($is_tao_fx == 1 && ($record['is_fenxiao'] == 1 || $record['is_fenxiao'] == 2)) {
                load_model('oms/TranslateOrderModel')->is_settlement = 1;
            }
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
        return $this->format_ret(1, $record);
    }

    /* function check_fenxiao_power($record) {
      $sql = "select custom_code from base_store where store_code = :store_code";
      $row = $this->db->get_row($sql, array(':store_code' => $record['store_code']));
      if (!empty($row['custom_code']) && !empty($record['fenxiao_code'])) {
      if ($row['custom_code'] == $record['fenxiao_code']) {
      return 1;
      }
      }
      return 0;
      } */

    function get_fx_express_money($record) {
        $sql = "select fixed_money from base_custom where custom_code = :custom_code";
        $row = $this->db->get_row($sql, array(':custom_code' => $record['fenxiao_code']));
        if (!empty($row)) {
            return $row['fixed_money'];
        }
        return 0;
    }

    /**
     * 添加商品明细（可批量）
     * @param type $detail
     */
    function add_detail($detail) {
        $ret = $this->db->insert($this->detail_table, $detail);
        if ($ret) {
            return $this->format_ret(1);
        } else {
            return $this->format_ret(-1, '', 'SELL_RECORD_SAVE_DETAIL_ERROR');
        }
    }

    /**
     * 对用户输入的逗号分隔的字符串进行处理
     * @param type $str 要处理的字符串
     * @param type $quote 是否为每个加上引号 0：不加，1：加
     * @param type $caps 是否转换大小写，0：不转换，1：转小写，2：转大写
     */
    function deal_strs($str, $quote = 1, $caps = 0) {
        $str = str_replace("，", ",", $str); //将中文逗号转成英文逗号
        $str = str_replace(" ", "", $str); //去掉空格
        $str = trim($str, ','); //去掉前后多余的逗号
        if ($quote = 1) {
            $str = "'" . str_replace(",", "','", $str) . "'";
        }
        if ($caps = 1) {
            $str = strtolower($str);
        } elseif ($caps = 2) {
            $str = strtoupper($str);
        }
        return $str;
    }

    /**
     * 多字段模糊查询sql拼接
     * @param string $strs 要处理的字符串
     * @param string $field 需要查询的字段
     * @return string $sql   拼接好的sql语句
     */
    function get_sql_for_search($strs, $field) {
        $str_arr = explode(",", $strs);
        foreach ($str_arr as $key => $value) {
            $value = str_replace("'", "%", $value);
            $str_arr[$key] = " {$field} like '" . $value . "'";
        }
        $sql = '(' . implode(' OR', $str_arr) . ') ';
        return $sql;
    }

    /**
     * 多字段模糊查询sql拼接
     * @param string $strs 要处理的字符串
     * @param string $field 需要查询的字段
     * @return string $sql   拼接好的sql语句
     */
    function get_sql_for_search2($str_arr, $field, &$sql_values) {
        //$str_arr = explode(",", $strs);
        $loop_i = 0;
        foreach ($str_arr as $key => $value) {
            $field_key = ':' . $field . "_" . $loop_i;
            $value = str_replace("'", "", $value);
            $str_arr[$key] = " {$field} ={$field_key} ";
            $sql_values[$field_key] = $value;
        }
        $sql = '(' . implode(' OR', $str_arr) . ') ';
        return $sql;
    }

    /**
     * 一键确认：已付款未确认、被我锁定或未被锁定、的正常单（正常单：非缺货、非设问、非挂起）；
     */
    function a_key_confirm($request) {
        $sys_user = load_model("oms/SellRecordOptModel")->sys_user();
        $sql = "select * from oms_sell_record where order_status=0 and pay_status=2 and (is_lock=0 or (is_lock=1 and is_lock_person='{$sys_user['user_code']}')) and is_pending=0 and (must_occupy_inv=1 and lock_inv_status=1) and is_problem=0";
        $data = $this->db->get_row($sql);
        $msg = '';
        if ($data) {
            $is_lock = false;
            //未锁定的订单先锁定
            if ($data['is_lock'] == '0') {
                $is_lock = true;
            }
            if ($is_lock) {
                $ret = load_model("oms/SellRecordOptModel")->opt_lock($data['sell_record_code']);
                if ($ret['status'] != '1') {
                    $msg .= "订单" . $data['sell_record_code'] . "确认失败:{$ret['message']}，";
                }
            }
            $ret = load_model("oms/SellRecordOptModel")->opt_confirm($data['sell_record_code']);
            if ($ret['status'] != '1') {
                $msg .= "订单" . $data['sell_record_code'] . "确认失败:{$ret['message']}，";
            }
            if ($is_lock) {
                $ret = load_model("oms/SellRecordOptModel")->opt_unlock($data['sell_record_code']);
                if ($ret['status'] != '1') {
                    $msg .= "订单" . $data['sell_record_code'] . "确认失败:{$ret['message']}，";
                }
            }
        } else {
            $response['status'] = 1;
        }
        if (!empty($msg)) {
            $task_id = load_model('common/TaskModel')->get_task_id($request);
            load_model('common/TaskModel')->save_log($task_id, $msg);
            $response['status'] = 100;
        }
    }

    function get_status_text($value) {
        //print_r($value);
        $order_status_text = require_conf("sys/order_status_text");
        $status_text = "";
        if (isset($value['is_problem']) && $value['is_problem'] == '1') {
            $status_text .= @$order_status_text['question'];
        }
        if (isset($value['is_handwork']) && $value['is_handwork'] == '1') {
            $status_text .= $order_status_text['handle'];
        }
        if (isset($value['is_copy']) && $value['is_copy'] == '1') {
            $status_text .= $order_status_text['copy'];
        }
        if (isset($value['is_pending']) && $value['is_pending'] == '1') {
            $status_text .= $order_status_text['pending'];
        }
        if ($value['must_occupy_inv'] == '1' && $value['lock_inv_status'] != '1') {
            $status_text .= @$order_status_text['short'];
        }
        if (isset($value['is_print_invoice']) && $value['is_print_invoice'] == '1') {
            $status_text .= $order_status_text['piao'];
        }
        if (isset($value['invoice_status']) && $value['invoice_status'] == '1') {
            $status_text .= $order_status_text['piao'];
        }
        if (isset($value['is_fenxiao']) && ($value['is_fenxiao'] == 1 || $value['is_fenxiao'] == 2)) {
            $status_text .= $order_status_text['fenxiao'];
        }
        if (isset($value['is_replenish']) && $value['is_replenish'] == 1) {
            $status_text .= $order_status_text['replenish'];
        }
        return $status_text;
    }

    //发票 问提 复制 缺货 换货 拆单 挂起 合单 手工单 锁定 piao wen fu que huan cai gua he shou shuo
    function get_sell_record_tag_img($row, $sysuser) {
        $tag_arr = array();
        if ($row['invoice_status'] > 0) {
            $tag_arr[] = array('piao', '有发票');
        }
        if ($row['is_problem'] > 0) {
            //获取具体问题类型
            $problem_type = load_model("oms/SellRecordTagModel")->get_tag_by_sell_record(array($row['sell_record_code']), 'problem', 'tag_desc');
            foreach ($problem_type['data'] as $vlaue) {
                $tag[] = $vlaue['tag_desc'];
            }
            $tag_desc = implode('/', $tag);
            $tag_arr[] = array('wen', $tag_desc);
        }
        if ($row['is_copy'] > 0) {
            $tag_arr[] = array('fu', '复制单');
        }
        if ($row['shipping_status'] == 0 && $row['order_status'] <> 3 && $row['must_occupy_inv'] == 1 && $row['lock_inv_status'] <> 1 && $row['lock_inv_status'] <> 0) {
            $tag_arr[] = array('que', '缺货单');
        }
        if ($row['is_change_record'] > 0) {
            $tag_arr[] = array('huan', '换货单');
        }
        if ($row['is_split_new'] > 0) {
            $tag_arr[] = array('cai', '拆单');
        }
        if ($row['is_rush'] > 0) {
            $tag_arr[] = array('ji', '急');
        }
        if ($row['is_pending'] > 0) {
            $tag_arr[] = array('gua', '挂起');
        }
        if ($row['is_combine_new'] > 0) {
            $tag_arr[] = array('he', '合单');
        }
        if ($row['is_handwork'] > 0) {
            $tag_arr[] = array('shou', '手工单');
        }
        if ($row['sale_mode'] == 'presale') {
            $tag_arr[] = array('yue', '预售单');
        }
        if ($row['is_fenxiao'] == 1) {
            $tag_arr[] = array('fen', '淘宝分销订单');
        }
        if ($row['is_fenxiao'] == 2) {
            $tag_arr[] = array('fen', '分销订单');
        }
        if ($row['pay_type'] == 'cod') {
            $tag_arr[] = array('cod', '货到付款');
        }
        if ($row['is_lock'] > 0 && $sysuser['user_code'] != $row['is_lock_person']) {
            $tag_arr[] = array('shuo', '锁定');
        }
        if ($row['order_status'] == 3) {
            $tag_arr[] = array('fei', '作废单');
        }
        if ($row['is_replenish'] == 1) {
            $tag_arr[] = array('replenish', '补单');
        }
//        $sell_return_code = $this->get_return_code_by_sell_record_code($row['sell_record_code']);
//        if (!empty($sell_return_code)) {
//            $tag_arr[] = array('tui', '存在退款/货');
//        }
        $html_arr = array();
        //var_dump($tag_arr);die;
        foreach ($tag_arr as $_tag) {
            $html_arr[] = "<img src='assets/img/state_icon/{$_tag[0]}_icon.png' title='{$_tag[1]}'/>";
        }
        return join('', $html_arr);
    }

    function a_key_confirm_create_task() {
        $sys_user = load_model("oms/SellRecordOptModel")->sys_user();
        $is_lock_person = $sys_user['user_code'];
        $obj_task = load_model('common/TaskModel');

        $task_data = array();
        $task_data['code'] = 'oms_a_key_confirm';
        $task_data['start_time'] = time();
        $ret = $obj_task->save_task($task_data);
        if ($ret === false) {
            return $ret;
        }
        $task_id = (int) $ret['data'];
        if ($task_id == 0) {
            return $this->format_ret(-1, '', '生成主任务失败');
        }

        $page_num = 1;
        while (1) {
            $ret = $this->a_key_confirm_create_taskeach($task_id, $is_lock_person, $page_num);
            if ($ret['status'] < 0) {
                $msg = '分发任务失败：' . $ret['message'];
                return $this->format_ret(-1, '', $msg);
            }
            if ($ret['status'] == 100) {
                break;
            }
            $page_num++;
        }

        return $this->format_ret(1, $task_id);
    }

    function a_key_confirm_create_taskeach($task_id, $is_lock_person, $page_num = 1, $page_size = 2) {
        $page_start = ($page_num - 1) * $page_size;

        $wh = '';
        $wh .= load_model('base/StoreModel')->get_sql_purview_store();
        $wh .= load_model('base/ShopModel')->get_sql_purview_shop();
        $wh .= " order by pay_time,sell_record_id limit {$page_start},{$page_size}";

        $sql = "select sell_record_id from oms_sell_record where order_status=0 and pay_status=2 and (is_lock=0 or (is_lock=1 and is_lock_person='{$is_lock_person}')) and is_pending=0 and (must_occupy_inv=1 and lock_inv_status=1) and is_problem=0 " . $wh;
        //echo $sql."<br/>";
        $sell_record_id_arr = ctx()->db->get_all_col($sql, array(), $page_size);

        //echo '<hr/>$sell_record_id_arr<xmp>'.var_export($sell_record_id_arr,true).'</xmp>';die;

        if (empty($sell_record_id_arr)) {
            return $this->format_ret(100, '', '分发任务完成');
        }
        $request = array();
        $request['app_fmt'] = 'json';
        $request['app_act'] = 'oms/sell_record/start_confirm';
        $request['id'] = join(',', $sell_record_id_arr);

        $obj_task = load_model('common/TaskModel');
        $ret = $obj_task->save_task_process(array('task_id' => $task_id), $request);
        $response = $obj_task->save_log($task_id, "当前确认ID=" . join(',', $sell_record_id_arr));

        return $ret;
    }

    /**
     * Get oms_deliver_record list, according to waves_record_id.
     * @param $ids
     * @return array|bool
     */
    public function get_deliver_record_ids($ids) {
        $str = implode(',', $ids);
        if (empty($str)) {
            return array('status' => '-1', 'data' => '', 'message' => '传入参数不正确');
        }

        $recordList = $this->db->get_all("select b.deliver_record_id
        from oms_sell_record a
        inner join oms_deliver_record b on b.waves_record_id = a.waves_record_id and b.sell_record_code = a.sell_record_code
        where a.sell_record_id in ($str)");
        if (empty($recordList)) {
            return array('status' => '-1', 'data' => '', 'message' => '发货单不存在');
        }

        $idList = array();
        foreach ($recordList as $row) {
            $idList[] = $row['deliver_record_id'];
        }
        return array('status' => '1', 'data' => $idList, 'message' => '验收成功');
    }

    public function shipped_import($sellRecordCode, $expressCode, $expressNo) {
        if (empty($expressCode) && empty($expressNo)) {
            return array('status' => '-1', 'message' => '快递方式和快递单号为空');
        }

        //var_dump($sellRecordCode, $expressCode, $expressNo);

        $arr = array();

        if (!empty($expressCode)) {
            $r = $this->db->get_row("select * from base_express where express_code = :express_code and status = 1", array('express_code' => $expressCode));
            if (empty($r)) {
                return array('status' => '-1', 'message' => '快递方式不存在或者未启用: ' . $expressCode);
            }

            $arr['express_code'] = $expressCode;
        }

        if (!empty($expressNo))
            $arr['express_no'] = $expressNo;
        $type = 'import';
        return load_model('oms/DeliverRecordModel')->edit_express($sellRecordCode, $arr, $type);
    }

    function import_trade_action($csv_path) {
        require_model('util/CsvImport');
        $import_obj = new CsvImport();
//    $ret = $import_obj->get_upload();
//            if ($ret['status'] < 0) {
//                return $ret;
//            }
//
//            $file_name = $ret['data'];
        //$file_name = 'import_sell_record_551d02b9ebd97.csv';

        list($path, $file_name) = explode("uploads/", $csv_path);
        $import_obj->is_iconv = 0;
        $ret = $import_obj->get_csv_data($file_name);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $data = $ret['data']['data'];
        if (!empty($data[0]['分销商代码*']) || !empty($data[0]['结算单价（分销应收）*'])) {
            return '导入分销订单请勾选分销导入';
        }
        $sql = "select shop_code,sale_channel_code,send_store_code from base_shop";
        $db_channel = ctx()->db->get_all($sql);
        $sale_channel_arr = array();
        $send_store_arr = array();
        foreach ($db_channel as $sub_channel) {
            $sale_channel_arr[$sub_channel['shop_code']] = $sub_channel['sale_channel_code'];
            $send_store_arr[$sub_channel['shop_code']] = $sub_channel['send_store_code'];
        }
        $fld_map = array(
            '下单日期*' => 'order_first_insert_time',
            '店铺代码*' => 'shop_code',
            '交易号*' => 'tid',
            '会员昵称*' => 'buyer_nick',
            '收货人*' => 'receiver_name',
            '手机号*' => 'receiver_mobile',
            '固定电话' => 'receiver_phone',
            '收货地址*' => 'receiver_address',
            '邮编' => 'receiver_zip_code',
            '是否货到付款' => 'is_cod',
            '付款日期' => 'pay_time',
            '仓库代码' => 'store_code',
            '配送方式代码' => 'express_code',
            '运费' => 'express_money',
            '买家留言' => 'buyer_remark',
            '商家留言' => 'seller_remark',
            '商品条形码*' => 'goods_barcode',
            '单价' => 'price',
            '数量*' => 'num',
        );

        $record_fld = 'order_first_insert_time,shop_code,source,tid,buyer_nick,receiver_name,receiver_mobile,receiver_phone,receiver_address,receiver_zip_code,pay_time,store_code,express_code,express_money,buyer_remark,seller_remark,pay_type,pay_code,receiver_province,receiver_city,receiver_district,receiver_street,receiver_addr,status,source,receiver_country';
        $record_fld .= ',receiver_email,express_no,sku_num,goods_num,goods_weigh,seller_flag,delivery_money,alipay_no,invoice_type,invoice_title,invoice_content,invoice_money';
        $record_mx_fld = 'tid,goods_barcode,price,num,avg_money';
        $record_mx_fld .= ',oid,sku_id';

        $util_obj = load_model('util/ViewUtilModel');

        $err_arr = array();
        $success_arr = array();
        $err_arr2 = array();
        $err_arr3 = array();
        $err_arr4 = array();

        foreach ($data as $data_row) {
            foreach ($data_row as $key => $v) {
                $v = preg_replace('/\xC2\xA0/is', "", $v);
                $data_row[$key] = trim($v);
            }
            $_row = array();
            foreach ($fld_map as $k => $v) {
                $_row[$v] = $data_row[$k];
            }

            if (empty($_row['tid'])) {
                //return '交易号不能为空！<br>';
                continue;
            }
            $_row['tid'] = trim($_row['tid']);
            $_row['avg_money'] = $_row['price'] * $_row['num'];
            $_row['avg_money'] = sprintf('%.2f', $_row['avg_money']);
            $_row['pay_type'] = $_row['is_cod'] == '是' ? '1' : '0';
            $_row['pay_code'] = $_row['pay_type'] == '1' ? 'cod' : 'bank';
            if (!empty($_row['order_first_insert_time'])) {
                $_row['order_first_insert_time'] = date('Y-m-d H:i:s', strtotime($_row['order_first_insert_time']));
            }
            if (!empty($_row['pay_time'])) {
                $_row['pay_time'] = date('Y-m-d H:i:s', strtotime($_row['pay_time']));
                if (!$_row['pay_time']) {
                    return '请正确填写付款时间！<br>';
                }
            }

            $_row['receiver_address'] = str_replace('  ', '', $_row['receiver_address']);
            $_addr = explode(' ', $_row['receiver_address']);
            //验证直辖市

            $addr_str = $this->check_addr($_addr);
            $_row['receiver_address'] = $addr_str;

            if (trim($_addr[0]) != '中国') {
                $_row['receiver_address'] = '中国 ' . $_row['receiver_address'];
                $_addr = explode(' ', $_row['receiver_address']);
            }

            $_row['receiver_province'] = $_addr[1];
            $_row['receiver_city'] = $_addr[2];
            $_row['receiver_district'] = $_addr[3];
            $_row['receiver_country'] = '中国';

            $_addr_str = "{$_addr[0]} {$_addr[1]} {$_addr[2]} {$_addr[3]}";
            $_addr_num = count($_addr);
            $receiver_addr = '';
            if ($_addr_num == 5) {
                $receiver_addr = $_addr[4];
            } else if ($_addr_num == 6) {
                $sql = "select id from base_area where name=:name AND type=5";
                $area_id = $this->db->get_value($sql, array(':name' => $_addr[4]));
                if (!empty($area_id)) {
                    $_row['receiver_street'] = $_addr[4];
                    $receiver_addr = $_addr[5];
                } else {
                    $receiver_addr = $_addr[4] . $_addr[5];
                }
            } else if ($_addr_num > 6) {
                $_row['receiver_street'] = $_addr[4];
                $i = 5;
                while ($i < $_addr_num) {
                    $receiver_addr .= $_addr[$i];
                    $i++;
                }
            }

            $_row['receiver_addr'] = !empty($receiver_addr) ? $receiver_addr : str_replace($_addr_str, '', $_row['receiver_address']);


            $_row['source'] = isset($sale_channel_arr[$_row['shop_code']]) ? $sale_channel_arr[$_row['shop_code']] : null;
            if (empty($_row['source'])) {
                // $err_arr[] = $_row['tid'] . ' 找不到订单来源';
                $err_arr2[$_row['tid']] = ' 找不到订单来源';
            }
            //判断店铺是否分销店铺
            $shop = load_model('base/ShopModel')->get_by_code($_row['shop_code']);
            $shop_data = $shop['data'];
            if (empty($shop_data)) {
                $err_arr2[$_row['tid']] = '店铺代码不存在';
            }
            if ($shop_data['is_active'] == 0) {
                $err_arr2[$_row['tid']] = '店铺已停用';
            }
            if (!empty($shop_data['custom_code'])) {
                $err_arr2[$_row['tid']] = '该店铺是分销店铺，不能导入普通订单';
            }
            $_row['source'] = $shop_data['sale_channel_code'];
            if (empty($_row['store_code'])) {
                if (isset($send_store_arr[$_row['shop_code']])) {
                    $_row['store_code'] = $send_store_arr[$_row['shop_code']];
                } else {
                    $err_arr2[$_row['tid']] = '找不到指定发货仓库';
                }
            } else {
                if ($this->check_store($_row['store_code']) === false) {
                    $err_arr2[$_row['tid']] = '找不到指定仓库';
                }
            }
            if (!isset($_addr[0]) || !isset($_addr[1]) || empty($_addr[0]) || empty($_addr[1])) {
                //   $err_arr[] = $_row['tid'] . ' 收货地址省、市不能为空';
                $err_arr3[$_row['tid']] = '  收货地址省、市不能为空';
            }

            $trade_data[$_row['tid']]['record'][] = $util_obj->copy_arr_by_fld($_row, $record_fld, 0, 1);
            $trade_data[$_row['tid']]['mx'][] = $util_obj->copy_arr_by_fld($_row, $record_mx_fld, 0, 1);
            //break;
        }
        //echo '<hr/>$trade_data<xmp>'.var_export($trade_data,true).'</xmp>';
        $api_data = array();
        foreach ($trade_data as $tid => $sub_tid) {
            $pre_v = '';
            $record_err_tag = 0;
            foreach ($sub_tid['record'] as $record_row) {
                $cur_v = join(',', $record_row);
                if ($pre_v != '' && $pre_v != $cur_v) {
                    $record_err_tag = 1;
                    break;
                }
                $pre_v = $cur_v;
            }
            if ($record_err_tag == 1) {
                //  $err_arr[] = $tid . '订单信息不匹配';
                $err_arr4[$tid] = ' 订单信息不匹配';
            }
            $api_data[$tid] = $sub_tid['record'][0];
            $_order_money = 0;
            $dit = array();
            foreach ($sub_tid['mx'] as $kk => $_row) {
                $dit[$kk]['tid'] = $_row['tid'];
                $dit[$kk]['goods_barcode'] = $_row['goods_barcode'];
                $_order_money = bcadd($_order_money, $_row['avg_money'], 4);
                $_row['tid'] = $tid;
                $_barcode = "{$_row['goods_barcode']}";
                if (isset($trade_data[$tid]['data'][$_barcode])) {
                    $api_data[$tid]['mx'][$_barcode]['num'] += $_row['num'];
                    $api_data[$tid]['mx'][$_barcode]['avg_money'] += $_row['avg_money'];
                } else {
                    $_row['sku_properties'] = '';
                    $api_data[$tid]['mx'][$_barcode] = $_row;
                }
            }
            $unique_arr = array_unique($dit, SORT_REGULAR);
            $repeat_arr = array_diff_assoc($dit, $unique_arr);
            if (!empty($repeat_arr)) {
                $err_arr4[$tid] = ' 系统检测到同一笔交易中存在多条相同商品条形码记录，请合并数量后再导入！';
            }
            $_order_money += $api_data[$tid]['express_money'];
            $api_data[$tid]['order_money'] = $_order_money;
        }
        //print_r($_row['tid']);
        //echo '<hr/>$api_data<xmp>'.var_export($api_data,true).'</xmp>';die;
        $obj = load_model('oms/TranslateOrderModel');
        $obj->import_flag = 1;
        foreach ($api_data as $tid => $sub_api_data) {
            if (isset($err_arr2[$tid]) && !empty($err_arr2[$tid])) {
                $err_arr[] = $tid . $err_arr2[$tid];
                continue;
            } elseif (isset($err_arr3[$tid]) && !empty($err_arr3[$tid])) {
                $err_arr[] = $tid . $err_arr3[$tid];
                continue;
            } elseif (isset($err_arr4[$tid]) && !empty($err_arr4[$tid])) {
                $err_arr[] = $tid . $err_arr4[$tid];
                continue;
            }
            $ret = $obj->translate_order_by_data($sub_api_data, 'import');
            if ($ret['status'] < 0) {
                $err_arr[] = $tid . $ret['message'];
            } else {
                $sql = "update oms_sell_record set is_handwork='1' where deal_code='" . $tid . "'";
                ctx()->db->query($sql);
                $success_arr[] = $tid . '导入成功';
            }
        }
        $ret_msg = '';
        if (!empty($err_arr)) {
            $ret_msg .= "<div style='color:red'>导入失败的订单：<br/>" . join('<br/>', $err_arr) . "</div>";
        }
        if (!empty($success_arr)) {
            $ret_msg .= "<hr/><div>导入成功的订单：<br/>" . join('<br/>', $success_arr) . "</div>";
        }
        return $ret_msg;
    }

    function import_xcf_trade_action($csv_path) {
        require_model('util/CsvImport');
        $import_obj = new CsvImport();

        list($path, $file_name) = explode("uploads/", $csv_path);
        $import_obj->is_iconv = 0;
        $ret = $import_obj->get_csv_data($file_name, $head_line = 1);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $data = $ret['data']['data'];
        foreach ($data as $key => $d) {
            $data[$key]["交易号"] = $data[$key]["订单号"];
            $data[$key]["购买人名称"] = $data[$key]["收货人姓名"];
            foreach ($d as $k => $v) {
                if (preg_match('/下单时间/i', $k)) {
                    $data[$key]["下单时间"] = $v;
                }
                if ($k == "商家") {
                    $shop_code = $this->db->get_value("select shop_code from base_shop where shop_name='{$v}' and sale_channel_code = 'xiachufang'");
                    $data[$key]["商店代码"] = $shop_code;
                }
            }
        }
        $sql = "select shop_code,sale_channel_code,send_store_code from base_shop";
        $db_channel = ctx()->db->get_all($sql);
        $sale_channel_arr = array();
        $send_store_arr = array();
        foreach ($db_channel as $sub_channel) {
            $sale_channel_arr[$sub_channel['shop_code']] = $sub_channel['sale_channel_code'];
            $send_store_arr[$sub_channel['shop_code']] = $sub_channel['send_store_code'];
        }
        $fld_map = array(
            '下单时间' => 'order_first_insert_time',
            '订单号' => 'sell_record_code',
            '商店代码' => 'shop_code',
            '交易号' => 'tid',
            '收货人姓名' => 'receiver_name',
            '详细地址' => 'receiver_address',
            '手机' => 'receiver_mobile',
            '商家' => 'shop_name',
            '运费' => 'express_money',
            '备注' => 'order_remark',
            '购买人名称' => 'buyer_nick',
            '省/直辖市' => 'receiver_province',
            '市' => 'receiver_city',
            '区' => 'receiver_district',
            '商品编号' => 'goods_barcode',
            '商品数量' => 'num',
            '商品价格' => 'price',
            '总价' => 'avg_money',
        );

        $record_fld = 'order_first_insert_time,shop_code,tid,buyer_nick,receiver_name,receiver_mobile,receiver_phone,receiver_address,receiver_zip_code,pay_time,store_code,express_code,express_money,buyer_remark,seller_remark,pay_type,pay_code,receiver_province,receiver_city,receiver_district,receiver_street,receiver_addr,status,source,receiver_country';
        $record_fld .= ',receiver_email,express_no,sku_num,goods_num,goods_weigh,seller_flag,delivery_money,alipay_no,invoice_type,invoice_title,invoice_content,invoice_money';
        $record_mx_fld = 'tid,goods_barcode,price,num,avg_money';
        $record_mx_fld .= ',oid,sku_id';

        $util_obj = load_model('util/ViewUtilModel');

        $err_arr = array();
        $success_arr = array();
        $err_arr2 = array();
        $err_arr3 = array();
        $err_arr4 = array();
        foreach ($data as $data_row) {
            $_row = array();
            foreach ($fld_map as $k => $v) {
                $_row[$v] = $data_row[$k];
            }
            if (empty($_row['tid'])) {
                return '交易号不能为空！<br>';
                //continue;
            }
            $_row['tid'] = trim($_row['tid']);
            $_row['avg_money'] = $_row['price'] * $_row['num'];
            $_row['pay_type'] = $_row['is_cod'] == '是' ? '1' : '0';
            $_row['pay_code'] = $_row['pay_type'] == '1' ? 'cod' : 'bank';
            if (!empty($_row['order_first_insert_time'])) {
                $_row['order_first_insert_time'] = date('Y-m-d H:i:s', strtotime($_row['order_first_insert_time']));
            }
            if (empty($_row['pay_time'])) {
                $_row['pay_time'] = $_row['order_first_insert_time'];
            }

            $_row['receiver_address'] = str_replace('  ', '', $_row['receiver_address']);

            $_addr = explode(' ', $_row['receiver_address']);
            //验证直辖市
            $addr_str = $this->check_addr($_addr);
            $_row['receiver_address'] = $addr_str;

            if (trim($_addr[0]) != '中国') {
                $_row['receiver_address'] = '中国 ' . $_row['receiver_address'];
                $_addr = explode(' ', $_row['receiver_address']);
            }

            $_row['receiver_province'] = $_addr[1];
            $_row['receiver_city'] = $_addr[2];
            $_row['receiver_district'] = $_addr[3];
            $_row['receiver_country'] = '中国';

            $_addr_str = "{$_addr[0]} {$_addr[1]} {$_addr[2]} {$_addr[3]}";
            $_addr_num = count($_addr);
            $receiver_addr = '';
            if ($_addr_num == 5) {
                $receiver_addr = $_addr[4];
            } else if ($_addr_num == 6) {
                $sql = "select id from base_area where name=:name AND type=5";
                $area_id = $this->db->get_value($sql, array(':name' => $_addr[4]));
                if (!empty($area_id)) {
                    $_row['receiver_street'] = $_addr[4];
                    $receiver_addr = $_addr[5];
                } else {
                    $receiver_addr = $_addr[4] . $_addr[5];
                }
            } else if ($_addr_num > 6) {
                $_row['receiver_street'] = $_addr[4];
                $i = 5;
                while ($i < $_addr_num) {
                    $receiver_addr .= $_addr[$i];
                    $i++;
                }
            }

            $_row['receiver_addr'] = !empty($receiver_addr) ? $receiver_addr : str_replace($_addr_str, '', $_row['receiver_address']);
            $_row['source'] = isset($sale_channel_arr[$_row['shop_code']]) ? $sale_channel_arr[$_row['shop_code']] : null;
            if (empty($_row['source'])) {
                // $err_arr[] = $_row['tid'] . ' 找不到订单来源';
                $err_arr2[$_row['tid']] = ' 找不到订单来源';
            }
            if (empty($_row['store_code'])) {
                if (isset($send_store_arr[$_row['shop_code']])) {
                    $_row['store_code'] = $send_store_arr[$_row['shop_code']];
                } else {
                    $err_arr2[$_row['tid']] = '找不到指定发货仓库';
                }
            } else {
                if ($this->check_store($_row['store_code']) === false) {
                    $err_arr2[$_row['tid']] = '找不到指定仓库';
                }
            }
            if (!isset($_addr[0]) || !isset($_addr[1]) || empty($_addr[0]) || empty($_addr[1])) {
                //   $err_arr[] = $_row['tid'] . ' 收货地址省、市不能为空';
                $err_arr3[$_row['tid']] = '  收货地址省、市不能为空';
            }
            $trade_data[$_row['tid']]['record'][] = $util_obj->copy_arr_by_fld($_row, $record_fld, 0, 1);
            $trade_data[$_row['tid']]['mx'][] = $util_obj->copy_arr_by_fld($_row, $record_mx_fld, 0, 1);
            //break;
        }

        //echo '<hr/>$trade_data<xmp>'.var_export($trade_data,true).'</xmp>';
        $api_data = array();
        foreach ($trade_data as $tid => $sub_tid) {
            $pre_v = '';
            $record_err_tag = 0;
            foreach ($sub_tid['record'] as $record_row) {
                $cur_v = join(',', $record_row);
                if ($pre_v != '' && $pre_v != $cur_v) {
                    $record_err_tag = 1;
                    break;
                }
                $pre_v = $cur_v;
            }
            if ($record_err_tag == 1) {
                //  $err_arr[] = $tid . '订单信息不匹配';
                $err_arr4[$tid] = ' 订单信息不匹配';
            }
            $api_data[$tid] = $sub_tid['record'][0];
            $_order_money = 0;
            foreach ($sub_tid['mx'] as $kk => $_row) {
                $_order_money += $_row['avg_money'];
                $_row['tid'] = $tid;
                $_barcode = "{$_row['goods_barcode']}";
                if (isset($trade_data[$tid]['data'][$_barcode])) {
                    $api_data[$tid]['mx'][$_barcode]['num'] += $_row['num'];
                    $api_data[$tid]['mx'][$_barcode]['avg_money'] += $_row['avg_money'];
                } else {
                    $_row['sku_properties'] = '';
                    $api_data[$tid]['mx'][$_barcode] = $_row;
                }
            }
            $_order_money += $api_data[$tid]['express_money'];
            $api_data[$tid]['order_money'] = $_order_money;
        }
        //echo '<hr/>$api_data<xmp>'.var_export($api_data,true).'</xmp>';die;
        $obj = load_model('oms/TranslateOrderModel');
        $obj->import_flag = 1;
        foreach ($api_data as $tid => $sub_api_data) {
            if (isset($err_arr2[$tid]) && !empty($err_arr2[$tid])) {
                $err_arr[] = $tid . $err_arr2[$tid];
                continue;
            } elseif (isset($err_arr3[$tid]) && !empty($err_arr3[$tid])) {
                $err_arr[] = $tid . $err_arr3[$tid];
                continue;
            } elseif (isset($err_arr4[$tid]) && !empty($err_arr4[$tid])) {
                $err_arr[] = $tid . $err_arr4[$tid];
                continue;
            }
            $ret = $obj->translate_order_by_data($sub_api_data);
            if ($ret['status'] < 0) {
                $err_arr[] = $tid . $ret['message'];
            } else {
                $success_arr[] = $tid . '导入成功';
            }
        }
        $ret_msg = '';
        if (!empty($err_arr)) {
            $ret_msg .= "<div style='color:red'>导入失败的订单：<br/>" . join('<br/>', $err_arr) . "</div>";
        }
        if (!empty($success_arr)) {
            $ret_msg .= "<hr/><div>导入成功的订单：<br/>" . join('<br/>', $success_arr) . "</div>";
        }
        return $ret_msg;
    }

    function check_store(&$store_code) {
        static $store_arr = NULL;
        if (!isset($store_arr[$store_code])) {
            $ret = load_model('base/StoreModel')->get_by_code($store_code);
            if (!empty($ret['data'])) {
                $new_store_code = $ret['data']['store_code'];
                $store_arr[$store_code] = $ret['data'];
                $store_arr[$new_store_code] = $ret['data'];
                $store_code = $new_store_code;
            }
        }
        if (!empty($store_arr[$store_code])) {
            return true;
        }
        return false;
    }

    //get_by_code($store_code)
    //今日已付款订单数
    function pay_num($date) {

        $sql = "select count(*) from api_order where pay_time >= '" . $date . "' and pay_type = '0' and status = 1 ";
        $pay_num1 = ctx()->db->getOne($sql);

        $sql = "select count(*)  from api_order where order_first_insert_time >= '" . $date . "' and pay_type = '1' and status = 1";
        $pay_num2 = ctx()->db->getOne($sql);

        $sql = " SELECT count(1) from api_taobao_fx_trade where is_invo=1 AND is_change<1 AND pay_time>='{$date}'";
        $pay_num3 = ctx()->db->getOne($sql);


        return $pay_num1 + $pay_num2 + $pay_num3;
    }

    //今日已付款订单数(明细)
    function category_num($date) {
        $sql = "select count(*) as num  , shop_code  from  api_order  where pay_time > '" . $date . "' and pay_type = '0'group by shop_code ";
        $category_num1 = ctx()->db->getAll($sql);

        $sql = "select count(*)  from api_order where order_first_insert_time >= '" . $date . "' and  pay_type = '1' and status = 1  group by shop_code ";
        $category_num2 = ctx()->db->getOne($sql);
        $category_num_arr = array();

        foreach ($category_num1 as $key => $value) {
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
            $category_num_arr[$value['shop_name']] = $value;
        }

        foreach ($category_num2 as $key => $value) {
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
            if (isset($category_num_arr[$value['shop_name']])) {
                $category_num_arr[$value['shop_name']]['num'] += $value['num'];
            } else {
                $category_num_arr[$value['shop_name']] = $value;
            }
        }
        foreach ($category_num_arr as $key => $value) {

            $value['short_name'] = substr($value['shop_name'], 0, 6);
        }

        return $category_num_arr;
    }

    //今日已转单数
    function transform_num($date) {
        $sql = "select count(*) from api_order where pay_time >= '" . $date . "' and pay_type ='0' and is_change=1";
        $transform_num1 = ctx()->db->getOne($sql);

        $sql = "select count(*)  from api_order where order_first_insert_time >= '" . $date . "' and pay_type = '1' and status = 1 and is_change=1";
        $transform_num2 = ctx()->db->getOne($sql);
        $sql = " SELECT count(1) from api_taobao_fx_trade where is_invo=1 AND is_change=1 AND pay_time>='{$date}'";
        $transform_num3 = ctx()->db->getOne($sql);
        return $transform_num1 + $transform_num2 + $transform_num3;
    }

    //待确认订单数
    function unconfirm_num() {
        $sql = "select count(*) as sum from oms_sell_record where order_status = 0 and pay_status=2 and  shipping_status=0 and is_fenxiao=0";
        $unconfirm_num = ctx()->db->getOne($sql);
        return $unconfirm_num;
    }

    //待确认订单数(挂起)
    function pending_num() {
        $sql = "select count(*) as sum from oms_sell_record where order_status <>3 and is_fenxiao = 0 and  is_pending = 1 ";
        $pending_num = ctx()->db->getOne($sql);
        return $pending_num;
    }

    //待确认订单数(问题)
    function problem_num() {
        $sql = "select count(*) as sum from oms_sell_record where order_status !=3 and is_problem=1 and is_fenxiao=0";
        $problem_num = ctx()->db->getOne($sql);
        return $problem_num;
    }

    //待确认订单数(缺货)
    function stockout_num() {
        $sql = "select count(*) as sum from oms_sell_record where order_status = 0 and must_occupy_inv=1  and lock_inv_status in (2,3) and is_fenxiao=0";
        $stockout_num = ctx()->db->getOne($sql);
        return $stockout_num;
    }

    //待通知配货订单数
    function unnotice_num() {
        $sql = "select count(*) as sum from oms_sell_record where order_status = 1 and shipping_status = 0 and is_fenxiao=0";
        $unnotice_num = ctx()->db->getOne($sql);
        return $unnotice_num;
    }

    //待拣货订单数
    function unpick_num() {
        $sql = "select count(*) as sum from oms_sell_record where order_status = 1 and shipping_status = 1 and waves_record_id=0 and is_fenxiao=0";
        $unpick_num = ctx()->db->getOne($sql);
        return $unpick_num;
    }

    function normal_num() {
        $sql = "select count(*) as  sum from oms_sell_record where  (pay_status = 2 or pay_type ='cod') and  order_status = 0 and  is_problem = 0 and is_pending=0 and lock_inv_status=1  and shipping_status = 0  and is_fenxiao=0";
        $back_error_num = ctx()->db->getOne($sql);
        return $back_error_num;
    }

    //待扫描订单数
    function unscan_num() {
        $sql = "select count(*) as sum from oms_deliver_record d INNER JOIN oms_waves_record w ON w.waves_record_id = d.waves_record_id
		where w.is_accept=1 and d.is_deliver = 0 and d.is_cancel = 0 ";

        $unscan_num = ctx()->db->getOne($sql);
        return $unscan_num;
    }

    //今日已发货订单数
    function deliver_num($date) {

        $sql = "select count(*) as sum  from oms_sell_record where shipping_status = 4 AND delivery_date = '" . $date . "' and is_fenxiao=0";
        $deliver_num = ctx()->db->getOne($sql);
        return $deliver_num;
    }

    //今日网单回写订单数
    function back_num($date) {
        $sql = "select count(*) as sum  from api_order_send where  status in ('1','2') and  upload_time > '" . $date . "' and send_time > '" . $date . "' ";
        $back_num = ctx()->db->getOne($sql);
        return $back_num;
    }

    //回写失败订单数
    function back_error_num() {
        $sql = "select count(*) as  sum from api_order_send where    status <0 ";
        $back_error_num = ctx()->db->getOne($sql);
        return $back_error_num;
    }

    function conv_fenxiao_json($fenxiao_data, $shop_code) {
        $_address = $fenxiao_data['receiver']['state'] . ' ' . $fenxiao_data['receiver']['city'] . ' ' . $fenxiao_data['receiver']['district'] . ' ' . $fenxiao_data['receiver']['address'];

        $fenxiao_data['receiver']['city'] = trim($fenxiao_data['receiver']['city']);
        $info = array('tid' => $fenxiao_data['fenxiao_id'],
            'source' => 'taobao',
            'shop_code' => $shop_code,
            'pay_type' => 0,
            'pay_time' => $fenxiao_data['pay_time'],
            'seller_nick' => $fenxiao_data['supplier_username'],
            'buyer_nick' => $fenxiao_data['receiver']['name'],
            'receiver_name' => $fenxiao_data['receiver']['name'],
            'receiver_country' => '中国',
            'receiver_province' => $fenxiao_data['receiver']['state'],
            'receiver_city' => empty($fenxiao_data['receiver']['city']) ? $fenxiao_data['receiver']['district'] : $fenxiao_data['receiver']['city'],
            'receiver_district' => empty($fenxiao_data['receiver']['city']) ? '' : $fenxiao_data['receiver']['district'],
            'receiver_address' => $_address,
            'receiver_addr' => $fenxiao_data['receiver']['address'],
            'receiver_mobile' => $fenxiao_data['receiver']['mobile_phone'],
            'receiver_zip_code' => '',
            'receiver_phone' => '',
            'receiver_email' => '',
            'express_no' => '',
            'express_code' => '',
            'invoice_type' => '',
            'invoice_title' => '',
            'invoice_content' => '',
            'invoice_money' => '',
            'invoice_status' => 0,
            'buyer_remark' => $fenxiao_data['memo'],
            'seller_remark' => $fenxiao_data['supplier_memo'],
            'seller_flag' => 0,
            'order_money' => $fenxiao_data['buyer_payment'],
            'express_money' => $fenxiao_data['post_fee'],
            'alipay_no' => '',
            'order_first_insert_time' => $fenxiao_data['created']);

        foreach ($fenxiao_data['sub_purchase_orders']['sub_purchase_order'] as $k => $sub_data) {
            $_row = array('source' => 'taobao', 'tid' => $sub_data['fenxiao_id'],
                'oid' => '', 'num' => $sub_data['num'],
                'sku_id' => $sub_data['sku_id'], 'goods_barcode' => $sub_data['sku_outer_id'],
                'sku_properties' => $sub_data['sku_properties'], 'price' => $sub_data['price'],
                'payment' => $sub_data['buyer_payment']);
            $info['mx'][] = $_row;
        }
        $total_je = $fenxiao_data['buyer_payment'] - $fenxiao_data['post_fee'];
        $info['mx'] = load_model('oms/SellRecordOptModel')->payment_ft($total_je, $info['mx']);
        return $info;
    }

    function import_fenxiao_trade_action() {
        require_model('util/CsvImport');
        $import_obj = new CsvImport();

        $ret = $import_obj->get_upload();
        if ($ret['status'] < 0) {
            return $ret['message'];
        }
        $file_name = $ret['data'];

        //$file_name = 'import_sell_record_551d02b9ebd97.csv';
        $ret = $import_obj->get_csv_data($file_name, 1);
        if ($ret['status'] < 0) {
            return $ret['message'];
        }
        $data = $ret['data']['data'];

        $sql = "select shop_code,sale_channel_code from base_shop";
        $db_channel = ctx()->db->get_all($sql);
        $sale_channel_arr = array();
        foreach ($db_channel as $sub_channel) {
            $sale_channel_arr[$sub_channel['shop_code']] = $sub_channel['sale_channel_code'];
        }

        $fld_map = array(
            '订单编号' => '订单编号',
            '采购单编号' => 'deal_code',
            '商家编码' => 'goods_barcode',
            '产品名称' => '产品名称',
            '商品属性' => '商品属性',
            '收件人' => 'receiver_name',
            '手机' => 'receiver_mobile',
            '电话' => 'receiver_phone',
            '地址' => 'receiver_address',
            '采购单状态' => '_trade_status',
            '产品采购单价' => 'goods_price',
            '有效采购数量' => 'num',
            '有效采购金额' => '有效采购金额',
            '改价金额' => '改价金额',
            '优惠类型' => '优惠类型',
            '优惠金额' => '优惠金额',
            '应付采购金额' => '应付采购金额',
            '应付邮费' => 'express_money',
            '应付金额合计' => 'payable_money',
            '分销商已支付金额' => '分销商已支付金额',
            '已退款的产品数' => '已退款的产品数',
            '已退款金额' => '已退款金额',
            '分销商销售收入' => '分销商销售收入',
            '供应商会员名' => '供应商会员名',
            '供应商公司名称' => '供应商公司名称',
            '分销商会员名' => '分销商会员名',
            '采购单留言' => 'seller_remark',
            '采购单创建时间' => 'record_time',
            '采购单付款时间' => 'pay_time',
            '采购单发货时间' => '采购单发货时间',
            '采购单退款时间' => '采购单退款时间',
            '采购单确认收货时间' => '采购单确认收货时间',
            '分销模式' => '分销模式',
            '支付方式' => '支付方式',
            '支付宝交易号' => '支付宝交易号',
            '分销商的支付宝账户' => '分销商的支付宝账户',
        );

        $deal_code_arr = array();
        foreach ($data as $data_row) {
            $_row = array();
            foreach ($fld_map as $k => $v) {
                $_vv = trim($data_row[$k]) == '-' ? '' : trim($data_row[$k]);
                $_row[$v] = $_vv;
            }
            if (empty($_row['deal_code'])) {
                continue;
            }
            $deal_code_arr[] = $_row['deal_code'];
        }

        $sql = "select shop_code,shop_user_nick from base_shop where sale_channel_code = 'taobao' and shop_user_nick<>''";
        $db_shop = ctx()->db->get_all($sql);
        $shop_map = load_model('util/ViewUtilModel')->get_map_arr($db_shop, 'shop_user_nick', 0, 'shop_code');

        $fenxiao_id_list = join(',', $deal_code_arr);
        $sql = "select fenxiao_id,supplier_username,status,jdp_response from sys_info.jdp_fx_trade where fenxiao_id in({$fenxiao_id_list})";
        $db_fx = ctx()->db->get_all($sql);
        $err_arr = array();
        $api_data = array();

        foreach ($db_fx as $sub_fx) {
            $_supplier_username = $sub_fx['supplier_username'];
            $_shop_code = @$shop_map[$_supplier_username];
            $_tid = $sub_fx['fenxiao_id'];
            if (empty($_shop_code)) {
                $err_arr[] = "{$_tid} {$_supplier_username} 找不到对应店铺";
                continue;
            }
            if ($sub_fx['status'] != 'WAIT_SELLER_SEND_GOODS') {
                $err_arr[] = "{$_tid} 不是待发货状态";
                continue;
            }
            $_json_data = json_decode($sub_fx['jdp_response'], true);
            $_json_data = $_json_data['fenxiao_orders_get_response']['purchase_orders']['purchase_order'][0];
            $api_data[$_tid] = $this->conv_fenxiao_json($_json_data, $_shop_code);
        }
        /*
          $diff_tid = array_diff($deal_code_arr,array_keys($api_data));
          echo '<hr/>$diff_tid<xmp>'.var_export($diff_tid,true).'</xmp>';
          echo '<hr/>$api_data<xmp>'.var_export($api_data,true).'</xmp>';
          echo '<hr/>$api_data_count<xmp>'.var_export(count($api_data),true).'</xmp>';
          echo '<hr/>$err_arr<xmp>'.var_export($err_arr,true).'</xmp>';
          die; */
        $obj = load_model('oms/TranslateOrderModel');
        $obj->import_flag = 1;

        foreach ($api_data as $tid => $sub_api_data) {
            $ret = $obj->translate_order_by_data($sub_api_data);
            if ($ret['status'] < 0) {
                $err_arr[] = $tid . $ret['message'];
            } else {
                $success_arr[] = $tid . '导入成功';
            }
        }
        $ret_msg = '';
        if (!empty($err_arr)) {
            $ret_msg .= "<div style='color:red'>导入失败的订单：<br/>" . join('<br/>', $err_arr) . "</div>";
        }
        if (!empty($success_arr)) {
            $ret_msg .= "<hr/><div>导入成功的订单：<br/>" . join('<br/>', $success_arr) . "</div>";
        }
        /*
          echo '<hr/>$err_arr<xmp>'.var_export($err_arr,true).'</xmp>';
          echo '<hr/>$success_arr<xmp>'.var_export($success_arr,true).'</xmp>';
          echo '<hr/>$ret_msg<xmp>'.var_export($ret_msg,true).'</xmp>';
         */
        return $ret_msg;
    }

    /**
     * 方法名        api_order_search_get
     * 功能描述      订单查询接口
     * @author      BaiSon PHP R&D
     * @date        2016-03-09
     * @param       array $param
     *              array(
     *                  可选: 'page', 'page_size','sell_record_code','store_code',
     *                        'order_status', 'shipping_status', 'start_time', 'end_time', 'shop_code'
     *                        'start_notice_time','end_notice_time','start_delivery_time','end_delivery_time',
     *                        'start_lastchanged','end_lastchanged','is_get_shelf','deal_code'
     *              )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":"10146"}
     */
    public function api_order_search_get($param) {
        require_lib('comm_util', true);
        //可选字段
        $key_option = array(
            's' => array(
                'page', 'page_size', 'sell_record_code', 'store_code', 'order_status', 'shipping_status', 'start_time', 'end_time', 'shop_code', 'start_notice_time', 'end_notice_time', 'start_delivery_time', 'end_delivery_time', 'start_lastchanged', 'end_lastchanged', 'is_get_shelf', 'deal_code', 'receiver_mobile'
            )
        );
        $arr_option = array();
        //提取可选字段中已赋值数据
        $ret_option = valid_assign_array($param, $key_option, $arr_option);
        //合并数据
        $arr_deal = $arr_option;

        //检查单页数据条数是否超限
        if (isset($arr_deal['page_size']) && $arr_deal['page_size'] > 100) {
            return $this->format_ret('-1', array('page_size' => $arr_deal['page_size']), API_RETURN_MESSAGE_PAGE_SIZE_TOO_LARGE);
        }

        //清空无用数据
        unset($arr_option);
        unset($param);

        if (!empty($arr_deal['store_code'])) {
            $store_code = json_decode($arr_deal['store_code'], TRUE);
            if (is_array($store_code)) {
                $arr_deal['store_code'] = "'" . implode("','", $store_code) . "'";
            } else {
                $arr_deal['store_code'] = "'" . $arr_deal['store_code'] . "'";
            }
        }
        $is_get_shelf = 0;
        if (isset($arr_deal['is_get_shelf']) && $arr_deal['is_get_shelf'] == 1) {
            $is_get_shelf = 1;
            unset($arr_deal['is_get_shelf']);
        }

        //开放字段
        $select = '
            `sell_record_code`,`order_status`,`shipping_status`,`pay_status`, `deal_code_list`,`sale_channel_code`, `store_code`, `shop_code`, `customer_code`, `buyer_name`, `receiver_name`,
            `receiver_country`, `receiver_province`, `receiver_city`, `receiver_district`, `receiver_street`, `receiver_address`, `receiver_addr`,
            `receiver_zip_code`, `receiver_mobile`, `receiver_phone`, `receiver_email`, `express_code`, `express_no`, `buyer_remark`,`seller_remark`,
            `order_remark`,`store_remark`,`is_change_record` AS `change_record`,`invoice_title`,`invoice_content`,`goods_num`,`goods_money`, `express_money`,`delivery_money`, `payable_money`, `paid_money`,`fx_payable_money`,`fx_express_money`,`record_time`, `pay_time`, `delivery_time`,`sign_time`,`is_notice_time`,`pay_type`, `pay_code`,`order_status`,`lastchanged`
            ';
        //查询SQL
        $sql_main = " FROM {$this->table} sr WHERE 1=1";
        //绑定数据
        $sql_values = array();
        if (isset($arr_deal['sell_record_code']) && !empty($arr_deal['sell_record_code'])) {
            $arr_deal = array('sell_record_code' => $arr_deal['sell_record_code']);
            $sql_main .= " AND sr.sell_record_code=:sell_record_code";
            $sql_values[":sell_record_code"] = $arr_deal['sell_record_code'];
        } else if (isset($arr_deal['deal_code']) && !empty($arr_deal['deal_code'])) {
            $sql_detail = 'SELECT sell_record_code FROM oms_sell_record_detail WHERE deal_code=:deal_code';
            $sell_code_arr = $this->db->get_all_col($sql_detail, array(':deal_code' => $arr_deal['deal_code']));
            if (empty($sell_code_arr)) {
                $sql_main .= " AND 1<>1";
            } else {
                $sell_code_str = $this->arr_to_in_sql_value($sell_code_arr, 'sell_record_code', $sql_values);
                $sql_main .= " AND sr.sell_record_code IN({$sell_code_str})";
            }
        } else {
            unset($arr_deal['sell_record_code']);
            unset($arr_deal['deal_code']);
            $this->create_sql_where($arr_deal, $sql_values, $sql_main);
            $this->create_default_time($arr_deal, $sql_main, $sql_values);
        }
        $ret = $this->get_page_from_sql($arr_deal, $sql_main, $sql_values, $select);

        if (count($ret['data']) <= 0) {
            return $this->format_ret(-10002, '', 'API_RETURN_MESSAGE_10002');
        }
        $order_list = &$ret['data'];
        filter_fk_name($order_list, array('shop_code|shop'));
        foreach ($order_list as $key => &$order) {
            deal_special_char($order, array('buyer_remark', 'seller_remark', 'order_remark', 'store_remark'));
            $record_decrypt_info = load_model('sys/security/OmsSecurityOptModel')->get_sell_record_decrypt_info($order['sell_record_code']);
            if (empty($record_decrypt_info)) {
                return $this->format_ret(-10010, ['sell_record_code' => $order['sell_record_code']], "订单:{$order['sell_record_code']},遇到无法解密数据！");
            }

            $order = array_merge($order, $record_decrypt_info);

            foreach ($order as $k => $v) {
                if ($k == 'receiver_country' || $k == 'receiver_province' || $k == 'receiver_city' ||
                        $k == 'receiver_district' || $k == 'receiver_street') {
                    $sql = "SELECT name AS {$k} FROM base_area WHERE id='{$v}'";
                    $addr = $this->db->get_row($sql);
                    if ($k != 'receiver_country') {
                        $order[$k . '_code'] = $order[$k];
                    }
                    $order[$k] = isset($addr[$k]) ? $addr[$k] : '';
                }
            }
            $order['discount_fee'] = $order['goods_money'] + $order['express_money'] + $order['delivery_money'] - $order['payable_money'];
            unset($order_list[$key]['goods_money'], $order_list[$key]['delivery_money']);
            $order['receiver_address'] = $order['receiver_country'] . ' ' . $order['receiver_province'] . ' ' . $order['receiver_city'] . ' ' . $order['receiver_district'];
            $order['receiver_address'] .= !empty($order['receiver_street']) ? ' ' . $order['receiver_street'] : '';
            $order['receiver_address'] .= ' ' . $order['receiver_addr'];
            $order['shop_name'] = $order['shop_code_name'];
            unset($order['shop_code_name'], $order['shop_code_code']);
            //提取订单明细
            $order_detail = $this->get_detail_by_sell_record_code($order['sell_record_code']);
            //检测是否为空
            if (empty($order_detail)) {
                $order['detail_list'] = array();
            } else if ($is_get_shelf == 1) {
                $sql_shelf = "SELECT bs.shelf_name goods_shelf FROM goods_shelf gs left
                        JOIN base_shelf bs ON gs.shelf_code=bs.shelf_code and gs.store_code=bs.store_code
                        WHERE gs.sku=:sku and gs.store_code=:store_code";
                foreach ($order_detail as $k1 => $v1) {
                    $sql_values = array(':sku' => $v1['sku'], ':store_code' => $order['store_code']);
                    $goods_shelf = $this->db->get_all($sql_shelf, $sql_values);
                    $order_detail[$k1]['goods_shelf'] = array();
                    foreach ($goods_shelf as $k2 => $v2) {
                        $order_detail[$k1]['goods_shelf'][$k2] = $v2['goods_shelf'];
                    }
                }
            }
            //不开放字段
            $del_key = array(
                'sell_record_detail_id', 'sku_id', 'sku', 'lock_num', 'return_num',
                'goods_weigh', 'lock_inv_status', 'sale_mode', 'delivery_mode', 'delivery_days_or_time',
                'plan_send_time', 'pic_path', 'is_delete', 'lastchanged', 'spec1_code_name', 'spec1_code_code',
                'spec2_code_name', 'spec2_code_code', 'sku_name', 'sku_code', 'goods_code_name', 'goods_code_code', 'status'
            );

            //获取产品名称、规格1和2的名称 | 剔除不开放字段
            foreach ($order_detail as $k => &$value) {
                foreach ($del_key as $v) {
                    if (array_key_exists($v, $value)) {
                        unset($value[$v]);
                    }
                }
            }

            //将订单详细信息压入订单数组中
            $order['detail_list'] = $order_detail;
            unset($order_detail);
        }

        //御城河日志
        load_model('common/TBlLogModel')->set_log_multi($order_list, '开放接口订单查询', 'sendOrder');
        //返回数据给请求方
        return $this->format_ret(1, $ret);
    }

    /**
     * @todo API-根据入参创建sql条件语句
     */
    private function create_sql_where($arr_deal, &$sql_values, &$sql_main) {
        //时间字段映射关系
        $time_fld = array(
            'start_time' => 'record_time',
            'end_time' => 'record_time',
            'start_notice_time' => 'is_notice_time',
            'end_notice_time' => 'is_notice_time',
            'start_delivery_time' => 'delivery_time',
            'end_delivery_time' => 'delivery_time',
            'start_lastchanged' => 'lastchanged',
            'end_lastchanged' => 'lastchanged'
        );
        //因加密需要转换的字段
        $trans_fld = ['receiver_mobile'];
        foreach ($arr_deal as $key => $val) {
            if ($key == 'page' || $key == 'page_size') {
                continue;
            }
            if ($key == 'store_code') {
                $sql_main .= " AND sr.store_code in({$val})";
                continue;
            }
            if (in_array($key, $trans_fld)) {
                $this->get_trans_field($val, 'tel', $sql_main, $sql_values);
                continue;
            }
            if (!array_key_exists($key, $time_fld)) {
                $sql_main .= " AND sr.{$key}=:{$key}";
                $sql_values[":{$key}"] = $val;
                continue;
            }
            if (strpos($key, 'start_') === 0) {
                $sql_main .= " AND sr.{$time_fld[$key]}>=:{$key}";
                $sql_values[":{$key}"] = $val;
                continue;
            }
            if (strpos($key, 'end_') === 0) {
                $sql_main .= " AND sr.{$time_fld[$key]}<=:{$key}";
                $sql_values[":{$key}"] = $val;
                continue;
            }
        }
    }

    private function get_trans_field($field_val, $type, &$sql_main, &$sql_values) {
        $fld_map = ['tel' => 'receiver_mobile'];
        $field = $fld_map[$type];
        $customer_address_id = load_model('crm/CustomerOptModel')->get_customer_address_id_with_search($field_val, $type);
        if (!empty($customer_address_id)) {
            $customer_address_id_str = implode(",", $customer_address_id);
            $sql_main .= " AND (sr.{$field}=:{$field} OR sr.customer_address_id IN ({$customer_address_id_str}) ) ";
            $sql_values[':' . $field] = $field_val;
        } else {
            $sql_main .= " AND sr.{$field}=:receiver_mobile ";
            $sql_values[':' . $field] = $field_val;
        }
    }

    /**
     * @todo API-订单查询接口时间处理
     * @param array $arr_deal
     * @param string $sql_main
     * @param string $sql_values
     */
    private function create_default_time($arr_deal, &$sql_main, &$sql_values) {
        $start_time = date("Y-m-d H:i:s", strtotime("today"));
        $end_time = date("Y-m-d H:i:s", strtotime("today +1 days -1 seconds"));
        $time_arr = array(
            'is_notice_time' => array('start_notice_time', 'end_notice_time'),
            'delivery_time' => array('start_delivery_time', 'end_delivery_time'),
            'lastchanged' => array('start_lastchanged', 'end_lastchanged'),
            'record_time' => array('start_time', 'end_time'),
        );

        $flag = 1; //time_arr时间全部为空标识
        foreach ($time_arr as $key => $val) {
            if (!isset($arr_deal[$val[0]]) && !isset($arr_deal[$val[1]])) {
                continue;
            }
            $flag = 0;
            if (!isset($arr_deal[$val[0]])) {
                $sql_main .= " AND sr.{$key} >= :{$val[0]}";
                $sql_values[":{$val[0]}"] = $start_time;
            }
            if (!isset($arr_deal[$val[1]])) {
                $sql_main .= " AND sr.{$key} <= :{$val[1]}";
                $sql_values[":{$val[1]}"] = $end_time;
            }
        }

        if ($flag == 1) {
            if (!isset($arr_deal['start_time'])) {
                $sql_main .= " AND sr.record_time >= :start_time";
                $sql_values[":start_time"] = $start_time;
            }
            if (!isset($arr_deal['end_time'])) {
                $sql_main .= " AND sr.record_time <= :end_time";
                $sql_values[":end_time"] = $end_time;
            }
        }
    }

    /**
     *
     * 方法名                               api_order_list_get
     *
     * 功能描述                           获取已发货订单信息
     *
     * @author      BaiSon PHP R&D
     * @date        2015-06-19
     * @param       array $param
     *              array(
     *                  可选: 'page', 'page_size',
     *              )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":"10146"}
     */
    public function api_order_list_get($param) {
        //可选字段
        $key_option = array(
            's' => array(
                'page', 'page_size', 'delivery_time_start', 'delivery_time_end', 'shop_code'
            )
        );
        $arr_option = array();
        //提取可选字段中已赋值数据
        $ret_option = valid_assign_array($param, $key_option, $arr_option);
        //合并数据
        $arr_deal = $arr_option;

        //检查单页数据条数是否超限
        if (isset($arr_deal['page_size']) && $arr_deal['page_size'] > 100) {
            return $this->format_ret('-1', array('page_size' => $arr_deal['page_size']), API_RETURN_MESSAGE_PAGE_SIZE_TOO_LARGE);
        }

        //清空无用数据
        unset($arr_option);
        unset($param);

        //开放字段
        $select = '
            `sell_record_code`, `deal_code_list`,`sale_channel_code`, `store_code`, `shop_code`, `customer_code`, `buyer_name`, `receiver_name`,
            `receiver_country`, `receiver_province`, `receiver_city`, `receiver_district`, `receiver_street`, `receiver_address`, `receiver_addr`,
            `receiver_zip_code`, `receiver_mobile`, `receiver_phone`, `receiver_email`, `express_code`, `express_no`, `buyer_remark`,
            `goods_num`, `express_money`, `payable_money`, `paid_money`, `record_time`, `pay_time`, `delivery_time`, `pay_type`, `pay_code`
        ';
        //查询SQL
        $sql_main = "FROM {$this->table} sr WHERE sr.order_status=:order_status AND sr.shipping_status=:shipping_status";
        //绑定数据
        $sql_values = array(':order_status' => 1, ':shipping_status' => 4);
        foreach ($arr_deal as $key => $val) {
            if ($key != 'page' && $key != 'page_size') {
                if ($key == 'delivery_time_start') {
                    $sql_values[":{$key}"] = $val;
                    $sql_main .= " AND sr.delivery_time>=:{$key}";
                } else if ($key == 'delivery_time_end') {
                    $sql_values[":{$key}"] = $val;
                    $sql_main .= " AND sr.delivery_time<=:{$key}";
                } else {
                    $sql_values[":{$key}"] = $val;
                    $sql_main .= " AND sr.{$key}=:{$key}";
                }
            }
        }
        if (isset($arr_deal['shop_code']) && empty($arr_deal['shop_code'])) {
            $sql_main .= " AND sr.shop_code=:shop_code";
            $sql_values[":shop_code"] = $arr_deal['shop_code'];
        }

        $ret = $this->get_page_from_sql($arr_deal, $sql_main, $sql_values, $select);
        if (count($ret['data']) > 0) {
            $order_list = &$ret['data'];
            foreach ($order_list as $key => &$order) {
                $record_decrypt_info = load_model('sys/security/OmsSecurityOptModel')->get_sell_record_decrypt_info($order['sell_record_code']);
                if (empty($record_decrypt_info)) {
                    return $this->format_ret(-10010, '', '请稍后再试，遇到无法解密数据！');
                }
                $order = array_merge($order, $record_decrypt_info);

                //提取订单明细
                $order_detail = $this->get_detail_by_sell_record_code($order['sell_record_code']);
                $order['receiver_address'] = $this->html_decode($order['receiver_address']);
                $order['receiver_addr'] = $this->html_decode($order['receiver_addr']);
                //检测是否为空
                if (empty($order_detail)) {
                    $order_list[$key]['detail_list'] = array();
                } else {
                    //不开放字段
                    $del_key = array(
                        'sell_record_detail_id', 'deal_code', 'sub_deal_code', 'sku_id', 'sku', 'lock_num', 'return_num',
                        'goods_weigh', 'lock_inv_status', 'sale_mode', 'delivery_mode', 'delivery_days_or_time',
                        'plan_send_time', 'pic_path', 'is_delete', 'lastchanged', 'spec1_code_name', 'spec1_code_code',
                        'spec2_code_name', 'spec2_code_code', 'sku_name', 'sku_code', 'goods_code_name', 'goods_code_code'
                    );

                    //获取产品名称、规格1和2的名称 | 剔除不开放字段
                    foreach ($order_detail as $k => &$value) {
                        foreach ($del_key as $v) {
                            if (array_key_exists($v, $value)) {
                                unset($value[$v]);
                            }
                        }
                    }

                    //将订单详细信息压入订单数组中
                    $order_list[$key]['detail_list'] = $order_detail;
                    unset($order_detail);
                }
            }
            //御城河日志
            load_model('common/TBlLogModel')->set_log_multi($order_list, '开放接口列表', 'sendOrder');
            //返回数据给请求方
            return $this->format_ret(1, $ret);
        } else {
            //返回数据给请求方
            return $this->format_ret(-1002, '', API_RETURN_MESSAGE_10002);
        }
    }

    /**
     *
     * 方法名                               api_order_detail_get
     *
     * 功能描述                           获取已发货订单明细
     *
     * @author      BaiSon PHP R&D
     * @date        2015-06-19
     * @param       array $param
     *              array(
     *                  必选: 'sell_record_code',
     *              )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":"10146"}
     */
    public function api_order_detail_get($param) {
        $key_required = array(
            's' => array('sell_record_code')
        );
        $arr_required = array();
        //验证必选字段是否为空并提取必选字段数据
        $ret_required = valid_assign_array($param, $key_required, $arr_required, TRUE);
        //必填项检测通过
        if (TRUE == $ret_required['status']) {
            //合并数据
            $arr_deal = $arr_required;
            //清空无用数据
            unset($arr_required);
            unset($param);

            //提取订单明细
            $ret = $this->get_detail_by_sell_record_code($arr_deal['sell_record_code']);
            if (empty($ret)) {
                return $this->format_ret("-10002", $param, "API_RETURN_MESSAGE_10002");
            } else {

                //御城河日志
                $trade_data = array($ret['data']);
                load_model('common/TBlLogModel')->set_log_multi($trade_data, '开放接口获取明细', 'sendOrder');

                $del_key = array(
                    'sell_record_detail_id', 'deal_code', 'sub_deal_code', 'sku_id', 'sku', 'lock_num', 'return_num',
                    'goods_weigh', 'lock_inv_status', 'sale_mode', 'delivery_mode', 'delivery_days_or_time',
                    'plan_send_time', 'pic_path', 'is_delete', 'lastchanged', 'spec1_code_name', 'spec1_code_code',
                    'spec2_code_name', 'spec2_code_code', 'sku_name', 'sku_code', 'goods_code_name', 'goods_code_code'
                );
                foreach ($ret as $key => &$value) {
                    foreach ($del_key as $v) {
                        if (array_key_exists($v, $value)) {
                            unset($value[$v]);
                        }
                    }
                }


                return $this->format_ret("1", $ret, "API_RETURN_MESSAGE_10003");
            }
        } else {
            return $this->format_ret("-10001", $param, "API_RETURN_MESSAGE_10001");
        }
        return $arr_required;
    }

    //订单详情页面 修改商品信息
    public function update_goods_info($params) {
        if (!empty($params)) {
            $this->begin_trans();
            $params = array_values($params);
            $params_goods_info = $params[0];
            $sell_record_code = &$params_goods_info['sell_record_code'];
            $old_record_detail = $this->get_detail_list_by_code($params_goods_info['sell_record_code'], 'sell_record_detail_id');
            $record = $this->get_record_by_code($sell_record_code);

            try {
                $log_msg = '';
                foreach ($params as $param) {
                    $deal_code = $param['deal_code'];
                    $sell_record_code = $param['sell_record_code'];
                    $spec1_code = $param['spec1_code'];
                    $spec2_code = $param['spec2_code'];
                    $num = $param['goods_num'];
                    $avg_money = $param['avg_money'];
                    $goods_code = $param['goods_code'];
                    $sell_record_detail_id = $param['sell_record_detail_id'];
                    // $sql = "select deal_code,spec1_code,spec2_code,num,avg_money from oms_sell_record_detail where sell_record_detail_id = $sell_record_detail_id";

                    $old_record = $old_record_detail[$sell_record_detail_id];
                    if (empty($old_record)) {
                        throw new Exception('交易号为' . $deal_code . '保存失败,订单明细不存在');
                    }
                    $old_str = $old_record['deal_code'] . $old_record['spec1_code'] . $old_record['spec2_code'] . $old_record['num'] . sprintf("%.2f", $old_record['avg_money']);

                    $sql = "select barcode,sku from goods_sku where goods_code='$goods_code' and spec1_code = '$spec1_code' and spec2_code = '$spec2_code'";
                    $barcode = $this->db->get_row($sql);
                    if (empty($barcode)) {
                        throw new Exception('交易号为' . $deal_code . '保存失败,条码不存在');
                    }
                    $is_gift = empty($old_record['is_gift']) ? 0 : 1;
                    $is_repeat_sku = "select sku,deal_code from oms_sell_record_detail where sell_record_code=:sell_record_code and sell_record_detail_id!=:sell_record_detail_id and is_gift=:is_gift";
                    $is_repeat_sku_value = array(":sell_record_code" => $sell_record_code, ":sell_record_detail_id" => $sell_record_detail_id, ":is_gift" => $is_gift);
                    $sku_array = $this->db->get_all($is_repeat_sku, $is_repeat_sku_value);
                    if (!empty($sku_array)) {
                        $new_array = array();
                        foreach ($sku_array as $key => $sku) {
                            $new_array[] = $sku['deal_code'] . '_' . $sku['sku'];
                        }
                        $_k = $deal_code . '_' . $barcode['sku'];
                        if (in_array($_k, $new_array)) {
                            throw new Exception('交易号为' . $deal_code . '保存失败,条码重复');
                        }
                    }
                    // $r = $this->db->update('oms_sell_record_detail', array('deal_code' => $deal_code, 'spec1_code' => $spec1_code, 'spec2_code' => $spec2_code, 'avg_money' => $avg_money, 'num' => $num, 'barcode' => $barcode['barcode'], 'sku' => $barcode['sku']), array('sell_record_detail_id' => $sell_record_detail_id));
                    $data = array('deal_code' => $deal_code, 'avg_money' => $avg_money, 'num' => $num, 'sku' => $barcode['sku']);
                    if (in_array($record['is_fenxiao'], array(1, 2))) {
//                        $fx_amount = $old_record['trade_price'] * $num;
//                        $data['fx_amount'] = $fx_amount;
                        $data['fx_amount'] = empty($param['fx_amount']) ? 0 : $param['fx_amount'];
                        $data['trade_price'] = empty($param['trade_price']) ? 0 : $param['trade_price'];
                    }
                    $r = $this->db->update('oms_sell_record_detail', $data, array('sell_record_detail_id' => $sell_record_detail_id));
                    if ($r !== true) {
                        throw new Exception('保存失败');
                    }
                    $new_str = $deal_code . $spec1_code . $spec2_code . $num . sprintf("%.2f", $avg_money);


                    $cur_detail_barcode = $barcode['barcode'];

                    if ($old_str !== $new_str) {
                        $log_msg .= "商品条码:" . $cur_detail_barcode . ";数量:" . $num . ";均摊金额:" . $avg_money;
                    }
                }
                if (!empty($log_msg)) {
                    $this->add_action($sell_record_code, "修改商品信息", $log_msg);
                }
                $record_detail = $this->get_detail_list_by_code($sell_record_code);
                //刷新订单数据
                $ret = load_model("oms/SellRecordOptModel")->edit_detail_after_flush_data($record, $old_record_detail, $record_detail, $sell_record_code);
                if ($ret['status'] < 0) {
                    $this->rollback();
                    return $ret;
                }


                $this->commit();

                return array('status' => 1, 'message' => '更新成功');
            } catch (Exception $e) {
                $this->rollback();
                return array('status' => -1, 'message' => $e->getMessage());
            }
        } else {
            return array('status' => -1, 'message' => '请填写正确商品信息！');
        }
    }

    public function update_shipping_info($sell_record_code, $data) {
        if (!$sell_record_code || empty($data)) {
            return array('status' => -1, 'message' => '送货信息有误，请正确填写！');
        }
        $customer_address_id = isset($data['customer_address_id']) ? $data['customer_address_id'] : -1;
        unset($data['sell_record_code']);
        unset($data['customer_address_id']);

        $sysuser = load_model("oms/SellRecordOptModel")->sys_user();
        $record = $this->get_record_by_code($sell_record_code);
        $detail = $this->get_detail_list_by_code($sell_record_code);
        if ($record['order_status'] >= 1) {
            return $this->format_ret(-2, '', '已确认或作废订单不能操作');
        }
        if ($record['shipping_status'] >= 1) {
            return $this->format_ret(-2, '', '已通知配货订单不能操作');
        }
        $customer_address_array = array();
        if ($data['radio_checked'] == 'checked') {
            unset($data['radio_checked']);

            if ($customer_address_id == 0) {
                $ret = $this->filter_elements($data);
                if ($ret['status'] < 1) {
                    return $this->format_ret(-1, '', $ret['message']);
                }
                $customer_address_array['address'] = $data['receiver_addr'];
                $customer_address_array['country'] = $data['receiver_country'];
                $customer_address_array['province'] = $data['receiver_province'];
                $customer_address_array['city'] = $data['receiver_city'];
                $customer_address_array['district'] = $data['receiver_district'];
                $customer_address_array['street'] = $data['receiver_street'];
                $customer_address_array['tel'] = $data['receiver_mobile'];
                $customer_address_array['home_tel'] = $data['receiver_phone'];
                $customer_address_array['name'] = $data['receiver_name'];
                $customer_address_array['customer_code'] = $record['customer_code'];
                $buyer_name = load_model('crm/CustomerOptModel')->get_buyer_name_by_code($record['customer_code'], $customer_address_id);
                if ($buyer_name === false) {
                    return $this->format_ret(-1, '', '暂时不能修改，安全解密异常！');
                }
                $customer_address_array['buyer_name'] = $buyer_name;
                $customer_address_array['shop_code'] = $record['shop_code'];
                $ret_create = load_model('crm/CustomerOptModel')->create_customer_address($customer_address_array);

                if ($ret_create['status'] < 1) {
                    return $ret_create;
                }
                $customer_address_id = $ret_create['data']['customer_address_id'];
            }
        } else {
            $new_data = array();
            $new_data['store_code'] = $data['store_code'];
            $new_data['express_code'] = $data['express_code'];
            $new_data['express_money'] = $data['express_money'];
            $data = $new_data;
        }
        $is_relock_lof = 0;
        if (!empty($data['store_code']) && $data['store_code'] != $record['store_code'] && $record['must_occupy_inv'] == 1 && $record['order_status'] != 3) {
            $is_relock_lof = 1;
        }
        $this->begin_trans();
        try {
            $log_msg = '';
            if ($record['store_code'] != $data['store_code']) {
                $old_store_name = oms_tb_val('base_store', 'store_name', array('store_code' => $record['store_code']));
                $new_store_name = oms_tb_val('base_store', 'store_name', array('store_code' => $data['store_code']));
                $log_msg .= '仓库由' . $old_store_name . '修改为' . $new_store_name;
            }

            if ($is_relock_lof) {
                $ret_1 = load_model("oms/SellRecordOptModel")->lock_detail($record, $detail, 0, 1); //释放锁定
                if ($ret_1['status'] < 1) {
                    $this->rollback();
                    return $ret_1;
                }
            }

//            if (!empty($customer_address_array)) {
//                $customer_ret = $this->db->update('crm_customer_address', $customer_address_array, array('customer_address_id' => $customer_address_id));
//                if ($customer_ret !== true) {
//                    throw new Exception('会员收货地址信息更新失败！');
//                }
//            }

            if ($customer_address_id > 0) {
                $customer_address = load_model('crm/CustomerOptModel')->get_customer_address($customer_address_id);
                $data['receiver_addr'] = $customer_address['address'];
                $data['receiver_phone'] = $customer_address['home_tel'];
                $data['receiver_name'] = $customer_address['name'];
                $data['receiver_mobile'] = $customer_address['tel'];
                $data['receiver_country'] = $customer_address['country'];
                $data['receiver_province'] = $customer_address['province'];
                $data['receiver_city'] = $customer_address['city'];
                $data['receiver_district'] = $customer_address['district'];
                $data['receiver_street'] = $customer_address['street'];

                $country = oms_tb_val('base_area', 'name', array('id' => $data['receiver_country']));
                $province = oms_tb_val('base_area', 'name', array('id' => $data['receiver_province']));
                $city = oms_tb_val('base_area', 'name', array('id' => $data['receiver_city']));
                $district = oms_tb_val('base_area', 'name', array('id' => $data['receiver_district']));
                $street = oms_tb_val('base_area', 'name', array('id' => $data['receiver_street']));
                $data['receiver_address'] = $country . ' ' . $province . ' ' . $city . ' ' . $district . ' ' . $street . ' ' . $data['receiver_addr'];
                $data['customer_address_id'] = $customer_address_id;
            }
            $ret = $this->update($data, array('sell_record_code' => $sell_record_code));
            if ($ret['status'] != 1) {
                throw new Exception('地址物流信息更新失败！');
            }

            if ($is_relock_lof) {
                $record['store_code'] = $data['store_code'];
                foreach ($detail as &$dd) {
                    $dd['lock_num'] = 0;
                }
                $ret_2 = load_model("oms/SellRecordOptModel")->lock_detail($record, $detail, 1, 1); //重新锁定
                if ($ret_2['status'] < 1) {
                    $this->rollback();
                    return $ret_2;
                }
            }

            if ($record['express_code'] != $data['express_code']) {
                $old_express_name = oms_tb_val('base_express', 'express_name', array('express_code' => $record['express_code']));
                $new_express_name = oms_tb_val('base_express', 'express_name', array('express_code' => $data['express_code']));
                $log_msg .= '配送方式由' . $old_express_name . '修改为' . $new_express_name;
            }
            if ($record['express_money'] != $data['express_money']) {

                $upd = array('express_money' => $data['express_money']);
                $ret = $this->update($upd, array('sell_record_code' => $sell_record_code));
                if ($ret['status'] == -1) {
                    $this->rollback();
                    return $ret;
                }
                $ret = $this->refresh_record_price($sell_record_code);

                $sql = "select payable_money,paid_money,pay_type,pay_status from oms_sell_record where sell_record_code = :sell_record_code";
                $pay_record = ctx()->db->get_row($sql, array(':sell_record_code' => $sell_record_code));
                if ($pay_record['payable_money'] > $pay_record['paid_money'] && $pay_record['pay_type'] != 'code' && $pay_record['pay_status'] == 2) {
                    $ret = load_model("oms/SellRecordOptModel")->opt_unpay($sell_record_code);
//                    $problem_remark = '修改金额，导致已付款小于应付款，自动设问换货单';
//                    $ret = load_model("oms/SellRecordOptModel")->set_problem_order('CHANGE_GOODS_MAKEUP', $problem_remark, $sell_record_code);
                    if ($ret['status'] < 0) {
                        $this->rollback();
                        return $ret;
                    }
                }
                $log_msg .= '运费由' . $record['express_money'] . '修改为' . $data['express_money'];
            }
            $this->add_action($sell_record_code, "修改送货信息", '修改送货信息：' . $data['receiver_address'] . "<br>" . $log_msg);
            $this->commit();
            return array('status' => 1, 'message' => '更新成功');
        } catch (Exception $e) {
            $this->rollback();
            return array('status' => -1, 'message' => $e->getMessage());
        }

        //订单‘确认’、或‘通知配货’后，除了‘订单备注’、‘仓库留言’可编辑外，其它项都不能编辑
        //$ret = $this->db->update('oms_sell_record', $data, array('sell_record_code'=>$sell_record_code));


        return $ret;
    }

    public function get_sell_record_decrypt_info(&$sell_record_data) {
        $sell_record_code = $sell_record_data['sell_record_code'];
        $ecrypt_info = load_model('sys/security/OmsSecurityOptModel')->get_sell_record_decrypt_info($sell_record_code);
        $sell_record_data = array_merge($sell_record_data, $ecrypt_info);
    }

    public function update_inv_info($sell_record_code, $data) {
        if (!$sell_record_code) {
            return array('status' => -1, 'message' => '留言信息有误');
        }
        unset($data['sell_record_code']);
        $log_msg = '';
        if ($data['seller_remark_diff'] == 1) {
            $log_msg .= '商家留言修改为：' . $data['seller_remark'] . '；<br>';
            unset($data['seller_remark_diff']);
        }
        if ($data['store_remark_diff'] == 1) {
            $log_msg .= '仓库留言修改为：' . $data['store_remark'] . '；<br>';
            unset($data['store_remark_diff']);
        }
        if ($data['order_remark_diff'] == 1) {
            $log_msg .= '订单备注修改为：' . $data['order_remark'] . '；<br>';
            //更新发货订单的订单备注
            $this->db->update('oms_deliver_record', array('order_remark' => $data['order_remark']), array('sell_record_code' => $sell_record_code));
            unset($data['order_remark_diff']);
        }

        $ret = $this->update($data, array('sell_record_code' => $sell_record_code));
        if (!empty($log_msg)) {
            $this->add_action($sell_record_code, "修改备注及留言", $log_msg);
        }
        return $ret;
    }

    public function update_invoice_info($sell_record_code, $data) {
        $res = $this->check_inv($sell_record_code);
        if ($res['status'] < 0) {
            return $res;
        }
        $log_msg = '';
        if (!empty($data['invoice_status_diff']) && $data['invoice_status_diff'] == 1) {
            if ($data['invoice_status']) {
                $msg = '开票';
            } else {
                $msg = '不开票';
            }
            $log_msg .= '是否开具发票修改为：' . $msg . '<br>';
            unset($data['invoice_status_diff']);
        }
        if ($data['invoice_status'] == 1) { //开票
            $log_data = $this->check_empty_log($data);
            if (!empty($data['invoice_money_diff']) && $data['invoice_money_diff'] == 1) {
                $log_msg .= '订单开票金额由 ' . $data['invoice_money_old'] . ' 元修改为：' . $data['invoice_money'] . '元<br>';
                unset($data['invoice_money_diff']);
            }
            if (!empty($data['invoice_title_diff']) && $data['invoice_title_diff'] == 1) {
                $log_msg .= '发票抬头由 ' . $log_data['invoice_title_old'] . ' 修改为：' . $log_data['invoice_title'] . '<br>';
                unset($data['invoice_title_diff']);
            }
            if (!empty($data['invoice_content_diff']) && $data['invoice_content_diff'] == 1) {
                $log_msg .= '发票内容由 ' . $log_data['invoice_content_old'] . ' 修改为：' . $log_data['invoice_content'] . '<br>';
                unset($data['invoice_content_diff']);
            }
            if (!empty($data['invoice_number_diff']) && $data['invoice_number_diff'] == 1) {
                $log_msg .= '发票号由 ' . $log_data['invoice_number_old'] . ' 修改为：' . $log_data['invoice_number'] . '<br>';
                unset($data['invoice_number_diff']);
            }
            if (!empty($data['invoice_taxpayers_diff']) && $data['invoice_taxpayers_diff'] == 1) {
                $log_msg .= '企业税号由 ' . $log_data['taxpayers_code_old'] . ' 修改为：' . $log_data['taxpayers_code'] . '<br>';
                unset($data['invoice_taxpayers_diff']);
            }
            if (!empty($data['receiver_address_diff']) && $data['receiver_address_diff'] == 1) {
                $log_msg .= '寄送地址由 ' . $log_data['receiver_address_old'] . ' 修改为：' . $log_data['receiver_address'] . '<br>';
                unset($data['receiver_address_diff']);
            }
            if (!empty($data['registered_addr_diff']) && $data['registered_addr_diff'] == 1) {
                $log_msg .= '注册地址由 ' . $log_data['registered_addr_old'] . ' 修改为：' . $log_data['registered_addr'] . '<br>';
                unset($data['registered_addr_diff']);
            }
            if (!empty($data['phone_diff']) && $data['phone_diff'] == 1) {
                $log_msg .= '注册电话由 ' . $log_data['phone_old'] . ' 修改为：' . $log_data['phone'] . '<br>';
                unset($data['phone_diff']);
            }
            if (!empty($data['bank_diff']) && $data['bank_diff'] == 1) {
                $log_msg .= '开户银行由 ' . $log_data['bank_old'] . ' 修改为：' . $log_data['bank'] . '<br>';
                unset($data['bank_diff']);
            }
            if (!empty($data['bank_account_diff']) && $data['bank_account_diff'] == 1) {
                $log_msg .= '银行账号由 ' . $log_data['bank_account_old'] . ' 修改为：' . $log_data['bank_account'] . '<br>';
                unset($data['bank_account_diff']);
            }
            if (!empty($data['receiver_email_diff']) && $data['receiver_email_diff'] == 1) {
                $log_msg .= '邮箱地址由 ' . $log_data['receiver_email_old'] . ' 修改为：' . $log_data['receiver_email'] . '<br>';
                unset($data['receiver_email_diff']);
            }
            if (!empty($data['invoice_title_type_diff']) && $data['invoice_title_type_diff'] == 1) {
                if ($data['title_type'] == 1) {
                    $msg = '企业';
                } else {
                    $msg = '个人';
                }
                $log_msg .= '发票抬头类型修改为：' . $msg . '<br>';
                unset($data['invoice_title_type_diff']);
            }
            if (!empty($data['invoice_type_diff']) && $data['invoice_type_diff'] == 1) {
                $msg = '';
                switch ($data['invoice_type']) {
                    case 'vat_invoice':
                        $msg = '发票类型修改为：纸质发票';
                        break;
                    case 'pt_invoice':
                        $msg = '发票类型修改为：电子发票';
                        break;
                    default:
                        break;
                }
                $log_msg .= $msg;
            }
            $params = array(
                'invoice_title' => $data['invoice_title'],
                'invoice_content' => $data['invoice_content'],
                'invoice_number' => $data['invoice_number'],
                'invoice_status' => $data['invoice_status'],
                'invoice_type' => $data['invoice_type'],
                'taxpayers_code' => isset($data['taxpayers_code']) ? $data['taxpayers_code'] : '',
                'invoice_money' => $data['invoice_money'],
                'invoice_title_type' => $data['title_type'], //发票抬头类型
            );
        } else {
            $params = array(
                'invoice_status' => $data['invoice_status'],
            );
        }

        $this->begin_trans();
        //   if($data['invoice_type'] == 'vat_invoice') {
        if ($data['invoice_status'] == 1) { //开票
            $ret = $this->insert_vat_invoict($sell_record_code, $data);
        } else {
            //不开票
            $ret = $this->insert_not_invoice($sell_record_code, $data);
        }

        if ($ret['status'] < 0) {
            $this->rollback();
            return $ret;
        }
        // }
        $ret = $this->update($params, array('sell_record_code' => $sell_record_code));
        $sql = "select * from oms_sell_record_notice where sell_record_code = :sell_record_code";
        $res = $this->db->getRow($sql, array(':sell_record_code' => $sell_record_code));
        if (!empty($res)) {
            $data_arr = array(
                'invoice_status' => $data['invoice_status'],
            );
            $rus = $this->update_exp('oms_sell_record_notice', $data_arr, array('sell_record_code' => $sell_record_code));
        }
        if ($ret['status'] < 0) {
            $this->rollback();
            return $ret;
        }
        if (!empty($log_msg)) {
            $this->add_action($sell_record_code, "修改发票信息", $log_msg);
        }
        $this->commit();
        return $ret;
    }

    //检验是否开票
    function check_inv($sell_record_code) {
        $sql = "SELECT * FROM oms_sell_invoice WHERE sell_record_code = :sell_record_code";
        $invo_list = $this->db->getRow($sql, array('sell_record_code' => $sell_record_code));
        if (!empty($invo_list)) {
            if (!($invo_list['is_invoice'] == 0 || $invo_list['is_red'] == 2)) {
                return $this->format_ret('-1', '', '订单正在开票中，不能修改发票信息');
            }
        }
        return $this->format_ret(1);
    }

    //验证为空时的日志
    function check_empty_log($data) {
        $log_data = $data;
        foreach ($data as $key => $val) {
            if ($val == '') {
                $log_data[$key] = '无';
            }
        }
        return $log_data;
    }

    function insert_vat_invoict($sell_record_code, $data) {
        $record = $this->get_record_by_code($sell_record_code);
        $invoice_arr = array(
            'sell_record_code' => $sell_record_code,
            'deal_code' => $record['deal_code'],
            'deal_code_list' => $record['deal_code_list'],
            'customer_code' => $record['customer_code'],
            'shop_code' => $record['shop_code'],
            'buyer_name' => $record['buyer_name'],
            'receiver_name' => $record['receiver_name'],
            'company_name' => isset($data['company_name']) ? $data['company_name'] : '',
            'taxpayers_code' => isset($data['taxpayers_code']) ? $data['taxpayers_code'] : '',
            'invoice_amount' => $data['invoice_money'], //开票金额
            'payable_money' => $record['payable_money'], //应收金额去订单中的应付款
            'registered_country' => isset($data['registered_country']) ? $data['registered_country'] : '0',
            'registered_province' => isset($data['registered_province']) ? $data['registered_province'] : '0',
            'registered_city' => isset($data['registered_city']) ? $data['registered_city'] : '0',
            'registered_district' => isset($data['registered_district']) ? $data['registered_district'] : '0',
            'registered_street' => isset($data['registered_street']) ? $data['registered_street'] : '0',
            'registered_addr' => isset($data['registered_addr']) ? $data['registered_addr'] : '',
            'registered_address' => isset($data['registered_address']) ? $data['registered_address'] : '',
            'phone' => isset($data['phone']) ? $data['phone'] : '',
            'bank' => isset($data['bank']) ? $data['bank'] : '',
            'bank_account' => isset($data['bank_account']) ? $data['bank_account'] : '',
            'is_company' => $data['is_company'],
            'invoice_title' => isset($data['invoice_title']) ? $data['invoice_title'] : '',
            'invoice_content' => isset($data['invoice_content']) ? $data['invoice_content'] : '',
            'invoice_number' => isset($data['invoice_number']) ? $data['invoice_number'] : '',
            'receiver_address' => isset($data['receiver_address']) ? $data['receiver_address'] : '',
            'receiver_email' => isset($data['receiver_email']) ? $data['receiver_email'] : '',
            'status' => $data['invoice_status'],
        );
        //vat_invoice"
        $invoice_arr['invoice_type'] = 1; //电子发票
        if ($data['invoice_type'] == 'vat_invoice') {
            $invoice_arr['invoice_type'] = 2; //纸张发票
        }

        $update_str = 'payable_money=VALUES(payable_money),invoice_amount=VALUES(invoice_amount),status=VALUES(status),receiver_address=VALUES(receiver_address),shop_code=VALUES(shop_code),receiver_email=VALUES(receiver_email),invoice_title=VALUES(invoice_title),invoice_content=VALUES(invoice_content),invoice_number=VALUES(invoice_number),is_company=VALUES(is_company),invoice_type=VALUES(invoice_type),receiver_name=VALUES(receiver_name),company_name=VALUES(company_name),taxpayers_code=VALUES(taxpayers_code),registered_country=VALUES(registered_country),registered_province=VALUES(registered_province),registered_city=VALUES(registered_city),registered_district=VALUES(registered_district),registered_street=VALUES(registered_street),registered_addr=VALUES(registered_addr),registered_address=VALUES(registered_address),phone=VALUES(phone),bank=VALUES(bank),bank_account=VALUES(bank_account)';
        $ret = $this->insert_multi_duplicate('oms_sell_invoice', array($invoice_arr), $update_str);
        return $ret;
    }

    //修改发票信息，不开票
    function insert_not_invoice($sell_record_code, $data) {
        $invoice_arr = array(
            'sell_record_code' => $sell_record_code,
            'status' => $data['invoice_status'],
        );
        $update_str = 'status=VALUES(status)';
        $ret = $this->insert_multi_duplicate('oms_sell_invoice', array($invoice_arr), $update_str);
        return $ret;
    }

    private function get_new_customer_address_array($record, $customer_address) {
        $new_customer_address = array();
        $new_customer_address['address'] = $record['receiver_addr'];
        $new_customer_address['country'] = $record['receiver_country'];
        $new_customer_address['province'] = $record['receiver_province'];
        $new_customer_address['city'] = $record['receiver_city'];
        $new_customer_address['district'] = $record['receiver_district'];
        $new_customer_address['street'] = $record['receiver_street'];
        $new_customer_address['zipcode'] = $record['receiver_zip_code'];
        $new_customer_address['tel'] = $record['receiver_mobile'];
        $new_customer_address['home_tel'] = $record['receiver_phone'];
        $new_customer_address['name'] = $record['receiver_name'];
        $new_customer_address['address1'] = $record['receiver_address'];
        array_unshift($customer_address, $new_customer_address);
        return $customer_address;
    }

    /**
     *
     * 方法名       get_record_by_deal_code
     *
     * 功能描述     按交易号获取订单信息
     *
     * @author      BaiSon PHP R&D
     * @date        2015-07-22
     * @param       string $deal_code
     *
     * @return      array
     */
    public function get_record_by_deal_code($deal_code) {
        if (empty($deal_code)) {
            return $this->format_ret('-1', '', 'error_params');
        }
        $filter = array(':deal_code' => $deal_code);
        $sql = "SELECT DISTINCT omrd.sell_record_code FROM {$this->detail_table} omrd WHERE omrd.deal_code=:deal_code";
        $ret = $this->db->get_all($sql, $filter);
        if (empty($ret)) {
            return $this->format_ret('-1', '', 'op_no_data');
        }
        $tem_arr = array_map(function ($v) {
            return $v['sell_record_code'];
        }, $ret);
        $tem_str = "'" . implode("','", $tem_arr) . "'";
        $sql = "SELECT omr.* FROM {$this->table} omr WHERE omr.sell_record_code IN ($tem_str)";
        $ret = $this->db->get_all($sql);
        if (empty($ret)) {
            return $this->format_ret('-1', '', 'op_no_data');
        } else {
            return $this->format_ret('1', $ret, 'op_success');
        }
    }

    //过滤传值元素
    public function filter_elements($data) {
        $status = 1;
        $message = '';
        if (empty($data['receiver_mobile']) && empty($data['receiver_phone'])) {
            unset($data['receiver_mobile']);
            unset($data['receiver_phone']);
            $status = -1;
            $message = '请输出收货人手机或电话';
        }

        foreach ($data as $key => $val) {
            if ((empty($val) || empty(trim($val))) && ($key != 'receiver_mobile' && $key != 'receiver_phone' && $key != 'receiver_street' && $key != 'receiver_district')) {
                $status = -1;
                $message = '送货信息不全';
                break;
            }
        }

        return array('status' => $status, 'data' => $data, 'message' => $message);
    }

    function update_return_num_money($detailList) {
        $ret = true;
        foreach ($detailList as $detail) {
            $sell_record_code = $detail['sell_record_code'];
            $sku = $detail['sku'];
            $deal_code = $detail['deal_code'];
            $avg_money = $detail['avg_money'];
            $recv_num = $detail['recv_num'];
            $sql = "update oms_sell_record_detail set return_num=return_num+{$recv_num},return_money=return_money+{$avg_money} where sell_record_code='{$sell_record_code}' and sku = '{$sku}' and deal_code = '{$deal_code}' and is_delete = 0";
            $ret = $this->db->query($sql);
            if (!$ret) {
                break;
            }
        }
        return $ret;
    }

    /**
     * @tode        订单拦截接口
     * @author      BaiSon PHP R&D
     * @date        2016-03-15
     * @param       array $param
     *               array(
     *                  必选: 'sell_record_code'
     *                  可选: 'desc'
     *               )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":"10146"}
     */
    function api_order_intercep($param) {
        if (!isset($param['sell_record_code'])) {
            return $this->format_ret(-10001, '', '订单号为必填项');
        }
        $sell_record_code = $param['sell_record_code'];
        $msg = isset($param['desc']) ? $param['desc'] : '接口拦截单据';
        load_model('oms/SellRecordActionModel')->record_log_check = 0;
        $record = $this->get_record_by_code($sell_record_code);
        $detail = $this->get_detail_list_by_code($sell_record_code);
        $sys_user = array('user_code' => 'admin', 'is_api' => '1');
        $check = load_model('oms/SellRecordOptModel')->opt_intercept_check($record, $detail, $sys_user);
        $ret = load_model('oms/SellRecordOptModel')->biz_intercept($record, 0, $msg, 0);

        if ($ret['status'] > 0) {
            $tag_req['desc'] = "开放接口拦截设问";
            $ret_tag = load_model('oms/SellRecordOptModel')->opt_problem_get_tag($sell_record_code, 'WMS_SHORT_ORDER', $tag_req);
            $this->insert_multi_exp('oms_sell_record_tag', $ret_tag['data']['tag_data'], true);
            $record_data['is_problem'] = 1;
            $where = "  sell_record_code = '{$record['sell_record_code']}' ";
            $this->db->update('oms_sell_record', $record_data, $where);
        }

        load_model('oms/SellRecordActionModel')->record_log_check = 1;
        return $ret;
    }

    //批量操作（任务执行）
    function opt_batch_task($request) {
        $sell_record_code_arr = explode(',', $request['sell_record_code']);
        $fun = $request['fun'];
        $_user_type = $request['_user_type'];
        $task = array(
            'total' => count($sell_record_code_arr),
            'fail' => 0,
            'success' => 0,
            'fail_msg' => '',
        );
        $lock_record = array();
        foreach ($sell_record_code_arr as $sell_record_code) {
            //先锁定
            $record = $this->get_record_by_code($sell_record_code);
            if ($record['is_lock'] == '0') {
                $ret = load_model("oms/SellRecordOptModel")->opt_lock($sell_record_code);
                if ($ret['status'] != 1) {
                    $task['fail'] ++;
                    $task['fail_msg'] .= $sell_record_code . $ret['message'] . "<br />";
                    continue;
                }
                $lock_record[] = $sell_record_code;
            }
            $ret = load_model("oms/SellRecordOptModel")->$fun($sell_record_code);
            if ($ret['status'] != 1) {
                $task['fail'] ++;
                $task['fail_msg'] .= $sell_record_code . $ret['message'] . "<br />";
                continue;
            }
            $task['success'] ++;
        }
        //解锁订单
        if (!empty($lock_record)) {
            foreach ($lock_record as $lock_code) {
                $ret = load_model("oms/SellRecordOptModel")->opt_unlock($lock_code);
            }
        }

        //任务执行完成
        $task_msg = "总订单数：" . $task['total'] . ",成功：" . $task['success'] . ",失败：" . $task['fail'] . "<br />";
        $task_msg .= $task['fail_msg'];
        //$_user_type = 'sell_record_confirm';
        $ret = load_model("sys/UserTaskModel")->save_msg($task_msg, $_user_type);
        return $ret;
    }

    //获取改款商品明细
    function get_change_goods($request) {
        $sql_main = '';
        $sql_join = '';
        $sql_join .= "LEFT JOIN base_goods r2 ON r1.goods_code = r2.goods_code
                      LEFT JOIN goods_sku r3 ON r1.sku = r3.sku";
        $select = "SELECT
                    r1.sku,
                    r1.goods_code,
                    r1.stock_num,
                    r1.lock_num,
                    r2.goods_name,
                    r2.barcode,
                    r3.barcode,
                    r3.spec1_name,
                    r3.spec2_name";

        if (isset($request['lof_status']) && $request['lof_status'] == 1) {
            $sql_main .= "{$select},lof_no FROM goods_inv_lof r1 {$sql_join} WHERE 1=1";
        } else {
            $sql_main .= "{$select} FROM goods_inv r1 {$sql_join} WHERE 1=1 ";
        }
        $store_code = isset($request['store_code']) ? $request['store_code'] : '';
        $goods_filter = isset($request['goods_multi']) ? $request['goods_multi'] : '';
        $sql_main .= " AND (r1.goods_code = :goods_filter OR goods_name = :goods_filter OR r3.barcode = :goods_filter)
                      AND r1.store_code = :store_code ";
        $sql_main .= " AND r2.status = :status ";
        $sql_main .= " GROUP BY r1.sku";
        $sql_values = array(":goods_filter" => $goods_filter, ":store_code" => $store_code, ':status' => 0);
        $data = CTX()->db->get_all($sql_main, $sql_values);
        foreach ($data as &$value) {
            $value['available_num'] = (int) $value['stock_num'] - (int) $value['lock_num'];
            $value['available_num'] = ($value['available_num'] < 0) ? 0 : $value['available_num'];
        }

        return $data;
    }

    //获取改款商品明细
    function get_change_detail_goods($request) {
        $sql_main = '';
        $sql_join = '';
        $sql_join .= "LEFT JOIN base_goods r2 ON r1.goods_code = r2.goods_code
                      LEFT JOIN goods_sku r3 ON r1.sku = r3.sku";
        $select = "SELECT
                    r1.sku,
                    r1.goods_code,
                    r1.stock_num,
                    r1.lock_num,
                    r2.goods_name,
                    r2.barcode,
                    r3.barcode,
                    r3.spec1_name,
                    r3.spec2_name";

        if (isset($request['lof_status']) && $request['lof_status'] == 1) {
            $sql_main .= "{$select},lof_no FROM goods_inv_lof r1 {$sql_join} WHERE 1=1";
        } else {
            $sql_main .= "{$select} FROM goods_inv r1 {$sql_join} WHERE 1=1 ";
        }
        $goods_filter = isset($request['goods_multi']) ? $request['goods_multi'] : '';
        $sql_main .= " AND (r1.goods_code = :goods_filter OR goods_name = :goods_filter OR r3.barcode = :goods_filter)";
        $sql_main .= " AND r2.status = :status ";
        $sql_main .= " GROUP BY r1.sku";
        $sql_values = array(":goods_filter" => $goods_filter, ':status' => 0);
        $data = CTX()->db->get_all($sql_main, $sql_values);
//        foreach ($data as &$value) {
//            $value['available_num'] = (int) $value['stock_num'] - (int) $value['lock_num'];
//            $value['available_num'] = ($value['available_num'] < 0) ? 0 : $value['available_num'];
//        }

        return $data;
    }

    function get_unsalable_report($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql_values = array();
        $where = '';
        $sql_join = "inner join base_goods bg on bg.goods_code = r1.goods_code
                    inner join api_goods_sku r2 on r2.goods_barcode = r1.barcode
                    inner join api_goods r3 on r3.goods_from_id = r2.goods_from_id and r3.status = 1";

        $sql_main = "FROM goods_sku r1 $sql_join WHERE 1";
        //过滤品牌权限
        $filter_brand_code = isset($filter['brand_code']) ? $filter['brand_code'] : null;
        $sql_main .= load_model('prm/BrandModel')->get_sql_purview_brand('bg.brand_code', $filter_brand_code);
        if (isset($filter['goods_code']) && !empty($filter['goods_code'])) {
            $sql_main .= " AND r1.goods_code = :goods_code";
            $sql_values[':goods_code'] = $filter['goods_code'];
        }

        if (isset($filter['barcode']) && !empty($filter['barcode'])) {
            $sql_main .= " AND  r1.barcode LIKE :barcode";
            $sql_values[':barcode'] = "%" . $filter['barcode'] . "%";
        }

        if (isset($filter['goods_name']) && $filter['goods_name'] !== '') {
            $sql_main .= " AND bg.goods_name LIKE :goods_name ";
            $sql_values[':goods_name'] = "%" . $filter['goods_name'] . "%";
        }

        if (isset($filter['record_time_start']) && !empty($filter['record_time_start'])) {
            //$record_time_start = strtotime(date("Y-m-d", strtotime($filter['record_time_start'])));
            $record_time_start = $filter['record_time_start'];
            $where .= " AND r.record_time >= '{$record_time_start}'";
        }

        if (isset($filter['record_time_end']) && !empty($filter['record_time_end'])) {
            $record_time_end = $filter['record_time_end'];
            $where .= " AND r.record_time <= '{$record_time_end}'";
        }

        $sql_main .= " AND r1.sku not in (
                        SELECT
                            sku
                        FROM
                            oms_sell_record r
                        INNER JOIN oms_sell_record_detail rr ON r.sell_record_code = rr.sell_record_code {$where})";
        $group = " group by r1.sku";
        $sql_main .= $group;
        $select = " bg.goods_name,r1.goods_code,r1.sku,r1.barcode ";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, $group);
        foreach ($data['data'] as &$value) {
            $key_arr = array('spec1_name', 'spec2_name');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
            $value = array_merge($value, $sku_info);
        }
        return $this->format_ret(1, $data);
    }

    //订单波次生成列表
    function get_wave_by_page($filter) {
        //去掉空格
        foreach ($filter as $key => $v) {
            $filter[$key] = trim($v);
        }
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = $filter['keyword'];
        }
        $detail_table = "oms_sell_record_notice_detail";
        $wave_table = "oms_sell_record_notice";
        $sql_values = array();
        $sql_join = "";
        $sql_join .= " left join  {$detail_table} rr on  r1.sell_record_code = rr.sell_record_code ";
        $is_join = false;
        $is_join_goods = false;
        $sub_values = array();
        $sql_main_select = " FROM {$wave_table} r1 ";
        $sql_main = "  WHERE 1 ";
//r1.is_fenxiao = 0 and r1.order_status = 1 and r1.shipping_status > 0
        //商店仓库权限
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('r1.store_code', $filter_store_code);
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('r1.shop_code', $filter_shop_code);

        $wms_store = load_model('sys/WmsConfigModel')->get_wms_store_all();
        $o2o_store = load_model('o2o/O2oEntryModel')->get_o2o_store_all();
        $no_select_store = array_merge($wms_store, $o2o_store);

        if (!empty($no_select_store)) {
            $no_select_store = array_unique($no_select_store);
            $no_store_str = "'" . implode("','", $no_select_store) . "'";
            $sql_main .= " AND r1.store_code not in($no_store_str) ";
        }

        //库位
        if (isset($filter['shelf_code']) && $filter['shelf_code'] != '') {
            $sql_join .= ' LEFT JOIN goods_shelf gs ON rr.sku=gs.sku AND gs.store_code=r1.store_code';
            $shelf_code = explode(',', $filter['shelf_code']);
            $shelf_str = $this->arr_to_in_sql_value($shelf_code, 'shelf_code', $sql_values);
            $sql_main .= " AND gs.shelf_code IN ({$shelf_str}) ";
            $is_join = TRUE;
        }
        //是否含运费
        $contain_express_money = 0;
        if (isset($filter['contain_express_money']) && $filter['contain_express_money'] == '1') {
            $contain_express_money = 1;
        }
        //订单价格
        if (isset($filter['money_start']) && $filter['money_start'] !== '') {
            if ($contain_express_money == 1) {
                $sql_main .= " AND r1.payable_money>= :money_start";
            } else {
                $sql_main .= " AND r1.payable_money - r1.express_money >= :money_start";
            }
            $sql_values[':money_start'] = $filter['money_start'];
        }
        if (isset($filter['money_end']) && $filter['money_end'] !== '') {
            if ($contain_express_money == 1) {
                $sql_main .= " AND r1.payable_money<= :money_end";
            } else {
                $sql_main .= " AND r1.payable_money - r1.express_money <= :money_end";
            }
            $sql_values[':money_end'] = $filter['money_end'];
        }
//        var_dump($sql_main,$sql_join);exit;
        //是否称重
//        if (isset($filter['is_weigh']) && $filter['is_weigh'] != 'all') {
//            $sql_main .= " AND r1.is_weigh = :is_weigh ";
//            $sql_values[':is_weigh'] = $filter['is_weigh'];
//        }
        //生产波次
//        if (isset($filter['waves_record_id']) && $filter['waves_record_id'] != 'all') {
//            if ($filter['waves_record_id'] == '0') {
//                $sql_main .= " AND r1.waves_record_id < 1";
//            } else {
//                $sql_main .= " AND r1.waves_record_id > 0";
//            }
//        }
//        if (!isset($filter['waves_record_id'])) {
//            $sql_main .= " AND r1.waves_record_id <= 1";
//        }
        //发货状态
//        if (!empty($filter['shipping_status']) && $filter['shipping_status'] != 'all') {
//            $sql_main .= " AND r1.shipping_status in (:shipping_status) ";
//            $sql_values[':shipping_status'] = explode(',', $filter['shipping_status']);
//        }
//        if (!isset($filter['shipping_status'])) {
//            $sql_main .= " and r1.shipping_status in (1,2,3) ";
//        }
        //订单号
        if (!empty($filter['sell_record_code'])) {
            $sql_main .= " AND r1.sell_record_code LIKE :sell_record_code ";
            $sql_values[':sell_record_code'] = '%' . $filter['sell_record_code'] . '%';
        }
        //交易号
        if (!empty($filter['deal_code'])) {
            $sql_main .= " AND r1.deal_code_list LIKE :deal_code ";
            $sql_values[':deal_code'] = '%' . $filter['deal_code'] . '%';
        }
        //收货人
        if (!empty($filter['receiver_name'])) {

            $customer_address_id = load_model('crm/CustomerOptModel')->get_customer_address_id_with_search($filter['receiver_name'], 'name');
            if (!empty($customer_address_id)) {
                $customer_address_id_str = implode(",", $customer_address_id);
                $sql_main .= " AND ( r1.receiver_name LIKE :receiver_name  OR r1.customer_address_id in ({$customer_address_id_str}) ) ";
                $sql_values[':receiver_name'] = '%' . $filter['receiver_name'] . '%';
            } else {
                $sql_main .= " AND r1.receiver_name LIKE :receiver_name ";
                $sql_values[':receiver_name'] = '%' . $filter['receiver_name'] . '%';
            }
        }
        //买家昵称
        if (!empty($filter['buyer_name'])) {

            $customer_code_arr = load_model('crm/CustomerOptModel')->get_customer_code_with_search($filter['buyer_name']);
            if (!empty($customer_code_arr)) {

                $customer_code_str = "'" . implode("','", $customer_code_arr) . "'";
                $sql_main .= " AND ( r1.customer_code in ({$customer_code_str}) ) ";
            } else {
                $sql_main .= " AND r1.buyer_name = :buyer_name ";
                $sql_values[':buyer_name'] = $filter['buyer_name'];
            }
//            $sql_main .= " AND r1.buyer_name LIKE :buyer_name ";
//            $sql_values[':buyer_name'] = '%' . $filter['buyer_name'] . '%';
        }
        //配送方式
        if (isset($filter['express_code']) && $filter['express_code'] != '') {
            $sql_main .= " AND r1.express_code in (:express_code) ";
            $sql_values[':express_code'] = explode(',', $filter['express_code']);
        }
        //商品名称
        if (isset($filter['goods_name']) && $filter['goods_name'] != '') {
            $is_join = TRUE;
            $sql_join .= " left join base_goods dr on dr.goods_code=rr.goods_code";
            $sql_main .= " AND dr.goods_name like :goods_name ";
            $sql_values[':goods_name'] = '%' . $filter['goods_name'] . '%';
        }
        //商品编码
        if (!empty($filter['goods_code'])) {
            $goods = explode(',', $filter['goods_code']);

            $goods_code_sql = $this->arr_to_in_sql_value($goods, 'goods_code', $sql_values);

            $sql_main .= " AND rr.goods_code IN ( " . $goods_code_sql . " ) ";
            $is_join = TRUE;
        }
        //商品条形码
        if (!empty($filter['barcode'])) {

//
//            $sub_sql .= " AND r2.barcode LIKE :barcode ";
//            $sub_values[':barcode'] = '%' . $filter['barcode'] . '%';

            $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
            if (empty($sku_arr)) {
                $sql_main .= " AND 1=2 ";
            } else {
                $sku_str = "'" . implode("','", $sku_arr) . "'";
                $sql_main .= " AND rr.sku in({$sku_str}) ";
            }
            $is_join = TRUE;
        }
        //套餐条形码
        if (isset($filter['combo_barcode']) && $filter['combo_barcode'] != '') {
            $combo_sku_arr = load_model('prm/GoodsComboModel')->get_combo_sku_by_barcode(trim($filter['combo_barcode']));
            if (!empty($combo_sku_arr)) {
                $combo_sku_str = $this->arr_to_in_sql_value($combo_sku_arr, 'combo_sku', $sql_values);
                $sql_main .= " AND rr.combo_sku in ({$combo_sku_str}) ";
            } else {
                $sql_main .= " AND 1=2 ";
            }
            $is_join = TRUE;
        }
        //包含SKU
        if (!empty($filter['sku'])) {
            $sql_main .= " AND rr.sku LIKE :sku";
            $sql_values[':sku'] = '%' . $filter['sku'] . '%';
            $is_join = TRUE;
        }
        //排除商品编码
        if (!empty($filter['goods_code_exp'])) {
            $sql_main .= " AND rr.goods_code NOT LIKE :goods_code_exp";
            $sql_values[':goods_code_exp'] = '%' . $filter['goods_code_exp'] . '%';
            $is_join = TRUE;
        }
        //付款类型
        if (!empty($filter['pay_type'])) {
            $sql_main .= " AND r1.pay_type = :pay_type";
            $sql_values[':pay_type'] = $filter['pay_type'];
        }

        //sku种类数
        if (!empty($filter['sku_num'])) {
            $sql_main .= " AND r1.sku_num = :sku_num ";
            $sql_values[':sku_num'] = $filter['sku_num'];
        }
        //商品数量
        if (isset($filter['num_end']) && $filter['num_end'] !== '') {
            $sql_main .= " AND r1.goods_num >= :num_start ";
            $sql_values[':num_start'] = $filter['num_start'];
        }
        if (isset($filter['num_end']) && $filter['num_end'] !== '') {
            $sql_main .= " AND r1.goods_num <= :num_end ";
            $sql_values[':num_end'] = $filter['num_end'];
        }

        //发票
        if (isset($filter['invoice_status']) && $filter['invoice_status'] != '') {
            if ($filter['invoice_status'] == '1') {
                $sql_main .= " AND r1.invoice_status <> 0";
            } elseif ($filter['invoice_status'] == '0') {
                $sql_main .= " AND r1.invoice_status = 0";
            }
        }
        //销售平台
        if (!empty($filter['source'])) {
            $sql_main .= " AND r1.sale_channel_code in (:source) ";
            $sql_values[':source'] = explode(',', $filter['source']);
        }
        //店铺
        if (!empty($filter['shop_code'])) {
            $sql_main .= " AND r1.shop_code in (:shop_code) ";
            $sql_values[':shop_code'] = explode(',', $filter['shop_code']);
        }
        //买家留言
        if (isset($filter['buyer_remark']) && $filter['buyer_remark'] != '') {
            if ($filter['buyer_remark'] == '1') {
                $sql_main .= " AND r1.buyer_remark <> ''";
            } else {
                $sql_main .= " AND r1.buyer_remark = ''";
            }
        }

        //商家留言
        if (isset($filter['seller_remark']) && $filter['seller_remark'] != '') {
            if ($filter['seller_remark'] == '1') {
                $sql_main .= " AND r1.seller_remark <> ''";
            } else {
                $sql_main .= " AND r1.seller_remark = ''";
            }
        }
        //是否加急单
        if (isset($filter['is_rush']) && $filter['is_rush'] != '') {
            if ($filter['is_rush'] == '1') {
                $sql_main .= " AND r1.is_rush = '1'";
            } else {
                $sql_main .= " AND r1.is_rush = '0'";
            }
        }
        //重量
        if (isset($filter['weight_start']) && $filter['weight_start'] != '') {
            $sql_main .= " AND r1.goods_weigh >= :weight_start ";
            $sql_values[':weight_start'] = $filter['weight_start'];
        }
        if (isset($filter['weight_end']) && $filter['weight_end'] != '') {
            $sql_main .= " AND r1.goods_weigh <= :weight_end ";
            $sql_values[':weight_end'] = $filter['weight_end'];
        }
        //仓库留言
        if (isset($filter['store_remark']) && $filter['store_remark'] != '') {
            if ($filter['store_remark'] == '1') {
                $sql_main .= " AND r1.store_remark <> ''";
            } else {
                $sql_main .= " AND r1.store_remark = ''";
            }
        }

        //品牌
        if (isset($filter['brand_code']) && $filter['brand_code'] != '') {
            $sql_main .= " AND r3.brand_code in (:brand_code) ";
            $sql_values[':brand_code'] = explode(',', $filter['brand_code']);
            $is_join = true;
            $is_join_goods = true;
        }
        //季节
        if (isset($filter['season_code']) && $filter['season_code'] != '') {
            $sql_main .= " AND r3.season_code in (:season_code) ";
            $sql_values[':season_code'] = explode(',', $filter['season_code']);
            $is_join = true;
            $is_join_goods = true;
        }
        //规格1
        if ($filter['spec1'] != '') {
            $spec1_arr = explode(',', $filter['spec1']);
            $spec1_record = load_model('oms/SellRecordNoticeModel')->get_sell_record_by_spec($spec1_arr, $type = "spec1");
            if (!empty($spec1_record)) {
                $spec1_record_str = $this->arr_to_in_sql_value($spec1_record, 'spec1_code', $sql_values);
                $sql_main .= " AND r1.sell_record_code in ({$spec1_record_str}) ";
            } else {
                $sql_main .= " AND 1=2 ";
            }
        }
        //规格2
        if ($filter['spec2'] != '') {
            $spec2_arr = explode(',', $filter['spec2']);
            $spec2_record = load_model('oms/SellRecordNoticeModel')->get_sell_record_by_spec($spec2_arr, $type = "spec2");
            if (!empty($spec2_record)) {
                $spec2_record_str = $this->arr_to_in_sql_value($spec2_record, 'spec2_code', $sql_values);
                $sql_main .= " AND r1.sell_record_code in ({$spec2_record_str}) ";
            } else {
                $sql_main .= " AND 1=2 ";
            }
        }


        //付款时间
        if (!empty($filter['pay_time_start'])) {
            $sql_main .= " AND r1.pay_time >= :pay_time_start ";
            $pay_time_start = strtotime(date("Y-m-d", strtotime($filter['pay_time_start'])));
            if ($pay_time_start == strtotime($filter['pay_time_start'])) {
                $sql_values[':pay_time_start'] = $filter['pay_time_start'];
            } else {
                $sql_values[':pay_time_start'] = $filter['pay_time_start'];
            }
        }
        if (!empty($filter['pay_time_end'])) {
            $sql_main .= " AND r1.pay_time <= :pay_time_end ";
            $pay_time_end = strtotime(date("Y-m-d", strtotime($filter['pay_time_end'])));
            if ($pay_time_end == strtotime($filter['pay_time_end'])) {
                $sql_values[':pay_time_end'] = $filter['pay_time_end'];
            } else {
                $sql_values[':pay_time_end'] = $filter['pay_time_end'];
            }
        }

        //通知配货时间
        if (!empty($filter['is_notice_time_start'])) {
            $sql_main .= " AND r1.is_notice_time >= :is_notice_time_start ";
            $is_notice_time_start = strtotime(date("Y-m-d", strtotime($filter['is_notice_time_end'])));
            if ($is_notice_time_start == strtotime($filter['is_notice_time_start'])) {
                $sql_values[':is_notice_time_start'] = $filter['is_notice_time_start'];
            } else {
                $sql_values[':is_notice_time_start'] = $filter['is_notice_time_start'];
            }
        }
        if (!empty($filter['is_notice_time_end'])) {
            $sql_main .= " AND r1.is_notice_time <= :is_notice_time_end ";
            $is_notice_time_end = strtotime(date("Y-m-d", strtotime($filter['is_notice_time_end'])));
            if ($is_notice_time_end == strtotime($filter['is_notice_time_end'])) {
                $sql_values[':is_notice_time_end'] = $filter['is_notice_time_end'];
            } else {
                $sql_values[':is_notice_time_end'] = $filter['is_notice_time_end'];
            }
        }
        //计划发货时间
        if (!empty($filter['plan_send_time_start'])) {
            $sql_main .= " AND r1.plan_send_time >= :plan_send_time_start ";
            $sql_values[':plan_send_time_start'] = $filter['plan_send_time_start'];
        }
        if (!empty($filter['is_plan_send_time_end'])) {
            $sql_main .= " AND r1.plan_send_time <= :plan_send_time_end ";
            $sql_values[':plan_send_time_end'] = $filter['plan_send_time_end'];
        }
        //下单时间
        if (!empty($filter['record_time_start'])) {
            $sql_main .= " AND r1.record_time >= :record_time_start ";
            $record_time_start = strtotime(date("Y-m-d", strtotime($filter['record_time_start'])));
            if ($record_time_start == strtotime($filter['record_time_start'])) {
                $sql_values[':record_time_start'] = $filter['record_time_start'];
            } else {
                $sql_values[':record_time_start'] = $filter['record_time_start'];
            }
        }
        if (!empty($filter['record_time_end'])) {
            $sql_main .= " AND r1.record_time <= :record_time_end ";
            $record_time_end = strtotime(date("Y-m-d", strtotime($filter['record_time_end'])));
            if ($record_time_end == strtotime($filter['record_time_end'])) {
                $sql_values[':record_time_end'] = $filter['record_time_end'];
            } else {
                $sql_values[':record_time_end'] = $filter['record_time_end'];
            };
        }
        //国家
        if (isset($filter['country']) && $filter['country'] !== '') {
            $sql_main .= " AND r1.receiver_country = :country ";
            $sql_values[':country'] = $filter['country'];
        }
        //省
        if (isset($filter['province']) && $filter['province'] !== '') {
            $sql_main .= " AND r1.receiver_province = :province ";
            $sql_values[':province'] = $filter['province'];
        }
        //城市
        if (isset($filter['city']) && $filter['city'] !== '') {
            $sql_main .= " AND r1.receiver_city = :city ";
            $sql_values[':city'] = $filter['city'];
        }
        //地区
        if (isset($filter['district']) && $filter['district'] !== '') {
            $sql_main .= " AND r1.receiver_district = :district ";
            $sql_values[':district'] = $filter['district'];
        }
        /* 省份多选 */
        // 省（多选）
        if (isset($filter['province_multi']) && $filter['province_multi'] !== '') {
            $province_multi = explode(',', $filter['province_multi']);
            $stand = $this->arr_to_in_sql_value($province_multi, 'receiver_province', $sql_values);
            $sql_main .= " AND r1.receiver_province IN ($stand) ";
        }
        //订单标签
        if (isset($filter['order_tag']) && $filter['order_tag'] !== '') {
            $tag_arr = explode(',', $filter['order_tag']);
            $_tag_str = "'" . implode("','", $tag_arr) . "'";
            if (in_array('none', $tag_arr)) {
                if (count($tag_arr) > 1) {
                    $sql_tag = "SELECT rn.sell_record_code,rt.tag_v FROM	oms_sell_record_notice rn LEFT JOIN oms_sell_record_tag rt ON rn.sell_record_code = rt.sell_record_code AND rt.tag_type = 'order_tag' having tag_v  in({$_tag_str}) or tag_v is null";
                } else {
                    $sql_tag = "SELECT rn.sell_record_code,rt.tag_v FROM	oms_sell_record_notice rn LEFT JOIN oms_sell_record_tag rt ON rn.sell_record_code = rt.sell_record_code AND rt.tag_type = 'order_tag' having  tag_v is null";
                }
                $tag_record_data = $this->db->get_all($sql_tag);
                if (!empty($tag_record_data)) {
                    $tag_record = array_column($tag_record_data, 'sell_record_code');
                    $tag_record_str = $this->arr_to_in_sql_value($tag_record, 'sell_record_code', $sql_values);
                    $sql_main .= " AND r1.sell_record_code  in ({$tag_record_str}) ";
                } else {
                    $sql_main .= "AND 1=2";
                }
            } else {
                $sql_tag = "select os.sell_record_code from oms_sell_record_notice os inner JOIN oms_sell_record_tag tag on os.sell_record_code=tag.sell_record_code where tag.tag_type='order_tag' and tag.tag_v in ({$_tag_str})";
                $tag_record_data = $this->db->get_all($sql_tag);
                if (!empty($tag_record_data)) {
                    $tag_record = array_column($tag_record_data, 'sell_record_code');
                    $tag_record_str = $this->arr_to_in_sql_value($tag_record, 'sell_record_code', $sql_values);
                    $sql_main .= " AND r1.sell_record_code  in ({$tag_record_str}) ";
                } else {
                    $sql_main .= " AND 1=2 ";
                }
            }
        }

        //订单性质
        if (isset($filter['record_nature']) && $filter['record_nature'] !== '') {
            $sell_record_attr_arr = explode(',', $filter['record_nature']);
            $sql_attr_arr = array();
            foreach ($sell_record_attr_arr as $attr) {
                if ($attr == 'is_presale') {
                    $sql_attr_arr[] = " r1.sale_mode = 'presale'";
                }
                if ($attr == 'is_fenxiao') {
                    $sql_attr_arr[] = " (r1.is_fenxiao = 1 OR r1.is_fenxiao = 2) ";
                }
                if ($attr == 'is_rush') {
                    $sql_attr_arr[] = " r1.is_rush = 1";
                }
                if ($attr == 'is_combine') {
                    $sql_attr_arr[] = " r1.is_combine = 1 ";
                }
                if ($attr == 'is_split') {
                    $sql_attr_arr[] = " r1.is_split = 1 ";
                }
                if ($attr == 'is_handwork') {
                    $sql_attr_arr[] = " r1.is_handwork = 1 ";
                }
                if ($attr == 'is_copy') {
                    $sql_attr_arr[] = " r1.is_copy = 1 ";
                }
                if ($attr == 'is_change_record') {
                    $sql_attr_arr[] = " r1.is_change_record = 1 ";
                }
                if ($attr == 'is_replenish') {
                    $order_type_sql=" select tl.sell_record_code from {$wave_table} tl inner join oms_sell_record t2 on tl.sell_record_code=t2.sell_record_code and t2.is_replenish='1'";
                    $record_type_data = $this->db->get_all($order_type_sql);
                    if (!empty($record_type_data)) {
                        $record_type_arr = array_column($record_type_data, 'sell_record_code');
                        $record_type_str = $this->arr_to_in_sql_value($record_type_arr, 'sell_record_code', $sql_values);
                        $sql_attr_arr[] = " r1.sell_record_code  in ({$record_type_str})";

                    }
                }
            }
            if(!empty($sql_attr_arr)){
                $sql_main .= ' AND (' . join(' OR ', $sql_attr_arr) . ')';
            }
        }

        //时间查询
        if (isset($filter['start_time']) && $filter['start_time'] != '') {
            switch ($filter['time_type']) {
                //下单时间
                case 'record_time':
                    $sql_main .= " AND r1.record_time >= :start_time ";
                    $record_time_start = strtotime(date("Y-m-d", strtotime($filter['start_time'])));
                    if ($record_time_start == strtotime($filter['start_time'])) {
                        $sql_values[':start_time'] = $filter['start_time'];
                    } else {
                        $sql_values[':start_time'] = $filter['start_time'];
                    }
                    break;
                //付款时间
                case 'pay_time':
                    $sql_main .= " AND r1.pay_time >= :start_time ";
                    $pay_time_start = strtotime(date("Y-m-d", strtotime($filter['start_time'])));
                    if ($pay_time_start == strtotime($filter['start_time'])) {
                        $sql_values[':start_time'] = $filter['start_time'];
                    } else {
                        $sql_values[':start_time'] = $filter['start_time'];
                    }
                    break;
                //通知配货时间
                case 'notice_time':
                    $sql_main .= " AND r1.is_notice_time >= :start_time ";
                    $is_notice_time_start = strtotime(date("Y-m-d", strtotime($filter['start_time'])));
                    if ($is_notice_time_start == strtotime($filter['start_time'])) {
                        $sql_values[':start_time'] = $filter['start_time'];
                    } else {
                        $sql_values[':start_time'] = $filter['start_time'];
                    }
                    break;
                //计划发货时间
                case 'plan_time':
                    $sql_main .= " AND r1.plan_send_time >= :start_time ";
                    $is_notice_time_start = strtotime(date("Y-m-d", strtotime($filter['start_time'])));
                    if ($is_notice_time_start == strtotime($filter['start_time'])) {
                        $sql_values[':start_time'] = $filter['start_time'];
                    } else {
                        $sql_values[':start_time'] = $filter['start_time'];
                    }
                    break;
            }
        }

        if (isset($filter['end_time']) && $filter['end_time'] != '') {
            switch ($filter['time_type']) {
                //下单时间
                case 'record_time':
                    $sql_main .= " AND r1.record_time <= :end_time ";
                    $record_time_end = strtotime(date("Y-m-d", strtotime($filter['end_time'])));
                    if ($record_time_end == strtotime($filter['end_time'])) {
                        $sql_values[':end_time'] = $filter['end_time'];
                    } else {
                        $sql_values[':end_time'] = $filter['end_time'];
                    }
                    break;
                //付款时间
                case 'pay_time':
                    $sql_main .= " AND r1.pay_time <= :end_time ";
                    $pay_time_end = strtotime(date("Y-m-d", strtotime($filter['end_time'])));
                    if ($pay_time_end == strtotime($filter['end_time'])) {
                        $sql_values[':end_time'] = $filter['end_time'];
                    } else {
                        $sql_values[':end_time'] = $filter['end_time'];
                    }
                    break;
                //通知配货时间
                case 'notice_time':
                    $sql_main .= " AND r1.is_notice_time <= :end_time ";
                    $is_notice_time_end = strtotime(date("Y-m-d", strtotime($filter['end_time'])));
                    if ($is_notice_time_end == strtotime($filter['end_time'])) {
                        $sql_values[':end_time'] = $filter['end_time'];
                    } else {
                        $sql_values[':end_time'] = $filter['end_time'];
                    }
                    break;
                //计划发货时间
                case 'plan_time':
                    $sql_main .= " AND r1.plan_send_time <= :end_time ";
                    $is_plan_time_end = strtotime(date("Y-m-d", strtotime($filter['end_time'])));
                    if ($is_plan_time_end == strtotime($filter['end_time'])) {
                        $sql_values[':end_time'] = $filter['end_time'];
                    } else {
                        $sql_values[':end_time'] = $filter['end_time'];
                    }
                    break;
            }
        }

        //子查询合并
        $group_by = false;
        if ($is_join === true) {
            $sql_main_select .= $sql_join . " ";
            $group_by = true;
        }

        if ($is_join_goods === true) {
            $sql_main_select .= " INNER JOIN base_goods r3 ON rr.goods_code = r3.goods_code ";
        }
        $sql_main = $sql_main_select . $sql_main;
        $select = 'r1.*';
        if ($group_by === true) {
            $sql_main .= " GROUP BY  r1.sell_record_code ";
        }
        if (isset($filter['is_sort']) && $filter['is_sort'] != '') {
            $sql_main .= "ORDER BY r1." . $filter['is_sort'] . " DESC ";
            $sql_main .= ",r1.sell_record_code DESC  ";
        }
         /*echo $sql_main;
          die;*/
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, $group_by);
        if (!empty($data['data'])) {
            //获取需要的订单号
            $sell_record_arr = array();
            $sql_val = array();
            $sell_tag = array();
            foreach ($data['data'] as $v) {
                $sell_record_arr[] = $v['sell_record_code'];
            }
            $sell_record_str = $this->arr_to_in_sql_value($sell_record_arr, 'sell_record_code', $sql_val);
            //获取所有有标签的订单
            $sql_tag = "SELECT t.sell_record_code,t.tag_desc from oms_sell_record_tag t 
             WHERE t.tag_type='order_tag' AND t.sell_record_code in ({$sell_record_str}) ";
            $tag_record_data = $this->db->get_all($sql_tag, $sql_val);
            foreach ($tag_record_data as $v) {
                $sell_tag[$v['sell_record_code']][] = $v['tag_desc'];
            }
            foreach ($sell_tag as $key => $value) {
                $sell_tag[$key] = implode(',', $value);
            }
        }
        $cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('safety_control'));
        foreach ($data['data'] as $key => &$value) {
            $value['paid_money'] = sprintf("%.2f", $value['paid_money']);
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
            $value['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $value['store_code']));
            $value['express_name'] = oms_tb_val('base_express', 'express_name', array('express_code' => $value['express_code']));
            if ($filter['ctl_type'] == 'view') {
//                $value['receiver_name'] = $this->name_hidden($value['receiver_name']);
//                $value['buyer_name'] = $this->name_hidden($value['buyer_name']);
//                $value['receiver_address'] = $this->address_hidden($value['receiver_address']);
                safe_data($value, 0);
            }
            $value['goods_weigh'] = $value['goods_weigh'];
            //订单标签值
            $value['sell_record_tag'] = $sell_tag[$value['sell_record_code']];
        }

        load_model('common/TBlLogModel')->set_log_multi($data['data'], 'search');
        //var_dump($data);die;
        return $this->format_ret(1, $data);
    }

    //淘宝物流跟踪
    function taobao_logistics_trace($sell_record_code) {
        $record = $this->get_record_by_code($sell_record_code);
        $params = array('shop_code' => $record['shop_code'], 'tid' => $record['deal_code_list']);
        $result = load_model('sys/EfastApiModel')->request_api('taobao_api/sync_logistics_trace', $params);
        return $result['resp_data'];
    }

    /**
     * @todo 获取物流信息-快递鸟
     */
    function logistic_trace($order_code) {
        $ret = load_model('api/kdniao/ApiKdPushModel', FALSE)->get_logistic_trace($order_code);
        return $ret;
    }

    function get_event_details($filter) {
        $type = $filter['sale_channel_code'];
        $deal_code_list = explode(',', $filter['deal_code_list']);

        $sql_value = array();
        $deal_code_str = $this->arr_to_in_sql_value($deal_code_list, 'tid', $sql_value);
        if ($type == 'taobao') {
            $sql_main = " FROM api_taobao_trade WHERE tid IN ({$deal_code_str})";
            $select = "promotion_details";

            $data = $this->get_page_from_sql($filter, $sql_main, $sql_value, $select);
            $arr = array();
            //将json数组转换成php数组
            foreach ($data['data'] as $key => $val) {
                $promotion_details = json_decode($val['promotion_details'], true);
                if (!empty($promotion_details)) {
                    $arr = array_merge($arr, $promotion_details['promotion_detail']);
                }
            }
            $data['data'] = $arr;
            $data["filter"]['record_count'] = count($arr);
        } else if ($type == 'jingdong') {
            $sql_main = " FROM api_jingdong_trade_coupon rl LEFT JOIN api_goods_sku gs ON rl.sku_id=gs.sku_id AND gs.source = '{$type}' WHERE rl.order_id IN ({$deal_code_str}) ";
            $select = "rl.coupon_price as discount_fee,rl.coupon_type as promotion_name,gs.goods_from_id";
            $data = $this->get_page_from_sql($filter, $sql_main, $sql_value, $select);
            foreach ($data['data'] as $key => &$val) {
                if (!empty($val['goods_from_id']) && $val['goods_from_id'] != NULL) {
                    $val['goods_name'] = oms_tb_val('api_goods', 'goods_name', array('goods_from_id' => $val['goods_from_id'], 'source' => $type));
                } else {
                    $val['goods_from_id'] = '';
                    $val['goods_name'] = '';
                }
            }
        }
        return $this->format_ret(1, $data);
    }

    function get_overtime_by_page($filter) {
        $date = strtotime("-3 days", strtotime(date('Y-m-d H:i:s')));
        $sql_join = "";
        $sql_main = " FROM oms_sell_record rl WHERE rl.sign_time = '0000-00-00 00:00:00' AND rl.embrace_time != 0 AND rl.embrace_time < {$date}";
        $sql_values = array();
        $select = ' rl.* ';
        //仓库和店铺
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code', $filter_store_code);

        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('rl.shop_code', $filter_shop_code);

        //配送方式
        if (isset($filter['express_code']) && $filter['express_code'] !== '') {
            $filter['express_code'] = deal_strs_with_quote($filter['express_code']);
            $sql_main .= " AND rl.express_code in ( " . $filter['express_code'] . " ) ";
        }

        //配送方式
//        if(isset($filter['express_code']) && $filter['express_code'] != '') {
//            $sql_main .= ' AND rl.express_code = :express_code ';
//            $sql_values[':express_code'] = $filter['express_code'];
//        }

        $sql_main .= ' ORDER BY rl.record_time DESC ';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        filter_fk_name($data['data'], array('shop_code|shop'));
        foreach ($data['data'] as $key => &$val) {
            $val['embrace_time'] = date('Y-m-d H:i:s', $val['embrace_time']);
            $val['express_name'] = oms_tb_val('base_express', 'express_name', array('express_code' => $val['express_code']));
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function communicate_log($data) {
        if (!empty($data['sell_record_code'])) {
            $sell_record_code = $data['sell_record_code'];
            $this->add_action($sell_record_code, "沟通日志", $data['communicate_log']);
            return $this->format_ret(1);
        } else {
            return $this->format_ret(-1, '', '订单号不存在');
        }
    }

    /*
     * 发货超时订单检索
     */

    function get_deliver_overtime_by_page($filter) {

        $sql = "FROM (SELECT rl.*,(unix_timestamp(date(now()))-unix_timestamp(date(rl.plan_send_time)))/86400 as days FROM oms_sell_record rl WHERE rl.order_status<>3 AND rl.order_status<>5 AND rl.shipping_status<>4 AND rl.plan_send_time < :current_time AND (rl.pay_status = 2 OR rl.pay_type = 'cod')"
                . " AND rl.plan_send_time<>'0000-00-00 00:00:00') AS t";
        $sql_values = array();
        $sql_values[':current_time'] = $filter['current_time'];
        $sql_main = ' WHERE 1';
        $select = "t.*";
        //仓库
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('t.store_code', $filter_store_code);
        //店铺
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('t.shop_code', $filter_shop_code);

        //超时天数
        if (isset($filter['days_start']) && $filter['days_start'] != '') {
            $sql_main .= " AND t.days >= :days_start";
            $sql_values[':days_start'] = $filter['days_start'];
        }
        if (isset($filter['days_end']) && $filter['days_end'] != '') {
            $sql_main .= " AND t.days <= :days_end";
            $sql_values[':days_end'] = $filter['days_end'];
        }
        $sql_main .= " ORDER BY t.plan_send_time";
        $sql_new = $sql . $sql_main;
        $data = $this->get_page_from_sql($filter, $sql_new, $sql_values, $select);
        filter_fk_name($data['data'], array('shop_code|shop'));
        foreach ($data['data'] as $key => &$value) {
            $value['days'] = round($value['days']);
            $value['status'] = $this->order_status[$value['order_status']];
            $value['status'] .= ' ' . $this->shipping_status[$value['shipping_status']];
            $value['status'] .= ' ' . $this->pay_status[$value['pay_status']];
        }

        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function deliver_overtime_count($filter) {

        $sql = " FROM (SELECT rl.*,(unix_timestamp(date(now()))-unix_timestamp(date(rl.plan_send_time)))/86400 as days FROM oms_sell_record rl WHERE rl.order_status<>3 AND rl.order_status<>5 AND rl.shipping_status<>4 AND rl.plan_send_time < :current_time AND (rl.pay_status = 2 OR rl.pay_type = 'cod')"
                . " AND rl.plan_send_time<>'0000-00-00 00:00:00') AS t";
        $sql_values = array();
        $sql_values[':current_time'] = $filter['current_time'];
        $sql_main = ' WHERE 1';
        $select = "SELECT t.sell_record_id";
        //仓库
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('t.store_code', $filter_store_code);
        //店铺
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('t.shop_code', $filter_shop_code);

        //超时天数
        if (isset($filter['days_start']) && $filter['days_start'] != '') {
            $sql_main .= " AND t.days >= :days_start";
            $sql_values[':days_start'] = $filter['days_start'];
        }
        if (isset($filter['days_end']) && $filter['days_end'] != '') {
            $sql_main .= " AND t.days <= :days_end";
            $sql_values[':days_end'] = $filter['days_end'];
        }
        $sql_main .= " ORDER BY t.plan_send_time";
        $sql_new = $select . $sql . $sql_main;
        $result = $this->db->get_all($sql_new, $sql_values);
        $count = count($result);
        return $this->format_ret(1, $count);
    }

    /**
     * 双十一图中确认的订单数
     * @param string $shop_code
     * @return array ['change_done' => '已确认的交易笔数', 'change_todo' => '未确认的交易笔数']
     */
    public function getConfirmOrderNum($shop_code = '') {
        $shop_sql = $shop_code == '0' ? '' : "AND `shop_code` = '{$shop_code}'";
        /** 已确认订单 */
        $sql1 = "SELECT COUNT(*) AS `confirm_done` "
                . "FROM `{$this->table}` WHERE `order_status` = '1' "
                . "AND `shipping_status` <> '4' {$shop_sql}";

        /** 未确认订单 */
        $sql2 = "SELECT COUNT(*) AS `confirm_todo` "
                . "FROM `{$this->table}` WHERE `order_status` = '0' "
                . "AND (`pay_type` = 'cod' OR `pay_status` = '2') {$shop_sql}";
        return array_merge($this->db->get_row($sql1), $this->db->get_row($sql2));
    }

    /**
     * 双十一图中拣货的订单数
     * @param string $shop_code
     * @return array ['pick_done' => '已拣货的交易笔数', 'pick_todo' => '未拣货的交易笔数']
     */
    public function getPickOrderNum($shop_code = '') {
        $shop_sql = $shop_code == '0' ? '' : "AND `shop_code` = '{$shop_code}'";
        /** 已拣货订单数 */
        $sql1 = "SELECT COUNT(*) AS `pick_done` "
                . "FROM `{$this->table}` WHERE `order_status` = '1' "
                . "AND `shipping_status` = '2' {$shop_sql}";

        /** 未拣货订单数 */
        $sql2 = "SELECT COUNT(*) AS `pick_todo` "
                . "FROM `{$this->table}` WHERE `order_status` = '1' "
                . "AND `shipping_status` = '1' {$shop_sql}";
        return array_merge($this->db->get_row($sql1), $this->db->get_row($sql2));
    }

    /**
     * 双十一图中发货的订单数
     * @param string $where_time
     * @param string $shop_code
     * @return array ['change_done' => '已发货的交易笔数', 'change_todo' => '已拣货未发货的交易笔数']
     */
    public function getDeliveryOrderNum($where_time = '', $shop_code = '') {
        $shop_sql = $shop_code == '0' ? '' : "AND `shop_code` = '{$shop_code}'";
        /** 已发货订单数 */
        $sql1 = "SELECT COUNT(*) AS `delivery_done` "
                . "FROM `{$this->table}` "
                . "WHERE `shipping_status` = '4' "
                . "AND `delivery_time` {$where_time} {$shop_sql}";

        /** 未验货订单数 */
        $sql2 = "SELECT COUNT(*) AS `delivery_todo` "
                . "FROM `{$this->table}` WHERE `order_status` = '1' "
                . "AND `shipping_status` = '3' {$shop_sql}";
        return array_merge($this->db->get_row($sql1), $this->db->get_row($sql2));
    }

    /**
     * 双十一图中回写的订单数
     * @param string $where_time
     * @param string $shop_code
     * @return type ['back_done' => '已发货回写的交易笔数', 'back_todo' => '未回写的交易笔数']
     */
    public function getBackOrderNum($where_time = '', $shop_code = '') {
        $shop_sql = $shop_code == '0' ? '' : "AND `shop_code` = '{$shop_code}'";

        /** 已发货回写订单数 */
        $sql1 = "SELECT COUNT(*) AS `back_done` "
                . "FROM `api_order_send` "
                . "WHERE `status` IN ('1','2') AND `upload_time` {$where_time} AND `send_time` {$where_time} {$shop_sql}";

        /** 未回写订单笔数 */
        $sql2 = "SELECT COUNT(*) AS `back_todo` "
                . "FROM `api_order_send` "
                . "WHERE `status` IN ('0', '-1', '-2') AND `send_time` {$where_time} {$shop_sql}";
        return array_merge($this->db->get_row($sql1), $this->db->get_row($sql2));
    }

    public function get_is_change_fail_num($shop_code = '') {
        $shop_sql = $shop_code == '0' ? '' : "AND `shop_code` = '{$shop_code}'";
        //获取当前时间
        $date = date('Y-m-d H:i:s', strtotime("-1 day"));
        $ret = array();
        //获取转单失败订单
        $sql = "SELECT count(*) FROM api_order WHERE is_change = -1  {$shop_sql}";
        $ret['fail_num'] = $this->db->get_value($sql);

        //获取审单超时订单
        $sql = "SELECT count(*) FROM oms_sell_record WHERE order_status = 0 AND must_occupy_inv = '1' AND lock_inv_status = '1' AND pay_status = 2 AND is_pending = 0 AND is_problem = 0 AND create_time < '{$date}'  {$shop_sql}";
        $ret['chec_timeout'] = $this->db->get_value($sql);
        //发货超时订单
        $sql = "SELECT count(*) FROM oms_sell_record WHERE order_status <> 3 AND order_status <> 5 AND shipping_status <> 4 AND plan_send_time < '2016-10-25 15:13:00' AND (pay_status = 2 OR pay_type = 'cod')	AND plan_send_time <> '0000-00-00 00:00:00' {$shop_sql}";
        $ret['overtime'] = $this->db->get_value($sql);

        //回写失败
        $sql = "SELECT count(*) FROM api_order_send WHERE `status` = -1 OR `status` = -2  {$shop_sql}";
        $ret['write_fail'] = $this->db->get_value($sql);

        //问题单
        $sql = "SELECT count(*) FROM oms_sell_record WHERE order_status !=3 AND is_problem=1  {$shop_sql}";
        $ret['problem'] = $this->db->get_value($sql);

        //缺货单
        $sql = "SELECT count(*) FROM oms_sell_record WHERE lock_inv_status in (2,3) and must_occupy_inv = 1  {$shop_sql}";
        $ret['out_store'] = $this->db->get_value($sql);

        //挂起单
        $sql = "SELECT count(*) FROM oms_sell_record rl  WHERE is_pending = 1 and order_status<>3  AND rl.sell_record_code
 in (select sell_record_code from oms_sell_record_detail rr where 1)  {$shop_sql}";
        $ret['pending'] = $this->db->get_value($sql);

        return $ret;
    }

    /**
     * @todo 过滤字符串中的特殊字符
     */
    private function html_decode($str) {
        $str = htmlspecialchars_decode($str);
        $replace = array(
            '&ndash;' => '–',
            '&mdash;' => '—',
            '&amp;' => '',
            '#' => '',
            '&ldquo' => '',
            '&rdquo' => '',
            '&lsquo' => '',
            '&rsquo' => ''
        );
        foreach ($replace as $key => $val) {
            $str = str_replace($key, $val, $str);
        }
        $str = str_replace(array('&'), '', $str);
        return $str;
    }

    /**
     * @todo 通过订单号数据查询订单明细，用于列表明细展开
     */
    function get_ex_list_cascade_data($sell_record_code_arr) {
        $sql_values = array();
        $sell_record_code_in_str = $this->arr_to_in_sql_value($sell_record_code_arr, 'sell_record_code', $sql_values);
        $detail_sql = "SELECT * FROM oms_sell_record_detail WHERE sell_record_code IN({$sell_record_code_in_str})";
        $detail_data = $this->db->get_all($detail_sql, $sql_values);
        foreach ($detail_data as $detail) {
            $key_arr = array('spec1_code', 'spec1_name', 'spec2_code', 'spec2_name', 'barcode', 'goods_name', 'goods_thumb_img');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($detail['sku'], $key_arr);
            $detail = array_merge($detail, $sku_info);
            if (!empty($detail['pic_path'])) {
                $html_arr = array();
                $html_arr[] = "<img width='50px' height='50px' src='{$detail['pic_path']}' />";
                $detail['pic_path'] = join('', $html_arr);
            } else {
                $html_arr = array();
                if (!empty($detail['goods_thumb_img'])) {
                    $html_arr[] = "<img width='50px' height='50px' src='{$sku_info['goods_thumb_img']}' />";
                } else {
                    $html_arr[] = "";
                }
                $detail['pic_path'] = join('', $html_arr);
            }
            if ($sub_data['is_gift'] > 0) {
                $sub_data['avg_money'] = 0;
            }
            $result[$detail['sell_record_code']][] = $detail;
        }
        return $result;
    }

    /**
     * @todo 通过退单号数据查询订单明细，用于列表明细一键展开
     */
    function get_return_list_cascade_data($sell_return_code_arr) {
        $sql_values = array();
        $sql_val = array();
        $result = array();
        $sell_return_code_in_str = $this->arr_to_in_sql_value($sell_return_code_arr, 'sell_return_code', $sql_values);
        $detail_sql = "SELECT * FROM oms_sell_return_detail WHERE sell_return_code IN({$sell_return_code_in_str})";
        $detail_data = $this->db->get_all($detail_sql, $sql_values);
        //原单商品数量
        $sell_record_code_arr = array_column($detail_data, 'sell_record_code');
        $str = $this->arr_to_in_sql_value($sell_record_code_arr, 'sell_record_code', $sql_val);
        $sql = "SELECT sell_record_code,sku,num FROM oms_sell_record_detail WHERE sell_record_code IN ({$str}) ";
        $data = $this->db->get_all($sql, $sql_val);
        foreach ($data as $value) {
            //原单商品数量
            $relation_num[$value['sell_record_code'] . ',' . $value['sku']] = $value['num'];
        }
        foreach ($detail_data as $detail) {
            $key_arr = array('spec1_code', 'spec1_name', 'spec2_code', 'spec2_name', 'barcode', 'goods_name', 'goods_thumb_img');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($detail['sku'], $key_arr);
            $detail = array_merge($detail, $sku_info);
            $detail['goods_price'] = sprintf('%.2f', $detail['goods_price']);
            $detail['avg_money'] = sprintf('%.2f', $detail['avg_money']);
            $detail['relation_num'] = empty($relation_num[$detail['sell_record_code'] . ',' . $detail['sku']]) ? 0 : $relation_num[$detail['sell_record_code'] . ',' . $detail['sku']];
            $result[$detail['sell_return_code']][] = $detail;
        }
        return $result;
    }

    function show_express($code) {
        if ($code == null) {
            return $this->format_ret(-1, '', '请输入快递单号');
        }
        $sql = "SELECT
	r1.sell_record_code,
	r2.express_code,
	r2.express_data,
	r1.order_status,
	r1.shipping_status,
	r1.pay_status,
	r1.shop_code,
	r1.shipping_status
        FROM
                {$this->table} r1
        INNER JOIN oms_deliver_record r2 ON r1.sell_record_code = r2.sell_record_code
        LEFT JOIN oms_deliver_record_package r3 ON r2.sell_record_code = r3.sell_record_code
        WHERE
                r2.express_no = :code
        OR r3.express_no = :code
        AND r2.is_cancel = 0;";
        $data = $this->db->get_row($sql, array(':code' => $code));
        if (empty($data)) {
            return $this->format_ret(-1, '', '未找到相应已发货订单');
        } else if ($data["shipping_status"] != 4) {
            return $this->format_ret(-1, '', '订单并未发货');
        }
        if (empty($data['express_data'])) {
            return $this->format_ret(-1, '', '快递号非云打印获取');
        }
        $express_data = json_decode($data['express_data']);
        if (empty($express_data->originCode) && empty($express_data->object_id) && empty($express_data->print_config)&&empty($express_data->shipping_branch_code)) {
            return $this->format_ret(-1, '', '快递号非云打印获取');
        }
        $order_status = $this->order_status[$data['order_status']];
        $shipping_status = $this->shipping_status[$data['shipping_status']];
        $pay_status = $this->pay_status[$data['pay_status']];
        $data['express_no'] = $code;
        $data['record_status'] = $order_status . ' ' . $shipping_status . ' ' . $pay_status;
        $data['express_name'] = oms_tb_val('base_express', 'express_name', array('express_code' => $data['express_code']));
        return $this->format_ret(1, $data, '');
    }

    function express_back($code, $c_code, $shop_code) {
        $sql_express = 'select count(1) from base_express where express_code=:c_code';
        $d = $this->db->get_value($sql_express, array(':c_code' => $c_code));
        if ($d == 0) {
            return $this->format_ret(-1, '', '快递公司在本系统未找到');
        }
        $sql_shop = 'select count(1) from base_shop where shop_code=:shop_code';
        $s = $this->db->get_value($sql_shop, array(':shop_code' => $shop_code));
        if ($s == 0) {
            return $this->format_ret(-1, '', '店铺信息在本系统未找到');
        }
        $client = new TaobaoClient($shop_code);
        $ret = $client->cloudWlbWaybillCancel($c_code, $code);
        if (isset($ret['cancel_result']) && $ret['cancel_result'] == true) {
            $result = $this->clear_express($code);
            return $result;
        } else {
            $msg = '';
            if (!empty($ret['error_response'])) {
                $msg = $ret['error_response']['msg'];
            }
            return $this->format_ret(-1, '', $msg);
        }
    }

    function clear_express($code) {
        $ret = $this->show_express($code);
        $data = $ret['data'];
        $sql = "select r2.package_no from oms_deliver_record r1 inner join oms_deliver_record_package r2 on r1.sell_record_code=r2.sell_record_code where r1.sell_record_code='{$data['sell_record_code']}' and r1.is_cancel=0";
        $data_1 = $this->db->get_all($sql);
        foreach ($data_1 as $value) {
            $code_data[] = $value['package_no'];
        }
        $sql_values = array();
        if (!empty($code_data)) {
            $list = $this->arr_to_in_sql_value($code_data, 'package_no', $sql_values);
            $this->db->query("delete from oms_deliver_record_package where sell_record_code='{$data['sell_record_code']}' AND package_no in ({$list}) AND express_no={$code}", $sql_values);
        }
        $this->db->query("update oms_deliver_record set express_no='',express_data='' where sell_record_code='{$data['sell_record_code']}' AND express_no={$code} ");
        $this->db->query("update oms_sell_record set express_no='',express_data='' where sell_record_code='{$data['sell_record_code']}' AND express_no={$code} ");
        $ret_1 = load_model('oms/SellRecordModel')->add_action($data['sell_record_code'], '快递单号回收', '快递单' . $code . '进行回收');

        return $this->format_ret(1, '', '回收成功');
    }

    //批量更改商品--核心功能方法
    function sure_change_goods($request) {
        $old_barcode = $request['old_barcode'];
        $new_barcode = $request['new_barcode'];
        $is_combo = $request['update_status'];
        //判断barcode是否存在
        $sql_old_barcode = "select sku from goods_barcode where barcode='{$old_barcode}'";
        $sku_old = CTX()->db->getOne($sql_old_barcode);
        $sql_new_barcode = "select sku from goods_barcode where barcode='{$new_barcode}'";
        $sku = CTX()->db->getOne($sql_new_barcode);
        if ($sku && $sku_old) {
            //该款成功的订单数量的定义
            $sure_oms_sell_recorde_count = 0;
            $mdl = new SellRecordOptModel();
            foreach ($request['sell_record_code_list'] as $k => $sell_record_code) {
                $sql = "select sell_record_detail_id,deal_code,num,avg_money,is_gift,combo_sku from oms_sell_record_detail where sell_record_code='{$sell_record_code}'  and sku='{$sku_old}'";
                $old_detail = ctx()->db->get_row($sql);
                $sell_record_info = array('sell_record_code' => $sell_record_code);
                if (empty($old_detail['sell_record_detail_id'])) {
                    $this->_message[] = array_merge($sell_record_info, array('msg' => "{$old_barcode}条码在订单中不存在"));
                    continue;
                }
                $sql = "select shipping_status from oms_sell_record where sell_record_code='{$sell_record_code}'";
                $shipping_status = CTX()->db->getOne($sql);
                if ($shipping_status == 1) {
                    $this->_message[] = array_merge($sell_record_info, array('msg' => "订单状态为已通知配货不能替换商品"));
                    continue;
                }
                if ($is_combo == 1 || ($is_combo == 0 && $old_detail['combo_sku'] == '')) {
                    //删除当前明细
                    $response1 = $mdl->opt_delete_detail($sell_record_code, $old_detail['sell_record_detail_id']);
                    //添加明细
                    $detail = array();
                    $detail['sell_record_code'] = $sell_record_code;
                    $detail['deal_code'] = $old_detail['deal_code'];
                    $detail['data'][0] = load_model('prm/GoodsModel')->get_sku_list($sku);
                    $detail['data'][0]['num'] = $old_detail['num'];
                    $detail['data']['barcode'] = $old_detail['barcode'];
                    $detail['data'][0]['sum_money'] = $old_detail['avg_money'];
                    if (isset($old_detail['is_gift']) && $old_detail['is_gift'] == '1') {
                        $old_detail['data'][0]['is_gift'] = $old_detail['is_gift'];
                    }
                    $response = $mdl->opt_new_multi_detail($detail);
                    load_model('oms/SellRecordOptModel')->set_tb_log($detail['sell_record_code']);
                    $sure_oms_sell_recorde_count++;
                } else {
                    $this->_message[] = array_merge($sell_record_info, array('msg' => "套餐中包含子条码{$old_barcode}"));
                }
            }
            $fail_oms_sell_record_count = count($request['sell_record_code_list']) - $sure_oms_sell_recorde_count;
            if ($fail_oms_sell_record_count > 0) {
                $msg = $this->create_fail_file($this->_message);
                $message = "改款成功{$sure_oms_sell_recorde_count}单，失败{$fail_oms_sell_record_count}单!{$msg}";
            } else {
                $message = "改款成功{$sure_oms_sell_recorde_count}单";
            }

            $response = array('status' => 1, 'message' => $message);
        } else if (empty($sku_old) && $sku) {
            $response = array('status' => -1, 'message' => '更换的商品在系统中不存在');
        } else if (empty($sku) && $sku_old) {
            $response = array('status' => -2, 'message' => '改款后的商品在系统中不存在');
        } else {
            $response = array('status' => -3, 'message' => '更换和改款后的商品在系统中不存在');
        }
        return $response;
    }

    //批量删除商品
    function alter_detete_detail($request) {
        $app['fmt'] = 'json';
        $sure_oms_sell_recorde_count = 0;
        $barcode_sql = "select sku from goods_barcode where barcode='{$request['old_barcode']}'";
        $sku = CTX()->db->getOne($barcode_sql);
        if (empty($sku)) {
            $response = array('status' => -1, 'message' => '商品条形码在系统中不存在');
            return $response;
        }
        foreach ($request['sell_record_code_list'] as $k => $sell_record_code) {
            $sell_record_info = array('sell_record_code' => $sell_record_code);
            $sql = "select sell_record_detail_id from oms_sell_record_detail where sell_record_code='{$sell_record_code}'  and sku='{$sku}'";
            $sell_record_detail_id = CTX()->db->getOne($sql);
            $sql = "select shipping_status from oms_sell_record where sell_record_code='{$sell_record_code}'";
            $shipping_status = CTX()->db->getOne($sql);
            if ($shipping_status == 1) {
                $this->_message[] = array_merge($sell_record_info, array('msg' => "订单状态为已通知配货不能删除商品"));
                continue;
            } else if (empty($sell_record_detail_id)) {
                $this->_message[] = array_merge($sell_record_info, array('msg' => "{$request['old_barcode']}条码在订单中不存在"));
                continue;
            } else {
                $mdl = new SellRecordOptModel();
                $action_bath = "批量";
                $response = $mdl->opt_delete_detail($sell_record_code, $sell_record_detail_id, $action_bath);
                load_model('oms/SellRecordOptModel')->set_tb_log($sell_record_code);
                $sure_oms_sell_recorde_count++;
            }
        }
        $fail_oms_sell_record_count = count($request['sell_record_code_list']) - $sure_oms_sell_recorde_count;
        if ($fail_oms_sell_record_count > 0) {
            $msg = $this->create_fail_file_delete($this->_message);
            $message = "删除成功{$sure_oms_sell_recorde_count}单，失败{$fail_oms_sell_record_count}单!{$msg}";
        } else {
            $message = "删除成功{$sure_oms_sell_recorde_count}单";
        }

        $response = array('status' => 1, 'message' => $message);
        return $response;
    }

    //批量新增商品
    function alter_add_detail($request) {
        //add_status 为0 订单已存在不添加商品
        $action_bath = "批量";
        $barcode = $request['new_barcode'];
        $num = $request['num'];
        $avg_money = $request['avg_money'];
        $sure_oms_sell_recorde_count = 0;
        $barcode_sql = "select sku from goods_barcode where barcode='{$barcode}'";
        $sku = CTX()->db->getOne($barcode_sql);
        if (empty($sku)) {
            $response = array('status' => -1, 'message' => '商品条形码在系统中不存在');
            return $response;
        }
        if ($request['add_status']) {
            foreach ($request['sell_record_code_list'] as $k => $sell_record_code) {
                $sell_record_info = array('sell_record_code' => $sell_record_code);
                $sql = "select shipping_status from oms_sell_record where sell_record_code='{$sell_record_code}'";
                $shipping_status = CTX()->db->getOne($sql);
                if ($shipping_status == 1) {
                    $this->_message[] = array_merge($sell_record_info, array('msg' => "订单状态为已通知配货不能添加商品"));
                    continue;
                } else {
                    $sql = "select sell_record_detail_id from oms_sell_record_detail where sell_record_code='{$sell_record_code}' and sku='{$sku}'";
                    $sell_record_detail_id = CTX()->db->getOne($sql);
                    $sql = "select deal_code from oms_sell_record_detail  where sell_record_code='{$sell_record_code}'";
                    $deal_code = $this->db->get_value($sql, array(':sell_record_code' => $sell_record_code));
                    if (empty($deal_code)) {
                        $sql = "select deal_code from oms_sell_record where sell_record_code='{$sell_record_code}'";
                        $deal_code = $this->db->get_value($sql, array(':sell_record_code' => $sell_record_code));
                    }
                    if (empty($sell_record_detail_id)) {
                        $parma = array();
                        $parma['sell_record_code'] = $sell_record_code;
                        $parma['deal_code'] = $deal_code;
                        $parma['data'][0] = load_model('prm/GoodsModel')->get_sku_list($sku);
                        $parma['data'][0]['sum_money'] = $avg_money;
                        $parma['data'][0]['num'] = $num;
                        $parma['data']['barcode'] = $barcode;
                        $parma['action_bath'] = "批量";
                        $mdl = new SellRecordOptModel();
                        $response = $mdl->opt_new_multi_detail($parma, 0, 1);
                        load_model('oms/SellRecordOptModel')->set_tb_log($sell_record_code);
                        $sure_oms_sell_recorde_count++;
                    } else {
                        $sql = "select num,avg_money from oms_sell_record_detail where sell_record_code='{$sell_record_code}' and sku='{$sku}'";
                        $goods = CTX()->db->getRow($sql);
                        $parma = array();
                        $parma['sell_record_code'] = $sell_record_code;
                        $parma['deal_code'] = $deal_code;
                        $parma['add_sum_money'] = $avg_money;
                        $parma['add_num'] = $num;
                        $parma['data'][0] = load_model('prm/GoodsModel')->get_sku_list($sku);
                        $parma['data'][0]['sum_money'] = $avg_money + $goods['avg_money'];
                        $parma['data'][0]['num'] = $num + $goods['num'];
                        $parma['data']['barcode'] = $barcode;
                        $mdl = new SellRecordOptModel();
                        $response = $mdl->opt_save_detail($sell_record_code, $sell_record_detail_id, $parma['data'][0]['num'], $parma['data'][0]['sum_money'], $parma['deal_code'], 0, $num, $avg_money, $action_bath);
                        load_model('oms/SellRecordOptModel')->set_tb_log($sell_record_code);
                        $sure_oms_sell_recorde_count++;
                    }
                }
            }
            $fail_oms_sell_record_count = count($request['sell_record_code_list']) - $sure_oms_sell_recorde_count;
            if ($fail_oms_sell_record_count > 0) {
                $msg = $this->create_fail_file_add($this->_message);
                $message = "添加成功{$sure_oms_sell_recorde_count}单，失败{$fail_oms_sell_record_count}单!{$msg}";
            } else {
                $message = "添加成功{$sure_oms_sell_recorde_count}单";
            }

            $response = array('status' => 1, 'message' => $message);
        } else {
            foreach ($request['sell_record_code_list'] as $k => $sell_record_code) {
                $sql = "select sell_record_detail_id from oms_sell_record_detail where sell_record_code='{$sell_record_code}' and sku='{$sku}'";
                $sell_record_detail_id = CTX()->db->getOne($sql);
                $sql = "select deal_code from oms_sell_record_detail where sell_record_code='{$sell_record_code}'";
                $deal_code = $this->db->get_value($sql, array(':sell_record_code' => $sell_record_code));
                if (empty($deal_code)) {
                    $sql = "select deal_code from oms_sell_record where sell_record_code='{$sell_record_code}'";
                    $deal_code = $this->db->get_value($sql, array(':sell_record_code' => $sell_record_code));
                }
                $sell_record_info = array('sell_record_code' => $sell_record_code);
                $sql = "select shipping_status from oms_sell_record where sell_record_code='{$sell_record_code}'";
                $shipping_status = CTX()->db->getOne($sql);
                if ($shipping_status == 1) {
                    $this->_message[] = array_merge($sell_record_info, array('msg' => "订单状态为已通知配货不能添加商品"));
                    continue;
                } else if (empty($sell_record_detail_id)) {
                    $parma = array();
                    $parma['sell_record_code'] = $sell_record_code;
                    $parma['deal_code'] = $deal_code;
                    $parma['data'][0] = load_model('prm/GoodsModel')->get_sku_list($sku);
                    $parma['data'][0]['sum_money'] = $avg_money;
                    $parma['data'][0]['num'] = $num;
                    $parma['data']['barcode'] = $barcode;
                    $parma['action_bath'] = "批量";
                    $mdl = new SellRecordOptModel();
                    $response = $mdl->opt_new_multi_detail($parma, 0, 1);
                    load_model('oms/SellRecordOptModel')->set_tb_log($sell_record_code);
                    $sure_oms_sell_recorde_count++;
                } else {
                    $this->_message[] = array_merge($sell_record_info, array('msg' => "订单中已存在此商品不进行添加"));
                    continue;
                }
            }
        }
        $fail_oms_sell_record_count = count($request['sell_record_code_list']) - $sure_oms_sell_recorde_count;
        if ($fail_oms_sell_record_count > 0) {
            $msg = $this->create_fail_file_add($this->_message);
            $message = "添加成功{$sure_oms_sell_recorde_count}单，失败{$fail_oms_sell_record_count}单!{$msg}";
        } else {
            $message = "添加成功{$sure_oms_sell_recorde_count}单";
        }
        $response = array('status' => 1, 'message' => $message);
        return $response;
    }

    /**
     * 创建导出信息csv文件
     * @param array $msg 信息数组
     * @return string 文件地址
     */
    private function create_fail_file($msg) {
        $fail_top = array('订单号', '替换商品失败信息');
        require_lib('csv_util');
        $csv_obj = new execl_csv();
        $file_name = $csv_obj->create_fail_csv_files($fail_top, $msg, 'create_sell_recored_detail');
//        $message = "，商品替换失败信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
        $url = set_download_csv_url($file_name, array('export_name' => 'error'));
        $message = "，商品替换失败信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        return $message;
    }

    private function create_fail_file_delete($msg) {
        $fail_top = array('订单号', '删除商品失败信息');
        require_lib('csv_util');
        $csv_obj = new execl_csv();
        $file_name = $csv_obj->create_fail_csv_files($fail_top, $msg, 'delete_sell_recored_detail');
//        $message = "，商品删除失败信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
        $url = set_download_csv_url($file_name, array('export_name' => 'error'));
        $message = "，商品删除失败信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        return $message;
    }

    private function create_fail_file_add($msg) {
        $fail_top = array('订单号', '添加商品失败信息');
        require_lib('csv_util');
        $csv_obj = new execl_csv();
        $file_name = $csv_obj->create_fail_csv_files($fail_top, $msg, 'add_sell_recored_detail');
//        $message = "，添加商品失败信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
        $url = set_download_csv_url($file_name, array('export_name' => 'error'));
        $message .= "，添加商品失败信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        return $message;
    }

    function fx_import_trade_action($csv_path) {
        require_model('util/CsvImport');
        $import_obj = new CsvImport();
//    $ret = $import_obj->get_upload();
//            if ($ret['status'] < 0) {
//                return $ret;
//            }
//
//            $file_name = $ret['data'];
        //$file_name = 'import_sell_record_551d02b9ebd97.csv';

        list($path, $file_name) = explode("uploads/", $csv_path);
        $import_obj->is_iconv = 0;
        $ret = $import_obj->get_csv_data($file_name);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $data = $ret['data']['data'];
        $sql = "select shop_code,sale_channel_code,send_store_code from base_shop";
        $db_channel = ctx()->db->get_all($sql);
        $sale_channel_arr = array();
        $send_store_arr = array();
        foreach ($db_channel as $sub_channel) {
            $sale_channel_arr[$sub_channel['shop_code']] = $sub_channel['sale_channel_code'];
            $send_store_arr[$sub_channel['shop_code']] = $sub_channel['send_store_code'];
        }
        $login_type = CTX()->get_session('login_type');
        if ($login_type == 2) {
            $fld_map = array(
                '下单日期*' => 'order_first_insert_time',
                '店铺代码*' => 'shop_code',
                '交易号*' => 'tid',
                '分销商代码*' => 'fenxiao_code',
                '会员昵称*' => 'buyer_nick',
                '收货人*' => 'receiver_name',
                '手机号*' => 'receiver_mobile',
                '固定电话' => 'receiver_phone',
                '收货地址*' => 'receiver_address',
                '邮编' => 'receiver_zip_code',
                '付款日期' => 'pay_time',
                '仓库代码' => 'store_code',
                '配送方式代码' => 'express_code',
                '运费' => 'express_money',
                '买家留言' => 'buyer_remark',
                '商家留言' => 'seller_remark',
                '商品条形码*' => 'goods_barcode',
                '均摊金额' => 'avg_money',
                '数量*' => 'num',
            );
        } else {
            $fld_map = array(
                '下单日期*' => 'order_first_insert_time',
                '店铺代码*' => 'shop_code',
                '交易号*' => 'tid',
                '分销商代码*' => 'fenxiao_code',
                '会员昵称*' => 'buyer_nick',
                '收货人*' => 'receiver_name',
                '手机号*' => 'receiver_mobile',
                '固定电话' => 'receiver_phone',
                '收货地址*' => 'receiver_address',
                '邮编' => 'receiver_zip_code',
                '付款日期' => 'pay_time',
                '仓库代码' => 'store_code',
                '配送方式代码' => 'express_code',
                '运费*' => 'express_money',
                '结算运费（分销商）' => 'fx_express_money',
                '买家留言' => 'buyer_remark',
                '商家留言' => 'seller_remark',
                '商品条形码*' => 'goods_barcode',
                '均摊金额' => 'avg_money',
                '数量*' => 'num',
                '结算单价（分销应收）*' => 'trade_price',
            );
        }

        $record_fld = 'order_first_insert_time,shop_code,tid,buyer_nick,receiver_name,receiver_mobile,receiver_phone,receiver_address,receiver_zip_code,pay_time,store_code,express_code,express_money,buyer_remark,seller_remark,pay_type,pay_code,receiver_province,receiver_city,receiver_district,receiver_street,receiver_addr,status,source,receiver_country,fenxiao_code,fx_express_money,is_fenxiao,fenxiao_name,fenxiao_id';
        $record_fld .= ',receiver_email,express_no,sku_num,goods_num,goods_weigh,seller_flag,delivery_money,alipay_no,invoice_type,invoice_title,invoice_content,invoice_money';
        $record_mx_fld = 'tid,goods_barcode,price,num,avg_money';
        $record_mx_fld .= ',oid,sku_id,trade_price,fx_amount';

        $util_obj = load_model('util/ViewUtilModel');

        $err_arr = array();
        $success_arr = array();
        $err_arr2 = array();
        $err_arr3 = array();
        $err_arr4 = array();
        $barcode_fx_arr = array();
        //分销商登录，查询当前登陆分销商编号
        if ($login_type == 2) {
            //获取增值服务
            $service_pay_time = load_model('common/ServiceModel')->check_is_auth_by_value('fx_import');
            $user_code = CTX()->get_session('user_code');
            $custom = load_model('base/CustomModel')->get_custom_by_user_code($user_code, 'custom_code,custom_name,settlement_method,fixed_money');
        }
        foreach ($data as $data_row) {
            foreach ($data_row as $key => $v) {
                $v = preg_replace('/\xC2\xA0/is', "", $v);
                $data_row[$key] = trim($v);
            }
            $_row = array();
            foreach ($fld_map as $k => $v) {
                $_row[$v] = $data_row[$k];
            }
            /* if($custom['settlement_method'] == 0 && $login_type == 2) { //分销商登录，运费取档案
              $_row['fx_express_money'] = $custom['fixed_money'];
              $sku = load_model('prm/GoodsBarcodeModel')->is_exists($_row['goods_barcode'], 'barcode');
              $goods_data = array('goods_code' => $sku['data']['goods_code'], 'sku' => $sku['data']['sku']);
              $_row['trade_price'] = load_model('fx/GoodsManageModel')->compute_fx_price($custom['custom_code'], $goods_data, $_row['order_first_insert_time']);
              } */

            if (empty($_row['tid'])) {

                return '交易号不能为空！<br>';
                //continue;
            }
            if ($login_type == 2 && $custom['custom_code'] != $_row['fenxiao_code']) {
                $err_arr2[$_row['tid']] = ' 导入的分销商跟当前登录的分销商不一致';
            }
            //判断是普通分销还是淘分销订单(分销商和店铺有关联为普通分销，没有就是淘宝分销)
            $shop = load_model('base/ShopModel')->get_by_code($_row['shop_code']);
            $shop_data = $shop['data'];
            if (empty($shop_data)) {
                $err_arr2[$_row['tid']] = ' 店铺代码不存在';
            }
            if ($shop_data['is_active'] == 0) {
                $err_arr2[$_row['tid']] = ' 店铺已停用';
            }

            $custom_data = load_model('base/CustomModel')->get_by_code($_row['fenxiao_code']);
            if (empty($custom_data['data'])) {
                $err_arr2[$_row['tid']] = ' 分销商不存在';
            }
            if ($custom_data['data']['is_effective'] == 0) {
                $err_arr2[$_row['tid']] = ' 分销商已停用';
            }
            if (!empty($shop_data['custom_code']) && $shop_data['entity_type'] == 2) { //判断是否普通分销
                if ($shop_data['custom_code'] == $_row['fenxiao_code']) {
                    $_row['is_fenxiao'] = 2; //普通分销
                } else {
                    $err_arr2[$_row['tid']] = ' 分销商和店铺不匹配';
                }
            } else if ((($shop_data['fenxiao_status'] == 1 && $shop_data['sale_channel_code'] == 'taobao') || $shop_data['sale_channel_code'] == 'fenxiao') && $login_typ != 2) { //判断是否淘宝分销订单,分销商登录不能导入淘分销订单
                if ($custom_data['data']['custom_type'] == 'tb_fx') {
                    $_row['is_fenxiao'] = 1; //淘宝分销
                } else {
                    $err_arr2[$_row['tid']] = ' 分销商不是淘宝分销商';
                }
            } else {
                $err_arr2[$_row['tid']] = ' 该订单不是分销订单';
            }
            $_row['fenxiao_name'] = $custom_data['data']['custom_name'];
            $_row['fenxiao_id'] = $custom_data['data']['custom_id'];

            $_row['tid'] = trim($_row['tid']);

//            $_row['avg_money'] = $_row['price'] * $_row['num'];
            $_row['price'] = $_row['avg_money'] / $_row['num'];
            $_row['pay_type'] = $_row['is_cod'] == '是' ? '1' : '0';
            $_row['pay_code'] = $_row['pay_type'] == '1' ? 'cod' : 'bank';
            $_row['fx_amount'] = !empty($_row['trade_price']) || $_row['trade_price'] == 0 ? $_row['trade_price'] * $_row['num'] : ''; //价格为零导入，为空计算系统价格
            $_row['trade_price'] = !empty($_row['trade_price']) || $_row['trade_price'] == 0 ? $_row['trade_price'] : '';

            if ($service_pay_time == true && $login_type == 2) { //分销商登录，有增值服务，下单时间为当前时间,付款时间取当前时间
                $_row['order_first_insert_time'] = date('Y-m-d H:i:s');
                $_row['pay_time'] = date('Y-m-d H:i:s');
            }
            if (!empty($_row['order_first_insert_time'])) {
                $_row['order_first_insert_time'] = date('Y-m-d H:i:s', strtotime($_row['order_first_insert_time']));
            }
            if (!empty($_row['pay_time'])) {
                $_row['pay_time'] = date('Y-m-d H:i:s', strtotime($_row['pay_time']));
                if (!$_row['pay_time']) {
                    return '请正确填写付款时间！<br>';
                }
            }

            $_row['receiver_address'] = str_replace('  ', '', $_row['receiver_address']);
            $_addr = explode(' ', $_row['receiver_address']);
            //验证直辖市
            $addr_str = $this->check_addr($_addr);
            $_row['receiver_address'] = $addr_str;

            if (trim($_addr[0]) != '中国') {
                $_row['receiver_address'] = '中国 ' . $_row['receiver_address'];
                $_addr = explode(' ', $_row['receiver_address']);
            }

            $_row['receiver_province'] = $_addr[1];
            $_row['receiver_city'] = $_addr[2];
            $_row['receiver_district'] = $_addr[3];
            $_row['receiver_country'] = '中国';

            $_addr_str = "{$_addr[0]} {$_addr[1]} {$_addr[2]} {$_addr[3]}";
            $_addr_num = count($_addr);
            $receiver_addr = '';
            if ($_addr_num == 5) {
                $receiver_addr = $_addr[4];
            } else if ($_addr_num == 6) {
                $sql = "select id from base_area where name=:name AND type=5";
                $area_id = $this->db->get_value($sql, array(':name' => $_addr[4]));
                if (!empty($area_id)) {
                    $_row['receiver_street'] = $_addr[4];
                    $receiver_addr = $_addr[5];
                } else {
                    $receiver_addr = $_addr[4] . $_addr[5];
                }
            } else if ($_addr_num > 6) {
                $_row['receiver_street'] = $_addr[4];
                $i = 5;
                while ($i < $_addr_num) {
                    $receiver_addr .= $_addr[$i];
                    $i++;
                }
            }

            $_row['receiver_addr'] = !empty($receiver_addr) ? $receiver_addr : str_replace($_addr_str, '', $_row['receiver_address']);


            $_row['source'] = isset($sale_channel_arr[$_row['shop_code']]) ? $sale_channel_arr[$_row['shop_code']] : null;
            if (empty($_row['source'])) {
                // $err_arr[] = $_row['tid'] . ' 找不到订单来源';
                $err_arr2[$_row['tid']] = ' 找不到订单来源';
            }

            if (empty($_row['store_code'])) {
                if (isset($send_store_arr[$_row['shop_code']])) {
                    $_row['store_code'] = $send_store_arr[$_row['shop_code']];
                } else {
                    $err_arr2[$_row['tid']] = '找不到指定发货仓库';
                }
            } else {
                if ($this->check_store($_row['store_code']) === false) {
                    $err_arr2[$_row['tid']] = '找不到指定仓库';
                }
            }
            if (!isset($_addr[0]) || !isset($_addr[1]) || empty($_addr[0]) || empty($_addr[1])) {
                //   $err_arr[] = $_row['tid'] . ' 收货地址省、市不能为空';
                $err_arr3[$_row['tid']] = ' 收货地址省、市不能为空';
            }
            if (isset($trade_data[$_row['tid']]) && $trade_data[$_row['tid']]['record'][0]['is_fenxiao'] != $_row['is_fenxiao']) {
                $err_arr3[$_row['tid']] = ' 存在重复交易号，不同类型的分销订单';
            }
            $trade_data[$_row['tid']]['record'][] = $util_obj->copy_arr_by_fld($_row, $record_fld, 0, 1);
            $trade_data[$_row['tid']]['mx'][] = $util_obj->copy_arr_by_fld($_row, $record_mx_fld, 0, 1);
            $key = $_row['tid'] . "," . $_row['goods_barcode'];
            $barcode_fx_arr[$key] = array('custom_code' => $_row['fenxiao_code'], 'barcode' => $_row['goods_barcode'], 'is_fenxiao' => $_row['is_fenxiao']);
            //break;
        }

        //判断商品是否分销款,是否与分销商匹配
        $goods_model = load_model('fx/GoodsModel');
        /* $barcode_arr = array_column($barcode_fx_arr,'barcode');
          $barcode_arr = array_unique($barcode_arr);
          //开启分销款的商品
          $sku_arr = $goods_model->get_by_fx_goods_sku('barcode',$barcode_arr);
          $fx_barcode_arr = array_column($sku_arr,'barcode');
          //开启分销款没指定分销商的商品
          $fx_barcode_no_custom = $goods_model->fx_goods_no_custom($barcode_arr,'r2.barcode');
          $fx_barcode_no_custom = array_column($fx_barcode_no_custom,'barcode');

          $custom_code_arr = array_column($barcode_fx_arr,'custom_code');
          $custom_code_arr = array_unique($custom_code_arr);
          //指定分销商的商品
          $custom_arr = $goods_model->get_custom_goods_sku('rl.custom_code,r2.barcode',$custom_code_arr); */
        foreach ($barcode_fx_arr as $key => $val) {
            if ($val['is_fenxiao'] == 2) {
                $key_arr = explode(",", $key);
                $tid = $key_arr[0];
                //开启分销款的商品
                $barcode_arr = array($val['barcode']);
                $sku_arr = $goods_model->get_by_fx_goods_sku('barcode', $barcode_arr);
                if (empty($sku_arr)) {
                    $err_arr3[$tid] = ' ' . $val['barcode'] . '条码，商品不是分销款商品';
                    continue;
                }
                $fx_barcode_arr = array_column($sku_arr, 'barcode');
                //开启分销款没指定分销商的商品
                $fx_barcode_no_custom = $goods_model->fx_goods_no_custom($barcode_arr, 'r2.barcode');
                $fx_barcode_no_custom = array_column($fx_barcode_no_custom, 'barcode');
                //指定分销商的商品
                $custom_arr = $goods_model->get_custom_goods_sku('rl.custom_code,r2.barcode', array($val['custom_code']));

                $is_fx_goods = in_array($val['barcode'], $fx_barcode_no_custom) ? true : false; //是否分销款没指定分销商的商品
                $is_fx_gustom_goods = isset($custom_arr[$val['custom_code']]) && in_array($val['barcode'], $custom_arr[$val['custom_code']]) ? true : false; //是否指定分销商的商品
                if ($is_fx_goods == false && $is_fx_gustom_goods == false) {
                    $err_arr3[$tid] = ' ' . $val['barcode'] . '，商品与分销商不匹配';
                }
            }
        }


        //echo '<hr/>$trade_data<xmp>'.var_export($trade_data,true).'</xmp>';
        $api_data = array();
        foreach ($trade_data as $tid => $sub_tid) {
            $pre_v = '';
            $record_err_tag = 0;
            foreach ($sub_tid['record'] as $record_row) {
                $cur_v = join(',', $record_row);
                if ($pre_v != '' && $pre_v != $cur_v) {
                    $record_err_tag = 1;
                    break;
                }
                $pre_v = $cur_v;
            }
            if ($record_err_tag == 1) {
                //  $err_arr[] = $tid . '订单信息不匹配';
                $err_arr4[$tid] = ' 订单信息不匹配';
            }
            $api_data[$tid] = $sub_tid['record'][0];
            //验证分销商信息
            $this->check_custom($sub_tid['record'][0], $err_arr4);
            $_order_money = 0;
            $dit = array();
            foreach ($sub_tid['mx'] as $kk => $_row) {
                if ($_row['trade_price'] == '' && $sub_tid['is_fenxiao'] == 1 && $login_type != 2) {
                    $err_arr4[$tid] = "结算单价不能为空。";
                }
                $dit[$kk]['tid'] = $_row['tid'];
                $dit[$kk]['goods_barcode'] = $_row['goods_barcode'];
                $_order_money = bcadd($_order_money, $_row['avg_money'], 2);
                $_row['tid'] = $tid;
                $_barcode = "{$_row['goods_barcode']}";
                $_row['sku_properties'] = '';
                $api_data[$tid]['mx'][$_barcode] = $_row;
            }
            $unique_arr = array_unique($dit, SORT_REGULAR);
            $repeat_arr = array_diff_assoc($dit, $unique_arr);
            if (!empty($repeat_arr)) {
                $err_arr4[$tid] = ' 系统检测到同一笔交易中存在多条相同商品条形码记录，请合并数量后再导入！';
            }
            $_order_money += $api_data[$tid]['express_money'];
            $api_data[$tid]['order_money'] = $_order_money;
        }
        //print_r($_row['tid']);
        //echo '<hr/>$api_data<xmp>'.var_export($api_data,true).'</xmp>';die;
        $obj = load_model('oms/TranslateOrderModel');
        $obj->import_flag = 1;
        foreach ($api_data as $tid => $sub_api_data) {
            if (isset($err_arr2[$tid]) && !empty($err_arr2[$tid])) {
                $err_arr[] = $tid . $err_arr2[$tid];
                continue;
            } elseif (isset($err_arr3[$tid]) && !empty($err_arr3[$tid])) {
                $err_arr[] = $tid . $err_arr3[$tid];
                continue;
            } elseif (isset($err_arr4[$tid]) && !empty($err_arr4[$tid])) {
                $err_arr[] = $tid . $err_arr4[$tid];
                continue;
            }
            $ret = $obj->translate_order_by_data($sub_api_data, 'import_fx');
            if ($ret['status'] < 0) {
                $err_arr[] = $tid . $ret['message'];
            } else {
                $sql = "update oms_sell_record set is_handwork='1' where deal_code='" . $tid . "'";
                ctx()->db->query($sql);
                $success_arr[] = $tid . '导入成功';
            }
        }
        $ret_msg = '';
        if (!empty($err_arr)) {
            $ret_msg .= "<div style='color:red'>导入失败的订单：<br/>" . join('<br/>', $err_arr) . "</div>";
        }
        if (!empty($success_arr)) {
            $ret_msg .= "<hr/><div>导入成功的订单：<br/>" . join('<br/>', $success_arr) . "</div>";
        }
        if (empty($ret_msg)) {
            $ret_msg = "<div style='color:red'>导入发生错误。</div>";
        }
        return $ret_msg;
    }

    function check_custom($record, &$err_arr4) {
        if ($record['shop_code'] == '') {
            $err_arr4[$record['tid']] = "店铺代码不能为空。";
        } else if ($record['fenxiao_code'] == '') {
            $err_arr4[$record['tid']] = "分销商代码不能为空。";
        }
    }

    public function check_sellrecord_status($sell_record_code) {
        $sql = "select order_status from oms_sell_record where sell_record_code = :sell_record_code";
        $order_status = $this->db->get_value($sql, array(':sell_record_code' => $sell_record_code));
        return $order_status;
    }

    //查询以修改的分销单据
    function fx_diff_record_data($filter) {
        //查询未作废的分销订单
        $sql = "SELECT r1.sell_record_code,r2.goods_code,r2.sku,r2.trade_price,r2.fx_amount,r1.fenxiao_code,r2.num,r1.fenxiao_name,r1.shop_code,r1.delivery_time,r1.fenxiao_name,r3.barcode FROM oms_sell_record AS r1 INNER JOIN oms_sell_record_detail AS r2 ON r1.sell_record_code = r2.sell_record_code LEFT JOIN goods_sku AS r3 ON r2.sku = r3.sku WHERE is_fenxiao = 2 AND order_status != 3 AND shipping_status = 4 ";
        $reocrd_data = $this->db->get_all($sql);
        foreach ($reocrd_data as $key => &$val) {
            //分销商折扣金额
            $fx_price = load_model('fx/GoodsManageModel')->compute_fx_price($val['fenxiao_code'], $val);
            if ($val['trade_price'] == $fx_price) {
                unset($reocrd_data[$key]);
                continue;
            }
            $val['fx_rebeta_price'] = $fx_price;
            //差异单价
            $val['fx_diff_price'] = bcsub($fx_price, $val['trade_price'], 3);
            $val['fx_rebeta_money'] = $fx_price * $val['num'];

            //店铺
            $val['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $val['shop_code']));
            $val['fenxiao_name'] = oms_tb_val('base_custom', 'custom_name', array('custom_code' => $val['fenxiao_code']));
        }
        $filter['data'] = $reocrd_data;
        return $this->format_ret(1, $filter);
    }

    function update_record_data($record_code, $data) {
        $ret = $this->update($data, array('sell_record_code' => $record_code));
        return $ret;
    }

    //验证直辖市
    function check_addr(&$addr_arr) {
        $city_arr = array(
            '北京', '天津', '重庆', '上海'
        );
        $province = substr($addr_arr[0], 0, 6);
        $city = substr($addr_arr[1], 0, 6);
        if (in_array($province, $city_arr) && !in_array($city, $city_arr)) {
            $addr_arr[0] = $province . '市';
            array_unshift($addr_arr, $province);
            array_unshift($addr_arr, '中国');
        }
        $addr_str = implode(' ', $addr_arr);
        return $addr_str;
    }

    function get_record_short_detail($sell_record_code_list) {
        $sell_record_code_arr = explode(',', $sell_record_code_list);
        $sql_value = array();
        $sell_record_code_str = $this->arr_to_in_sql_value($sell_record_code_arr, 'sell_record_code', $sql_value);
        $sql = "SELECT r1.store_code,r2.sku,SUM(r2.num) AS num,SUM(r2.lock_num) AS lock_num FROM oms_sell_record AS r1 INNER JOIN oms_sell_record_detail AS r2 ON r1.sell_record_code=r2.sell_record_code WHERE r1.sell_record_code IN ({$sell_record_code_str}) AND r2.num<>r2.lock_num GROUP BY r1.store_code,r2.sku";
        $record_detail = $this->db->get_all($sql, $sql_value);
        foreach ($record_detail as &$detail) {
            //缺货数
            $detail['short_num'] = $detail['num'] - $detail['lock_num'];
            $key_arr = array('spec1_name', 'spec2_name', 'goods_name', 'barcode', 'goods_code');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($detail['sku'], $key_arr);
            $detail = array_merge($detail, $sku_info);
        }
        return $record_detail;
    }

    /**
     * 由缺货订单创建采购订单
     * @param $out_params
     * @return array
     */
    function add_plan_record_action($out_params) {
        $plan_record = array();
        foreach ($out_params as $params) {
            $key = $params['store_code'] . '_' . $params['supplier_code'];
            $plan_record[$key]['store_code'] = $params['store_code'];
            $plan_record[$key]['supplier_code'] = $params['supplier_code'];
            $plan_record[$key]['detail'][$params['sku']]['short_num'] += $params['short_num'];
            $plan_record[$key]['detail'][$params['sku']]['sku'] = $params['sku'];
        }

        if (empty($plan_record)) {
            return $this->format_ret('-1', '', '单据为空！');
        }
        $this->begin_trans();
        foreach ($plan_record as $record_params) {
            $ret = $this->do_add_plan_record($record_params);
            if ($ret['status'] != 1) {
                $this->rollback();
                return $ret;
            }
        }
        $this->commit();
        return $this->format_ret(1);
    }

    /**
     * 添加采购订单的主单和明细
     * @param $params
     * @return array
     */
    function do_add_plan_record($params) {
        $obj = load_model('pur/PlannedRecordModel');
        $record_code = $obj->create_fast_bill_sn();
        $record_params = array(
            'record_code' => $record_code,
            'record_time' => date('Y-m-d H:i:s'),
            'planned_time' => date('Y-m-d'),
            'in_time' => date('Y-m-d', strtotime("+1 month")),
            'pur_type_code' => '000',
            'store_code' => $params['store_code'],
            'supplier_code' => $params['supplier_code'],
            'rebate' => '1',
            'remark' => '由缺货订单生成',
        );
        $ret = $obj->insert($record_params);
        if ($ret['status'] != 1) {
            return $ret;
        }
        $pid = $ret['data'];
        //日志
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "未确认", 'finish_status' => '未完成', 'action_name' => "创建", 'module' => "planned_record", 'pid' => $pid);
        load_model('pur/PurStmLogModel')->insert($log);

        //添加单据明细
        $detail_params = array();
        foreach ($params['detail'] as $detail) {
            $key_arr = array('spec1_code', 'spec2_code', 'goods_code');
            $sku = $detail['sku'];
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($sku, $key_arr);
            $sku_map = array($sku => $sku_info['goods_code']);
            $ret = load_model('prm/GoodsModel')->get_goods_price('purchase_price', $sku_map);
            if ($ret['status'] < 0) {
                return $ret;
            }
            $price = (float) $ret['data'][$sku];
            $detail_params[] = array(
                'pid' => $pid,
                'goods_code' => $sku_info['goods_code'],
                'spec1_code' => $sku_info['spec1_code'],
                'spec2_code' => $sku_info['spec2_code'],
                'sku' => $detail['sku'],
                'refer_price' => $price,
                'price' => $price,
                'money' => $price * $detail['short_num'],
                'num' => $detail['short_num'],
                'record_code' => $record_code,
            );
        }
        $ret = $this->insert_multi_exp('pur_planned_record_detail', $detail_params);
        //回写数量和金额
        load_model('pur/PlannedRecordDetailModel')->mainWriteBack($pid);
        //日志
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => "未确认", 'finish_status' => '未完成', 'action_name' => "添加明细", 'module' => "planned_record", 'pid' => $pid);
        load_model('pur/PurStmLogModel')->insert($log);
        return $ret;
    }

    /**
     * 合并订单
     * @param $params
     * @return array
     */
    function get_problem_data($sell_record_codes) {
        if (is_array($sell_record_codes)) {
            $sell_record_code_list = "'" . join("','", $sell_record_codes) . "'";
        } else {
            $sell_record_code_list = "'" . $sell_record_codes . "'";
        }
        $sql = "select sell_record_code,tag_v,tag_desc from oms_sell_record_tag where sell_record_code in($sell_record_code_list)";
        $db_arr = ctx()->db->getAll($sql, array(':sell_record_code' => $sell_record_codes));
        $desc_arr = array();
        foreach ($db_arr as $sub_arr) {
            $desc_arr[$sub_arr['sell_record_code']]['tag_v'][] = $sub_arr['tag_v'];
            $desc_arr[$sub_arr['sell_record_code']]['tag_desc'][] = $sub_arr['tag_desc'];
        }
        if (is_array($sell_record_codes)) {
            return $desc_arr;
        } else {
            return @$desc_arr[$sell_record_codes];
        }
    }

    function get_sell_record_tag_desc($sell_record_codes) {
        $sell_record_code = $sell_record_codes['sell_record_code'];
        $sql = "select tag_desc from oms_sell_record_tag where tag_type = 'order_tag' and sell_record_code = '{$sell_record_code}'";
        $db_arr = ctx()->db->getAll($sql, array(':sell_record_code' => $sell_record_codes));
        $desc_arr = array();
        foreach ($db_arr as $sub_arr) {
            $desc_arr[] = $sub_arr['tag_desc'];
        }
        $desc_data = implode(',', $desc_arr);
        return $desc_data;
    }

    /**
     * 订单中是否含有非分销商品
     * @param $record_code
     */
    public function is_out_goods($record_code, $type = 0) {
        $data = $this->get_out_sql($record_code);
        if (empty($data)) {
            return $this->format_ret(0);
        } else {
            //分销商订单不能结算非分销商品是否开启
            $fx_jiesuan_out = load_model('sys/SysParamsModel')->get_val_by_code(array('fx_jiesuan_out'))['fx_jiesuan_out'];
            $error_messge = array();
            if ($fx_jiesuan_out == 1 || $type == 0) {
                foreach ($data as $k => $val) {
                    if ($val['is_custom_money'] == 0) {
                        $error_messge[0] = '订单包含非分销商品';
                    } elseif ($val['custom_code'] != NULL && !in_array($val['fenxiao_code'], explode(',', $val['custom_code']))) {
                        $error_messge[1] = '订单所属分销商非该分销商品指定分销商';
                    } else {
                        unset($data[$k]);
                    }
                }
                if (!empty($error_messge)) {
                    $msg = implode('，', $error_messge);
                    return $this->format_ret(-1, '', $msg);
                } else {
                    return $this->format_ret(1);
                };
            } else {
                return $this->format_ret(1);
            }
        }
    }

    public function have_out_goods($ids) {
        if ($ids != '') {
            $data = $this->get_out_sql($ids);
            if (!empty($data)) {
                foreach ($data as $k => $val) {
                    if ($val['is_custom_money'] == 0) {
                        $data[$k]['message'] = '非分销商品';
                    } elseif ($val['custom_code'] != NULL && !in_array($val['fenxiao_code'], explode(',', $val['custom_code']))) {
                        $data[$k]['message'] = '订单所属分销商非该分销商品指定分销商';
                    } else {
                        unset($data[$k]);
                    }
                }
                if (!empty($data)) {
                    foreach ($data as &$v) {
                        $key_arr = array('barcode');
                        $sku_info = load_model('goods/SkuCModel')->get_sku_info($v['sku'], $key_arr);
                        $v = array_merge($v, $sku_info);
                    }
                    $filename = $this->create_import_fail_files($data);
                    //分销商订单不能结算非分销商品是否开启
                    $url = set_download_csv_url($filename, array('export_name' => 'error'));
                    $fx_jiesuan_out = load_model('sys/SysParamsModel')->get_val_by_code(array('fx_jiesuan_out'))['fx_jiesuan_out'];
                    return $fx_jiesuan_out == 1 ? $this->format_ret(1, '', '分销订单包含<a target="_blank" href="' . $url . '">不允许结算的商品</a>，') : $this->format_ret(2, '', '分销订单包含<a target="_blank" href="' . $url . '">不允许结算的商品</a>,是否确认要结算?');
                } else {
                    return $this->format_ret(0);
                }
            } else {
                return $this->format_ret(0);
            }
        } else {
            $this->format_ret(0);
        }
    }

    private function get_out_sql($ids) {
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        $sql_values = array();
        $sql_str = $this->arr_to_in_sql_value($ids, 'sell_record_code', $sql_values);
        $sql = 'select osd.sell_record_code,osdd.sku,bg.is_custom_money,osd.fenxiao_code,GROUP_CONCAT(fas.custom_code) custom_code,osdd.goods_code
                    from oms_sell_record osd 
                    INNER join oms_sell_record_detail osdd on osd.sell_record_code = osdd.sell_record_code
                    inner join base_goods bg on bg.goods_code = osdd.goods_code
                    left join fx_appoint_goods fas on fas.goods_code = bg.goods_code
                    where osd.sell_record_code in(' . $sql_str . ')
                    and osd.is_fenxiao = 2
                    group by osd.sell_record_code,osdd.sku ';
        $data = $this->db->get_all($sql, $sql_values);
        return $data;
    }

    /**
     * 生成错误信息
     * @param $fail_data
     * @param $msg
     * @return string
     */
    function create_import_fail_files($fail_data) {
        $filename = '分销结算错误' . time() . rand(1000, 9999);
        $file_str = "订单号,商品编码,商品条形码,错误信息\r\n";
        foreach ($fail_data as $barcode => $val) {
            $file_str .= "\t" . $val['sell_record_code'] . "\t,\t" . $val['goods_code'] . "\t,\t" . $val['barcode'] . "\t," . $val['message'] . "\r\n";
        }
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        //var_dump($file_str);die;
        return $filename;
    }

    /**
     * 获取畅销商品的基本数据(sku级别)
     */
    public function barcode_well_by_page($filter) {
        return $this->get_goods_by_filter($filter, 1);
    }

    /**
     * 获取畅销商品的基本数据(goods_code级别)
     */
    public function goods_well_by_page($filter) {
        return $this->get_goods_by_filter($filter, 2);
    }

    //获取畅销商品主体方法
    private function get_goods_by_filter($filter, $type) {
        $filter['year_month'] = isset($filter['year_month']) ? $filter['year_month'] : '';
        $filter['shop_code'] = isset($filter['shop_code']) ? $filter['shop_code'] : '';
        $filter['source_code'] = isset($filter['source_code']) ? $filter['source_code'] : '';
        $filter['goods_code'] = isset($filter['goods_code']) ? $filter['goods_code'] : '';
        if ($filter['year_month'] == '' || $filter['shop_code'] == '')
            return false;
        $order_type = isset($filter['order_by']) ? $filter['order_by'] : 'sku_money';
        $sql_values = array();
        $sql_main = '';
        $this->get_well_goods_main($filter, $sql_main, $sql_values, $type);

        if ($order_type == 'num') {
            $sql_main .= ' order by num desc ';
        } else {
            $sql_main .= ' order by money desc ';
        }
        if ($type == 1) {
            $select = "sum(d.avg_money) as money,sum(d.num) as num,d.sku,d.goods_code";
        } else {
            $select = "sum(d.avg_money) as money,sum(d.num) as num,d.goods_code";
        }
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        $this->get_goods_info($data, $type, 'well');
        return $this->format_ret(1, $data);
    }

    //获取畅销商品拼接sql
    private function get_well_goods_main($filter, &$sql_main, &$sql_values, $type) {
        $sql_main = "from {$this->table} r
                    INNER JOIN {$this->detail_table} d ON r.sell_record_code=d.sell_record_code 
                    where r.shipping_status=4 ";
        if ($filter['year_month'] != '') {
            $start_day = $filter['year_month'] . '-1';
            $end_day = $filter['year_month'] . '-' . date('t', strtotime($start_day));
            $sql_values[':start_date'] = $start_day;
            $sql_values[':end_date'] = $end_day;
            $sql_main .= ' AND r.delivery_date>=:start_date AND r.delivery_date<=:end_date';
        }
        if ($filter['shop_code'] != '') {
            $sql_values[':shop_code'] = $filter['shop_code'];
            $sql_main .= ' AND r.shop_code = :shop_code ';
        }
        if ($filter['goods_code'] != '') {
            $sql_values[':goods_code'] = $filter['goods_code'];
            $sql_main .= ' AND d.goods_code = :goods_code ';
        }
        if ($type == 2) {
            $sql_main .= ' GROUP BY d.goods_code ';
        } else {
            $sql_main .= ' GROUP BY d.sku ';
        }
    }

    //获取滞销商品(sku)
    public function barcode_unsalable_by_page($filter) {
        return $this->get_unsalable($filter, 1);
    }

    //获取滞销商品（goods_code）
    public function goods_unsalable_by_page($filter) {
        return $this->get_unsalable($filter, 2);
    }

    //获取滞销商品主体方法
    private function get_unsalable($filter, $type) {
        $filter['year_month'] = isset($filter['year_month']) ? $filter['year_month'] : '';
        $filter['shop_code'] = isset($filter['shop_code']) ? $filter['shop_code'] : '';
        $filter['goods_code'] = isset($filter['goods_code']) ? $filter['goods_code'] : '';
        if ($filter['year_month'] == '' || $filter['shop_code'] == '')
            return false;
        $sql_values = array();
        $sql_main = '';
        if ($type == 1) {
            $select = ' gs.spec1_code,gs.spec2_code,gs.barcode,gs.goods_code,g.shop_code,gs.sku,sum(gi.stock_num) inv_num ';
        } elseif ($type == 2) {
            $select = ' gs.goods_code,g.shop_code,sum(gi.stock_num) inv_num ';
        }
        $this->get_unsalable_sql($filter, $type, $sql_values, $sql_main);
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        $this->get_goods_info($data, $type, 'unsalable');
        return $this->format_ret(1, $data);
    }

    //获取滞销商品的sql
    public function get_unsalable_sql($filter, $type, &$sql_values, &$sql_main) {
        //获取商品库存数
        $shop_sql = 'select 
                                CASE 
                                WHEN stock_source_store_code is null OR stock_source_store_code = ""
                                THEN send_store_code
                                ELSE stock_source_store_code
                                END as store_code,shop_code
                        from base_shop where shop_code = :shop_code';
        $store_code_str = $this->db->get_value($shop_sql, array(':shop_code' => $filter['shop_code']));
        $store_str = $this->arr_to_in_sql_value((explode(',', $store_code_str)), 'store_code', $sql_values);
        $sql_main2 = '';
        $this->get_well_goods_main($filter, $sql_main2, $sql_values, 1);
        $sql_main = 'from api_goods  g
                    INNER JOIN api_goods_sku s ON g.goods_from_id=s.goods_from_id
                    INNER JOIN goods_sku gs ON gs.barcode=s.goods_barcode
                    INNER JOIN goods_inv gi ON gi.sku = gs.sku 
                    LEFT JOIN ( select d.sku ' . $sql_main2 . ') as de on de.sku = gs.sku ';
        $sql_main .= ' where g.status=1 and de.sku is NULL and gi.store_code in(' . $store_str . ') ';
        if ($filter['shop_code'] != '') {
            $sql_main .= ' AND g.shop_code = :shop_code ';
        }
        if ($filter['goods_code'] != '') {
            $sql_main .= ' AND gs.goods_code = :goods_code ';
        }
        if ($type == 1) {
            $sql_main .= ' GROUP BY gs.sku order by inv_num desc ';
        } else {
            $sql_main .= ' GROUP BY gs.goods_code order by inv_num desc ';
        }
    }

    //获取商品的信息
    private function get_goods_info(&$data, $type, $op_type) {
        //获取商品基本信息
        $goods_code_arr = array_unique(array_column($data['data'], 'goods_code'));
        $goods_code_arr_ch = array_chunk($goods_code_arr, 100);
        $goods_code_info = array();
        foreach ($goods_code_arr_ch as $v) {
            $goods_sql_values = array();
            $goods_code_str = $this->arr_to_in_sql_value($v, 'goods_code', $goods_sql_values);
            $goods_sql = 'select goods_code,goods_name,goods_thumb_img,goods_img from base_goods where goods_code in(' . $goods_code_str . ')';
            $goods_ch = $this->db->get_all($goods_sql, $goods_sql_values);
            $goods_code_info = array_merge($goods_code_info, $goods_ch);
        }
        //条码级别获取库存
        $sku_arr = array_unique(array_column($data['data'], 'sku'));
        $sku_arr_ch = array_chunk($sku_arr, 100);
        $sku_info = array();
        if ($type == 1) {
            foreach ($sku_arr_ch as $v) {
                $this->get_sku_info($v, $sku_info);
            }
        }
        $goods_code_info = load_model('util/ViewUtilModel')->get_map_arr($goods_code_info, 'goods_code');
        foreach ($data['data'] as &$val) {
            if (isset($goods_code_info[$val['goods_code']])) {
                $val['goods_name'] = $goods_code_info[$val['goods_code']]['goods_name'];
                if($goods_code_info[$val['goods_code']]['goods_thumb_img'] != '' ) $val['goods_thumb_img'] = "<img width='48px' height='48px' data-goods-img='{$goods_code_info[$val['goods_code']]['goods_img']}' src='{$goods_code_info[$val['goods_code']]['goods_thumb_img']}' />";
            } else {
                $val['goods_name'] = '';
                $val['goods_thumb_img'] = '';
            }
            if ($type == 1) {
                $key = $val['goods_code'] . ',' . $val['sku'];
                if (isset($sku_info[$key])) {
                    $val['barcode'] = $sku_info[$key]['barcode'];
                    $val['spec1_code_name'] = $sku_info[$key]['spec1_name'];
                    $val['spec2_code_name'] = $sku_info[$key]['spec2_name'];
                } else {
                    $val['barcode'] = '';
                    $val['spec1_code_name'] = '';
                    $val['spec2_code_name'] = '';
                }
            }
        }
    }

    private function get_sku_info($v, &$sku_info) {
        $sku_values = array();
        $sku_str = $this->arr_to_in_sql_value($v, 'sku', $sku_values);
        $sku_result = $this->db->get_all('select goods_code,sku,barcode,spec1_name,spec2_name from goods_sku where sku in(' . $sku_str . ')', $sku_values);
        $sku_result = load_model('util/ViewUtilModel')->get_map_arr($sku_result, 'goods_code,sku');
        $sku_info = array_merge($sku_info, $sku_result);
    }

    public function get_lof_by_record_code($record_code, $col = '*') {
        $sql_values = array(':record_code' => $record_code);
        return $this->db->get_all('select ' . $col . ' from oms_sell_record_lof where record_type = 1 and record_code = :record_code', $sql_values);
    }

}
