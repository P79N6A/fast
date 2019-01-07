<?php

/**
 * 通灵相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lib('util/oms_util', true);
require_lang('stm');

class DeliverRecordWtyModel extends TbModel {

    /**
     * @var array 打印质保单模板所需字段
     * 
     */
    public $print_fields_default = array(
        'record' => array(
            '顾客' => 'receiver_name',
            '订单号' => 'sell_record_code',
            '店名' => 'shop_code',
            '联系电话' => 'receiver_mobile',
            '现金' => 'cash',
            '刷卡' => 'invoice_money',
            '抵用卷' => 'coupon_fee',
            '备注' => 'deal_code',
            '购买时间' => 'pay_time',
        ),
        'detail' => array(
            array(
                '饰品编号' => 'unique_code',
                '证书号' => 'check_station_num', //检测站证书号
                '主石重量' => 'pri_diamond_weight',
                '辅石重量' => 'ass_diamond_weight',
                '总重量' => 'credential_weight',//证书重量
                //'饰品信息' => 'pri_diamond_weight'.'/'.'ass_diamond_weight'.'/'.'total_weight',
                '数量' => 'num',
                '金额' => 'total_price',
                '合计' => 'total',
            ),
        ),
    );

    /**
     * 默认打印数据
     * @param $id
     * @return array
     */
    public function print_data_default(&$record_code) {
        
        $r = array();
        $sql = "select a.pay_type,a.sale_channel_code,a.sell_record_code,a.receiver_name,a.deal_code_list,a.shop_code,a.payable_money,a.invoice_money,a.coupon_fee,a.pay_time,a.delivery_time,a.receiver_mobile,a.receiver_phone from oms_sell_record a where a.sell_record_code= :record_code";
        $r['record'] = $this->db->get_row($sql,array('record_code' => $record_code));
        $record_decrypt_info = load_model('sys/security/OmsSecurityOptModel')->get_sell_record_decrypt_info($r['record']['sell_record_code'], 'receiver_name');
        $r['record'] = array_merge($r['record'], $record_decrypt_info);
        if ($r['record']['pay_type'] == 'cod') {
            $r['record']['pay_time'] = $r['record']['delivery_time'];
        }
        $r['record']['invoice_money'] = $r['record']['invoice_money'] > 0 ? $r['record']['invoice_money'] : $r['record']['payable_money'];
        if(!empty($r['record']['invoice_money'])){
            $r['record']['cash'] = '';
        }
        $r['record']['receiver_mobile'] = empty($r['record']['receiver_mobile']) ? $r['record']['receiver_phone'] : $r['record']['receiver_mobile'];
        $deal_code_list = explode(",", $r['record']['deal_code_list']);
        $deal_code_list_str = "'" . implode("','", $deal_code_list) . "'";
        $r['record']['coupon_fee'] = 0;
        $r['record']['deal_code'] = $r['record']['deal_code_list'];
        
        $ret_invocie= load_model('oms/invoice/JsFapiaoModel')->get_invoice_param_by_shop_code($r['record']['shop_code']) ;
        
        $r['record']['shop_code'] = !empty($ret_invocie['data'])?$ret_invocie['data']['nsrmc']:$r['record']['shop_code'];
        
        if ($r['record']['sale_channel_code'] == 'taobao') {
            //a.deal_code_list
            $sql_tb = "select coupon_fee,alipay_point,promotion_details,discount_fee from api_taobao_trade where tid in ({$deal_code_list_str}) ";
            $tb_data = $this->db->get_all($sql_tb);
            foreach ($tb_data as $val) {
                if ($val['coupon_fee'] > 0) {
                    $r['record']['coupon_fee']+=round($val['coupon_fee'] / 100, 2);
                }
                if ($val['alipay_point'] > 0) {
                    $r['record']['coupon_fee']+=round($val['alipay_point'] / 100, 2);
                }
                if (!empty($val['promotion_details'])) {
                    $discount_fee = $this->get_promotion_details_fee($val['promotion_details']);
                    $r['record']['coupon_fee']+=$discount_fee;
                }
            }
        } elseif ($r['record']['sale_channel_code'] == 'jingdong') {
            $sql_jdq = "select coupon_type,coupon_price from api_jingdong_trade_coupon where order_id in ({$deal_code_list_str}) ";
            $jd_coupon_data = $this->db->get_all($sql_jdq);
            $coupon_price = 0;
            foreach ($jd_coupon_data as $val) {
                if (strpos($val['coupon_type'] , '41-')!==false  || strpos($val['coupon_type'] , '39-')!==false) {
                    $r['record']['coupon_fee']+=$val['coupon_price'];
                    $coupon_price+=$val['coupon_price'];
                }
            }
            $sql_jd = "select balance_used,order_seller_price,order_payment from api_jingdong_trade where order_id in ({$deal_code_list_str}) ";
            $jd_data = $this->db->get_all($sql_jd);
            foreach ($jd_data as $val) {
                $temp = $val['order_payment']+$val['balance_used'];//先相加保证数据精度(买家应付金额+京东余额) 
                $r['record']['coupon_fee']+=bcsub($val['order_seller_price'], $temp, 2);//订单货款金额-(买家应付金额+京东余额)
                //支付有礼金额=订单货款金额("order_seller_price") - 买家应付金额(“order_payment”) - 京东余额(“banlance_used”) - coupon_type为“39-京豆优惠”的“coupon_price” - coupon_type为“41-京东券优惠”的“coupon_price”
                $r['record']['coupon_fee']+=$val['balance_used'];
            }
            $r['record']['coupon_fee'] = bcsub($r['record']['coupon_fee'], $coupon_price, 2);//最后的总优惠金额减去所有单子的coupon_price
        }

        $sql1 = "select sr.sell_record_detail_id,sr.is_gift,g.unique_code,g.international_num,g.check_station_num,g.guarantee_num,g.pri_diamond_weight,g.ass_diamond_weight,g.credential_weight,g.total_price 
                from goods_unique_code_tl g INNER JOIN goods_unique_code_log h on g.unique_code=h.unique_code 
                inner join oms_sell_record_detail sr on g.sku = sr.sku AND h.record_code = sr.sell_record_code
                where h.record_code= :record_code";
        $r['detail'] = $this->db->get_all($sql1,['record_code' => $record_code]);
        $d = array('record' => array(), 'detail' => array());
        foreach ($r['record'] as $k => $v) {
            // 键值对调
            $nk = array_search($k, $this->print_fields_default['record']);
            $nk = $nk === false ? $k : $nk;
            $d['record'][$nk] = is_null($v) ? '' : $v; //$v; // 
        }
        foreach ($r['detail'] as $k1 => $v1) {
            if($v1['is_gift'] == 1){     //如果是赠品就过滤
                unset($r['detail'][$k1]);
            }
        }
        $r['detail'] = array_values($r['detail']);//重新排序下标
        $d['detail'][0]['合计'] == 0;
        foreach ($r['detail'] as $k1 => $v1) {
            // 键值对调
            $d['detail'][0]['合计']+=$v1['total_price'];
            foreach ($v1 as $k => $v) {
                $nk = array_search($k, $this->print_fields_default['detail'][0]);
                $nk = $nk === false ? $k : $nk;
                $d['detail'][$k1][$nk] = is_null($v) ? '' : $v; //$v; //
                $d['detail'][$k1]['数量'] = '1';
            }
        }
        //var_dump($d['detail'][0]['合计']);die;
        // 增加打印日志
        //var_dump($d);die;
//        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => $sure_status, 'finish_status' => $finish_status, 'action_name' => '商品信息打印', 'module' => "store_out_record", 'pid' => $id);
//        $ret1 = load_model('pur/PurStmLogModel')->insert($log);
        //更新质保书标识为已打印
        $this->update_exp('oms_sell_record', array('is_print_warranty' => 1), array('sell_record_code' => $record_code));
        return $d;
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

    //获取相应发货单对应的订单编号
    function get_sell_record_code($record_ids){
        $sql = "select sell_record_code from oms_deliver_record where deliver_record_id in ({$record_ids})";
        $data =  $this->db->get_all($sql);
        //var_dump($data);die;
        if(empty($data)){
           return $this->format_ret(-1, '', '请选择好订单'); 
        }
        $data = $this->arr_to_str($data);
        return $data;
    }
    
//二维数组转化为字符串，中间用,隔开  
    function arr_to_str($arr){  
        foreach ($arr as $v){  
            $v = join(",",$v);
            $temp[] = $v;  
        }  
            foreach($temp as $v){  
                $t.=$v.",";  
        }  
            $t=substr($t,0,-1);
            return $t;  
    }
    
    //检验发货单是否存在
    function check_deliver($sell_record_code) {
        $sql = "select b.deliver_record_id from oms_sell_record a
                inner join oms_deliver_record b on b.waves_record_id = a.waves_record_id and b.sell_record_code = a.sell_record_code
                where a.sell_record_code = :sell_record_code";
        $data = $this->db->getAll($sql,['sell_record_code' => $sell_record_code]);
        if(empty($data)){
           return $this->format_ret(-1, '', '发货单不存在');
        }
        return $this->format_ret(1);
    }
}
