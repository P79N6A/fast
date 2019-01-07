<?php

/**
 * 订购页面-立即订购方法
 *
 * @author zyp
 *
 */
require_model('tb/TbModel');
require_lib("comm_util");

class SoonbuyModel extends TbModel {

    function get_table() {
        return 'osp_productorder';
    }

    /*
     * 添加产品订购
     */

    function insert_order($porders) {
        if (isset($porders)) {
            $porders['pro_num'] = create_fast_bill_sn('DGBH');
            $ret = parent::insert($porders);
            if ($ret['status'] == "1")
                return $this->format_ret("1", $porders['pro_num'], 'insert_success');
            else
                return $ret;
        }
    }

    //更新订购状态    
    function update_order($order, $num) {
        if (isset($num)) {
            $ret = parent::update($order, array('pro_num' => $num));
            return $ret;
        } else {
            return '';
        }
    }

    //获取客户id
    function get_clientinfo($usercode) {
        if (isset($usercode)) {
            $sql = "SELECT kh_id,kh_verify_status FROM osp_kehu WHERE kh_code=:code ";
            $sql_value[':code'] = $usercode;
            $khid = $this->db->get_row($sql, $sql_value);
            return $khid;
        }
    }

    function get_planprice($cpid) {
        //获取支持在线订购的产品列表
        //$sql = "select * from osp_chanpin where cp_order=1";   
        $sql = "select * from osp_chanpin where cp_id=".$cpid;    //先支持eFAST5产品
        $ordercplist = $this->db->get_row($sql);
        if(!empty($ordercplist)){
            //获取营销类型
            $sql="select * from osp_market_strategytype ORDER BY st_id desc";
            $market= $this->db->get_all($sql);
            //默认获取产品的相关报价信息(标准版本,租用型的报价)
            $sql = "SELECT * FROM osp_plan_price "
                    . "WHERE price_cpid = '".$ordercplist['cp_id']."' "
                    . "AND price_status = '1' "
                    . "AND price_pversion = '1' AND price_stid = '2' ORDER BY price_id desc";
            $plan = $this->db->get_all($sql);
        }
        $orderplan['chanpin']=$ordercplist;
        $orderplan['market']=$market;
        $orderplan['plan']=$plan;
        return $orderplan;
    }
    
    function get_planprice_mby($version,$stid,$cpid){
        $sql = "SELECT * FROM osp_plan_price "
                    . "WHERE price_cpid = '".$cpid."' "
                    . "AND price_status = '1' "
                    . "AND price_pversion = '".$version."' AND price_stid = '".$stid."' ORDER BY price_id desc";
        $data = $this->db->get_all($sql);
        $ret_status = $data ? 1 : 'op_no_data';
        return $this->format_ret($ret_status, $data);
    }
    
    function get_planprice_mone($priceid){
        $sql = "SELECT * FROM osp_plan_price "
                    . "WHERE price_id = '".$priceid."'";
        $data = $this->db->get_row($sql);
        $ret_status = $data ? 1 : 'op_no_data';
        return $this->format_ret($ret_status, $data);
    }
    
    //获取订购单据
    function get_order_byid($djbh){
        $strSQL="select kh.kh_code,kh.kh_name,cp.cp_name,"
                . "mst.st_name,pro.* FROM osp_productorder pro "
                . "LEFT JOIN osp_kehu kh on kh.kh_id=pro.pro_kh_id "
                . "LEFT JOIN osp_chanpin cp on cp.cp_id=pro.pro_cp_id "
                . "LEFT JOIN osp_market_strategytype mst on mst.st_id=pro.pro_st_id "
                . "where pro_num='".$djbh."'";
        $orderprice = $this->db->get_row($strSQL);
        switch ($orderprice['pro_product_version']) {
            case "1":
                $orderprice['pro_product_version_name'] = '标准版';
                break;
            case "2":
                $orderprice['pro_product_version_name'] = '企业版';
                break;
            case "3":
                $orderprice['pro_product_version_name'] = '旗舰版';
                break;
        }
        return $orderprice;
    }
    
    /**
     * @todo 获取订单信息
     */
    function get_order_info($pra_id) {
        $auth_sql = "SELECT pra_pro_num FROM osp_productorder_auth WHERE pra_id = :pra_id";
        $auth_sql_value = array(":pra_id" => $pra_id);
        $data = $this->db->get_row($auth_sql, $auth_sql_value);
        $price_sql = "SELECT oms.st_name, op.pro_real_price,op.pro_product_version,op.pro_kh_id FROM osp_productorder op, osp_market_strategytype oms WHERE op.pro_st_id = oms.st_id AND pro_num = :pro_num";
        $price_sql_value = array(":pro_num" => $data['pra_pro_num']);
        $price_data = $this->db->get_row($price_sql, $price_sql_value);
        switch ($price_data['pro_product_version']) {
            case "1":
                $pro_product_version_name = '标准版';
                break;
            case "2":
                $pro_product_version_name = '企业版';
                break;
            case "3":
                $pro_product_version_name = '旗舰版';
                break;
        }
        return array('order_price' => $price_data['pro_real_price'], 'product_name' => 'eFAST365' . $pro_product_version_name . $price_data['st_name'], 'pro_kh_id' => $price_data['pro_kh_id']);
    }
    
