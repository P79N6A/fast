<?php

/**
 * 商店 授权 相关业务
 *
 */
require_model('tb/TbModel');
require_lang('base');

class ShopAuthModel extends TbModel {

    //des 密钥
    private $secret = '4rXm3QQW30Y4EkVEiuJ5CapgAG$xXsh$_';
    public $app_info;

    public function __construct() {
        parent::__construct();
        $this->app_info['taobao'] = array('app_key' => '12651526', 'app_secret' => '11b9128693bfb83d095ad559f98f2b07');
        $this->app_info['jingdong'] = array('app_key' => 'C0C9110ED2D32E13266D4522D55C78AF', 'app_secret' => 'f58736a321bd44fabf2253f5d25344f0');
    }

    function get_params_state($shop_code) {
        $state_arr = array();
        $state_arr['shop_code'] = $shop_code;
        $state_arr['efast_url'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
        $sql = "select value from sys_auth where code = 'login_server_url'";
        $login_server_url = ctx()->db->getOne($sql);
        if (empty($login_server_url)) {
            return $this->format_ret(-1, '', '没有找到登录服务器的URL');
        }
        $state_arr['login_server_url'] = $login_server_url;
        $sql = "select value from sys_auth where code = 'auth_key'";
        $auth_key = ctx()->db->getOne($sql);
        if (empty($auth_key)) {
            return $this->format_ret(-1, '', '没有找到授权产品密钥');
        }
        /*
          require_model('common/CryptModel');
          $obj = new CryptModel('上海百胜',1);
          $auth_key = $obj->decode($auth_key); */
        $state_arr['auth_key'] = $auth_key;
        //echo '<hr/>state_arr<xmp>'.var_export($state_arr,true).'</xmp>';die;
        $state = 'from_efast;' . base64_encode(json_encode($state_arr));
        return $this->format_ret(1, $state);
    }

    function get_params_state_new($shop_code) {
        $state_arr = array();
        $state_arr['shop_code'] = $shop_code;
        $state_arr['efast_url'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];

        $sql = "select value from sys_auth where code = 'auth_key'";
        $auth_key = ctx()->db->getOne($sql);
        if (empty($auth_key)) {
            return $this->format_ret(-1, '', '没有找到授权产品密钥');
        }
        $state_arr['auth_key'] = $auth_key;
        $state_arr['kh_id'] = CTX()->saas->get_saas_key();
        $state = 'f_e365:' . base64_encode(json_encode($state_arr));
        return $this->format_ret(1, $state);
    }

    function auth_shop($shop_id) {
        $sql = "select sale_channel_code,shop_code from base_shop where shop_id = :shop_id";
        $row = ctx()->db->get_row($sql, array('shop_id' => $shop_id));
        if (empty($row)) {
            return $this->format_ret(-1, '', '店铺不存在');
        }
        $sale_channel_code = $row['sale_channel_code'];
        $shop_code = $row['shop_code'];
        $method = "auth_" . $sale_channel_code;

        $ret = $this->$method($shop_code);
        return $ret;
    }

    function auth_taobao($shop_code) {
        $ret = $this->get_params_state($shop_code);
        if ($ret['status'] < 0) {
            return $ret;
        }


        $auth_arr = load_model('sys/SysAuthModel')->get_auth();
        
     
        $app_str = $this->db->get_value("select api from base_shop_api where shop_code=:shop_code",array(':shop_code'=>$shop_code));
        if(!empty($app_str)){
            $app_data = json_decode($app_str, true);
            $app_key  = $app_data['app_key'];
        }    
        $app_key = empty($app_key) ? $auth_arr['app_key'] : $app_key;
        $app_info = require_conf('sys/app_info');

        $cur_app_info = $app_info["taobao"][$app_key];
        $state = $ret['data'];
        $url = "https://oauth.taobao.com/authorize";
        $req_arr = array();
        $req_arr['response_type'] = 'code';
        $req_arr['client_id'] = $cur_app_info['app_key'];
        $req_arr['state'] = $state;

        $api_url_conf = require_conf('api_url');

        // $req_arr['redirect_uri'] = 'http://218.242.57.204:8081/efast_bz/taobao_session/authorize_callback.php';
        $req_arr['redirect_uri'] = $api_url_conf['auth_taobao_callback'][$app_key];


        $url .= '?' . http_build_query($req_arr);
        return $this->format_ret(1, $url);
    }

    function auth_jingdong($shop_code) {
        $ret = $this->get_params_state($shop_code);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $sql = "select * from base_shop_api where shop_code = :shop_code";
        $row = $this->db->get_row($sql, array(':shop_code' => $shop_code));
        $data = json_decode($row['api'], true);
        $app_key = isset($data['app_key']) && !empty($data['app_key']) ? $data['app_key'] : '6789E62BD86407B9E74143EBC524D1EB';
        $app_info = require_conf('sys/app_info');

        $cur_app_info = $app_info["jingdong"][$app_key];


        $callback = $app_info["jingdong"][$app_key]['callback'];
        $state = $ret['data'];
        $url = "";
        if ($cur_app_info['app_key'] == 'C0C9110ED2D32E13266D4522D55C78AF') {
            $url = "http://218.242.57.204:8081/jdapi.php?key=C0C9110ED2D32E13266D4522D55C78AF";
        } else {
            $url = "https://oauth.jd.com/oauth/authorize";
            $param['response_type'] = 'code';
            $param['redirect_uri'] = $callback;
            $param['state'] = $state;
            $param['client_id'] = $cur_app_info['app_key'];
            $url = $url . '?' . http_build_query($param);
        }
        //var_dump(   $data);
        return $this->format_ret(1, $url);
    }

    function auth_yihaodian($shop_code) {
        $ret = $this->get_params_state_new($shop_code);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $app_info = require_conf('sys/app_info');
        $url = " https://member.yhd.com/login/authorize.do";

        $param['response_type'] = 'code';
        $param['redirect_uri'] = 'http://login.baotayun.com/session/yhd/authorize_callback.php';
        $param['client_id'] = $app_info['yihaodian']['app_key'];
        $param['state'] = $ret['data'];
        $url = $url . '?' . http_build_query($param);
        //$url = 'http://login.baotayun.com/session/yhd/tt.php'. '?' . http_build_query($param); //测试
        //echo $url;die;
        return $this->format_ret(1, $url);
    }

    function auth_suning($shop_code) {
        $ret = $this->get_params_state_new($shop_code);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $app_info = require_conf('sys/app_info');
        $url = "http://open.suning.com/api/oauth/authorize";

        $param['response_type'] = 'code';
        $param['itemcode'] = '002';

        $param['redirect_uri'] = 'http://login.baotayun.com/session/sun_session/authorize_callback.php';
        $param['client_id'] = $app_info['suning']['app_key'];
        $param['state'] = $ret['data'];
        $url = $url . '?' . http_build_query($param);



        return $this->format_ret(1, $url);
    }

    function save_auth_shop_info($request) {
        if(isset( $request['state'])){
              $state_str = $request['state'];
              $state_data = json_decode(base64_decode($state_str), true);
        }else{
            $state_data = $request;
        }
      


        $shop_code = $state_data['shop_code'];
        $sql = "select * from base_shop where shop_code = :shop_code";
        $shop_info = $this->db->get_row($sql, array(':shop_code' => $shop_code));
        if (empty($shop_info)) {
            return $this->format_ret(-1, '', $shop_code . ' 的店铺代码不存在');
        }

        $app_info = require_conf('sys/app_info');
        $app_param = isset($app_info[$shop_info['sale_channel_code']])?$app_info[$shop_info['sale_channel_code']]:array();

        $app_func = 'get_' . $shop_info['sale_channel_code'] . '_app_info';
        $app_param_data = $this->$app_func($request, $app_param);



        $data['shop_code'] = $shop_code;
        $data['kh_id'] = CTX()->saas->get_saas_key();
        $data['source'] = $shop_info['sale_channel_code'];
        $data['api'] = json_encode($app_param_data);
        $update_str = " api = VALUES(api) ;";
        $this->insert_multi_duplicate('base_shop_api', array($data), $update_str);
        load_model('sys/security/SysEncrypModel')->create_shop_encrypt($shop_code);


        $u_data['authorize_state'] = 1;
        $time = time();
        $time_str = '2030-01-01 00:00:00';
        if (isset($u_data['expires_in']) && $u_data['expires_in'] > $time) {
            $time = $u_data['expires_in'];
            $time_str = date('Y-m-d H:i:s', $time);
        }

        $u_data['authorize_date'] = $time_str;
        $this->update_exp('base_shop', $u_data, " shop_code='{$shop_code}'");

        return $this->format_ret(1, '', $shop_info['shop_name'] . ' 授权成功');
    }

    function get_yihaodian_app_info($request, $app_param) {
        $app_param['session'] = $request['session'];
        $app_param['nick'] = $request['nick'];
        $app_param['refreshToken'] = $request['refreshToken'];
        return $app_param;
    }

    function get_alibaba_app_info($request, $app_param) {
        $app_param['app_key'] = $request['client_id'];
        $app_param['app_secret'] = $request['client_secret'];
        $app_param['access_token'] = $request['access_token'];
        $app_param['refresh_token'] = $request['refresh_token'];
        $app_param['memberId'] = $request['memberId'];

    //    {"aliId":"8888888888","resource_owner":"xxx","memberId":"xxxxxxx","expires_in":"36000","refresh_token":"479f9564-1049-456e-ab62-29d3e82277d9","access_token":"f14da3b8-b0b1-4f73-a5de-9bed637e0188","refresh_token_timeout":"20121222222222+0800"} 
        return $app_param;
    }
    function get_suning_app_info($request, $app_param) {
        $app_param['session'] = $request['session'];
        $app_param['suning_user_name'] = $request['suning_user_name'];
        $app_param['appKey'] = $app_param['app_key'];
        $app_param['appSecret'] = $app_param['app_secret'];
        $app_param['refresh_token'] = $request['refresh_token'];
        $app_param['kh_id'] = CTX()->saas->get_saas_key();
        $app_param['access_token'] = $request['session'];

        unset($app_param['app_key']);
        unset($app_param['app_secret']);

        return $app_param;
    }

    function save_auth_shop($request) {
        $ret = load_model('common/CryptReqModel')->get($request);

        if ($ret['status'] < 0) {
            return $ret;
        }
        //echo '<hr/>$ret<xmp>'.var_export($ret,true).'</xmp>';die;
        $data = $ret['data'];
        $shop_code = $data['shop_code'];
        $sale_channel_code = $data['sale_channel_code'];
        $sql = "select shop_name from base_shop where shop_code = :shop_code";
        $shop_name = ctx()->db->getOne($sql, array(':shop_code' => $shop_code));
        if (empty($shop_name)) {
            return $this->format_ret(-1, '', $shop_code . ' 的店铺代码不存在');
        }
        $_t = $data['expires_in'] > time() ? $data['expires_in'] : time() + $data['expires_in'];
        $data['expires_in'] = date('Y-m-d H:i:s', $_t);

        $tb_shop_type = $data['shop_type'];

        // $cur_app_info = @$this->app_info[$sale_channel_code];
        $app_info = require_conf('sys/app_info');
        //使用翼商的KEY

        if ($sale_channel_code == 'taobao') {
            $cur_app_info = $app_info['taobao'][$data['app_key']];
        } else if ($sale_channel_code == 'jingdong') {
            //默认为翼商
            // $jd_app_key = 'A66FE28F89F8B5AAD5D3CDB165097A55';
            $jd_app_key = $data['app_key'];

            $cur_app_info = $app_info['jingdong'][$jd_app_key];
        } else {
            $cur_app_info = isset($app_info[$sale_channel_code]) ? $app_info[$sale_channel_code] : array();
        }



        if (empty($cur_app_info)) {
            return $this->format_ret(-1, '', $sale_channel_code . ' 缺少appkey配置');
        }
        //echo '<hr/>$arr<xmp>'.var_export($cur_app_info,true).'</xmp>';

        $api_arr = array('app_key' => $cur_app_info['app_key'], 'app_secret' => $cur_app_info['app_secret']);
        $api_arr['session'] = $data['access_token'];
        $api_arr['refresh_token'] = $data['refresh_token'];
        $api_arr['expires_in'] = $data['expires_in'];
        $api_arr['nick'] = $data['shop_user_nick'];
        $api_arr['shop_type'] = $tb_shop_type;
        $api_arr['type'] = $tb_shop_type;

        $api = json_encode($api_arr);

        $up_row = array();
        $up_row['shop_code'] = $shop_code;
        $up_row['source'] = $sale_channel_code;
        $up_row['api'] = $api;
        $up_row['tb_shop_type'] = $tb_shop_type;

        $up_row['nick'] = $api_arr['nick'];
        $up_row['app_key'] = $cur_app_info['app_key'];
        $up_row['app_secret'] = $cur_app_info['app_secret'];
        $up_row['session_key'] = $api_arr['session'];
        $up_row['kh_id'] = isset($data['kh_id']) ? $data['kh_id'] : '0';
        $up_row['remark'] = isset($data['remark']) ? $data['remark'] : '';

        $sql_nick = "select shop_code from base_shop_api where nick = :nick AND source=:source ";
        $shop_data = CTX()->db->get_row($sql_nick, array(':nick' => $api_arr['nick'], ':source' => $sale_channel_code));
        if (!empty($shop_data)) {
            if (!empty($shop_data['shop_code']) && $shop_data['shop_code'] != $shop_code) {
                return $this->format_ret(-1, '', " 授权保存异常(昵称：{$api_arr['nick']})，与已经存在店铺({$shop_code})授权信息相同!");
            }
        }


        $sql = "select * from base_shop_api where shop_code = :shop_code AND source=:source";
        $db_shop_api = ctx()->db->get_row($sql, array(':shop_code' => $shop_code, ':source' => $sale_channel_code));
        // var_dump($up_row,$api_arr);
        $on_dup_up_arr = array('api=values(api)', 'session_key=values(session_key)');
        if (!empty($db_shop_api)) {
            $shop_api_nick = trim($db_shop_api['nick']);
            $api_nick = !empty($up_row['nick']) ? $up_row['nick'] : $up_row['shop_title'];
            if (!empty($shop_api_nick) && $shop_api_nick != $api_nick) {
                return $this->format_ret(-1, '', $shop_name . " 授权失败:授权店铺昵称({$api_nick})不是原店铺的昵称({$shop_api_nick})");
            }

            $chk_fld_arr = explode(',', 'nick,app_key,app_secret,kh_id');
            foreach ($chk_fld_arr as $_fld) {
                if (empty($db_shop_api[$_fld])) {
                    $on_dup_up_arr[] = "{$_fld}=values({$_fld})";
                }
            }
        }
        $on_dup_up = join(',', $on_dup_up_arr);
        //京东青龙参数
        if (!empty($db_shop_api) && $sale_channel_code == 'jingdong') {
            $api_data = json_decode($db_shop_api['api'], true);
            $api_arr['customerCode'] = isset($api_data['customerCode']) ? $api_data['customerCode'] : '';
            $api = json_encode($api_arr);
            $up_row['api'] = $api;
        }
        //echo '<hr/>$on_dup_up_arr<xmp>'.var_export($on_dup_up_arr,true).'</xmp>';
        $ret = M('base_shop_api')->insert_multi_duplicate('base_shop_api', array($up_row), $on_dup_up);
        if ($ret['status'] < 0) {
            return $this->format_ret(-1, '', $shop_name . ' 授权失败,' . $ret['message']);
        }
        //填入运营平台参数
        $shop_params = get_array_vars($up_row, array(
            'shop_code',
            'source',
            'api',
            'tb_shop_type',
            'nick',
            'kh_id',
            'remark',
        ));
        $shop_params['shop_name'] = $shop_name;
        $up_row = array();
        $up_row['authorize_state'] = 1;
        $up_row['authorize_date'] = $data['expires_in'];
        $up_row['shop_user_nick'] = $data['shop_user_nick'];
        $ret = M('base_shop')->update($up_row, array('shop_code' => $shop_code));
        //echo '<hr/>up_row222<xmp>'.var_export($up_row,true).'</xmp>';
        if ($ret['status'] < 0) {
            return $this->format_ret(-1, '', $shop_name . ' 授权失败,' . $ret['message']);
        }
        $shop_params['authorize_state'] = $up_row['authorize_state'];
        $shop_params['authorize_date'] = $up_row['authorize_date'];
        if ($sale_channel_code == 'taobao') {
            //最后1次授权时间
            $auth_arr['code'] = 'auth_shop_time';
            $auth_arr['name'] = CTX()->get_session('user_code', true);
            $auth_arr['value'] = time();
            $upstr = " value= VALUES(value),name= VALUES(name) ";
            $this->insert_multi_duplicate('sys_auth', array($auth_arr), $upstr);
            $ret = load_model('sys/sysServerModel')->osp_server('shop.value.list', array($shop_params));
            if ($ret['status'] != 1) {
                return $this->format_ret(-1, '', '授权信息填入运营平台失败,' . $ret['message']);
            }
        }
        //添加系统日志
        $module = '网络店铺'; //模块名称
        $operate_type = '授权'; //操作类型
        $log_xq = '网络店铺:' . $shop_name . '完成授权;';
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module,  'operate_type' => $operate_type, 'operate_xq' => $log_xq);
        load_model('sys/OperateLogModel')->insert($log);
        return $this->format_ret(1, '', $shop_name . ' 授权成功');
    }

