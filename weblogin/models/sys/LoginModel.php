<?php
require_model('tb/TbModel');

class loginModel extends TbModel {
    //哪些产品CODE是efast用到的
    private $cp_code_limit = 'efast365';
    //根据授权码能取到cp_id,也就能定位出当前的授权码是用于哪个EFAST版本的
    private $user_cp_code;
    private $user_cp_id;
    private $kh_app_key = '';
    private $login_fail_num_limit = 3;
    private $pra_product_version_map = array();
            function __construct(){
		parent::__construct();
                $this->pra_product_version_map = array('1'=>'efast5_Standard','2'=>'efast5_Enterprise','3'=>'efast5_Ultimate');    
                $this->pra_product_area_map = array('1'=>'online','2'=>'offline','3'=>'o2o');  
	}

    //分配一个可用的VM给新用户
    /*
    function get_vm_info($kh_id){
        $data = date('Y-m-d');
        $sql = "select host_id,pra_serverpath from osp_aliyun_host where ali_server_use = 2 and ali_endtime>{$data} and kh_id = {$kh_id}";
        $row = ctx()->db->get_row($sql);
        if(empty($row)){
            return $this->format_ret(-1,'','没有可用的WEB服务器');
        }
        return $this->format_ret(1,$row);
    }*/

    /*
    取当前用户绑定的已使用的数据库,如果没有则取当前用户绑定数据库，如果没有新分配一个RDS

    */
    function get_rds_info($kh_id){
        $ret = $this->get_bind_used_rds_info($kh_id);
        //存在已绑定已使用的数据库
        if($ret['status']>0){
            return $this->format_ret(1,$ret['data']);
        }
        $host_id = null;
        $ret = $this->get_new_rds($host_id,$kh_id);
        if($ret['status']<0){
            return $ret;
        }

        //add wq
        if(empty($this->user_cp_id)){
           $row = $this->db->get_row("select * from osp_chanpin where cp_code = '{$this->cp_code_limit}' ");
           $this->user_cp_id = $row['cp_id'];
        }


        $rds_id = $ret['data']['rds_id'];
        $rds_dbname = $ret['data']['rds_dbname'];
        //rds_name
        //初始化 rem_db_version_ip

            $sql = "select ali_outip from osp_autoservice_acc acc,osp_aliyun_host vm where acc.asa_vm_id = vm.host_id and asa_cp_id = {$this->user_cp_id} and asa_rds_id = {$rds_id}";
            $ali_outip = ctx()->db->getOne($sql);
            if(empty($ali_outip)){
                return $this->format_ret(-1,'','自动服务分配到的VM_IP为空');
            }
            $sql = "update osp_rdsextmanage_db set rem_db_version_ip = '{$ali_outip}' where rem_db_pid = {$rds_id} and rem_db_name = '{$rds_dbname}'";
            ctx()->db->query($sql);


        //新分配的数据库
        return $this->format_ret(2,$ret['data']);
    }

    //当前用户已绑定并已使用的数据库
    function get_bind_used_rds_info($kh_id){
        //r2.rds_user,r2.rds_pass,rds_link,rds_dbname
        $sql = "select r2.rds_id,r2.rds_createdate,r2.rds_user,r2.rds_pass,r2.rds_link,r1.rem_db_name as rds_dbname,r2.rds_dbname as rds_name  from osp_rdsextmanage_db r1,osp_aliyun_rds r2 where r1.rem_db_pid = r2.rds_id and r1.rem_db_khid = :rem_db_khid and r1.rem_db_is_bindkh=1 and r1.rem_db_bindtype = 1";
        //echo $sql;
        //echo '<hr/>$kh_id<xmp>'.var_export($kh_id,true).'</xmp>';//die;
        $rem_db = ctx()->db->get_row($sql,array(':rem_db_khid'=>$kh_id));
        if(empty($rem_db)){
            return $this->format_ret(-1,'','当前用户没有已绑定并已使用数据库');
        }
        
        
        return $this->format_ret(1,$rem_db);
    }

    //取当前用户绑定数据库
    function get_bind_rds_info($kh_id){
        $sql = "select r1.rem_db_pid as rds_id,r2.rds_createdate,r2.rds_user,r2.rds_pass,r2.rds_link,r1.rem_db_name as rds_dbname from osp_rdsextmanage_db r1,osp_aliyun_rds r2 where r1.rem_db_pid = r2.rds_id and r1.rem_db_khid = :rem_db_khid and r1.rem_db_is_bindkh=1 and r1.rem_db_bindtype = 0";
        $rem_db = ctx()->db->get_row($sql,array(':rem_db_khid'=>$kh_id));
        if(empty($rem_db)){
            return $this->format_ret(-1,'','当前用户没有绑定数据库');
        }
        return $this->format_ret(1,$rem_db);
    }

    //分配新的RDS给新用户 $host_id 没用了
    function get_new_rds($host_id = null,$kh_id){
        $ret = $this->get_bind_rds_info($kh_id);
        //存在已绑定的数据库
        if($ret['status']>0){
            return $ret;
        }
        $end_time = date('Y-m-d').' 23:59:59';
        $sql = "SELECT
                    rds.rem_rds_id,mx.rem_db_name,mx.rem_db_id
                FROM
                    osp_rdsextmanage rds,
                    osp_rdsextmanage_db mx,
                    osp_chanpin cp
                WHERE
                    rds.rem_rds_id = mx.rem_db_pid
                AND rds.rem_cp_id = cp.cp_id
                AND cp.cp_code = '{$this->cp_code_limit}'
                AND mx.rem_db_is_bindkh = 0
                AND mx.rem_db_bindtype = 0";//AND rds.rem_rds_endtime >= '{$end_time}'
        //echo $sql;die;
        $rds = ctx()->db->getRow($sql);
        //echo '<hr/>$rds<xmp>'.var_export($rds,true).'</xmp>';
        if(empty($rds)){
            return $this->format_ret(-1,'','没有可用的rds');
        }
        //新分配的RDS 绑定到当前用户
        $sql = "update osp_rdsextmanage_db set rem_db_khid = :rem_db_khid,rem_db_is_bindkh=1,rem_db_bindtype = 0 where rem_db_id = :rem_db_id";
        ctx()->db->query($sql,array(':rem_db_khid'=>$kh_id,':rem_db_id'=>$rds['rem_db_id']));
        //die;

        //更新 osp_rdsextmanage 表的未用和已用的数据库 数量
        $rem_db_pid = (int)$rds['rem_rds_id'];
        $sql = "select rem_db_is_bindkh,count(rem_db_is_bindkh) as sl from osp_rdsextmanage_db where rem_db_pid = {$rem_db_pid} group by rem_db_is_bindkh";
        //echo $sql;die;
        $db_used = ctx()->db->get_all($sql);
        $used_db_arr = array();
        foreach($db_used as $sub_arr){
            $used_db_arr[$sub_arr['rem_db_is_bindkh']] = $sub_arr['sl'];
        }
        $noused_db_sl = (int)@$used_db_arr[0];
        $sql = "update osp_rdsextmanage set rem_dbunnum = $noused_db_sl where rem_rds_id=$rem_db_pid";
        ctx()->db->query($sql);

        $sql = "select * from osp_aliyun_rds where rds_id = {$rds['rem_rds_id']}";
        $rds_info = ctx()->db->get_row($sql);
        $rds_info['rds_name'] = $rds['rds_dbname'];
        $rds_info['rds_dbname'] = $rds['rem_db_name'];

        
        //echo '<hr/>$rds_info<xmp>'.var_export($rds_info,true).'</xmp>';die;
        return $this->format_ret(1,$rds_info);
    }


