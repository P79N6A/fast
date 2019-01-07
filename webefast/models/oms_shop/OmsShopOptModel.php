<?php

require_lib('util/oms_util', true);
require_model('tb/TbModel');
require_lang('oms');

class OmsShopOptModel extends TbModel {

    private $opt_arr = array();

    function __construct() {
        parent::__construct();
        $this->opt_arr = array('cancel', 'cancel_pay', 'pay', 'send');
    }

    function opt_action(&$record_data, &$opt, $params = array()) {
        $mod = $this->get_opt_mod($opt);
        $ret = $mod->check($record_data['record_data'], $record_data['record_detail']);
        if ($ret['status'] > 0) {
            $ret = $mod->opt($params);
        }
        return $ret;
    }

    function opt_record($record_code, $opt, $params = array()) {
        $data = $this->get_record_info($record_code);
        if (empty($data['record_data'])) {
            return $this->format_ret(-1, '', '单据编号异常找不到对应数据');
        }

        $opt_str = "opt_" . $opt;
        $check = load_model('sys/PrivilegeModel')->check_priv('oms/oms_shop/' . $opt_str);
        if ($check === FALSE) {
            return $this->format_ret(-1, '', '没有操作权限');
        }

        $this->begin_trans();
        $ret = $this->opt_action($data, $opt, $params);
        if ($ret['status'] < 1) {
            $this->rollback();
        } else {
            $this->commit();
        }

        return $ret;
    }

    function check_opt_priv($record_code, $opt_params = '') {
        $opt_arr = array();
        if ($opt_params == '') {
            $opt_arr = $this->opt_arr;
        } else {
            $opt_arr = explode(",", $opt_params);
        }
        $opt_priv_arr = array();

        $record_info = $this->get_record_info($record_code);

        foreach ($opt_arr as $opt_val) {
            $opt_str = in_array($opt_val, $this->opt_arr) ? 'opt' : "opt_" . $opt_val;
            $check = load_model('sys/PrivilegeModel')->check_priv('oms/oms_shop/' . $opt_str);
            if ($check) {
                $mod = $this->get_opt_mod($opt_val);
                $ret_check = $mod->check($record_info['record_data'], $record_info['record_detail']);
                if ($ret_check['status'] < 0) {
                    $check = false;
                }
            }
            $opt_priv_arr[$opt_val] = $check === TRUE ? 1 : 0;
        }

        $opt_priv_arr['print_ticket'] = 1;
        if ($opt_priv_arr['pay'] == 1 || $opt_priv_arr['cancel'] == 0) {
            $opt_priv_arr['print_ticket'] = 0;
        }
        return $this->format_ret(1, $opt_priv_arr);
    }

    private function get_opt_mod($opt) {
        $opt_str = ucfirst($opt);
        $opt_arr = explode("_", $opt);
        if (count($opt_arr) > 1) {
            $opt_str = '';
            foreach ($opt_arr as $v) {
                $opt_str .= ucfirst($v);
            }
        }
        $class = "OmsShopOpt" . $opt_str . "Model";
        return load_model('oms_shop/opt/' . $class);
    }

    function get_record_info($record_code) {
        $data['record_data'] = $this->get_record_data($record_code);
        $data['record_detail'] = $this->get_record_detail($record_code);
        return $data;
    }

    function get_record_data($record_code) {
        $sql = "select * from oms_shop_sell_record where record_code=:record_code";
        $data = $this->db->get_row($sql, array(':record_code' => $record_code));
        return $data;
    }

    function get_record_detail($record_code) {
        $sql = "select rd.*,sr.send_store_code store_code from oms_shop_sell_record_detail rd left join oms_shop_sell_record sr on rd.record_code=sr.record_code  where rd.record_code=:record_code";
        $data = $this->db->get_all($sql, array(':record_code' => $record_code));
        return $data;
    }

