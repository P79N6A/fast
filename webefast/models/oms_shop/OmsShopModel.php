<?php

/**
 * 门店订单主单据业务
 */
require_lib('util/oms_util', true);
require_model('tb/TbModel');
require_lang('oms');

class OmsShopModel extends TbModel {

    function __construct() {
        parent::__construct('oms_shop_sell_record');
    }

    protected $detail_table = 'oms_shop_sell_record_detail';
//付款状态
    public $pay_status = array(
        0 => '未付款',
        1 => '部分付款',
        2 => '已付款',
    );
//发货状态
    public $send_status = array(
        0 => '未发货',
        1 => '已发货',
    );
//发货状态
    public $cancel_status = array(
        0 => '未作废',
        1 => '已作废',
    );
//订单类型
    public $record_type = array(
        0 => '门店开单',
        1 => '网店下单',
    );
//订单类型
    public $send_way = array(
        0 => '快递配送',
        1 => '门店自提',
    );

    function get_list_by_page($filter) {
        $tab = empty($filter['ex_list_tab']) ? 'tabs_all' : $filter['ex_list_tab'];
        switch ($tab) {
            case 'tabs_all'://全部
                break;
            case 'tabs_pay'://待付款
                $filter['pay_status'] = "'0','1'";
                $filter['cancel_status'] = 0;
                break;
            case 'tabs_send'://待发货
                $filter['send_way'] = 0;
                $filter['pay_status'] = "'2'";
                $filter['send_status'] = 0;
                $filter['cancel_status'] = 0;
                break;
            case 'tabs_pickup'://待提货
                $filter['send_way'] = 1;
                $filter['pay_status'] = "'2'";
                $filter['send_status'] = 0;
                $filter['cancel_status'] = 0;
                break;
            case 'tabs_shipped'://已发货
                $filter['send_status'] = 1;
                $filter['cancel_status'] = 0;
                break;
            case 'tabs_cancel'://已作废
                $filter['cancel_status'] = 1;
                break;
        }
        return $this->get_by_page($filter);
    }

    private function is_join_detail($filter) {
        $key_arr = array('action_type', 'barcode', 'combo_barcode', 'goods_name', 'goods_code', 'exact_code');
        $check = FALSE;
        foreach ($key_arr as $key) {
            if (isset($filter[$key]) && $filter[$key] != '') {
                $check = TRUE;
                break;
            }
        }

        $key_val_arr = array('ctl_type' => 'export');
        foreach ($key_val_arr as $k => $v) {
            if (isset($filter[$k]) && $filter[$k] == $v) {
                $check = TRUE;
                break;
            }
        }
        return $check;
    }

    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql_values = array();
        $select = 'sr.*';
        $sql_join = "";
        if ($filter['ctl_type'] == 'export') {
            $select = 'sr.record_code,sr.pay_status,sr.send_status,sr.cancel_status,sr.create_time,sr.record_date,sr.buyer_name,sr.receiver_phone,sr.goods_num,sr.payable_amount,sr.buyer_real_amount,sr.remark,rd.goods_code,rd.sku,rd.num,rd.price,rd.avg_money,gs.spec1_name,gs.spec2_name,gs.barcode';
        }

        $is_join = $this->is_join_detail($filter);
        if ($is_join === TRUE) {
            $sql_join = " LEFT JOIN {$this->detail_table} AS rd ON  sr.record_code = rd.record_code LEFT JOIN goods_sku AS gs ON rd.sku = gs.sku ";
        }

        if (isset($filter['goods_name']) && $filter['goods_name'] !== '') {
            $sql_join .= " INNER JOIN  base_goods  g ON  g.goods_code = rr.goods_code ";
        }

        $sql_main = "FROM {$this->table} AS sr $sql_join WHERE 1 ";