    function auth_zhe800() {
        $app_info = require_conf('sys/app_info');
        $url = $app_info['zhe800']['auth_url'];
        $param['response_type'] = 'code';
        $param['redirect_uri'] = $app_info['zhe800']['callback'];
        $param['client_id'] = $app_info['zhe800']['client_id'];
        $param['state'] = time();
        $url = $url . '?' . http_build_query($param);
        return $this->format_ret(1, $url);
    }

    function auth_renrendian() {
        $app_info = require_conf('sys/app_info');
        $url = $app_info['renrendian']['auth_url'];
        $param['response_type'] = 'code';
        $param['redirect_uri'] = $app_info['renrendian']['callback'];
        $param['appid'] = $app_info['renrendian']['app_id'];
        $url = $url . '?' . http_build_query($param);
        return $this->format_ret(1, $url);
    }

    function auth_kaola() {
        $app_info = require_conf('sys/app_info');
        $url = $app_info['kaola']['auth_url'];
        $param['client_id'] = $app_info['kaola']['client_id'];
        $param['response_type'] = 'code';
        $param['state'] = 1212;
        $param['type'] = 101;
        $param['redirect_uri'] = $app_info['kaola']['callback'];
        $url = $url . '?' . http_build_query($param);
        return $this->format_ret(1, $url);
    }

