<?php
require_lib ('util/web_util', true);
class sys_auth {
	//系统参数配置
    function do_list(array &$request, array &$response, array &$app) {
        $sql = "select name,value from sys_auth where code in('version','company_name','auth_key','auth_num','auth_enddate','star_code','star_password')";
        $arr = ctx()->db->get_all($sql);
    	$response['auth'] = $arr;
        $response['cp_code'] =  ctx()->db->get_value("select  value  from  sys_auth where code=:code ",array(':code'=>'cp_code'));
//            1、系统管理>版本信息，添加显示软件授权到期时间
//2、显示星联卡号、星联密码    
    }
	
}

