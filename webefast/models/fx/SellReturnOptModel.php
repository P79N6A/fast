<?php

require_lib('util/oms_util', true);
require_lib('apiclient/Validation');
require_model('tb/TbModel');
require_model('prm/InvOpModel');
require_lang('fx');

class SellReturnOptModel extends TbModel {

    var $sell_record_info;
    var $sell_record_mx_info;

    /*
     * 从订单生成退单
     * params_info = array()
     * adjust_money 手工调整金额
     * seller_express_money 卖家承担运费金额
     * compensate_money 赔付金额
     * return_reason_code 退货原因CODE
     * return_remark 退单备注
     * return_buyer_memo 退单说明
     * return_pay_code 退款方式
     * return_express_code 买家退货快递公司
     * return_express_no 买家退货快递单号
     * params_info['mx'][] = array();
     * deal_code 交易号
     * sku sku
     * num 数量
     * 实际总退款金额[return_money] = 商品均摊金额(退款)[return_avg_money] + 卖家承担运费[seller_express_money] + 赔付金额[compensate_money] + 手工调整金额[adjust_money]
     * return_type 退单类型 1仅退款 2仅退货 3退款退货
     */

    function create_return($params_info, $sell_record_code, $return_type = 3, $return_store_code = null, $api_return_je = 0, $msg = '', $fx_adjust_check_time = '') {
        //退的明细数据验证及计算
        $check_params_info_ret = $this->check_params_info($params_info, $sell_record_code, $return_type, $fx_adjust_check_time);
        if ($check_params_info_ret['status'] < 0) {
            return $check_params_info_ret;
        }
        $message = '';
        if (isset($params_info['return_type_money']) && $params_info['return_type_money'] == 1) {
            $message = "客户申请‘仅退款’";
            unset($params_info['return_type_money']);
        }

        if (empty($return_store_code)) {
            $ret = $this->get_return_store_code($this->sell_record_info['shop_code']);
            if ($ret['status'] < 0) {
                return $ret;
            }
            $return_store_code = $ret['data'];
        }
        $return_avg_money = 0;
        $new_return_info_mx = $check_params_info_ret['data']['new_return_info_mx'];
        $is_fenxiao = $check_params_info_ret['data']['sell_record_info']['is_fenxiao'];
        if ($is_fenxiao == 1) { //淘分销
            foreach ($new_return_info_mx as $val) {
                $return_avg_money += $val['avg_money'];
            }
        } else {
            $return_avg_money = $check_params_info_ret['data']['return_avg_money'];
        }
        if ($return_type == 1) {
            $params_info['compensate_money'] = isset($params_info['compensate_money']) ? $params_info['compensate_money'] : 0;
            $params_info['compensate_money'] += $api_return_je;
            //批量转退款
            if (!empty($msg)) {
                $return_avg_money = $params_info['return_avg_money'];
                $message = $msg;
            }
        } else {
            if ($api_return_je > 0) {
                $params_info['adjust_money'] = $api_return_je - $return_avg_money;
            }
        }

        /*
          echo '<hr/>$api_return_je<xmp>'.var_export($api_return_je,true).'</xmp>';
          echo '<hr/>$params_info<xmp>'.var_export($params_info,true).'</xmp>';
          die; */
        $sell_record_info = $check_params_info_ret['data']['sell_record_info'];
        //取新退单主表的数据
        $ret = $this->get_new_sell_return($params_info, $sell_record_info, $return_avg_money, $return_type);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $new_return_info = $ret['data'];
        if($is_fenxiao == 2){ // 普通分销
            //计算主表结算金额
            foreach ($new_return_info_mx as $val) {
                $new_return_info['fx_payable_money'] += $val['fx_amount'];
            }
        }
        $new_return_info['return_avg_money'] = empty($new_return_info['return_avg_money']) ? 0 : $new_return_info['return_avg_money'];
        $new_return_info['mx'] = $new_return_info_mx;
        $new_return_info['store_code'] = $return_store_code;
        $new_return_info['is_compensate'] = isset($check_params_info_ret['data']['is_compensate']) ? (int) $check_params_info_ret['data']['is_compensate'] : '';
        $new_return_info['change_express_code'] = $this->sell_record_info['express_code'];
        //生成新退单
        $ret = $this->save_return($new_return_info);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $sell_return_code = $ret['data'];

        $new_return_info['sell_return_code'] = $sell_return_code;
        $new_return_info['return_order_status'] = 0;
        $new_return_info['return_shipping_status'] = 0;
        $new_return_info['finance_check_status'] = 0;

        //如果是发货前，订单挂起，财务结算，自动解挂，退回未确认，订单旧明细作废，生成新明细
        if ($this->sell_record_info['shipping_status'] < 4) {
            $ret = load_model('oms/SellRecordOptModel')->biz_pending('wait_check_refund', $sell_record_code, $this->sell_record_info['store_code']);
            if ($ret['status'] < 0) {
                return $ret;
            }
            $action_note = '生成退款单，订单自动挂起，单号为' . $sell_return_code;
        } else {
            $action_note = '生成退单，单号为' . $sell_return_code;
        }
        load_model('oms/SellRecordModel')->add_action($sell_record_code, '生成退单', $action_note);
        if (isset($check_params_info_ret['data']['return_error']) && !empty($check_params_info_ret['data']['return_error'])) {
            $record_data = $this->db->get_row("select * from oms_sell_return where sell_return_code=:sell_return_code", array(':sell_return_code' => $sell_return_code));
            load_model('fx/SellReturnModel')->add_action($record_data, '生成退单存在明细异常', join(',', $check_params_info_ret['data']['return_error']));
        } else {
            $record_data = $this->db->get_row("select * from oms_sell_return where sell_return_code=:sell_return_code", array(':sell_return_code' => $sell_return_code));
            load_model('fx/SellReturnModel')->add_action($record_data, '生成退单', $message);
        }


        return $this->format_ret(1, $sell_return_code);
    }

    //取退单的退货仓
    function get_return_store_code($shop_code) {
        $sql = "select refund_store_code from base_shop where shop_code = :shop_code";
        $refund_store_code = ctx()->db->getOne($sql, array(':shop_code' => $shop_code));
        if (empty($refund_store_code)) {
            $ret = load_model('sys/SysParamsModel')->get_val_by_code(array('return_default_store_code'));
            $refund_store_code = isset($ret['return_default_store_code']) ? $ret['return_default_store_code'] : 0;
            if (empty($refund_store_code)) {
                return $this->format_ret(-1, '', '找不到退单仓库');
            }
        }
        return $this->format_ret(1, $refund_store_code);
    }

    //获取订单明细已生成的退单的情况
    function get_mx_return_info($sell_record_code) {
        $sql = 'SELECT
                    t.sell_record_code,t.return_shipping_status,mx.deal_code,mx.sku,sum(mx.note_num) as note_num,sum(mx.recv_num) as recv_num
                FROM
                    oms_sell_return t,
                    oms_sell_return_detail mx
                WHERE
                    t.sell_return_code = mx.sell_return_code
                AND t.sell_record_code = :sell_record_code
                and t.return_order_status<>3 group by t.return_shipping_status,t.sell_record_code,mx.deal_code,mx.sku';
        $db_mx = ctx()->db->getAll($sql, array(':sell_record_code' => $sell_record_code));
        $mx_arr = array();
        foreach ($db_mx as $sub_mx) {
            $ks = $sub_mx['deal_code'] . ',' . $sub_mx['sku'];
            $sub_mx['return_num'] = ($sub_mx['return_shipping_status'] == 1) ? $sub_mx['recv_num'] : $sub_mx['note_num'];
            unset($sub_mx['return_shipping_status']);
            if (!isset($mx_arr[$ks])) {
                $mx_arr[$ks] = $sub_mx;
            } else {
                $mx_arr[$ks]['recv_num'] +=$sub_mx['recv_num'];
                $mx_arr[$ks]['note_num'] +=$sub_mx['note_num'];
                $mx_arr[$ks]['return_num'] +=$sub_mx['return_num'];
            }
        }
        return $this->format_ret(1, $mx_arr);
    }

    //验证生成退单参数是否准确
    function check_params_info($params_info, $sell_record_code, $return_type, $fx_adjust_check_time = '') {
        //读取订单
        $this->sell_record_info = $sell_record_info = load_model('oms/SellRecordModel')->get_record_by_code($sell_record_code);
        $is_allowed_exceed = load_model('sys/SysParamsModel')->get_val_by_code(array('is_allowed_exceed'));
        $no_chk_return_info = isset($is_allowed_exceed['is_allowed_exceed']) ? $is_allowed_exceed['is_allowed_exceed'] : 0;

        /* COD的订单发货前 生成应退为0的退款单
         *  COD的订单发货后 生成退款单(赔付) 或 退货单
         *  非COD发货前 生成应退为0的退款单
         *  非COD发货后 生成退款单(赔付) 或 退款退货单
         */
        if ($sell_record_info['pay_type'] == 'cod') {
            if ($sell_record_info['shipping_status'] < 4) {
                if ($return_type <> 1) {
                    return $this->format_ret(-1, '', '货到付款的订单未发货前，只能生成 退款单');
                }
            } else {
                if ($return_type == 3) {
                    return $this->format_ret(-1, '', '货到付款的订单发货后，只能生成 退款单 或 退货单');
                }
//				if ($return_type == 1 && count($params_info['mx'])>0){
//					return $this->format_ret(-1,'','货到付款的订单发货后，生成 退款单 不能有明细商品');
//				}
                if ($return_type == 2 && count($params_info['mx']) == 0) {
                    return $this->format_ret(-1, '', '货到付款的订单发货后，生成 退货单 要有明细商品');
                }
            }
        } else {
            if ($sell_record_info['shipping_status'] < 4) {
                if ($return_type <> 1) {
                    return $this->format_ret(-1, '', '非货到付款的订单未发货前，只能生成 退款单');
                }
            } else {
                if ($return_type == 2) {
                    return $this->format_ret(-1, '', '非货到付款的订单发货后，只能生成 退款单 或 退款退货单');
                }
//				if ($return_type == 1 && count($params_info['mx'])>0){
//					return $this->format_ret(-1,'','非货到付款的订单发货后，生成 退款单 不能有明细商品');
//				}
                if ($return_type == 3 && count($params_info['mx']) == 0) {
                    return $this->format_ret(-1, '', '非货到付款的订单发货后，生成 退款退货单 要有明细商品');
                }
            }
        }

        $db_arr = load_model('oms/SellRecordModel')->get_detail_list_group_by_code($sell_record_code, $sell_record_info['is_fenxiao'],'create_return');



        $mx_arr = array();
        foreach ($db_arr as $sub_mx) {
            $ks = $sub_mx['deal_code'] . ',' . $sub_mx['sku'];
            $mx_arr[$ks] = $sub_mx;
        }
        $this->sell_record_mx_info = $sell_record_mx_info = $mx_arr;

        if (empty($sell_record_info)) {
            return $this->format_ret(-1, '', 'SELL_RECORD_NOT_EXIST');
        }

        //只有未发货的 有效的 非问提单 才能生成退款单
        if ($return_type == 1) {
            if ($sell_record_info['order_status'] == 3 || $sell_record_info['is_problem'] > 0) {
                return $this->format_ret(-1, '', '只有非问提单,有效的订单才能生成退款单');
            }
        } else {
            //只有已发货的订单，才能生成退货单
            if ($sell_record_info['shipping_status'] < 4) {
                return $this->format_ret(-1, '', '只有已发货的才能生成退货单');
            }
        }

        //已退明细
        $ret = $this->get_mx_return_info($sell_record_code);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $sell_return_mx = $ret['data'];
        $new_return_info_mx = array();
        $return_avg_money = 0;
        $_err_arr = array();
        $_err_return = array();
//$return_num = ($_find_row['return_shipping_status'] ==1) ? $_find_row['recv_num']:$_find_row['note_num'];
        //验证订单的明细是否存在,是否有数量可退

        $adjust_money = 0;
        foreach ($params_info['mx'] as $sub_info) {
            $ks = $sub_info['deal_code'] . ',' . $sub_info['sku'];
            $_sell_mx_row = isset($sell_record_mx_info[$ks]) ? $sell_record_mx_info[$ks] : NULL;

//            if (!isset($_sell_mx_row)) {
//                $_err_arr[] = "交易号: {$sub_info['deal_code']} SKU：{$sub_info['sku']} 在订单{$sell_record_code} 明细中不存在";
//                continue;
//            }
//
            if (!isset($_sell_mx_row)) {
//                $_err_arr[] = "交易号: {$sub_info['deal_code']} SKU：{$sub_info['sku']} 在订单{$sell_record_code} 明细中不存在";
//                continue;

                $key_arr = array('goods_code', 'price');
                $sku_info = load_model('goods/SkuCModel')->get_sku_info($sub_info['sku'], $key_arr);

                $_sell_mx_row = array(
                    'sell_record_code' => $sell_record_code,
                    'goods_code' => $sku_info['goods_code'],
                    'deal_code' => $sub_info['deal_code'],
                    'goods_price' => $sku_info['price'],
                    'sku_id' => 0,
                    'sku' => $sub_info['sku'],
                    'num' => $sub_info['return_num'],
                    //    'return_price'=>$sub_info['return_price'],
                    'avg_money' => $_sell_mx_row['avg_money'],
                );

            if(isset($sell_record_info['is_fenxiao']) && $sell_record_info['is_fenxiao'] == 2) { //普通分销订单，没匹配到明细，重新计算结算单价
                $data = array('sku' => $sub_info['sku'],'goods_code' => $sku_info['goods_code']);
                $fx_time = !empty($fx_adjust_check_time) ? $fx_adjust_check_time : time(); //没有传入分销调价单验证时间，取当前时间
                $fx_price = load_model('fx/GoodsManageModel')->compute_fx_price($sell_record_info['fenxiao_code'],$data, $fx_time);
                $_sell_mx_row['trade_price'] = $fx_price;
                $_sell_mx_row['fx_amount'] = $fx_price * $_sell_mx_row['num'];
            } else if(isset($sell_record_info['is_fenxiao']) && $sell_record_info['is_fenxiao'] == 1) { //淘宝分销，没匹配到明晰，取接口的价格
                $_sell_mx_row['trade_price'] = isset($sub_info['refund_price']) && !empty($sub_info['refund_price']) ? $sub_info['refund_price'] : 0;
            }


                $_sell_num = $sub_info['return_num'];
            } else {
                $_sell_num = $_sell_mx_row['num'];
            }
            //订单明细卖出多少件
            //已退数量
            if (isset($sell_return_mx[$ks])) {
                $_return_num = $sell_return_mx[$ks]['return_num'];
            } else {
                $_return_num = 0;
            }

            //当前要求退多少件
            $_request_return_num = @$sub_info['return_num'];

            if ($_sell_num - $_return_num < $_request_return_num && $no_chk_return_info == 1) {
                $_t_num = $_sell_num - $_return_num;
                $_err_return[] = "交易号: {$sub_info['deal_code']} SKU：{$sub_info['sku']} 当前要求退{$_request_return_num}件,只有{$_t_num}件可退";
                continue;
            }


            $_cur_avg_money = $_request_return_num == $_sell_mx_row['num'] ? $_sell_mx_row['avg_money'] : ($_sell_mx_row['avg_money'] / $_sell_mx_row['num']) * $_request_return_num;
            $_cur_avg_money = number_format($_cur_avg_money, 2, '.', '');


            $new_return_info_mx1 = array(
                'sell_record_code' => $_sell_mx_row['sell_record_code'],
                'deal_code' => $sub_info['deal_code'],
                'goods_code' => $_sell_mx_row['goods_code'],
//                'spec1_code' => $_sell_mx_row['spec1_code'],
//                'spec2_code' => $_sell_mx_row['spec2_code'],
                'goods_price' => $_sell_mx_row['goods_price'],
                'sku_id' => $_sell_mx_row['sku_id'],
                'sku' => $_sell_mx_row['sku'],
                //'barcode' => $_sell_mx_row['barcode'],
                'note_num' => $_request_return_num,
//                'recv_num'=>$_request_return_num,//在入库是设置实际退货数量
                'avg_money' => $_cur_avg_money,
            );
            //普通分销订单，重新计算结算单价
            if(isset($sell_record_info['is_fenxiao']) && ($sell_record_info['is_fenxiao'] == 2)) { 
                // 分销单价
                $fx_price = $_sell_mx_row['fx_amount'] / $_sell_num;
                // 退单分销金额
                $new_return_info_mx1['trade_price'] = $fx_price;
                $new_return_info_mx1['fx_amount'] = $fx_price * $_request_return_num;
            } else if(isset($sell_record_info['is_fenxiao']) && $sell_record_info['is_fenxiao'] == 1) {//淘宝分销
                // 退单分销金额
                $new_return_info_mx1['trade_price'] = $_cur_avg_money / $_request_return_num;
                $new_return_info_mx1['fx_amount'] = $_cur_avg_money;
            }
            $new_return_info_mx[] = $new_return_info_mx1;
            $adjust_money_sku = bcsub($sub_info['return_price'], $_cur_avg_money, 2);
            $adjust_money = bcadd($adjust_money, $adjust_money_sku, 2);
            $return_avg_money = bcadd($return_avg_money, $_cur_avg_money, 2);
        }
        /*
          echo '<hr/>$_err_arr<xmp>'.var_export($_err_arr,true).'</xmp>';
          echo '<hr/>$new_return_info_mx<xmp>'.var_export($new_return_info_mx,true).'</xmp>';
          echo '<hr/>$return_avg_money<xmp>'.var_export($return_avg_money,true).'</xmp>';
          die; */
        if (!empty($_err_return)) {
            return $this->format_ret(-2, '', join(' ', $_err_return));
        }

        if (!empty($_err_arr)) {
            //设置成整单退
            $return_avg_money = 0;
            $new_return_info_mx = array();
            foreach ($mx_arr as $new_sub_info) {
                $return_num = $new_sub_info['num'] - $new_sub_info['return_num'];
                if ($return_num > 0) {
                    $_cur_avg_money = ($return_num == $new_sub_info['num']) ? $new_sub_info['avg_money'] : ($new_sub_info['avg_money'] / $new_sub_info['num']) * $return_num;
                    $_cur_avg_money = number_format($_cur_avg_money, 2, '.', '');
                    //refund_price


                    $new_return_info_mx[] = array(
                        'sell_record_code' => $new_sub_info['sell_record_code'],
                        'deal_code' => $new_sub_info['deal_code'],
                        'goods_code' => $new_sub_info['goods_code'],
                        'spec1_code' => $new_sub_info['spec1_code'],
                        'spec2_code' => $new_sub_info['spec2_code'],
                        'goods_price' => $new_sub_info['goods_price'],
                        // 'sku_id'=>$new_sub_info['sku_id'],
                        'sku' => $new_sub_info['sku'],
                        'barcode' => $new_sub_info['barcode'],
                        'note_num' => $return_num,
                        //                'recv_num'=>$_request_return_num,//在入库是设置实际退货数量
                        'avg_money' => $_cur_avg_money,
                    );
                    $return_avg_money += $_cur_avg_money;
                }
            }
            //没有可退商品
            if (empty($new_return_info_mx)) {
                return $this->format_ret(-1, '', join(' ', $_err_arr));
            }
        }


        $data = array(
            'new_return_info_mx' => $new_return_info_mx,
            'return_avg_money' => $return_avg_money,
            'sell_record_info' => $sell_record_info,
            'sell_record_code' => $sell_record_code,
            'return_type' => isset($return_type) ? $return_type : '',
            'return_error' => $_err_arr,
        );
        $insert_fld_arr = explode(',', 'is_compensate,return_buyer_memo,return_express_code,return_express_no,return_pay_code,return_reason_code,return_type');
        foreach ($insert_fld_arr as $fld) {
            $data[$fld] = isset($params_info[$fld]) ? $params_info[$fld] : '';
        }
        $insert_fld_arr2 = explode(',', 'seller_express_money,adjust_money,compensate_money');
        foreach ($insert_fld_arr2 as $fld) {
            $data[$fld] = (float) $params_info[$fld];
        }
        $params_info['adjust_money'] = bcadd($params_info['adjust_money'], $adjust_money, 2);

//        if ($data['compensate_money']>0){
//	        $data['is_compensate'] = 1;
//        }
        //echo '<hr/>$xxxx<xmp>'.var_export($data,true).'</xmp>';die;
        return $this->format_ret(1, $data);
    }