    function auth_vdian() {
        $app_info = require_conf('sys/app_info');
        $url = $app_info['vdian']['auth_url'];
        $param['appkey'] = $app_info['vdian']['app_key'];
        $param['state'] = time();
        $param['response_type'] = 'code';
        $param['redirect_uri'] = $app_info['vdian']['callback'];
        $url = $url . '?' . http_build_query($param);
        return $this->format_ret(1, $url);
    }

    function auth_aliexpress($request) {
        $sql = "SELECT api FROM base_shop_api WHERE shop_code=:shop_code";
        $sql_value[':shop_code'] = $request['shop_code'];
        $api = $this->db->get_value($sql, $sql_value);
        $shop_api = json_decode($api, true);
        $request['app_key'] = $request['app_key'] == '机密参数，暂不显示' ? $shop_api['app_key'] : $request['app_key'];
        $request['app_secret'] = $request['app_secret'] == '机密参数，暂不显示' ? $shop_api['app_secret'] : $request['app_secret'];

        if ($request['app_key'] == '24683750') {//新接口
            $url = 'http://login.baotayun.com/session_app/aliexpress_app/';
            return $this->format_ret(1, $url);
        }

        $state = $this->get_ali_state($request);
        $app_info = require_conf('sys/app_info');
        $url = $app_info['aliexpress']['auth_url'];
        $params['client_id'] = $request["app_key"];
        $params['state'] = $state;
        $params['site'] = 'aliexpress';
        $params['redirect_uri'] = $app_info['aliexpress']['callback'];
        $params['_aop_signature'] = $this->aliexpress_get_sign($params, $request["app_secret"]);
        $url = $url . '?' . http_build_query($params);
        return $this->format_ret(1, $url);
    }

