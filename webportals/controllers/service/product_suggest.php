<?php

/*
 * 服务中心-提单管理-产品建议
 */

class product_suggest {

    //产品需求提单列表
    function do_list(array & $request, array & $response, array & $app) {
        
    }

    //新建、编辑,查看产品需求提单
    function detail(array & $request, array & $response, array & $app) {
        $title_arr = array('edit' => '编辑需求提单', 'add' => '新建需求提单', 'view' => '查看需求提单');
        $app['title'] = $title_arr[$app['scene']];
        $ret = load_model('service/ProductSuggestModel')->get_by_id($request['_id']);
        $response['data'] = $ret['data'];
    }

}