    function get_new_sell_return($params_info, $sell_record_info, $return_avg_money, $return_type) {
        $return_info = array(
            'sell_record_code' => $sell_record_info['sell_record_code'],
            'deal_code' => $sell_record_info['deal_code_list'],
            'shop_code' => $sell_record_info['shop_code'],
            'sale_channel_code' => $sell_record_info['sale_channel_code'],
            'relation_shipping_status' => $sell_record_info['shipping_status'],
            'return_pay_code' => $sell_record_info['pay_code'],
            'return_order_status' => 0,
            'return_shipping_status' => 0,
            'customer_code' => $sell_record_info['customer_code'],
            'buyer_name' => $sell_record_info['buyer_name'],
            'return_name' => $sell_record_info['receiver_name'],
            'return_country' => $sell_record_info['receiver_country'],
            'return_province' => $sell_record_info['receiver_province'],
            'return_city' => $sell_record_info['receiver_city'],
            'return_district' => $sell_record_info['receiver_district'],
            'return_street' => $sell_record_info['receiver_street'],
            'return_address' => $sell_record_info['receiver_address'],
            'return_addr' => $sell_record_info['receiver_addr'],
            'return_zip_code' => $sell_record_info['receiver_zip_code'],
            'return_mobile' => $sell_record_info['receiver_mobile'],
            'return_phone' => $sell_record_info['receiver_phone'],
            'return_email' => $sell_record_info['receiver_email'],
            'change_name' => $sell_record_info['receiver_name'],
            'change_country' => $sell_record_info['receiver_country'],
            'change_province' => $sell_record_info['receiver_province'],
            'change_city' => $sell_record_info['receiver_city'],
            'change_district' => is_null($sell_record_info['receiver_district']) ? '' : $sell_record_info['receiver_district'],
            'change_street' => is_null($sell_record_info['receiver_street']) ? '' : $sell_record_info['receiver_street'],
            'change_address' => $sell_record_info['receiver_address'],
            'change_addr' => $sell_record_info['receiver_addr'],
            'change_mobile' => $sell_record_info['receiver_mobile'],
            'change_phone' => $sell_record_info['receiver_phone'],
            'buyer_alipay_no' => $sell_record_info['buyer_alipay_no']
        );
        $return_info['store_code'] = ''; //这个要改成系统参数指定的
        $return_info['return_type'] = $return_type;
        $return_info['create_time'] = date('Y-m-d H:i:s');
        $sysuser = $this->sys_user();
        $return_info['create_person'] = $sysuser['user_name'];
        $return_info['compensate_money'] = number_format(@$params_info['compensate_money'], 2, '.', '');
        $return_info['seller_express_money'] = number_format(@$params_info['seller_express_money'], 2, '.', '');
        $return_info['adjust_money'] = number_format(@$params_info['adjust_money'], 2, '.', '');
        $return_info['return_avg_money'] = $return_avg_money;
        $return_info['return_reason_code'] = isset($params_info['return_reason_code']) ? (string) $params_info['return_reason_code'] : '';
        $return_info['return_remark'] = isset($params_info['return_remark']) ? (string) $params_info['return_remark'] : '';
        $return_info['is_package_out_stock'] = isset($params_info['is_package_out_stock']) ? (int) $params_info['is_package_out_stock'] : '';
        $return_info['is_compensate'] = isset($params_info['is_compensate']) ? (int) $params_info['is_compensate'] : '';
        $return_info['sell_record_checkpay_status'] = isset($params_info['sell_record_checkpay_status']) ? (int) $params_info['sell_record_checkpay_status'] : '';

        //echo '<hr/>$asssrr<xmp>'.var_export($params_info,true).'</xmp>';
        //实际总退款金额（610） = 退单商品均摊金额合计（600） + 卖家承担运费（10） + 赔付金额（5） + 手工调整金额（-5）
        $return_info['refund_total_fee'] = $return_info['return_avg_money'] + $params_info['seller_express_money'] + $params_info['compensate_money'] + $params_info['adjust_money'];
        $return_info['return_buyer_memo'] = isset($params_info['return_buyer_memo']) ? (string) $params_info['return_buyer_memo'] : '';
        $return_info['return_pay_code'] = isset($params_info['return_pay_code']) ? (string) $params_info['return_pay_code'] : '';
        $return_info['return_express_code'] = isset($params_info['return_express_code']) ? (string) $params_info['return_express_code'] : '';
        $return_info['return_express_no'] = isset($params_info['return_express_no']) ? (string) $params_info['return_express_no'] : '';
        $return_info['refund_id'] = isset($params_info['refund_id']) ? (string) $params_info['refund_id'] : "";
        
        if ($sell_record_info['is_fenxiao'] == 1 || $sell_record_info['is_fenxiao'] == 2) {
            $return_info['is_fenxiao'] = $sell_record_info['is_fenxiao'];
            $return_info['fenxiao_name'] = $sell_record_info['fenxiao_name'];
            $return_info['fenxiao_code'] = $sell_record_info['fenxiao_code'];
        }
//        if ($return_info['return_type'] != 2 && $return_info['refund_total_fee'] == 0) {
//            return $this->format_ret(-1, '', '实际总退款金额为不能为0');
//        }
        //echo '<hr/>$return_info<xmp>'.var_export($return_info,true).'</xmp>';die;
        return $this->format_ret(1, $return_info);
    }

    function save_return($return_info) {
        $sell_return_code = load_model('util/CreateCode')->get_code('oms_sell_return');
        $return_info['sell_return_code'] = $sell_return_code;
        foreach ($return_info['mx'] as $k => $row) {
            $return_info['mx'][$k]['sell_return_code'] = $sell_return_code;
        }
        //echo '<hr/>return_info<xmp>'.var_export($return_info,true).'</xmp>';die;

        $return_info['seller_express_money'] = !empty($return_info['seller_express_money']) ? $return_info['seller_express_money'] : 0;
        ctx()->db->begin_trans();
        $ins_ret = M('oms_sell_return')->insert($return_info);
        if ($ins_ret['status'] < 0) {
            ctx()->db->rollback();
            return $ins_ret;
        }

        if (!empty($return_info['mx'])) {
            $ins_ret = M('oms_sell_return_detail')->insert($return_info['mx']);
            if ($ins_ret['status'] < 0) {
                ctx()->db->rollback();
                return $ins_ret;
            }
        }

        ctx()->db->commit();
        return $this->format_ret('1', $sell_return_code);
    }

    /*
     * 换货明细库存锁定和释放 $occupy_type = 1 占用 0 释放
     */

    function change_record_lock($sell_return_code, $occupy_type = 1) {
        
    }

    /**
     * 完成检查
     * @param $record
     * @param $detail
     * @param $sysuser
     * @return array
     */
    function opt_finish_check($record) {
        //#############权限
        if (!load_model('sys/PrivilegeModel')->check_priv('oms/return_opt/opt_finish')) {
            return $this->return_value(-1, "无权访问");
        }
        if (empty($record)) {
            return $this->format_ret(-1, '', '没有找到匹配的退单');
        }
        if ($record['return_order_status'] == 0) {
            return $this->format_ret(-1, '', '未确认退单不能操作');
        }
        if ($record['finsih_status'] == 1) {
            return $this->format_ret(-1, '', '已完成订单不能操作');
        }
        if ($record['return_shipping_status'] == 0 && in_array($record['return_type'], array(2, 3))) {
            return $this->format_ret(-1, '', '未验收入库的退单不能操作');
        }
        if ($record['return_order_status'] == 3) {
            return $this->format_ret(-1, '', '已作废退单不能操作');
        }
        if ($record['finance_check_status'] != 1) {
            return $this->format_ret(-1, '', '财务未审核通过的退单不能操作');
        }
        return $this->format_ret(1);
    }

    /**
     * 完成操作
     * @param $sell_record_code
     * @return array
     */
    public function opt_finish($sell_record_code) {
        $record = load_model('fx/SellReturnModel')->get_return_by_return_code($sell_record_code);
        $check = $this->opt_finish_check($record);
        if ($check['status'] != '1') {
            return $check;
        }
        $data['finsih_status'] = 1;
        $ret = M('oms_sell_return')->update($data, array('sell_return_code' => $sell_record_code));
        if ($ret['status'] < 0) {
            return $ret;
        }
        load_model('fx/SellReturnModel')->add_action($record, '完成');
        return $ret;
    }

    /**
     * 确认检查
     * @param $record
     * @param $detail
     * @param $sysuser
     * @return array
     */
    function opt_confirm_check($record, $detail, $sysuser) {
        //#############权限
        if (!load_model('sys/PrivilegeModel')->check_priv('oms/return_opt/opt_confirm')) {
            return $this->return_value(-1, "无权访问");
        }
        //###########
        if (empty($record)) {
            return $this->format_ret(-1, '', '没有找到匹配的退单');
        }
        /*
          if (empty($detail)) {
          return $this->format_ret(-1, '','退单无明细');
          } */
        /*
          if ($record['is_lock'] == 1 && $sysuser['user_name'] != $record['is_lock_person']) {
          return $this->format_ret(-1, '','已锁定退单不能操作');
          } */

        if ($record['return_order_status'] == 1) {
            return $this->format_ret(-1, '', '已确定退单不能操作');
        }
        if ($record['return_order_status'] == 3) {
            return $this->format_ret(-1, '', '已作废退单不能操作');
        }
        if (in_array($record['return_shipping_status'], array(1))) {
            return $this->format_ret(-1, '', '已验收入库的退单不能操作');
        }
        /*
          if (empty($record['store_code'])) {
          return $this->format_ret(-1,'','退货仓库不能为空');
          } */

        return $this->format_ret(1);
    }

