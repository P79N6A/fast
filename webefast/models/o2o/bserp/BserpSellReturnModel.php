<?php
require_model('o2o/bserp/BserpBaseModel');
class BserpSellReturnModel extends BserpBaseModel {

    function __construct() {
        parent::__construct();
    }
    function convert_data($record_code) {
       
        $sql = "select json_data from o2o_oms_trade where record_code = :record_code and record_type = :record_type";
        $json_data = $this->db->getOne($sql, array(':record_code' => $record_code, ':record_type' => 'sell_return'));
        $order = json_decode($json_data, true);
                
        $record_decrypt_info = load_model('sys/security/OmsSecurityOptModel')->get_sell_return_decrypt_info($order['sell_return_code']);

        if (empty($record_decrypt_info)) {
                    return $this->format_ret(-1,'','解密失败，稍后再试...');
        }
        if($record_decrypt_info['return_addr']=='*****'|| strpos($record_decrypt_info['return_mobile'],"***")!==false){
                   return $this->format_ret(-1,'','解密失败，稍后再试...');
        }
        
        
        $bserp_lsxhd = array();
        $bserp_lsxhd['timer'] = 0;
        $bserp_lsxhd['state'] = 'success';
        $trade = array();
        
        $new_record_code = $this->is_canceled($record_code,'sell_return');
        $trade['YDJH'] = $order['sell_record_code'];
        $trade['DJBH'] = $new_record_code;
        $trade['RQ'] = date('Y-m-d',strtotime($order['create_time']));
        $trade['QDDM'] = '';
        //客户代码
        $trade['KHDM'] = $this->get_shop_outside_code($order['shop_code']);
        //仓库代码
        $trade['CKDM'] = '';
        $trade['plat'] = $this->get_sale_channel_name($order['sale_channel_code']);
        $trade['SL'] = 0;
        $trade['JE'] = $order['refund_total_fee'];
        $trade['ZDR'] = 'eFAST365';
       
        $trade['THR'] = $order['return_name'];//退货人
        $trade['CELLPHONE'] = $order['return_mobile'];//退货人手机
        $trade_mxs = array();
        foreach ($order['goods'] as $goods)
        {
            $trade_mx = array();
            $trade_mx['SPDM'] = $goods['goods_code'];
            $trade_mx['GG1DM'] = $goods['spec1_code'];
            $trade_mx['GG2DM'] = $goods['spec2_code'];
            $trade_mx['SL'] = $goods['num'];

            $trade_mx['DJ'] = $order['is_fenxiao'] == 1 ? $goods['fx_amount']/$goods['num'] : $goods['avg_money']/$goods['num'];
            $trade_mx['DJH'] = '';
            $trade_mx['BZ'] = '';
            $trade_mx['JE'] = $order['is_fenxiao'] == 1 ?  $goods['fx_amount'] : $goods['avg_money'] ;
            $trade_mx['ZK'] = ($trade_mx['DJ'] * $trade_mx['SL'] != 0) ? sprintf("%.2f", $trade_mx['JE'] /($trade_mx['DJ'] * $trade_mx['SL'])) : 1;
            $trade_mxs[] = $trade_mx;
            $trade['SL'] += $trade_mx['SL'];
        }
        
        //商品金额明细
        $jemx = array();
        $jemx['SL'] = $trade['SL'];//数量
       
        $bserp_lsxhd['data'] = array();
        $bserp_lsxhd['data']['InvoicesList'] = array($trade);
        $bserp_lsxhd['data']['InvoicesMX'] = $trade_mxs;
        $bserp_lsxhd['data']['JEMX'] = array($jemx);
        return $this->format_ret(1,$bserp_lsxhd);
        
        

    }
    function upload($record_code, $sys_store_code) {
        $sell_record_ret = $this->convert_data($record_code);
        if ($sell_record_ret['status'] < 1) {
            return $sell_record_ret;
        }

        $method = 'o2o_return_record_upload';
        $params['Invoices_List'] = json_encode($sell_record_ret['data']);
        
        $ret = $this->biz_req($method, $params,$sys_store_code);
        if ($ret['status'] > 0) {
            return $this->format_ret(1,$ret['data']); 
        }
        
        return $ret;
    }
    
    
    function cancel($record_code, $sys_store_code){
        $method = 'o2o_trade_cancel';
        $params['orderID'] = $record_code;
        $params['TableName'] = 'WXTD';
        $ret = $this->biz_req($method, $params,$sys_store_code);
        
        return $ret;
    }
    
    function _record_info($record_code, $sys_store_code){
        $method = 'o2o_trade_status';
        $params['orderId'] = $record_code;
        $ret = $this->biz_req($method, $params,$sys_store_code);
        if ($ret['status']<0){
            return $ret;
        }
        $ret = $this->conv_o2o_record_info($ret['data'], $record_code);
        return $ret;
    }
    
    function conv_o2o_record_info($result,$record_code){
        $ret['efast_record_code'] = $record_code;
        if ($result['data']['LIST'][0]['state'] == 1){
            $ret['order_status'] = 'flow_end';
            $ret['order_status_txt'] = '已收发货';
            $ret['express_code'] = $this->get_express_code($result['data']['LIST'][0]['logisticsName']);
            $ret['express_no'] = $result['data']['LIST'][0]['expressCode'];
        } else {
            $ret['order_status'] = 'upload';
            $ret['order_status_txt'] = '已上传';
        }
        return $this->format_ret(1,$ret);
    }
    
     function get_express_code($express_name) {
        $sql = "SELECT express_code FROM base_express where express_name=:name";
        $express_code = $this->db->get_value($sql, array(':name' => $express_name));
        return $express_code == FALSE ? '' : $express_code;
    }
    
    function get_sale_channel_name($sale_channel_code){
        $sql = "SELECT sale_channel_name FROM base_sale_channel where sale_channel_code=:sale_channel_code";
        $sale_channel_name = $this->db->get_value($sql, array(':sale_channel_code' => $sale_channel_code));
        return $sale_channel_name == FALSE ? '' : $sale_channel_name;
    }
}
