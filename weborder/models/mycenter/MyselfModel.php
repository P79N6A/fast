<?php

/**
 * 官网用户个人中心
 *
 * @author wsc
 *
 */
require_model('tb/TbModel');
class MyselfModel extends TbModel {
    
    function get_table() {
        return 'osp_kehu';
    }
    
    /*
     * 获取客户详细信息
     */
    function getkhinfo($khid) {
        return $this->get_row(array('kh_id' => $khid));
    }
    
    /*
     * 编辑保存客户详细信息
     */
    function update_client($client,$khid){
        /*
        $ret = $this->get_row(array('kh_id' => $khid));
        if ($client['kh_name'] != $ret['data']['kh_name']) {
            $retname = $this->is_exists($client['kh_name'], 'kh_name');
            if ($retname['status'] > 0 && !empty($retname['data']))
                return $this->format_ret('name_is_exist');
        }*/
        $ret = parent::update($client, array('kh_id' => $khid));
        return $ret;
    }
    
    private function is_exists($value, $field_name = 'kh_name') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }
    
    //修改密码
    function updatepwd($khid,$pwd){
        $data = array( 'kh_login_pwd'=>$pwd);
        $result  = parent::update($data, array('kh_id'=>$khid));
        return $result;
    }
    
    /*
     * 客户产品的授权信息
     */
    function getclientorder_auth($khid) {
        //首先获取客户的产品授权信息
        $sql_main="select auth.pra_id,auth.pra_kh_id,auth.pra_cp_id,cp.cp_name,"
                . "auth.pra_product_version,auth.pra_strategytype,"
                . "auth.pra_authkey,auth.pra_authnum,auth.pra_shopnum,"
                . "auth.pra_startdate,auth.pra_enddate,auth.pra_state "
                . "from osp_productorder_auth auth "
                . "LEFT JOIN osp_chanpin cp on cp.cp_id=auth.pra_cp_id "
                . "where pra_kh_id=:pra_kh_id ";
        $sql_values[':pra_kh_id'] = $khid;
        $ret=$this->db->get_all($sql_main, $sql_values);
        if($ret){
            $fields= require_conf('field');
            foreach ($ret as & $authinfo){
                $authinfo['pra_product_version_name']=  $fields['product_version'][$authinfo['pra_product_version']];
            }
        }
        //获取客户的增值授权记录
        $retlist['authinfo']=$ret;
        return $retlist;
        
    }
    //通过客户id获取客户登录地址
    function get_path_by_khid($kh_id){
        $sql = "SELECT pra_serverpath,pra_kh_status,is_notice FROM osp_productorder_auth WHERE pra_kh_id =:pra_kh_id";
        $sql_value = array(":pra_kh_id" => $kh_id);
        $data = $this->db->get_row($sql,$sql_value);
        return $data;
    }
    
    //获取授权详情
    function getorderauthdetail($id){
        $sql_main="select auth.pra_id,auth.pra_kh_id,auth.pra_cp_id,"
                . "cp.cp_name,auth.pra_product_version,auth.pra_strategytype,"
                . "auth.pra_authkey,auth.pra_authnum,auth.pra_shopnum,auth.pra_bz,"
                . "auth.pra_startdate,auth.pra_enddate,auth.pra_state,auth.pra_serverpath from osp_productorder_auth auth "
                . "LEFT JOIN osp_chanpin cp on cp.cp_id=auth.pra_cp_id "
                . "where pra_id=:pra_id";
        $sql_values[':pra_id'] = $id;
        $ret=$this->db->get_row($sql_main, $sql_values);
        if($ret){
            $fields= require_conf('field');
            $ret['pra_product_version_name']=  $fields['product_version'][$ret['pra_product_version']];
        }
        $retlist['orderauthdetail']=$ret;
        return $retlist;
    }
    
    /*
     * 客户产品的订购信息
     */
    function getclientorder($khid) {
        //获取客户的产品订购记录
        $sql_main_order="select pro.pro_num, pro.pro_cp_id,pro.pro_product_version,"
                . "pro.pro_real_price,pro.pro_hire_limit,pro.pro_dot_num,"
                . "pro.pro_pay_status,pro.pro_check_status,pro.pro_orderdate,pro.pro_st_id,"
                . "cp.cp_jc,cp.cp_name,auth.pra_state from osp_productorder pro,osp_productorder_auth auth,osp_chanpin cp "
                . "where pro_kh_id=:pro_kh_id GROUP BY pro_num ORDER BY pro.pro_orderdate DESC";
        $sql_values_order[':pro_kh_id'] = $khid;
        $ret_order=$this->db->get_all($sql_main_order, $sql_values_order);
        if($ret_order){
            $fields= require_conf('field');
            foreach ($ret_order as & $orderinfo){
                $orderinfo['pro_product_version_name']=  $fields['product_version'][$orderinfo['pro_product_version']];
            }
        }
        //获取客户的增值订购记录
        $retlist['orderinfo']=$ret_order;
        return $retlist;
    }
    
    //获取订单详情
    function getorderdetail($djbh){
        $sql_main="select pro.pro_num, pro.pro_cp_id,pro.pro_product_version,"
            ."pro.pro_real_price,pro.pro_hire_limit,pro.pro_dot_num,"
            ."pro.pro_pay_status,pro.pro_check_status,pro.pro_orderdate,"
            ."pro.pro_price_id,plan.price_name,market.st_name,pro.pro_st_id,pro.pro_paydate,pro.pro_checkdate,"
            ."pro.pro_sell_price,cp.cp_jc,cp.cp_name,pro.pro_rebate_price,pro.pro_real_price,pro_desc "
            ."from osp_productorder pro "
            ."LEFT JOIN osp_chanpin cp on cp.cp_id=pro.pro_cp_id "
            ."LEFT JOIN osp_plan_price plan on plan.price_id=pro.pro_price_id "
            ."LEFT JOIN osp_market_strategytype market ON market.st_id=pro.pro_st_id "
            ."where pro.pro_num=:pro_num ";
        $sql_values[':pro_num'] = $djbh;
        $ret=$this->db->get_row($sql_main, $sql_values);
        if($ret){
            $fields= require_conf('field');
            $ret['pro_product_version_name']=  $fields['product_version'][$ret['pro_product_version']];
        }
        $retlist['orderdetail']=$ret;
        return $retlist;
    }
    
    /*
     * 客户产品的订购信息和授权信息
     */
    function getproduct_orderauth($khid) {
        //首先获取客户的产品授权信息
        $sql_main="select auth.pra_id,auth.pra_kh_id,auth.pra_cp_id,cp.cp_name,auth.pra_product_version,auth.pra_strategytype,
                auth.pra_authkey,auth.pra_authnum,auth.pra_shopnum,auth.pra_startdate,auth.pra_enddate,
                auth.pra_state,cp.cp_name
                 from osp_productorder_auth auth
                LEFT JOIN osp_chanpin cp on cp.cp_id=auth.pra_cp_id
                where pra_kh_id=:pra_kh_id ";
        $sql_values[':pra_kh_id'] = $khid;
        $ret=$this->db->get_row($sql_main, $sql_values);
        if($ret){
            $fields= require_conf('field');
            $ret['pra_product_version_name']=  $fields['product_version'][$ret['pra_product_version']];
        }
        //平台店铺明细
        $sql_main_shop="select sauth.pra_shop_num,sauth.pra_shop_pfid,pf.pt_name from osp_productorder_shopauth sauth 
                left JOIN osp_platform pf on pf.pt_id=sauth.pra_shop_pfid 
                where sauth.pra_shop_pid=:pra_shop_pid";
        $sql_values_shop[':pra_shop_pid'] = $ret['pra_id'];
        $ret_shop=$this->db->get_all($sql_main_shop, $sql_values_shop);
        //获取订单列表
        $sql_main_order="select pro_num, pro_product_version,pro_real_price,pro_hire_limit,pro_dot_num,
                pro_pay_status,pro_check_status,pro_orderdate 
                from osp_productorder where pro_kh_id=:pro_kh_id";
        $sql_values_order[':pro_kh_id'] = $khid;
        //$sql_values_order[':pro_cp_id'] = $ret['pra_cp_id'];
        $ret_order=$this->db->get_all($sql_main_order, $sql_values_order);
        if($ret_order){
            $fields= require_conf('field');
            foreach ($ret_order as & $orderinfo){
                $orderinfo['pro_product_version_name']=  $fields['product_version'][$orderinfo['pro_product_version']];
            }
        }
        
        $retlist['authinfo']=$ret;
        $retlist['authshopinfo']=$ret_shop;
        $retlist['orderinfo']=$ret_order;
        return $retlist;
        //获取订购信息
    }
    function get_order_info_by_pro_num($request){
        $sql = "SELECT pro_sell_price FROM osp_productorder WHERE pro_num=:pro_num";
        $sql_value = array(":pro_num" => $request['pro_num']); 
        $ret = $this->db->get_row($sql, $sql_value);
        return $ret;
    }
    
    function get_auth_key($pra_id){
        $sql = "SELECT pra_authkey FROM osp_productorder_auth WHERE pra_id=:pra_id";
        $sql_value = array(":pra_id"=>$pra_id);
        $auth_key = $this->db->get_row($sql,$sql_value);
        return $auth_key;
    }
    
    function check_khinfo($field,$kh_info) {
        $sql = "SELECT COUNT(1) FROM osp_kehu WHERE {$field}=:kh_info";
        $sql_value = array(":kh_info" => $kh_info);
        $ret = $this->db->get_value($sql, $sql_value);
        return $ret;
    }  
}