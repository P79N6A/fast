<?php

require_model('tb/TbModel');
require_lang('oms');

/**
 * 订单业务相关接口
 * @author WMH
 */
class SellRecordApiModel extends TbModel {

    /**
     * 回写快递单号
     * @author wmh
     * @date 2017-05-19
     * @param array $params 接口参数
     * <pre> 必选 'sell_record_code',express_code',express_no'
     * <pre> 可选 'remark'
     * @return array 操作结果
     */
    public function api_express_return($params) {
        $key_required = array(
            's' => array('sell_record_code', 'express_code', 'express_no'),
        );
        $r_required = array();
        $ret_required = valid_assign_array($params, $key_required, $r_required, TRUE);
        if ($ret_required['status'] !== TRUE) {
            return $this->format_ret(-10001, $ret_required['req_empty'], 'API_RETURN_MESSAGE_10001');
        }
        $record_wh = array('sell_record_code' => $r_required['sell_record_code']);

        $record = $this->check_record_exists('oms_sell_record', 'sell_record_code', $r_required['sell_record_code']);
        if (empty($record)) {
            return $this->format_ret(-10002, $record_wh, '订单不存在');
        }
        if ($record['shipping_status'] == 4) {
            return $this->format_ret(-1, $record_wh, '订单已发货');
        }

        $express = $this->check_record_exists('base_express', 'express_code', $r_required['express_code'], 'company_code,express_code');
        if (empty($express)) {
            return $this->format_ret(-10002, array('express_code' => $r_required['express_code']), '物流公司不存在');
        }
        $this->begin_trans();
        //更新订单快递信息
        $order_remark = empty($params['remark']) ? '' : $params['remark'];
        $up_record = array('express_code' => $r_required['express_code'], 'express_no' => $r_required['express_no'], 'order_remark' => $order_remark);
        $ret = $this->update_exp('oms_sell_record', $up_record, $record_wh);
        if ($ret['status'] < 1 || $this->affected_rows() != 1) {
            $this->rollback();
            return $this->format_ret(-1, '', '订单快递回写失败');
        }

        //插入网单回写记录
        $status = ($record['sale_channel_code'] == 'houtai') ? '2' : '0';
        $data = array(
            'source' => $record['sale_channel_code'],
            'shop_code' => $record['shop_code'],
            'sell_record_code' => $record['sell_record_code'],
            'express_code' => $express['express_code'],
            'company_code' => $express['company_code'],
            'express_no' => $r_required['express_no'],
            'send_time' => date("Y-m-d H:i:s"),
            'status' => $status
        );

        $api_send_data = array();
        $deal_code_list = explode(',', $record['deal_code_list']);
        foreach ($deal_code_list as $deal_code) {
            $data['tid'] = $deal_code;
            $api_send_data[] = $data;
        }
        $ret = $this->insert_multi_exp('api_order_send', $api_send_data, TRUE);
        if ($ret['status'] < 0) {
            $this->rollback();
            return $this->format_ret(-1, '', '订单快递回写失败');
        }
        $obj_aciton = load_model('oms/SellRecordActionModel');
        $obj_aciton->record_log_check = 0;
        load_model('oms/SellRecordActionModel')->add_action($r_required['sell_record_code'], '快递回写', "快递公司：[ {$r_required['express_code']} ],快递单号：[ {$r_required['express_no']} ]");
        $obj_aciton->record_log_check = 1;

        $this->commit();

        return $this->format_ret(1, '', '回写成功');
    }

    /**
     * 检查订单存在
     * @param string $sell_record_code 订单号
     * @param string $fld 字段
     * @return array
     */
    private function check_record_exists($table, $wh_fld, $wh_value, $fld = '*') {
        $sql = "SELECT {$fld} FROM {$table} WHERE {$wh_fld}=:code";
        return $this->db->get_row($sql, array(':code' => $wh_value));
    }

