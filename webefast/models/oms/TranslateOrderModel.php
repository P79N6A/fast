<?php

/**
 * 转单操作
 * 2015/1/21
 * @author jia.ceng
 */
require_model('tb/TbModel');
require_lang("oms");
class TranslateOrderModel extends TbModel {

    public $taobao_nick_map;
    public $shop_code_priv_arr;
    public $express_code_priv_arr;
    public $sell_record_data = array();
    public $sell_record_mx_data = array();
    public $import_flag = 0;
    public $is_cli_trans = 0; //如果是后台转单，这个设为1，这里生成的订单是非锁定状态
    public $cur_trans_user;
    public $sell_record_op_log = array();
    public $translate_msg = array();
    public $is_buyer_remark_cs = 0;
    private $tag_arr = array();
    private $trade_mx = array();
    private $platform_detail = array();
    private $platform_spec = array();
    private $exec_time = 0;
    private $sys_param = array();
    public $is_settlement = 0;
    public $filter_code = '';
    private $crm_customer_address = array();
    private $invoice_info = array();
    private $incr_service = [];
    function __construct() {
        parent::__construct();
        $this->exec_time = $this->msectime();
        $this->get_sys_param_cfg();
        $this->get_incr_service();
        $this->filter_code = $this->get_filter_code();
    }

    //当前转单的用户

    function get_cur_trans_user() {
        if (!isset($this->cur_trans_user)) {
            $this->cur_trans_user = CTX()->get_session('user_name');
        }
        return $this->cur_trans_user;
    }

    //计算均摊金额
    function payment_ft($trade_ft_money, $goods, $is_fx = 0) {

        //print_R($trade_ft_money);die;

        $items_count = count($goods);
        if ($items_count < 1) {
            return $this->put_error(-1, '均摊缺少明细数据');
        }

        $total_ft_ed = 0; //已经分摊掉的金额
        $total_goods_payment = 0; //所有商品明细的总额
        foreach ($goods as $k => &$sub_goods) {
            if ($is_fx == 1) {
                $sub_goods['payment2'] = $sub_goods['payment1'];
            } else {
                $sub_goods['payment2'] = $sub_goods['payment'];
            }
            $total_goods_payment += $sub_goods['payment2'];
        }

        foreach ($goods as $k => $data) {
            if ($items_count != 1) {
                $cur_ft = $trade_ft_money * ($data['payment2'] / $total_goods_payment);
                $cur_ft = floor($cur_ft * 100) / 100;
                $total_ft_ed = bcadd($total_ft_ed, $cur_ft, 2);
                $items_count--;
            } else {
                $cur_ft = bcsub($trade_ft_money, $total_ft_ed, 2);

                $total_ft_ed = bcadd($total_ft_ed, $cur_ft, 2);
            }
            $goods[$k]['avg_money'] = $cur_ft;
        }

        if (bccomp($total_ft_ed, $trade_ft_money, 2) != 0) {
            return $this->put_error(-1, '均摊的数据总和验证失败');
        }

        return $goods;
    }

    /**
     * 转单主方法
     * @param type $tids 可以是单个交易号，也可以是数组
     */
    function translate_fenxiao_order($tids) {

        //$sys_cfg = load_model('sys/SysParamsModel')->get_val_by_code('online_date');
        $online_date = date('Y-m-d H:i:s', strtotime($this->sys_param['online_date']));


        $err_arr = array();
        if (empty($tids)) {
            return $this->format_ret(-1, '', '请指定要转单的交易号');
        }
        if (is_array($tids)) {
            $tid_arr = $tids;
        } else {
            $tid_arr = array($tids);
        }
        $tid_list = "'" . join("','", $tid_arr) . "'";

        $sql = "select fenxiao_id as tid,is_invo,distributor_username as fenxiao_name,supplier_from as source,shop_code,status,case pay_type when 'cod' then 1 else 0 END as pay_type,pay_time,supplier_username as seller_nick,receiver_name as buyer_nick,receiver_name,'中国' as receiver_country,receiver_state as receiver_province,receiver_city,receiver_district,receiver_address as receiver_addr,receiver_zip as receiver_zip_code,receiver_mobile_phone as receiver_mobile,IFNULL(receiver_phone,'') as receiver_phone,memo as buyer_remark,order_message,supplier_memo,supplier_flag as seller_flag,logistics_id as express_no,if(trade_type='AGENT',buyer_payment,distributor_payment) as order_money,post_fee as fx_express_money,0 as express_money,alipay_no,is_change,created as order_first_insert_time,trade_type,customer_address_id,customer_code  from api_taobao_fx_trade where fenxiao_id in($tid_list)";

        $db_order = ctx()->db->get_all($sql);
        //存在风险
        $sql = "select 'taobao' as source,oid as detail_id,fenxiao_id as tid,fenxiao_oid as oid,num,distributor_payment as payment,buyer_payment as payment1,sku_id,item_outer_id,if(sku_outer_id='' or sku_outer_id is null,item_outer_id,sku_outer_id ) as goods_barcode,sku_properties,price,snapshot_url as pic_path from api_taobao_fx_order where fenxiao_id in($tid_list) ";
        $db_order_detail = ctx()->db->get_all($sql);

        //获取店铺信息
        $shop_code_arr = array_column($db_order, 'shop_code');
        $shop_data = $this->get_shop_entity_type($shop_code_arr);
        $shop_info_arr = load_model('util/ViewUtilModel')->get_map_arr($shop_data, 'shop_code');
        $order_arr = load_model('util/ViewUtilModel')->get_map_arr($db_order, 'tid');
        $order_detail_arr = load_model('util/ViewUtilModel')->get_map_arr($db_order_detail, 'source,tid', 1);

        //判断参数是否开启买家留言匹配
        //$param_code = 'buyer_remark';
        //$ret = load_model('sys/SysParamsModel')->get_val_by_code($param_code);
        if ($this->sys_param['buyer_remark'] == 1) {
            $this->is_buyer_remark_cs = 1;
        }
        foreach ($tid_arr as $tid) {
            $find_order = isset($order_arr[$tid]) ? $order_arr[$tid] : null;
            if (empty($find_order)) {
                $err_arr[] = array('status' => -3, 'message' => $tid . ' 找不到此交易号');
                continue;
            }
            $ks = $find_order['source'] . ',' . $find_order['tid'];
            $find_order_detail = isset($order_detail_arr[$ks]) ? $order_detail_arr[$ks] : null;
            if (empty($find_order_detail)) {
                $err_arr[] = array('status' => -4, 'message' => $tid . ' 找不到此交易号的商品明细');
                continue;
            }
            if ($find_order['is_invo'] != 1) {
                $err_arr[] = array('status' => -2, 'message' => $tid . ' 交易号不允许转单');
                continue;
            }
            if ($find_order['is_change'] > 0) {
                $err_arr[] = array('status' => -5, 'message' => $tid . ' 交易号已转单');
                continue;
            }
            //如果是经销单卖家运费等于分销商运费
            if ($find_order['trade_type'] != 'AGENT') {
                $find_order['express_money'] = $find_order['fx_express_money'];
            }
            //订单的时间要比系统上线时间大，用 下单时间
            $_order_time = $find_order['order_first_insert_time'];
            $_order_time_tips = '下单时间';
            if ($_order_time < $online_date) {
                $err_arr[] = array('status' => -7, 'message' => "{$tid}的 {$_order_time_tips} 小于 {$online_date}(系统上线时间)");
                continue;
            }

            $find_order['receiver_address'] = $find_order['receiver_province'] . ' ' . $find_order['receiver_city'] . ' ' . $find_order['receiver_district'] . ' ' . $find_order['receiver_addr'];
            //过滤地址中的特殊字符(主要过滤水平和垂直制表符，双引号，换行符,`)
            $specialchars = "\"\n\b`";
            $chars = '/[' . $specialchars . ']/u';
            $find_order['receiver_address'] = preg_replace($chars, '', $find_order['receiver_address']);
            $find_order['receiver_addr'] = preg_replace($chars, '', $find_order['receiver_addr']);

            //分销订单过滤品牌
            if (!empty($this->filter_code) && $shop_info_arr[$find_order['shop_code']]['entity_type'] == 2) {
                $filter_detail_money = array();
                $detail_id = array();
                foreach ($find_order_detail as $key => $detail) {
                    $is_filter = $this->check_is_filter($detail['sku_properties'], $this->filter_code);
                    if ($is_filter == 0) {
                        $detail_id[] = $detail['detail_id'];
                        $filter_detail_money[] = ($find_order['trade_type'] == 'AGENT') ? $detail['payment1'] : $detail['payment'];
                        unset($find_order_detail[$key]);
                    }
                }
                //更新过滤状态
                if (!empty($detail_id)) {
                    $sql_values = array();
                    $detail_id_str = $this->arr_to_in_sql_value($detail_id, 'oid', $sql_values);
                    $sql = "UPDATE api_taobao_fx_order SET is_filter=1 WHERE oid IN ({$detail_id_str}) ";
                    $this->query($sql, $sql_values);
                }
                if (empty($find_order_detail)) {
                    $err_arr[] = array('status' => -8, 'message' => $tid . ' 此交易号的商品无品牌权限');
                    //日志
                    $this->set_tran_result($tid, 1, '', '此交易号的商品无品牌权限', 1);
                    continue;
                }
                //处理主单金额,减去未过滤品牌的金额
                if (!empty($filter_detail_money)) {
                    $filter_detail_money_all = array_sum($filter_detail_money);
                    $find_order['order_money'] = $find_order['order_money'] - $filter_detail_money_all;
                }
            }
            $api_data = $find_order;
            //print_r($find_order);die;
            //计算均摊金额
            $total_payment = $find_order['order_money'] - $find_order['express_money'];

            $is_fx = ($find_order['trade_type'] == 'AGENT') ? 1 : 0;

            $order_detail = $this->payment_ft($total_payment, $find_order_detail, $is_fx);
            $detail_arr = array();
            foreach ($order_detail as $val) {
                if (array_key_exists($val['goods_barcode'], $detail_arr)) {
                    $detail_arr[$val['goods_barcode']]['num'] += (int) $val['num'];
                    $detail_arr[$val['goods_barcode']]['avg_money'] += (float) $val['avg_money'];
                    $detail_arr[$val['goods_barcode']]['payment'] += (float) $val['payment'];
                    continue;
                }
                $detail_arr[$val['goods_barcode']] = $val;
            }
            $order_detail = array();
            foreach ($detail_arr as $val) {
                $order_detail[] = $val;
            }
            $api_data['mx'] = $order_detail;
            $api_data['is_fenxiao'] = 1;
            //商家备注
            if (!empty($api_data['order_message'])) {
                $api_data['seller_remark'] = $api_data['order_message'];
            }
            if (!empty($api_data['supplier_memo'])) {
                $api_data['seller_remark'] .= "," . $api_data['supplier_memo'];
                $api_data['seller_remark'] = trim($api_data['seller_remark'], ',');
            }
            //分销转单解密
            $ret_check_encrypt = $this->check_encrypt_order($api_data);

            if ($ret_check_encrypt['status'] < 0) {
                $err_arr[] = array('status' => -11, 'message' => $tid .$ret_check_encrypt['message']);
                $this->set_tran_result($tid, $ret_check_encrypt['status'], '', $ret_check_encrypt['message'], 1);
                continue;
            }

            //转单主方法
            $ret = $this->translate_order_by_data($api_data);

            if ($ret['status'] < 0 && $ret['status'] != -10) {
                $err_arr[] = $ret;
                $this->set_tran_result($tid, $ret['status'], '', $ret['message'], 1);
            } elseif ($ret['status'] == -10) {
                $err_arr[] = $ret;
                $this->set_tran_result($tid, 1, '', $ret['message'], 1);
            } else {
                $success_arr[] = $ret;
                $this->set_tran_result($tid, 1, $ret['data'], '', 1);
            }
        }
        if (is_array($tids)) {
            $result = array('success' => $success_arr, 'err' => $err_arr);
        } else {
            if (empty($err_arr)) {
                $result = $success_arr[0];
            } else {
                $result = $err_arr[0];
            }
        }
        return $result;
    }

