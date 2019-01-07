<?php

/**
 * 操作产品业务库
 *
 * @author WangShouChong
 */
require_model('tb/TbModel');
require_lib("keylock_util");

class BusinessOperModel extends TbModel {
    
    public $app_info;

    public function __construct() {
            parent::__construct();
	    $this->app_info['taobao'] = array('app_key'=>'12651526','app_secret'=>'11b9128693bfb83d095ad559f98f2b07'); 
	    $this->app_info['jingdong'] = array('app_key'=>'C0C9110ED2D32E13266D4522D55C78AF','app_secret'=>'f58736a321bd44fabf2253f5d25344f0'); 
    }
    
    /*
     * 获取客户所属RDS数据库信息
     * $khid 客户id
     */
    function getRdsdb_bykh($khid) {
        if(!empty($khid)){
            $rdsdbinfo=$this->db->get_row("select * from osp_rdsextmanage_db where rem_db_khid=:rem_db_khid", array(":rem_db_khid" => $khid));
            return $rdsdbinfo;
        }else{
            return false;
        }
    }
    
    /*
     * 获取店铺所属客户
     * $sdid 店铺id
     */
    function getkhid_bysd($sdid) {
        if(!empty($sdid)){
            $khid=$this->db->get_value("select sd_kh_id from osp_shangdian where sd_id=:sd_id", array(":sd_id" => $sdid));
            return $khid;
        }else{
            return false;
        }
    }
    
    /*
     * 获取店铺信息
     * $sdid 店铺id
     */
    function getshop_byid($sdid) {
        if(!empty($sdid)){
            $shopinfo=$this->db->get_row("select * from osp_shangdian where sd_id=:sd_id", array(":sd_id" => $sdid));
            return $shopinfo;
        }else{
            return false;
        }
    }
    
    /*
     * 获取平台代码
     * $sdid 店铺id
     */
    function getptcode_byid($ptid) {
        if(!empty($ptid)){
            $ptcode=$this->db->get_value("select pt_code from osp_platform where pt_id=:pt_id", array(":pt_id" => $ptid));
            return $ptcode;
        }else{
            return false;
        }
    }
    
    /*
     * 获取RDS连接信息
     * $rdsid
     */
    function getrdsinfo($rdsid) {
        if(!empty($rdsid)){
            $rdsinfo=$this->db->get_row("select * from osp_aliyun_rds where rds_id=:rds_id", array(":rds_id" => $rdsid));
            if(!empty($rdsinfo)){
                //解密密码
                $keylock=get_keylock_string($rdsinfo['rds_createdate']);
                $rdspwd= create_aes_decrypt($rdsinfo['rds_pass'],$keylock);
                $rdsinfo['rds_pass']=$rdspwd;
            }
            return $rdsinfo;
        }else{
            return false;
        }
    }
    
