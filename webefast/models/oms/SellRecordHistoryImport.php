<?php

require_lib('util/oms_util', true);
require_model('tb/TbModel');
require_lang('oms');

/**
 *
 * 历史订单导入
 * @author Administrator
 *
 */
class SellRecordHistoryImport extends TbModel {

    /**
     * @var string 表名
     */
    protected $table = 'oms_sell_record';
    protected $detail_table = 'oms_sell_record_detail';
    public $order_test = array(
        array(
            'shop_code' => '兔兔的娘子',
            'store_code' => '001',
            'is_cod' => FALSE,
            'sell_record_code' => '1006778779',
            'deal_code' => '1006778779',
            'pay_time' => '2016-01-11 00:00:00',
            'buyer_nick' => 'shopping_attb2',
            'receiver_name' => '袁飞',
            'receiver_province' => '北京',
            'receiver_city' => '北京',
            'receiver_district' => '',
            'receiver_addr' => '解放路110号',
            'receiver_mobile' => '15921632517',
            'receiver_phone' => '021-50184188',
            'express_code' => 'SF',
            'express_no' => '021-50184188',
            'create_time' => '2016-01-10 00:00:00',
            'delivery_time' => '2016-01-12 00:00:00',
            'express_money' => 12,
            'details' => array(
                array(
                    'barcode' => 'D0001',
                    'num' => 2,
                    'price' => 100,
                    'avg_money' => 200
                )
            )
        )
    );

    /**
     * EXCEL导入订单入口
     * @param type $csv_path
     * @return string
     */
    function import_trade_action($csv_path) {
        require_lib('csv_util');
        $exec = new execl_csv();
        $key_arr = array(
            'deal_code',
            'shop_code',
            'store_code',
            'buyer_nick',
            'create_time',
            'is_cod',
            'pay_time',
            'delivery_time',
            'express_code',
            'express_no',
            'receiver_name',
            'receiver_mobile',
            'receiver_province',
            'receiver_city',
            'receiver_district',
            'receiver_addr',
            'express_money',
            'barcode',
            'num',
            'price',
            'avg_money'
        );

        $csv_data = $exec->read_csv($csv_path, 1, $key_arr, array('shop_code', 'buyer_nick', 'receiver_name', 'receiver_province', 'receiver_city', 'receiver_district', 'receiver_addr'));

        if (count($csv_data) < 1) {
            print_r('没有订单数据数据,请重新整理数据导入');
            die;
        }

        if (count($csv_data) > 10000) {
            print_r('单次最大支持10000条数据导入,已超限，请重新整理数据导入');
            die;
        }

        if (is_array($csv_data) && count($csv_data) > 0) {
            $trades = array();

            $error_row_data = array();

            $last_sell_record = '';
            $error_num = 0;
            $deal_code_by_sell_record_cod = array();
            foreach ($csv_data as $key => $value) {
                if (!isset($deal_code_by_sell_record_cod[$value['deal_code']])) {
                    $value['sell_record_code'] = load_model('oms/SellRecordModel')->new_code();
                    $deal_code_by_sell_record_cod[$value['deal_code']] = $value['sell_record_code'];
                } else {
                    $value['sell_record_code'] = $deal_code_by_sell_record_cod[$value['deal_code']];
                }
                //$value['sell_record_code'] = $value['deal_code'];
                $ret = $this->is_valid_excel_data($value, $key);
                if ($ret['status'] == 1) {
                    $trade = array();
                    $details = array();

//                    if ($last_sell_record == $value['sell_record_code']) {
//                        $details['barcode'] = $value['barcode'];
//                        $details['num'] = $value['num'];
//                        $details['price'] = $value['price'];
//                        $details['avg_money'] = $value['avg_money'];
//
//                        $trades[$value['sell_record_code']]['details'][] = $details;
//                    } else {
                    if (!array_key_exists($value['sell_record_code'], $trades)) {
                        $last_sell_record = $value['sell_record_code'];

                        $trade = $value;
                        $details['barcode'] = $value['barcode'];
                        $details['num'] = $value['num'];
                        $details['price'] = $value['price'];
                        $details['avg_money'] = $value['avg_money'];

                        $trade['details'] = array();
                        $trade['details'][$value['barcode']] = $details;

                        $trades[$value['sell_record_code']] = $trade;
                    } else {
                        if (array_key_exists($value['barcode'], $trades[$value['sell_record_code']]['details'])) {
                            unset($trades[$value['sell_record_code']]);
                            $value['msg'] = '表格存在重复数据';
                            $error_row_data[] = $value;
                        } else {
                            $details['barcode'] = $value['barcode'];
                            $details['num'] = $value['num'];
                            $details['price'] = $value['price'];
                            $details['avg_money'] = $value['avg_money'];
                            $trades[$value['sell_record_code']]['details'][$value['barcode']] = $details;
                        }
                    }
//                    }
                } else {
                    ++$error_num;
                    $value['msg'] = $ret['message'];
                    if (!empty($ret['data'])) {
                        $value['not_deal_code'] = 1;
                    }
                    $error_row_data[] = $value;
                }
            }
        } else {
            return '没有找到需要导入的订单！<br>';
        }

        $this->order_import($trades, '', $error_row_data);
    }