    /**
     * 转单主方法
     * @param type $tids 可以是单个交易号，也可以是数组
     */
    function translate_order($tids) {

        //$sys_cfg = load_model('sys/SysParamsModel')->get_val_by_code('online_date');
        $online_date = date('Y-m-d H:i:s', strtotime($this->sys_param['online_date']));


        $success_arr = array();
        $err_arr = array();
        if (empty($tids)) {
            return $this->format_ret(-1, '', '请指定要转单的交易号');
        }
        if (is_array($tids)) {
            $tid_arr = $tids;
        } else {
            $tid_arr = array($tids);
        }
        $tid_list = "'" . join("','", $tid_arr) . "'";
        $sql = "select tid,source,shop_code,status,pay_type,pay_code,pay_time,seller_nick,buyer_nick,receiver_name,receiver_country,receiver_province,receiver_city,receiver_district,receiver_street,receiver_address,receiver_addr,receiver_zip_code,receiver_mobile,receiver_phone,receiver_email,express_code,express_no,buyer_remark,seller_remark,seller_flag,order_money,express_money,alipay_no,integral_change_money,coupon_change_money,balance_change_money,invoice_type,invoice_title,invoice_content,invoice_money,invoice_pay_type,is_change,seller_nick,delivery_money,order_first_insert_time,api_data,buyer_alipay_no,sale_mode,customer_address_id,customer_code,taxpayers_code from api_order where tid in($tid_list)";

        $db_order = ctx()->db->get_all($sql);
        // sku_properties,num,sku_id
        $sql = "select source,tid,oid,return_status,sum(num) as num,sku_id,goods_barcode,sum(avg_money) as avg_money,sku_properties,price,pic_path,sale_mode,is_gift from api_order_detail where tid in($tid_list) group by goods_barcode,tid"; //
        $db_order_detail = ctx()->db->get_all($sql);
        //获取店铺信息
        $shop_code_arr = array_column($db_order, 'shop_code');
        $shop_data = $this->get_shop_entity_type($shop_code_arr);
        $shop_info_arr = load_model('util/ViewUtilModel')->get_map_arr($shop_data, 'shop_code');
        $order_arr = load_model('util/ViewUtilModel')->get_map_arr($db_order, 'tid');
        $order_detail_arr = load_model('util/ViewUtilModel')->get_map_arr($db_order_detail, 'source,tid', 1);
        //判断参数是否开启买家留言匹配
        //$param_code = 'buyer_remark';
        //$ret = load_model('sys/SysParamsModel')->get_val_by_code($param_code);
        if ($this->sys_param['buyer_remark'] == 1) {
            $this->is_buyer_remark_cs = 1;
        }
        foreach ($tid_arr as $tid) {
            $find_order = isset($order_arr[$tid]) ? $order_arr[$tid] : null;
            if (empty($find_order)) {
                $err_arr[] = array('status' => -3, 'message' => $tid . ' 找不到此交易号');
                continue;
            }
            $ks = $find_order['source'] . ',' . $find_order['tid'];
            $find_order_detail = isset($order_detail_arr[$ks]) ? $order_detail_arr[$ks] : null;
            if (empty($find_order_detail)) {
                $err_arr[] = array('status' => -4, 'message' => $tid . ' 找不到此交易号的商品明细');
                continue;
            }
            if ($find_order['status'] != 1) {
                $err_arr[] = array('status' => -2, 'message' => $tid . ' 交易号不允许转单');
                continue;
            }
            if ($find_order['is_change'] > 0) {
                $err_arr[] = array('status' => -5, 'message' => $tid . ' 交易号已转单');
                continue;
            }
            if ($find_order['source'] == 'taobao') {
                $ret = $this->check_app_nick($find_order['shop_code'], $find_order['seller_nick']);
                if ($ret['status'] < 0) {
                    $err_arr[] = array('status' => -6, 'message' => $ret['message']);
                    continue;
                }
            }
            //订单的时间要比系统上线时间大，用 下单时间
            $_order_time = $find_order['order_first_insert_time'];
            $_order_time_tips = '下单时间';
            if ($_order_time < $online_date) {
                $err_arr[] = array('status' => -7, 'message' => "{$tid}的 {$_order_time_tips} 小于 {$online_date}(系统上线时间)");
                continue;
            }

            //过滤地址中的特殊字符(主要过滤水平和垂直制表符，双引号，换行符,`)
            $specialchars = "\"\n\b`";
            $chars = '/[' . $specialchars . ']/u';
            $find_order['receiver_address'] = preg_replace($chars, '', $find_order['receiver_address']);
            $find_order['receiver_addr'] = preg_replace($chars, '', $find_order['receiver_addr']);

            // $find_order['receiver_district'] = !empty($find_order['receiver_district']) ? $find_order['receiver_district'] : 0;
            //分销订单过滤品牌
            if (!empty($this->filter_code) && ($shop_info_arr[$find_order['shop_code']]['entity_type'] == 2)) {
                $filter_detail_money = array();
                $oid = array();
                foreach ($find_order_detail as $key => $datail) {
                    $is_filter = $this->check_is_filter($datail['sku_properties'], $this->filter_code);
                    if ($is_filter == 0) {
                        $oid[] = $datail['oid'];
                        $filter_detail_money[] = $datail['avg_money'];
                        unset($find_order_detail[$key]);
                    }
                }
                //打上过滤标识
                if (!empty($oid)) {
                    $sql_value_oid = array();
                    $oid_str = $this->arr_to_in_sql_value($oid, 'oid', $sql_value_oid);
                    $sql = "UPDATE api_order_detail SET is_filter=1 WHERE oid IN ({$oid_str})";
                    $this->query($sql, $sql_value_oid);
                    //更新代销标识
                    $this->update_exp('api_order', array('is_daixiao' => 1), array('tid' => $tid));
                }
                if (empty($find_order_detail)) {
                    $err_arr[] = array('status' => -8, 'message' => $tid . ' 此交易号订单为分销订单，无授权品牌明细！');
                    //增加错误日志
                    $this->set_tran_result($tid, 1, '', '此交易号订单为分销订单，无授权品牌明细！');
                    continue;
                }
                //处理金额
                if (!empty($filter_detail_money)) {
                    $avg_money_all = array_sum($filter_detail_money);
                    $find_order['order_money'] = $find_order['order_money'] - $avg_money_all;
                    $find_order['order_money'] = $find_order['order_money'] - $find_order['express_money'];
                    $find_order['express_money'] = 0;
                }
            }


            $api_data = $find_order;
            $api_data['mx'] = $find_order_detail;
            //加上解密方法
            //    receiver_mobile
            //自动检查是否加密

            $ret_check_encrypt = $this->check_encrypt_order($api_data);
            if ($ret_check_encrypt['status'] < 0) {
                return    array('success' => array(), 'err' => array($ret_check_encrypt));
             //   return $ret_check_encrypt;
            }

            $ret = $this->translate_order_by_data($api_data);


            if ($ret['status'] < 0 && $ret['status'] != -10) {
                $err_arr[] = $ret;
                $this->set_tran_result($tid, $ret['status'], '', $ret['message']);
            } elseif ($ret['status'] == -10) {
                $err_arr[] = $ret;
                $this->set_tran_result($tid, 1, '', $ret['message']);
            } else {
                $success_arr[] = $ret;
                $this->set_tran_result($tid, 1, $ret['data']);
            }
        }
        if (is_array($tids)) {
            $result = array('success' => $success_arr, 'err' => $err_arr);
        } else {
            if (empty($err_arr)) {
                $result = $success_arr[0];
            } else {
                $result = $err_arr[0];
            }
        }
        return $result;
    }

    //验证nick是否匹配，防session关联错了,只处理TAOBAO来源的
    function check_app_nick($shop_code, $sell_nick) {
        if (empty($shop_code) || empty($sell_nick)) {
            return $this->format_ret(-1, '', '店铺代码 和 卖家呢称 不能为空');
        }
        if (!isset($this->taobao_nick_map)) {
            $sql = "select shop_code,api from base_shop_api where source = 'taobao'";
            $db_api = ctx()->db->get_all($sql);
            $api_data = array();
            foreach ($db_api as $sub_api) {
                $_json_api = json_decode($sub_api['api'], true);
                $api_data[$sub_api['shop_code']] = isset($_json_api['nick']) ? $_json_api['nick'] : '';
            }
            $this->taobao_nick_map = $api_data;
        }
        $find_v = isset($this->taobao_nick_map[$shop_code]) ? $this->taobao_nick_map[$shop_code] : null;
        if (empty($find_v)) {
            return $this->format_ret(-1, '', '店铺代码 和 卖家呢称 不匹配');
        }
        return $this->format_ret(1, $find_v);
    }

    //设置转单结果
    function set_tran_result($tid, $is_change, $sell_record_code = '', $change_remark = '', $is_fenxiao = 0) {
        $is_change = $is_change <= 0 ? -1 : $is_change;
        $up = array(
            'change_remark' => $change_remark,
            'is_change' => $is_change,
        );
        if (!empty($sell_record_code)) {
            $up['sell_record_code'] = $sell_record_code;
        }
        if ($is_fenxiao == 0) {
            $ret = load_model("api/OrderModel")->update($up, array('tid' => $tid));
        } else {
            $ret = load_model("api/FxTaobaoTradeModel")->update($up, array('fenxiao_id' => $tid));
        }

        return $ret;
    }

    //检测交易号已转单
    function check_deal_code_exists($tid) {
        //如果已转单过,不要再转
        $sql = "select sell_record_code from oms_sell_record_detail where deal_code='{$tid}'";
        $row = ctx()->db->get_row($sql);
        if (!empty($row)) {
            $up_data = array(
                'sell_record_code' => $row['sell_record_code'],
                'is_change' => 1,
            );
            $this->update_exp('api_order', $up_data, " tid='{$tid}' ");

            return $this->format_ret(-10, $row['sell_record_code'], '订单已存在');
        }
        return $this->format_ret(1);
    }

    //订单信息预检查
    function check_api_data($api_data) {
        /*
          order_money 订单应付款 可能为0
         */
        $check_order = array(
            'tid' => '交易号',
            'shop_code' => '店铺代码',
            'buyer_nick' => '买家昵称',
            'receiver_name' => '收货人',
            'receiver_addr' => '收货地址',
            'receiver_address' => '收货地址-包含省市区',
            'order_first_insert_time' => '下单日期'
        );
        $check_order_detail = array(
            'tid' => '交易号',
            'num' => '商品数量',
            'goods_barcode' => '商品条形码',
        );
        $err_arr = array();
        foreach ($check_order as $_fld => $_fld_name) {
            $_t = !empty($api_data[$_fld]) ? $api_data[$_fld] : null;
            if (empty($_t)) {
                $err_arr[] = $_fld_name;
            }
        }
        $this->platform_detai = array();
        $is_set = false;
        foreach ($check_order_detail as $_fld => $_fld_name) {

            foreach ($api_data['mx'] as $sub_mx) {
                $_t = !empty($sub_mx[$_fld]) ? $sub_mx[$_fld] : null;
                if (empty($_t)) {
                    $err_arr[] = $_fld_name;
                }
                if ($is_set === true) {
                    continue;
                }
                //初始化平台规格
                if (isset($this->platform_detail[$sub_mx['goods_barcode']][$sub_mx['sku_properties']])) {
                    $this->platform_detail[$sub_mx['goods_barcode']][$sub_mx['sku_properties']]['num'] += $sub_mx['num'];
                } else {
                    $this->platform_detail[$sub_mx['goods_barcode']][$sub_mx['sku_properties']] = array(
                        'sku_properties' => $sub_mx['sku_properties'],
                        'sku_id' => $sub_mx['sku_id'],
                        'num' => $sub_mx['num'],
                    );
                }
            }
            $is_set = true;
        }


        if ($api_data['pay_type'] != 0 && empty($api_data['pay_type'])) {
            $err_arr[] = '付款日期';
        }
        if (empty($api_data['receiver_mobile']) && empty($api_data['receiver_phone'])) {
            $err_arr[] = '手机或电话';
        }
        $err_msg = '';
        if (!empty($err_arr)) {
            $err_msg .= join(',', array_unique($err_arr)) . '不能为空';
        }

        $zx_map = array(
            '上海市' => '上海',
            '北京市' => '北京',
            '天津市' => '天津',
            '重庆市' => '重庆'
        );
        if ($api_data['source'] != 'taobao' && isset($zx_map[$api_data['receiver_province']])) {
            $api_data['receiver_province'] = $zx_map[$api_data['receiver_province']];
        }

        $api_data['receiver_province'] = trim($api_data['receiver_province']);
        $api_data['receiver_city'] = trim($api_data['receiver_city']);
        if (!empty($api_data['receiver_address']) && preg_match('/[\x{4e00}-\x{9fa5}]/u', $api_data['receiver_address'])) {
            if (!empty($api_data['receiver_province']) && !empty($api_data['receiver_city'])) {

                if (strpos($api_data['receiver_address'], $api_data['receiver_province']) === false || strpos($api_data['receiver_address'], $api_data['receiver_city']) === false) {
                    $err_msg .= "地址包含省市区的信息有误";
                }
            } else {
                $err_msg .= "收货地址省,收货地址市 不能为空";
            }
        }
        //金额验证
        $total_avg_money = 0;
        foreach ($api_data['mx'] as $sub_mx) {
            $total_avg_money = bcadd($total_avg_money, $sub_mx['avg_money'], 2);
        }
        $total_je = bcadd($total_avg_money, $api_data['express_money'], 2);

        if (bccomp($total_je, $api_data['order_money']) != 0) {
            $err_msg .= "订单金额验证失败";
        }
        if (empty($err_msg)) {
            return $this->format_ret(1);
        } else {
            return $this->format_ret(-20, '', $err_msg);
        }
    }