    /*
     * 获取平台产品KEY信息
     * $sdid 店铺id
     */
    function getrds_app($ptid,$cpid) {
        if(!empty($ptid) && !empty($cpid)){
            $appinfo=$this->db->get_row("select * from osp_rds where relation_product=:relation_product and relation_platform=:relation_platform", array(":relation_product"=>$cpid,":relation_platform" => $ptid));
            return $appinfo;
        }else{
            return false;
        }
    }
    
    
    /*
     * 获取客户所属RDS数据库
     * $sdid 店铺ID
     */
    function shopsession_push($sdid){
        $shopinfo=$this->getshop_byid($sdid);
        $rdsdbinfo=$this->getRdsdb_bykh($shopinfo['sd_kh_id']);
        $rdsinfo=$this->getrdsinfo($rdsdbinfo['rem_db_pid']);
        //获取平台代码
        $ptcode=$this->getptcode_byid($shopinfo['sd_pt_id']);
        //获取店铺平台对应的appkey信息
        $cur_app_info=$this->getrds_app($shopinfo['sd_pt_id'],'21');  //默认efast5产品
        if (empty($rdsdbinfo)){
             return $this->format_ret(-1,'',$shopinfo['sd_name'].' 匹配不到数据库');	        
        }
        if (empty($rdsinfo)){
             return $this->format_ret(-1,'',$shopinfo['sd_name'].' 匹配不到对应RDS');	        
        }
        if (empty($cur_app_info)){
             return $this->format_ret(-1,'',$shopinfo['sd_name'].' 匹配不到对应企业session');	        
        }
        //连接数据库
        CTX()->register_tool('db_kh', 'lib/db/PDODB.class.php');
        CTX()->db_kh->set_conf(array(
            'name' => $rdsdbinfo['rem_db_name'],
            'host' => $rdsinfo['rds_link'],
            'user' => $rdsinfo['rds_user'],
            'pwd'  => $rdsinfo['rds_pass'],
            'type' => 'mysql',
        ));
        
        $db = CTX()->db_kh;
        //查询业务库店铺信息
        $shopinfo_bus=$db->get_row("select * from base_shop where shop_name=:shop_name", array(":shop_name" => $shopinfo['sd_name']));
        if(!empty($shopinfo_bus)){
            //更新店铺session
            if (empty($cur_app_info)){
                 return $this->format_ret(-1,'',$ptcode.' 缺少appkey配置');	        
            }
            $api_arr = array('app_key'=>$cur_app_info['app_key'],'app_secret'=>$cur_app_info['app_secret']);
            $api_arr['session'] = $shopinfo['sd_top_session'];
            $api_arr['refresh_token'] = $cur_app_info['refresh_token'];
            $api_arr['expires_in'] = $shopinfo['sd_end_time'];
            $api_arr['nick'] = $shopinfo['sd_nick'];
            $api_arr['shop_type'] = $shopinfo_bus['shop_type'];

            $api = json_encode($api_arr);
        
            $up_row = array();
            $up_row['shop_code'] = $shopinfo_bus['shop_code'];
            $up_row['source'] = $ptcode;
            $up_row['api'] = $api;
            $up_row['tb_shop_type'] = $shopinfo_bus['shop_type'];

            $up_row['nick'] = $api_arr['nick'];
            $up_row['app_key'] = $cur_app_info['app_key'];
            $up_row['app_secret'] = $cur_app_info['app_secret'];
            $up_row['session_key'] =  $api_arr['session'];
            $up_row['kh_id'] =$shopinfo['sd_kh_id'];
            
            $sql = "select * from base_shop_api where shop_code = :shop_code";
            $db_shop_api = $db->get_row($sql,array(':shop_code'=>$shopinfo_bus['shop_code']));
            $on_dup_up_arr = array('api=values(api)','session_key=values(session_key)');
            if (!empty($db_shop_api)){
                $chk_fld_arr = explode(',','nick,app_key,app_secret,kh_id');
                foreach($chk_fld_arr as $_fld){
                    if (empty($db_shop_api[$_fld])){
                            $on_dup_up_arr[] = "{$_fld}=values({$_fld})";
                    }
                }
            }
            $on_dup_up = join(',',$on_dup_up_arr);
        
            $tbm =new TbModel("base_shop_api",'',$db);
            $ret =$tbm->insert_multi_duplicate('base_shop_api', array($up_row),$on_dup_up);
            if ($ret['status']<0){
                return $this->format_ret(-1,'',$shopinfo_bus['shop_name'].' 授权失败,'.$ret['message']);
            }
            
            $up_row = array();
            $up_row['authorize_state'] = 1;
            $up_row['authorize_date'] = $shopinfo['sd_end_time'];
            $up_row['shop_user_nick'] = $shopinfo['sd_nick'];
            $tbmshop =new TbModel("base_shop",'',$db);
            $ret = $tbmshop->update($up_row,array('shop_code'=>$shopinfo_bus['shop_code']));
            //echo '<hr/>up_row222<xmp>'.var_export($up_row,true).'</xmp>';
            if ($ret['status']<0){
                 return $this->format_ret(-1,'',$shopinfo_bus['shop_name'].' 授权失败,'.$ret['message']);
            }
            return $this->format_ret(-1,'',$shopinfo_bus['shop_name'].' 授权成功');
        }else {
            //店铺信息不存在
            return $this->format_ret('-1', '', '店铺授权推送失败,店铺不存在');
        }
    }
}