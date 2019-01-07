<?php
require_model('tb/TbModel');
class O2oSellReturnModel extends TbModel {

    function __construct() {
        parent::__construct();
    }

    function order_shipping($sell_return_code,$record_time,$return_mx,$express_code = null,$express_no = null){
        $sql = "select return_shipping_status from oms_sell_return where sell_return_code = :sell_return_code";
        $shipping_status = ctx()->db->getOne($sql,array(':sell_return_code'=>$sell_return_code));
        if($shipping_status == 1){
            return $this->format_ret(1);
        }
        if (!empty($express_code)){
                $express_code = strtoupper($express_code);
                $sql = "select count(*) from base_express where express_code = :express_code";
                $c = ctx()->db->getOne($sql,array(':express_code'=>$express_code));
                if ($c == 0){
                        return $this->format_ret(-1,'',$express_code.'配送方式代码在EFAST中不存在');
                }
                if (empty($express_no)){
                        return $this->format_ret(-1,'','快递号不能为空');
                }
        }
      
        $sql = "update oms_sell_return set return_express_code = :express_code,return_express_no = :express_no where sell_return_code = :sell_return_code";
        $sql_values = array(':express_code'=>$express_code,':express_no'=>$express_no,':sell_return_code'=>$sell_return_code);
        ctx()->db->query($sql,$sql_values);

        $ret = load_model("oms/SellReturnOptModel")->opt_return_shipping($sell_return_code,array(),1);
        return $ret;
    }

    function get_record_info($sell_return_code){
        $sql = "select create_time,sell_return_code,sell_record_code,deal_code,store_code,shop_code,sale_channel_code,buyer_name,return_name,return_country,return_province,return_city,return_district,return_street,return_address,return_addr,return_zip_code,return_mobile,return_phone,return_email,return_express_code,return_express_no,return_reason_code,return_buyer_memo,return_remark,refund_total_fee,is_fenxiao from oms_sell_return where sell_return_code = :sell_return_code";
        $info = ctx()->db->get_row($sql,array(':sell_return_code'=>$sell_return_code));
        if (empty($info)){
                return $this->put_error(-1,'找不到退单');
        }
        $sql = "select deal_code,goods_code,spec1_code,spec2_code,sku,barcode,note_num as num,goods_price,avg_money,trade_price,fx_amount from oms_sell_return_detail where sell_return_code = :sell_return_code";
        $info['goods'] = ctx()->db->get_all($sql,array(':sell_return_code'=>$sell_return_code));
        if (empty($info)){
                return $this->put_error(-1,'找不到退单明细');
        }
        $info['goods'] = load_model('util/ViewUtilModel')->record_detail_append_goods_info($info['goods']);
        $ret = load_model('o2o/O2oRecordModel')->check_mx($info['goods']);
        if ($ret['status']<0){
                return $ret;
        }
        return $this->format_ret(1,$info);
    }

}
