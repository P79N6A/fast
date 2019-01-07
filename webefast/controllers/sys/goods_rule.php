<?php
require_lib('util/web_util', true);
class goods_rule {

    function do_list(array & $request, array & $response, array & $app) {
        
    }

    function detail(array & $request, array & $response, array & $app) {
        $ret = array();
        if (isset($request['_id']) && $request['_id'] != '') {
            $ret = load_model('sys/GoodsRuleModel')->get_by_ids($request['_id']);
        }
        $response['data'] = isset($ret['data']) ? $ret['data'] : '';
    }

    function do_edit(array & $request, array & $response, array & $app) {
        $ret = load_model('sys/GoodsRuleModel')->update($request, $request['goods_rule_id']);
        exit_json_response($ret);
    }
}