    //地址匹配
    function match_addr($api_data) {
        //如果是海外地址，不进行地址匹配，但要转成问提单 （$api_data['receiver_address'] 全是非中文）//receiver_country
        if ((!empty($api_data['receiver_address']) && !preg_match('/[\x{4e00}-\x{9fa5}]/u', $api_data['receiver_address'])) || $api_data['receiver_country'] == '海外') {
             if (!empty($this->crm_customer_address)) {
                $this->sell_record_data['receiver_country'] = $this->crm_customer_address['country'];
                $this->sell_record_data['receiver_province'] = $this->crm_customer_address['province'];
                $this->sell_record_data['receiver_city'] = $this->crm_customer_address['city'];
                $this->sell_record_data['receiver_district'] = $this->crm_customer_address['district'];
                $this->sell_record_data['receiver_street'] = $this->crm_customer_address['street'];
                $this->sell_record_data['receiver_addr'] = $this->crm_customer_address['address'];
                $this->sell_record_data['receiver_address'] = $this->crm_customer_address['address_detail'];
                return $this->format_ret(1);
            }
            
            $this->sell_record_data['receiver_country'] = '250';
            $this->sell_record_data['receiver_province'] = '250000';
            $this->sell_record_data['receiver_city'] = '25000000';
            $this->sell_record_data['receiver_district'] = 0;
            $this->sell_record_data['receiver_street'] = '';
            $this->sell_record_data['receiver_address'] = $api_data['receiver_address'];
            $this->sell_record_data['receiver_addr'] = $api_data['receiver_address'];
            
        } else {
            if (!empty($this->crm_customer_address)) {
                $this->sell_record_data['receiver_country'] = $this->crm_customer_address['country'];
                $this->sell_record_data['receiver_province'] = $this->crm_customer_address['province'];
                $this->sell_record_data['receiver_city'] = $this->crm_customer_address['city'];
                $this->sell_record_data['receiver_district'] = $this->crm_customer_address['district'];
                $this->sell_record_data['receiver_street'] = $this->crm_customer_address['street'];
                $this->sell_record_data['receiver_addr'] = $this->crm_customer_address['address'];
                $this->sell_record_data['receiver_address'] = $this->crm_customer_address['address_detail'];
                return $this->format_ret(1);
            }

            //$obj_name = "oms/trans_order/Addr".ucfirst($api_data['source'])."Model";
            //    if(strtolower($api_data['source'])!= 'taobao'){
            $obj_name = "oms/trans_order/AddrCommModel";
            //  }
//            $cls_name = basename($obj_name);
//            $addr_obj = new $cls_name();
            //地址匹配
            $addr_ret = load_model($obj_name)->match_addr($api_data);

            if ($addr_ret['status'] < 0) {
                return $addr_ret;
            }
            $addr_ret = $addr_ret['data'];
            if (empty($addr_ret['receiver_province']) || empty($addr_ret['receiver_city'])) {
                return $this->format_ret(-30, '', '地址匹配找不到省市信息');
            }
            $this->sell_record_data['receiver_country'] = $addr_ret['receiver_country'];
            $this->sell_record_data['receiver_province'] = $addr_ret['receiver_province'];
            $this->sell_record_data['receiver_city'] = $addr_ret['receiver_city'];
            $this->sell_record_data['receiver_district'] = !empty($addr_ret['receiver_district']) ? $addr_ret['receiver_district'] : 0;
            $this->sell_record_data['receiver_street'] = isset($addr_ret['receiver_street']) ? $addr_ret['receiver_street'] : '';



            //todo: 需要完善
            $this->sell_record_data['receiver_addr'] = $addr_ret['receiver_addr'];
            $this->sell_record_data['receiver_address'] = $addr_ret['receiver_address'];
        }
        return $this->format_ret(1);
    }

    //验证当前用户是否有这个店铺的转单权限
    function check_user_shop_priv($shop_code) {
        if (!isset($this->shop_code_priv_arr)) {
            $ret = load_model('base/ShopModel')->get_purview_shop();
            $shop_code_arr = array();
            foreach ($ret as $sub_ret) {
                $shop_code_arr[] = $sub_ret['shop_code'];
            }
            $this->shop_code_priv_arr = $shop_code_arr;
        }
        if (in_array($shop_code, $this->shop_code_priv_arr)) {
            return $this->format_ret(1);
        } else {
            return $this->format_ret(-20, '', "店铺代码{$shop_code}不存在或没权限");
        }
    }

    //如果中间表存在 $express_code,则验证这个CODE是否在系统中存在并启用
    function check_express_code($express_code) {
        if (!isset($this->express_code_priv_arr)) {
            $sql = "select express_code,express_name from base_express where status = 1";
            $db_express = ctx()->db->get_all($sql);
            $express_code_arr = array();
            foreach ($db_express as $sub_express) {
                $express_code_arr[] = $sub_express['express_code'];
            }
            $this->express_code_priv_arr = $express_code_arr;
        }
        if (in_array($express_code, $this->express_code_priv_arr)) {
            return $this->format_ret(1);
        } else {
            return $this->format_ret(-20, '', "配送方式代码{$express_code}不存在或没权限");
        }
    }

    //快递匹配
    function match_express($api_data) {
        //如果是京东COD的

        if ($api_data['source'] == 'jingdong' && $api_data['pay_type'] == 1) {
            $this->sell_record_data['express_code'] = 'JDCOD';
            return $this->format_ret(1);
        }

        if (!empty($api_data['express_code'])) {
            $ret = $this->check_express_code($api_data['express_code']);

            if ($ret['status'] < 0) {
                return $ret;
            }
            $this->sell_record_data['express_code'] = $api_data['express_code'];
            return $this->format_ret(1);
        }

        /** 会员指定快递适配 */
        if ($this->sell_record_data['buyer_name'] && !empty($this->sell_record_data['buyer_name'])) {
            $sale_channel_code_arr = array('taobao','fenxiao');
            $buyer_name = $this->sell_record_data['buyer_name'];
           if(!empty($api_data['customer_code'])&&  in_array($api_data['source'],$sale_channel_code_arr)){
                $buyer_name = load_model('crm/CustomerOptModel')->get_buyer_name_by_code($api_data['customer_code']);
           }

            $return = load_model('crm/OpExpressByUserModel')->parse($buyer_name);
            if ($return['status'] == 1) {
                $this->sell_record_data['express_code'] = $return['data'];
                return $this->format_ret(1);
            }
        }

        //根据指定商品匹配指定快递
        $ret = load_model('crm/OpExpressByGoodsModel')->get_express_by_sku($this->sell_record_mx_data);

        if (empty($ret['data'])) {
            //根据买家留言识别
            $is_find = 0;
            if (trim($api_data['buyer_remark']) != '') {
                //判断有没有开启买家留言匹配
                if ($this->is_buyer_remark_cs == 1) {
                    $ret = load_model('crm/OpExpressByBuyerRemarkModel')->get_express_by_buyer_remark($api_data['buyer_remark']);
                    $is_find = empty($ret['data']) ? 0 : 1;
                }
            }
            if ($is_find == 0) {

                $api_data['receiver_city'] = $this->sell_record_data['receiver_city'];
                $api_data['receiver_district'] = $this->sell_record_data['receiver_district'];


                $ret = load_model('op/PolicyExpressModel')->parse($this->sell_record_data, $this->sell_record_mx_data);

                if ($ret['status'] < 0) {
                    return $this->format_ret(-30, '', "快递匹配失败" . $ret['message']);
                }
            }
        }
        $this->sell_record_data['express_code'] = $ret['data'];
        return $this->format_ret(1);
    }

    //仓库匹配
    function match_store(&$api_data) {
        if (isset($api_data['store_code']) && !empty($api_data['store_code'])) {
            $this->sell_record_data['store_code'] = $api_data['store_code'];
            return $this->format_ret(1);
        }


        $ret = load_model('op/PolicyStoreOpModel')->set_plicy_store_code($this->sell_record_data, $this->sell_record_mx_data);

        return $ret;
    }

