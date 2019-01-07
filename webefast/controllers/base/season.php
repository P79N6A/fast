<?php
require_lib('util/web_util', true);
class season {
    function do_list(array &$request, array &$response, array &$app) {

    }
    function detail(array &$request, array &$response, array &$app) {
    	$ret = array();
    	if (isset($request['_id']) && $request['_id'] != '') {
        	$ret = load_model('base/SeasonModel')->get_by_id($request['_id']);
    	}
        $response['data'] = isset($ret['data'])?$ret['data']:'';
        $response['app_scene'] = $_GET['app_scene'];
    }

    function do_edit(array &$request, array &$response, array &$app) {
        
        $season = get_array_vars($request, array('season_name'));
        $ret = load_model('base/SeasonModel')->update($season, $request['season_id']);
        exit_json_response($ret);
    }

    function do_add(array &$request, array &$response, array &$app) {
        $season = get_array_vars($request, array('season_code', 'season_name'));
        $ret = load_model('base/SeasonModel')->insert($season);
        exit_json_response($ret);
    }

    function do_delete(array &$request, array &$response, array &$app) {
        $ret = load_model('base/SeasonModel')->delete($request['season_id']);
        exit_json_response($ret);
    }
    
    function opt_delete(array &$request, array &$response, array &$app) {
        $i = 0;
        $fail = '';
        $fail_code='';
        foreach ($request['season_id'] as $value) {
            $ret = load_model('base/SeasonModel')->delete($value);
            if ($ret['status'] == -1) {
                $fail_code .= $ret['data'].',';
                if ($i==5) {
                    $fail .= $fail_code .'<br/>';
                    $fail_code = '';
                    $i = 0;
                }
                $i++;
            }
        }
        if (empty($fail_code) && empty($fail)) {
            $ret2 = array(
                "status"=> 1,
                "data" =>  '',
                "message"=>  "操作成功！"
            );
        } else {
            $ret2 = array(
                "status"=> -1,
                "data" =>  '',
                "message"=>  "季节代码为:{$fail}{$fail_code}的已经在业务系统中使用，不能删除！"
            );
        }        
        exit_json_response($ret2);
    }
}