    /**
     * 订单生成入口
     * @param type $orders
     * @param type $action_user
     */
    function order_import($orders = '', $action_user = '', $error_row_data = '') {
        if (!is_array($orders) || count($orders) > 1000) {
            //return $this->format_ret(-1, '', 'SELL_RECORD_SAVE_ERROR');
        }
        $shop_list = array();
        $store_list = array();
        $express_list = array();

        foreach ($orders as $sub_order) {
            if (!array_key_exists($sub_order['shop_code'], $shop_list)) {
                $shop_list[$sub_order['shop_code']] = $sub_order['shop_code'];
            }

            if (!array_key_exists($sub_order['express_code'], $express_list)) {
                $express_list[$sub_order['express_code']] = $sub_order['express_code'];
            }

            if (!array_key_exists($sub_order['store_code'], $store_list)) {
                $store_list[$sub_order['store_code']] = $sub_order['store_code'];
            }
        }

        $shop_list_str = "'" . implode("','", $shop_list) . "'";
        $sql = "select shop_code as code,sale_channel_code from base_shop WHERE shop_code IN(" . $shop_list_str . ")";
        $shop_data = $this->db->get_all($sql);

        $store_list_str = "'" . implode("','", $store_list) . "'";
        $sql = "select store_code as code from base_store WHERE store_code IN(" . $store_list_str . ")";
        $store_data = $this->db->get_all($sql);

        $express_list_str = "'" . implode("','", $express_list) . "'";
        $sql = "select express_code as code from base_express WHERE express_code IN(" . $express_list_str . ")";
        $express_data = $this->db->get_all($sql);

        $fail_order = array();

        $success_import_trade_count = 0;
        $fail_import_trade_count = 0;

        foreach ($orders as $sub_order) {
            $is_fenxiao = isset($sub_order['is_fenxiao']) && !empty($sub_order['is_fenxiao']) ? $sub_order['is_fenxiao'] : 0;
            $shop_check = $this->order_check($shop_data, $sub_order['shop_code']);
            $store_check = $this->order_check($store_data, $sub_order['store_code']);
            $express_check = $this->order_check($express_data, $sub_order['express_code']);

            $sql = "SELECT COUNT(1) as deal_code_count FROM oms_sell_record WHERE deal_code = :deal_code";
            $deal_code_count = $this->db->get_row($sql, array(':deal_code' => $sub_order['deal_code']));

            if ($deal_code_count['deal_code_count'] > 0) {
                $sub_order['msg'] = '已存在交易号';
                $fail_order[] = $sub_order;
                continue;
            }

            if (!$shop_check || !$store_check || !$express_check) {//店铺代码、仓库代码、快递代码无法识别
                $sub_order['msg'] = '店铺代码、仓库代码、快递代码存在不匹配项';
                $fail_order[] = $sub_order;
                continue;
            }

            $order = array();
            $order_details = array();
			$order_lof = array();

            $sys_lof = load_model('prm/GoodsLofModel')->get_sys_lof();
            $sys_lof = $sys_lof['data'];

            $goods_num = 0;
            $sku_num = 0;
            $sku_list = array();
            $payable_money = $sub_order['express_money'];
            $fx_payable_money = 0;

            $is_exist_sku = TRUE;

            $sub_order['create_time'] = date('Y-m-d H:i:s', strtotime($sub_order['create_time']));
            $sub_order['pay_time'] = date('Y-m-d H:i:s', strtotime($sub_order['pay_time']));
            $sub_order['delivery_time'] = date('Y-m-d H:i:s', strtotime($sub_order['delivery_time']));

            $order['goods_money'] = 0;
            foreach ($sub_order['details'] as $sub_order_detail) {
                $goods_num = $goods_num + $sub_order_detail['num'];
                $sku_list[] = $sub_order_detail['barcode'];
                $payable_money = $payable_money + $sub_order_detail['avg_money'];

                $sql = "select sku,barcode,goods_code,spec1_code,spec2_code from goods_sku WHERE barcode = '{$sub_order_detail['barcode']}'";
                $skus = $this->db->get_row($sql);

                if (empty($skus)) {
                    $sql = "select goods_sku.sku,goods_sku.barcode,goods_sku.goods_code from goods_barcode_child,goods_sku
WHERE goods_barcode_child.sku = goods_sku.sku AND goods_barcode_child.barcode = '{$sub_order_detail['barcode']}';";
                    $skus = $this->db->get_all($sql);
                }

                if (empty($skus) || count($skus) < 1) {
                    $is_exist_sku = FALSE;
                    break;
                }
                $order_detail = array();
                $order_detail['sell_record_code'] = $sub_order['sell_record_code'];
                $order_detail['deal_code'] = $sub_order['deal_code'];
                $order_detail['sku'] = $skus['sku'];
                $order_detail['spec1_code'] = $skus['spec1_code'];
                $order_detail['spec2_code'] = $skus['spec2_code'];
                $order_detail['goods_code'] = $skus['goods_code'];
                $order_detail['barcode'] = $sub_order_detail['barcode'];
                $order_detail['goods_price'] = $sub_order_detail['price'];
                $order_detail['num'] = $sub_order_detail['num'];
                $order_detail['lock_num'] = $sub_order_detail['num'];
                $order_detail['avg_money'] = $sub_order_detail['avg_money'];
                $order_detail['plan_send_time'] = $sub_order['pay_time'];
                if ($is_fenxiao == 1 || $is_fenxiao == 2) { //普通分销、淘分销导入
                    $order_detail['fx_amount'] = !empty($sub_order_detail['fx_amount']) ? $sub_order_detail['fx_amount'] : 0;
                    $order_detail['trade_price'] = !empty($sub_order_detail['fx_amount']) ? $sub_order_detail['fx_amount'] / $sub_order_detail['num'] : 0;
                    //主表结算金额
                    $fx_payable_money += $order_detail['fx_amount'];
                }

                $order_details[] = $order_detail;
                $order['goods_money'] += $sub_order_detail['avg_money'];
				
                $lof_data = array();
                $lof_data['record_type'] = 1;
                $lof_data['record_code'] = $sub_order['sell_record_code'];
                $lof_data['deal_code'] = $sub_order['deal_code'];
                $lof_data['goods_code'] = $skus['goods_code'];
                $lof_data['spec1_code'] = $skus['spec1_code'];
                $lof_data['spec2_code'] = $skus['spec2_code'];
                $lof_data['sku'] = $skus['sku'];
                $lof_data['store_code'] = $sub_order['store_code'];
                $lof_data['lof_no'] = $sys_lof['lof_no'];
                $lof_data['production_date'] = $sys_lof['production_date'];
                $lof_data['num'] = $sub_order_detail['num'];
                $lof_data['occupy_type'] = 2;
                $lof_data['order_date'] = $sub_order['delivery_time'];
                $lof_data['create_time'] = strtotime($sub_order['create_time']);
                $order_lof[] = $lof_data;
            }

            if (!$is_exist_sku) {//商品识别异常
                $sub_order['msg'] = $sub_order_detail['barcode'] . '商品SKU不存在';
                $fail_order[] = $sub_order;
                continue;
            }

            $sku_list = array_unique($sku_list);
            $sku_num = count($sku_list);

            $order['sell_record_code'] = $sub_order['sell_record_code'];
            $order['deal_code'] = $sub_order['deal_code'];
            $order['deal_code_list'] = $sub_order['deal_code'];

            foreach ($shop_data as $shop) {
                if ($shop['code'] == $sub_order['shop_code']) {
                    $order['sale_channel_code'] = $shop['sale_channel_code'];
                    break;
                }
            }

            $order['order_status'] = 1;
            $order['shipping_status'] = 4;
            $order['store_code'] = $sub_order['store_code'];
            $order['shop_code'] = $sub_order['shop_code'];
            $order['pay_type'] = $sub_order['is_cod'] == '是' ? 'cod' : 'secured';
            $order['pay_code'] = 'alipay';
            $order['pay_status'] = 2;
            $order['pay_time'] = date('Y-m-d H:i:s', strtotime($sub_order['pay_time']));

            $order['buyer_name'] = $sub_order['buyer_nick'];

            $order['customer_code'] = $sub_order['customer_code'];
            $order['receiver_name'] = $sub_order['receiver_name'];
            $order['receiver_country'] = 1;

            $province = $this->get_area($sub_order['receiver_province'], '1');

            if ($province == '') {
                $sub_order['msg'] = '省份无法识别';
                $fail_order[] = $sub_order;
                continue;
            }

            $city = $this->get_area($sub_order['receiver_city'], $province[0]['id']);
            if (!empty($city)) {
                $district = $this->get_area($sub_order['receiver_district'], $city[0]['id']);
            } else {
                $sub_order['msg'] = $sub_order['receiver_city'] . ':市无法识别';
                $fail_order[] = $sub_order;
                continue;
            }

            $order['receiver_province'] = $province[0]['id'];
            $order['receiver_city'] = empty($city) ? '' : $city[0]['id'];
            $order['receiver_district'] = empty($district) ? '' : $district[0]['id'];
            $order['receiver_address'] = $sub_order['receiver_province'] . ' ' .
                    $sub_order['receiver_city'] . ' ' .
                    $sub_order['receiver_district'] . ' ' .
                    $sub_order['receiver_addr']; //含省市区地址
            $order['receiver_addr'] = $sub_order['receiver_addr']; //不含省市区地址

            $order['receiver_mobile'] = $sub_order['receiver_mobile'];
            $order['receiver_phone'] = $sub_order['receiver_phone'];

             $this->get_customer($order);
          //  $order['customer_code'] = $buyer_info['customer_code'];
            $order['receiver_phone'] = $sub_order['receiver_phone'];

            $order['express_code'] = $sub_order['express_code'];
            $order['express_no'] = $sub_order['express_no'];

            $order['plan_send_time'] = $sub_order['delivery_time'];

            $order['goods_num'] = $goods_num;
            $order['sku_num'] = $sku_num;
            $order['lock_inv_status'] = 1;
            $order['must_occupy_inv'] = 1;
            $order['buyer_remark'] = $sub_order['buyer_remark'];
            $order['seller_remark'] = $sub_order['seller_remark'];

            $order['express_money'] = $sub_order['express_money'];

            $order['payable_money'] = $payable_money;
            $order['paid_money'] = $payable_money;

            $order['create_time'] = $sub_order['create_time'];
            $order['record_time'] = $order['create_time'];

            $order['delivery_time'] = $sub_order['delivery_time'];
            $order['delivery_date'] = date('Y-m-d', strtotime($order['delivery_time']));
            ;

            $order['record_date'] = $order['delivery_date'];
            $order['is_back'] = 1;
            $order['is_back_time'] = $order['pay_time'];

            $order['check_time'] = $order['pay_time'];
            $order['is_notice_time'] = $order['pay_time'];
            $order['sale_mode'] = 'stock';
            $order['buyer_remark'] = '';
            $order['seller_remark'] = '';

            $order['order_remark'] = '历史订单,导入时间-' . date('Y-m-d H:i:s');
            if ($is_fenxiao == 1) { //淘分销导入发货单
                $distributor_row = load_model('base/CustomModel')->get_by_code($sub_order['fenxiao_code']);
                if (empty($distributor_row['data'])) {//没有分销商档案就新建
                    $distributor = array(
                        'custom_code' => $sub_order['fenxiao_code'],
                        'custom_name' => $sub_order['fenxiao_code'],
                        'shop_code' => $sub_order['shop_code'],
                        'custom_type' => 'tb_fx',
                        'custom_rebate' => 1,
                        'custom_price_type' => 2,
                        'is_effective' => 1,
                        'create_time' => date('Y-m-d H:i:s'),
                    );

                    $ret = load_model('base/CustomModel')->insert($distributor);
                    $order['fenxiao_code'] = $sub_order['fenxiao_code'];
                    $order['fenxiao_id'] = $ret['data'];
                    $order['fenxiao_name'] = $sub_order['fenxiao_code'];
                } else {
                    $order['fenxiao_id'] = $distributor_row['data']['custom_id'];
                    $order['fenxiao_code'] = $distributor_row['data']['custom_code'];
                    $order['fenxiao_name'] = $distributor_row['data']['custom_name'];
                }
                $order['is_fenxiao'] = $is_fenxiao; //分销标识
                $order['fx_payable_money'] = $fx_payable_money;
                $order['fx_express_money'] = $sub_order['fx_express_money'];
                $order['is_fx_settlement'] = 1; //已结算
            }
            if ($is_fenxiao == 2) { // 普通分销导入发货单
                $distributor_row = load_model('base/CustomModel')->get_by_code($sub_order['fenxiao_code']);
                if (empty($distributor_row['data'])) {//没有分销商档案不添加
                    continue;
                }
                //如果分销订单含有非分销商品则不允许导入
                $goods_out = $this->get_goods_out($order_detail['goods_code'],$distributor_row['data']['custom_code']);
                if($goods_out['status'] < 0){
                    $sub_order['msg'] = $goods_out['message'];
                    $fail_order[] = $sub_order;
                    continue;
                }
                $order['fenxiao_id'] = $distributor_row['data']['custom_id'];
                $order['fenxiao_code'] = $distributor_row['data']['custom_code'];
                $order['fenxiao_name'] = $distributor_row['data']['custom_name'];
                $order['is_fenxiao'] = $is_fenxiao; //分销标识
                $order['fx_payable_money'] = $fx_payable_money;
                $order['fx_express_money'] = $sub_order['fx_express_money'];
                $order['is_fx_settlement'] = 1; //已结算
            }

            $ret = $this->insert_dup($order);

            $ret = $this->insert_multi_duplicate('oms_sell_record_detail', $order_details, 'deal_code = values(deal_code)');
			
			$ret = $this->insert_multi_exp('oms_sell_record_lof', $order_lof);

            //回写oms_sell_record_lof表明细id
            $sql = 'UPDATE oms_sell_record_lof AS rl,oms_sell_record_detail AS rd SET rl.p_detail_id=rd.sell_record_detail_id WHERE rl.record_code=:record_code AND rl.record_code=rd.sell_record_code AND rl.sku=rd.sku';
            $ret = $this->query($sql, array(':record_code' => $sub_order['sell_record_code']));

            $success_import_trade_count++;

            if (1 != $ret['status']) {
                //return $ret;
            }
             //导入订单时添加操作日志
            $obj_action = load_model('oms/SellRecordActionModel');
            $log_msg = '已发货订单导入';
            $obj_action->add_action($sub_order['sell_record_code'], "新增", $log_msg);
        }
        foreach ($error_row_data as $val) {
            $fail_order[] = $val;
        }
        $fail_import_trade_count = count($fail_order);
        $msg_arr = array();
        foreach ($fail_order as $key => $val) {
            if (array_key_exists($val['deal_code'], $msg_arr)) {
                $msg_arr[$val['deal_code']] = $msg_arr[$val['deal_code']] . "," . $val['msg'];
                continue;
            }
            if ($val['not_deal_code'] == 1 && isset($val['not_deal_code'])) {
                $msg_arr[$key] = $val['msg'];
            } else {
                $msg_arr[$val['deal_code']] = '交易号：' . $val['deal_code'] . ":" . $val['msg'];
            }
        }
        $msg = '导入成功订单数:' . $success_import_trade_count . ',失败订单数:' . $fail_import_trade_count . "</br>";
        if (!empty($msg_arr)) {
            $msg .= implode("</br>", $msg_arr);
        }
        header('Content-Type: text/html;charset=UTF-8');
        print($msg);
    }

