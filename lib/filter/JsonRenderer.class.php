<?php
require_once ROOT_PATH.'boot/req_inc.php';
/**
 * 用于json数据渲染。
 * $app['mode']=='func'强制采用json渲染，其它情况根据$app['fmt']!=='json'来决定是否采用json渲染。
 * json渲染结果包括resp_error，resp_data两种情况。
 */
class JsonRenderer implements IReponseRenderer{
	function render(array & $request,array & $response,array & $app){
		if($app['mode']!=='func' && $app['fmt']!=='json') return;
		if($app['err_no']!==0)
			 $json=json_encode(array('resp_error'=>array('app_err_no'=>$app['err_no'],'app_err_msg'=>$app['err_msg'])));
		else
			  $json=json_encode($response);
		if(isset($request['callback']) && $request['callback'] && $app['mode']!=='func')
			echo $request['callback'] . '(' . $json .')';
		else  echo $json;
		return true;
	}
}