    //条码匹配 同时生成订单明细
    function match_barcode($api_data, $type = '') {
        //条码转成小写
        $api_data_mx = &$api_data['mx'];
        array_walk($api_data_mx, function (&$val) {
            $val['goods_barcode'] = strtolower($val['goods_barcode']);
        });
        //合并明细中相同条码的数据
        $api_mx = array(); //已存在的明细条码
        foreach ($api_data_mx as $mx) {
            $goods_barcode = $mx['goods_barcode'];
            if (isset($api_mx[$goods_barcode])) {
                $api_mx[$goods_barcode]['num'] += $mx['num'];
                $api_mx[$goods_barcode]['avg_money'] += $mx['avg_money'];
            } else {
                $api_mx[$goods_barcode] = $mx;
            }
        }
        //获取条码识别后的二维数组，平台条码为键
        $barcode_list = array_column($api_data['mx'], 'goods_barcode');
        $sku_data = load_model('prm/SkuModel')->convert_barcode($barcode_list);
        $sku_arr = $sku_data['data'];
        //明细处理
        $barcode_arr = array();
        foreach ($sku_arr as $key => $row) {
            $row_barcode = strtolower($row['barcode']); //转换后的系统条形码
            $k = 0;
            foreach ($api_mx as $key1 => $row1) {
                //若平台条码不同，但转换后的系统条码在明细中存在，合并数据后删掉平台订单明细中的识别前的条码数据$key（国标码、子条码）
                if ($key != $key1 && $row_barcode == $row1['goods_barcode']) {
                    $k = 1;
                    $api_mx[$key1]['num'] += $api_mx[$key]['num'];
                    $api_mx[$key1]['avg_money'] += $api_mx[$key]['avg_money'];
                }
            }

            if ($k == 0) {
                //识别后明细中还是不存在的条码数据
                $barcode['goods_barcode'] = $row_barcode;
                $barcode['goods_barcode_child'] = $barcode['goods_barcode'];
                $api_mx[$key] = array_merge($api_mx[$key], $barcode);
            } else if ($k == 1) {
                //识别后明细中存在则合并数据，然后删除识别前的条码数据
                unset($api_mx[$key]);
            }

            $barcode_arr[$row_barcode] = $row;
        }

        $this->trade_mx = array();
        $gb_barcode_arr = $sku_data['gb_data'];
        $child_barcode_arr = $sku_data['child_data'];
        $combo_barcode_arr = array();
        unset($sku_arr, $sku_data);

        if (count($api_mx) > count($barcode_arr)) {
            //套餐应用
            $ret = load_model("prm/GoodsComboOpModel")->set_split_combo($api_mx, $barcode_arr);
            $combo_barcode_arr = $ret['data'];
            if ($ret['status'] < 1) {
                return $ret;
            }
        }

        $detail = array();
        foreach ($api_mx as $sub_mx) {
            $find_barcode = isset($barcode_arr[$sub_mx['goods_barcode']]) ? $barcode_arr[$sub_mx['goods_barcode']] : null;
            if (empty($find_barcode)) {
                return $this->format_ret(-50, '', "商品条形码{$sub_mx['goods_barcode']}不存在");
            }
            $new_platform_spec = array();
            if ($sub_mx['num'] > 1) {
                if (isset($sub_mx['goods_barcode_child'])) {
                    $platform_spec = $this->get_platform_spec($sub_mx['goods_barcode_child'], $api_data['tid'], $sub_mx['source']);
                } else {
                    $platform_spec = $this->get_platform_spec($sub_mx['goods_barcode'], $api_data['tid'], $sub_mx['source']);
                }

                foreach ($platform_spec as $spec) {
                    $new_platform_spec[] = $spec['sku_properties'] . '(' . $spec['num'] . '件)';
                }
            } else {
                if ($sub_mx['source'] == 'taobao') {
                    $new_platform_spec = array($sub_mx['sku_properties'] . '(1件)');
                }
                if ($sub_mx['source'] == 'jingdong' || $sub_mx['source'] == 'yihaodian') {
                    $sql = "select sku_properties_name from api_goods_sku where sku_id = '" . $sub_mx['sku_id'] . "' and source = '{$sub_mx['source']}'";
                    $ret = $this->db->get_row($sql);
                    $new_platform_spec = array($ret['sku_properties_name'] . '(1件)');
                }
            }
            
            //平台商品名称
            $new_platform_name = array();
            if(!empty($sub_mx['title'])){
                $new_platform_name[] = $sub_mx['title'];
            }else{
                $goods_barcode = isset($sub_mx['goods_barcode_child'])&&!empty($sub_mx['goods_barcode_child'])?$sub_mx['goods_barcode_child']:$sub_mx['goods_barcode'];
                $new_platform_name = $this->get_platform_name($sub_mx['goods_barcode_child'], $api_data['tid'], $sub_mx['source']);
            }
   
            $combo_num = isset($sub_mx['combo_num']) ? $sub_mx['combo_num'] : 0;
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($find_barcode['sku'], array('cost_price'));
            $is_gift = isset($sub_mx['is_gift']) ? $sub_mx['is_gift'] : 0;
            $arr = array(
                'deal_code' => $api_data['tid'],
                'sub_deal_code' => isset($sub_mx['oid']) && !empty($sub_mx['oid']) ? $sub_mx['oid'] : '',
                'goods_price' => $this->get_goods_price($sub_mx['price'], $sub_mx['goods_barcode']),
                // 'cost_price'=>  $this->get_cost_price($sub_mx['goods_barcode']),
                'cost_price' => $sku_info['cost_price'],
                'num' => $sub_mx['num'],
                'sku_id' => (string) $sub_mx['sku_id'],
                'avg_money' => $sub_mx['avg_money'],
                'platform_spec' => implode(";", $new_platform_spec),
                'platform_name' => implode(";", $new_platform_name),
                'pic_path' => (string) $sub_mx['pic_path'],
                'barcode' => $sub_mx['goods_barcode'],
                'sku' => $find_barcode['sku'],
                'goods_code' => $find_barcode['goods_code'],
                'combo_sku' => isset($find_barcode['combo_sku']) ? $find_barcode['combo_sku'] : '',
                'lock_num' => 0,
                'is_gift' => $is_gift,
                'sku_properties' => $sub_mx['sku_properties'],
                'combo_num' => $combo_num, //套餐数量
                'sale_mode' => isset($sub_mx['sale_mode']) ? $sub_mx['sale_mode'] : 'stock', //预售
            );
            //导入分销订单（普通、淘宝）
            if ($type == 'import_fx' && !empty($api_data['is_fenxiao']) && ($api_data['is_fenxiao'] == 1 || $api_data['is_fenxiao'] == 2)) {
                $arr['fx_amount'] = (float) $sub_mx['fx_amount'];
                $arr['trade_price'] = (float) $sub_mx['trade_price'];
            }
            //淘分销转单
            if ($api_data['source'] == 'taobao' && !empty($api_data['fenxiao_name']) && $type != 'import_fx') {
                $arr['fx_amount'] = (float) $sub_mx['payment'];
                $arr['trade_price'] = (float) $sub_mx['payment'] / $sub_mx['num'];
            }
            //获取分销商店
            $sql = "SELECT entity_type,custom_code,sale_channel_code FROM base_shop WHERE shop_code = '{$api_data['shop_code']}'";
            $shop_data = $this->db->get_row($sql);
            //不是分销订单，但是是分销店铺订单，计算明细分销价格    //普通分销转单或者导入分销单（普通）结算单价为空,计算结算价格 //价格为零不计算，为空计算系统价格
            if (($shop_data['entity_type'] == 2 && $type != 'import_fx') || ($type == 'import_fx' && $api_data['is_fenxiao'] == 2 && $sub_mx['trade_price'] == '')) {
                // && empty($api_data['fenxiao_name']) && empty($api_data['is_fenxiao'])
                //区分淘分销和普通分销的时间字段
                $fx_adjust_check_date = $api_data['order_first_insert_time'];
                $price = load_model('fx/GoodsManageModel')->compute_fx_price($shop_data['custom_code'], $arr, $fx_adjust_check_date);
                $arr['trade_price'] = $price;
                $arr['fx_amount'] = $price * $sub_mx['num'];
            }

            $detail[] = $arr;
            if ($shop_data['entity_type'] == 2 || $type == 'import_fx' || ($api_data['source'] == 'taobao' && !empty($api_data['fenxiao_name']))) {
                $this->translate_msg[] = '' . $sub_mx['goods_barcode'] . "：数量:{$sub_mx['num']},均摊价格:{$sub_mx['avg_money']},结算金额：{$arr['fx_amount']},结算单价:{$arr['trade_price']}";
            } else {
                $this->translate_msg[] = '' . $sub_mx['goods_barcode'] . "：数量:{$sub_mx['num']},均摊价格:{$sub_mx['avg_money']}";
            }
        }

        $this->sell_record_mx_data = $detail;

        //整理原始数据转换，赠品策略使用
        if (!empty($combo_barcode_arr) || !empty($child_barcode_arr)) {
            foreach ($api_data['mx'] as $val) {
                $mx = array();

                if (isset($barcode_arr[$val['goods_barcode']])) {
                    $mx = $barcode_arr[$val['goods_barcode']];
                    $mx['num'] = $val['num'];
                    $mx['avg_money'] = $val['avg_money'];
                } else if (isset($combo_barcode_arr[$val['goods_barcode']])) {
                    $mx = $combo_barcode_arr[$val['goods_barcode']];
                    $mx['num'] = $val['num'];
                    $mx['avg_money'] = $val['avg_money'];
                } else if ($child_barcode_arr[$val['goods_barcode']]) {
                    $mx = $child_barcode_arr[$val['goods_barcode']];
                    $mx['num'] = $val['num'];
                    $mx['avg_money'] = $val['avg_money'];
                } else if ($gb_barcode_arr[$val['goods_barcode']]) {
                    $mx = $gb_barcode_arr[$val['goods_barcode']];
                    $mx['num'] = $val['num'];
                    $mx['avg_money'] = $val['avg_money'];
                }
                if (empty($mx)) {
                    return $this->format_ret(-1, '', '条码解析异常');
                }
                $this->trade_mx[] = $mx;
            }
        }

        return $this->format_ret(1);
    }

    //子条码查询
    public function get_barcode_child(&$api_mx, &$barcode_arr) {
        $no_find = array();
        foreach ($api_mx as $key1 => $sub_mx) {
            $find_barcode = isset($barcode_arr[$sub_mx['goods_barcode']]) ? $barcode_arr[$sub_mx['goods_barcode']] : null;
            if (empty($find_barcode)) {
                $no_find[$key1] = $sub_mx;
            }
        }
        $db_barcode_child_arr = array();
        foreach ($no_find as $key => $barcode) {
            $sql = "select goods_code,sku,barcode from goods_barcode_child where barcode = '" . $barcode['goods_barcode'] . "'";
            $db_barcode_child = ctx()->db->get_row($sql);
            if (empty($db_barcode_child)) {
                continue;
            }
            $sql = "select goods_code,sku,barcode,spec1_code,spec2_code  from goods_barcode where sku = '" . $db_barcode_child['sku'] . "'";
            $db_barcode = ctx()->db->get_row($sql);

            if (!empty($db_barcode)) {
                $db_barcode_child_arr[$barcode['goods_barcode']] = $db_barcode;
                $k = 1;
                foreach ($api_mx as $key1 => $sub_mx) {
                    if ($sub_mx['goods_barcode'] == $db_barcode['barcode']) {
                        $k++;
                        $api_mx[$key1]['num'] = $barcode['num'] + $api_mx[$key1]['num'];
                        $api_mx[$key1]['avg_money'] = $barcode['avg_money'] + $api_mx[$key1]['avg_money'];
                        break;
                    }
                }
                //未找到
                if ($k == 1) {
                    $barcode['goods_barcode'] = $db_barcode['barcode'];
                    $barcode['goods_barcode_child'] = $barcode['goods_barcode'];
                    $api_mx[$key] = $barcode;
                } else {  //找到相同条码
                    unset($api_mx[$key]);
                }

                $barcode_arr[$db_barcode['barcode']]['goods_code'] = $db_barcode['goods_code'];
                $barcode_arr[$db_barcode['barcode']]['sku'] = $db_barcode['sku'];
                $barcode_arr[$db_barcode['barcode']]['barcode'] = $db_barcode['barcode'];
            }
        }
        return $db_barcode_child_arr;
    }

    function get_platform_spec($goods_barcode, $tid, $source) {

        if (isset($this->platform_detail[$goods_barcode])) {
            $res = $this->platform_detail[$goods_barcode];
        } else {
            $sql = "select sku_properties,num,sku_id from api_order_detail where goods_barcode='$goods_barcode' and tid='$tid'";
            $res = $this->db->get_all($sql);
        }

        if ($source == 'jingdong' || $source == 'yihaodian') {
            foreach ($res as $key => $r) {
                $sql = "select sku_properties_name from api_goods_sku where sku_id = '" . $r['sku_id'] . "' and source = '$source'";
                $ret = $this->db->get_row($sql);
                $res[$key]['sku_properties'] = $ret['sku_properties_name'];
            }
        }
        return $res;
    }
    
    /**
     * 获取平台名称
     * @param type $goods_barcode
     * @param type $tid
     * @param type $source
     * @return type
     */
    function get_platform_name($goods_barcode, $tid) {

   
         $sql = "select title,sku_properties,num,sku_id from api_order_detail where goods_barcode='$goods_barcode' and tid='$tid'";
         $res = $this->db->get_all($sql);
        return array_column($res, 'title');
    }

