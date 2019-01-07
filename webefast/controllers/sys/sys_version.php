<?php

class sys_version {
    function do_list(array &$request, array &$response, array &$app) {
        $sql = "select version_num,public_date,about_url,update_time from sys_version where is_main_version = 1 and update_status = 1 order by id desc";
        $arr = ctx()->db->get_all($sql);
    	$response['version'] = $arr;
    }


    function show_patch(array &$request, array &$response, array &$app) {
    	if (empty($request['parent_version_num'])){
    		echo '';
    	}else{
	    	$sql = "select version_num,public_date,about_url,update_time,relation_patch_code from sys_version where is_main_version = 0 and update_status = 1 and parent_version_num = :parent_version_num order by id desc";
	    	$list = ctx()->db->get_all($sql,array(':parent_version_num'=>$request['parent_version_num']));
	    	$html = "<table><tr><th>主版本号</th><th>补丁编号</th><th>补丁更新日期</th><th>补丁描述</th></tr>";
	    	if (empty($list)){
	    		$html .= "<tr><td colspan='4'>此版本没有补丁信息</td></tr>";
	    	}else{
				foreach($list as $row){
					$html .="<tr><td>{$row['version_num']}</td><td>{$row['version_num']}</td><td>{$row['update_time']}</td><td><a href='{$row['about_url']}' target='_blank'>查看</a></td></tr>";
				}    		
	    	}
	    	$html .= "</table>";
	    	echo $html;
	    	die;    		
    	}
    }
	
}