    function get_record_detail_lof($record_code) {
        $sql = "select * from oms_sell_record_lof where record_code=:record_code AND record_type = 4 ";
        $data = $this->db->get_all($sql, array(':record_code' => $record_code));
        return $data;
    }

    function get_record_pay_info($record_code) {
        $sql = "select rp.*,bp.pay_type_name from oms_shop_sell_record_pay rp left join base_pay_type bp on rp.pay_code = bp.pay_type_code where rp.record_code=:record_code";
        $data = $this->db->get_all($sql, array(':record_code' => $record_code));
        return $data;
    }

    function get_record_discount_info($record_code) {
        $sql = "select * from oms_shop_sell_record_discount where record_code=:record_code";
        $data = $this->db->get_all($sql, array(':record_code' => $record_code));
        return $data;
    }

    /**
     * @todo 新增多条明细
     */
    function opt_new_multi_detail($request, $is_skip_lock = 0) {
        $record_code = $request['record_code'];
        $record_detail = load_model('oms_shop/OmsShopModel')->get_deal_detail_by_code($record_code, 'sell_goods_id');

        $record = $this->get_record_data($record_code);
        if ($record['check_status'] == 1) {
            return $this->format_ret(-1, '', '订单被确认不能操作！');
        }

        $this->begin_trans();
        try {
            $log = '';
            foreach ($request['data'] as $k => $v) {
                if (!is_array($v) || empty($v['num']) || $v['num'] < 0) {
                    continue;
                }
                if (!isset($v['sum_money'])) {
                    $v['sum_money'] = -1;
                }
                $is_gift = isset($v['is_gift']) ? 1 : 0;
                $detail = $this->opt_new_detail($record_code, $v['sku'], $v['num'], $v['sum_money'], $is_gift);

                if ($detail['status'] != 1) {
                    $this->rollback();
                    return $this->format_ret(-1, $r['message']);
                }
                $log .= "商品条码:" . $v['barcode'] . ";数量:" . $v['num'] . ";均摊金额:" . $detail['data']['avg_money'];
            }
            $new_record_detail = load_model('oms_shop/OmsShopModel')->get_deal_detail_by_code($record_code, 'sell_goods_id');

            //刷新订单数据
            $ret = $this->write_back_record($record, $record_detail, $new_record_detail, $record_code, $is_skip_lock);
            if ($ret['status'] != 1) {
                $this->rollback();
                return $ret;
            }
            if ($ret['status'] == 1) {
                $log .= " 实物库存锁定：" . $ret['message'];
            }
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
        return $this->format_ret(1);
    }

    /**
     * @todo 添加明细
     */
    function opt_new_detail($record_code, $sku, $num, $sum, $is_gift = 0) {
        $record = $this->get_record_data($record_code);
        if (empty($record)) {
            return $this->format_ret(-1, '', '订单不存在');
        }
        if ($is_gift == 1) {
            $sum = 0;
        }
        $sql_sku = "SELECT * FROM goods_sku WHERE sku = :sku";
        $sku_info = $this->db->get_row($sql_sku, array(':sku' => $sku));
        if (empty($sku_info)) {
            return $this->format_ret(-1, '', 'SKU不存在:' . $sku);
        }

        $sql_goods = "SELECT * FROM base_goods WHERE goods_code = :goods_code";
        $goods = $this->db->get_row($sql_goods, array(':goods_code' => $sku_info['goods_code']));
        if (empty($goods)) {
            return array('status' => -1, 'message' => '商品不存在');
        }

        try {
            if ($sum < 0) {
                //当转入价格小于0, 重新计算商品金额
                $sum = $goods['sell_price'] * $num;
            }
            $detail = array(
                'record_code' => $record_code,
                'goods_code' => $sku_info['goods_code'],
                'sku' => $sku_info['sku'],
                'price' => $goods['sell_price'],
                'rebate' => '1',
                'is_delete' => 0,
                'avg_money' => $sum,
                'goods_amount' => $sum,
                'num' => $num,
                'is_gift' => $is_gift,
            );
            if ($is_gift == 0) {
                $update_str = "num = VALUES(num), avg_money = VALUES(avg_money), goods_amount = VALUES(goods_amount)";
            } else {
                $update_str = "num = VALUES(num) + num , avg_money = VALUES(avg_money) + avg_money , goods_amount = VALUES(goods_amount)+goods_amount";
            }
            $ret = $this->insert_multi_duplicate('oms_shop_sell_record_detail', array($detail), $update_str);
            if ($ret['status'] < 1) {
                return $this->format_ret(-1, '', '保存订单明细出错');
            }
        } catch (Exception $e) {
            return array('status' => -1, 'message' => '保存失败:' . $e->getMessage());
        }

        $detail['sell_goods_id'] = $ret['data'];

        return array('status' => 1, 'data' => $detail);
    }

    /**
     * @todo 回写订单主信息
     */
    function write_back_record($record, $old_detail, $new_detail, $record_code, $is_skip_lock = 0) {
        if (empty($record)) {
            $record = $this->get_record_data($record_code);
        }
        //新的单据明细
        if (empty($new_detail)) {
            $new_detail = load_model('oms_shop/OmsShopModel')->get_deal_detail_by_code($record_code);
        }
        //主单价格
        $ret = $this->deal_record($record, $new_detail);
        if ($ret['status'] != 1) {
            return $ret;
        }
        $payable_money = $ret['data']['payable_money'];

        $ret = $this->update_record($ret['data']);
        if ($ret['status'] != 1) {
            return $ret;
        }

        $pay_info = $this->get_record_pay_info($record_code);
        $pay_code_arr = array_column($pay_info, 'pay_code');

//        //释放库存
//        if (!empty($old_detail) && $record['must_occupy_inv'] == 1) {
//            $ret = $this->lock_detail($record, $old_detail, 0);
//            if ($ret['status'] < 0) {
//                return $ret;
//            }
//        }
        //如果应付>已付的非COD单子，要返回释放库存，返回未付款状态
//        if (in_array('cod', $pay_code_arr) && bccomp(floatval($payable_money), floatval($record['buyer_real_amount']), 2) > 0 && $record['cancel_status'] = 0 && $record['send_status'] == 0) {
//            $upd = array('lock_inv_status' => 0, 'pay_status' => $record['buyer_real_amount'] > 0 ? 1 : 0, 'pay_time' => '0000-00-00 00:00:00');
//            $ret = M('oms_sell_record')->update($upd, array('record_code' => $record['record_code']));
//            if ($ret['status'] < 0) {
//                return $ret;
//            }
//            $ret_status = 2;
//        } else {
//            //重新占用库存
//            if (!empty($new_detail)) {
//                $record['lock_inv_status'] = 0;
//                foreach ($new_detail as $k => $sub_detail) {
//                    $new_detail[$k]['lock_num'] = 0;
//                }
//
////                $ret = $this->opt_action(array('record_data' => $record, 'record_detail' => $new_detail), 'inv', array('inv_status' => 1));
////                if ($ret['status'] < 0) {
////                    return $ret;
////                }
//            }
//            $ret_status = 1;
//        }
        return $this->format_ret(1, $ret['data'], $ret['message']);
    }

    /**
     * @todo 重新计算订单主信息
     */
    function deal_record($record, $record_detail) {
        //订单应付款
        $record['payable_amount'] = 0;
        //商品总额
        $record['record_amount'] = 0;
        //商品总数量
        $record['goods_num'] = 0;
        //商品SKU数量
        $record['sku_num'] = 0;

        $sku_num_arr = array();
        foreach ($record_detail as $sub_detail) {
            $sku_num_arr[$sub_detail['sku']] = 1;
            if (isset($sub_detail['price'])) {
                $record['record_amount'] += $sub_detail['price'] * $sub_detail['num'];
            }
            $record['goods_num'] += $sub_detail['num'];
            $record['payable_amount'] += $sub_detail['goods_amount'];
        }
        $record['payable_amount'] += $record['express_money'];
        $record['sku_num'] = count($sku_num_arr);

        return $this->format_ret(1, $record);
    }

    /**
     * @todo 更新订单主单信息（主要是价格）
     */
    function update_record($result) {
        $upd_fld_arr = array('payable_amount', 'record_amount', 'goods_num', 'sku_num'); //更新字段
        $upd = array();
        foreach ($upd_fld_arr as $fld) {
            $upd[$fld] = $result[$fld];
        }
        $ret = M('oms_shop_sell_record')->update($upd, array('record_code' => $result['record_code']));
        return $ret;
    }

    /**
     * 保存明细信息
     */
    function opt_save_detail($param) {
        if ($param['num'] <= 0) {
            return $this->format_ret(-1, '', '订单商品数量必须大于0');
        }

        $record = $this->get_record_data($param['record_code']);
        if (empty($record)) {
            return $this->format_ret(-1, '', '订单不存在');
        }

        $record_detail = load_model('oms_shop/OmsShopModel')->get_deal_detail_by_code($param['record_code'], 'sell_goods_id');
        $cur_detail = $record_detail[$param['sell_goods_id']];

        if (empty($cur_detail)) {
            return $this->format_ret(-1, '', '订单明细不存在');
        }

        $this->begin_trans();
        try {
            $detail = array(
                'num' => $param['num'],
            );

            $result = $this->update_exp('oms_shop_sell_record_detail', $detail, array('sell_goods_id' => $param['sell_goods_id']));
            if ($result['status'] != 1) {
                $this->rollback();
                return $result;
            }

            //刷新订单数据
            $ret = $this->write_back_record($record, $record_detail, null, $param['record_code']);
            if ($ret['status'] != 1) {
                $this->rollback();
                return $ret;
            }

            //商品编码:1006034101 颜色:大红 尺码:42 數量:2 价格:99
            //$log_msg .= "商品条码:" . $cur_detail['barcode'] . ";数量:" . $num . ";均摊金额:" . $avg_money;

            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', '保存失败:' . $e->getMessage());
        }
        return $this->format_ret(1);
    }

    /**
     * 删除明细
     */
    function opt_delete_detail($record_code, $detail_id, $is_gift = 0) {
        $this->begin_trans();
        $record_detail = load_model('oms_shop/OmsShopModel')->get_deal_detail_by_code($record_code, 'sell_goods_id');
        if (empty($record_detail[$detail_id])) {
            return $this->format_ret(1);
        }

        try {
            $where = "";
            if ($is_gift == 1) {
                $where = "and is_gift = 1";
            }
            $sql_detail = "SELECT * FROM oms_shop_sell_record_detail WHERE sell_goods_id = :sell_goods_id " . $where . "";
            $oms_sell_detail = $this->db->get_row($sql_detail, array('sell_goods_id' => $detail_id));
            $sql = "DELETE FROM oms_shop_sell_record_detail WHERE sell_goods_id = :sell_goods_id " . $where . "";
            $result = $this->db->query($sql, array('sell_goods_id' => $detail_id));
            if ($result === FALSE) {
                $this->rollback();
                return $this->format_ret("-1", '', 'SELL_RECORD_DETAIL_NO_DATA');
            }

            //刷新订单数据
            $ret = $this->write_back_record(null, $record_detail, null, $record_code);
            if ($ret['status'] < 0) {
                $this->rollback();
                return $ret;
            }
            $barcode_info = load_model('goods/SkuCModel')->get_sku_info($oms_sell_detail['sku'], array('barcode'));
            $log_msg = "商品条码:" . $barcode_info['barcode'] . ';数量:' . $oms_sell_detail['num'] . ';均摊金额' . $oms_sell_detail['avg_money'];
            if ($ret['status'] == 1) {
                $log_msg .= " 实物库存锁定：" . $ret['message'];
            }

            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', '操作失败:' . $e->getMessage());
        }
    }

}