    private function get_goods_price($price, $barcode) {
        $sql = "SELECT gb.price FROM goods_sku gb WHERE gb.barcode = '{$barcode}'";
        $new_price = $this->db->getOne($sql);
        if (empty($new_price) || $new_price <= 0) {
            $sql = "SELECT bg.sell_price FROM base_goods bg LEFT JOIN goods_barcode gd ON bg.goods_code = gd.goods_code WHERE gd.barcode = '{$barcode}';";
            $new_price = $this->db->getOne($sql);
        }
        return $new_price;
    }

//    private function get_cost_price($barcode) {
//        $sql = "SELECT gb.cost_price FROM goods_sku gb WHERE gb.barcode = '{$barcode}'";
//        $cost_price = $this->db->getOne($sql);
//        if(empty($cost_price) || $cost_price <= 0) {
//         //   $sql = "SELECT gp.cost_price FROM base_goods gp,goods_sku gb WHERE gb.barcode = '{$barcode}' and gb.goods_code = gp.goods_code";
//            $sql = "SELECT gp.cost_price FROM base_goods gp,goods_sku gb WHERE gb.barcode = '{$barcode}' and gb.goods_code = gp.goods_code";
//            $cost_price = $this->db->getOne($sql);
//        }
//        return $cost_price;
//    }
    //创建会员
    function create_customer($type) {

        if (!empty($this->crm_customer_address)) {
            $this->sell_record_data['customer_code'] = $this->crm_customer_address['customer_code'];
            $this->sell_record_data['customer_address_id'] = $this->crm_customer_address['customer_address_id'];

            return $this->format_ret(1);
        }

        $customer = array(
            'customer_name' => $this->sell_record_data['buyer_name'],
            'shop_code' => $this->sell_record_data['shop_code'],
            'source' => $this->sell_record_data['sale_channel_code'],
            'address' => $this->sell_record_data['receiver_addr'],
            'country' => $this->sell_record_data['receiver_country'],
            'province' => $this->sell_record_data['receiver_province'],
            'city' => $this->sell_record_data['receiver_city'],
            'district' => empty($this->sell_record_data['receiver_district']) ? 0 : $this->sell_record_data['receiver_district'],
            'street' => $this->sell_record_data['receiver_street'],
            'zipcode' => $this->sell_record_data['receiver_zip_code'],
            'tel' => $this->sell_record_data['receiver_mobile'],
            'home_tel' => $this->sell_record_data['receiver_phone'],
            'name' => $this->sell_record_data['receiver_name'],
            'is_add_time' => date('Y-m-d H:i:s'),
        );
//        $ret = load_model("crm/CustomerModel")->handle_customer($customer);
        $ret = load_model("crm/CustomerOptModel")->handle_customer($customer);
        if ($ret['status'] < 1) {
            return $ret;
        }
        if ($type == 'import'||$type == 'import_fx') {
            $customer_address = load_model('crm/CustomerOptModel')->get_customer_address($ret['data']['customer_address_id']);
            $this->sell_record_data['receiver_addr'] = $customer_address['address'];
            $this->sell_record_data['receiver_phone'] = $customer_address['home_tel'];
            $this->sell_record_data['receiver_name'] = $customer_address['name'];
            $this->sell_record_data['receiver_mobile'] = $customer_address['tel'];
            $this->sell_record_data['buyer_name'] = load_model('crm/CustomerOptModel')->get_customer_name($ret['data']['customer_code']);
            
            $country = oms_tb_val('base_area', 'name', array('id' =>  $this->sell_record_data['receiver_country']));
            $province = oms_tb_val('base_area', 'name', array('id' =>  $this->sell_record_data['receiver_province']));
            $city = oms_tb_val('base_area', 'name', array('id' =>  $this->sell_record_data['receiver_city']));
            $district = oms_tb_val('base_area', 'name', array('id' =>  $this->sell_record_data['receiver_district']));
            $street = oms_tb_val('base_area', 'name', array('id' =>  $this->sell_record_data['receiver_street']));
            $this->sell_record_data['receiver_address'] = $country . ' ' . $province . ' ' . $city . ' ' . $district . ' ' . $street . ' ' .   $this->sell_record_data['receiver_addr'];

        }
        $this->sell_record_data['customer_code'] = $ret['data']['customer_code'];
        $this->sell_record_data['customer_address_id'] = $ret['data']['customer_address_id'];
        return $ret;
    }

    //生成主单信息，并初始化主单有明细的单号
    function create_sell_record_info($api_data, $type) {
        $new_sell_record_code = load_model('oms/SellRecordModel')->new_code();

        //$pay_type = $api_data['pay_type']=='0'?'secured':'cod';
        if ($api_data['pay_type'] == 1) {
            $pay_type = 'cod';
        } else if ($api_data['pay_type'] == 0) {
            $pay_type = 'secured';
        } else {
            $pay_type = 'nosecured';
        }
        $pay_time = $api_data['pay_time'] == '' ? '0000-00-00 00:00:00' : $api_data['pay_time'];
        $pay_time = $pay_type == 'cod' ? '0000-00-00 00:00:00' : $pay_time;
        $api_data['buyer_remark'] = trim($api_data['buyer_remark']);
        $api_data['seller_remark'] = isset($api_data['seller_remark']) ? trim($api_data['seller_remark']) : '';
        $paid_money = $api_data['pay_type'] == '0' ? $api_data['order_money'] : 0;
        //判断是否开启计算订单理论重量参数
        //  $sync_status = load_model('sys/SysParamsModel')->get_val_by_code('sum_weight');
          
        $invoice_status =  empty($api_data['invoice_title'])&& empty($api_data['invoice_type']) ? 0 : 1;
        $invoice_type = $invoice_status == 1 ? 'pt_invoice' : '';
        if ($invoice_status == 1) {
            $invoice_type = !empty($api_data['invoice_type']) ? $api_data['invoice_type'] :$invoice_type;
        }
        //根据是否有企业税号 判断发票抬头类型
        $invoice_title_type = 0;//默认为个人
        if(isset($api_data['taxpayers_code']) && !empty($api_data['taxpayers_code'])){
            $invoice_title_type = 1;//1是企业
        } 
        //如果没有发票抬头和发票类型 则抓取系统参数中的发票设置
        if(empty($api_data['invoice_title'])&& empty($api_data['invoice_type'])){ 
            $inv_status = load_model('sys/SysParamsModel')->get_val_by_code('default_invoice');//获取参数   
            if($inv_status['default_invoice'] == '1'){//如果开启参数默认为个人
                $api_data['invoice_title'] = '个人';//发票抬头
                $invoice_title_type = 0;//发票抬头类型 个人
                $invoice_type = load_model('sys/ParamsModel')->get_param_set('default_invoice');//获取发票的data值  发票类型
                $invoice_status = 1;//开票状态：开票
            }
        }
        $record = array(
            'sell_record_code' => $new_sell_record_code,
            'deal_code' => $api_data['tid'],
            'deal_code_list' => $api_data['tid'],
            'sale_channel_code' => $api_data['source'],
            'shop_code' => $api_data['shop_code'],
            'pay_type' => $pay_type,
            'pay_code' => $api_data['pay_type'] == '0' ? 'alipay' : 'cod',
            'pay_time' => $pay_time,
            'record_time' => $api_data['order_first_insert_time'],
            'buyer_name' => $api_data['buyer_nick'],
            'receiver_name' => $api_data['receiver_name'],
            'receiver_zip_code' => empty($api_data['receiver_zip_code']) ? '' : $api_data['receiver_zip_code'],
            'receiver_mobile' => empty($api_data['receiver_mobile']) ? '' : $api_data['receiver_mobile'],
            'receiver_phone' => empty($api_data['receiver_phone']) ? '' : $api_data['receiver_phone'],
            'receiver_email' => empty($api_data['receiver_email']) ? '' : $api_data['receiver_email'],
            'express_no' => empty($api_data['express_no']) ? '' : $api_data['express_no'],
            'buyer_remark' => empty($api_data['buyer_remark']) ? '' : $api_data['buyer_remark'],
            'seller_remark' => empty($api_data['seller_remark']) ? '' : $api_data['seller_remark'],
            'is_buyer_remark' => empty($api_data['buyer_remark']) ? 0 : 1,
            'is_seller_remark' => empty($api_data['seller_remark']) ? 0 : 1,
            'seller_flag' => (int) $api_data['seller_flag'],
            'payable_money' => empty($api_data['order_money']) ? 0 : (float) $api_data['order_money'],
            'express_money' => !empty($api_data['express_money']) ? (float) $api_data['express_money'] : 0,
            'delivery_money' => !empty($api_data['delivery_money']) ? (float) $api_data['delivery_money'] : 0,
            'paid_money' => $paid_money,
            // 'goods_weigh'=>($this->sys_param['sum_weight']==1)?(load_model('op/PolicyExpressModel')->get_record_detail_goods_weigh($this->sell_record_mx_data))*0.001:'',
            'alipay_no' => empty($api_data['alipay_no']) ? '' : $api_data['alipay_no'],
            'invoice_status' => $invoice_status,
            'invoice_type' =>$invoice_type,
            'invoice_title' => empty($api_data['invoice_title']) ? '' : $api_data['invoice_title'],
            'invoice_content' => empty($api_data['invoice_content']) ? '' : $api_data['invoice_content'],
            'invoice_money' => isset($api_data['invoice_money']) ? (float) $api_data['invoice_money'] : 0,
            'create_time' => date('Y-m-d H:i:s'),
            'pay_status' => $api_data['pay_type'] == '1' ? 0 : 2,
            'must_occupy_inv' => '1',
            'fenxiao_name' => empty($api_data['fenxiao_name']) ? '' : $api_data['fenxiao_name'],
            'order_status' => 0,
            'buyer_alipay_no' => isset($api_data['buyer_alipay_no']) ? $api_data['buyer_alipay_no'] : '',
            'sale_mode' => isset($api_data['sale_mode']) ? $api_data['sale_mode'] : 'stock',
            'taxpayers_code' => isset($api_data['taxpayers_code']) ? $api_data['taxpayers_code'] : '',
            'fx_express_money' => !empty($api_data['fx_express_money']) && isset($api_data['fx_express_money']) ? (float) $api_data['fx_express_money'] : 0,
            'invoice_title_type' => $invoice_title_type,
        );



        if ($type != 'import_fx') {
            $record['fx_express_money'] = !empty($api_data['fx_express_money']) && isset($api_data['fx_express_money']) ? (float) $api_data['fx_express_money'] : 0;
        } else {
            $record['fx_express_money'] = isset($api_data['fx_express_money']) && $api_data['fx_express_money'] != '' ? (float) $api_data['fx_express_money'] : '';
        }
        if (isset($api_data['is_fenxiao']) && $api_data['is_fenxiao'] == 1) {
            $record['is_fenxiao'] = 1;
            $record['fenxiao_name'] = !empty($api_data['fenxiao_name']) ? $api_data['fenxiao_name'] : '';
            $record['fenxiao_code'] = !empty($api_data['fenxiao_code']) ? $api_data['fenxiao_code'] : '';
        }
        if (isset($api_data['is_fenxiao']) && $api_data['is_fenxiao'] == 2) {
            $record['is_fenxiao'] = 2;
            $record['fenxiao_name'] = !empty($api_data['fenxiao_name']) ? $api_data['fenxiao_name'] : '';
            $record['fenxiao_code'] = !empty($api_data['fenxiao_code']) ? $api_data['fenxiao_code'] : '';
        }
        //支付方式
        if (!empty($api_data['pay_code'])) {
            $record['pay_code'] = $api_data['pay_code'];
        }
        $record['is_lock'] = 0;
        $record['is_lock_person'] = '';
        $this->sell_record_data = array_merge($this->sell_record_data, $record);

        if (!empty($this->crm_customer_address)) {
            $this->sell_record_data['receiver_mobile'] = $this->crm_customer_address['tel'];
            $this->sell_record_data['receiver_phone'] = $this->crm_customer_address['home_tel'];
            $this->sell_record_data['buyer_name'] = $this->crm_customer_address['customer_name'];
            $this->sell_record_data['receiver_name'] = $this->crm_customer_address['name'];
        }

        foreach ($this->sell_record_mx_data as $k => $row) {
            $this->sell_record_mx_data[$k]['sell_record_code'] = $new_sell_record_code;
        }
        //增加开票金额处理
        $this->get_invoice_money($api_data);
        $this->sell_record_data['coupon_fee'] = $api_data['coupon_fee'];
        $this->sell_record_data['invoice_money'] = $api_data['invoice_money'];

        
        if(isset($this->sell_record_data['taxpayers_code'])&&!empty($this->sell_record_data['taxpayers_code'])){
            $this->invoice_info = array(
                'taxpayers_code'=>$this->sell_record_data['taxpayers_code'],
                'invoice_money'=> $this->sell_record_data['invoice_money'],
                'is_company'=>1,
                'invoice_type'=>$invoice_type,
            );
        }

        
        return $this->format_ret(1);
    }

    
    
