<?php
require_model('oms/SellRecordModel');
require_model('base/ShopApiModel');

//各平台订单回写方法
class SellRecordBackModel extends SellRecordModel
{
    //taobao
    function opt_back_api_9($record, $detail, $sys_user){
        $mdl_shop = new ShopApiModel();
        $shop = $mdl_shop->get_shop_api_by_shop_code($record['shop_code']);

        //回写前判断
        $sql = "select * from sys_info.jdp_tb_trade where seller_nick = :seller_nick and tid = :tid";
        $data = $this->db->get_row($sql, array('seller_nick' => $shop['api']['nick'], 'tid' => $record['deal_code']));
        if(empty($data)){
            return $this->return_value(-1, '平台订单不存在:'.$record['deal_code']);
        }
        $data['jdp_response'] = preg_replace('/(":)(\d{9,})/i', '${1}"${2}"', $data['jdp_response']);
        $trade = json_decode($data['jdp_response'], true);
        $trade = $trade['trade_fullinfo_get_response']['trade'];

        //无需回写状态
        $success_status_arr = array('WAIT_BUYER_CONFIRM_GOODS','TRADE_BUYER_SIGNED','TRADE_FINISHED','TRADE_CLOSED','TRADE_CLOSED_BY_TAOBAO');
        if (in_array($trade['status'],$success_status_arr)){
            return $this->return_value(1, '回写成功');
        }

        //验证明细退货
        $no_chk_refund_status_arr = array('SELLER_REFUSE_BUYER','CLOSED','NO_REFUND');
        $mx_refund_info = array();
        foreach($trade['orders'] as $sub_order){
            if (in_array($sub_order['refund_status'],$no_chk_refund_status_arr)){
                continue;
            } else {
                $mx_refund_info[] = $sub_order['title'];
            }
        }
        if (!empty($mx_refund_info)){
            $msg = join(',',$mx_refund_info);
            return $this->return_value(-1, '订单商品 '.$msg .' 存在未处理的退款信息');
        }

        //验证物流公司及快递单号规则
        $s = $this->check_express_no($record['express_code'], $record['express_no']);
        if($s == false){
            return $this->return_value(-1, '快递单号不合法:'.$record['express_no']);
        }

        //淘宝订单回写
        $parameter = $shop['api'];
        $parameter['tid'] = $record['deal_code'];
        $parameter['out_sid'] = $record['express_no'];
        $parameter['company_code'] = $record['express_code'];

        require_lib('util/taobao_util', true);
        $taobao = new taobao_util($parameter['app'],$parameter['secret'], $parameter['session']);
        $ret = $taobao->post('taobao.logistics.offline.send',$parameter);

        return $ret;
    }
}