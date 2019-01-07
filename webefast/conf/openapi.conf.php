<?php
//http://localhost/fastapp-new/webefast/web/?app_act=openapi/router&key=demo&kh_id=888&timestamp=2015-04-23+07%3A22%3A00&page_no=10&sign=F82D57640EA1B1DD68289841E44555D8&m=basic.shop_detail&shop_code=tb022

/**
 * 形如：
 *  array(
 *      api=>array(
 *          model路径=>array(
 *              // 1、参数留空，则默认将request作为唯一参数传入方法；
 *              // 2、参数以逗号分隔，按顺序传入方法进行调用
 *              // 比如:
 *              // get_by_page=>''，最终调用为$model->get_by_page($request); 
 *              // get_detail=>'code,name'，最终调用为$model->get_detail($request['code'], $request['name']),
 *              //      如果$request['code']或$request['code']不存在会返回-514错误
 *              接口方法=>参数 
 *              ...
 *          )
 *      ),
 *      alias=>arry(
 *          接口别名=>对应model里的方法
 *          ...
 *      )
 *  )
 */
$api_conf = array('api'=>array(), 'alias'=>array());
$conf_list = array('base', 'prm', 'oms', 'api','stm');
foreach ($conf_list as $name) {
	$ret = require_conf('openapi/'.$name);
	if (empty($ret)) {
		continue;
	}
	$api_conf['api'] = array_merge($api_conf['api'], $ret['api']);
	$api_conf['alias'] = array_merge($api_conf['alias'], $ret['alias']);
}

return $api_conf;