    /**
     * 快递交接扫描
     * @author wmh
     * @date 2017-06-16
     * @param array $param 接口参数
     * @return array 操作结果
     */
    public function api_package_receive_scan($param) {
        $key_required = array(
            's' => array('express_no', 'opt_user_code'),
        );
        $r_required = array();
        $ret_required = valid_assign_array($param, $key_required, $r_required, TRUE);
        if ($ret_required['status'] !== TRUE) {
            return $this->format_ret(-10001, $ret_required['req_empty'], 'API_RETURN_MESSAGE_10001');
        }
        $express_no = $r_required['express_no'];
        $user_code = $r_required['opt_user_code'];
        unset($param, $r_required);
        $user_name = $this->get_user_name_by_code($user_code);
        if (empty($user_name)) {
            return $this->format_ret(-1, array('opt_user_code' => $user_code), '用户不存在');
        }

        $ret = load_model('oms/PackageDeliveryReceivedModel')->express_scan_receive($express_no);
        if ($ret['status'] == -1) {
            return $ret;
        }
        $record = $ret['data'];

        //添加日志
        $log_data = array(
            'action_name' => '快递交接',
            'action_note' => 'API-快递交接扫描。' . $ret['message'],
            'user_code' => $user_code,
            'user_name' => $user_name,
        );
        load_model('oms/SellRecordActionModel')->api_add_action($record['sell_record_code'], $log_data);

        $revert_data = array(
            'sell_record_code' => $record['sell_record_code'],
            'express_code' => $record['express_code'],
            'express_name' => $record['express_name'],
            'express_no' => $express_no
        );

        $status = $ret['status'] < 1 ? -1 : 1;
        return $this->format_ret($status, $revert_data, $ret['message']);
    }

    /**
     * 快递交接统计
     * @author wmh
     * @date 2017-06-16
     * @param array $param 接口参数
     * @return array 操作结果
     */
    public function api_package_census_get($param) {
        $sql_values = array();
        $sql_main = 'SELECT sr.store_code,sr.express_code,sr.delivery_date FROM oms_sell_record AS sr WHERE sr.shipping_status = 4';

        if (!empty($param['store_code'])) {
            $sql_main .= " AND sr.store_code = :store_code ";
            $sql_values[':store_code'] = $param['store_code'];
        }
        if (empty($param['start_time'])) {
            $param['start_time'] = date('Y-m-d');
        }
        $sql_main .= " AND sr.delivery_date >= :start_time ";
        $sql_values[':start_time'] = $param['start_time'];
        if (empty($param['end_time'])) {
            $param['end_time'] = date('Y-m-d');
        }
        $sql_main .= " AND sr.delivery_date <= :end_time ";
        $sql_values[':end_time'] = $param['end_time'];

        $sql_main .= " GROUP BY sr.store_code,sr.express_code";
        $data = $this->db->get_all($sql_main, $sql_values);
        filter_fk_name($data, array('store_code|store', 'express_code|express'));

        $revert_data = array();
        foreach ($data as $row) {
            $temp = array();
            $temp['store_code'] = $row['store_code'];
            $temp['store_name'] = $row['store_code_name'];
            $temp['express_code'] = $row['express_code'];
            $temp['express_name'] = $row['express_code_name'];

            $sql = 'SELECT COUNT(1) FROM oms_sell_record WHERE store_code =:store_code AND express_code=:express_code 
                    AND delivery_date>=:start_time AND delivery_date<=:end_time';
            $sql_val = array(':store_code' => $temp['store_code'], ':express_code' => $temp['express_code'], ':start_time' => $param['start_time'], ':end_time' => $param['end_time']);
            $temp['deliver_num'] = $this->db->get_value($sql, $sql_val);

            $sql .= " AND is_receive =:is_receive ";
            $sql_val[':is_receive'] = 1;
            $temp['receive_num'] = $this->db->get_value($sql, $sql_val);

            $revert_data[] = $temp;
        }

        return $this->format_ret(1, $revert_data);
    }

    private function get_user_name_by_code($user_code) {
        $sql = 'SELECT user_name FROM sys_user WHERE user_code=:user_code';
        return $this->db->get_value($sql, array(':user_code' => $user_code));
    }