    //根据user_nick查找订购的信息
    function get_order_by_nick($user_nick){
        $sql = "SELECT
                    db.rem_db_name,
                    sd.sd_kh_id
                FROM
                    osp_shangdian sd,
                    osp_rdsextmanage_db db
                WHERE
                    sd.sd_kh_id = db.rem_db_khid
                AND sd.sd_nick = :sd_nick";

        //echo '<hr/>$sql<xmp>'.$sql.'</xmp>';
        //echo '<hr/>$user_nick<xmp>'.var_export($user_nick,true).'</xmp>';

        $db_rds = ctx()->db->get_row($sql,array(':sd_nick'=>$user_nick));
        //echo '<hr/>$db_rds<xmp>'.var_export($db_rds,true).'</xmp>';die;
        if(empty($db_rds)){
            return $this->format_ret(-1,'','此用户没有订购过');
        }
        return $this->format_ret(1,$db_rds);
    }

    function parse_params_data($request){
        $ret = load_model('common/CryptReqModel')->get($request);
        $data_arr = $ret['data'];
        $user_nick = $data_arr['shop_user_nick'];
        if(empty($user_nick)){
            return $this->format_ret(-1,'','user_nick参数不能为空');
        }
        return $this->format_ret(1,$data_arr);
    }


    //取店铺的名称及 B店/C店的标识
    function get_api_shop_title($user_nick,$access_token,$app_key=''){
        //--test
        //$data = array('shop_title'=>'唐江镇','shop_type'=>'C');
        //return $this->format_ret(1,$data);

        $user_nick = urldecode($user_nick);
        require_model('sys/TaobaoModel');
        $tb_mdl = new TaobaoModel();
        if(empty($app_key)){
            $app_key = $this->kh_app_key;
        }
        $tb_mdl->set_app($app_key);
      
        $tb_mdl->app_session = $access_token;

        $req_arr = array('fields'=>'title,nick','nick'=>$user_nick);
        $ret = $tb_mdl->get_result('taobao.shop.get',$req_arr);
        //echo '<hr/>$req_arr<xmp>'.var_export($req_arr,true).'</xmp>';
        //echo '<hr/>$ret<xmp>'.var_export($ret,true).'</xmp>';
        if($ret['status']<0){
            
            if($ret['message']=='用户对应的店铺不存在'){
                $ret_data = array('shop_title'=>$user_nick,'shop_type'=>'B');
                return $this->format_ret(1,$ret_data);
            }
            
            return $ret;
        }
        $shop_title = $ret['data']['shop_get_response']['shop']['title'];
        if(empty($shop_title)){
            return $this->format_ret(-1,'','通过API查找店铺名称失败');
        }
        $result = array('shop_title'=>$shop_title);
        $req_arr = array('fields'=>'nick,type');

        $ret = $tb_mdl->get_result('taobao.user.seller.get',$req_arr);
        if($ret['status']<0){
            return $ret;
        }
        $shop_type = $ret['data']['user_seller_get_response']['user']['type'];
        if(empty($shop_title)){
            return $this->format_ret(-1,'','通过API查找店铺类型失败');
        }
        $data = array('shop_title'=>$shop_title,'shop_type'=>$shop_type);
        return $this->format_ret(1,$data);
    }
    
  
    function set_jdp_add($rds_name,$access_token){

        require_model('sys/TaobaoModel');
        $tb_mdl = new TaobaoModel();
         $tb_mdl->set_app($this->kh_app_key);
        $tb_mdl->app_session = $access_token;

        $req_arr = array('rds_name'=>$rds_name);
        $ret = $tb_mdl->get_result('taobao.jushita.jdp.user.add',$req_arr);

        $is_success = $ret['data']['jushita_jdp_user_add_response']['is_success'];
        if($is_success==true||$is_success=='true'){
            return $this->format_ret(1);
        }else{
            $log = ' rds_name:'.$rds_name.',access_token:'.$access_token;
            $log .= 'jdp_add:'.var_export($ret['data'],true);
            error_log($log, 3, ROOT_PATH.'logs/sql.log');
        }
        
        return $this->format_ret(1);

    }
    
    
    
    
    
    
    private function check_sys_is_login($kh_id,$user_code){
        $ip = $this->get_real_ip();
        $force_logout = require_conf('force_logout');
        if(in_array($ip, $force_logout['ip'])){
              return $this->format_ret(-1,'','禁止登录IP，请联系管理员');
        }
        if(isset($force_logout['user'][$kh_id])&&in_array($user_code, $force_logout['user'][$kh_id])){
             return $this->format_ret(-1,'','禁止登录用户，请联系管理员');
        }
          return $this->format_ret(1);
    }
            

    function login_by_form($request){
        $customer_name = $request['customer_name'];
        $user_code = $request['user_code'];
        $password = $request['password'];
        if(empty($customer_name) || empty($user_code) || empty($password)){
            return $this->format_ret(-1,'','请输入公司名称,用户名和密码');
        }
        $cp_code_limit_insql = "'".join("','",explode(',',$this->cp_code_limit))."'";
        $sql = "SELECT
                    kh.kh_id,
                    kh.kh_code,
                    kh.kh_name,
                    auth.pra_cp_version,
                    auth.pra_cp_version,
                    auth.pra_enddate,
                    auth.pra_state,
                    auth.pra_serverpath,
                    auth.pra_product_area,
                    auth.pra_product_version,
                    auth.pra_app_key,
                    auth.pra_authnum
                FROM
                    osp_productorder_auth auth,
                    osp_chanpin cp,
                    osp_kehu kh
                WHERE
                    auth.pra_cp_id = cp.cp_id
                AND auth.pra_kh_id = kh.kh_id
                AND cp.cp_code in({$cp_code_limit_insql})
                AND kh.kh_name = :kh_name";
        $db_kh = ctx()->db->get_row($sql,array(':kh_name'=>$customer_name));
        if(empty($db_kh)){
            return $this->format_ret(-1,'',$customer_name.' 不存在');
        }
   
        //设置登录日志
        load_model("sys/LoginCheckModel")->set_login_kh_info($db_kh['kh_id'],$user_code,$db_kh['pra_app_key']);

        $ret_check =  $this->check_sys_is_login($db_kh['kh_id'],$user_code);
        
        if($ret_check['status']<1){
          return  $ret_check;
        }

        
        if(time()-strtotime($db_kh['pra_enddate'])>0) {
            return $this->format_ret(-1,'',$customer_name.' 授权已过期');
        }
        if($db_kh['pra_state'] == 0){
            return $this->format_ret(-1,'',$customer_name.' 已禁用');
        }
        $ret = $this->get_rds_info($db_kh['kh_id']);
        if($ret['status']<0){
            return $ret;
        }

        $params = array('app_act'=>'login_by_form');

        $data = array();
        $data['customer_name'] = $request['customer_name'];
        $data['user_code'] = $request['user_code'];
        $data['password'] = $request['password'];
        $data['kh_id'] = $db_kh['kh_id'];
        $data['app_key'] = $db_kh['pra_app_key'];

        $data['rds_user'] = $ret['data']['rds_user'];
        $data['rds_id'] = $ret['data']['rds_id'];
        $data['cp_code'] =  isset($this->pra_product_version_map[$db_kh['pra_product_version']])?
                $this->pra_product_version_map[$db_kh['pra_product_version']]:$this->pra_product_version_map[1];
         $data['cp_area'] =  isset($this->pra_product_area_map[$db_kh['pra_product_area']])?
                $this->pra_product_area_map[$db_kh['pra_product_area']]:$this->pra_product_area_map[1];
         $data['auth_num'] = $db_kh['pra_authnum'];
        
        
        $data['rds_id'] = $ret['data']['rds_id'];

        $keylock = load_model('sys/OspCryptModel')->get_keylock_string($ret['data']['rds_createdate']);
        $data['rds_pass'] =
        load_model('sys/OspCryptModel')->create_aes_decrypt($ret['data']['rds_pass'],$keylock);

        $data['rds_link'] = $ret['data']['rds_link'];
        $data['rds_dbname'] = $ret['data']['rds_dbname'];
        $data['login_server_url'] = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];

