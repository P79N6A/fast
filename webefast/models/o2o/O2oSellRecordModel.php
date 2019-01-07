<?php
require_model('tb/TbModel');
class O2oSellRecordModel extends TbModel {

    function __construct() {
        parent::__construct();
    }

    function order_shipping($sell_record_code, $record_time, $express_code, $express_no, $order_weight) {
        $sql = "select shipping_status from oms_sell_record where sell_record_code = :sell_record_code";
        $shipping_status = ctx()->db->getOne($sql, array(':sell_record_code' => $sell_record_code));
        if ($shipping_status >= 4) {
            return $this->format_ret(1);
        }
        $express_code = strtoupper($express_code);
        

        if (empty($express_no) && $express_code != 'KHZT') {
            return $this->format_ret(-1, '', '快递号不能为空');
        }


        $sql = "update oms_sell_record set express_code = :express_code,express_no = :express_no,real_weigh=:real_weigh where sell_record_code = :sell_record_code";
        $sql_values = array(':express_code' => $express_code, ':express_no' => $express_no, ':real_weigh' => $order_weight, ':sell_record_code' => $sell_record_code);
        ctx()->db->query($sql, $sql_values);

        $record = load_model("oms/SellRecordOptModel")->get_record_by_code($sell_record_code);
        $detail = load_model("oms/SellRecordOptModel")->get_detail_list_by_code($sell_record_code);
        $sys_user = load_model("oms/SellRecordOptModel")->sys_user();
        $this->check_mx_lof($sell_record_code, 'sell_record', $record['store_code']);

        $ret = load_model("oms/SellRecordOptModel")->sell_record_send($record, $detail, $sys_user, 'o2o_send', 0);

        return $ret;
    }

    function get_record_info($sell_record_code) {
        $sql = "select sell_record_code,deal_code_list,sale_channel_code,order_status,shipping_status,store_code,shop_code,pay_type,pay_code,pay_time,buyer_name,receiver_name,receiver_country,receiver_province,receiver_city,receiver_district,receiver_street,receiver_address,receiver_addr,receiver_zip_code,receiver_mobile,receiver_phone,receiver_email,express_code,express_no,plan_send_time,goods_num,sku_num,goods_weigh,lock_inv_status,buyer_remark,seller_remark,seller_flag,order_remark,store_remark,order_money,goods_money,express_money,payable_money,paid_money,invoice_type,invoice_title,invoice_content,invoice_money,invoice_status,record_time,is_change_record,is_wap,is_jhs,is_fenxiao,check_time,is_notice_time,plan_send_time,is_handwork,sale_mode from oms_sell_record where sell_record_code = :sell_record_code";
        $info = ctx()->db->get_row($sql, array(':sell_record_code' => $sell_record_code));
        if (empty($info)) {
            return $this->format_ret(-1, '', '找不到订单');
        }
        $sql = "select deal_code,goods_code,spec1_code,spec2_code,sku,barcode,num,lock_num,is_gift,goods_price,goods_weigh,avg_money,trade_price,fx_amount from oms_sell_record_detail where sell_record_code = :sell_record_code";
        $info['goods'] = ctx()->db->get_all($sql, array(':sell_record_code' => $sell_record_code));
        if (empty($info)) {
            return $this->format_ret(-1, '', '找不到订单明细');
        }
        $info['goods'] = load_model('util/ViewUtilModel')->record_detail_append_goods_info($info['goods']);
        $ret = load_model('o2o/O2oRecordModel')->check_mx($info['goods']);

        if ($ret['status'] < 0) {
            return $ret;
        }
        return $this->format_ret(1, $info);
    }

}
