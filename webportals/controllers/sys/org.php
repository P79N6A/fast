<?php

require_lib('util/web_util', true);

class org {

    function do_list(array & $request, array & $response, array & $app) {
        
    }

    function detail(array & $request, array & $response, array & $app) {
        $title_arr = array('view' => '查看用户');
        $app['title'] = $title_arr[$app['scene']];
        $ret = load_model('sys/OrgModel')->get_by_id($request['_id']);
        $response['data'] = $ret['data'];
    }

    //获取组织机构json数据
    function getList(array & $request, array & $response, array & $app) {
        if (!isset($request['pid'])) {
            $orgid = "0"; //默认一级机构数据
        } else {
            $orgid = $request['pid'];
        }
        $ret = load_model('sys/OrgModel')->getOrgListById($orgid);
        //$response['data'] = $ret['data'];
        foreach ($ret as & $data) {
            if ($data['leaf'] == "0") {
                $data['leaf'] = false;
            } else {
                $data['leaf'] = true;
            }
        }
        exit_json_response($ret);
    }

}
