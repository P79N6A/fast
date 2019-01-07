<?php

/*
 * 基础数据-云服务供应商-型号配置
 */
require_lib('util/web_util', true);

class cloudserver {


    //云主机显示页面的方法
    function hostdetail(array & $request, array & $response, array & $app) {
        $title_arr = array('edit' => '编辑型号配置', 'add' => '新建型号配置');
        $app['title'] = $title_arr[$app['scene']];
        $ret = load_model('basedata/CloudserverModel')->get_by_id($request['_id']);
        $response['data'] = $ret['data'];
    }

    //云数据库显示页面的方法
    function dbdetail(array & $request, array & $response, array & $app) {
        $title_arr = array('edit' => '编辑型号配置', 'add' => '新建型号配置');
        $app['title'] = $title_arr[$app['scene']];
        $ret = load_model('basedata/CloudserverModel')->get_by_id($request['_id']);
        $response['data'] = $ret['data'];
    }

    //编辑云主机
    function do_host_edit(array & $request, array & $response, array & $app) {
        $host = get_array_vars($request, array(
            'cm_host_type',
            'cm_host_cpu',
            'cm_host_mem',
            'cm_host_disk',
            'cm_host_net',
        ));
        $host['cm_type'] = '1';
        $host['cm_cd_id'] = $request['cm_cd_id'];
        $ret = load_model('basedata/CloudserverModel')->update_host($host, $request['cm_id']);
        exit_json_response($ret);
    }

    //添加云主机
    function do_host_add(array & $request, array & $response, array & $app) {
        $host = get_array_vars($request, array(
            'cm_host_type',
            'cm_host_cpu',
            'cm_host_mem',
            'cm_host_disk',
            'cm_host_net',
        ));
        $host['cm_type'] = '1';
        $host['cm_cd_id'] = $request['cm_cd_id'];
        $ret = load_model('basedata/CloudserverModel')->insert_host($host);
        exit_json_response($ret);
    }

        //编辑云数据库配置   
    function do_db_edit(array & $request, array & $response, array & $app) {
        $host = get_array_vars($request, array(
            'cm_db_type',
            'cm_db_mem',
            'cm_db_disk',
            'cm_max_con',
            'cm_max_qps',
            'cm_max_iops',
        ));
        $host['cm_type'] = '2';
        $host['cm_cd_id'] = $request['cm_cd_id'];
        $ret = load_model('basedata/CloudserverModel')->update_db($host, $request['cm_id']);
        exit_json_response($ret);
    }

    //添加云数据库配置   
    function do_db_add(array & $request, array & $response, array & $app) {
        $host = get_array_vars($request, array(
           'cm_db_type',
            'cm_db_mem',
            'cm_db_disk',
            'cm_max_con',
            'cm_max_qps',
            'cm_max_iops',
        ));
        $host['cm_type'] = '2';
        $host['cm_cd_id'] = $request['cm_cd_id'];
        $ret = load_model('basedata/CloudserverModel')->insert_db($host);
        exit_json_response($ret);
    }
    

    
    //删除产品模块信息
    function do_delete(array & $request, array & $response, array & $app) {
        $ret = load_model('basedata/CloudserverModel')->delete($request['cm_id']);
        exit_json_response($ret);
    }

}
