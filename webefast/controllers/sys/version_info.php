<?php
require_lib('util/web_util', true);
class version_info {
    function do_index(array &$request, array &$response, array &$app) {
//        $company = M('sys_company_info')->get_row(array());
//        $version = M('sys_version')->get_value('select app_version from sys_version order by lastchanged');
        $data = load_model("cloud_api/CloudModel")->send("?app_act=api/kehu/get_auth_info");
        $response = array(
            'company_name' =>$data['data']['kh_name'],
            'version' => (isset($version['data'])&&!empty($version['data']))?'v'.$version['data']:'dev',
            'time'=>$data['data']['pro_endtime'],
            'point'=>'5',
            'sn'=>'201405060',
        );
    }
}