    function aliexpress_get_sign($params, $secret) {
        ksort($params);
        $sign_text = '';
        if ($params) {
            foreach ($params as $key => $value) {
                if ($value) {
                    $sign_text .= $key . $value;
                }
            }
        }
        return strtoupper(bin2hex(hash_hmac("sha1", $sign_text, $secret, true)));
    }

    function auth_weimob($request) {
        $app_info = require_conf('sys/app_info');
        $url = $app_info['weimob']['auth_url'];
        // $url .= '&appid=' . $request['AppId'] . '&secret=' . $request['AppSecret'];
        $params = array();
        $params['enter'] = 'wm';
        $params['client_id'] = $app_info['weimob']['client_id'];
        $params['view'] = 'pc';
        $params['response_type'] = 'code';
        $params['scope'] = 'default';
        $params['redirect_uri'] = $app_info['weimob']['callback'];
        $params['state'] = time();
        $url = $url . '?' . http_build_query($params);
        return $this->format_ret(1, $url);
    }

    function auth_zouxiu($request) {
        $sql = "SELECT api FROM base_shop_api WHERE shop_code=:shop_code";
        $sql_value[':shop_code'] = $request['shop_code'];
        $api = $this->db->get_value($sql, $sql_value);
        $shop_api = json_decode($api, true);
        $request['username'] = $request['username'] == '机密参数，暂不显示' ? $shop_api['username'] : $request['username'];
        $request['password'] = $request['password'] == '机密参数，暂不显示' ? $shop_api['password'] : $request['password'];

        $app_info = require_conf('sys/app_info');
        //$timestamp = date('YmdHis');
        $url = $app_info['zouxiu']['auth_url'];
        $url .= '?uid=' . $request['uid'] . '&username=' . $request['username'] . '&password=' . $request['password'];
        return $this->format_ret(1, $url);
    }
    
