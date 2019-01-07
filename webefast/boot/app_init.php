<?php
function app_init(){
	//add your app init code
	require_lang('general');
	require_lib('util/common_util,util/web_util,util/bui_util,business_util,keylock_util', true);
    
 
    
	app_init_set_debug_status();
//    $link = CTX()->get_session('link');
//    if (!empty($link)){
//    	CTX()->db->set_conf($link);	    
//    }
   
        if (defined('RUN_SAAS') && RUN_SAAS) {  
            CTX()->saas->init_saas_client();
       }  else {
                     //本地环境用205 sysdb
            if(RUN_MODE=='DEV'){
//                $conf = array(
//                    'host' => '192.168.164.205',
//                    'user' =>'efast',
//                    'pwd' => 'efast',
//                    'type' => 'mysql'    
//                );
//
//                CTX()->saas->create_sys_db($conf,1);
                      
            }
            
              $db_conf = array(
                'name' => CTX()->get_app_conf('db_name'),
                'host' => CTX()->get_app_conf('db_host'),
                'user' => CTX()->get_app_conf('db_user'),
                'pwd' => CTX()->get_app_conf('db_pass'),
                'type' => 'mysql'
            );
            $init_info['db_conf'] = $db_conf;
            $init_info['client_id'] = CTX()->get_app_conf('saas_client_id');
            $init_info['product_version'] = CTX()->get_app_conf('saas_product_version');
            $init_info['rds_id'] = CTX()->get_app_conf('saas_rds_id');
            
        // array('1'=>'efast5_Standard','2'=>'efast5_Enterprise','3'=>'efast5_Ultimate');
            CTX()->saas->init_saas_client($init_info);
            require_lib('util/dev_util',true);
 
         
       }
    
    
    
    //CTX()->cache = app_get_cache('cache_rdsinfo');
//    $clean_xss = CTX()->get_app_conf('global_clean_xss');
//    if ($clean_xss) {
//    	CTX()->request = clean_xss(CTX()->request);
//    }
}