    /**
     *
     * 订单信息验证
     * @param unknown_type $arr
     * @param unknown_type $key
     */
    function order_check($arr, $key) {
        foreach ($arr as $sub) {
            if ($sub['code'] == $key) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     *
     * 获取会员信息
     * @param unknown_type $order
     */
    function get_customer(&$order) {
   
        $customer = array(
            'customer_name' => $order['buyer_name'],
            'shop_code' => $order['shop_code'],
            'source' => $order['sale_channel_code'],
            'address' =>$order['receiver_addr'],
            'country' => $order['receiver_country'],
            'province' =>$order['receiver_province'],
            'city' => $order['receiver_city'],
            'district' => empty($order['receiver_district']) ? 0 : $order['receiver_district'],
            'street' => $order['receiver_street'],
            'zipcode' => $order['receiver_zip_code'],
            'tel' => $order['receiver_mobile'],
            'home_tel' =>$order['receiver_phone'],
            'name' => $order['receiver_name'],
            'is_add_time' => date('Y-m-d H:i:s'),
        );

        $ret = load_model("crm/CustomerOptModel")->handle_customer($customer);
        if ($ret['status'] < 1) {
            return $ret;
        }

            $customer_address = load_model('crm/CustomerOptModel')->get_customer_address($ret['data']['customer_address_id']);
           $order['receiver_addr'] = $customer_address['address'];
           $order['receiver_phone'] = $customer_address['home_tel'];
           $order['receiver_name'] = $customer_address['name'];
           $order['receiver_mobile'] = $customer_address['tel'];
           $order['buyer_name'] = load_model('crm/CustomerOptModel')->get_customer_name($ret['data']['customer_code']);
            
            $country = oms_tb_val('base_area', 'name', array('id' => $order['receiver_country']));
            $province = oms_tb_val('base_area', 'name', array('id' => $order['receiver_province']));
            $city = oms_tb_val('base_area', 'name', array('id' =>  $order['receiver_city']));
            $district = oms_tb_val('base_area', 'name', array('id' =>  $order['receiver_district']));
            $street = oms_tb_val('base_area', 'name', array('id' =>  $order['receiver_street']));
            $order['receiver_address'] = $country . ' ' . $province . ' ' . $city . ' ' . $district . ' ' . $street . ' ' .   $order['receiver_addr'];


        $order['customer_code'] = $ret['data']['customer_code'];
        $order['customer_address_id'] = $ret['data']['customer_address_id'];
        return $this->format_ret(1);
        

    }

    /**
     *
     * 获取区域
     * @param unknown_type $area_name
     * @param unknown_type $parent_id
     * @param unknown_type $parent_parent_id
     */
    function get_area($area_name, $parent_id, $parent_parent_id = NULL) {
        if (empty($area_name))
            return '';

        $area_info = '';

        if (!empty($parent_parent_id)) {
            $sql = "select id from base_area where parent_id = '{$parent_parent_id}'";
            $parent_data = $this->db->get_all($sql);

            $parent_list = array();

            foreach ($parent_data as $sub_parent_data) {
                $parent_list[] = $sub_parent_data['id'];
            }

            $parent_str = "'" . implode("','", $parent_list);
        }

        while (mb_strlen($area_name) > 1) {
            if (!empty($parent_id)) {
                $sql = "select id,parent_id,`name` from base_area where parent_id = '{$parent_id}' AND `name` LIKE '%" . $area_name . "%' limit 1";
            } else {
                $sql = "select id,parent_id,`name` from base_area where parent_id IN ($parent_str) AND `name` LIKE '%" . $area_name . "%' limit 1";
            }

            $area_info = $this->db->get_all($sql);

            if (empty($area_info)) {
                $area_name = mb_substr($area_name, 0, -1);
            } else {
                break;
            }
        }

        return $area_info;
    }

    /**
     * 判定导入数据是否有效
     * @param type $row_data 行数据
     * @return true 有效 false 无效
     */
    function is_valid_excel_data($row_data, $key, $is_fenxiao = 'no') {
        $key += 2;
        $i = 0;
        $err = '';
        if ($row_data['deal_code'] == '') {
            $i++;
            $err .= '第' . $key . '行，这一行交易号不能为空；';
            //return $this->format_ret(-1,'not_deal_code','第'.$key.'行，这一行交易号不能为空');
        }
        if ($row_data['shop_code'] == '') {
            $i++;
            $err .= '店铺代码不能为空；';
            //return $this->format_ret(-1,'','店铺代码不能为空');
        }
        if ($row_data['store_code'] == '') {
            $i++;
            $err .= '仓库代码不能为空；';
            //return $this->format_ret(-1,'','仓库代码不能为空');
        }
        if ($row_data['buyer_nick'] == '') {
            $i++;
            $err .= '买家昵称不能为空；';
            //return $this->format_ret(-1,'','买家昵称不能为空');
        }
        if ($row_data['create_time'] == '') {
            $i++;
            $err .= '下单时间不能为空；';
            //return $this->format_ret(-1,'','下单时间不能为空');
        }
        if ($row_data['is_cod'] == '' && $is_fenxiao == 'no') {
            $i++;
            $err .= '是否到付不能为空；';
            //return $this->format_ret(-1,'','是否到付不能为空');
        }
        if ($row_data['pay_time'] == '') {
            $i++;
            $err .= '付款时间不能为空；';
            //return $this->format_ret(-1,'','付款时间不能为空');
        }
        if ($row_data['delivery_time'] == '') {
            $i++;
            $err .= '发货时间不能为空；';
            //return $this->format_ret(-1,'','发货时间不能为空');
        }
        if ($row_data['express_code'] == '') {
            $i++;
            $err .= '配送方式代码不能为空；';
            //return $this->format_ret(-1,'','配送方式代码不能为空');
        }
        if ($row_data['express_no'] == '') {
            $i++;
            $err .= '运单号不能为空；';
            //return $this->format_ret(-1,'','运单号不能为空');
        }
        if ($row_data['receiver_name'] == '') {
            $i++;
            $err .= '收货人不能为空；';
            //return $this->format_ret(-1,'','收货人不能为空');
        }
        if ($row_data['receiver_mobile'] == '') {
            $i++;
            $err .= '手机号不能为空；';
            //return $this->format_ret(-1,'','手机号不能为空');
        }
        if ($row_data['receiver_province'] == '') {
            $i++;
            $err .= '省不能为空；';
            //return $this->format_ret(-1,'','省不能为空');
        }
        if ($row_data['receiver_city'] == '') {
            $i++;
            $err .= '市不能为空；';
            //return $this->format_ret(-1,'','市不能为空');
        }
        if ($row_data['receiver_district'] == '') {
            $i++;
            $err .= '区不能为空；';
            //return $this->format_ret(-1,'','区不能为空');
        }
        if ($row_data['receiver_addr'] == '') {
            $i++;
            $err .= '详细地址不能为空；';
            //return $this->format_ret(-1,'','详细地址不能为空');
        }
        if ($row_data['express_money'] == '') {
            $i++;
            $err .= '运费不能为空；';
            //return $this->format_ret(-1,'','运费不能为空');
        }
        if ($row_data['barcode'] == '') {
            $i++;
            $err .= '商品条码不能为空；';
            //return $this->format_ret(-1,'','商品条码不能为空');
        }
        if ($row_data['num'] == '') {
            $i++;
            $err .= '数量不能为空；';
            //return $this->format_ret(-1,'','数量不能为空');
        }
        if ($row_data['price'] == '') {
            $i++;
            $err .= '单价不能为空；';
            //return $this->format_ret(-1,'','单价不能为空');
        }
        if ($row_data['avg_money'] == '') {
            $i++;
            $err .= '均摊金额不能为空；';
            //return $this->format_ret(-1,'','均摊金额不能为空');
        }
        if ($is_fenxiao == 'yes' && $row_data['fenxiao_code'] == '') {
            $i++;
            $err .= '分销商不能为空；';
            //return $this->format_ret(-1,'','分销商不能为空');
        }
        if (!empty($err)) {
            if (($is_fenxiao == 'yes' || $is_fenxiao == 'no') && $i == 21) {
                return $this->format_ret(-1, '', $key . '行，信息为空。');
            } else {
                return $this->format_ret(-1, '', $err);
            }
        }

//        foreach ($row_data as $sub_row_data) {
//            $sub_row_data = trim($sub_row_data);
//            if ($sub_row_data == '') {
//                return FALSE;
//            }
//        }

        return $this->format_ret(1);
    }

    function write_log($key, $str) {
        // $str_path = 'D:\\xampp\\htdocs\\efast365\\webefast\\uploads\\'.$key.'_'.date("Y-m-d").'.txt';
        //file_put_contents(iconv("utf-8","gb2312",$str_path),date("Y-m-d H:i:s").'---'.$str."\n",FILE_APPEND);
    }

    /**
     * EXCEL导入订单入口
     * @param type $csv_path
     * @return string
     */
    function fx_import_trade_action($csv_path) {
        require_lib('csv_util');
        $exec = new execl_csv();
        $key_arr = array(
            'deal_code',
            'shop_code',
            'fenxiao_code',
            'store_code',
            'buyer_nick',
            'create_time',
            'pay_time',
            'delivery_time',
            'express_code',
            'express_no',
            'receiver_name',
            'receiver_mobile',
            'receiver_province',
            'receiver_city',
            'receiver_district',
            'receiver_addr',
            'express_money',
            'fx_express_money',
            'barcode',
            'num',
            'price',
            'avg_money',
            'fx_amount'
        );

        $csv_data = $exec->read_csv($csv_path, 1, $key_arr, array('shop_code', 'buyer_nick', 'receiver_name', 'receiver_province', 'receiver_city', 'receiver_district', 'receiver_addr'));

        if (count($csv_data) < 1) {
            print_r('没有订单数据数据,请重新整理数据导入');
            die;
        }

        if (count($csv_data) > 10000) {
            print_r('单次最大支持10000条数据导入,已超限，请重新整理数据导入');
            die;
        }

        if (is_array($csv_data) && count($csv_data) > 0) {
            $trades = array();

            $error_row_data = array();

            $last_sell_record = '';
            $error_num = 0;
            $deal_code_by_sell_record_cod = array();
            $login_type = CTX()->get_session('login_type');
            //分销商登录，查询当前登陆分销商编号
            if ($login_type == 2) {
                $user_code = CTX()->get_session('user_code');
                $custom = load_model('base/CustomModel')->get_custom_by_user_code($user_code);
            }
            foreach ($csv_data as $key => $value) {
                //$value['sell_record_code'] = $value['deal_code'];
                $ret = $this->is_valid_excel_data($value, $key, 'yes');
                if ($ret['status'] == 1) {
                    if ($login_type == 2 && $custom['custom_code'] != $value['fenxiao_code']) {
                        ++$error_num;
                        $value['msg'] = '导入的分销商跟当前登录的分销商不一致';
                        $value['not_deal_code'] = 1;
                        $error_row_data[] = $value;
                        continue;
                    }
                    //判断是普通分销还是淘分销订单(分销商和店铺有关联为普通分销，没有就是淘宝分销)
                    $shop = load_model('base/ShopModel')->get_by_code($value['shop_code']);
                    $shop_data = $shop['data'];
                    if (empty($shop_data)) {
                        ++$error_num;
                        $value['msg'] = '店铺代码错误';
                        $value['not_deal_code'] = 1;
                        $error_row_data[] = $value;
                        continue;
                    }
                    if ($shop_data['is_active'] == 0) {
                        ++$error_num;
                        $value['msg'] = '店铺已停用';
                        $value['not_deal_code'] = 1;
                        $error_row_data[] = $value;
                        continue;
                    }
                    $custom_data = load_model('base/CustomModel')->get_by_code($value['fenxiao_code']);
                    if (empty($custom_data['data'])) {
                        ++$error_num;
                        $value['msg'] = ' 分销商代码错误';
                        $value['not_deal_code'] = 1;
                        $error_row_data[] = $value;
                        continue;
                    }
                    if ($custom_data['data']['is_effective'] == 0) {
                        ++$error_num;
                        $value['msg'] = ' 分销商已停用';
                        $value['not_deal_code'] = 1;
                        $error_row_data[] = $value;
                        continue;
                    }
                    if (!empty($shop_data['custom_code']) && $shop_data['entity_type'] == 2) { //判断是否普通分销
                        if ($shop_data['custom_code'] == $value['fenxiao_code']) {
                            $value['is_fenxiao'] = 2; //普通分销
                        } else {
                            ++$error_num;
                            $value['msg'] = '分销商和店铺不匹配';
                            $value['not_deal_code'] = 1;
                            $error_row_data[] = $value;
                            continue;
                        }
                    } else if ($shop_data['fenxiao_status'] == 1 && ($shop_data['sale_channel_code'] == 'taobao' || $shop_data['sale_channel_code'] == 'fenxiao') && $login_type != 2) { //判断是否淘宝分销订单,分销商登录不能导入淘分销订单
                        if ($custom_data['data']['custom_type'] == 'tb_fx') {
                            $value['is_fenxiao'] = 1; //淘宝分销
                        } else {
                            ++$error_num;
                            $value['msg'] = '该订单不是分销订单';
                            $value['not_deal_code'] = 1;
                            $error_row_data[] = $value;
                            continue;
                        }
                    } else {
                        ++$error_num;
                        $value['msg'] = '该订单不是分销订单';
                        $value['not_deal_code'] = 1;
                        $error_row_data[] = $value;
                        continue;
                    }
                    if (!isset($deal_code_by_sell_record_cod[$value['deal_code']])) {
                        $value['sell_record_code'] = load_model('oms/SellRecordModel')->new_code();
                        $arr['sell_record_code'] = $value['sell_record_code'];
                        $arr['is_fenxiao'] = $value['is_fenxiao'];
                        $deal_code_by_sell_record_cod[$value['deal_code']] = $arr;
                    } else {
                        //重复交易号，分销类型/barcode是否重复
                        if ($deal_code_by_sell_record_cod[$value['deal_code']]['is_fenxiao'] == $value['is_fenxiao']) {
                            $value['sell_record_code'] = $deal_code_by_sell_record_cod[$value['deal_code']]['sell_record_code'];
                        } else {
                            ++$error_num;
                            $value['msg'] = '存在重复交易号，不同类型的分销订单';
                            $value['not_deal_code'] = 1;
                            $error_row_data[] = $value;
                            continue;
                        }
                    }
                    $trade = array();
                    $details = array();
                    if (!array_key_exists($value['sell_record_code'], $trades)) {
                        $last_sell_record = $value['sell_record_code'];

                        $trade = $value;
                        $details['barcode'] = $value['barcode'];
                        $details['num'] = $value['num'];
                        $details['price'] = $value['price'];
                        $details['avg_money'] = $value['avg_money'];
                        $details['fx_amount'] = !empty($value['fx_amount']) ? $value['fx_amount'] : 0;
                        $details['trade_price'] = !empty($value['fx_amount']) ? $value['fx_amount'] / $value['num'] : 0;

                        $trade['details'] = array();
                        $trade['details'][$value['barcode']] = $details;

                        $trades[$value['sell_record_code']] = $trade;
                    } else {
                        if (array_key_exists($value['barcode'], $trades[$value['sell_record_code']]['details'])) {
                            unset($trades[$value['sell_record_code']]);
                            $value['msg'] = '表格存在重复数据';
                            $error_row_data[] = $value;
                        } else {
                            $details['barcode'] = $value['barcode'];
                            $details['num'] = $value['num'];
                            $details['price'] = $value['price'];
                            $details['avg_money'] = $value['avg_money'];
                            $details['fx_amount'] = !empty($value['fx_amount']) ? $value['fx_amount'] : 0;
                            $details['trade_price'] = !empty($value['fx_amount']) ? $value['fx_amount'] / $value['num'] : 0;

                            $trades[$value['sell_record_code']]['details'][$value['barcode']] = $details;
                        }
                    }
//                    }
                } else {
                    ++$error_num;
                    $value['msg'] = $ret['message'];
                    if (!empty($ret['data'])) {
                        $value['not_deal_code'] = 1;
                    }
                    $error_row_data[] = $value;
                }
            }
        } else {
            return '没有找到需要导入的订单！<br>';
        }

        $this->order_import($trades, '', $error_row_data);
    }

    /**
     * 已发货普通订单新增
     * @author wmh
     * @date 2017-04-17
     * @param array $params
     * <pre> 
     * @return array 操作结果
     */
    public function api_shipped_order_add($params) {
        return $this->create_order($params, 1);
    }

    /**
     * 已发货分销订单新增
     * @author wmh
     * @date 2017-04-17
     * @param array $params
     * <pre> 
     * @return array 操作结果
     */
    public function api_shipped_fxorder_add($params) {
        return $this->create_order($params, 2);
    }

    private function create_order($params, $type) {
        if (!isset($params['order'])) {
            return $this->format_ret(-10001, array('order'), 'API_RETURN_MESSAGE_10001');
        }
        $order_data = json_decode($params['order'], TRUE);
        if (empty($order_data) || !isset($order_data[0]['deal_code'])) {
            return $this->format_ret(-10005, array('order'), '数据为空或格式有误');
        }
        if (count($order_data) > 100) {
            return $this->format_ret(-1, '', '数据超限，最大支持100条');
        }
        $error = array();
        $order_data = $this->check_params_format($order_data, $type, $error);
        if (empty($order_data['data'])) {
            return $this->format_ret(1, $error);
        }

        //存在的barcode数据
        $barcode_data = load_model('prm/SkuModel')->convert_barcode($order_data['barcode']);
        $barcode_data = $barcode_data['data'];
        $combo_arr = array();
        if (!empty($order_data['combo_barcode'])) {
            $sql_values = array();
            $combo_barcode_str = $this->arr_to_in_sql_value($order_data['combo_barcode'], 'barcode', $sql_values);
            $sql = "SELECT sku AS combo_sku,barcode AS combo_barcode FROM goods_combo_barcode WHERE barcode IN({$combo_barcode_str})";
            $combo_arr = $this->db->get_all($sql, $sql_values);
            $combo_arr = array_column($combo_arr, 'combo_sku', 'combo_barcode');
        }

        $order_data = $order_data['data'];

        $contrast = array('sale_channel_code' => array('sale_channel', '销售平台'), 'shop_code' => array('shop', '店铺'), 'store_code' => array('store', '仓库'), 'express_code' => array('express', '快递'));
        if ($type == 2) {
            $contrast['custom_code'] = array('custom', '分销商');
        }
        foreach ($contrast as $k => $c) {
            $variable = $c[0];
            $$variable = $this->get_base_data($order_data, $k);
//            if (empty($$variable)) {
//                return $this->format_ret(-10002, '', "全部订单的{$c[1]}数据均不存在");
//            }
        }

        foreach ($order_data as $line => $order) {
            $error_temp = array('order_line' => $line, 'deal_code' => $order['deal_code']);
            $sell_record_code = $this->check_record_exists($order['deal_code']);
            if (!empty($sell_record_code)) {
                $error_temp['sell_record_code'] = $sell_record_code;
                $error[] = $this->format_ret(-10003, $error_temp, '订单已存在');
                continue;
            }
            //数据检查
            foreach ($contrast as $k => $c) {
                $ret = $this->contrast_data($order[$k], $$c[0], $k);
                if ($ret['status'] == -1 && $k == 'shop_code') {
                    //店铺不存在则新增
                    $new_shop = array(
                        'shop_code' => $order['shop_code'],
                        'shop_name' => $order['shop_name'],
                        'sale_channel_code' => $order['sale_channel_code'],
                        'shop_user_nick' => $order['shop_nick'],
                        'shop_type' => 0,
                        'is_active' => 1,
                    );
                    if ($type == 2) {
                        $new_shop['entity_type'] = $order['shop_type'] == 1 ? 2 : 0;
                        $new_shop['fenxiao_status'] = $order['is_fenxiao'] == 1 && $order['sale_channel_code'] == 'taobao' ? 1 : 0;
                    }
                    $this->insert_exp('base_shop', $new_shop);

                    $new_shop['status'] = 1;
                    $shop[$order['shop_code']] = $new_shop;

                    if ($order['sale_channel_code'] != 'taobao') {
                        continue;
                    }
                    //新增店铺参数
                    $shop_api = array(
                        'shop_code' => $order['shop_code'],
                        'source' => $order['sale_channel_code'],
                        'api' => json_encode(array('nick' => $order['shop_nick'])),
                        'nick' => $order['shop_nick'],
                    );
                    $this->insert_multi_exp('base_shop_api', array($shop_api), TRUE);

                    continue;
                }
                if ($type == 2 && $ret['status'] == -1 && $k == 'custom_code') {
                    //分销商不存在则新增
                    $new_custom = array(
                        'custom_code' => $order['custom_code'],
                        'custom_name' => $order['custom_name'],
                        'custom_type' => 'tb_fx',
                        'is_effective' => 1,
                    );

                    $ret_custom = $this->insert_exp('base_custom', $new_custom);
                    $new_custom['custom_id'] = $ret_custom['data'];
                    $new_custom['status'] = 1;
                    $custom[$order['custom_code']] = $new_custom;
                    continue;
                }
                if ($ret['status'] < 1) {
                    $error_temp['error_data'] = array($k => $order[$k]);
                    $error[] = $this->format_ret(-10002, $error_temp, $c[1] . $ret['message']);
                    break;
                }
            }
            if (!empty($error_temp['error_data'])) {
                continue;
            }
            /*
              if ($type == 2) {
              $shop_code = $order['shop_code'];
              if ($shop[$shop_code]['entity_type'] != 2 && $shop[$shop_code]['fenxiao_status'] != 1) {
              $error_temp['error_data'] = array('shop_code' => $order['shop_code']);
              $error[] = $this->format_ret(-10002, $error_temp, '不是分销店铺');
              continue;
              }

              $fenxiao_type = 2;
              if ($shop[$shop_code]['sale_channel_code'] == 'taobao' && $shop[$shop_code]['entity_type'] != 2 && $shop[$shop_code]['fenxiao_status'] == 1) {
              $fenxiao_type = 1;
              }

              if ($fenxiao_type == 2 && $shop[$shop_code]['custom_code'] != $order['custom_code']) {
              $error_temp['error_data'] = array('custom_code' => $order['custom_code'], 'shop_code' => $order['shop_code']);
              $error[] = $this->format_ret(-10002, $error_temp, '店铺绑定的分销商和订单分销商不一致');
              continue;
              } else if (($fenxiao_type == 2 && $custom[$order['custom_code']]['custom_type'] != 'pt_fx') || ($fenxiao_type == 1 && $custom[$order['custom_code']]['custom_type'] != 'tb_fx')) {
              $error_temp['error_data'] = array('custom_code' => $order['custom_code'], 'shop_code' => $order['shop_code']);
              $error[] = $this->format_ret(-10002, $error_temp, '店铺和分销商分销类型不一致');
              continue;
              }
              } */

            //数据组装
            $order['sell_record_code'] = load_model('oms/SellRecordModel')->new_code();
            $order['create_time'] = date('Y-m-d H:i:s');
            $goods_num = 0;
            $goods_money = 0;
            $payable_money = empty($order['express_money']) ? 0 : $order['express_money'];
            $fx_payable_money = 0;

			$sys_lof = load_model('prm/GoodsLofModel')->get_sys_lof();
            $sys_lof = $sys_lof['data'];

            $order_detail = array();
            $order_lof = array();
            $detail_line = -1;
            foreach ($order['detail'] as $d) {
                $detail_line++;
                $barcode = strtolower($d['barcode']);
                if (!isset($barcode_data[$barcode])) {
                    $error_temp['detail_line'] = $detail_line;
                    $error_temp['error_data'] = array('barcode' => $d['barcode']);
                    $error[] = $this->format_ret(-10002, $error_temp, '条码不存在');
                    break;
                }
                if (!empty($d['combo_barcode']) && !isset($combo_arr[$d['combo_barcode']])) {
                    $error_temp['detail_line'] = $detail_line;
                    $error_temp['error_data'] = array('combo_barcode' => $d['combo_barcode']);
                    $error[] = $this->format_ret(-10002, $error_temp, '套餐条码不存在');
                    break;
                } else if (isset($d['combo_barcode'])) {
                    $d['combo_sku'] = $combo_arr[$d['combo_barcode']];
                    unset($d['combo_barcode']);
                }

                $sku_data = $barcode_data[$barcode];

                $goods_num += $d['num'];
                $payable_money += $d['payment'];
                $goods_money += $d['payment'];

                $goods = array(
                    'sku' => $sku_data['sku'],
                    'spec1_code' => $sku_data['spec1_code'],
                    'spec2_code' => $sku_data['spec2_code'],
                    'goods_code' => $sku_data['goods_code'],
                );
                $deal_code_arr = explode(',', $order['deal_code']);
                $detail_deal_code = $deal_code_arr[0];
                //明细数据
                $detail_temp = array(
                    'sell_record_code' => $order['sell_record_code'],
                    'deal_code' => $detail_deal_code,
                    'barcode' => $sku_data['barcode'],
                    'goods_price' => $d['price'],
                    'num' => $d['num'],
                    'lock_num' => $d['num'],
                    'avg_money' => $d['payment'],
                    'is_gift' => isset($d['is_gift']) ? $d['is_gift'] : 0,
                    'plan_send_time' => $order['delivery_time'],
                );
                if ($type == 2) {
                    $detail_temp['trade_price'] = $d['fx_price'];
                    $detail_temp['fx_amount'] = $d['fx_price'] * $d['num'];
                    $fx_payable_money += $detail_temp['fx_amount'];
                }
                $k = $goods['sku'] . '_' . $detail_temp['is_gift'];
                if (isset($order_detail[$k])) {
                    $order_detail[$k]['num'] += $detail_temp['num'];
                    $order_detail[$k]['lock_num'] += $detail_temp['lock_num'];
                    $order_detail[$k]['avg_money'] += $detail_temp['avg_money'];
                    if ($type == 2) {
                        $order_detail[$k]['fx_amount'] += $detail_temp['fx_amount'];
                    }
                } else {
                    $order_detail[$k] = array_merge($detail_temp, $goods);
                }
                //批次数据
                $lof_temp = array(
                    'record_type' => 1,
                    'record_code' => $order['sell_record_code'],
                    'deal_code' => $detail_deal_code,
                    'store_code' => $order['store_code'],
                    'lof_no' => $sys_lof['lof_no'],
                    'production_date' => $sys_lof['production_date'],
                    'num' => $d['num'],
                    'occupy_type' => 2,
                    'order_date' => $order['delivery_time'],
                    'create_time' => strtotime($order['create_time']),
                );
                if (isset($order_lof[$k])) {
                    $order_lof[$k]['num'] += $lof_temp['num'];
                } else {
                    $order_lof[$k] = array_merge($detail_temp, $goods);
                }
                $order_lof[$k] = array_merge($lof_temp, $goods);
            }
            if (!empty($error_temp['error_data'])) {
                continue;
            }
            unset($order['detail']);

            if ($order['receiver_country'] == '中国') {
                $order['receiver_country'] = '1';
                $add_arr = get_array_vars($order, array('receiver_province', 'receiver_city', 'receiver_district', 'receiver_street', 'receiver_addr'));
                $ret_addr = load_model('oms/trans_order/AddrCommModel')->match_addr($add_arr);
                if ($ret_addr['status'] < 1) {
                    $addr_err_msg = $ret_addr['message'];
                    if (strpos($addr_err_msg, '省') !== FALSE) {
                        $error_temp['error_data'] = array('receiver_province' => $order['receiver_province']);
                    } else if (strpos($addr_err_msg, '市') !== FALSE) {
                        $error_temp['error_data'] = array('receiver_city' => $order['receiver_city']);
                    }
                    $error[] = $this->format_ret(-10002, $error_temp, $addr_err_msg);
                    continue;
                }
                $add_arr = $ret_addr['data'];

                $order['receiver_address'] = $order['receiver_province'] . ' ' . $order['receiver_city'];
                $order['receiver_address'] .= empty($order['receiver_district']) ? '' : ' ' . $order['receiver_district'];
                $order['receiver_address'] .= empty($order['receiver_street']) ? '' : ' ' . $order['receiver_street'];
                $order['receiver_address'] .= empty($order['receiver_addr']) ? '' : ' ' . $order['receiver_addr'];
                $order['receiver_province'] = $add_arr['receiver_province'];
                $order['receiver_city'] = $add_arr['receiver_city'];
                $order['receiver_district'] = empty($add_arr['receiver_district']) ? 0 : $add_arr['receiver_district'];
                $order['receiver_street'] = empty($add_arr['receiver_street']) ? 0 : $add_arr['receiver_street'];
                $order['receiver_addr'] = empty($add_arr['receiver_addr']) ? '' : $add_arr['receiver_addr'];
            } else {
                $order['receiver_address'] = '海外 海外';
                $order['receiver_country'] = '250';
                $order['receiver_province'] = '250000';
                $order['receiver_city'] = '25000000';
            }

            $order['sale_channel_code'] = $order['sale_channel_code'];
            $order['deal_code_list'] = $order['deal_code'];
            $order['order_status'] = 1;
            $order['shipping_status'] = 4;
            $order['pay_type'] = $order['pay_type'] == 1 ? 'cod' : 'secured';
            $order['pay_code'] = 'alipay';
            $order['pay_status'] = 2;
            $order['receiver_country'] = 1;
            $order['plan_send_time'] = $order['delivery_time'];
            $order['goods_num'] = $goods_num;
            $order['sku_num'] = count(array_unique(array_column($order_detail, 'sku')));
            $order['payable_money'] = $payable_money;
            $order['paid_money'] = $payable_money;
            $order['lock_inv_status'] = 1;
            $order['must_occupy_inv'] = 1;
            $order['delivery_date'] = date('Y-m-d', strtotime($order['delivery_time']));
            $order['record_date'] = $order['delivery_date'];
            $order['is_back'] = 1;
            $order['is_back_time'] = $order['pay_time'];
            $order['check_time'] = $order['pay_time'];
            $order['is_notice_time'] = $order['pay_time'];
            $order['sale_mode'] = 'stock';
            $order['buyer_remark'] = '';
            $order['seller_remark'] = '';
            $order['order_remark'] = '历史订单,新增时间-' . date('Y-m-d H:i:s');

            $buyer_info = $this->get_customer($order);

            if ($type == 2) {
                $custom_data = $custom[$order['custom_code']];
                $order['fenxiao_id'] = $custom_data['custom_id'];
                $order['fenxiao_code'] = $custom_data['custom_code'];
                $order['fenxiao_name'] = $custom_data['custom_name'];
                $order['is_fenxiao'] = 1; //$fenxiao_type;
                $order['fx_payable_money'] = $fx_payable_money;
                $order['is_fx_settlement'] = 1; //已结算
            }
            unset($order['custom_code']);
            $this->begin_trans();
            $ret = $this->insert($order);
            if ($ret['status'] < 1) {
                $this->rollback();
                $error[] = $this->format_ret(-10002, $error_temp, '导入失败');
                continue;
            }
            $ret = $this->insert_multi_duplicate('oms_sell_record_detail', $order_detail, 'deal_code = values(deal_code)');
            if ($ret['status'] < 1) {
                $this->rollback();
                $error[] = $this->format_ret(-10002, $error_temp, '导入失败');
                continue;
            }
			$ret = $this->insert_multi_exp('oms_sell_record_lof', $order_lof);
            if ($ret['status'] < 1) {
                $this->rollback();
                $error[] = $this->format_ret(-10002, $error_temp, '导入失败');
                continue;
            }
            $this->commit();

            //回写oms_sell_record_lof表明细id
            $sql = 'UPDATE oms_sell_record_lof AS rl,oms_sell_record_detail AS rd SET rl.p_detail_id=rd.sell_record_detail_id WHERE rl.record_code=:record_code AND rl.record_code=rd.sell_record_code AND rl.sku=rd.sku';
            $ret = $this->query($sql, array(':record_code' => $order['sell_record_code']));
            $obj_action = load_model('oms/SellRecordActionModel');
            $obj_action->record_log_check = 0;
            $msg = '普通订单';
            if ($type == 2) {
//                $msg = $fenxiao_type == 1 ? '淘分销订单' : '普通分销订单';
                $msg = '淘分销订单';
            }
            $obj_action->add_action($order['sell_record_code'], '新增', '接口新增历史已发货' . $msg);
            $obj_action->record_log_check = 1;
            $error_temp['sell_record_code'] = $order['sell_record_code'];
            $error[] = $this->format_ret(1, $error_temp, '新增成功');
        }

        return $this->format_ret(1, $error);
    }

    private function check_record_exists($deal_code) {
        $sql = 'SELECT sell_record_code FROM oms_sell_record WHERE deal_code=:deal_code';
        return $this->db->get_value($sql, array(':deal_code' => $deal_code));
    }

    private function get_base_data(&$data, $field) {
        $sql_values = array();
        $field_data = array_unique(array_column($data, $field));
        if (empty($field_data)) {
            return array();
        }
        $field_data_str = $this->arr_to_in_sql_value($field_data, $field, $sql_values);

        $type_arr = array(
            'sale_channel_code' => array('table' => 'base_sale_channel', 'field' => 'sale_channel_code,1 AS `status`'),
            'shop_code' => array('table' => 'base_shop', 'field' => 'shop_code,sale_channel_code,fenxiao_status,entity_type,custom_code,is_active AS `status`'),
            'store_code' => array('table' => 'base_store', 'field' => 'store_code,`status`'),
            'express_code' => array('table' => 'base_express', 'field' => 'express_code,`status`'),
            'custom_code' => array('table' => 'base_custom', 'field' => 'custom_id,custom_code,custom_name,custom_type,shop_code,is_effective AS status')
        );
        $sql = "SELECT {$type_arr[$field]['field']} FROM {$type_arr[$field]['table']} WHERE {$field} IN({$field_data_str})";
        $base_data = $this->db->get_all($sql, $sql_values);
        $base_data = load_model('util/ViewUtilModel')->get_map_arr($base_data, $field);
        return $base_data;
    }

    /**
     * 检查参数格式
     * @param array $data
     * @param int $import_type 1-普通订单导入;2-分销订单导入
     * @return array 结果集
     */
    private function check_params_format($data, $import_type, &$error) {
        $key_required = array(
            's' => array('deal_code', 'sale_channel_code', 'shop_code', 'shop_name', 'shop_nick', 'store_code', 'buyer_name', 'record_time', 'delivery_time', 'express_code', 'express_no', 'receiver_name', 'receiver_mobile', 'detail'),
            'i' => array('pay_type')
        );
        $key_option = array(
            's' => array('pay_time', 'receiver_country', 'receiver_district', 'receiver_street', 'receiver_addr', 'pay_code', 'alipay_no'),
            'i' => array('express_money')
        );
        $detail_required = array(
            's' => array('barcode'),
            'i' => array('num', 'price', 'payment')
        );
        $detail_option = array(
            's' => array('combo_barcode'),
            'i' => array('is_gift')
        );
        if ($import_type == 2) {
            $key_required['s'][] = 'custom_code';
            $key_required['s'][] = 'custom_name';
            $key_required['i'][] = 'shop_type';
            $key_required['i'][] = 'is_fenxiao';
            $key_required['i'][] = 'fx_express_money';
            $detail_required['i'][] = 'fx_price';
        }
        $new_data = array();
        $barcode_arr = array();
        $combo_barcode = array();
        $order_line = -1;
        foreach ($data as $val) {
            $r_required = array(); //主信息必填项
            $r_option = array(); //主信息选填项

            $order_line++;
            $error_temp = array('deal_code' => $val['deal_code'], 'order_line' => $order_line);

            if (!isset($val['receiver_country']) || (isset($val['receiver_country']) && $val['receiver_country'] == '中国')) {
                $val['receiver_country'] = '中国';
                $key_required['s'] = array_merge($key_required['s'], array('receiver_province', 'receiver_city'));
            }
            $ret_required = valid_assign_array($val, $key_required, $r_required, TRUE);
            if ($ret_required['status'] !== TRUE) {
                $error_temp['error_data'] = $ret_required['req_empty'];
                $error[] = $this->format_ret(-10001, $error_temp, 'API_RETURN_MESSAGE_10001');
                continue;
            }

            $ret_option = valid_assign_array($val, $key_option, $r_option);
            if (isset($r_option['pay_code']) && !in_array($r_option['pay_code'], array('alipay', 'weixinpay', 'chinabank', 'cod'))) {
                $error_temp['error_data'] = array('pay_code' => $r_option['pay_code']);
                $error[] = $this->format_ret(-10002, $error_temp, '支付方式不存在');
                continue;
            }
            $record = array_merge($r_required, $r_option);

            if (isset($record['receiver_country']) && !in_array($record['receiver_country'], array('中国', '海外'))) {
                $error_temp['error_data'] = array('receiver_country' => $record['receiver_country']);
                $error[] = $this->format_ret(-10001, $error_temp, '国家值必须为中国或海外');
                continue;
            }
            if ($record['pay_type'] == 0 && empty($record['pay_time'])) {
                $error_temp['error_data'] = array('pay_time');
                $error[] = $this->format_ret(-10001, $error_temp, 'API_RETURN_MESSAGE_10001');
                continue;
            }
            if ($record['pay_type'] == 0) {
                $record['pay_time'] = date('Y-m-d H:i:s', strtotime($record['pay_time']));
            } else {
                $record['pay_time'] = '';
            }
            $record['record_time'] = date('Y-m-d H:i:s', strtotime($record['record_time']));
            $record['delivery_time'] = date('Y-m-d H:i:s', strtotime($record['delivery_time']));

            if (!is_array($record['detail'])) {
                $error_temp['error_data'] = array('detail');
                $error[] = $this->format_ret(-10005, $error_temp, '明细数据解析错误');
                continue;
            }

            $detail = array();
            $detail_line = -1;
            foreach ($record['detail'] as $d) {
                $detail_line++;
                $d_required = array();
                $d_option = array();
                $ret_d_required = valid_assign_array($d, $detail_required, $d_required, TRUE);
                if ($ret_d_required['status'] !== TRUE) {
                    $error_temp['detail_line'] = $detail_line;
                    $error_temp['error_data'] = $ret_d_required['req_empty'];
                    $error[] = $this->format_ret(-10001, $error_temp, 'API_RETURN_MESSAGE_10001');
                    break;
                }
                $ret_d_option = valid_assign_array($d, $detail_option, $d_option);
                if (isset($d_option['is_gift']) && !in_array($d_option['is_gift'], array(0, 1))) {
                    $error_temp['detail_line'] = $detail_line;
                    $error_temp['error_data'] = array('is_gift' => $d_option['is_gift']);
                    $error[] = $this->format_ret(-10002, $error_temp, '赠品状态值错误');
                    break;
                }
                $detail[] = array_merge($d_required, $d_option);
            }
            if (!empty($error_temp['error_data'])) {
                continue;
            }
            if (empty($detail)) {
                $error_temp['error_data'] = array('detail');
                $error[] = $this->format_ret(-10001, $error_temp, 'API_RETURN_MESSAGE_10001');
                continue;
            }
            $barcode_arr = array_merge($barcode_arr, array_column($record['detail'], 'barcode'));
            if (!empty($d['combo_barcode'])) {
                $combo_barcode[] = $d['combo_barcode'];
            }
            $record['detail'] = $detail;
            $new_data[$order_line] = $record;
        }
        $barcode_arr = array_unique($barcode_arr);

        return array('data' => $new_data, 'barcode' => $barcode_arr, 'combo_barcode' => $combo_barcode);
    }

    private function contrast_data($value, &$c_data) {
        if (!isset($c_data[$value])) {
            return $this->format_ret(-1, '', '不存在');
        }
        if ($c_data[$value]['status'] != 1) {
            return $this->format_ret(-2, '', '未启用');
        }
        return $this->format_ret(1);
    }
    private function get_goods_out($goods_code,$custom_code){
        $goods_values = array(':goods_code'=>$goods_code);
        $goods_sql = 'select bg.is_custom_money ,group_concat(fas.custom_code) custom_code from base_goods bg
                      left join fx_appoint_goods fas on fas.goods_code = bg.goods_code
                      where bg.goods_code = :goods_code ';
        $goods_data = $this->db->get_row($goods_sql,$goods_values);
        if($goods_data['is_custom_money'] == 0){
            return $this->format_ret(-1,'','商品为非分销商品，不允许导入');
        }elseif($goods_data['custom_code'] != NULL && !in_array($custom_code,explode(',',$goods_data['custom_code']))){
            return $this->format_ret(-1,'','订单所属分销商非该分销商品指定分销商');
        }
        return $this->format_ret(1);
    }

}