    /**
     * 打印数据结构获取
     * 根据对应接口数据组装结构数据
     * @author wmh
     * @date 2018-02-02
     * @param array $param 接口参数
     * @return array 操作结果
     */
    public function api_deliver_print_struct_get($param) {
        $data = [];
        $type_arr = [1];
        if (!isset($param['struct_type']) || !in_array($param['struct_type'], $type_arr)) {
            return $this->format_ret(-10005, (object) array(), '打印单据类型错误');
        }
        switch ($param['struct_type']) {
            case 1:
                $struct = [
                    [
                        'sell_record_code' => '系统订单号',
                        'order_status' => '订单状态',
                        'shipping_status' => '发货状态',
                        'pay_status' => '付款状态',
                        'deal_code_list' => '平台交易号',
                        'sale_channel_code' => '销售平台',
                        'shop_code' => '店铺代码',
                        'shop_name' => '店铺名称',
                        'customer_code' => '会员代码',
                        'buyer_name' => '会员昵称',
                        'receiver_name' => '收货人',
                        'receiver_country' => '国家编码',
                        'receiver_province' => '省(名称)',
                        'receiver_province_code' => '省(编码)',
                        'receiver_city' => '市(名称)',
                        'receiver_city_code' => '市(编码)',
                        'receiver_district' => '区(名称)',
                        'receiver_district_code' => '区(编码)',
                        'receiver_street' => '街道(名称)',
                        'receiver_street_code' => '街道(编码)',
                        'receiver_address' => '收货地址(包含省市区)',
                        'receiver_addr' => '收货地址(不含省市区)',
                        'receiver_zip_code' => '收货邮编',
                        'receiver_mobile' => '收货手机号码',
                        'receiver_phone' => '收货电话',
                        'receiver_email' => '收货邮箱',
                        'express_code' => '配送方式代码',
                        'express_no' => '快递单号',
                        'buyer_remark' => '买家留言',
                        'seller_remark' => '商家备注',
                        'order_remark' => '订单备注',
                        'store_remark' => '仓库留言',
                        'change_record' => '换货单状态',
                        'invoice_title' => '发票抬头',
                        'invoice_content' => '发票内容',
                        'goods_num' => '商品总数量',
                        'express_money' => '运费',
                        'payable_money' => '订单应付款',
                        'paid_money' => '订单已付款',
                        'order_money' => '订单总金额',
                        'discount_fee' => '优惠金额',
                        'fx_payable_money' => '分销结算金额',
                        'fx_express_money' => '分销结算运费',
                        'record_time' => '下单时间',
                        'pay_time' => '付款时间',
                        'is_notice_time' => '通知配货时间',
                        'delivery_time' => '发货时间',
                        'sign_time' => '签收时间',
                        'pay_type' => '支付类型',
                        'pay_code' => '支付代码',
                        'lastchanged' => '订单最后更新时间',
                        'detail_list' => [
                            [
                                'sell_record_code' => '系统订单号',
                                'deal_code' => '平台交易号',
                                'sub_deal_code' => '平台子交易号',
                                'goods_code' => '商品代码',
                                'goods_name' => '商品名称',
                                'spec1_code' => '规格1代码',
                                'spec1_name' => '规格1名称',
                                'spec2_code' => '规格2代码',
                                'spec2_name' => '规格2名称',
                                'barcode' => '商品条形码',
                                'combo_sku' => '套餐sku',
                                'combo_barcode' => '套餐商品条码',
                                'goods_shelf' => '商品绑定库位',
                                'goods_price' => '商品单价',
                                'cost_price' => '成本单价',
                                'num' => '商品数量',
                                'avg_money' => '均摊金额',
                                'fx_amount' => '分销结算金额',
                                'trade_price' => '分销单价',
                                'platform_name' => '平台商品名称',
                                'platform_spec' => '平台规格',
                                'is_gift' => '礼品标识',
                                'api_refund_num' => '平台退货数',
                                'api_refund_desc' => '平台退货描述',
                                'return_money' => '退款金额',
                                'goods_thumb_img' => '商品缩略图地址',
                            ]
                        ],
                    ]
                ];
                $data = ['data' => $struct];
                break;
            default:
                break;
        }


        return $this->format_ret(1, (object) $data, '打印数据结构');
    }

}