    /**
     * 转单主方法
     */
    function translate_order_by_data($api_data, $type = '') {
   
        
        $this->translate_msg = array();
        $this->sell_record_op_log = array();
        $this->sell_record_data = array();
        $this->invoice_info = array();
        //检测交易号是否存在
        if (!$api_data['tid']) {
            return $this->format_ret(-11, $api_data['tid'], '请填写交易单号');
        }
        //检测手机号必填
        if (empty($api_data['receiver_mobile']) && empty($api_data['receiver_phone'])) {
            return $this->format_ret(-12, $api_data['tid'], '手机和电话不能同时为空');
        }

        //付款日期要控制到必填，其中付款日期有两种方式
        //（1）“是否货到付款”为“否”以及为空时必填
        //（2）“是否货到付款”为“是“的时候不必判断付款日期是否填写，导入后值为空
        if ($api_data['pay_type'] == 0) {
            if (!$api_data['pay_time']) {
                return $this->format_ret(-13, $api_data['tid'], '请填写付款时间');
            }
        }
// 		if ($api_data['pay_type'] != '0' || $api_data['pay_type'] <> '') {
// 			if (!$api_data['pay_time']) {
// 				return $this->format_ret(-13,$api_data['tid'],'请填写付款时间');
// 			}
// 		}

        $order_first_insert_time = strtotime($api_data['order_first_insert_time']);
        //判断时间不合法
        if (!$order_first_insert_time || $order_first_insert_time = 0) {
            return $this->format_ret(-14, $api_data['tid'], '下单日期不合法');
        }

        //检测交易号已转单
        $ret = $this->check_deal_code_exists($api_data['tid']);
        if ($ret['status'] < 1) {
            return $ret;
        }

        //验证当前用户是否有这个店铺的转单权限 
        $login_type = CTX()->get_session('login_type'); //分销商登录
        if ($login_type != 2) {//不是分销商登录
            if ($this->is_cli_trans == 0) {
                $ret = $this->check_user_shop_priv($api_data['shop_code']);
                if ($ret['status'] < 1) {
                    return $ret;
                }
            }
        }
        //订单信息预检查
        $ret = $this->check_api_data($api_data);
        if ($ret['status'] < 1) {
            return $ret;
        }
        $this->crm_customer_address = array();
        if (!empty($api_data['customer_address_id'])) {
            $this->crm_customer_address = $this->get_crm_customer_address($api_data['customer_address_id']);
        }

        //地址处理
        $ret = $this->match_addr($api_data);
        if ($ret['status'] < 1) {
            return $ret;
        }

        //条码匹配 同时生成订单明细
        $ret = $this->match_barcode($api_data, $type);
        if ($ret['status'] < 1) {
            return $ret;
        }
        $this->log('match_barcode');

        //唯品会直发,将期望送货时间(字段transport_day)改存到订单备注,增值服务控制
        if($api_data['source'] == 'weipinhui' && $this->incr_service['wph_remark_control'] == TRUE){
            $api_data['order_remark'] = $api_data['buyer_remark'];
            $api_data['buyer_remark'] = '';
        }

        //生成主单信息，并初始化主单有明细的单号
        $ret = $this->create_sell_record_info($api_data, $type);

        if ($ret['status'] < 1) {
            return $ret;
        }
        $this->log('create_sell_record_info');
        //仓库匹配
        $ret = $this->match_store($api_data);
        if ($ret['status'] < 1) {
            return $ret;
        }

        //快递匹配
        $ret = $this->match_express($api_data);
        if ($ret['status'] < 1) {
            return $ret;
        }

        if (isset($api_data['api_data']) && !empty($api_data['api_data'])) {
            $this->check_api_data_content($api_data['api_data']);
        }
        
        if ($api_data['source'] == 'weipinhui' && !empty($api_data['order_remark']) && $this->incr_service['wph_remark_control'] == TRUE) {
            $this->sell_record_data['order_remark'] = empty($this->sell_record_data['order_remark']) ? $api_data['order_remark'] : $this->sell_record_data['order_remark'] . ';' . $api_data['order_remark'];
        }

        //计划发货时间
        if ($this->sys_param['presell_plan'] == 1) {
            $ret = load_model('oms/SellRecordOptModel')->js_plan_send_time_by_presell($this->sell_record_data, $this->sell_record_mx_data);
        } else {
            $ret = load_model('oms/SellRecordOptModel')->js_sell_plan_send_time($this->sell_record_data, $this->sell_record_mx_data);
        }
        if ($ret['status'] < 0) {
            return $ret;
        }
        $this->log('js_sell_plan_send_time');
        $this->sell_record_data = $ret['data'];
        //主单价格
        $ret = load_model('oms/SellRecordOptModel')->js_record_price($this->sell_record_data, $this->sell_record_mx_data, 0, $type);
        if ($ret['status'] < 0) {
            return $ret;
        }

        $this->sell_record_mx_data = $ret['data']['mx'];
        
        unset($ret['data']['mx']);
        $this->sell_record_data = $ret['data'];
        $ret = $this->create_customer($type);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $this->log('create_customer');
        //补邮自动处理
        $is_postage = $this->postage_auto($this->sell_record_mx_data);

        if ($is_postage == 0) {
            //策略
            $this->set_strategy();

            //主单价格
            $ret = load_model('oms/SellRecordOptModel')->js_record_price($this->sell_record_data, $this->sell_record_mx_data);
            if ($ret['status'] < 0) {
                return $ret;
            }
            $this->sell_record_data = $ret['data'];
        }
        $this->log('set_strategy');
        //取小数点后3位
        $goods_weight = (load_model('op/PolicyExpressModel')->get_record_detail_goods_weigh($this->sell_record_mx_data)) * 0.001;
        $this->sell_record_data['goods_weigh'] = ($this->sys_param['sum_weight'] == 1) ? floor($goods_weight * 1000) / 1000 : '';
        //普通分销订单转单、分销订单导入运费为零不重新计算分销运费
        if ((isset($this->sell_record_data['is_fenxiao']) && $this->sell_record_data['is_fenxiao'] == 2 && $type != 'import_fx') ||
                (isset($this->sell_record_data['is_fenxiao']) && $this->sell_record_data['is_fenxiao'] == 2 && $type == 'import_fx' && $this->sell_record_data['fx_express_money'] === '')) {
            $custom_model = load_model('base/CustomModel');
            $custom_data = $custom_model->get_by_code($this->sell_record_data['fenxiao_code']);
            //是否是按重量结算运费，根据商品理论重量计算运费
            if ((int) $custom_data['data']['settlement_method'] == 1) {
                //计算分销运费
                $fx_express_money = load_model('oms/SellRecordCzModel')->get_trade_weigh_express_money($this->sell_record_data, $this->sell_record_data['goods_weigh']);
                $this->sell_record_data['fx_express_money'] = $fx_express_money['data'];
            } else { //固定分销运费
                //设置多快递运费,取已设置的快递运费
                $fx_express_money_data = $custom_model->get_custom_express_row($this->sell_record_data['fenxiao_code'], $this->sell_record_data['express_code']);
                if (!empty($fx_express_money_data)) {
                    $this->sell_record_data['fx_express_money'] = $fx_express_money_data['express_money'];
                } else {
                    $this->sell_record_data['fx_express_money'] = $custom_data['data']['fixed_money'];
                }
            }
        }
        $this->sell_record_data['fx_express_money'] = empty($this->sell_record_data['fx_express_money']) ? 0 : $this->sell_record_data['fx_express_money'];

        //生成订单
        /*
          if ($this->is_cli_trans == 0){
          $add_log_msg = ' '.$this->get_cur_trans_user().'已锁定此订单';
          } */

        $add_log_msg = implode("；", $this->translate_msg);
         $this->db->begin_trans();
        $ret = load_model("oms/SellRecordModel")->add_api_order($this->sell_record_data, $this->sell_record_mx_data, 1, $add_log_msg, $type);
        $this->log('add_api_order');

        if ($ret['status'] < 1) {
            $this->db->rollback();
            return $ret;
        }
        $this->sell_record_data = $ret['data'];

        $new_sell_record_code = $this->sell_record_data['sell_record_code'];
        $sql = "select * from oms_sell_record_detail where sell_record_code='{$new_sell_record_code}'";
        $this->sell_record_mx_data = $this->db->getAll($sql);
        if ($is_postage == 1) {
            $ret = $this->postage_handle($new_sell_record_code);
            load_model('oms/SellSettlementModel')->generate_settlement_data($new_sell_record_code, 1);
            $this->db->commit();
            return $ret;
        }

        $this->save_strategy_info();

        //订单打标签
        $this->add_order_tag($new_sell_record_code, $this->tag_arr);


        //锁定库存调整导致 不能事务嵌套 ，调整逻辑去掉
        $detail_data = $this->sell_record_mx_data;
        $ret_lock = load_model('prm/InvOpLockModel')->check_detail_lock($this->sell_record_data, $detail_data);
        if ($ret_lock['status'] < 1) {
            $this->db->rollback();
            return $ret_lock;
        }

        if (!empty($detail_data)) {

            //商品占用库存
            load_model("oms/SellRecordOptModel")->set_sell_record_is_lock($new_sell_record_code, false);
            //   $lock_ret = load_model("oms/SellRecordOptModel")->lock_detail($this->sell_record_data,$this->sell_record_mx_data);

            $lock_ret = $this->lock_detail($detail_data);

            $this->log('lock_detail');

            if ($lock_ret['status'] < 1 && $lock_ret['status'] != -10) {
                ctx()->db->rollback();
                return $lock_ret;
            }
        }

        $this->set_lock_inv_status($this->sell_record_data['sell_record_code']);


        $tran_order_auto_split = 0;
        //设置要进行缺货拆单的标识
        if ($lock_ret['status'] > 0 && $lock_ret['data'] == 2 && $this->sys_param['tran_order_auto_split'] == 1) {
            $tran_order_auto_split = 1;
        }

        //设问
        $this->sell_record_data['mx'] = $this->sell_record_mx_data;
        $problem_ret = load_model('oms/SellProblemModel')->set_problem($this->sell_record_data);
        if ($problem_ret['status'] < 0) {
            ctx()->db->rollback();
            return $problem_ret;
        }
        if(!empty($this->invoice_info)){
            load_model('oms/SellRecordModel')->insert_vat_invoict($this->sell_record_data['sell_record_code'], $this->invoice_info) ;
        }
        $this->db->commit();
        $fn = 'translate_refund_by_deal_code';
        if ($this->sell_record_data['is_fenxiao'] == 1) { //淘分销设问
            $fn = 'translate_fx_refund_by_deal_code';
        }
        load_model('oms/TranslateRefundModel')->$fn($this->sell_record_data['deal_code']);

        //自动通知配货
        if ($this->sys_param['tran_order_auto_confirm'] == 1 && $tran_order_auto_split == 0) {
            load_model('oms/SellRecordOptModel')->auto_confirm_and_notice($this->sell_record_data['sell_record_code']);
            $this->log('auto_confirm_and_notice');
        }
        if ($this->is_settlement == 1) {
            $ret = load_model("oms/SellRecordOptModel")->opt_settlement_new($this->sell_record_data['sell_record_code'], array('type' => 'auto_trans'));
            if($ret['status'] == -2){
                //记录订单修改地址日志
                load_model("oms/SellRecordModel")->add_action($this->sell_record_data['sell_record_code'], '分销结算失败', $ret['message']);
            }
        }

        $new_record_code = array($this->sell_record_data['sell_record_code']);
        //预售拆单
        if ($this->sell_record_data['sale_mode'] == 'presale' && $this->sys_param['tran_order_auto_presell_split'] == 1) {
            $this->process_auto_presell_split($this->sell_record_data['sell_record_code']);
            $sql = "SELECT sell_record_code FROM oms_sell_record WHERE split_order=:split_order";
            $ret = $this->db->get_col($sql, array(':split_order' => $this->sell_record_data['sell_record_code']));
            if (!empty($ret)) {
                $new_record_code = $ret;
            }
        }

        //分仓发货的缺货拆单的处理
        if ($tran_order_auto_split == 1) {
            foreach ($new_record_code as $code) {
                $this->process_tran_order_auto_split($code, $this->sys_param['tran_order_auto_confirm']);
            }
        }

        return $this->format_ret(1, $new_sell_record_code);
    }