        //商店仓库权限
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('sr.send_store_code', $filter_store_code, 'get_entity_store');
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('sr.offline_shop_code', $filter_shop_code, 'get_shop_entity');
        //付款状态
        if (isset($filter['pay_status']) && $filter['pay_status'] !== '') {
            $sql_main .= " AND sr.pay_status in({$filter['pay_status']}) ";
        }
        //作废状态
        if (isset($filter['cancel_status']) && $filter['cancel_status'] !== '') {
            $sql_main .= " AND sr.cancel_status = :cancel_status ";
            $sql_values[':cancel_status'] = $filter['cancel_status'];
        }
        //发货状态
        if (isset($filter['send_status']) && $filter['send_status'] !== '') {
            $sql_main .= " AND sr.send_status = :send_status ";
            $sql_values[':send_status'] = $filter['send_status'];
        }
        //发货方式
        if (isset($filter['send_way']) && $filter['send_way'] !== '') {
            $sql_main .= " AND sr.send_way = :send_way ";
            $sql_values[':send_way'] = $filter['send_way'];
        }
        //订单编号
        if (isset($filter['record_code']) && $filter['record_code'] !== '') {
            $filter['record_code'] = $this->deal_strs($filter['record_code']);
            $filter['record_code'] = $this->get_sql_for_search($filter['record_code'], 'sr.record_code');
            $sql_main .= " AND {$filter['record_code']} ";
        }
        //买家昵称
        if (isset($filter['buyer_name']) && $filter['buyer_name'] !== '') {
            $sql_main .= " AND sr.buyer_name LIKE :buyer_name ";
            $sql_values[':buyer_name'] = "%" . $filter['buyer_name'] . "%";
        }
        //手机号码
        if (isset($filter['receiver_phone']) && $filter['receiver_phone'] !== '') {
            $sql_main .= " AND sr.receiver_phone LIKE :receiver_phone ";
            $sql_values[':receiver_phone'] = "%" . $filter['receiver_phone'] . "%";
        }
        //业务日期-开始
        if (isset($filter['record_date_start']) && $filter['record_date_start'] !== '') {
            $sql_main .= " AND sr.record_date >=:record_date_start ";
            $sql_values[':record_date_start'] = $filter['record_date_start'];
        }
        //业务日期-结束
        if (isset($filter['record_date_end']) && $filter['record_date_end'] !== '') {
            $sql_main .= " AND sr.record_date <=:record_date_end ";
            $sql_values[':record_date_end'] = $filter['record_date_end'];
        }
        //门店
        if (isset($filter['offline_shop_code']) && $filter['offline_shop_code'] !== '') {
            $shop_arr = explode(',', $filter['offline_shop_code']);
            $shop_str = $this->arr_to_in_sql_value($shop_arr, 'offline_shop_code', $sql_values);
            $sql_main .= " AND sr.offline_shop_code IN({$shop_str}) ";
        }

