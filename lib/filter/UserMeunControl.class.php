<?php
require_once ROOT_PATH.'boot/req_inc.php';
//require_once ROOT_PATH.'config.php';
class UserMeunControl implements IRequestFilter{
	function handle_before(array & $request,array & $response,array & $app){

		//判断是否是计划任务运行
		if (not_null($app['mode']) && 'cli' == $app['mode']) {
			return;
		}
        /**
         * 如果云版 更换数据链接和私有目录
         */
        if(defined('CLOUD') && CLOUD){

            //session中获取数据库名
            $renter = $GLOBALS['context']->get_session("renter");
            if((empty($renter) || !isset($renter['renter_code'])) && empty($request['token'])){
                //跳转到云登录页面
                $url = $GLOBALS['context']->get_app_conf('cloud_url');
                header("Location:$url");
                die();
            }
            
            if((empty($renter) || !isset($renter['renter_code'])) && !empty($request['token'])){
                $token = isset($request['token']) ? $request['token'] : '';
                $uncode = unserialize(gzuncompress(mcrypt_decrypt(MCRYPT_3DES, APP_SALT, base64url_decode($token), "ecb")));
            }
            
            //切换数据链接
            CTX()->db->set_conf(array(
                'name' => !empty($renter['renter_code']) ? $renter['renter_code'] : $uncode['renter_code'],
                'host' => CTX()->get_app_conf('db_host'),
                'user' => CTX()->get_app_conf('db_user'),
                'pwd' => CTX()->get_app_conf('db_pass'),
                'type' => CTX()->get_app_conf('db_server'),
            ));
            //检查和创建客户私有目录
            $dir = ROOT_PATH . $GLOBALS['context']->app_name . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . $renter['renter_code'];
            make_folder($dir);
            make_folder($dir . DIRECTORY_SEPARATOR . 'download');
            make_folder($dir . DIRECTORY_SEPARATOR . 'download' . DIRECTORY_SEPARATOR . 'importData');
            make_folder($dir . DIRECTORY_SEPARATOR . 'download' . DIRECTORY_SEPARATOR . 'stock_import_errs');
            make_folder($dir . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'base');
            make_folder($dir . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'excelImportTpl');
            make_folder($dir . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'excelTmp');
            make_folder($dir . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'excelTpl');
            make_folder($dir . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'logo');
        }

        $app = $GLOBALS['context']->app;
        $db = new db();
        
        /*if('cli'==$app['mode'])
            return;*/
	    if(isset($request['source']))
	        return;
		 //wx 先不设权限	    
	     //if(isset($app['path']) && strncmp($app['path'],'wx/',3)==0) return;
	     
	    $path = $app['grp']."/".$app['act'];
	    /*if($path == "index/auth_yjzl" || $path == "index/auth_wkfwzx" ){ //直接访问
	    	return;
	    }*/
	    	
	    $sys_user = sys_user();

	    if($app['name'] == "manage" && $path != "index/quit"
        && $path != "index/index" && $path != "index/login"
        && $path != "index/do_top" && $path != "index/reg" 
        && $path != "index/posReg" && $path != "index/posReg_action"
        && $path != "index/do_default_content" && $path != "index/do_index"
        && $path != 'danju_print/generate_barcode' && $path != "index/index/do_index"
        && $path != 'vip/login' 
        && $path != 'task_cli/deamon' 
        && $path != 'task_cli/syn'
        && $path != 'task_cli/step1_reply'
        && $path != 'upload_cli/deamon' 
        && $path != 'upload_cli/reply_one'
        && $path != 'upload_cli/reply_task'
        && $app['path'] != 'weixinclub/'
        && $path != "index/reg_action" && $path != 'index/captcha'
        && !stristr($app['grp'], "erp")
        && !stristr($app['path'], "weixin")){

            manage_login();
        }

        //微信登录
	    /*if($app['name'] == "manage" && isset($app['path']) && $app['path'] =='weixinclub/'){
            switch($app['grp']){ //品牌导购不需要登录
                case 'nearby_shop':
                case 'site_activity':
                case 'collocation':
                case 'shop_suggest':
                case 'hot_single':
                case 'today_activity':
                case 'goods_guide':
                    return;
            }

            if($app['path'].$app['grp'] != 'weixinclub/index' && $app['path'].$app['grp'] != 'weixinclub/my_share'){
                if(crm_user() == false){
                    $weixinId = isset($request['weixin_id']) ? '&weixin_id='.$request['weixin_id'] : '';
                    $url = 'http://'. $_SERVER["HTTP_HOST"]. $_SERVER["REQUEST_URI"];
                    header("Location: ?app_act=weixinclub/index/index".$weixinId.'&referer='.urlencode($url));
                    exit;
                }
            }
        }*/
        //后台登录
	    elseif($app['name'] == "manage"){

    	    /****************网站防火墙****************/
	        $ip = isset($_SERVER["REMOTE_ADDR"])?$_SERVER["REMOTE_ADDR"]:'0.0.0.0';
    	    /*$param_value = get_param_value(1);
    	    $sql = "select count(*) from sys_gfw where ip = '".$ip."'";
    	    if($param_value == 1){
    	        $num = $db->get_value($sql);
    	        if($num['data'] == 1){
    	            header('Content-Type: text/html;charset=utf-8');
    	            echo "系统启用了黑名单，您的ip禁止访问";
    	            exit;
    	        }  
    	    }else if($param_value == 2){
    	        $num = $db->get_value($sql);
    	        if($num['data'] != 1){
    	            header('Content-Type: text/html;charset=utf-8');
    	            echo "系统启用了白名单，您的ip禁止访问";
    	            exit;
    	        }
    	    }*/
    	    /****************网站防火墙****************/
    	    
    	    /****************在线用户*****************/
    	    /*if(isset($sys_user['user_name'])){
        	    $sql = "select count(*) from serve_online where user_id = ".$sys_user['user_id'];
        	    $num = $db->get_value($sql);
        	    if($num['data'] == 0){
        	        $db->insert("serve_online",array("user_id"=>$sys_user['user_id'],"user_name"=>$sys_user['user_name'],"ip"=>$ip,"add_time"=>add_time()));
        	    }else{
        	        $db->update("serve_online",array("user_name"=>$sys_user['user_name'],"ip"=>$ip,"add_time"=>add_time()),array("user_id"=>$sys_user['user_id']));
        	    }
    	    }*/
    	    /****************在线用户*****************/
	    }
        
        //获取当前的屏幕号和胜券在握网url地址
          //sql注入以及xss攻击过滤
          //$request = daddslashes($request);
          
          $request['s']  = isset($request['s'])?htmlspecialchars($request['s']):'manage';
       
        
        $app_act = $app['path'] . $app['grp'] . "/" . $app['act'];

        $purview = get_purview_info_by_path($app_act, $request['s']);
        $app['title_info']['screen_number'] = isset($purview['screen_number']) ? $purview['screen_number'] : '';
        //帮助地址改为固定的sqzw.com地址
        //$app['title_info']['help_url'] = isset($purview['help_url']) ? $purview['help_url'] : '';
        $app['title_info']['help_url'] = isset($purview['screen_number']) ? 'http://xy.sqzw.com/sq365/' . $purview['screen_number'] . '.htm' : '';
    }
}