    function set_lock_inv_status($sell_record_code) {
        $sql = " update oms_sell_record_detail set lock_inv_status=1 where sell_record_code='{$sell_record_code}' AND lock_num=num ";
        $this->query($sql);
        $sql = " update oms_sell_record_detail set lock_inv_status=2 where sell_record_code='{$sell_record_code}' AND lock_num>0 AND  lock_inv_status=0 ";
        $this->query($sql);
        $sql = "select sum(lock_num) as lock_num,sum(num) as num from oms_sell_record_detail where sell_record_code='{$sell_record_code}' group by sell_record_code ";
        $data = $this->db->get_row($sql);
        $lock_inv_status = 0;


        if ($data['lock_num'] == $data['num']) {
            $lock_inv_status = 1;
        } else if ($data['lock_num'] < $data['num'] && $data['lock_num'] > 0) {
            $lock_inv_status = 2;
        }

        if ($lock_inv_status > 0) {
            $sql = " update oms_sell_record set lock_inv_status={$lock_inv_status} where sell_record_code='{$sell_record_code}' ";
            $this->query($sql);
        }
    }

    /**
     * 预售拆单
     * @param string $sell_record_code 订单号
     * @return array 结果
     */
    function process_auto_presell_split($sell_record_code) {
        $record = load_model('oms/SellRecordModel')->get_record_by_code($sell_record_code);
        $detail = load_model('oms/SellRecordModel')->get_detail_list_by_code($sell_record_code);
        ctx()->db->begin_trans();
        $ret = load_model('oms/OrderSplitModel')->presell_order_split($record, $detail);
        if ($ret['status'] < 1) {
            ctx()->db->rollback();
            return $ret;
        }
        ctx()->db->commit();
    }

    function process_tran_order_auto_split($sell_record_code, $tran_order_auto_confirm) {
        $record = load_model('oms/SellRecordModel')->get_record_by_code($sell_record_code);
        $detail = load_model('oms/SellRecordModel')->get_detail_list_by_code($sell_record_code);
        ctx()->db->begin_trans();
        $ret = load_model('oms/OrderSplitModel')->divide_store_send($record, $detail);
        //echo '<hr/>$ret<xmp>'.var_export($ret,true).'</xmp>';
        $sell_record_data = array();
        if ($ret['status'] > 0) {
            $sell_record_data = $ret['data'];
            //echo '<hr/>$split_sell_record_data<xmp>'.var_export($sell_record_data,true).'</xmp>';
            foreach ($sell_record_data as $sub_data) {
                $problem_ret = load_model('oms/SellProblemModel')->set_problem($sub_data);
                if ($problem_ret['status'] < 0) {
                    ctx()->db->rollback();
                    return $problem_ret;
                }
            }
        } else {
            ctx()->db->rollback();
            return $ret;
        }
        ctx()->db->commit();

        if ($tran_order_auto_confirm == 1 && !empty($sell_record_data)) {
            foreach ($sell_record_data as $sub_data) {
                $ret = load_model('oms/SellRecordOptModel')->auto_confirm_and_notice($sub_data['sell_record_code']);
                //echo '<hr/>$auto_confirm_and_notice_ret<xmp>'.var_export($ret,true).'</xmp>';
            }
        }
    }

    function lock_detail($detail_data) {

        if (!empty($detail_data)) {
            $detail_data = $this->check_is_inv_out($detail_data);

            $ret = array();
            if (!empty($detail_data)) {
                $ret = load_model("oms/SellRecordOptModel")->lock_detail($this->sell_record_data, $detail_data);
            } else {
                $ret = load_model("oms/SellRecordOptModel")->set_stock_out($this->sell_record_data['sell_record_code']);
            }
        }
        return $ret;
    }

    private function check_is_inv_out($detail_data) {

        $sku_arr = array();


        //锁定单替换锁定

        $new_detail_key = array();
        foreach ($detail_data as $key => &$v) {
            $new_detail_key[$v['sku']][] = $key;
            $sku_arr[] = $v['sku'];
        }

        $sku_str = "'" . implode("','", $sku_arr) . "'";
        $sql = "select stock_num,lock_num,out_num,sku from goods_inv where store_code='{$this->sell_record_data['store_code']}' AND sku in($sku_str)   ";
        $data = $this->db->get_all($sql);
        $out_data = array();
        foreach ($data as $val) {
            $sku = $val['sku'];
            if ($val['out_num'] > 0 && $val['stock_num'] > $val['lock_num']) {
                foreach ($new_detail_key[$sku] as $mx_k) {
                    if (!isset($out_data[$sku])) {
                        $out_data[$sku] = $detail_data[$mx_k];
                        $out_data[$sku]['out_num'] = $detail_data[$mx_k]['num'];
                    } else {
                        $out_data[$sku]['out_num'] += $detail_data[$mx_k]['num'];
                    }
                    unset($detail_data[$mx_k]);
                }
            }
        }

        if (!empty($out_data)) {
            load_model('prm/InvModel')->update_stock_out_inv($out_data, $this->sell_record_data['store_code']);
        }


        if (!empty($out_data)) {
            return array_values($detail_data);
        }
        return $detail_data;
    }

    private function add_order_tag($sell_record_code, $tag_arr) {
        if (!empty($tag_arr)) {
            load_model("oms/SellRecordTagModel")->add_record_tag($sell_record_code, $tag_arr);
        }
    }

    private function check_api_data_content($api_data_content) {
        if (!empty($api_data_content)) {
            $data = json_decode($api_data_content, true);
            $this->sell_record_data['order_remark'] = '';
            //020订单设置订单备注，并打标签
            if (isset($data['o2o_delivery']) && $data['o2o_delivery'] == 'online') {
                $this->sell_record_data['order_remark'] .= isset($data['o2o_shop_name']) ? $data['o2o_shop_name'] : '';
                $this->tag_arr[] = 'O2O';
            }
            if (isset($data['return_order']) && (int) $data['return_order'] > 0) {
                $this->sell_record_data['is_change_record'] = 1;
                $sql = "SELECT sale_channel_name FROM base_sale_channel WHERE sale_channel_code = :sale_channel_code ";
                $name = $this->db->get_value($sql, array(':sale_channel_code' => $this->sell_record_data['sale_channel_code']));
                $this->sell_record_data['order_remark'] .= "此单为" . $name . "{$data['return_order']}单的换货单;";
            }
            if (isset($data['duration'])) {
                $order_remark = '';
                foreach ($data['duration'] as $d) {
                    $order_remark .= '商品条形码' . $d['goods_barcode'] . "，生产周期" . $d['duration'] . '天；';
                }
                $this->sell_record_data['order_remark'] .= $order_remark;
            }
        }
    }

    function cli_trans($other_param = array()) {


        $page_size = 1000;
        $sql = "select count(*) from api_order where status = 1 and is_change = 0";
        if (isset($other_param['shop_code'])) {
            $sql .= " AND shop_code='{$other_param['shop_code']}'";
        }

        $c = ctx()->db->getOne($sql);
        $page_num = ceil($c / $page_size);
        $this->is_cli_trans = 1;
        for ($page_no = 1; $page_no <= $page_num; $page_no++) {
            $this->cli_trans_each($page_size, $other_param);
        }
//                      'is_trans_shop',
//                'trans_shop_max',
    }

    function set_cli_trans_max($request) {
        //is_trans_shop
        //trans_shop_max
        if ($request['type'] == 0) {
            $request['value'] = isset($request['value']) ? $request['value'] : 0;
            $sql = "INSERT INTO `sys_params`  (param_code,parent_code,param_name,type,form_desc,`value`,sort,remark)
                    VALUES ( 'is_trans_shop', 'hidden', '转单是否分店铺多进程', 'radio', '[\"关闭\",\"开启\"]', '{$request['value']}', 1, '1-开启 0-关闭')
                    ON DUPLICATE KEY UPDATE `value`=  VALUES(`value`)  ";
            $this->db->query($sql);
        } else if (isset($request['shop_code']) && isset($request['trans_shop_max'])) {
            $trans_shop_data = array();
            if (isset($this->sys_param['trans_shop_max']) && !empty($this->sys_param['trans_shop_max'])) {
                $trans_shop_data = json_decode($this->sys_param['trans_shop_max'], true);
            }
            $trans_shop_data[$request['shop_code']] = $request['trans_shop_max'];

            $trans_shop_str = json_encode($trans_shop_data);
            $trans_shop_str = addslashes($trans_shop_str);


            $sql = "INSERT INTO `sys_params`  (param_code,parent_code,param_name,type,form_desc,`value`,sort,remark)
                    VALUES ( 'trans_shop_max', 'hidden', '转单是否店铺多进程', 'text', '', '{$trans_shop_str}', 1, '')
                    ON DUPLICATE KEY UPDATE `value`=  VALUES(`value`)  ";
            $this->db->query($sql);
        }
    }

    function cli_trans_exec() {
        $check_num = $this->db->get_value("SELECT count(1) FROM oms_sell_record WHERE (pay_status=2 OR pay_type='cod') AND order_status=0");
        if ($check_num > 100000) {
            return $this->format_ret(-1, '', '待确认订单列表订单数超过100000，不能转单');
        }

        //执行转单
        //trans_shop_max is_trans_shop 参数判断
        if (isset($this->sys_param['is_trans_shop']) && $this->sys_param['is_trans_shop'] == 1) {
            $this->cli_trans_shop();
        } else {
            $this->cli_trans();
        }
    }

    function cli_trans_shop() {

        $sql = "SELECT shop_code from base_shop where is_active=1 ";
        $shop_data = $this->db->get_all($sql);
        foreach ($shop_data as $val) {
            $shop_code = $val['shop_code'];
            $sql_num = "select 1 from api_order where status = 1 and is_change = 0 AND shop_code='{$shop_code}'";
            $num = $this->db->get_value($sql_num);
            if ($num == 1) {
                $this->create_cli_task($shop_code);
            }
        }
    }

    function create_cli_task($shop_code) {
        $trans_shop_param = array();
        if (isset($this->sys_param['trans_shop_max']) && !empty($this->sys_param['trans_shop_max'])) {
            $trans_shop_param = json_decode($this->sys_param['trans_shop_max'], true);
        }
        require_model('common/TaskModel');
        $task = new TaskModel();
        $task_data = array();


        $task_data['code'] = 'cli_trans_' . $shop_code;
        $task_code = 'cli_trans_' . $shop_code;
        $request['app_act'] = 'cli/cli_trans_api_order';
        $request['shop_code'] = $shop_code;

        if (!empty($trans_shop_param) && isset($trans_shop_param[$shop_code]) && $trans_shop_param[$shop_code] > 1) {
            $max = $trans_shop_param[$shop_code];

            for ($i = 0; $i < $max; $i++) {
                $request['app_fmt'] = 'json';
                $task_data['code'] = $task_code . "_" . $i;
                $request['tran_max'] = $max;
                $request['each_num'] = $i;
                $task_data['start_time'] = time();
                $task_data['request'] = $request;
                $task->save_task($task_data);
            }
        } else {
            $request['app_fmt'] = 'json';
            $task_data['start_time'] = time();
            $task_data['request'] = $request;
            $task->save_task($task_data);
        }
    }

    function cli_trans_fenxiao() {
        $page_size = 1000;
        $sql = "select count(*) from api_taobao_fx_trade where is_invo = 1 and is_change = 0 ";
        $c = ctx()->db->getOne($sql);
        $page_num = ceil($c / $page_size);
        $this->is_cli_trans = 1;
        for ($page_no = 1; $page_no <= $page_num; $page_no++) {
            $this->cli_trans_fenxiao_each($page_size);
        }
    }

    function cli_trans_fenxiao_each($batch_num) {
        //$sys_cfg = load_model('sys/SysParamsModel')->get_val_by_code('online_date');
        $online_date = date('Y-m-d H:i:s', strtotime($this->sys_param['online_date']));

        $sql = "select fenxiao_id from api_taobao_fx_trade where is_invo = 1 and is_change = 0   and created>='{$online_date}'";
        $sql .= " order by created asc limit {$batch_num}";
        $db_order = ctx()->db->get_all($sql);
        if (empty($db_order)) {
            return false;
        }
        foreach ($db_order as $sub_order) {
            $_tid = $sub_order['fenxiao_id'];
            $ret = $this->translate_fenxiao_order($_tid);
            if ($ret['status'] < 0) {
                echo "{$_tid} 转单失败 {$ret['message']}\n";
            } else {
                echo "{$_tid} 转单成功 \n";
            }
        }
        return true;
    }