    function auth_dajiashequ($request) {
        $app_info = require_conf('sys/app_info');
        $url = $app_info['dajiashequ']['callback'];
        $param['username'] = $request['username'];
        $param['password'] = $request['password'];
        $param['customeID'] = substr($request['companyID'], 0, strlen($request['companyID']) - 5);
        $url = $url . '?' . http_build_query($param);
        return $this->format_ret(1, $url);
    }
    
    function auth_chuizhicai($request){
        $state = $this->get_chuizhicai_state($request);

        $app_info = require_conf('sys/app_info');
        $url = $app_info['chuizhicai']['auth_url'];
        $param['client_id'] = $request['AppKey'];
        $param['client_secret'] = $request['AppSecret'];
        $param['state'] = $state;
        $param['response_type'] = 'code';
        $param['scope'] = 'read';
        $param['redirect_uri'] = 'http://login.baotayun.com/session/chuizhicai/authorize_callback.php';
        $url = $url . '?' . http_build_query($param);
        return $this->format_ret(1, $url);
    }
    
    function get_chuizhicai_state($request) {
        $param = array();
        $param['client_id'] = $request['AppKey'];
        $param['client_secret'] = $request['AppSecret'];
        $state = 'f_e365:' . base64_encode(json_encode($param));
        return $state;
    }