        $data_t = $data;
        $data_t['check'] = 1;

        require_model('common/CryptReqModel');
        $obj = new CryptReqModel();
        $params = $obj->create($data_t);
        $params['app_act'] = 'index/login_by_form';
        /*
        $ret = $this->get_vm_info($db_kh['kh_id']);
        if($ret['status']<0){
            return $ret;
        }
        $vm_info = $ret['data'];
        */
        //如果需要验证码
        $show_captcha = CTX()->get_session('show_captcha');
        //echo '<hr/>$captcha_code<xmp>'.var_export($captcha_code,true).'</xmp>';
        if(!empty($show_captcha)){
            $captcha_code = CTX()->get_session('captcha_code');
            if(strtolower($captcha_code) != strtolower(@$request['captcha'])){
                return $this->format_ret(-10,'','您输入验证码不对,请重新输入');
            }
        }
        $pra_serverpath = $db_kh['pra_serverpath'];
        $login_chk = $this->do_execute($pra_serverpath,$params);
        //echo '<hr/>$login_chk<xmp>'.var_export($login_chk,true).'</xmp>';die;
        $login_chk_arr = json_decode($login_chk,true);
        if(empty($login_chk_arr)){
            return $this->format_ret(-1,'','登录失败 '.$login_chk);
        }

        if((int)$login_chk_arr['status'] != 1){
            $login_fail_num = (int)@$login_chk_arr['data']['login_fail_num'];
            if($login_fail_num>=$this->login_fail_num_limit){
                $login_chk_arr['data']['show_captcha'] = 1;
                CTX()->set_session('show_captcha',1);
            }
            return $login_chk_arr;
        }

         CTX()->set_session('show_captcha','');
        if(!empty($login_chk_arr['data']['tel'])){
            load_model("sys/LoginCheckModel")->set_user_tel($login_chk_arr['data']['tel']);
        }   
        
        $params = $obj->create($data);
        $params['app_act'] = 'index/login_by_form';
        
        if(isset($request['is_app'])&&$request['is_app']==1){
            $params['is_app'] = 1;
        }
        