        $sql_main .= " ORDER BY sr.create_time desc";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$value) {
            //整理订单状态
            $value['status'] = $this->pay_status[$value['pay_status']];
            $value['status'] .= ' ' . $this->send_status[$value['send_status']];
            $value['status'] .= $value['cancel_status'] == 1 ? ' ' . $this->cancel_status[$value['cancel_status']] : '';
        }
        if ($filter['ctl_type'] == 'export') {
            filter_fk_name($data['data'], array('goods_code|goods_code'));
        }

        $ret_status = OP_SUCCESS;
        return $this->format_ret($ret_status, $data);
    }

    /**
     * 通过field_name查询
     * @param  $ :查询field_name
     * @param  $select ：查询返回字段
     * @return array (status, data, message)
     */
    public function get_by_field($table, $field_name, $value, $select = "*") {
        $sql = "select {$select} from {$table} where {$field_name} = :{$field_name}";
        $data = $this->db->get_all($sql, array(":{$field_name}" => $value));

        if ($data) {
            return $this->format_ret('1', $data);
        } else {
            return $this->format_ret('-1', '', 'get_data_fail');
        }
    }

    /**
     * 根据订单号获取明细信息
     */
    function get_detail_by_code($record_code) {
        $detail = load_model('oms_shop/OmsShopOptModel')->get_record_detail($record_code);
        //filter_fk_name($data, array('spec1_code|spec1_code', 'spec2_code|spec2_code', 'sku|barcode', 'goods_code|goods_code'));
        $result = array();
        foreach ($detail as $sub_detail) {
            $key_arr = array('spec1_code', 'spec1_name', 'spec2_code', 'spec2_name', 'barcode', 'goods_name');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($sub_detail['sku'], $key_arr);
            $sub_detail = array_merge($sub_detail, $sku_info);
            $result[] = $sub_detail;
        }
        return $result;
    }

    /**
     * @todo 获取明细，并指定值作为键
     * @param string $record_code 订单号
     * @param string $key 键
     * @return array 明细
     */
    public function get_deal_detail_by_code($record_code, $key = '') {
        $result = $this->get_by_field($this->detail_table, 'record_code', $record_code);
        if ($result['status'] != 1) {
            return array();
        }
        $result = $result['data'];
        if ($key == '') {
            return $result;
        }
        $result_2 = array();
        $id_map_arr = explode(',', $key);
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

    /**
     * 读取详情页各部分信息
     */
    public function component($record_code, $types) {
        $res = array();
        //基本信息
        $res = load_model('oms_shop/OmsShopOptModel')->get_record_data($record_code);
        if (empty($res)) {
            return $res = array();
        }
        $pay_info = load_model('oms_shop/OmsShopOptModel')->get_record_pay_info($record_code);
        //$discount_info = load_model('oms_shop/OmsShopOptModel')->get_record_discount_info($record_code);
        $pay_code_arr = array_column($pay_info, 'pay_type_name');

        $res['status'] = $this->pay_status[$res['pay_status']]; //订单状态
        $res['status'] .= ' | ' . $this->send_status[$res['send_status']];
        $res['status'] .= ' | ' . $this->cancel_status[$res['cancel_status']];
        $res['record_type_name'] = $this->record_type[$res['record_type']]; //订单类型
        $res['send_way_name'] = $this->send_way[$res['send_way']]; //发货方式
        $res['pay_way_name'] = implode(' | ', $pay_code_arr); //支付方式
        $res['cashier_name'] = oms_tb_val('sys_user', 'user_name', array('user_code' => $res['cashier_code'])); //收银员
        $res['guide_name'] = oms_tb_val('sys_user', 'user_name', array('user_code' => $res['guide_code'])); //导购员
        $res['offline_shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $res['offline_shop_code'])); //门店
        $res['send_store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $res['send_store_code'])); //发货仓库
        $res['express_name'] = oms_tb_val('base_express', 'express_name', array('express_code' => $res['express_code'])); //
        if ($res['record_type'] == 1) {
            $res['online_shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $res['online_shop_code'])); //网络店铺
        }
        if ($res['send_way'] == 0) {
            $res['express_name'] = oms_tb_val('base_express', 'express_name', array('express_code' => $res['express_code'])); //配送方式
        }
        $response['record'] = $res;

        $spec = $this->get_spec_rename();
        $response['goods_spec1'] = $spec['goods_spec1'];
        $response['goods_spec2'] = $spec['goods_spec2'];
        //商品信息
        if (array_search('detail', $types) !== false) {
            $response['detail_list'] = $this->get_detail_by_code($record_code);
            if (!empty($response['detail_list'])) {
                foreach ($response['detail_list'] as $key => &$value) {
                    $value['goods_price'] = $value['price'] * $value['rebate'];
                }
            }
        }

        $response['is_cod'] = 0;
        //货到付款
        if (in_array('cod', $pay_code_arr)) {
            $response['is_cod'] = 1;
        }
        //下单时间
        $status_info[1] = array('time' => explode(" ", $response['record']['create_time']));
        //部分付款
        if ($response['record']['pay_status'] == 1) {
            $status_info[2] = array('time' => explode(" ", $response['record']['pay_time']));
        }
        //已付款
        if ($response['record']['pay_status'] == 2) {
            $status_info[3] = array('time' => explode(" ", $response['record']['pay_time']));
        }
        //已发货
        if ($response['record']['send_status'] == 1) {
            $status_info[4] = array('time' => explode(" ", $response['record']['send_time']));
        }
        //订单作废
        if ($response['record']['cancel_status'] == 1) {
            $status_info[5] = array('time' => explode(" ", $response['record']['cancel_time']));
            $response['data_invalid'] = array('is_invalid' => 1, 'time' => explode(" ", $response['record']['cancel_time']));
        }

        ksort($status_info);
        $response['status_info'] = $status_info;

        return $response;
    }

    /**
     * 保存详情各部分
     */
    public function save_component($record_code, $type, $req_data = array()) {
        $record = load_model('oms_shop/OmsShopOptModel')->get_record_data($record_code);
        if ($record['order_status'] == 1) {
            return $this->format_ret(-1, '', '已作废的订单不能操作');
        }
        if ($record['send_status'] == 1) {
            return $this->format_ret(-1, '', '已发货的订单不能操作');
        }

        $this->begin_trans();

        $ret = $this->update($req_data, array('record_code' => $record_code));
        if ($ret['status'] != 1) {
            $this->rollback();
            return $ret;
        }
        $this->commit();
        return $ret;
    }

    /**
     * 获取改款商品
     */
    function get_change_goods($request) {
        $sql_main = '';
        $sql_join = "LEFT JOIN base_goods r2 ON r1.goods_code = r2.goods_code
                     LEFT JOIN goods_sku r3 ON r1.sku = r3.sku";
        $select = "SELECT r1.sku, r1.goods_code, r1.stock_num, r1.lock_num, r2.goods_name, r2.barcode, r3.barcode, r3.spec1_name, r3.spec2_name";

        $sql_main .= "{$select} FROM goods_inv r1 {$sql_join} WHERE 1=1 ";

        $store_code = isset($request['store_code']) ? $request['store_code'] : '';
        $goods_filter = isset($request['goods_multi']) ? $request['goods_multi'] : '';
        $sql_main .= " AND (r1.goods_code = :goods_filter OR goods_name = :goods_filter OR r3.barcode = :goods_filter)
                      AND r1.store_code = :store_code ";
        $sql_main .= " GROUP BY r1.sku";
        $sql_values = array(":goods_filter" => $goods_filter, ":store_code" => $store_code);
        $data = $this->db->get_all($sql_main, $sql_values);
        foreach ($data as &$value) {
            $value['available_num'] = (int) $value['stock_num'] - (int) $value['lock_num'];
            $value['available_num'] = ($value['available_num'] < 0) ? 0 : $value['available_num'];
        }
        return $data;
    }

    /**
     * 获取订单操作日志
     */
    function get_log_by_page($filter) {
        $sql_main = " FROM oms_shop_sell_record_log WHERE 1 ";
        if (isset($filter['record_code']) && !empty($filter['record_code'])) {
            $sql_main .= " AND record_code = :record_code ";
            $sql_values[':record_code'] = $filter['record_code'];
        }
        $sql_main .= " ORDER BY log_id DESC";
        $select = " * ";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$val) {
            $val['pay_status'] = $this->pay_status[$val['pay_status']];
            $val['send_status'] = $this->send_status[$val['send_status']];
        }
        return $this->format_ret(1, $data);
    }

    /**
     * 获取规格名
     */
    function get_spec_rename() {
        $arr = array('goods_spec1', 'goods_spec2');
        $arr_spec = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $res['goods_spec1'] = isset($arr_spec['goods_spec1']) ? $arr_spec['goods_spec1'] : '';
        $res['goods_spec2'] = isset($arr_spec['goods_spec2']) ? $arr_spec['goods_spec2'] : '';
        return $res;
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
     * @param string $strs  要处理的字符串
     * @param string $field 需要查询的字段
     * @return string $sql   拼接好的sql语句
     */
    function get_sql_for_search($strs, $field) {
        $str_arr = explode(",", $strs);
        foreach ($str_arr as $key => $value) {
            $value = str_replace("'", "%", $value);
            $str_arr[$key] = " {$field} like '" . $value . "'";
        }
        $sql = implode(' OR', $str_arr);
        return $sql;
    }

    /**
     * 门店收银新增订单
     */
    function cashier_add($request) {
        if (empty($request['goods'])) {
            return $this->format_ret(-1, '', '扫描商品信息不存在');
        }
        $fields = array('goods_name', 'goods_code', 'spec1_name', 'spec2_name', 'num', 'price', 'rebate', 'price1', 'goods_amount', 'sku', 'spec1_code', 'spec2_code');
        $record_code = $this->new_code();
        $goods = array();
        $discount = empty($request['discount']) ? 0 : $request['discount'];
        $amount = $request['amount'];
        foreach ($request['goods'] as $key => $val) {
            foreach ($val as $k => $v) {
                $goods[$key]['record_code'] = $record_code;
                $goods[$key][$fields[$k]] = $v;
            }
            if ($discount != 0) {
                $goods[$key]['avg_money'] = $goods[$key]['goods_amount'] - ($goods[$key]['goods_amount'] / $amount * $discount);
            } else {
                $goods[$key]['avg_money'] = $goods[$key]['goods_amount'];
            }
        }
        if (!empty($request['tel'])) {
            $member = load_model('crm/CustomerModel')->get_by_field('tel', $request['tel']);
            $member = $member['data'];
        }

        //主单据信息
        $record = array(
            'record_code' => $record_code,
            'customer_code' => isset($member['customer_code']) ? $member['customer_code'] : '',
            'receiver_name' => isset($member['customer_name']) ? $member['customer_name'] : '',
            'buyer_name' => isset($member['nickname']) ? $member['nickname'] : '',
            'receiver_phone' => isset($member['tel']) ? $member['tel'] : '',
            'receiver_address' => isset($member['address']) ? $member['address'] : '',
            'send_store_code' => $request['shop_code'],
            'offline_shop_code' => $request['shop_code'],
            'cashier_code' => $request['cashier_code'],
            'create_person' => CTX()->get_session('user_name'),
            'create_time' => date('Y-m-d H:i:s'),
            'goods_num' => $request['num'],
            'sku_num' => count($goods),
            'record_amount' => array_sum(array_map(function($val) {
                                return $val['num'] * $val['price'];
                            }, $goods)),
            'buyer_real_amount' => $request['real_amount'],
            'hand_adjust_money' => $request['discount'],
            'payable_amount' => $request['amount'],
            'record_date' => date('Y-m-d'),
            'remark' => $request['remark'],
        );
        $pay_info = array(
            'pay_code' => $request['pay_code'],
            'pay_money' => $request['amount'],
        );
        $this->begin_trans();
        try {
            $ret = $this->db->insert('oms_shop_sell_record', $record);
            if ($ret !== true) {
                $this->rollback();
                return $this->format_ret(-1, '', '保存订单出错');
            }
            $ret_detail = $this->add_detail($goods);
            if ($ret_detail['status'] != 1) {
                $this->rollback();
                return $ret_detail;
            }
            $ret_pay = load_model('oms_shop/OmsShopOptModel')->opt_record($record_code, 'pay', $pay_info);
            if ($ret_pay['status'] != 1) {
                $this->rollback();
                return $ret_pay;
            }
            if ($record['payable_amount'] == $pay_info['pay_money']) {
                $ret_send = load_model('oms_shop/OmsShopOptModel')->opt_record($record_code, 'send');
                if ($ret_send['status'] != 1) {
                    $this->rollback();
                    return $ret_send;
                }
            }
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }

        return $this->format_ret(1, $record_code);
    }

    /**
     * 添加明细商品
     */
    function add_detail($data) {
        try {
            $update_str = "num = VALUES(num) + num , goods_amount = VALUES(goods_amount) + goods_amount";
            $ret = $this->insert_multi_duplicate($this->detail_table, $data, $update_str);
            if ($ret['status'] != 1) {
                return $this->format_ret(-1, '', '保存订单明细出错');
            }
        } catch (Exception $e) {
            return $this->format_ret(-1, '', '保存失败:' . $e->getMessage());
        }

        return $this->format_ret(1);
    }

    /**
     * create new sell_record code
     * @return string
     */
    function new_code() {
        $sql = "select record_id  from {$this->table} order by record_id desc limit 1 ";
        $data = $this->db->get_all($sql);
        if (!empty($data)) {
            $num = intval($data[0]['record_id']) + 1;
        } else {
            $num = 1;
        }
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
     * 沟通日志
     */
    function opt_communicate($data) {
        if (!empty($data['record_code'])) {
            $return_record = load_model('oms_shop/OmsShopOptModel')->get_record_data($data['record_code']);
            return $this->add_action($return_record, "沟通日志", $data['communicate_log']);
        } else {
            return $this->format_ret(-1, '', '订单不存在');
        }
    }

}