    function auth_alibaba($request) {
        $sql = "SELECT api FROM base_shop_api WHERE shop_code=:shop_code";
        $sql_value[':shop_code'] = $request['shop_code'];
        $api = $this->db->get_value($sql, $sql_value);
        $shop_api = json_decode($api, true);
        $request['app_key'] = $request['app_key'] == '机密参数，暂不显示' ? $shop_api['app_key'] : $request['app_key'];
        $request['app_secret'] = $request['app_secret'] == '机密参数，暂不显示' ? $shop_api['app_secret'] : $request['app_secret'];

        $state = $this->get_ali_state($request);
        $app_info = require_conf('sys/app_info');
        $url = $app_info['alibaba']['auth_url'];
        $param['client_id'] = $request['app_key'];
        $param['state'] = $state;
        $param['redirect_uri'] = $app_info['alibaba']['callback'];

        if ($param['client_id'] == '6137467') {//新授权
            $url = $app_info['alibaba']['new_auth_url'];
            $param['site'] = '1688';
            $url .= '?' . http_build_query($param);
            return $this->format_ret(1, $url);
        }

        $param['site'] = 'china';
        $param['_aop_signature'] = $this->get_alibaba_sign($param, $request['app_secret']);
        $url = $url . '?' . http_build_query($param);
        return $this->format_ret(1, $url);
    }
    
