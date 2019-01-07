<?php

require_lib('util/web_util', true);

class upgrade {

    function do_list(array & $request, array & $response, array & $app) {

        $ret = load_model('upgrade/VersionPatchModel')->get_version();
        $response['version_no'] = array(array('', '请选择'));
        foreach ($ret['data'] as $val) {
            $response['version_no'][] = array('0' => $val['version_no'], '1' => $val['version_no']);
        }
//var_dump($response['version_no']);die;
    }

    //获取客户升级补丁
    function get_version_patch(array & $request, array & $response, array & $app) {
        $response = load_model('upgrade/VersionPatchModel')->get_upgrade_patch_list($request['version_no']);
    }

    function do_upgrade_all(array & $request, array & $response, array & $app) {
        $response = load_model('upgrade/VersionPatchModel')->set_upgrade_ids($request['ids'], $request['version_no'], $request['version_patch']);
    }

    function exprot_upgrade_fail_log(array & $request, array & $response, array & $app) {
        load_model('upgrade/VersionPatchModel')->exprot_upgrade_fail_log($request);
    }

    function exec_upgrade_patch(array & $request, array & $response, array & $app) {
        $user_id = CTX()->get_session('user_id');
        if (CTX()->is_in_cli() || !empty($user_id)) {
            $response = load_model('upgrade/VersionPatchModel')->exec_upgrade_patch($request);
        }
    }

    function get_upgrade_info(array & $request, array & $response, array & $app) {
        $response = load_model('upgrade/VersionPatchModel')->get_exec_status();
    }

    function update_sysdb(array & $request, array & $response, array & $app) {
        if (isset($request['sql_name'])) {
            $response = load_model('basedata/RdsDataModel')->update_rds_all_sql($request['sql_name']);
        }
    }

}

?>