    /**
     * 退单确认
     * @param $sellRecordCode
     * @param $request
     * @return array
     */
    public function opt_confirm($sellReturnCode, $request = array()) {
        $record = load_model('fx/SellReturnModel')->get_return_by_return_code($sellReturnCode);
        $detailList = load_model('fx/SellReturnModel')->get_detail_list_by_return_code($sellReturnCode);
        $sys_user = $this->sys_user();
        $check = $this->opt_confirm_check($record, $detailList, $sys_user);
        if ($check['status'] != '1') {
            return $check;
        }
        //$cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('return_auto_notice_finance', 'return_auto_notice_store'));
        //echo '<hr/>$arr<xmp>'.var_export($cfg,true).'</xmp>';die;
        //$cfg_return_auto_notice_finance = $cfg['return_auto_notice_finance'];
        //$cfg_return_auto_notice_store = $cfg['return_auto_notice_store'];
        //$is_auto_notice_finance = 0;
        //$is_auto_notice_store = 0;
        //通知财务审核
//        if (in_array($record['return_type'], array(1, 3)) && $cfg_return_auto_notice_finance == 1) {
//            $is_auto_notice_finance = 1;
//        }
        //echo '<hr/>$arr<xmp>'.var_export($record['return_type'],true).'</xmp>';die;
        //通知仓库收货
//        if (in_array($record['return_type'], array(2, 3)) && $cfg_return_auto_notice_store == 1) {
//            $is_auto_notice_store = 1;
//        }
        //$sell_reocrd_info = load_model('oms/SellRecordModel')->get_record_by_code($record['sell_record_code'], 'store_code');

        try {
            $this->begin_trans();
            //锁定换货单的库存
            $is_lock_change = $this->check_is_lock_change($record['sell_return_code']);
            if ($is_lock_change === TRUE && empty($record['change_record'])) {
                $this->reset_lock_change_detail($record['sell_return_code']);
            }

            //如果是退货单,通知仓库收货
            if (in_array($record['return_type'], array(2, 3))) {
                $ret = load_model('oms/ReturnPackageModel')->create_return_package($record, $record_type = 1);
                if ($ret['status'] == -2) {
                    ctx()->db->rollback();
                    return $ret;
                } else if ($ret['status'] < 0) {
                    ctx()->db->rollback();
                    return $this->format_ret(-1, '', '通知仓库收货出错' . $ret['message']);
                }
            }
            $return_package_type = $ret['status'];
            $data = array();
            $data['return_order_status'] = 1;
            //$data['check_time'] = date('Y-m-d H:i:s');
            $data['confirm_person'] = $sys_user['user_name'];
            $data['confirm_time'] = date('Y-m-d H:i:s');
            $data['sell_return_package_code'] = empty($ret['data']) ? '' : $ret['data'];
            //如果绑定已确认的包裹单就验收入库
            if ($return_package_type == 3) {
                $data['return_shipping_status'] = 1;
            }

            //通知财务审核
//            if ($is_auto_notice_finance) {
//                $data['finance_check_status'] = 2;
//            }
//
//            //通知仓库收货
//            if ($is_auto_notice_store) {
//                $data['return_shipping_status'] = 2;
//            }
            //echo '<hr/>$data<xmp>'.var_export($data,true).'</xmp>';die;

            $ret = M('oms_sell_return')->update($data, array('sell_return_code' => $record['sell_return_code']));
            if ($ret['status'] != 1) {
                ctx()->db->rollback();
                return $this->format_ret(-1, '', '退单确认出错');
            } else {
                $record['return_shipping_status'] = 1;
            }
            load_model('fx/SellReturnModel')->add_action($record, '确认');
            if ($return_package_type == 3) {
                $record['return_order_status'] = 1;
                load_model('fx/SellReturnModel')->add_action($record, '入库', '关联已入库包裹单导致的验收入库');
            }

            if (in_array($record['return_type'], array(2, 3))) { //退货单推送
                $ret = load_model('wms/WmsEntryModel')->add($record['sell_return_code'], 'sell_return', $record['store_code']);
                if ($ret['status'] < 0) {
                    ctx()->db->rollback();
                    return $ret;
                }
                //如果是门店仓发货数据推送到o2o_oms_trade表
                $ret = load_model('o2o/O2oEntryModel')->add($record['sell_return_code'], 'sell_return', $record['store_code']);
                if ($ret['status'] < 0) {
                    ctx()->db->rollback();
                    return $ret;
                }
                $ret = load_model('mid/MidBaseModel')->set_mid_record('return_shipping', $record['sell_return_code'], 'sell_return', $record['store_code']);

                if ($ret['status'] < 0) {
                    ctx()->db->rollback();
                    return $ret;
                }
            }


            $this->commit();

            return $this->format_ret(1, '操作成功');
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    private function check_is_lock_change($sell_return_code) {
        $sql = "select SUM(e.num-if(ISNULL(l.num),0,l.num))  from oms_sell_change_detail e
            LEFT JOIN oms_sell_record_lof l
            ON e.sell_return_code=l.record_code AND l.record_type=3 AND e.sku=l.sku
            where e.sell_return_code = :sell_return_code ";
        $sql_values = array(':sell_return_code' => $sell_return_code);
        $num = $this->db->get_value($sql, $sql_values);
        return ($num > 0) ? TRUE : FALSE;
    }

    /**
     * 反确认检查
     * @param $record
     * @param $detail
     * @param $sysuser
     * @return array
     */
    function opt_unconfirm_check($record, $detail, $sysuser, $skip_priv = 0) {
        //#############权限
        if (!load_model('sys/PrivilegeModel')->check_priv('oms/return_opt/opt_unconfirm') && $skip_priv == 0) {
            return $this->return_value(-1, "无权访问");
        }
        //###########
        if (empty($record)) {
            return $this->format_ret(-1, '', '没有找到匹配的退单');
        }
        /*
          if (empty($detail)) {
          return $this->format_ret(-1, '','退单无明细');
          } */
        /*
          if ($record['is_lock'] == 1 && $sysuser['user_name'] != $record['is_lock_person']) {
          return $this->format_ret(-1, '','已锁定退单不能操作');
          } */
        if ($record['return_order_status'] == 0) {
            return $this->format_ret(-1, '', '未确认退单不能操作');
        }
        if ($record['return_order_status'] == 3) {
            return $this->format_ret(-1, '', '已作废退单不能操作');
        }
        if ($record['return_shipping_status'] == 1) {
            return $this->format_ret(-1, '', '已验收入库的退单不能操作');
        }
//        if ($record['return_shipping_status'] == 2) {
//            return $this->format_ret(-1, '', '已通知仓库收货的退单不能操作');
//        }
        if ($record['finance_check_status'] == 1) {
            return $this->format_ret(-1, '', '财务审核通过的退单不能操作');
        }
//        if ($record['finance_check_status'] == 2) {
//            return $this->format_ret(-1, '', '通知财务审核的退单不能操作');
//        }
        /*
          if (empty($record['store_code'])) {
          return $this->format_ret(-1,'','退货仓库不能为空');
          } */
        return $this->format_ret(1);
    }

    /**
     * 退单反确认
     * @param $sellRecordCode
     * @param $request
     * @return array
     */
    public function opt_unconfirm($sellReturnCode, $request = array(), $add_log = '') {
        $record = load_model('fx/SellReturnModel')->get_return_by_return_code($sellReturnCode);
        $detailList = load_model('fx/SellReturnModel')->get_detail_list_by_return_code($sellReturnCode);
        $sys_user = $this->sys_user();
        $check = $this->opt_unconfirm_check($record, $detailList, $sys_user);
        if ($check['status'] != '1') {
            return $check;
        }
        $ret = $this->biz_unconfirm($record, $add_log);
        return $ret;
    }

    function biz_unconfirm($record, $add_log = '') {
        try {
            $this->begin_trans();
            //释放换货单的库存
            //  $filter = array('record_code' => $record['sell_return_code']);
            //    $change_detail_list = load_model('oms/SellRecordLofModel')->get_list_by_params($filter);
            //  if (1 == $change_detail_list['status']) {
            //        $invobj = new InvOpModel($record['sell_return_code'], 'oms_change', $record['store_code'], 0, $change_detail_list['data']);
            //      $ret = $invobj->adjust();
            /* if ($ret['status'] == 1) {
              load_model('fx/SellReturnModel')->add_action($record, '解锁', '换货商品库存解锁成功');
              } else
              {
              load_model('fx/SellReturnModel')->add_action($record, '解锁', '换货商品库存解锁失败');
              } */
            //  }
            //如果是退货单,取消仓库收货
            if (in_array($record['return_type'], array(2, 3))) {
                $ret = load_model('oms/ReturnPackageModel')->cancel_return_package($record['sell_return_code']);
                if ($ret['status'] != 1) {
                    $this->rollback();
                    return $this->format_ret(-1, '', '取消通知仓库收货出错');
                }
            }

            $data = array();
            $data['return_order_status'] = 0;
            $data['confirm_person'] = $sys_user['user_name'];
            $data['sell_return_package_code'] = '';
//            //取消通知财务审核
//            $data['finance_check_status'] = 0;
//            //取消通知仓库收货
//            $data['return_shipping_status'] = 0;

            $data['confirm_time'] = '0000-00-00 00:00:00';

            $ret = M('oms_sell_return')->update($data, array('sell_return_code' => $record['sell_return_code']));

            $add_log = empty($add_log) ? $add_log : ',' . $add_log;
//            if ($record['return_shipping_status'] == 2) {
//                load_model('fx/SellReturnModel')->add_action($record, '取消通知仓库收货' . $add_log);
//            }
            if (in_array($record['return_type'], array(2, 3))) {//退货单推送
                $ret = load_model('wms/WmsEntryModel')->cancel($record['sell_return_code'], 'sell_return', $record['store_code']);
                if ($ret['status'] < 0) {
                    $this->rollback();
                    return $ret;
                }
                //门店仓发货
                $ret = load_model('o2o/O2oEntryModel')->cancel($record['sell_return_code'], 'sell_return', $record['store_code']);
                if ($ret['status'] < 0) {
                    ctx()->db->rollback();
                    return $ret;
                }
                $ret = load_model('mid/MidBaseModel')->cancel_mid_record($record['sell_return_code'], 'sell_return', $record['store_code']);
                if ($ret['status'] < 0) {
                    ctx()->db->rollback();
                    return $ret;
                }
            }
            //退单状态为未确认
            $record['return_order_status'] = 0;
            load_model('fx/SellReturnModel')->add_action($record, '取消确认', $add_log);

            $this->commit();
            return $this->format_ret(1, '操作成功');
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    /**
     * 作废检查
     * @param $record
     * @param $detail
     * @param $sysuser
     * @return array
     */
    function opt_cancel_check($record, $detail, $sysuser) {
        //#############权限
        if (!load_model('sys/PrivilegeModel')->check_priv('oms/return_opt/opt_cancel')) {
            return $this->return_value(-1, "无权访问");
        }
        //###########
        if (empty($record)) {
            return $this->format_ret(-1, '', '没有找到匹配的退单');
        }
        if (in_array($record['return_order_status'], array(3))) {
            return $this->format_ret(-1, '', '已作废退单不能操作');
        }
        if (in_array($record['return_order_status'], array(1))) {
            return $this->format_ret(-1, '', '已确定退单不能操作');
        }
        if (in_array($record['return_shipping_status'], array(1))) {
            return $this->format_ret(-1, '', '已验收入库的退单不能操作');
        }
        if ($record['finance_check_status'] == 1) {
            return $this->format_ret(-1, '', '财务已审核的退单不能操作');
        }
        return $this->format_ret(1);
    }

    /**
     * 退单作废
     * @param $sellRecordCode
     * @param $request
     * @return array
     */
    public function opt_cancel($sellReturnCode, $request = array()) {
        $cancel_time = isset($request['cancel_time']) ? $request['cancel_time'] : date('Y-m-d H:i:s');
        $record = load_model('fx/SellReturnModel')->get_return_by_return_code($sellReturnCode);
        $detailList = load_model('fx/SellReturnModel')->get_detail_list_by_return_code($sellReturnCode);
        $sys_user = $this->sys_user();
        $check = $this->opt_cancel_check($record, $detailList, $sys_user);
        if ($check['status'] != '1') {
            return $check;
        }
        try {
            $this->begin_trans();
            //释放换货单的库存
            $filter = array('record_code' => $record['sell_return_code']);
            $change_detail_list = load_model('oms/SellRecordLofModel')->get_list_by_params($filter);
            if (!empty($change_detail_list['data'])) {
                $store_code = $change_detail_list['data'][0]['store_code'];
                $invobj = new InvOpModel($record['sell_return_code'], 'oms_change', $store_code, 0, $change_detail_list['data']);
                $ret = $invobj->adjust();
                /* if ($ret['status'] == 1) {
                  load_model('fx/SellReturnModel')->add_action($record, '解锁', '换货商品库存解锁成功');
                  } else
                  {
                  load_model('fx/SellReturnModel')->add_action($record, '解锁', '换货商品库存解锁失败');
                  } */
            }

            //如果是退货单,取消仓库收货
            if (in_array($record['return_type'], array(2, 3)) && $record['return_shipping_status'] == 2) {
                $ret = load_model('oms/ReturnPackageModel')->cancel_return_package($sellReturnCode);
                if ($ret['status'] != 1) {
                    return $this->format_ret(-1, '', '取消通知仓库收货出错');
                }
            }

            $data = array();
            $data['return_order_status'] = 3;
            //取消通知财务审核
            $data['finance_check_status'] = 0;
            $data['cancel_time'] = $cancel_time;

            $ret = M('oms_sell_return')->update($data, array('sell_return_code' => $record['sell_return_code']));
            if ($ret['status'] != 1) {
                return $this->format_ret(-1, '', '退单作废出错');
            }
            if ($record['finance_check_status'] == 2) {
                load_model('fx/SellReturnModel')->add_action($record, '取消通知财务审核');
            }
            if ($record['return_shipping_status'] == 2) {
                load_model('fx/SellReturnModel')->add_action($record, '取消通知仓库收货');
            }
//            if (in_array($record['return_type'], array(2, 3))){
//                $ret = load_model('wms/WmsEntryModel')->cancel($record['sell_return_code'], 'sell_return', $record['store_code']);
//                if ($ret['status'] < 0) {
//                    $this->rollback();
//                    return $ret;
//                }
//            }
            if ($request['cancel_type'] == 'return_money') {
                $ret['message'] = '生成仅退款作废的退单。';
            }
            load_model('fx/SellReturnModel')->add_action($record, '退单作废.', $ret['message']);

            //解挂对应的订单
            $ret = load_model('oms/SellRecordOptModel')->biz_unpending($record['sell_record_code']);
            if ($ret['status'] < 0) {
                return $ret;
            }
            //判断是否是已发货订单
            $sell_record_code = load_model('oms/SellRecordModel')->get_record_by_code($record['sell_record_code']);
            if ($sell_record_code['shipping_status'] != 4) {
                //如果是发货前的退款单，订单自动解挂
                $remark = '退款单作废，订单自动解挂';
                load_model('oms/SellRecordOptModel')->add_action($record['sell_record_code'], '解挂', $remark);
            }

            $this->commit();
            return $this->format_ret(1, '操作成功');
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    /**
     * 验收入库检查
     * @param $record
     * @param $detail
     * @param $sysuser
     * @return array
     */
    function opt_return_shipping_check($record, $detail, $is_wms = 0, $sysuser = '') {
        //#############权限
        if (empty($sysuser) || $sysuser != 'open_api') {
            if (!load_model('sys/PrivilegeModel')->check_priv('oms/return_opt/opt_return_shipping')) {
                return $this->return_value(-1, "无权访问");
            }
        }
        //###########
        if (empty($record)) {
            return $this->format_ret(-1, '', '没有找到匹配的退单');
        }
        if (empty($detail)) {
            return $this->format_ret(-1, '', '没有找到匹配的退单明细');
        }

        if (in_array($record['return_order_status'], array(3))) {
            return $this->format_ret(-1, '', '已作废退单不能操作');
        }
        if (in_array($record['return_shipping_status'], array(1))) {
            return $this->format_ret(-1, '', '已验收入库的退单不能操作');
        }
        if ($record['return_order_status'] == 0) {
            return $this->format_ret(-1, '', '未确认的退单不能操作');
        }
        /*
          if ($record['finance_check_status'] != 1) {
          return $this->format_ret(-1,'','财务未审核的退单不能操作');
          } */
//        if ($record['return_shipping_status'] != 2) {
//            return $this->format_ret(-1, '', '未通知仓库收货的退单不能操作');
//        }
        if ($record['return_type'] == 1) {
            return $this->format_ret(-1, '', '退款单，不能进行此操作');
        }
        if ($is_wms == 0) {
            $ret = load_model('wms/WmsEntryModel')->check_wms_store($record['store_code']);
            if ($ret['status'] > 0) {
                return $this->format_ret(-1, '', '退单仓库对接wms，不允许手工验收入库');
            }

            $status = load_model('mid/MidBaseModel')->check_is_mid('return_shipping', 'sell_return', $record['store_code']);

            if ($status !== false) {
                return $this->format_ret(-1, '', '退单仓库对接' . $status . '，不允许手工验收入库');
            }
        }



        return $this->format_ret(1);
    }

    /**
     * 方法名        api_opt_return_shipping
     * 功能描述      收货接口
     * @date        2016-07-05
     * @param       array $param
     *               array(
     *                  必选：'sell_return_code'
     *                  根据条件： 'barcode_list'=>array('barcode', 'recv_num')，
     *                      开启<退货确认收货是否需要扫描商品>参数则后两个字段为必选
     *               )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":""}
     */
    function api_opt_return_shipping($param) {
        if (empty($param['sell_return_code'])) {
            return $this->format_ret(-1, '', '退单号不能为空');
        }
        $this->begin_trans();
        $return_scanning = load_model('sys/SysParamsModel')->get_val_by_code('sell_return_scanning');
        if ($return_scanning['sell_return_scanning'] == 1) {
            $check_key = array('条码' => 'barcode', '实际收货数' => 'recv_num');
            $barcode_list = json_decode($param['barcode_list'], true);
            //检查明细是否为空
            $find_data = $this->api_check_detail($barcode_list, $check_key);
            if ($find_data['status'] != 1) {
                return $find_data;
            }

            $model = load_model('fx/SellReturnModel');
            foreach ($barcode_list as $val) {
                $ret = $model->scan_barcode($param['sell_return_code'], $val['barcode'], array('recv_num' => $val['recv_num']));
                if ($ret['status'] != 1) {
                    $this->rollback();
                    return $ret;
                }
            }
        }

        $ret = $this->opt_return_shipping($param['sell_return_code'], array(), 0, 'open_api');
        if ($ret['status'] != 1) {
            $this->rollback();
        } else {
            $this->commit();
        }
        return $ret;
    }

    /**
     * 检查接口传入明细信息是否为空
     */
    private function api_check_detail($barcode_list, $check_key) {
        $err_data = array();
        foreach ($barcode_list as $key => $val) {
            foreach ($check_key as $k => $v) {
                if (empty($val[$v])) {
                    $err_data[$key][$k] = $v;
                }
            }
        }
        if (!empty($err_data)) {
            return $this->format_ret(-1, $err_data, "数据不能为空");
        }
        return $this->format_ret(1);
    }

    public function opt_return_shipping_package($return_package_code) {

        $package_data = load_model('oms/ReturnPackageModel')->get_return_package_by_code($return_package_code);
        $store_code = $package_data['store_code'];
        $ret_lof = load_model("prm/GoodsLofModel")->get_sys_lof();

        $pack_mx = $this->db->get_all("select * from oms_return_package_detail where return_package_code=:return_package_code", array(':return_package_code' => $return_package_code));
        $lof_data = &$ret_lof['data'];
        $is_whole = FALSE;
        foreach ($pack_mx as $val) {
            if ($val['num'] != 0) {
                $is_whole = TRUE;
                break;
            }
        }
        foreach ($pack_mx as $val) {
            if ($is_whole == TRUE) {
                if ($val['num'] > 0) {
                    $pack_lof_mx[] = array(
                        'sku' => $val['sku'],
                        'goods_code' => $val['goods_code'],
                        'spec1_code' => $val['spec1_code'],
                        'spec2_code' => $val['spec2_code'],
                        'num' => $val['num'],
                        'record_code' => $val['return_package_code'],
                        'lof_no' => $lof_data['lof_no'],
                        'production_date' => $lof_data['production_date'],
                        'store_code' => $store_code,
                        'occupy_type' => 0,
                        'record_type' => 2,
                    );
                }
            } else {
                $pack_lof_mx[] = array(
                    'sku' => $val['sku'],
                    'goods_code' => $val['goods_code'],
                    'spec1_code' => $val['spec1_code'],
                    'spec2_code' => $val['spec2_code'],
                    'num' => $val['apply_num'],
                    'record_code' => $val['return_package_code'],
                    'lof_no' => $lof_data['lof_no'],
                    'production_date' => $lof_data['production_date'],
                    'store_code' => $store_code,
                    'occupy_type' => 0,
                    'record_type' => 2,
                );
            }
        }
        $update_str = " num = VALUES(num)";
        $this->insert_multi_duplicate("oms_sell_record_lof", $pack_lof_mx, $update_str);


        //  $pack_lof_mx = $ret['data']['pack_lof_mx'];
        // $pack_mx = $ret['data']['pack_mx'];
        $stock_date = date('Y-m-d H:i:s');
        $ret = load_model('oms/ReturnPackageModel')->recv_in_store($return_package_code, $store_code, $stock_date, $pack_lof_mx, $pack_mx);
        //echo '<hr/>$ret<xmp>'.var_export($ret,true).'</xmp>';
        return $ret;
    }

    /**
     * 退单验收入库
     * @param $sellReturnCode
     * @param $request
     * @return array
     */
    public function opt_return_shipping($sellReturnCode, $request = array(), $is_wms = 0, $opt_source = '') {
        $receive_time = (isset($request['receive_time']) && !empty($request['receive_time'])) ? $request['receive_time'] : date('Y-m-d H:i:s');
        $type = isset($request['type']) ? $request['type'] : '';
        $stock_date = date('Y-m-d', strtotime($receive_time));

        $record = load_model('fx/SellReturnModel')->get_return_by_return_code($sellReturnCode);

        $detailList = load_model('fx/SellReturnModel')->get_detail_list_by_return_code($sellReturnCode);
        $change_detail = load_model('fx/SellReturnModel')->get_change_detail_list_by_return_code($sellReturnCode);
        if ($opt_source == 'open_api') {
            //openapi收货
            $sys_user = 'open_api';
        } else {
            $sys_user = $this->sys_user();
        }

        $check = $this->opt_return_shipping_check($record, $detailList, $is_wms, $sys_user);
        if ($check['status'] != '1' && $check['message'] != '没有找到匹配的退单') {
            return $check;
        }
        $this->begin_trans();
        try {
            $return_package_code = load_model('oms/ReturnPackageModel')->get_return_package_code($sellReturnCode);
            //删除B2C库位表对应数据
//            $del_r = $this->delete_exp('oms_sell_record_lof', array('record_code' => $return_package_code));
//            if ($del_r['status'] < 0) {
//                throw new Exception($ret['message']);
//            }
            //获取退货包裹单明细数据用于组装B2C库位表数据
            $return_package_data = load_model('oms/ReturnPackageModel')->get_detail_list_by_return_code($sellReturnCode);
            $sell_return_data = load_model('fx/SellReturnModel')->get_record_by_code($sellReturnCode, 'deal_code,store_code');
            $flag = TRUE;
            //若所有退货商品的实退数量都为0，将实退数量更新为申请数
            foreach ($return_package_data as $sub_pack) {
                if ($sub_pack['recv_num'] == 0) {
                    continue;
                }
                $flag = FALSE;
            }
            if ($is_wms == 0) {
                $pack_lof_mx = array();
                foreach ($return_package_data as $sub_pack) {
                    $sql = "SELECT r2.lof_no,r2.production_date FROM oms_sell_record rl LEFT JOIN oms_sell_record_lof r2 ON rl.sell_record_code = r2.record_code WHERE rl.sell_record_code = '{$record['sell_record_code']}' AND r2.sku = '{$sub_pack['sku']}' ";
                    $lof_arr = $this->db->get_row($sql);
                    if (empty($lof_arr)) {
                        $moren = load_model('prm/GoodsLofModel')->get_sys_lof();
                        $lof_no = isset($moren['data']['lof_no']) ? $moren['data']['lof_no'] : '';
                        $production_date = isset($moren['data']['production_date']) ? $moren['data']['production_date'] : '';
                    } else {
                        $lof_no = $lof_arr['lof_no'];
                        $production_date = $lof_arr['production_date'];
                    }
                    $num = ($flag == FALSE) ? $sub_pack['recv_num'] : $sub_pack['note_num'];
                    $pack_lof_mx[] = array(
                        'record_type' => 2,
                        'record_code' => $return_package_code,
                        'deal_code' => $sell_return_data['deal_code'],
                        'store_code' => $sell_return_data['store_code'],
                        'goods_code' => $sub_pack['goods_code'],
                        'spec1_code' => $sub_pack['spec1_code'],
                        'spec2_code' => $sub_pack['spec2_code'],
                        'sku' => $sub_pack['sku'],
                        'barcode' => $sub_pack['barcode'],
                        'lof_no' => $lof_no,
                        'production_date' => $production_date,
                        'num' => $num,
                        'stock_date' => date('Y-m-d'),
                        'occupy_type' => 0,
                        'create_time' => time()
                    );
                }
                $update_str = "num = VALUES(num)";
                $ret = M('oms_sell_record_lof')->insert_multi_duplicate('oms_sell_record_lof', $pack_lof_mx, $update_str);
            }


            if ($ret['status'] < 0) {
                throw new Exception($ret['message']);
            }
            $ret = load_model('oms/ReturnPackageModel')->get_package_record($sellReturnCode);
            //echo '<hr/>$ret<xmp>'.var_export($ret,true).'</xmp>';
            if ($ret['status'] < 0) {
                throw new Exception($ret['message']);
            }
            $return_package_code = $ret['data']['return_package_code'];
            $store_code = $ret['data']['store_code'];
            $pack_lof_mx = $ret['data']['pack_lof_mx'];
            $pack_mx = $ret['data']['pack_mx'];
            $ret = load_model('oms/ReturnPackageModel')->recv_in_store($return_package_code, $store_code, $stock_date, $pack_lof_mx, $pack_mx);
            //echo '<hr/>$ret<xmp>'.var_export($ret,true).'</xmp>';
            if ($ret['status'] < 0) {
                throw new Exception($ret['message']);
            }
            $new_detailList = load_model('fx/SellReturnModel')->get_detail_list_by_return_code($sellReturnCode);
            //包裹单入库数明细回写的退单明细
            $ret = load_model('oms/ReturnPackageModel')->set_return_recv_num($ret['data'], $new_detailList);
            //die;
            if ($ret['status'] < 0) {
                throw new Exception($ret['message']);
            }
            //添加退货单明细
            $ret = load_model('oms/ReturnPackageModel')->add_return_detail($ret['data'], $new_detailList, $record);
            if ($ret['status'] < 0) {
                throw new Exception($ret['message']);
            }
            //如果存在 换货单商品信息 则要新生成换货单
            $change_record = '';
            if (!empty($change_detail)) {
                $ret = $this->create_change_record($record, $change_detail);
                if ($ret['status'] < 0) {

                    throw new Exception($ret['message']);
                }
                //echo '<hr/>$ret<xmp>'.var_export($ret,true).'</xmp>';die;
                $change_record = $ret['data'];
                load_model('fx/SellReturnModel')->add_action($record, '生成换货单', "新生成的换货单号为" . $change_record);
            }
            $do_person = $opt_source == 'open_api' ? 'open_api' : ctx()->get_session('user_name');
            //获取当前确认收货人名称
            $sys_user = $this->sys_user();
            //设置验收入库标识
            $data = array();
            $data['return_shipping_status'] = 1;
            $data['change_record'] = $change_record;
            $data['receive_time'] = $receive_time;
            $data['receive_person'] = $do_person;
            $data['stock_date'] = $stock_date;
            //回写确认收货人
            $data['receive_person'] = $sys_user['user_name'];
            //退款金额为0 自动审核财务
            if ($record['refund_total_fee'] == 0) {
                $data['finance_check_status'] = 1;
                $data['agreed_refund_time'] = date("Y-m-d H:i:s");
            }

            //加参数
            $param_arr = load_model('sys/SysParamsModel')->get_val_by_code(array('tmall_return', 'return_auto_finish'));
            if ((int) $param_arr['return_auto_finish'] == 1 && $data['finance_check_status'] == 1) {
                $data['finsih_status'] = 1;
            }
            $sql = "select sum(note_num) as note_num,sum(recv_num) as recv_num from oms_sell_return_detail where sell_return_code=:sell_return_code group by :sell_return_code";
            $result = $this->db->get_row($sql, array(':sell_return_code' => $record['sell_return_code']));
            $data['note_num'] = $result['note_num'];
            $data['recv_num'] = $result['recv_num'];
            $ret = M('oms_sell_return')->update($data, array('sell_return_code' => $record['sell_return_code']));
            if ($ret['status'] != 1) {
                throw new Exception('退单验收入库出错');
            }
            //写log
            if ($type == 'scan_barcode') {


                $action_name = '扫描验收入库';
            } else if ($type == 'force_acceptance') {
                $action_name = '强制确认收货';
            } else {
                $action_name = '退单验收入库';
            }
            $record_new = load_model('fx/SellReturnModel')->get_return_by_return_code($sellReturnCode);
            load_model('fx/SellReturnModel')->add_action($record_new, $action_name);
            if ((int) $param_arr['return_auto_finish'] == 1 && $data['finance_check_status'] == 1) {
                load_model('fx/SellReturnModel')->add_action($record_new, '完成', '退款金额为0，退单自动完成');
            }
            //$ret = load_model('oms/SellSettlementModel')->new_settlement_return($record['sell_return_code']);
            $ret = load_model('oms/SellSettlementModel')->generate_settlement_data($record['sell_return_code'], 2);
            // $this->rollback();
            // return $this->format_ret(-1, $ret['data'], $ret['message']);
            if ($ret['status'] < 0) {
                throw new Exception($ret['message']);
            }


            if ((int) $param_arr['tmall_return'] == 1 && $record['sale_channel_code'] == 'taobao') {
                $sql = "select `tb_shop_type` FROM base_shop_api where shop_code = :shop_code";
                $shopType = $this->db->get_value($sql, array('shop_code' => $record['shop_code']));
                if ($shopType == 'B') {
                    $sql = "SELECT refund_id from api_refund where refund_record_code = :refund_record_code";
                    $refund_id = $this->db->get_value($sql, array('refund_record_code' => $record['sell_return_code']));
                    if (!empty($refund_id)) {
                        $params = array();
                        $params['shop_code'] = $record['shop_code'];
                        $params['refund_id'] = $refund_id;
                        $params['operator'] = $do_person;
                        $params['refund_phase'] = $record['is_packet_out_stock'] == 1 ? 'aftersale' : 'onsale';
                        $params['logistics_waybill_no'] = $record['return_express_no'];
                        $params['logistics_company_code'] = $record['return_express_code'];

                        $result = load_model('sys/EfastApiModel')->request_api('taobao_api/returngoods_refill', $params);
                        if ($result['resp_data']['code'] != "0") {
                            $action_desc = "天猫卖家回填物流信息成功";
                        } else {
                            $action_desc = "天猫卖家回填物流信息失败";
                        }
                        $do_person = ctx()->get_session('user_name');
                        $do_person = empty($do_person) ? '定时服务' : $do_person;
                        $log = array(
                            'platform_code' => $record['sale_channel_code'],
                            'shop_code' => $record['shop_code'],
                            'business_type_id' => 1,
                            'action_desc' => $action_desc,
                            'do_time' => date('Y-m-d H:i:s'),
                            'do_person' => $do_person,
                        );
                        $r = load_model('sys/PlatformLogModel')->insert($log);
                    }
                }
            }

            //回写订单详细表的退单数量和退单金额
            $return_detailList = load_model('fx/SellReturnModel')->get_detail_list_by_return_code($sellReturnCode);
            $ret = load_model('oms/SellRecordModel')->update_return_num_money($return_detailList);
            if (!$ret) {
                throw new Exception('回写订单详细表的退单数量和退单金额失败');
            }
            $this->commit();
            $this->set_sell_record_refund_sku_info($record['sell_record_code'], $sellReturnCode);
            //润米调用接口
            $kh_id = CTX()->saas->get_saas_key();
            if ($kh_id == 2289) {
                foreach ($detailList as $detail) {
                    $params = array();
                    $params['refund_id'] = $record['refund_id'];
                    $params['tid'] = $record['deal_code'];
                    $params['barcode'] = $detail['barcode'];
                    $result = load_model('sys/EfastApiModel')->request_api('xiaomi/refund_shipping_sync', $params);
                }
            }
            return $this->format_ret(1, '操作成功');
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    private function set_sell_record_refund_sku_info($sell_record_code, $sellReturnCode) {
        // oms_sell_return_detail
        //$detailList
        $detailList = load_model('fx/SellReturnModel')->get_detail_list_by_return_code($sellReturnCode);
        $refund_data = array();
        foreach ($detailList as $val) {
            if ($val['recv_num'] > 0) {
                $refund_data[$val['sku']] = array('sku' => $val['sku'], 'num' => $val['recv_num'], 'deal_code' => $val['deal_code'], 'sell_return_code' => $val['sell_return_code']);
            }
        }
        if (!empty($refund_data)) {
            load_model('oms/SellRecordOptModel')->intercept_refund_sku($sell_record_code, $refund_data, 'refund');
        }
    }

    //生成新的换货单
    function create_change_record($return_record, $change_detail) {
        //如果存在换货单号，则认为换货单已生成，无需再生成
        if ($return_record['change_record'] != '') {
            return $this->format_ret(1, $return_record['change_record']);
        }
        $sell_record_code = $return_record['sell_record_code'];
        $sell_record = load_model('oms/SellRecordModel')->get_record_by_code($sell_record_code);
        $copy_sell_fld = 'deal_code,sale_channel_code,shop_code,user_code,customer_code,buyer_name,receiver_zip_code,receiver_email,is_fenxiao';
        $copy_sell_fld .= ',pay_type,pay_code,express_code,receiver_name,receiver_country,receiver_province,receiver_city,receiver_district,receiver_street,receiver_address,receiver_addr,receiver_mobile,receiver_phone,fenxiao_name,fenxiao_code,fx_express_money';
        $new_sell_record = load_model('util/ViewUtilModel')->copy_arr_by_fld($sell_record, $copy_sell_fld);
        //如果原单是COD的方式，那么默认 pay_type=nosecured pay_code=bank
        if ($sell_record['pay_type'] == 'cod') {
            $new_sell_record['pay_type'] = 'nosecured';
            $new_sell_record['pay_code'] = 'bank';
        }
        //换货仓库
        if (empty($return_record['change_store_code'])) {
            $new_sell_record['store_code'] = $sell_record['store_code'];
        } else {
            $new_sell_record['store_code'] = $return_record['change_store_code'];
        }
        $new_sell_record['record_time'] = date('Y-m-d H:i:s');
        $new_sell_record_code = load_model('oms/SellRecordModel')->new_code();
        $new_sell_record['sell_record_code'] = $new_sell_record_code;
        $sys_user = $this->sys_user();
        $new_sell_record['is_lock'] = 1;
        $new_sell_record['is_lock_person'] = $sys_user['user_code'];
        //把退单中的主单信息COPY过来
        $copy_change_fld = array(
            'change_name' => 'receiver_name',
            'change_country' => 'receiver_country',
            'change_province' => 'receiver_province',
            'change_city' => 'receiver_city',
            'change_district' => 'receiver_district',
            'change_street' => 'receiver_street',
            'change_address' => 'receiver_address',
            'change_addr' => 'receiver_addr',
            'change_mobile' => 'receiver_mobile',
            'change_phone' => 'receiver_phone',
            'change_express_code' => 'express_code',
            'change_express_money' => 'express_money',
        );
        foreach ($copy_change_fld as $k => $v) {
            $new_sell_record[$v] = $return_record[$k];
        }
        //订单设置换货单的标识
        $new_sell_record['is_change_record'] = 1;
        $new_sell_record['change_record_from'] = $sell_record_code;
        //设置基本的字段
        $new_sell_record['delivery_money'] = 0;
        if (empty($new_sell_record['express_code'])) {
            $ret_shop = load_model('base/ShopModel')->get_by_code($new_sell_record['shop_code']);
            if (isset($ret_shop['data']['express_code']) && !empty($ret_shop['data']['express_code'])) {
                $new_sell_record['express_code'] = $ret_shop['data']['express_code'];
            } else {
                $new_sell_record['express_code'] = $sell_record['express_code'];
            }
        }

        /* $realreturn = $return_record['refund_total_fee'] - $return_record['change_avg_money'];//实际应退款
          if($realreturn < 0){
          $new_sell_record['order_status'] = 0;
          }else{
          $new_sell_record['order_status'] = 1;
          }

          if($realreturn < 0){
          $new_sell_record['pay_status'] = 0;
          }else{
          $new_sell_record['pay_status'] = 2;
          } */

        $new_sell_record['lock_inv_status'] = 0;
        //计算 财务应收 和 已付
        $total_change_je = $return_record['change_express_money'] + $return_record['change_avg_money'];
        $new_sell_record['order_money'] = $total_change_je;
        /* if ($return_record['refund_total_fee']>$total_change_je){
          $new_sell_record['paid_money'] = $total_change_je;
          }else{
          $new_sell_record['paid_money'] = $return_record['refund_total_fee'];
          } */
        //计算换货单“已付金额”
        $change_ysje = number_format(($return_record['change_express_money'] + $return_record['change_avg_money']), 3, '.', '');
        $ytk = $return_record['return_avg_money'] + $return_record['seller_express_money'] + $return_record['compensate_money'] + $return_record['adjust_money'];
        $total_return_money = $ytk - $change_ysje;
        if ($total_return_money >= 0) {
            $new_sell_record['paid_money'] = $change_ysje;
        } else {
            $new_sell_record['paid_money'] = $ytk;
        }
        //    if($total_return_money < 0){
        $new_sell_record['order_status'] = 0;
//		}else{
//			$new_sell_record['order_status'] = 1;
//            $new_sell_record['check_time'] = date('Y-m-d H:i:s');
//		}

        if ($total_return_money < 0) {
            $new_sell_record['pay_status'] = 0;
        } else {
            $new_sell_record['pay_status'] = 2;
            $new_sell_record['pay_time'] = date('Y-m-d H:i:s');
        }

        //把退单中的换货明细信息COPY过来
        $copy_sell_fld = 'deal_code,goods_code,sku_id,sku,goods_price,num,avg_money,pic_path,fx_amount';
        $new_sell_record_detail = load_model('util/ViewUtilModel')->copy_arr_by_fld($change_detail, $copy_sell_fld, 1);
        $new_sell_record_detail = load_model('util/ViewUtilModel')->set_arr_el_val($new_sell_record_detail, array('sell_record_code' => $new_sell_record_code, 'sell_record_detail_id' => 0));


        //刷新主单的信息
        $ret = load_model('oms/SellRecordOptModel')->js_sell_plan_send_time($new_sell_record, $new_sell_record_detail);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $new_sell_record = $ret['data'];
        $ret = load_model('oms/SellRecordOptModel')->js_record_price($new_sell_record, $new_sell_record['mx']);
        if ($ret['status'] < 0) {
            return $ret;
        }
        // 生成分销换货单结算分销结算单价
        if($ret['data']['is_fenxiao'] == 2) {
            foreach($ret['data']['mx'] as $key => &$val) {
                $trade_price = $val['fx_amount'] / $val['num'];
                $ret['data']['mx'][$key]['trade_price'] = sprintf("%.3f",$trade_price);
            }
        }

        $new_sell_record = $ret['data'];
        $new_sell_record_detail = $new_sell_record['mx'];

        //重新生成主单的交易号
        $deal_code_arr = array();
        foreach ($new_sell_record['mx'] as $sub_mx) {
            $deal_code_arr[] = $sub_mx['deal_code'];
        }
        $new_sell_record['deal_code_list'] = join(',', array_unique($deal_code_arr));
        $new_sell_record['deal_code'] = load_model('SellRecordOptModel')->get_guid_deal_code($new_sell_record['deal_code_list']);
        //维护成本价
        foreach ($new_sell_record_detail as &$value) {
            //先取sku级，若无值则取商品级
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], array('cost_price'));
            $value['cost_price'] = $sku_info['cost_price'];
        }
        ctx()->db->begin_trans();

        $ret = M('oms_sell_record')->insert($new_sell_record);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $ret = M('oms_sell_record_detail')->insert_multi($new_sell_record_detail);
        if ($ret['status'] < 0) {
            return $ret;
        }

        $new_sell_record['must_occupy_inv'] = 1;

        //释放换货单的库存
        $filter = array('record_code' => $return_record['sell_return_code'], 'record_type' => 3);
        $change_detail_list = load_model('oms/SellRecordLofModel')->get_list_by_params($filter);
        if (1 == $change_detail_list['status']) {
            $store_code = $change_detail_list['data'][0]['store_code'];
            $invobj = new InvOpModel($return_record['sell_return_code'], 'oms_change', $store_code, 0, $change_detail_list['data']);
            $ret = $invobj->adjust();
            /* if ($ret['status'] == 1) {
              $record = $return_record;
              $record['sell_record_code'] = $return_record['sell_return_code'];
              load_model('fx/SellReturnModel')->add_action($record, '解锁', '换货商品库存解锁成功');
              } else
              {
              load_model('fx/SellReturnModel')->add_action($record, '解锁', '换货商品库存解锁失败');
              } */
        }
        if ($total_return_money >= 0) {
            load_model('oms/SellRecordOptModel')->set_sell_record_is_lock($new_sell_record_code, false);
            $lock_sell_record_detail = load_model('oms/SellRecordOptModel')->get_detail_by_sell_record_code($new_sell_record_code);
            $lock_ret = load_model('SellRecordOptModel')->lock_detail($new_sell_record, $lock_sell_record_detail, 1);
            /*
              echo '<hr/>$new_sell_record<xmp>'.var_export($new_sell_record,true).'</xmp>';
              echo '<hr/>$new_sell_record_detail<xmp>'.var_export($new_sell_record_detail,true).'</xmp>';
              echo '<hr/>$lock_ret<xmp>'.var_export($lock_ret,true).'</xmp>';die; */
            if ($lock_ret['status'] < 1 && $lock_ret['status'] <> -10) {
                return $lock_ret;
            }
        }

        $data = array();
        $data['paid_money'] = $new_sell_record['paid_money'];
        //$data['pay_status'] = 2;
        $data['pay_time'] = date('Y-m-d H:i:s');
        $data['must_occupy_inv'] = ($total_return_money < 0) ? 0 : 1;

        $ret = M('oms_sell_record')->update($data, array('sell_record_code' => $new_sell_record_code));
        if ($ret['status'] < 0) {
            return $ret;
        }
        $data = array('change_record' => $new_sell_record_code);
        M('oms_sell_return')->update($data, array('sell_return_code' => $return_record['sell_return_code']));

        if ($total_return_money < 0) {
            $problem_remark = '生成换货单，换货单已付款小于应付款，自动设问换货单';
            $ret = load_model("oms/SellRecordOptModel")->set_problem_order('CHANGE_GOODS_MAKEUP', $problem_remark, $new_sell_record_code);
            if ($ret['status'] < 0) {
                return $ret;
            }
        }
        //维护订单理论重量
        $ret = load_model('oms/SellRecordOptModel')->update_record_goods_weigh($new_sell_record_code);
        if ($ret != true) {
            return $this->format_ret('-1', '', '更新理论重量失败');
        }

        ctx()->db->commit();


        //写日志
        $log = '此换货单是由退单 ' . $return_record['sell_return_code'] . ' 生成的';
        load_model('oms/SellRecordModel')->add_action($new_sell_record_code, '生成换货单', $log);

        //锁定订单
        load_model('oms/SellRecordOptModel')->opt_unlock($new_sell_record['sell_record_code']);

        return $this->format_ret(1, $new_sell_record_code);
    }

    function opt_lock_check($record, $detail, $sysuser) {
        if (empty($record)) {
            return $this->format_ret(-1, '', '没有找到匹配的退单');
        }
        if ($record['is_lock'] == 1) {
            return $this->format_ret(-1, '', '已锁定退单不能操作');
        }
        if (in_array($record['return_order_status'], array(3))) {
            return $this->format_ret(-1, '', '已作废退单不能操作');
        }
        if (in_array($record['return_shipping_status'], array(1))) {
            return $this->format_ret(-1, '', '已验收入库的退单不能操作');
        }
        return $this->format_ret(1);
    }

    function opt_unlock_check($record, $detail, $sysuser) {
        if (empty($record)) {
            return $this->format_ret(-1, '', '没有找到匹配的退单');
        }

        if ($record['is_lock'] == 1 && $sysuser['user_code'] != $record['is_lock_person'] && $sysuser['is_manage'] != 1) {
            return $this->format_ret(-1, '', '已锁定退单不能操作');
        }

        if ($record['is_lock'] == 0) {
            return $this->format_ret(-1, '', '未锁定退单不能操作');
        }
        if (in_array($record['return_order_status'], array(3))) {
            return $this->format_ret(-1, '', '已作废退单不能操作');
        }
        if (in_array($record['return_shipping_status'], array(1))) {
            return $this->format_ret(-1, '', '已验收入库的退单不能操作');
        }
        return $this->format_ret(1);
    }

    function opt_notice_finance_check($record, $detail, $sysuser) {
        //#############权限
        if (!load_model('sys/PrivilegeModel')->check_priv('oms/return_opt/opt_notice_finance')) {
            return $this->return_value(-1, "无权访问");
        }
        //###########
        if (empty($record)) {
            return $this->format_ret(-1, '', '没有找到匹配的退单');
        }
        if ($record['is_lock'] == 1) {
            return $this->format_ret(-1, '', '已锁定的退单不能操作');
        }
        if (in_array($record['return_order_status'], array(3))) {
            return $this->format_ret(-1, '', '已作废退单不能操作');
        }
        if ($record['return_order_status'] == 0) {
            return $this->format_ret(-1, '', '未确认的退单不能操作');
        }
        if (in_array($record['finance_check_status'], array(1, 2))) {
            return $this->format_ret(-1, '', '已通知财务审核的退单不能操作');
        }
        return $this->format_ret(1);
    }

    function opt_unnotice_finance_check($record, $detail, $sysuser) {
        //#############权限
        if (!load_model('sys/PrivilegeModel')->check_priv('oms/return_opt/opt_unnotice_finance')) {
            return $this->return_value(-1, "无权访问");
        }
        //###########
        if (empty($record)) {
            return $this->format_ret(-1, '', '没有找到匹配的退单');
        }
        if ($record['is_lock'] == 1) {
            return $this->format_ret(-1, '', '已锁定的退单不能操作');
        }
        if (in_array($record['return_order_status'], array(3))) {
            return $this->format_ret(-1, '', '已作废退单不能操作');
        }
        if ($record['return_order_status'] == 0) {
            return $this->format_ret(-1, '', '未确认的退单不能操作');
        }
        if ($record['finance_check_status'] == 0) {
            return $this->format_ret(-1, '', '未通知财务审核的退单不能操作');
        }
        if ($record['finance_check_status'] == 1) {
            return $this->format_ret(-1, '', '财务已审核的退单不能操作');
        }
        return $this->format_ret(1);
    }

    function opt_notice_store_check($record, $detail, $sysuser) {
        //#############权限
        if (!load_model('sys/PrivilegeModel')->check_priv('oms/return_opt/opt_notice_store')) {
            return $this->return_value(-1, "无权访问");
        }
        //###########
        if (empty($record)) {
            return $this->format_ret(-1, '', '没有找到匹配的退单');
        }
        if ($record['is_lock'] == 1) {
            return $this->format_ret(-1, '', '已锁定的退单不能操作');
        }
        if (in_array($record['return_order_status'], array(3))) {
            return $this->format_ret(-1, '', '已作废退单不能操作');
        }
        if (in_array($record['return_shipping_status'], array(1))) {
            return $this->format_ret(-1, '', '已验收入库的退单不能操作');
        }
        if ($record['return_order_status'] == 0) {
            return $this->format_ret(-1, '', '未确认的退单不能操作');
        }
        if ($record['return_shipping_status'] == 2) {
            return $this->format_ret(-1, '', '已通知仓库收货的退单不能操作');
        }
        if ($record['return_type'] == 1) {
            return $this->format_ret(-1, '', '退款单，不能进行此操作');
        }
        return $this->format_ret(1);
    }

    function opt_unnotice_store_check($record, $detail, $sysuser) {
        //#############权限
        if (!load_model('sys/PrivilegeModel')->check_priv('oms/return_opt/opt_unnotice_store')) {
            return $this->return_value(-1, "无权访问");
        }
        //###########
        if (empty($record)) {
            return $this->format_ret(-1, '', '没有找到匹配的退单');
        }
        if ($record['is_lock'] == 1) {
            return $this->format_ret(-1, '', '已锁定的退单不能操作');
        }
        if (in_array($record['return_order_status'], array(3))) {
            return $this->format_ret(-1, '', '已作废退单不能操作');
        }
        if (in_array($record['return_shipping_status'], array(1))) {
            return $this->format_ret(-1, '', '已验收入库的退单不能操作');
        }
        if ($record['return_order_status'] == 0) {
            return $this->format_ret(-1, '', '未确认的退单不能操作');
        }
        //echo '<hr/>$record<xmp>'.var_export($record,true).'</xmp>';
        if ($record['return_shipping_status'] != 2) {
            return $this->format_ret(-1, '', '未通知仓库收货的退单不能操作');
        }

        return $this->format_ret(1);
    }

    public function opt_lock($sellReturnCode, $request = array()) {
        $record = load_model('fx/SellReturnModel')->get_return_by_return_code($sellReturnCode);
        $detailList = load_model('fx/SellReturnModel')->get_detail_list_by_return_code($sellReturnCode);
        $sys_user = $this->sys_user();
        $check = $this->opt_lock_check($record, $detailList, $sys_user);

        if ($check['status'] != '1') {
            return $check;
        }

        try {
            $this->begin_trans();

            $data = array();
            $data['is_lock'] = 1;
            $data['is_lock_person'] = ctx()->get_session('user_code');

            $ret = M('oms_sell_return')->update($data, array('sell_return_code' => $record['sell_return_code']));
            if ($ret['status'] != 1) {
                return $this->format_ret(-1, '', '退单锁定出错');
            }
            $msg = "退单已被 {$date['is_lock_person']} 锁定";
            load_model('fx/SellReturnModel')->add_action($record, '锁定', $msg);
            $this->commit();
            return $this->format_ret(1, '操作成功');
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    public function opt_unlock($sellReturnCode, $request = array()) {
        $record = load_model('fx/SellReturnModel')->get_return_by_return_code($sellReturnCode);
        $detailList = load_model('fx/SellReturnModel')->get_detail_list_by_return_code($sellReturnCode);
        $sys_user = $this->sys_user();
        $check = $this->opt_unlock_check($record, $detailList, $sys_user);
        //echo '<hr/>check<xmp>'.var_export($check,true).'</xmp>';
        if ($check['status'] != '1') {
            return $check;
        }
        try {
            $this->begin_trans();

            $data = array();
            $data['is_lock'] = 0;
            $data['is_lock_person'] = '';

            $ret = M('oms_sell_return')->update($data, array('sell_return_code' => $record['sell_return_code']));
            if ($ret['status'] != 1) {
                return $this->format_ret(-1, '', '退单解锁出错');
            }
            load_model('fx/SellReturnModel')->add_action($record, '解锁');
            $this->commit();
            return $this->format_ret(1, '操作成功');
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, $e->getMessage());
        }
    }

    public function opt_notice_finance($sellReturnCode, $request = array()) {
        $record = load_model('fx/SellReturnModel')->get_return_by_return_code($sellReturnCode);
        $detailList = load_model('fx/SellReturnModel')->get_detail_list_by_return_code($sellReturnCode);
        $sys_user = $this->sys_user();
        $check = $this->opt_notice_finance_check($record, $detailList, $sys_user);
        //echo '<hr/>check<xmp>'.var_export($check,true).'</xmp>';
        if ($check['status'] != '1') {
            return $check;
        }
        try {
            $this->begin_trans();

            $data = array();
            $data['finance_check_status'] = 2;

            $ret = M('oms_sell_return')->update($data, array('sell_return_code' => $record['sell_return_code']));
            if ($ret['status'] != 1) {
                return $this->format_ret(-1, '', '退单通知财务出错');
            }

            load_model('fx/SellReturnModel')->add_action($record, '通知财务');
            $this->commit();
            return $this->format_ret(1, '操作成功');
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, $e->getMessage());
        }
    }

    public function opt_unnotice_finance($sellReturnCode, $request = array()) {
        $record = load_model('fx/SellReturnModel')->get_return_by_return_code($sellReturnCode);
        $detailList = load_model('fx/SellReturnModel')->get_detail_list_by_return_code($sellReturnCode);
        $sys_user = $this->sys_user();
        $check = $this->opt_unnotice_finance_check($record, $detailList, $sys_user);
        //echo '<hr/>check<xmp>'.var_export($check,true).'</xmp>';
        if ($check['status'] != '1') {
            return $check;
        }
        try {
            $this->begin_trans();

            $data = array();
            $data['finance_check_status'] = 0;

            $ret = M('oms_sell_return')->update($data, array('sell_return_code' => $record['sell_return_code']));
            if ($ret['status'] != 1) {
                return $this->format_ret(-1, '', '退单取消通知财务出错');
            }
            load_model('fx/SellReturnModel')->add_action($record, '取消通知财务');
            $this->commit();
            return $this->format_ret(1, '操作成功');
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, $e->getMessage());
        }
    }

    public function opt_notice_store($sellReturnCode, $request = array()) {
        $record = load_model('fx/SellReturnModel')->get_return_by_return_code($sellReturnCode);
        $detailList = load_model('fx/SellReturnModel')->get_detail_list_by_return_code($sellReturnCode);
        $sys_user = $this->sys_user();
        $check = $this->opt_notice_store_check($record, $detailList, $sys_user);
        //echo '<hr/>check<xmp>'.var_export($check,true).'</xmp>';
        if ($check['status'] != '1') {
            return $check;
        }
        try {
            $this->begin_trans();
            //生成入库通知单
            $ret = load_model('oms/ReturnPackageModel')->create_return_package($record);
            if ($ret['status'] < 0) {
                return $ret;
            }
            $data = array();
            $data['return_shipping_status'] = 2;

            $ret = M('oms_sell_return')->update($data, array('sell_return_code' => $record['sell_return_code']));
            if ($ret['status'] != 1) {
                return $this->format_ret(-1, '', '退单通知仓库收货出错');
            }
            if (in_array($record['return_type'], array(2, 3))) {
                $ret = load_model('wms/WmsEntryModel')->add($record['sell_return_code'], 'sell_return', $record['store_code']);
                if ($ret['status'] < 0) {
                    return $ret;
                }
                $ret = load_model('mid/MidBaseModel')->set_mid_record('return_shipping', $record['sell_return_code'], 'sell_return', $record['store_code']);
                if ($ret['status'] < 0) {
                    return $ret;
                }
            }

            load_model('fx/SellReturnModel')->add_action($record, '通知仓库收货');
            $this->commit();
            return $this->format_ret(1, '操作成功');
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, $e->getMessage());
        }
    }

    public function opt_unnotice_store($sellReturnCode, $request = array()) {
        $record = load_model('fx/SellReturnModel')->get_return_by_return_code($sellReturnCode);
        $detailList = load_model('fx/SellReturnModel')->get_detail_list_by_return_code($sellReturnCode);
        $sys_user = $this->sys_user();
        $check = $this->opt_unnotice_store_check($record, $detailList, $sys_user);
        //echo '<hr/>check<xmp>'.var_export($check,true).'</xmp>';
        if ($check['status'] != '1') {
            return $check;
        }
        try {
            $this->begin_trans();
            //如果是退货单,取消仓库收货
            if (in_array($record['return_type'], array(2, 3)) && $record['return_shipping_status'] == 2) {
                $ret = load_model('oms/ReturnPackageModel')->cancel_return_package($sellReturnCode);
                if ($ret['status'] != 1) {
                    return $this->format_ret(-1, '', '取消通知仓库收货出错 ' . $ret['message']);
                }
            }
            $data = array();
            $data['return_shipping_status'] = 0;

            $ret = M('oms_sell_return')->update($data, array('sell_return_code' => $record['sell_return_code']));
            if ($ret['status'] != 1) {
                return $this->format_ret(-1, '', '取消通知仓库收货出错 ' . $ret['message']);
            }
            if (in_array($record['return_type'], array(2, 3))) {
                $ret = load_model('wms/WmsEntryModel')->cancel($record['sell_return_code'], 'sell_return', $record['store_code']);
                if ($ret['status'] < 0) {
                    $this->rollback();
                    return $ret;
                }
                $ret = load_model('mid/MidBaseModel')->cancel_mid_record($record['sell_return_code'], 'sell_return', $record['store_code']);
                if ($ret['status'] < 0) {
                    ctx()->db->rollback();
                    return $ret;
                }
            }
            load_model('fx/SellReturnModel')->add_action($record, '取消通知仓库收货', $ret['message']);
            $this->commit();
            return $this->format_ret(1, '操作成功');
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, $e->getMessage());
        }
    }

    /**
     * 财务审核 check
     * @param $record
     * @param $detail
     * @param $sysuser
     * @return array
     */
    function opt_finance_confirm_check($record, $detail, $sysuser) {
        //#############权限
        if (!load_model('sys/PrivilegeModel')->check_priv('oms/return_opt/opt_finance_confirm')) {
            return $this->return_value(-1, "无权访问");
        }
        //###########
        if (empty($record)) {
            return $this->format_ret(-1, '', '没有找到匹配的退单');
        }

        //$finance_check_status = $map_arr_finance_confirm = array('0' => '未通知财务审核', '1' => '财务已经审核通过', '3' => '财务已退回');
        $map_arr_finance_confirm = array('1' => '财务已经审核通过');
        $finance_confirm_msg = isset($map_arr_finance_confirm[$record['finance_check_status']]) ? $map_arr_finance_confirm[$record['finance_check_status']] : '';
        if ($finance_confirm_msg != '') {
            return $this->format_ret(-1, '', $finance_confirm_msg . '的退单不能操作');
        }
        if ($record['return_order_status'] == 0) {
            return $this->format_ret(-1, '', '未确认退单不能操作');
        }
        if (in_array($record['return_order_status'], array(3))) {
            return $this->format_ret(-1, '', '已作废退单不能操作');
        }
        //设置参数启动后必须
        $arr = array('order_return_huo');
        $ret_arr = load_model('sys/SysParamsModel')->get_val_by_code($arr);
        $response['order_return_huo'] = isset($ret_arr['order_return_huo']) ? $ret_arr['order_return_huo'] : '';
        if ($response['order_return_huo'] == '1' && $record['return_type'] == '3' && $record['return_shipping_status'] <> '1') {
            return $this->format_ret(-1, '', '退款退货单启用参数后必须确认收货后才能操作');
        }
        /*
          if (in_array($record['return_shipping_status'], array(1))) {
          return $this->format_ret(-1, '','已验收入库的退单不能操作');
          } */
        return $this->format_ret(1);
    }

    /**
     * 财务审核
     * @param $sellReturnCode
     * @param $request
     * @return array
     */
    public function opt_finance_confirm($sellReturnCode, $request = array()) {
        $record = load_model('fx/SellReturnModel')->get_return_by_return_code($sellReturnCode);
        $detailList = load_model('fx/SellReturnModel')->get_detail_list_by_return_code($sellReturnCode, 'deal_code,sku');
        $sys_user = $this->sys_user();
        $check = $this->opt_finance_confirm_check($record, $detailList, $sys_user);
        if ($check['status'] != '1') {
            return $check;
        }
        //自动结算
        /*$change_detail = load_model('fx/SellReturnModel')->get_change_detail_list_by_return_code($sellReturnCode);
        //扣减换货单结算金额
        if(!empty($change_detail)) {
            foreach ($change_detail as $val) {
                $record['fx_payable_money'] -= $val['fx_amount'];
            }
        }*/
        $ret = load_model('oms/SellRecordOptModel')->is_fx_finance_account_manage($record,'return_finance_confirm'); // 生成资金流水
        if($ret['status'] < 0) {
            return $ret;
        }
        //如果是退款单 并且是 发货前的订单，财务审核退单后，要处理订单的明细，并解挂对应的订单
        if ($record['return_type'] == 1) {
            $ret = $this->return_finance_check_process_sell_record($record['sell_record_code'], $detailList);
            if ($ret['status'] < 0) {
                return $ret;
            }

            if ($record['relation_shipping_status'] == 4) {//relation_shipping_status $record['is_packet_out_stock'] == 1
                $ret = load_model('oms/SellSettlementModel')->generate_settlement_data($sellReturnCode, 2);
                if (1 != $ret['status']) {
                    return $ret;
                }
            }
        }
        $param_arr = load_model('sys/SysParamsModel')->get_val_by_code(array('tmall_return', 'return_auto_finish'));

        $this->begin_trans();
        try {
            $data = array();
            $data['finance_check_status'] = 1;
            $data['agreed_refund_time'] = date('Y-m-d H:i:s');
            $data['agree_refund_person'] = $sys_user['user_name'];
            if ($record['return_type'] == 1 && (int) $param_arr['return_auto_finish'] == 1) {
                $data['finsih_status'] = 1;
            }
            if ($record['return_shipping_status'] == 1 && (int) $param_arr['return_auto_finish'] == 1 && in_array($record['return_type'], array(2, 3))) {
                $data['finsih_status'] = 1;
            }
            // $ret = M('oms_sell_return')->update($data, array('sell_return_code' => $sellReturnCode));

            $ret = $this->db->update('oms_sell_return', $data, "sell_return_code = '{$record['sell_return_code']}'");
            if (!$ret) {
                $this->rollback();
                return $ret;
            }
            $record['finance_check_status'] = 1;
            load_model('fx/SellReturnModel')->add_action($record, '财务审核');


            if ((int) $param_arr['tmall_return'] == 1 && $record['sale_channel_code'] == 'taobao') {
                $sql = "select `tb_shop_type` FROM base_shop_api where shop_code = :shop_code";
                $shopType = $this->db->get_value($sql, array('shop_code' => $record['shop_code']));
                if ($shopType == 'B') {
                    $sql = "SELECT refund_id from api_refund where refund_record_code = :refund_record_code";
                    $refund_id = $this->db->get_value($sql, array('refund_record_code' => $record['sell_return_code']));

                    if (!empty($refund_id)) {
                        $ret_shop = load_model('base/ShopModel')->get_by_code($record['shop_code']);
                        $shop_id = $ret_shop['data']['shop_id'];

                        $is_refund_review = false;
                        if ($record['return_type'] == '3') {
                            $sql = "SELECT status from api_taobao_refund where refund_id = :refund_id";
                            $refund_status = $this->db->get_value($sql, array('refund_id' => $record['refund_id']));
                            if ($refund_status == 'WAIT_SELLER_CONFIRM_GOODS') {
                                $is_refund_review = true;
                            }
                        } else if ($record['return_type'] == '1') {
                            $is_refund_review = true;
                        }

                        if ($is_refund_review) {
                            $params = array();
                            $params['sd_id'] = $shop_id;
                            $params['refund_id'] = $refund_id;
                            $params['operator'] = CTX()->get_session('user_name');
                            $params['refund_phase'] = $record['is_packet_out_stock'] == 1 ? 'aftersale' : 'onsale';
                            $params['refund_version'] = time();
                            $params['result'] = true;
                            $params['message'] = '同意退款';
                            $result = load_model('sys/EfastApiModel')->request_api('taobao_api/refund_review', $params);
                            if ($result['resp_data']['code'] != "0") {
                                $action_desc = "天猫退款单审核成功";
                            } else {
                                $action_desc = "天猫退款单审核失败";
                            }
                            $log = array(
                                'platform_code' => $record['sale_channel_code'],
                                'shop_code' => $record['shop_code'],
                                'business_type_id' => 1,
                                'action_desc' => $action_desc,
                                'do_time' => date('Y-m-d H:i:s'),
                                'do_person' => ctx()->get_session('user_name'),
                            );
                            $r = load_model('sys/PlatformLogModel')->insert($log);
                            // 2.获取退单状态优先通过RDS获取，如果RDS无数据，则通过API获取
                            // 3.执行日志记录到“平台关键业务流水”中，业务类型为：天猫退款单审核
                        }
                    }
                }
            }
            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    /**
     * 如果是退款单 并且是 发货前的订单，财务审核退单后，要处理订单的明细，并解挂对应的订单
     */
    function return_finance_check_process_sell_record($sellRecordCode, $return_detail) {
        $record = load_model('oms/SellRecordModel')->get_record_by_code($sellRecordCode);
        $record_detail = load_model('oms/SellRecordModel')->get_detail_by_sell_record_code($sellRecordCode, 0, 1);
        //如果是已发货的订单则不处理，已发货的会生产赔付的退款单
        if ($record['shipping_status'] >= 4) {
            return $this->format_ret(1);
        }
        ctx()->db->begin_trans();
        //如果订单已占用库存，释放对应订单占用的库存
        if ($record['must_occupy_inv'] == 1 && $record['lock_inv_status'] == 1) {
            $ret = load_model('oms/SellRecordOptModel')->lock_detail($record, $record_detail, 0);
            if ($ret['status'] < 0) {
                return $ret;
            }
            $record['lock_inv_status'] = 0;
        }
        //作废对应的订单明细
        $sell_record_mx_id_arr = array();
        foreach ($return_detail as $ks => $sub_detail) {
            $find_sell_row = isset($record_detail[$ks]) ? $record_detail[$ks] : null;
            if (isset($find_sell_row)) {
                $sell_record_mx_id_arr[] = $find_sell_row['sell_record_detail_id'];
            }
        }
        $mx_fld_arr = explode(',', 'sell_record_code,deal_code,sub_deal_code,goods_code,sku_id,sku,goods_price,num,goods_weigh,platform_spec,is_gift,sale_mode,delivery_mode,delivery_days_or_time,plan_send_time');
        //新的订单明细
        $new_detail = array();
        //要新增加的订单明细
        $add_new_detail = array();
        foreach ($record_detail as $ks => $sub_detail) {
            if (!isset($return_detail[$ks])) {
                $new_detail[] = $sub_detail;
            } else {
                $_t_sub_sl = $sub_detail['num'] - $return_detail[$ks]['note_num'];
                if ($_t_sub_sl > 0) {
                    $sub_detail['num'] = $_t_sub_sl;
                    $new_detail[] = $sub_detail;
                    $add_new_detail_row = array();
                    foreach ($mx_fld_arr as $mx_fld) {
                        $add_new_detail_row[$mx_fld] = $sub_detail[$mx_fld];
                    }
                    $add_new_detail_row['avg_money'] = $sub_detail['avg_money'] - $return_detail[$ks]['avg_money'];
                    $add_new_detail[] = $add_new_detail_row;
                }
            }
        }
        /*
          echo '<hr/>$record_detail<xmp>'.var_export($record_detail,true).'</xmp>';
          echo '<hr/>$return_detail<xmp>'.var_export($return_detail,true).'</xmp>';
          echo '<hr/>$new_detail<xmp>'.var_export($new_detail,true).'</xmp>';
          echo '<hr/>$add_new_detail<xmp>'.var_export($add_new_detail,true).'</xmp>';
          echo '<hr/>$sell_record_mx_id_arr<xmp>'.var_export($sell_record_mx_id_arr,true).'</xmp>';
          die; */
        if (!empty($sell_record_mx_id_arr)) {
            $sell_record_mx_id_list = join(',', $sell_record_mx_id_arr);
            $is_del_time = time();
            $sql = "update oms_sell_record_detail set is_delete = {$is_del_time} where sell_record_detail_id in($sell_record_mx_id_list)";
            ctx()->db->query($sql);
            //如果存在部分退的情况新增订单的明细
            if (!empty($add_new_detail)) {
                $ret = M('oms_sell_record_detail')->insert($add_new_detail);
                if ($ret['status'] < 0) {
                    return $ret;
                }
            }
            //计划发货时间
            $ret = load_model('oms/SellRecordOptModel')->js_sell_plan_send_time($record, $new_detail);
            if ($ret['status'] < 0) {
                return $ret;
            }
            $ret = load_model('oms/SellRecordOptModel')->update_sell_plan_send_time($ret['data']);
            if ($ret['status'] < 0) {
                return $ret;
            }
            //主单价格
            $ret = load_model('oms/SellRecordOptModel')->js_record_price($record, $new_detail);
            if ($ret['status'] < 0) {
                return $ret;
            }
            $payable_money = $ret['data']['payable_money'];

            $ret = load_model('oms/SellRecordOptModel')->update_record_price($ret['data']);
            if ($ret['status'] < 0) {
                return $ret;
            }
        }

        //重新占用订单的库存
        if ($record['must_occupy_inv'] == 1 && $record['lock_inv_status'] == 0) {

            $ret = load_model('oms/SellRecordOptModel')->lock_detail($record, $record_detail, 1);
            if ($ret['status'] < 0) {
                return $ret;
            }
        }

        //解挂对应的订单
        $ret = load_model('oms/SellRecordOptModel')->biz_unpending($record['sell_record_code']);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $remark = '退款单财务审核，订单自动解挂';
        load_model('oms/SellRecordOptModel')->add_action($record['sell_record_code'], '解挂', $remark);

        ctx()->db->commit();
        return $this->format_ret(1);
    }

    /**
     * 财务退回 check
     * @param $record
     * @param $detail
     * @param $sysuser
     * @return array
     */
    function opt_finance_reject_check($record, $detail, $sysuser) {
        //#############权限
        if (!load_model('sys/PrivilegeModel')->check_priv('oms/return_opt/opt_finance_reject')) {
            return $this->return_value(-1, "无权访问");
        }
        //###########
        if (empty($record)) {
            return $this->format_ret(-1, '', '没有找到匹配的退单');
        }

        $map_arr_finance_confirm = array('0' => '未通知财务审核', '1' => '财务已经审核通过', '3' => '财务已退回');
        $finance_confirm_msg = isset($map_arr_finance_confirm[$record['finance_check_status']]) ? $map_arr_finance_confirm[$record['finance_check_status']] : '';
        if ($finance_confirm_msg != '') {
            return $this->format_ret(-1, '', $finance_confirm_msg . '的退单不能操作');
        }
        if (in_array($record['return_order_status'], array(3))) {
            return $this->format_ret(-1, '', '已作废退单不能操作');
        }
        /*
          if (in_array($record['return_shipping_status'], array(1))) {
          return $this->format_ret(-1, '','已验收入库的退单不能操作');
          } */
        return $this->format_ret(1);
    }

    /**
     * 财务退回
     * @param $sellRecordCode
     * @param $request
     * @return array
     */
    public function opt_finance_reject($sellReturnCode, $request = array()) {
        $record = load_model('fx/SellReturnModel')->get_return_by_return_code($sellReturnCode);
        $detailList = load_model('fx/SellReturnModel')->get_detail_list_by_return_code($sellReturnCode);
        $check = $this->opt_finance_reject_check($record, $detailList);
        if ($check['status'] != '1') {
            return $check;
        }
        $sys_user = load_model('oms/SellRecordOptModel')->sys_user();
        $sql = "update oms_sell_return set finance_check_status = 0, finance_reject_time=:finance_reject_time, finance_reject_person=:finance_reject_person where sell_return_code = :sell_return_code";
        $record['finance_check_status'] = 0;
        ctx()->db->query($sql, array(':sell_return_code' => $sellReturnCode, ':finance_reject_person' => $sys_user['user_name'], ':finance_reject_time' => date('Y-m-d H:i:s')));
        load_model('fx/SellReturnModel')->add_action($record, '财务退回');
        return $this->format_ret(1);
    }

    function sys_user() {
        $ret = load_model('oms/SellRecordOptModel')->sys_user();
        return $ret;
    }

    function edit_baseinfo_check($record) {
//        if ($record['return_order_status'] > 0) {
//            return $this->format_ret(-1, '', '只有未确认的退单才能编辑');
//        }
//      if(($record['return_order_status'] == 0)|| ($record['return_order_status'] ==1 && $record['return_shipping_status'] != 1 && $record['return_type'] != 1)){
//         return $this->format_ret(1);
//     }
        if ($record['return_order_status'] == 3) {
            return $this->format_ret(-1, '', '已作废订单不可编辑');
        }
        return $this->format_ret(1, '', '');
    }

    function edit_return_person_check($record) {
//        if ($record['return_order_status'] > 0) {
//            return $this->format_ret(-1, '', '只有未确认的退单才能编辑');
//        }
//      if(($record['return_order_status'] == 0)|| ($record['return_order_status'] ==1 && $record['return_shipping_status'] != 1 && $record['return_type'] != 1)){
//         return $this->format_ret(1);
//     }
        if ($record['return_order_status'] == 3) {
            return $this->format_ret(-1, '', '已作废订单不能编辑');
        }
        return $this->format_ret(1, '', '');
    }

    function edit_return_order_check($record) {
        if ($record['return_order_status'] > 0) {
            return $this->format_ret(-1, '', '只有未确认的退单才能编辑');
        }
        return $this->format_ret(1);
    }

    function edit_return_money_check($record) {
        if ($record['return_order_status'] > 0) {
            return $this->format_ret(-1, '', '只有未确认的退单才能编辑');
        }
        return $this->format_ret(1);
    }

    function edit_return_goods_check($record) {
        if ($record['return_order_status'] > 0) {
            return $this->format_ret(-1, '', '只有未确认的退单才能编辑');
        }
        return $this->format_ret(1);
    }

    function add_return_goods_check($record) {
        if ($record['return_order_status'] > 0) {
            return $this->format_ret(-1, '', '只有未确认的退单才能编辑');
        }
        if (!empty($record['change_record'])) {
            return $this->format_ret(-1, '', '已收货的退单不能编辑');
        }
        if ($record['finance_check_status'] > 0) {
            return $this->format_ret(-1, '', '已通知财务审核不能编辑');
        }
        if ($record['return_shipping_status'] > 0 && $record['return_type'] != 1) {
            return $this->format_ret(-1, '', '已确认收货');
        }

        return $this->format_ret(1);
    }

    function edit_change_baseinfo_check($record) {
        if (!empty($record['change_record'])) {
            return $this->format_ret(-1, '', '已收货的退单不能编辑');
        }
        if ($record['finance_check_status'] == 3) {
            return $this->format_ret(-1, '', '财务已退回不能编辑');
        }
        if ($record['return_order_status'] == 3) {
            return $this->format_ret(-1, '', '已作废的退单不能编辑');
        }
        return $this->format_ret(1);
    }

    function edit_change_goods_check($record) {
        if (!empty($record['change_record'])) {
            return $this->format_ret(-1, '', '已收货的退单不能编辑');
        }
        if ($record['finance_check_status'] > 0) {
            return $this->format_ret(-1, '', '已通知财务审核不能编辑');
        }
        if ($record['return_order_status'] == 3) {
            return $this->format_ret(-1, '', '已作废的退单不能编辑');
        }
        if ($record['return_shipping_status'] > 0 && $record['return_type'] != 1) {
            return $this->format_ret(-1, '', '已确认收货');
        }
        return $this->format_ret(1);
    }

    function add_change_goods_check($record) {
        if (!empty($record['change_record'])) {
            return $this->format_ret(-1, '', '已收货的退单不能编辑');
        }
        if ($record['finance_check_status'] > 0) {
            return $this->format_ret(-1, '', '已通知财务审核不能编辑');
        }
        if ($record['return_order_status'] == 3) {
            return $this->format_ret(-1, '', '已作废的退单不能编辑');
        }
        if ($record['return_shipping_status'] > 0 && $record['return_type'] != 1) {
            return $this->format_ret(-1, '', '已确认收货');
        }

        return $this->format_ret(1);
    }

    function change_goods_del_check($record) {
        if (!load_model('sys/PrivilegeModel')->check_priv('oms/return_opt/change_goods_del')) {
            return $this->return_value(-1, "无权访问");
        }
        if (!empty($record['change_record'])) {
            return $this->format_ret(-1, '', '已收货的退单不能删除');
        }
        if ($record['finance_check_status'] > 0) {
            return $this->format_ret(-1, '', '已通知财务审核不能删除');
        }
        if ($record['return_order_status'] == 3) {
            return $this->format_ret(-1, '', '已作废的退单不能删除');
        }
        if ($record['return_shipping_status'] > 0 && $record['return_type'] != 1) {
            return $this->format_ret(-1, '', '已确认收货');
        }
        return $this->format_ret(1);
    }

    function change_goods_change_check($record) {
        if (!load_model('sys/PrivilegeModel')->check_priv('oms/return_opt/change_goods_change')) {
            return $this->return_value(-1, "无权访问");
        }
        if (!empty($record['change_record'])) {
            return $this->format_ret(-1, '', '已收货的退单不能改款');
        }
        if ($record['return_order_status'] == 3) {
            return $this->format_ret(-1, '', '已作废的退单不能改款');
        }
        if ($record['finance_check_status'] > 0) {
            return $this->format_ret(-1, '', '已通知财务审核不能编辑');
        }
        if ($record['return_shipping_status'] > 0 && $record['return_type'] != 1) {
            return $this->format_ret(-1, '', '已确认收货不能编辑');
        }
        return $this->format_ret(1);
    }

    function save_component_baseinfo($sell_return_code, $req) {
        $sql = "select * from oms_sell_return where sell_return_code='{$sell_return_code}';";
        $value = $this->db->get_row($sql);
        $record = load_model('fx/SellReturnModel')->get_return_by_return_code($sell_return_code);
        $ret = $this->edit_return_person_check($record);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $fld = 'store_code,return_express_code,return_express_no,sell_record_checkpay_status,return_pay_code,return_reason_code,return_buyer_memo,return_remark';
        $fld_arr = explode(',', $fld);
        $upd_arr = array();
        $log_arr = array();
        $i = 0;
        foreach ($fld_arr as $sub_fld) {
            if (isset($req[$sub_fld])) {
                $upd_arr[$sub_fld] = $req[$sub_fld];
                if ($value[$sub_fld] != $upd_arr[$sub_fld]) {
                    $log_arr[$sub_fld] = $upd_arr[$sub_fld];
                    $i++;
                }
            }
        }

        //echo '<hr/>$arr<xmp>'.var_export($upd_arr,true).'</xmp>';die;
        $ret = M('oms_sell_return')->update($upd_arr, array('sell_return_code' => $sell_return_code));
        //更新退货包裹单
        $where = " return_package_code ='{$value['sell_return_package_code']}'";
        $ret = $this->db->update('oms_return_package', $upd_arr, $where);
        if ($ret['status'] < 0) {
            return $ret;
        }
        if ($i != 0) {
            $message = '';
            if (isset($log_arr['store_code'])) {
                $store = oms_tb_val('base_store', 'store_name', array('store_code' => $upd_arr['store_code']));
                $old_store = oms_tb_val('base_store', 'store_name', array('store_code' => $value['store_code']));
                $message.='退货仓库由' . $old_store . '修改为' . $store . '。';
            }
            if (isset($log_arr['return_express_code'])) {
                $express_name = oms_tb_val('base_express', 'express_name', array('express_code' => $upd_arr['return_express_code']));
                $old_express_name = oms_tb_val('base_express', 'express_name', array('express_code' => $value['return_express_code']));
                $message.='退货快递公司由' . $old_express_name . '修改为' . $express_name . '。';
            }
            if (isset($log_arr['return_express_no'])) {
                $express_no = $upd_arr['return_express_no'];
                $message.='快递单号由' . $value['return_express_no'] . '修改为' . $express_no . '。';
            }
            if (isset($log_arr['sell_record_checkpay_status'])) {
                if ($upd_arr['sell_record_checkpay_status'] == 0) {
                    $status = '未确认';
                } else {
                    $status = '已确认';
                }
                if ($value['sell_record_checkpay_status'] == 0) {
                    $old_status = '未确认';
                } else {
                    $old_status = '已确认';
                }
                $message.='买家确认支付状态由' . $old_status . '修改为' . $status . '。';
            }
            if (isset($log_arr['return_pay_code'])) {
                $return_pay_name = oms_tb_val('base_refund_type', 'refund_type_name', array('refund_type_code' => $upd_arr['return_pay_code']));
                $old_return_pay_name = oms_tb_val('base_refund_type', 'refund_type_name', array('refund_type_code' => $value['return_pay_code']));
                $message.='退款方式由' . $old_return_pay_name . '修改为' . $return_pay_name . '。';
            }
            if (isset($log_arr['return_reason_code'])) {
                $return_reason_name = oms_tb_val('base_return_reason', 'return_reason_name', array('return_reason_code' => $upd_arr['return_reason_code']));
                $old_return_reason_name = oms_tb_val('base_return_reason', 'return_reason_name', array('return_reason_code' => $value['return_reason_code']));
                $message.='退货原因由' . $old_return_reason_name . '修改为' . $return_reason_name . '。';
            }
            if (isset($log_arr['return_buyer_memo'])) {
                $message.='退单说明由' . $value['return_buyer_memo'] . '修改为' . $log_arr['return_buyer_memo'] . '。';
            }
            if (isset($log_arr['return_remark'])) {
                $message.='买家退单备注由' . $value['return_remark'] . '修改为' . $log_arr['return_remark'] . '。';
            }
            load_model('fx/SellReturnModel')->add_action($value, "修改基本信息", $message);
        }

        return $this->format_ret(1);
    }

    function save_component_return_person($sell_return_code, $req) {
        $record = load_model('fx/SellReturnModel')->get_return_by_return_code($sell_return_code);
        $ret = $this->edit_return_person_check($record);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $fld = 'return_name,return_email,return_zip_code,return_phone,return_mobile,return_country,return_province,return_city,return_district,return_street,return_addr';
        $fld_arr = explode(',', $fld);
        $upd_arr = array();
        foreach ($fld_arr as $sub_fld) {
            if (isset($req[$sub_fld])) {
                $upd_arr[$sub_fld] = $req[$sub_fld];
            }
        }
        $upd_arr['return_address'] = load_model('util/ViewUtilModel')->get_address_for_each($upd_arr, 'return_country,return_province,return_city,return_district,return_street', 'return_addr');
        //echo '<hr/>$arr<xmp>'.var_export($upd_arr,true).'</xmp>';die;
        $ret = M('oms_sell_return')->update($upd_arr, array('sell_return_code' => $sell_return_code));
        if ($ret['status'] < 0) {
            return $ret;
        }
        if (($upd_arr['return_email'] != $record['return_email']) && isset($upd_arr['return_email'])) {
            load_model('fx/SellReturnModel')->add_action($record, "修改Email", $record['return_email'] . "修改为" . $upd_arr['return_email']);
        }
        if (($upd_arr['return_zip_code'] != $record['return_zip_code']) && isset($upd_arr['return_zip_code'])) {
            load_model('fx/SellReturnModel')->add_action($record, "修改邮编", $record['return_zip_code'] . " 修改为 " . $upd_arr['return_zip_code']);
        }
        if (($upd_arr['return_phone'] != $record['return_phone']) && isset($upd_arr['return_phone'])) {
            load_model('fx/SellReturnModel')->add_action($record, "修改电话", $record['return_phone'] . " 修改为 " . $upd_arr['return_phone']);
        }
        if (($upd_arr['return_name'] != $record['return_name']) && isset($upd_arr['return_name'])) {
            load_model('fx/SellReturnModel')->add_action($record, "修改退货人", $record['return_name'] . " 修改为 " . $upd_arr['return_name']);
        }
        if (($upd_arr['return_mobile'] != $record['return_mobile']) && isset($upd_arr['return_mobile'])) {
            load_model('fx/SellReturnModel')->add_action($record, "修改手机", $record['return_mobile'] . " 修改为 " . $upd_arr['return_mobile']);
        }
        if (($upd_arr['return_address'] != $record['return_address']) && isset($upd_arr['return_address'])) {
            load_model('fx/SellReturnModel')->add_action($record, "修改地址", $record['return_address'] . " 修改为 " . $upd_arr['return_address']);
        }

        return $this->format_ret(1);
    }

    function save_component_return_order($sell_return_code, $req) {
        $record = load_model('fx/SellReturnModel')->get_return_by_return_code($sell_return_code);
        $ret = $this->edit_return_order_check($record);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $fld = 'store_code,return_reason_code,return_pay_code,return_buyer_memo,service_code,return_remark';
        $fld_arr = explode(',', $fld);
        $upd_arr = array();
        foreach ($fld_arr as $sub_fld) {
            if (isset($req[$sub_fld])) {
                $upd_arr[$sub_fld] = $req[$sub_fld];
            }
        }
        $ret = M('oms_sell_return')->update($upd_arr, array('sell_return_code' => $sell_return_code));
        if ($ret['status'] < 0) {
            return $ret;
        }
        return $this->format_ret(1);
    }

    function save_component_return_money($sell_return_code, $req) {
        $record = load_model('fx/SellReturnModel')->get_return_by_return_code($sell_return_code);
        $ret = $this->edit_return_money_check($record);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $fld = 'compensate_money,seller_express_money,change_express_money,adjust_money';
        $fld_arr = explode(',', $fld);
        $upd_arr = array();
        foreach ($fld_arr as $sub_fld) {
            if (isset($req[$sub_fld])) {
                $upd_arr[$sub_fld] = $req[$sub_fld];
            }
        }
        $ret = M('oms_sell_return')->update($upd_arr, array('sell_return_code' => $sell_return_code));
        if ($ret['status'] < 0) {
            return $ret;
        }
        $ret = $this->reflush_return_info($sell_return_code);
        if ($ret['status'] < 0) {
            return $ret;
        }
        return $this->format_ret(1);
    }

    function save_component_change_baseinfo($sell_return_code, $req) {
        $record = load_model('fx/SellReturnModel')->get_return_by_return_code($sell_return_code);
        $ret = $this->edit_change_baseinfo_check($record);
        if ($ret['status'] < 0) {
            return $ret;
        }

        $fld = 'change_store_code,change_name,change_mobile,change_phone,change_express_code,change_country,change_province,change_city,change_district,change_street,change_addr';
        $fld_arr = explode(',', $fld);
        $upd_arr = array();
        foreach ($fld_arr as $sub_fld) {
            if (isset($req[$sub_fld])) {
                $upd_arr[$sub_fld] = $req[$sub_fld];
            }
        }
        $upd_arr['change_address'] = load_model('util/ViewUtilModel')->get_address_for_each($upd_arr, 'change_country,change_province,change_city,change_district,change_street', 'change_addr');

        $this->begin_trans();
        //是否重新锁定 如果原先是 需要锁定的 在改仓库的情况 下要重新锁定
        $where = "sell_return_code='{$sell_return_code}' AND   return_order_status<>3 AND return_shipping_status<>1 ";

        $status = $this->db->update('oms_sell_return', $upd_arr, $where);

        $num = $this->affected_rows();
        if ($status === false || $num != 1) {
            $this->rollback();
            return $ret;
        }



        $ret = $this->reset_lock_change_detail($sell_return_code);
        if ($ret['status'] < 1) {
            $this->rollback();
            return $ret;
        }

        $this->commit();
        //库存不足可以操作
        return $this->format_ret(1);
    }

    //如果编辑了主单退单金额信息 和 商品明细信息要刷新 实际退款总金额（退单） 和 实际应退款
    function reflush_return_info($sell_return_code) {
        $record = load_model('fx/SellReturnModel')->get_return_by_return_code($sell_return_code);
        $record_detail = load_model('fx/SellReturnModel')->get_detail_list_by_return_code($sell_return_code);
        $change_detail = load_model('fx/SellReturnModel')->get_change_detail_list_by_return_code($sell_return_code);
        $return_avg_money = 0;
        $fx_payable_money = 0;
        foreach ($record_detail as $sub_detail) {
            $return_avg_money += $sub_detail['avg_money'];
            $fx_payable_money += $sub_detail['fx_amount'];
        }
        $change_avg_money = 0;
        $change_fx_amount = 0;
        foreach ($change_detail as $sub_detail) {
            $change_avg_money += $sub_detail['avg_money'];
            $change_fx_amount += $sub_detail['fx_amount'];
        }
        $refund_total_fee = $return_avg_money + $record['seller_express_money'] + $record['compensate_money'] + $record['adjust_money'] - $change_avg_money - $record['change_express_money'];

        $upd_arr = array('refund_total_fee' => $refund_total_fee, 'return_avg_money' => $return_avg_money, 'change_avg_money' => $change_avg_money,'fx_payable_money' => $fx_payable_money,'change_fx_amount' => $change_fx_amount);
        $wh_arr = array('sell_return_code' => $sell_return_code);
        $ret = M("oms_sell_return")->update($upd_arr, $wh_arr);
        if ($ret['status'] < 0) {
            return $ret;
        }
        return $ret;
    }

    function save_component_return_goods($sell_return_code, $req) {
        $record_detail = load_model('fx/SellReturnModel')->get_detail_list_by_return_code($sell_return_code);
        foreach ($record_detail as $sub_detail) {
            $t_id = $sub_detail['sell_return_detail_id'];
            if (!isset($req[$t_id])) {
                continue;
            }
            $req_row = $req[$t_id];
            $fx_amount = !empty($req_row['fx_amount']) ? $req_row['fx_amount'] : 0;
            $trade_price = !empty($req_row['fx_amount']) ? $req_row['fx_amount'] / $req_row['note_num'] : 0;
            $upd_arr = array('note_num' => $req_row['note_num'], 'avg_money' => $req_row['avg_money'], 'recv_num' => $req_row['recv_num'],'fx_amount' => $fx_amount, 'trade_price' => $trade_price);
            $wh_arr = array('sell_return_detail_id' => $t_id);
            $ret = M('oms_sell_return_detail')->update($upd_arr, $wh_arr);
            if ($ret['status'] < 0) {
                return $ret;
            }
        }
        $ret = $this->reflush_return_info($sell_return_code);
        if ($ret['status'] < 0) {
            return $ret;
        }
        return $this->format_ret(1, $req);
    }

    function save_component_change_goods($sell_return_code, $req) {
        $record_detail = load_model('fx/SellReturnModel')->get_change_detail_list_by_return_code($sell_return_code);
        foreach ($record_detail as $sub_detail) {
            $t_id = $sub_detail['sell_change_detail_id'];
            if (!isset($req[$t_id])) {
                continue;
            }
            $req_row = $req[$t_id];
            $fx_amount = !empty($req_row['fx_amount']) ? $req_row['fx_amount'] : 0;
            $upd_arr = array('num' => $req_row['num'], 'avg_money' => $req_row['avg_money'],'fx_amount' => $fx_amount);
            $wh_arr = array('sell_change_detail_id' => $t_id);
            $ret = M('oms_sell_change_detail')->update($upd_arr, $wh_arr);
            if ($ret['status'] < 0) {
                return $ret;
            }
        }


        $ret = $this->reflush_return_info($sell_return_code);

        if ($ret['status'] < 0) {
            return $ret;
        }
        //重新锁定换货商品
        $ret = $this->reset_lock_change_detail($sell_return_code);
        //修改退货表中换货状态
        $this->db->update('oms_sell_return', array('is_exchange_goods' => '1'), array('sell_return_code' => $sell_return_code));
        return $ret;
    }

    private function reset_lock_change_detail($sell_return_code) {

        $record = load_model('fx/SellReturnModel')->get_return_by_return_code($sell_return_code);
        $sell_reocrd_info = load_model('oms/SellRecordModel')->get_record_by_code($record['sell_record_code'], 'store_code');
        $store_code = !empty($record['change_store_code']) ? $record['change_store_code'] : $sell_reocrd_info['store_code'];

        $sql = "select * from oms_sell_record_lof where record_type=3 AND record_code=:record_code ";
        $data = $this->db->get_all($sql, array(':record_code' => $sell_return_code));


        if (!empty($data)) {
            $this->begin_trans();
            $old_store_code = $data[0]['store_code'];

            $invobj = new InvOpModel($sell_return_code, 'oms_change', $old_store_code, 0, $data);
            $ret = $invobj->adjust();
            if ($ret['status'] < 0) {
                $this->rollback();
                // return $ret;
            } else {
                $this->commit();
            }
        }

        $this->begin_trans();
        $change_detail_list = load_model('fx/SellReturnModel')->get_change_detail_list_by_return_code($sell_return_code);
        if (!empty($change_detail_list)) {
            $change_detail_list = load_model('util/ViewUtilModel')->record_detail_append_goods_info($change_detail_list);

            foreach ($change_detail_list as &$list) {
                $list['store_code'] = $store_code;
                $list['sell_record_code'] = $record['sell_return_code'];
            }
        }
        $invobj = new InvOpModel($sell_return_code, 'oms_change', $store_code, 1, $change_detail_list);
        $ret = $invobj->adjust();
        if ($ret['status'] < 0) {
            $this->rollback();
        } else {
            $this->commit();
        }
        // return $ret;
        //库存不足可以操作
        return $this->format_ret(1);
    }

    function add_change_goods_by_return_goods($sell_return_code) {
        $data = $this->db->create_mapper('oms_sell_change_detail')->delete(array('sell_return_code' => $sell_return_code));
        if (!$data) {
            return $this->format_ret("-1", '', 'delete_error');
        }
        $record = load_model('fx/SellReturnModel')->get_return_by_return_code($sell_return_code);
        $record_detail = load_model('fx/SellReturnModel')->get_detail_list_by_return_code($sell_return_code);
        $change_detail = array();

        $copy_fld_arr = explode(',', 'sell_return_code,sell_record_code,deal_code,goods_code,sku_id,sku,goods_price,avg_money');
        $log_arr = array();
        foreach ($record_detail as $k => $sub_detail) {
            foreach ($copy_fld_arr as $copy_fld) {
                $change_detail[$k][$copy_fld] = $sub_detail[$copy_fld];
            }
            $change_detail[$k]['num'] = $sub_detail['note_num'];
            $log_arr[] = "SKU: {$sub_detail['sku']} ; {$sub_detail['note_num']}件";
        }
        $ret = M('oms_sell_change_detail')->insert_multi($change_detail);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $ret = load_model('fx/SellReturnModel')->add_action($record, '从退单商品中追加商品', join(' ', $log_arr));
        return $ret;
    }

    //打标
    function opt_label($sellReturnCode, $return_label_code, $request = array()) {
        $return = load_model('fx/SellReturnModel')->get_record_by_code($sellReturnCode);
        $sys_user = $this->sys_user();
        $this->begin_trans();
        try {

            $ret = load_model('fx/SellReturnTagModel')->add_return_tag($sellReturnCode, array($return_label_code));
            if ($ret['status'] < 0) {
                return $ret;
            }
            $return_label_name = $this->db->get_value("select return_label_name from base_return_label where return_label_code = '{$return_label_code}'");
            $remark = "退单打标:" . $return_label_name;
            load_model('fx/SellReturnModel')->add_action($return, '打标', $remark);
            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    /**
     * 生成换货单检查
     * @param $record
     * @param $detail
     * @param $sysuser
     * @return array
     */
    function opt_create_change_order_check($record, $detail, $change_detail) {
        //#############权限
        if (!load_model('sys/PrivilegeModel')->check_priv('oms/return_opt/opt_create_change_order')) {
            return $this->return_value(-1, "无权访问");
        }
        //###########
        if (empty($record)) {
            return $this->format_ret(-1, '', '没有找到匹配的退单');
        }
        if (empty($detail)) {
            return $this->format_ret(-1, '', '没有找到匹配的退单明细');
        }
        if (empty($change_detail)) {
            return $this->format_ret(-1, '', '没有找到匹配的换货单明细');
        }
        if (in_array($record['return_order_status'], array(3))) {
            return $this->format_ret(-1, '', '已作废退单不能操作');
        }
        if (in_array($record['return_shipping_status'], array(1))) {
            return $this->format_ret(-1, '', '已验收入库的退单不能操作');
        }
        if ($record['return_order_status'] == 0) {
            return $this->format_ret(-1, '', '未确认的退单不能操作');
        }
        if ($record['return_type'] == 1) {
            return $this->format_ret(-1, '', '退款单，不能进行此操作');
        }
        if ($record['change_record'] != '') {
            return $this->format_ret(-1, '', '已生成过换货单');
        }
        return $this->format_ret(1);
    }

    /**
     * 生成换货单
     * @param $sellRecordCode
     * @param $request
     * @return array
     */
    public function opt_create_change_order($sellReturnCode) {
        $record = load_model('fx/SellReturnModel')->get_return_by_return_code($sellReturnCode);
//         var_dump($record);
        $detailList = load_model('fx/SellReturnModel')->get_detail_list_by_return_code($sellReturnCode);
//         var_dump($detailList);var_dump($sellReturnCode);
        $change_detail = load_model('fx/SellReturnModel')->get_change_detail_list_by_return_code($sellReturnCode);


        $check = $this->opt_create_change_order_check($record, $detailList, $change_detail);
        if ($check['status'] != '1') {
            return $check;
        }
        //解锁锁定



        $ret = $this->create_change_record($record, $change_detail);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $new_sell_record_code = $ret['data'];
        load_model('fx/SellReturnModel')->add_action($record, '生成换货单', "新生成的换货单号为" . $new_sell_record_code);


        return $this->format_ret(1, $new_sell_record_code);
    }

    /**
     *
     * 方法名       auto_confirm_return_money
     *
     * 功能描述     已'确认收货'的退款退货单，3天后，系统将自动确认退款
     *
     * @author      BaiSon PHP R&D
     * @date        2015-07-24
     */
    public function auto_confirm_return_money() {
        //$current_time = time();
        $three_days_ago_time = strtotime('-3 day');
        $return_obj = load_model('fx/SellReturnModel');
        $return_arr = $return_obj->get_return_record_confirm_list($three_days_ago_time);
        if (!empty($return_arr)) {
            foreach ($return_arr as $sell_return_code) {
                $ret = $this->opt_finance_confirm($sell_return_code);
            }
        }
    }

    public function auto_checked_and_return_money() {
        $sql = "select status from sys_schedule where code = 'return_auto_checked_and_return_money'";
        $status = $this->db->get_value($sql);
        if ($status == 0) {
            return $this->format_ret(1, '', '天猫退单智能处理参数未开启');
        }
        $sql1 = "select sell_return_code from oms_sell_return where return_order_status = 0";
        $return_record1 = $this->db->get_all($sql1);
        $sql2 = "select sell_return_code from oms_sell_return where return_order_status = 1 and (finance_check_status = 0 or finance_check_status = 2)";
        $return_record2 = $this->db->get_all($sql2);

        $sql_api = 'SELECT DISTINCT r.`status` FROM `api_taobao_refund` AS r INNER JOIN `oms_sell_return_detail` AS rd ON r.`tid`=rd.`deal_code` WHERE rd.`sell_return_code`=:sell_return_code';
        foreach ($return_record1 as $record1) {
            $api_refund = $this->db->get_all($sql_api, array(':sell_return_code' => $record1['sell_return_code']));
            if (empty($api_refund)) {
                continue;
            }
            $check = 1;
            foreach ($api_refund as $val) {
                if ($val['status'] != 'WAIT_BUYER_RETURN_GOODS') {
                    $check = 0;
                    break;
                }
            }
            if ($check == 1) {
                $this->opt_confirm($record1['sell_return_code']);
            }
        }

        foreach ($return_record2 as $record2) {
            $api_refund2 = $this->db->get_all($sql_api, array(':sell_return_code' => $record2['sell_return_code']));
            if (empty($api_refund2)) {
                continue;
            }
            $check = 1;
            foreach ($api_refund2 as $val) {
                if ($val['status'] != 'SUCCESS') {
                    $check = 0;
                    break;
                }
            }
            if ($check == 1) {
                $this->opt_finance_confirm($record2['sell_return_code']);
            }
        }
    }

    //订单直接操作作废时生成仅退款退单  type='direct_cancel'
    //订单发货，金额存在已付>应付，生成退款类型售后服务单 type=‘delivery’
    public function create_return_record_by_cancel($sell_record_code, $type) {
        //取新退单主表的数据
        $sell_record_info = load_model('oms/SellRecordModel')->get_record_by_code($sell_record_code);
        $detail_record_info = load_model('oms/SellRecordModel')->get_detail_list_by_code($sell_record_code);

        $return_info = array(
            'sell_record_code' => $sell_record_info['sell_record_code'],
            'deal_code' => $sell_record_info['deal_code_list'],
            'shop_code' => $sell_record_info['shop_code'],
            'sale_channel_code' => $sell_record_info['sale_channel_code'],
            'relation_shipping_status' => $sell_record_info['shipping_status'],
            'return_pay_code' => $sell_record_info['pay_code'],
            'return_order_status' => 0,
            'return_shipping_status' => 0,
            'customer_code' => $sell_record_info['customer_code'],
            'buyer_name' => $sell_record_info['buyer_name'],
            'return_name' => $sell_record_info['receiver_name'],
            'return_country' => $sell_record_info['receiver_country'],
            'return_province' => $sell_record_info['receiver_province'],
            'return_city' => $sell_record_info['receiver_city'],
            'return_district' => $sell_record_info['receiver_district'],
            'return_street' => $sell_record_info['receiver_street'],
            'return_address' => $sell_record_info['receiver_address'],
            'return_addr' => $sell_record_info['receiver_addr'],
            'return_zip_code' => $sell_record_info['receiver_zip_code'],
            'return_mobile' => $sell_record_info['receiver_mobile'],
            'return_phone' => $sell_record_info['receiver_phone'],
            'return_email' => $sell_record_info['receiver_email'],
            'change_name' => $sell_record_info['receiver_name'],
            'change_country' => $sell_record_info['receiver_country'],
            'change_province' => $sell_record_info['receiver_province'],
            'change_city' => $sell_record_info['receiver_city'],
            'change_district' => is_null($sell_record_info['receiver_district']) ? '' : $sell_record_info['receiver_district'],
            'change_street' => $sell_record_info['receiver_street'],
            'change_address' => $sell_record_info['receiver_address'],
            'change_addr' => $sell_record_info['receiver_addr'],
            'change_mobile' => $sell_record_info['receiver_mobile'],
            'change_phone' => $sell_record_info['receiver_phone']
        );

        $ret = $this->get_return_store_code($sell_record_info['shop_code']);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $return_info['store_code'] = $ret['data'];
        $return_info['return_type'] = 1;
        $return_info['create_time'] = date('Y-m-d H:i:s');

        if (load_model('oms/SellRecordModel')->return_log_check != 0) {
            $sysuser = $this->sys_user();
            $return_info['create_person'] = $sysuser['user_name'];
        } else {
            $return_info['create_person'] = '系统管理员';
        }

        $return_info['return_avg_money'] = 0;
        if ($type == 'direct_cancel') {
            $return_info['seller_express_money'] = number_format($sell_record_info['express_money']);
            foreach ($detail_record_info as $sub_detail) {
                if (isset($sub_detail['is_delete']) && $sub_detail['is_delete'] == 1) {
                    continue;
                }
                $return_info['return_avg_money'] += $sub_detail['avg_money'];
            }
        }
        if ($type == 'delivery') {
            $return_info['seller_express_money'] = 0;
            $return_info['return_avg_money'] = $sell_record_info['paid_money'] - $sell_record_info['payable_money'];
        }

        $return_info['return_reason_code'] = '';
        $return_info['return_remark'] = '';
        $return_info['sell_record_checkpay_status'] = 1;
        //实际总退款金额（610） = 退单商品均摊金额合计（600） + 卖家承担运费（10） + 赔付金额（5） + 手工调整金额（-5）
        $return_info['refund_total_fee'] = $return_info['return_avg_money'] + $return_info['seller_express_money'] + 0 + 0;
        $sell_return_code = load_model('util/CreateCode')->get_code('oms_sell_return');
        $return_info['sell_return_code'] = $sell_return_code;

        $ins_ret = M('oms_sell_return')->insert($return_info);
        if ($ins_ret['status'] < 0) {
            return $ins_ret;
        }
        $record_data = $this->db->get_row("select * from oms_sell_return where sell_return_code=:sell_return_code", array(':sell_return_code' => $sell_return_code));
        load_model('fx/SellReturnModel')->add_action($record_data, '生成退单', '订单作废，生成仅退款类型的售后服务单');
        return $this->format_ret('1', $sell_return_code);
    }

    //批量转退款单
    public function opt_return_money($sellReturnCode, $request = array()) {
        $table = 'oms_sell_return';
        try {
            $this->begin_trans();
            $sql = "SELECT sell_record_code,return_type,return_reason_code,return_remark,return_buyer_memo,return_pay_code,return_express_code,return_express_no,is_compensate,is_packet_out_stock,sell_record_checkpay_status,store_code,return_avg_money FROM {$table} WHERE sell_return_code = '{$sellReturnCode}';";
            $return_record = $this->db->get_row($sql);
            if ($return_record['return_type'] == 3) {
                $return_record['return_type'] = 1;

                //先作废退单
                $ret = $this->opt_cancel($sellReturnCode, array('cancel_type' => 'return_money'));
                if ($ret['status'] != 1) {
                    return $ret;
                }

                //生成新的退单
                $msg = '由编号为：' . $sellReturnCode . '的退款退货单，生成的仅退款单。';
                $ret = $this->create_return($return_record, $return_record['sell_record_code'], $return_record['return_type'], $return_record['store_code'], 1, $msg);
                if ($ret['status'] != 1) {
                    return $ret;
                }
                $this->commit();
                return $this->format_ret(1, '', '转退单成功');
            } else {
                return $this->format_ret('-1', $sellReturnCode, '只能是退款退货单');
            }
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    //快速入库
    function opt_confirm_return_shipping($sell_return_code) {
        $this->begin_trans();
        //确认
        $ret = $this->opt_confirm($sell_return_code);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $ret;
        }
        //确认收货
        $ret = $this->opt_return_shipping($sell_return_code);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $ret;
        }
        $this->commit();
        return $ret;
    }

    /**
     * 快速入库权限
     * @param $record
     * @param $detail
     * @param $sysuser
     * @return array
     */
    function opt_confirm_return_shipping_check($record, $detail, $sysuser) {
        //#############权限
//        if (!load_model('sys/PrivilegeModel')->check_priv('oms/return_opt/opt_confirm')) {
//            return $this->return_value(-1, "无权访问");
//        }
        //###########
        if (empty($record)) {
            return $this->format_ret(-1, '', '没有找到匹配的退单');
        }
        if ($record['return_order_status'] == 1) {
            return $this->format_ret(-1, '', '已确定退单不能操作');
        }
        if ($record['return_order_status'] == 3) {
            return $this->format_ret(-1, '', '已作废退单不能操作');
        }
        if (in_array($record['return_shipping_status'], array(1))) {
            return $this->format_ret(-1, '', '已验收入库的退单不能操作');
        }
        //#############权限
//        if (empty($sysuser) ||  $sysuser != 'open_api') {
//            if (!load_model('sys/PrivilegeModel')->check_priv('oms/return_opt/opt_return_shipping')) {
//                return $this->return_value(-1, "无权访问");
//            }
//        }
        if (empty($detail)) {
            return $this->format_ret(-1, '', '没有找到匹配的退单明细');
        }
        if ($record['return_type'] == 1) {
            return $this->format_ret(-1, '', '退款单，不能进行此操作');
        }
        if ($is_wms == 0) {
            $ret = load_model('wms/WmsEntryModel')->check_wms_store($record['store_code']);
            if ($ret['status'] > 0) {
                return $this->format_ret(-1, '', '退单仓库对接wms，不允许手工验收入库');
            }
        }

        return $this->format_ret(1);
    }

}