    /**
     * @todo 获取续费后产品订单信息
     */
    function get_renew_info($pra_id){
        $auth_sql = "SELECT is_notice, pra_startdate, pra_enddate FROM osp_productorder_auth WHERE pra_id = :pra_id";
        $auth_sql_value = array(":pra_id" => $pra_id);
        $data = $this->db->get_row($auth_sql, $auth_sql_value);
        if($data['is_notice'] == 1){
            return $this->format_ret(-1);
        }
        $ret = array('pra_startdate'=> $data['pra_startdate'], 'pra_enddate' => $data['pra_enddate']);
        return $this->format_ret(1, $ret);
    }
    
    function get_neworder_info($pra_id){
        $auth_sql = "SELECT pra_enddate,pra_pro_num FROM osp_productorder_auth WHERE pra_id = :pra_id";
        $auth_sql_value = array(":pra_id" => $pra_id);
        $data = $this->db->get_row($auth_sql, $auth_sql_value);
        $price_sql = "SELECT * FROM osp_productorder WHERE pro_num = :pro_num";
        $price_sql_value = array(":pro_num" => $data['pra_pro_num']);
        $price_data = $this->db->get_row($price_sql, $price_sql_value);
        $pra_enddate = array('pra_enddate' => $data['pra_enddate']);
        $price_data = array_merge($pra_enddate, $price_data);
        return $price_data;
    }
    
    /**
     * @todo 续费后处理信息
     */
    function handle_info($params, $kh_id){
        $pra_id = $this->get_renew($params['out_trade_no']);
        $order_info = $this->get_neworder_info($pra_id);
        $pro_num = create_fast_bill_sn('DGBH');
        
        $kh_order['pro_num'] = $pro_num;
        $kh_order['pro_kh_id'] = $kh_id;
        $kh_order['pro_cp_id'] = $order_info['cpid'];
        $kh_order['pro_sell_price'] = $order_info['pro_real_price'];  //标准售价
        $kh_order['pro_rebate_price']= '0';   //折扣价格
        $kh_order['pro_real_price']= $order_info['pro_real_price'];   //实际价格
        $kh_order['pro_channel_id'] = '85'; //销售渠道，85是翼商在线订购固定id
        $kh_order['pro_dot_num'] = $order_info['pro_dot_num'];
        $kh_order['pro_product_version'] = $order_info['p_version'];
        $kh_order['pro_price_id'] = $order_info['p_priceid'];   //报价方案id,12是官网年租版的id后续根据正式环境里面的固定id
        $kh_order['pro_st_id'] = $order_info['p_st_id'];  //营销策略类型id，2代表租用型id
        $kh_order['pro_hire_limit'] = $order_info['pro_hire_limit'];
        $kh_order['pro_orderdate'] = date('Y-m-d H:i:s'); //订购日期
        $order_ret = parent::insert_exp('osp_productorder', $kh_order);
        
        $pra_startdate = $order_info['pra_enddate'];
        $pra_enddate = date('Y-m-d H:i:s',strtotime('1 years', strtotime($pra_startdate)));
        $data = array('pra_startdate' => $pra_startdate, 'pra_enddate' => $pra_enddate, 'is_notice' => 0);
        $where = array('pra_id' => $pra_id);
        $auth_ret = parent::update_exp('osp_productorder_auth', $data, $where);
        
        $re_data = array(
            'pra_pro_num' => $pro_num, 
            'pay_status' => 1, 
            'alipay_trade_no' => $params['trade_no'], 
            'buyer_email' => $params['buyer_email'], 
            'seller_email' => $params['seller_email'], 
            'pay_time' => $params['notify_time']);
        $re_where = array('pra_out_trade_no' => $params['out_trade_no']);
        $re_ret = parent::update_exp('osp_productorder_renew', $re_data, $re_where);
        return $re_ret;
    }
    
    function update_exp($table, $data, $where) {
        parent::update_exp($table, $data, $where);
    }
    
    function insert_exp($table, $data) {
        parent::insert_exp($table, $data);
    }
    
    function get_renew($out_trade_no){
        $sql = "SELECT pra_id FROM osp_productorder_renew WHERE pra_out_trade_no = :out_trade_no";
        $sql_value = array(":out_trade_no" => $out_trade_no);
        $data = $this->db->get_row($sql, $sql_value);
        return $data['pra_id'];
    }
}
