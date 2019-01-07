<?php
/**
 * 管理企业级SESSION
 */
require_lib ( 'util/web_util', true );
class session {
    public function __construct() {
        //parent::__construct();
        $this->mdl = load_model('sys/RdsModel_ex');
    }
    
    /**
     * 系统参数列表
     */
    public function index(array & $request, array & $response,  array & $app){
        $app['title'] = '企业SESSION列表';
        $response['action_url'] = '?app_act=sys/session/get_sessionkey';
        $search = isset($request['search']) ? $request['search'] : array();
        $result = $this->mdl->get_by_page($search);
        return $response['data'] = $result;
    }
    
    
    function detail(array & $request, array & $response, array & $app) {
            $title_arr = array('edit'=>'编辑产品平台KEY', 'add'=>'添加产品平台KEY', 'view'=>'查看产品平台KEY');
            $app['title'] = $title_arr[$app['scene']];
            $ret = load_model('sys/RdsModel_ex')->get_by_id($request['_id']);
            $response['data'] = $ret['data'];
    }
    
    //编辑产品平台KEY
    function do_edit(array & $request, array & $response, array & $app) {
            $rdsinfo = get_array_vars($request, array('access_token','memo'));
            $rdsinfo['app_key']=$request['rdsapp_key'];
            $rdsinfo['app_secret']=$request['rdsapp_secret'];
            $ret = load_model('sys/RdsModel_ex')->update_by_id($rdsinfo, $request['rds_id']);
            exit_json_response($ret);
    }
    //添加产品平台KEY    
    function do_add(array & $request, array & $response, array & $app) {
            $rdsinfo = get_array_vars($request, array('access_token','relation_product','relation_platform','memo'));
            $rdsinfo['app_key']=$request['rdsapp_key'];
            $rdsinfo['app_secret']=$request['rdsapp_secret'];
            $ret = load_model('sys/RdsModel_ex')->insert($rdsinfo);
            exit_json_response($ret);
    }
    
    /**
     * 向淘宝发送获取企业级sessionkey的请求
     * @author jhua.zuo <jhua.zuo@baisonmail.com>
     * @date 2014-12-04
     */
    public function get_sessionkey(array & $request, array & $response, array & $app) {
	    $app['fmt'] = 'json';
	    $mdl_rds = $this->mdl;
	    $rds_list = $mdl_rds->get_all();

	    foreach($rds_list['data'] as $rds_val) {
		    $app_key = $rds_val['app_key'];
		    $app_secret = $rds_val['app_secret'];
		    //接收TOP返回值的地址, 需要过滤掉用户登录
		    $back_url = CTX()->get_app_conf('jushita_backurl');
		    //封装请求+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
		    $array_send = array();
		    $array_send["appkey"] = $app_key;
		    $array_send["post_uri"] = $back_url;
		    $array_send["timestamp"] = time() * 1000;
		    //post参数拼接生成需要签名的字符串
		    $str_verify = "";
		    ksort($array_send);
		    foreach ($array_send as $key => $val) {
			    $str_verify .= $key . $val;
		    }
		    //构造签名
		    $array_send["sign"] = strtoupper(md5($app_secret . $str_verify . $app_secret));
		    //请求发送出去
		    $result = makeRequest("http://container.api.taobao.com/container/token", $array_send, "POST");
		    if ($result != '') {
			    //请求验证时遇到了错误
			    echo $result;
			    //exit;
		    }
	    }
    }
    
    /**
     * 保存淘宝刷新后的企业session
     * @author jhua.zuo <jhua.zuo@baisonmail.com>
     * @date 2014-12-04
     */
    public function save_session(array & $request, array & $response, array & $app){
        //TOP返回数据以输入流形式获取
	    $back_info = file_get_contents("php://input");
	    //保存到临时文件
	    //file_put_contents('session.txt', $back_info);
	    //示例返回值
	    //{ "w2_expires_in": 300, "w1_expires_in": 86400, "re_expires_in": 86400, "appkey": "12570713", "r2_expires_in": 86400, "expires_in": 86400, "token_type": "undefined", "refresh_token": "6101c10f7f8ff93bcf12e2b5fa7e2a2c55bd60540277dad-20101010", "top_sign": "D0BA1B1F5ED566C1F35267F41796F5A8", "access_token": "61028102b69f1a4e521da5a2ac26d31549a8f1ca0758e64-20101010", "r1_expires_in": 86400 }
	    //保存到数据库
	    $mdl_rds = $this->mdl;
	    try {
		    $array_info = json_decode($back_info, true);
		    //@TODO 此处返回信息需要签名验证以确保是淘宝返回的
		    //签名方法同发送请求时的签名方法, 签名为top_sign
		    $data = array(
			    'access_token' => $array_info['access_token'],
			    'refresh_token' => $array_info['access_token'],
			    'refresh_time' => date('Y-m-d H:i:s')
		    );
            
		    $mdl_rds->update_by_backurl($data, $array_info['appkey']);
	    } catch (Exception $exc) {
            $message = $exc->getTraceAsString();
		    echo $message;
            //记录错误日志
            error_log($message);
	    }
    }
    
    /**
     * 保存淘宝刷新后的企业session(临时使用，eFAST5刷新用的)
     * @author wangshouchong
     * @date 2014-12-04
     */
    public function callback_session(array & $request, array & $response, array & $app){
        $mdl_rds = $this->mdl;
        try {
                $data = array(
                    'access_token' => $request['token'],
                    'refresh_token' => $request['token'],
                    'refresh_time' => date('Y-m-d H:i:s')
                );

                $mdl_rds->update_by_backurl($data, $request['key']);
        } catch (Exception $exc) {
            $message = $exc->getTraceAsString();
            echo $message;
            //记录错误日志
            error_log($message);
        }
    }
}