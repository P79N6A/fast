<?php
/**
 * Description of strategytype
 *
 * @author Administrator
 */
require_lib ( 'util/web_util', true );
class strategytype {
    
        function do_list(array & $request, array & $response, array & $app) {

        }
		
        function detail(array & $request, array & $response, array & $app) {
            	$title_arr = array('edit'=>'编辑营销策略类型', 'add'=>'新增营销策略类型', 'view'=>'查看营销策略类型');
		$app['title'] = $title_arr[$app['scene']];
                $ret = load_model('market/StrategyTypeModel')->get_by_id($request['_id']);
		$response['data'] = $ret['data'];
        }
        
        function do_add(array & $request, array & $response, array & $app){
            	$sts= get_array_vars($request, array('st_code', 'st_name','st_remark'));
		$ret = load_model('market/StrategyTypeModel')->insert($sts);
		exit_json_response($ret);
        }
        
        function do_edit(array & $request, array & $response, array & $app){
            	$sts= get_array_vars($request, array('st_code', 'st_name','st_remark'));
		$ret = load_model('market/StrategyTypeModel')->update($sts, $request['st_id']);
		exit_json_response($ret);
        }
}