        $login_url = $pra_serverpath.'?'.http_build_query($params);
        //echo $login_url;die;
        return $this->format_ret(1,$login_url);
    }

    function do_execute($url, $params) {
        $ch = curl_init ();
        //curl_setopt($ch,   CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_FAILONERROR, false );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt ( $ch, CURLOPT_TIMEOUT, 3);
        /**
         * 如果参数为数组则
         */
        if (is_array ( $params ) && 0 < count ( $params )) {
            $postBodyString = "";
            foreach ( $params as $k => $v ) {
                $postBodyString .= "$k=" . urlencode ( $v ) . "&";
            }
            unset ( $k, $v );
        } else {
            $postBodyString = $params;
        }
        curl_setopt ( $ch, CURLOPT_POST, true );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, substr ( $postBodyString, 0, - 1 ) );
        //echo $url . '?' .substr ( $postBodyString, 0, - 1 );//die;
        $reponse = curl_exec ( $ch );
        if (curl_errno ( $ch )) {
            $curl_error = curl_error ( $ch );
            throw new Exception ( $curl_error, 0 );
        } else {
            $httpStatusCode = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
            if (200 !== $httpStatusCode) {
                throw new Exception ( $reponse, $httpStatusCode );
            }
        }
        curl_close ( $ch );

        //echo '<hr/>$curl<xmp>'.$reponse.'</xmp>';die;
        return $reponse;
    }

    function guid(){
      mt_srand((double)microtime()*10000);
      $charid = strtoupper(md5(uniqid(rand(), true)));
      $hyphen = chr(45);// "-"
      $uuid = substr($charid, 0, 8).$hyphen
              .substr($charid, 8, 4).$hyphen
              .substr($charid,12, 4).$hyphen
              .substr($charid,16, 4).$hyphen
              .substr($charid,20,12);
      return $uuid;
    }

    //验证授权码是否有效
    function chk_auth_code($auth_code){
        $auth_code = trim($auth_code);
        /*
        主要取 pra_kh_id pra_id pra_cp_id
        `pro_product_version` int(10) DEFAULT NULL COMMENT '产品版本，1标准版、2企业版、3旗舰版',
        efast5_Standard,efast5_Ultimate,efast5_Enterprise
        */
        $pra_product_version_map = array('1'=>'efast5_Standard','2'=>'efast5_Enterprise','3'=>'efast5_Ultimate');
        $sql = "select pra_id,pra_cp_id,pra_kh_id,pra_enddate,pra_state,pra_authkey,pra_authnum,pra_serverpath,pra_product_version,pra_product_area,pra_app_key from osp_productorder_auth where pra_authkey = :pra_authkey";
        $db_auth_row = ctx()->db->get_row($sql,array(':pra_authkey'=>$auth_code));
        if(empty($db_auth_row)){
            return $this->format_ret(-1,'',$auth_code." 授权码不存在");
        }
        $pra_enddate = $db_auth_row['pra_enddate'];
        if(strtotime($pra_enddate)<time()){
            return $this->format_ret(-1,'',"授权已超期");
        }
        if((int)$db_auth_row['pra_state'] == 0){
            return $this->format_ret(-1,'',"授权已禁用");
        }
        //取user_cp_code
        $user_cp_code = @$pra_product_version_map[$db_auth_row['pra_product_version']];
        if(empty($user_cp_code)){
            return $this->format_ret(-1,'',$auth_key." 授权码关联的产品版本标识有错");
        }
       $db_auth_row['pra_product_area'] = empty($db_auth_row['pra_product_area'])?1:$db_auth_row['pra_product_area'];
       $db_auth_row['cp_area'] = $this->pra_product_area_map[$db_auth_row['pra_product_area']];
        
        $this->user_cp_code = $user_cp_code;
        $this->user_cp_id = $db_auth_row['pra_cp_id'];
        return $this->format_ret(1,$db_auth_row);
    }

    //根据 sale_channel_code 验证当前客户可用店铺数
    function check_active_shop_by_sale_channel_code($sale_channel_code,$pra_id,$cp_id,$kh_id,$is_exist_user_nick){
        $sql = "select pt_id,pt_name,pt_pay_type from osp_platform where pt_code = :pt_code";
        $pt_row = ctx()->db->get_row($sql,array(':pt_code'=>$sale_channel_code));
        if(empty($pt_row)){
            return $this->format_ret(-1,'',"找不到{$sale_channel_code}的平台信息");
        }
        $pt_id = $pt_row['pt_id'];
        $pt_name = $pt_row['pt_name'];
        $pt_pay_type = $pt_row['pt_pay_type'];
        if($pt_pay_type == 1 && $is_exist_user_nick == 0){
            $sql = "select pra_shop_num from osp_productorder_shopauth where pra_shop_pid = :pra_shop_pid and pra_shop_pfid = :pra_shop_pfid";
            $pra_shop_num = (int)ctx()->db->getOne($sql,array(':pra_shop_pid'=>$pra_id,':pra_shop_pfid'=>$cp_id));
            $sql= "select count(*) from osp_shangdian where sd_kh_id = {$kh_id}";
            $exist_shop_num = ctx()->db->getOne($sql);
            if($exist_shop_num>=$pra_shop_num){
                return $this->format_ret(-1,'',"您允许使用{$pra_shop_num}个{$pt_name}店铺,当前已经使用{$exist_shop_num},请重新订购.");
            }
        }
        return $this->format_ret(1,$pt_row);
    }

    function create_shangdian_auth_jd($info){
        $sql = "select sd_id from osp_shangdian where sd_jd_id = :sd_jd_id";
        $sd_id = (int)ctx()->db->getOne($sql,array(':sd_jd_id'=>$info['uid']));
        if($sd_id>0){
            $sql = "delete from osp_shangdian where sd_id = {$sd_id}";
            ctx()->db->query($sql);
            $sql = "delete from osp_shop_warrant where sw_sd_id = {$sd_id}";
            ctx()->db->query($sql);
        }
        $ins = array('sd_name'=>$info['shop_title'],
                    'sd_pt_id'=>$info['pt_id'],
                    'sd_kh_id'=>$info['kh_id'],
                    'sd_top_session'=>$info['access_token'],
                    'sd_nick'=>$info['user_nick'],
                    'sd_jd_id'=>$info['uid'],
                    'sd_end_time'=>$info['expires_in']);
        $info['sd_isauth'] = 1;
        $info['sd_session_expired'] = 1;
        $ret = M('osp_shangdian')->insert_dup($ins);
        if($ret['status']<0){
            return $ret;
        }
        $sd_jd_id = $info['uid'];
        $sql = "select sd_id from osp_shangdian where sd_jd_id = :sd_jd_id";
        $sd_id = ctx()->db->getOne($sql,array(':sd_jd_id'=>$sd_jd_id));

        if((int)$sd_id == 0){
            return $this->format_ret(-1,'',"生成店铺档案失败");
        }
        $sw_update_date = date('Y-m-d H:i:s');
        $ins = array('sw_kh_id'=>$info['kh_id'],
                    'sw_cp_id'=>$info['cp_id'],
                    'sw_sd_id'=>$sd_id,
                    'sw_pt_id'=>$info['pt_id'],
                    'sw_shop_session'=>$info['access_token'],
                    'sw_update_date'=>$sw_update_date,
                    'sw_valid_date'=>$info['expires_in']);
        $ret = M('osp_shop_warrant')->insert_dup($ins);
        if($ret['status']<0){
            return $ret;
        }
    }
    
    /*
    生成店铺及授权
    $info = array('shop_title'=>,'cp_id'=>,'pt_id'=>,'kh_id'=>,'access_token'=>,'user_nick'=>,'expires_in'=>);
    */
    function create_shangdian_auth($info){
        $sql = "select sd_id from osp_shangdian where sd_nick = :sd_nick";
        $sd_id = (int)ctx()->db->getOne($sql,array(':sd_nick'=>$info['user_nick']));
        if($sd_id>0){
            $sql = "delete from osp_shangdian where sd_id = {$sd_id}";
            ctx()->db->query($sql);
            $sql = "delete from osp_shop_warrant where sw_sd_id = {$sd_id}";
            ctx()->db->query($sql);
        }
        $ins = array('sd_name'=>$info['shop_title'],
                    'sd_pt_id'=>$info['pt_id'],
                    'sd_kh_id'=>$info['kh_id'],
                    'sd_top_session'=>$info['access_token'],
                    'sd_nick'=>$info['user_nick'],
                    'sd_end_time'=>$info['expires_in']);
        $info['sd_isauth'] = 1;
        $info['sd_session_expired'] = 1;
        $ret = M('osp_shangdian')->insert_dup($ins);
        if($ret['status']<0){
            return $ret;
        }
        $sd_nick = $info['user_nick'];
        $sql = "select sd_id from osp_shangdian where sd_nick = :sd_nick";
        $sd_id = ctx()->db->getOne($sql,array(':sd_nick'=>$sd_nick));

        if((int)$sd_id == 0){
            return $this->format_ret(-1,'',"生成店铺档案失败");
        }
        $sw_update_date = date('Y-m-d H:i:s');
        $ins = array('sw_kh_id'=>$info['kh_id'],
                    'sw_cp_id'=>$info['cp_id'],
                    'sw_sd_id'=>$sd_id,
                    'sw_pt_id'=>$info['pt_id'],
                    'sw_shop_session'=>$info['access_token'],
                    'sw_update_date'=>$sw_update_date,
                    'sw_valid_date'=>$info['expires_in']);
        $ret = M('osp_shop_warrant')->insert_dup($ins);
        if($ret['status']<0){
            return $ret;
        }
        

        
    }

    /*
    $data = array();
    $data['user_nick'] = $ret_arr['taobao_user_nick'];
    $data['access_token'] = $ret_arr['access_token'];
    $data['refresh_token'] = $ret_arr['refresh_token'];
    $data['expires_in'] = $ret_arr['expires_in'];
    $data['auth_key'] = $auth_key;

    $data['sale_channel_code'] = 'taobao';
    $data['shop_code'] = $efast_shop_code;
    $data['efast_url'] = $efast_url;
    */
    function from_efast_auth_shop($req){
        $ret = load_model('common/CryptReqModel')->get($req);
        if($ret['status']<0){
            return $ret;
        }

        $req_data = $ret['data'];
        $auth_key = $req_data['auth_key'];
        $user_nick = $req_data['user_nick'];
        $access_token = $req_data['access_token'];
        $refresh_token = $req_data['refresh_token'];
        $sale_channel_code = $req_data['sale_channel_code'];
        $shop_code = $req_data['shop_code'];
        $app_key = isset($req_data['app_key'])?$req_data['app_key']:'';
        
        //--test
        //$user_nick = 'shopping_attb';
        //$access_token = '70000100e39971915bb3c7e4a46771843f287f10e8cad6e5def1ef4c1826f2e2e7a8e0258123788';

        $ret = $this->chk_auth_code($auth_key);
        if($ret['status']<0){
            return $ret;
        }

        //主要取 pra_kh_id  pra_cp_id pra_id
        //echo '<hr/>$auth_key_info<xmp>'.var_export($ret,true).'</xmp>';
        $auth_key_info = $ret['data'];
        $kh_id = $auth_key_info['pra_kh_id'];
        $cp_id = $auth_key_info['pra_cp_id'];
        $pra_id = $auth_key_info['pra_id'];
        $auth_num = $auth_key_info['pra_authnum'];//点数
        $cp_area = $auth_key_info['cp_area'];
        $pra_app_key = $auth_key_info['pra_app_key'];//点数
        $this->kh_app_key = $pra_app_key;
        $is_exist_user_nick = 0;
        if(!empty($user_nick)){
            $sql = "select count(*) from osp_shangdian where sd_nick = :sd_nick";
            $c = ctx()->db->getOne($sql,array(':sd_nick'=>$user_nick));
            if($c>0){
                $is_exist_user_nick = 1;
            }
        }

        $ret = $this->check_active_shop_by_sale_channel_code($sale_channel_code,$pra_id,$cp_id,$kh_id,$is_exist_user_nick);
        if($ret['status']<0){
            return $ret;
        }
        $pt_id = $ret['data']['pt_id'];

        //调用api取 shop_title shop_type
        if($sale_channel_code == 'taobao'){
            $result = $this->get_api_shop_title($user_nick,$access_token,$app_key);

            if($result['status']<0){
                return $result;
            }
            $shop_title = $result['data']['shop_title'];
            $shop_type = $result['data']['shop_type'];
            
                    //新增设置推送
                    $rds_info_ret = $this->get_rds_info($kh_id);
                    //echo '<hr/>$rds_info_ret<xmp>'.var_export($rds_info_ret,true).'</xmp>';die;
                    if($rds_info_ret['status']<0){
                        return $rds_info_ret;
                    }
                    if($rds_info_ret['status'] == 2){
                        $is_init_sys = 1;
                    }else{
                        $is_init_sys = 0;
                    }
                    //设置推送 
                    $this->set_jdp_add($rds_info_ret['data']['rds_name'],$access_token);

        }else{
            $jd_api = load_model('sys/JingdongModel');
            //默认翼商KEY，授权没设置，否则为宝塔key
            $app_key = empty($app_key)?'C0C9110ED2D32E13266D4522D55C78AF':$app_key;
            $pra_app_key = $app_key;
            $jd_api->set_app($app_key);
            $result = $jd_api->get_api_shop_title($access_token);
            //echo '<hr/>$result<xmp>'.var_export($result,true).'</xmp>';
            if($result['status']<0){
                $result['message'] = '读取京东店铺名称失败：'.$result['message'];
                return $result;
            }
            //$shop_title = $result['data'];
            $shop_title = $result['data']['shop_name'];
            $user_nick = $shop_title;
            $remark = $result['data']['vender_id'];// 	商家编号  
            $shop_type = $result['data']['type'];//店铺类型
        }

        $user_nick = empty($user_nick)?$shop_title:$user_nick;
        
        $info = array('shop_title'=>$shop_title,
                    'cp_id'=>$cp_id,
                    'pt_id'=>$pt_id,
                    'kh_id'=>$kh_id,
                    'access_token'=>$access_token,
                    'user_nick'=>$user_nick,
                    'expires_in'=>$req_data['expires_in']);
        $ret = $this->create_shangdian_auth($info);
        //echo '<hr/>$ret<xmp>'.var_export($ret,true).'</xmp>';

        if($ret['status']<0){
            return $ret;
        }

       // $params = array('app_act'=>'from_efast_auth_shop');

        $data = array();
        $data['shop_user_nick'] = $user_nick;
        $data['shop_title'] = $shop_title;
        $data['access_token'] = $access_token;
        $data['refresh_token'] = $refresh_token;
        $data['expires_in'] = $req_data['expires_in'];
        $data['shop_type'] = $shop_type;
        $data['shop_code'] = $req_data['shop_code'];
        $data['sale_channel_code'] = $sale_channel_code;
        $data['kh_id'] = $kh_id;        
        $data['app_key'] = !empty($app_key)?$app_key: $pra_app_key;       
        $data['remark'] = $remark;//记录京东的vender_id
        //echo '<hr/>$req_data<xmp>'.var_export($req_data,true).'</xmp>';
        //echo '<hr/>$data<xmp>'.var_export($data,true).'</xmp>';

        require_model('common/CryptReqModel');
        $obj = new CryptReqModel();
        $req_arr = $obj->create($data);

        $req_arr['app_act'] = 'index/save_auth_shop';
        //echo '<hr/>$req_arr<xmp>'.var_export($req_arr,true).'</xmp>';die;
        $login_url = $req_data['efast_url'].'?'.http_build_query($req_arr);
        //echo $login_url;die;
        echo "<script>location.href = '{$login_url}';</script>";
      }

    //根据平台店铺id匹配授权码—只针对京东
    function get_auth_code_by_shop_user_id($uid){
        $sql = "select sd_kh_id from osp_shangdian where sd_jd_id = :sd_jd_id";
        $sd_row = ctx()->db->get_row($sql,array(':sd_jd_id'=>$uid));
        if(empty($sd_row)){
            return $this->format_ret(-1,'',$user_nick.' 店铺不存在');
        }
        $kh_id = $sd_row['sd_kh_id'];
        //如果能查到店铺，但查不到关连的数据库，也认为这个店铺不存在
        $ret = $this->get_bind_used_rds_info($kh_id);
        if($ret['status']<0){
            return $ret;
        }

        $sql = "select pra_authkey from osp_productorder_auth where pra_kh_id = {$kh_id}";
        $auth_row = ctx()->db->get_row($sql);
        if(empty($sd_row)){
            return $this->format_ret(-1,'','授权码不存在');
        }
        return $this->format_ret(1,$auth_row['pra_authkey']);
    }
      
    //根据 shop_user_nick 查到授权码
    function get_auth_code_by_shop_user_nick($user_nick){
        $sql = "select sd_kh_id from osp_shangdian where sd_nick = :sd_nick";
        $sd_row = ctx()->db->get_row($sql,array(':sd_nick'=>$user_nick));
        if(empty($sd_row)){
            return $this->format_ret(-1,'',$user_nick.' 店铺不存在');
        }
        $kh_id = $sd_row['sd_kh_id'];
        //如果能查到店铺，但查不到关连的数据库，也认为这个店铺不存在
        $ret = $this->get_bind_used_rds_info($kh_id);
        if($ret['status']<0){
            return $ret;
        }

        $sql = "select pra_authkey from osp_productorder_auth where pra_kh_id = {$kh_id}";
        $auth_row = ctx()->db->get_row($sql);
        if(empty($sd_row)){
            return $this->format_ret(-1,'','授权码不存在');
        }
        return $this->format_ret(1,$auth_row['pra_authkey']);
    }
    
    /*
    * 一号店授权—服务平台授权操作
    * @author WangShouChong
    */
    function from_fuwu_yihaodian($req){
        //反编码授权信息
        $ret = load_model('common/CryptReqModel')->get($req);
        if($ret['status']<0){
            return $ret;
        }
        //解析授权信息
        $req_data = $ret['data'];
        $user_nick = $req_data['user_nick'];        //授权用户对应的昵称
        $access_token = $req_data['access_token'];
        $refresh_token = $req_data['refresh_token'];
        $sale_channel_code = $req_data['sale_channel_code'];
        
        $encode_req_data = load_model('common/CryptModel')->encode(json_encode($ret['data']));
        $ret = $this->get_auth_code_by_shop_user_nick($user_nick);
        //如果根据user_nick找不到对应的授权码，那么让客户手动输入授权码
        if($ret['status']<0){
            $params = array('app_act'=>'input_auth_key');
            $params['_data'] = $encode_req_data;
            $url = '?'.http_build_query($params);
            return $this->format_ret(-2,$url,$ret['message']);
        }
        $auth_key = $ret['data'];
        $ret = $this->from_fuwu_yihaodian_to_efast($req_data,$auth_key,0);
        return $ret;
    }
    
    function from_fuwu_yihaodian_to_efast($req_data,$auth_key,$is_allot_new_rds = 0,$new_user = array()){
        $user_nick = $req_data['user_nick'];
        $access_token = $req_data['access_token'];
        $refresh_token = $req_data['refresh_token'];
        $sale_channel_code = $req_data['sale_channel_code'];
        //验证运营平台授权信息
        $ret = $this->chk_auth_code($auth_key);
        if($ret['status']<0){
            return $ret;
        }
        $pra_serverpath = $ret['data']['pra_serverpath'];
        //主要取 pra_kh_id  pra_cp_id pra_id
        $auth_key_info = $ret['data'];
        $kh_id = $auth_key_info['pra_kh_id'];
        $cp_id = $auth_key_info['pra_cp_id'];
        $pra_id = $auth_key_info['pra_id'];
        $auth_num = $auth_key_info['pra_authnum'];//点数
        
        $sql = "select kh_name from osp_kehu where kh_id = :kh_id";
        $kh_name = (string)ctx()->db->getOne($sql,array(':kh_id'=>$kh_id));
        if(empty($kh_name)){
            return $this->format_ret(-1,'','找不到客户名称');
        }
        
        $rds_info_ret = $this->get_rds_info($kh_id);
        if($rds_info_ret['status']<0){
            return $rds_info_ret;
        }
        if($rds_info_ret['status'] == 2){
            $is_init_sys = 1;
        }else{
            $is_init_sys = 0;
        }
        if(empty($new_user) && $is_init_sys == 1){
            return $this->format_ret(-3,$kh_name,'请提供初始化系统的管理员账号和密码');
        }
        
        $sql = "select count(*) from osp_shangdian where sd_nick = :sd_nick";
        $c = ctx()->db->getOne($sql,array(':sd_nick'=>$user_nick));
        if($c>0){
            $is_exist_user_nick = 1;
        }else{
            $is_exist_user_nick = 0;
        }

        $ret = $this->check_active_shop_by_sale_channel_code($sale_channel_code,$pra_id,$cp_id,$kh_id,$is_exist_user_nick);
        if($ret['status']<0){
            return $ret;
        }
        $pt_id = $ret['data']['pt_id'];
        
        //调用api取 shop_title shop_type
        $result = load_model('sys/YihaodianModel')->get_api_shop_title($access_token);
        if($result['status']<0){
            return $result;
        }
        $shop_title = $result['data']['shop_title'];

        $info = array('shop_title'=>$shop_title,
                    'cp_id'=>$cp_id,
                    'pt_id'=>$pt_id,
                    'kh_id'=>$kh_id,
                    'access_token'=>$access_token,
                    'user_nick'=>$user_nick,
                    'expires_in'=>$req_data['expires_in']);
        $ret = $this->create_shangdian_auth($info);
        if($ret['status']<0){
            return $ret;
        }
        //同步数据到sysdb
        $ret = load_model('sys/RdsDataModel')->update_kh_data($kh_id,0,'osp_rdsextmanage_db');
        if ($ret['status']<0){
	        return $ret;
        }
        $params = array('app_act'=>'from_fuwu_yihaodian');

        $data = array();
        $data['shop_user_nick'] = $user_nick;
        $data['shop_title'] = $shop_title;
        $data['access_token'] = $access_token;
        $data['refresh_token'] = $refresh_token;
        $data['expires_in'] = $req_data['expires_in'];
        $data['shop_type'] = $shop_type;
        $data['is_init_sys'] = $is_init_sys;
        //要把 授权KEY和点数 带上
        $data['auth_key'] = $auth_key;
        $data['auth_num'] = $auth_num;
        $data['kh_name'] = $kh_name;
        $data['kh_id'] = $kh_id;
        $data['version'] = 'v5.0.1';
        $data['cp_code'] = $this->user_cp_code;
        $data['login_server_url'] = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];

        $data['rds_user'] = $rds_info_ret['data']['rds_user'];
        $data['rds_id'] = $rds_info_ret['data']['rds_id'];

        $keylock = load_model('sys/OspCryptModel')->get_keylock_string($rds_info_ret['data']['rds_createdate']);
        $rds_info_ret['data']['rds_pass'] =
        load_model('sys/OspCryptModel')->create_aes_decrypt($rds_info_ret['data']['rds_pass'],$keylock);

        $data['rds_pass'] = $rds_info_ret['data']['rds_pass'];
        $data['rds_link'] = $rds_info_ret['data']['rds_link'];
        $data['rds_dbname'] = $rds_info_ret['data']['rds_dbname'];

        $data['login_user_name'] = $new_user['login_user_name'];
        $data['login_password'] = $new_user['login_password'];

        require_model('common/CryptReqModel');
        $obj = new CryptReqModel();
        $req_arr = $obj->create($data);

        $req_arr['app_act'] = 'index/login_by_auth_key';

        $login_url = $pra_serverpath.'?'.http_build_query($req_arr);
        
        return $this->format_ret(1,$login_url,$shop_title);
    }
    
    /*
    * 京东授权—服务平台授权操作
    * @author WangShouChong
    */
    function from_fuwu_jingdong($req){
        $ret = load_model('common/CryptReqModel')->get($req);
        if($ret['status']<0){
            return $ret;
        }
        //分解京东平台授权返回信息
        $req_data = $ret['data'];
        $user_nick = $req_data['user_nick'];        //授权用户对应的京东昵称
        $uid = $req_data['uid'];                //授权用户对应的京东ID
        $access_token = $req_data['access_token'];
        $refresh_token = $req_data['refresh_token'];
        $sale_channel_code = $req_data['sale_channel_code'];
        
        $encode_req_data = load_model('common/CryptModel')->encode(json_encode($ret['data']));
        $ret = $this->get_auth_code_by_shop_user_id($uid);  //京东店铺—运营平台验证
        //如果根据uid找不到对应的授权码，那么让客户手动输入授权码
        if($ret['status']<0){
            //首次授权验证，跳转到授权码向导
            $params = array('app_act'=>'input_auth_key');
            $params['_data'] = $encode_req_data;
            if($sale_channel_code=="jingdong"){
                $params['_sale']="jingdong";
                $params['_token']=$access_token;
            }
            $url = '?'.http_build_query($params);
            return $this->format_ret(-2,$url,$ret['message']);
        }
        //得到授权码继续验证
        $auth_key = $ret['data'];
        $ret = $this->from_fuwu_jingdong_to_efast($req_data,$auth_key,0);
        return $ret;
    }
    
    function from_fuwu_jingdong_to_efast($req_data,$auth_key,$is_allot_new_rds = 0,$new_user = array()){
        $user_nick = $req_data['user_nick'];
        $uid = $req_data['uid'];
        $access_token = $req_data['access_token'];
        $refresh_token = $req_data['refresh_token'];
        $sale_channel_code = $req_data['sale_channel_code'];
        //验证运营平台授权信息
        $ret = $this->chk_auth_code($auth_key);
        if($ret['status']<0){
            return $ret;
        }
        //得到授权跳转地址等相关授权信息字段
        $pra_serverpath = $ret['data']['pra_serverpath'];
        $auth_key_info = $ret['data'];
        $kh_id = $auth_key_info['pra_kh_id'];
        $cp_id = $auth_key_info['pra_cp_id'];
        $pra_id = $auth_key_info['pra_id'];
        $auth_num = $auth_key_info['pra_authnum'];//点数
        //获取客户名称
        $sql = "select kh_name from osp_kehu where kh_id = :kh_id";
        $kh_name = (string)ctx()->db->getOne($sql,array(':kh_id'=>$kh_id));
        if(empty($kh_name)){
            return $this->format_ret(-1,'','找不到客户名称');
        }
        //匹配数据库
        $rds_info_ret = $this->get_rds_info($kh_id);
        if($rds_info_ret['status']<0){
            return $rds_info_ret;
        }
        //标识是否为新库，未初始化的
        if($rds_info_ret['status'] == 2){
            $is_init_sys = 1;
        }else{
            $is_init_sys = 0;
        }
        //新匹配的数据库，跳转到初始化用户帐号信息向导页面
        if(empty($new_user) && $is_init_sys == 1){
            return $this->format_ret(-3,$kh_name,'请提供初始化系统的管理员账号和密码');
        }
        //验证京东店铺是否存在
        $sql = "select count(*) from osp_shangdian where sd_jd_id = :sd_jd_id";
        $c = ctx()->db->getOne($sql,array(':sd_jd_id'=>$uid));
        if($c>0){
            $is_exist_user_nick = 1;
        }else{
            $is_exist_user_nick = 0;
        }
        //匹配获取运营平台—店铺平台信息验证
        $ret = $this->check_active_shop_by_sale_channel_code($sale_channel_code,$pra_id,$cp_id,$kh_id,$is_exist_user_nick);
        if($ret['status']<0){
            return $ret;
        }
        $pt_id = $ret['data']['pt_id'];
        //调用api取 shop_title
        $result = load_model('sys/JingdongModel')->get_api_shop_title($access_token);
        if($result['status']<0){
            return $result;
        }
        $shop_title = $result['data']['shop_name'];
        $remark = $result['data']['vender_id'];// 	商家编号  
        $shop_type = $result['data']['type'];//店铺类型
            
        //准备店铺回流信息
        $info = array('shop_title'=>$shop_title,
            'cp_id'=>$cp_id,
            'pt_id'=>$pt_id,
            'kh_id'=>$kh_id,
            'access_token'=>$access_token,
            'user_nick'=>$user_nick,
            'uid'=>$uid,
            'expires_in'=>$req_data['expires_in']);
        $ret = $this->create_shangdian_auth_jd($info);
        if($ret['status']<0){
            return $ret;
        }
        //同步数据到sysdb
        $ret = load_model('sys/RdsDataModel')->update_kh_data($kh_id,0,'osp_rdsextmanage_db');
        if ($ret['status']<0){
            return $ret;
        }
        
        $params = array('app_act'=>'from_fuwu_jingdong');
        
        $data = array();
        $data['shop_user_nick'] = $user_nick;
        $data['shop_user_jdid'] = $uid;
        $data['sale_channel_code'] = $sale_channel_code;
        $data['shop_title'] = $shop_title;
        $data['access_token'] = $access_token;
        $data['refresh_token'] = $refresh_token;
        $data['expires_in'] = $req_data['expires_in'];
        $data['shop_type'] = $shop_type;
        $data['is_init_sys'] = $is_init_sys;
        //要把 授权KEY和点数 带上
        $data['auth_key'] = $auth_key;
        $data['auth_num'] = $auth_num;
        $data['kh_name'] = $kh_name;
        $data['kh_id'] = $kh_id;
        $data['version'] = 'v5.0.1';
        $data['cp_code'] = $this->user_cp_code;
        $data['login_server_url'] = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];

        $data['rds_user'] = $rds_info_ret['data']['rds_user'];
        $data['rds_id'] = $rds_info_ret['data']['rds_id'];
        
        $keylock = load_model('sys/OspCryptModel')->get_keylock_string($rds_info_ret['data']['rds_createdate']);
        $rds_info_ret['data']['rds_pass'] =
        load_model('sys/OspCryptModel')->create_aes_decrypt($rds_info_ret['data']['rds_pass'],$keylock);

        $data['rds_pass'] = $rds_info_ret['data']['rds_pass'];
        $data['rds_link'] = $rds_info_ret['data']['rds_link'];
        $data['rds_dbname'] = $rds_info_ret['data']['rds_dbname'];

        $data['login_user_name'] = $new_user['login_user_name'];
        $data['login_password'] = $new_user['login_password'];

        require_model('common/CryptReqModel');
        $obj = new CryptReqModel();
        $req_arr = $obj->create($data);

        $req_arr['app_act'] = 'index/login_by_auth_key';

        $login_url = $pra_serverpath.'?'.http_build_query($req_arr);
        return $this->format_ret(1,$login_url,$shop_title);
    }

    function from_fuwu_taobao($req){
        $ret = load_model('common/CryptReqModel')->get($req);
        if($ret['status']<0){
            return $ret;
        }
        //echo '<hr/>$ret<xmp>'.var_export($ret,true).'</xmp>';
        $req_data = $ret['data'];
        $user_nick = $req_data['user_nick'];
        $access_token = $req_data['access_token'];
        $refresh_token = $req_data['refresh_token'];
        $sale_channel_code = $req_data['sale_channel_code'];
        $auth_app_key = $req_data['app_key'];
        $encode_req_data = load_model('common/CryptModel')->encode(json_encode($ret['data']));
        //--test
        //$user_nick = 'shopping_attb';
        //$access_token = '70000100e39971915bb3c7e4a46771843f287f10e8cad6e5def1ef4c1826f2e2e7a8e0258123788';

        $ret = $this->get_auth_code_by_shop_user_nick($user_nick);
        //如果根据user_nick找不到对应的授权码，那么让客户手动输入授权码
        if($ret['status']<0){
            $params = array('app_act'=>'input_auth_key');
            $params['_data'] = $encode_req_data;
            $url = '?'.http_build_query($params);
            return $this->format_ret(-2,$url,$ret['message']);
        }
        $auth_key = $ret['data'];
        $ret = $this->from_fuwu_taobao_to_efast($req_data,$auth_key,0);
        //echo '<hr/>$ret<xmp>'.var_export($ret,true).'</xmp>';die;
        return $ret;
    }
    function from_fuwu_alibaba($req){
      $ret = load_model('common/CryptReqModel')->get($req);
        if($ret['status']<0){
            return $ret;
        }
        //echo '<hr/>$ret<xmp>'.var_export($ret,true).'</xmp>';
        $req_data = $ret['data'];
   
        $key_data = array('access_token','member_id','ali_id','refresh_token','client_id','client_secret');
        $sd_data = array();
        foreach($key_data as $k){
            $sd_data[$k] = isset($req_data[$k])?$req_data[$k]:'';
        }
        $req_data['create_time'] = time();
        
        
        $update_str =  'access_token = VALUES(access_token),refresh_token = VALUES(refresh_token)';
        $this->insert_multi_duplicate('osp_shangdian_alibaba',array($sd_data) , $update_str);
			header('Content-Type: text/html;charset=UTF-8' );
		   echo "<script> alert('授权成功！');location.href='http://operate.baotayun.com:8080/efast365-help/';</script>";
		   die;
        
        //--test
        //$user_nick = 'shopping_attb';
        //$access_token = '70000100e39971915bb3c7e4a46771843f287f10e8cad6e5def1ef4c1826f2e2e7a8e0258123788';

         $sql = "select * from osp_shangdian_alibaba where aliId=:aliId AND client_id=:client_id "       ;
          $ali_shop_data = $this->db->get_row($sql,array(':aliId'=>$req_data['aliId'],':client_id'=>$req_data['client_id']));      
      //  $ret = $this->get_auth_code_by_shop_user_nick($user_nick);
        //如果根据user_nick找不到对应的授权码，那么让客户手动输入授权码
         if (!empty($ali_shop_data)) {
            $url  = $this->db->get_value("select pra_serverpath from osp_productorder_auth where pra_kh_id='{$ali_shop_data['kh_id']}");
            $ret['data']['shop_code'] =  $ali_shop_data['shop_code'];
            $ret['data']['kh_id'] =   $ali_shop_data['kh_id'];
            
            $encode_req_data = load_model('common/CryptModel')->encode(json_encode($ret['data']));
            $params = array('app_act'=>'index/save_auth_shop_info');
            $params['_data'] = $encode_req_data;
            $url = '?'.http_build_query($params);
            return $this->format_ret(-2,$url,$ret['message']);  
            
         }
         
         echo "请先登录系统，创建店铺，并填写memberId，然后在进行授权！";die;
         
    
    }
    
    
    

    
    function act_input_auth_key($req){
        $data_str = load_model('common/CryptModel')->decode($req['_data']);
        $data_arr = json_decode($data_str,true);
        $data_arr['auth_key'] = $req['auth_key'];
        if(!empty($req['login_user_name']) && !empty($req['login_password'])){
            $new_user = array('login_user_name'=>$req['login_user_name'],'login_password'=>$req['login_password']);
        }else{
            $new_user = array();
        }
        //$ret = $this->from_fuwu_taobao_to_efast($data_arr,$req['auth_key'],1,$new_user);
        
        $method='from_fuwu_'.$data_arr['sale_channel_code'].'_to_efast';
        $ret = $this->$method($data_arr,$req['auth_key'],1,$new_user);
        return $ret;
    }

    function from_fuwu_taobao_to_efast($req_data,$auth_key,$is_allot_new_rds = 0,$new_user = array()){
        $user_nick = $req_data['user_nick'];
        $access_token = $req_data['access_token'];
        $refresh_token = $req_data['refresh_token'];
        $sale_channel_code = $req_data['sale_channel_code'];
        //验证运营平台授权信息
        $ret = $this->chk_auth_code($auth_key);
        if($ret['status']<0){
            return $ret;
        }

        $pra_serverpath = $ret['data']['pra_serverpath'];
        //echo '<hr/>$pra_serverpath<xmp>'.var_export($ret,true).'</xmp>';die;

        //主要取 pra_kh_id  pra_cp_id pra_id
        $auth_key_info = $ret['data'];
        $kh_id = $auth_key_info['pra_kh_id'];
        $cp_id = $auth_key_info['pra_cp_id'];
        $pra_id = $auth_key_info['pra_id'];
        $cp_area = $auth_key_info['cp_area'];
        $auth_num = $auth_key_info['pra_authnum'];//点数
        $pra_app_key = $auth_key_info['pra_app_key'];//点数
        $this->kh_app_key = $pra_app_key;

        $sql = "select kh_name from osp_kehu where kh_id = :kh_id";
        $kh_name = (string)ctx()->db->getOne($sql,array(':kh_id'=>$kh_id));
        if(empty($kh_name)){
            return $this->format_ret(-1,'','找不到客户名称');
        }

        //vm rds 绑定
        /*
        $ret = $this->get_vm_info($kh_id);
        if($ret['status']<0){
            return $ret;
        }
        $vm_info = $ret['data'];
        $host_id = $vm_info['host_id'];
        $pra_serverpath = $vm_info['pra_serverpath'];
        */

        $rds_info_ret = $this->get_rds_info($kh_id);
        //echo '<hr/>$rds_info_ret<xmp>'.var_export($rds_info_ret,true).'</xmp>';die;
        if($rds_info_ret['status']<0){
            return $rds_info_ret;
        }
        if($rds_info_ret['status'] == 2){
            $is_init_sys = 1;
        }else{
            $is_init_sys = 0;
        }
        

        //设置推送 
        $this->set_jdp_add($rds_info_ret['data']['rds_name'],$access_token);
        

        if(empty($new_user) && $is_init_sys == 1){
            return $this->format_ret(-3,$kh_name,'请提供初始化系统的管理员账号和密码');
        }
        //---

        $sql = "select count(*) from osp_shangdian where sd_nick = :sd_nick";
        $c = ctx()->db->getOne($sql,array(':sd_nick'=>$user_nick));
        if($c>0){
            $is_exist_user_nick = 1;
        }else{
            $is_exist_user_nick = 0;
        }

        $ret = $this->check_active_shop_by_sale_channel_code($sale_channel_code,$pra_id,$cp_id,$kh_id,$is_exist_user_nick);
        if($ret['status']<0){
            return $ret;
        }
        $pt_id = $ret['data']['pt_id'];

        //调用api取 shop_title shop_type
        $result = $this->get_api_shop_title($user_nick,$access_token);
        if($result['status']<0){
            return $result;
        }
        $shop_title = $result['data']['shop_title'];
        $shop_type = $result['data']['shop_type'];

        $info = array('shop_title'=>$shop_title,
                    'cp_id'=>$cp_id,
                    'pt_id'=>$pt_id,
                    'kh_id'=>$kh_id,
                    'access_token'=>$access_token,
                    'user_nick'=>$user_nick,
                    'expires_in'=>$req_data['expires_in']);
        $ret = $this->create_shangdian_auth($info);
        if($ret['status']<0){
            return $ret;
        }
        
        
        
        
        //同步数据到sysdb
        $ret = load_model('sys/RdsDataModel')->update_kh_data($kh_id,0,'osp_rdsextmanage_db');
        if ($ret['status']<0){
	        return $ret;
        }
        //echo "update_kh_data";die;

        $params = array('app_act'=>'from_fuwu_taobao');

        $data = array();
        $data['shop_user_nick'] = $user_nick;
        $data['shop_title'] = $shop_title;
        $data['sale_channel_code'] = $sale_channel_code;
        $data['access_token'] = $access_token;
        $data['refresh_token'] = $refresh_token;
        $data['expires_in'] = $req_data['expires_in'];
        $data['shop_type'] = $shop_type;
        $data['is_init_sys'] = $is_init_sys;
        //要把 授权KEY和点数 带上
        $data['auth_key'] = $auth_key;
        $data['auth_num'] = $auth_num;
        $data['kh_name'] = $kh_name;
        $data['kh_id'] = $kh_id;
        $data['version'] = 'v5.0.1';
        $data['app_key'] = $pra_app_key;
        $data['cp_area'] = $cp_area;

        $data['login_server_url'] = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];

        $data['rds_user'] = $rds_info_ret['data']['rds_user'];
        $data['rds_id'] = $rds_info_ret['data']['rds_id'];

        $keylock = load_model('sys/OspCryptModel')->get_keylock_string($rds_info_ret['data']['rds_createdate']);
        $rds_info_ret['data']['rds_pass'] =
        load_model('sys/OspCryptModel')->create_aes_decrypt($rds_info_ret['data']['rds_pass'],$keylock);

        $data['rds_pass'] = $rds_info_ret['data']['rds_pass'];
        $data['rds_link'] = $rds_info_ret['data']['rds_link'];
        $data['rds_dbname'] = $rds_info_ret['data']['rds_dbname'];

        $data['login_user_name'] = $new_user['login_user_name'];
        $data['login_password'] = $new_user['login_password'];

        require_model('common/CryptReqModel');
        $obj = new CryptReqModel();
        $req_arr = $obj->create($data);

        $req_arr['app_act'] = 'index/login_by_auth_key';
        /*
        echo '<hr/>$data<xmp>'.var_export($data,true).'</xmp>';
        echo '<hr/>$req_arr<xmp>'.var_export($req_arr,true).'</xmp>';
        die;*/

	    //表示efast赢家
	    if ($cp_id == 24) {
		    return $this->format_ret(2,'',$shop_title);
	    }

        $login_url = $pra_serverpath.'?'.http_build_query($req_arr);
        //echo $login_url;die;
        return $this->format_ret(1,$login_url,$shop_title);
    }

    //EFAST回调初始化系统完成
    function set_init_sys_end($req){
        $ret = load_model('common/CryptReqModel')->get($req);
        if($ret['status']<0){
            return $ret;
        }
        //echo '<hr/>$ret<xmp>'.var_export($ret,true).'</xmp>';
        $req_data = $ret['data'];
        $auth_key = $req_data['auth_key'];
        $ret = $this->chk_auth_code($auth_key);
        if($ret['status']<0){
            return $ret;
        }
        $kh_id = $ret['data']['pra_kh_id'];
        $sql = "UPDATE osp_rdsextmanage_db r1,osp_rdsextmanage r2
                SET r1.rem_db_bindtype = 1,r1.rem_db_is_bindkh = 1,r1.rem_db_bindtype=1
                WHERE
                    r1.rem_db_pid = r2.rem_rds_id
                AND r1.rem_db_is_bindkh = 1
                AND r1.rem_db_khid = :kh_id";
        $ret = ctx()->db->query($sql,array(':kh_id'=>$kh_id));
        if($ret === true){
            return $this->format_ret(1);
        }else{
            return $this->format_ret(-1,'','更新数据库失败');
        }
    }
    
    //获取运营平台产品维护标志
    function  get_maintain(){
        $sql_main = "select cp_maintain from osp_chanpin where cp_id=21";
        $cp_maintain = $this->db->get_value($sql_main);
        return $cp_maintain;
    }
    
    //获取运营平台产品实时公告信息
    function  get_maintain_info(){
        $sql_main = "select * from osp_notice where not_sh='1' and not_cp_id='21' and not_enddate>'".date('Y-m-d H:i:s')."' order by not_createdate desc";
        $data = $this->db->get_row($sql_main);
        return $data;
    }
    
    function get_app_key_by_customer_name($customer_name){
        $sql = "select a.pra_app_key from osp_kehu   k
            INNER JOIN osp_productorder_auth a ON a.pra_kh_id= k.kh_id
            where k.kh_name=:kh_name ";
        $app_key =  $this->db->get_value($sql,array(':kh_name'=>$customer_name));
        return $this->format_ret(1,$app_key);
        
    }
    
    
 private function get_real_ip() {
        $ip = false;
        if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
            if ($ip) {
                array_unshift($ips, $ip);
                $ip = FALSE;
            }
            for ($i = 0; $i < count($ips); $i++) {
                if (!preg_match("/^(10|172\.16|192\.168)\./", $ips[$i])) {
                    $ip = $ips[$i];
                    break;
                }
            }
        }
        return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
    }
    
    
    
}