    function get_ali_state($request) {
        $param = array();
        $param['client_id'] = $request['app_key'];
        $param['client_secret'] = $request['app_secret'];
        $state = 'f_e365:' . base64_encode(json_encode($param));
        return $state;
    }
    
    function get_alibaba_sign($param, $client_secret){

       ksort($param);
       $str = '';
       foreach ($param as $key => $value) {
           $sign_str .= $key . $value;
       }
       return strtoupper(bin2hex(hash_hmac('sha1', $sign_str, $client_secret, TRUE)));
    }
    
    function auth_youzan(){
        $app_info = require_conf('sys/app_info');
        $url = $app_info['youzan']['auth_url'];
        $param['client_id'] = $app_info['youzan']['client_id'];
        $param['response_type'] = 'code';
        $param['state'] = time();
        $param['redirect_uri'] = $app_info['youzan']['callback'];
        $url = $url . '?' . http_build_query($param);
        return $this->format_ret(1, $url);
    }

    /**
     * 拼多多授权
     * @return array
     */
    function auth_pinduoduo(){
        $app_info = require_conf('sys/app_info');
        $url = $app_info['pinduoduo']['auth_url'];
        $param['client_id'] = $app_info['pinduoduo']['client_id'];
        $param['response_type'] = 'code';
        $param['state'] = time();
        $param['redirect_uri'] = $app_info['pinduoduo']['callback'];
        $url = $url . '?' . http_build_query($param);
        return $this->format_ret(1, $url);
    }
    
    function auth_ebay($request) {
        $url = 'https://signin.ebay.com/ws/eBayISAPI.dll?SignIn';
        $api_params = array();
        $api_params['shop_code'] = $request['shop_code'];
        $result = load_model('sys/EfastApiModel')->request_api('ebay_api/get_auth', $api_params);
        if(isset($result['resp_data']['code']) && $result['resp_data']['code'] != 0) {
            return $this->format_ret(-1, '', $result['resp_data']['msg']);
        }
        $r = $this->handle_api_params($request['shop_code'], $result['resp_data'], 'SessionID');
        if($r['status'] != 1) {
            return $r;
        }
        $login_param = array();
        $login_param['runame'] = $request['RuName'];
        $login_param['SessID'] = $result['resp_data'];
        $url = $url . '&' . http_build_query($login_param);
        return $this->format_ret(1, $url, '获取SessionID成功，已自动保存，请在新打开的浏览器窗口中登陆授权！');
    }

    function get_ebay_auth($request) {
        $api_params = array();
        $api_params['shop_code'] = $request['shop_code'];
        $result = load_model('sys/EfastApiModel')->request_api('ebay_api/get_token', $api_params);
        if(isset($result['resp_data']['code']) && $result['resp_data']['code'] != 0) {
            return $this->format_ret(-1, '', $result['resp_data']['msg']);
        }
        $ret = $this->handle_api_params($request['shop_code'], $result['resp_data'], 'eBayAuthToken');
        return $this->format_ret(1, '', '获取eBayAuthToken成功，已自动保存，请点击提交进行授权！');
    }

    function handle_api_params($shop_code, $api_param, $key) {
        $row = $this->db->get_row("SELECT shop_api_id,api FROM base_shop_api WHERE shop_code=:shop_code", array(":shop_code" => $shop_code));
        $params = array();
        if(!empty($row)){
            if(!empty($row['api'])){
                $params = json_decode($row['api'],true);
            }
        }
        $params[$key] = $api_param;
        return $this->update_exp('base_shop_api', array('api'=>  json_encode($params)), array('shop_code'=>$shop_code));
    }
}
