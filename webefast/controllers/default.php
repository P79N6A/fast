<?php
function __get_conf($type, $env) {
	$conf = require_conf('table/'.$type, $env);

	if (empty($conf)) {
		exit_error_page('提示', '暂未开放，敬请期待');
	}
	return $conf;
}
function do_list(array & $request,array & $response,array & $app){
	$type = $request['type_'];
	$conf = __get_conf($type,  array('request'=>$request));
	
	$response['type_'] = $type;
	$response['conf'] = $conf;
	$response['list_conf_name'] = $type;
	$app['tpl'] = 'default/do_list';
}
function add(array & $request,array & $response,array & $app){
	$type = $request['type_'];
	$response['type_'] = $type;
	
	// 处理提交请求
	if (isset($request['do']) && $request['do'] == 1) {
		$ret = load_model(__get_model_path($type))->add($request);
		if ($ret['status'] > 0) {
			$ret['data'] = array();
			$ret['data']['url'] = get_app_url('default/do_list&type_='.$type);
		}
		$response = $ret;
		$app['fmt'] = 'json';
	// 添加页面
	} else {
		$conf_path = $type.'_add';
		$conf = require_conf('form/'.$conf_path);
		
		$response['conf'] = $conf;
		$response['list_conf_name'] = $conf_path;
		
		$app['tpl'] = 'default/add';
	}
}
function edit(array & $request,array & $response,array & $app){
	$type = $request['type_'];
	$response['type_'] = $type;
	
	// 处理提交请求
	if (isset($request['do']) && $request['do'] == 1) {
		$ret = load_model(__get_model_path($type))->update($request);
		if ($ret['status'] > 0) {
			$ret['data']['url'] = get_app_url('default/do_list&type_='.$type);
		}
		$response = $ret;
		$app['fmt'] = 'json';
	// 编辑页面
	} else {
		$conf_path = $type.'_edit';
		$conf = require_conf('form/'.$conf_path);
		
		$response['conf'] = $conf;
		$response['list_conf_name'] = $conf_path;
		$ret = load_model(__get_model_path($type))->get_detail_by_pk($request['id']);
		$response['data'] = $ret['data'];
		
		$app['tpl'] = 'default/edit';
	}
}
function delete(array & $request,array & $response,array & $app){
	$type = $request['type_'];
	
	list($dir, $class) = explode('/', $type);
	$model_path = $dir.'/'.ucfirst($class).'_model';
	$ret = load_model($model_path)->delete($request['id']);
	$response = $ret;
	$app['fmt'] = 'json';
}
function custom(array & $request,array & $response,array & $app){
	$type = $request['type_'];
	if (isset($request['method_'])) {
		$_method = $request['method_'];
	}
	$ret = load_model(__get_model_path($type))->$_method($request);
	$response = $ret;
	$app['fmt'] = 'json';
}
function export_data(array & $request,array & $response,array & $app){
	$type = $request['type_'];
	load_model(__get_model_path($type))->export_data($request);
}

function __get_model_path($type) {
	list($dir, $class) = explode('/', $type);
	return $dir.'/'.ucfirst($class).'_model';
}