    function cli_trans_each($batch_num, $other_param = array()) {
        //$sys_cfg = load_model('sys/SysParamsModel')->get_val_by_code('online_date');
        // $online_date = date('Y-m-d H:i:s',strtotime($sys_cfg['online_date']));
        $online_time = strtotime($this->sys_param['online_date']);
        // $batch_num = 50;
        //order_first_insert_time_int order_first_insert_time  $online_date
        $sql = "select tid from api_order where status = 1 and is_change = 0  and order_first_insert_time_int>={$online_time} ";

        if (isset($other_param['shop_code'])) {
            $sql .= " AND  shop_code='{$other_param['shop_code']}' ";
        }

        if (isset($other_param['tran_max'])) {
            $tran_max = $other_param['tran_max'];
            $each_num = $other_param['each_num'];
            $sql .= " AND  (id%{$tran_max})={$each_num}";
        }

        $sql .= " order by order_first_insert_time_int asc limit {$batch_num}";
        $db_order = ctx()->db->get_all($sql);
        if (empty($db_order)) {
            return false;
        }


        foreach ($db_order as $sub_order) {
            $_tid = $sub_order['tid'];
            $stime1 = $this->msectime();
            $ret = $this->translate_order($_tid);
            $stime2 = $this->msectime();
            $t = $stime2 - $stime1;
            $this->log($_tid . "end({$t})");
            if ($ret['status'] < 0) {
                echo "{$_tid} 转单失败 {$ret['message']}\n";
            } else {
                echo "{$_tid} 转单成功 \n";
            }
        }
        return true;
    }

    function set_strategy() {
        //赠品策略
        //   $ret = load_model("op/GiftStrategyOpModel")->set_trade_gift($this->sell_record_data,$this->sell_record_mx_data);

        $ret = load_model("op/GiftStrategy/GiftStrategyOpModel")->set_trade_gift($this->sell_record_data, $this->sell_record_mx_data, $this->trade_mx);

        if (!empty($ret['data'])) {
            $this->sell_record_op_log[] = $ret['message'];
        }
    }

    function save_strategy_info() {//保存策略相关日志信息
        $log_data = load_model("op/GiftStrategy/GiftStrategyOpModel")->get_strategy_log();


        //新方法这个要取消 直接用 $log_data
//              if( !empty($op_log_data) ){
//                  $log_data[] = $op_log_data;
//              }


        if (!empty($log_data)) {
            load_model("op/StrategyLogModel")->insert_multi($log_data);
        }

        if (!empty($this->sell_record_op_log)) {
            load_model("oms/SellRecordModel")->add_action($this->sell_record_data['sell_record_code'], '策略', implode(" ", $this->sell_record_op_log));
        }
        $this->sell_record_op_log = array();
    }

    //get_strategy_log
//        function set_new_sell_record_data(){
//
//            $param =  array('sell_record_code'=>$this->sell_record_data['sell_record_code']);
//            $ret = load_model("oms/SellRecordOptModel")->get_row($param);
//            $this->new_sell_record_data = $ret['data'];
//        }
//
    //补邮自动处理
    function postage_auto(&$detail_data) {
        //  $params = load_model('sys/SysParamsModel')->get_val_by_code(array('postage_auto'));
        $is_postage = 0;
        if ($this->sys_param['postage_auto'] == 1) {
            $goods_arr = array();
            foreach ($detail_data as $val) {
                $goods_arr[$val['goods_code']] = 1;
            }
            $goods_arr = array_keys($goods_arr);
                $sql_values = array();
            $goods_str = $this->arr_to_in_sql_value($goods_arr, 'goods_code', $sql_values);
            $sql = "select COUNT(1) as num from base_goods where goods_code in({$goods_str}) AND goods_prop=1";
            $num = $this->db->get_value($sql, $sql_values);
            if ($num == count($goods_arr)) {
                $is_postage = 1;
            }
        }
        return $is_postage;
    }

    function postage_handle($sell_record_code) {

        $record_data = array('order_status' => '1', 'shipping_status' => 4);
        $date = date('Y-m-d H:i:s');
        $record_data['check_time'] = $date;
        $record_data['delivery_time'] = $date;
        $record_data['delivery_date'] = date('Y-m-d');
        $record_data['lock_inv_status'] = 1;
        $this->db->update('oms_sell_record', $record_data, array('sell_record_code' => $sell_record_code));

        $sql = "UPDATE oms_sell_record_detail SET lock_num=num,lock_inv_status=1 WHERE sell_record_code='{$sell_record_code}' ";
        $this->db->query($sql);

        load_model('oms/SellRecordActionModel')->add_action($sell_record_code, '补邮订单处理', '补邮订单，系统自动设置为已发货  不开启，保持现状');


        //补邮订单更新发货单需生成结算单

        return $this->format_ret(1, $sell_record_code);
    }

    function get_sys_param_cfg() {
        $param_code = array(
            'online_date',
            'postage_auto',
            'tran_order_auto_confirm',
            'tran_order_auto_split',
            'tran_order_auto_presell_split',
            'sum_weight',
            'buyer_remark',
            'is_trans_shop',
            'trans_shop_max',
            'presell_plan'
        );
        $this->sys_param = load_model('sys/SysParamsModel')->get_val_by_code($param_code);
    }

    function get_incr_service(){
        $this->incr_service['wph_remark_control'] = load_model('common/ServiceModel')->check_is_auth_by_value('weipinhui_transport_day_control');
    }

    function log($msg) {
//            $now_time = $this->msectime();
//            $c_time = $now_time - $this->exec_time ;
//            $logPath = '/www/webroot/efast365_dev/webefast/logs/oms.log';
//            $info = date("Y-m-d H:i:s")." $msg 耗时：$c_time \n\n";
//            error_log($info, 3, $logPath);
//            $this->exec_time = $now_time;
    }

    function msectime() {
        list($tmp1, $tmp2) = explode(' ', microtime());
        return (float) sprintf('%.0f', (floatval($tmp1) + floatval($tmp2)) * 1000);
    }

    /**
     * 获取过滤字段
     */
    function get_filter_code($filter_obj = 'brand', $filter_type = 0) {
        $sql_value = array();
        $sql = "SELECT filter_code FROM api_order_fx_filter WHERE filter_type=:filter_type AND filter_obj=:filter_obj ";
        $sql_value['filter_type'] = $filter_type;
        $sql_value['filter_obj'] = $filter_obj;
        $filter_code = $this->db->get_value($sql, $sql_value);
        if (!$filter_code) {
            $filter_code = '';
        }
        return $filter_code;
    }

    /**
     * 判断是否过滤品牌
     * @param type $properties_name
     * @return int
     */
    function check_is_filter($properties_name, $filter_code_str) {
        $is_filter = 0;
        $filter_code_arr = explode(';', $filter_code_str);
        foreach ($filter_code_arr as $filter_code) {
            if (strpos($properties_name, $filter_code) !== false) {
                $is_filter = 1;
                break;
            }
        }
        return $is_filter;
    }

    function get_shop_entity_type($shop_code) {
        $shop_code_arr = is_array($shop_code) ? $shop_code : array($shop_code);
        $shop_data = array();
        if(!empty($shop_code_arr)){
            $sql_values = array();
            $shop_code_str = $this->arr_to_in_sql_value($shop_code_arr, 'shop_code', $sql_values);
            $sql = "SELECT entity_type,shop_code FROM base_shop WHERE shop_code IN ({$shop_code_str})";
            $shop_data = $this->db->get_all($sql, $sql_values);
        }
        return $shop_data;
    }

    function get_crm_customer_address($customer_address_id) {
        $sql = "select * from crm_customer_address where customer_address_id = :customer_address_id ";
        $customer_data = $this->db->get_row($sql, array(':customer_address_id' => $customer_address_id));
        if (!empty($customer_data['customer_code'])) {
            $sql = "select customer_name from crm_customer where customer_code = :customer_code ";
            $customer_data['customer_name'] = $this->db->get_value($sql, array(':customer_code' => $customer_data['customer_code']));
        }
        return $customer_data;
    }

    function check_encrypt_order(&$api_data) {
         $sale_channel_code =   $api_data['source']=='fenxiao' ?'taobao':$api_data['source'];   
        $is_encrypt = load_model('sys/security/CustomersSecurityModel')->is_encrypt_sale_channel($sale_channel_code);
        if ($is_encrypt === false) {
        	return $this->format_ret(1);
        }


        //识别手动转单
        if ($api_data['customer_address_id'] == 0 && strlen($api_data['buyer_nick']) > 5) {
            $encryp_data = load_model('sys/security/SysEncrypModel')->get_encrypt_info_by_shop($api_data['shop_code']);
            if (empty($encryp_data)) {
                $is_encryp_value = load_model('sys/security/CustomersSecurityModel')->is_encrypt_value($api_data['buyer_nick'], $api_data['source'],'buyer_nick');
                if ($is_encryp_value) {
                    $ret = load_model('sys/security/SysEncrypModel')->create_shop_encrypt($api_data['shop_code']);
                    if ($ret['status'] < 1) {
                        return $ret;
                    }
                } else {
                    return $this->format_ret(1);
                }
            }
            $is_encryp_value = load_model('sys/security/CustomersSecurityModel')->is_encrypt_value($api_data['buyer_nick'], $api_data['source'],'buyer_nick');
            if($is_encryp_value===false){
                  return $this->format_ret(1);
            }
            $ret_dercypt = load_model('sys/security/CustomersSecurityOptModel')->decrypt_order($api_data);
            if ($ret_dercypt['status'] < 1) {
                return $ret_dercypt;
            }

            $api_data['customer_address_id'] = $ret_dercypt['data']['customer_address_id'];
            $api_data['customer_code'] = $ret_dercypt['data']['customer_code'];
            //可以增加更新这个表
        }
        return $this->format_ret(1);
    }
    
    function get_invoice_money(&$api_data){
            $invoice_money = 0;
            $api_data['coupon_fee'] = 0;
        if($api_data['source']=='jingdong'){
                $sql_jdq = "select order_seller_price,order_payment,balance_used from api_jingdong_trade where order_id=:tid";
                $jd_data = $this->db->get_row($sql_jdq,array(':tid'=>$api_data['tid']));
                if(!empty($jd_data)){
                 $invoice_money = bcadd($jd_data['order_payment'],$jd_data['balance_used'],2);
                }
                
                
                
            }else if($api_data['source']=='taobao'){
            $sql_tb = "select coupon_fee,alipay_point,promotion_details,discount_fee from api_taobao_trade where tid =:tid";
            $tb_data = $this->db->get_row($sql_tb,array(':tid'=>$api_data['tid']));
             $coupon_fee = 0;
              if (!empty($tb_data)) {
                if ($tb_data['coupon_fee'] > 0) {//
                    $coupon_fee+=round($tb_data['coupon_fee'] / 100, 2);
                }
                if ($tb_data['alipay_point'] > 0) {
                    $coupon_fee+=round($tb_data['alipay_point'] / 100, 2);
                }
                if (!empty($tb_data['promotion_details'])) {
                    $discount_fee = $this->get_promotion_details_fee($tb_data['promotion_details']);
                    $coupon_fee+=$discount_fee;
                }
            }
            $api_data['coupon_fee'] = $coupon_fee;
            $invoice_money = bcsub($api_data['order_money'],$coupon_fee,2);

        }
        $api_data['invoice_money'] = $invoice_money;
    }
    
 
        private function get_promotion_details_fee($promotion_details_str) {
        $discount_fee = 0;
        $promotion_details = json_decode($promotion_details_str, true);
         foreach ($promotion_details['promotion_detail'] as $val) {
            if (strpos($val['promotion_name'], '天猫购物券') !== false) {
                $discount_fee = bcadd($discount_fee, $val['discount_fee'],2);
            }
        }
        return $discount_fee;
    }
    public function get_trade_mx(){
        return $this->trade_mx;
    }
}
