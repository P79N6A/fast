<?php
require_once ROOT_PATH . 'boot/req_inc.php';

class UserAclFilter implements IRequestFilter {
	function handle_before(array &$request, array &$response, array &$app) {      
	

		$app_act = get_app_act();
		if (isset($app['scene']) && $app['scene'] != 'default') {
			$app_act .= '#scene=' . $app['scene'];
		}


                if (defined('APP_MODE_CLI_CHECK')&&APP_MODE_CLI_CHECK&&CTX()->is_in_cli()) {//cli模式不检测权限
                    require_model('common/OspSaasModel'); 
                    $status = OspSaasModel::cli_app_init();
                   if($status===false){
                        echo "cli error...";die;
                    }else{
                        return ;
                    }
		} 
   
                
            
		if ($app ['mode'] == 'func') {
			// TODO func模式处理
			exit('TODO');
		} else {
			$filename = ROOT_PATH . CTX()->app_name . '/conf/acl.conf.php';
			if (file_exists($filename)) {
				$ret = include $filename;
				$uncheck_login_list = $ret['uncheck_login_list'];
				$uncheck_priv_list = $ret['uncheck_priv_list'];
				$unlogin_redirect_page = $ret['unlogin_redirect_page'];
			} else {
				exit('acl.conf not exist!');
			}
                        
			// 不需要登录的页面
			if (in_array($app_act, $uncheck_login_list)) return;

			// 检查是否已登录
			if (null == CTX()->get_session('user_id')) {
				if (isset($request['un_check_ajax'])) { //un_check_ajax不以ajax方式监测
					CTX()->redirect($unlogin_redirect_page);
				}

				if (isset($request['ajax']) || isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
					$data = array('location' => '?app_act=' . $unlogin_redirect_page);
					exit_json_response(401, $data, lang('op_no_login'));
				} else {
					CTX()->redirect($unlogin_redirect_page);
				}
                        }else{
                            if(defined('APP_MODE_CLI_CHECK')&&APP_MODE_CLI_CHECK){
                                $force_logout = require_conf('force_logout');
                                $logout_user = isset($force_logout['user'])?$force_logout['user']:array();
                                
                                $kh_id = CTX()->saas->get_saas_key();
                            
                                if(isset($logout_user[$kh_id])){
                                    $user_code = CTX()->get_session('user_code');
                                    if(in_array($user_code, $force_logout[$kh_id])){
                                        session_destroy();
                                           echo "<script>top.location.href='?app_act=index/logout';</script>";
                                                   echo "<meta http-equiv='Refresh' content='0;URL=?app_act=index/logout'>";
                                                   echo "强制退出，请联系服务商！";
                                                   exit; 
                                    }

                                }
                            }else{
                                  $force_logout = require_conf('force_logout');  
                                  $user_code = CTX()->get_session('user_code');
                                  if(isset($force_logout['operate_user'])&&in_array($user_code, $force_logout['operate_user'])){
                                       echo "<script>top.location.href='?app_act=login/do_logout';</script>";
                                                   echo "<meta http-equiv='Refresh' content='0;URL=?app_act=login/do_logout'>";
                                                   echo "强制退出，请联系管理员！";
                                                   exit;   
                                  }
                                  
                                  
                            }
//                         
                        }
                        
                           
                        

			// TOOD 完善权限检查
			// 不需要启用acl的act列表
			if (in_array($app_act, $uncheck_priv_list)) {
				return;
			}
                        $is_service = CTX()->get_app_conf('is_service');
                        if($is_service===TRUE){
                            return ;
                        }
                     
			// 检查访问权限
			if (!load_model('sys/PrivilegeModel')->check_priv($app_act)) {
				if ((isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == 1)
					|| (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
				) {
					exit_json_response(-401, '', '无权访问');
				} else {
					exit_no_priv_page('无权进行此操作', '请确保您使用的帐号【' . CTX()->get_session('user_name', true) . '】有执行此操作的权限！');
				}
			}
		}
	}
}