<?php

/*
 * 产品中心-增值授权
 */

class valueorderauth {

    //增值授权列表
    function do_list(array & $request, array & $response, array & $app) {
        
    }

    //新建、编辑产品订购显示页面的方法
    function detail(array & $request, array & $response, array & $app) {
        $title_arr = array('edit' => '编辑增值服务订购', 'add' => '新建增值服务订购');
        $app['title'] = $title_arr[$app['scene']];
        $ret = load_model('products/ValueorderauthModel')->get_by_id($request['_id']);
        $response['data'] = $ret['data'];
    }

}
