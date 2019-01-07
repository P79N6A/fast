<?php

require_lib('util/web_util', true);

class shop_clerk {

    function do_list(array &$request, array &$response, array &$app) {

    }

    function detail(array & $request, array & $response, array & $app) {
        $title_arr = array('edit' => '编辑店员', 'add' => '添加店员', 'view' => '查看店员');
        $app['title'] = $title_arr[$app['scene']];
        if ($app['scene'] == 'edit') {
            $ret = load_model('sys/UserModel')->query_by_id($request['_id']);
            if ($ret['status'] > 0) {
                $ret['data']['type'] = (int)$ret['data']['type'];
                $response['data'] = $ret['data'];
            }
        }
    }

    /**
     * 添加店员
     */
    function do_add(array &$request, array &$response, array &$app) {
        $user = get_array_vars($request, array('user_code', 'user_name', 'sex', 'birthday', 'phone', 'relation_shop', 'type'));
        $user['login_type'] = 1;
        $ret = load_model('sys/UserModel')->insert_user($user);
        exit_json_response($ret);
    }

    /**
     * 编辑店员信息
     */
    function do_edit(array &$request, array &$response, array &$app) {
        $user = get_array_vars($request, array('user_name', 'sex', 'birthday', 'phone', 'relation_shop', 'type'));
        $ret = load_model('sys/UserModel')->update($user, $request['user_id']);
        exit_json_response($ret);
    }

    /**
     * 更新店员启用状态
     */
    function update_active(array & $request, array & $response, array & $app) {
        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('sys/UserModel')->update_active($arr[$request['status']], $request['user_id'], 1);
        exit_json_response($ret);
    }

    /**
     * 删除店员
     */
    function do_delete(array & $request, array & $response, array & $app) {
        $ret = load_model('sys/UserModel')->delete($request['user_id']);
        exit_json_response($ret);
    }

    /**
     * 重设密码
     */
    function reset_pwd(array & $request, array & $response, array & $app) {
        $ret = load_model('sys/UserModel')->reset_pwd($request['user_id'], 1);
        exit_json_response($ret);
    }

}
