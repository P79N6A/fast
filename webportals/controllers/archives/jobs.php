<?php

/*
 * 系统档案-岗位信息类
 */

class Jobs {

    function do_list(array & $request, array & $response, array & $app) {
        
    }

    //新建岗位、编辑岗位显示页面的方法
    function detail(array & $request, array & $response, array & $app) {
        $title_arr = array('edit' => '编辑岗位', 'add' => '新建岗位');
        $app['title'] = $title_arr[$app['scene']];
        $ret = load_model('archives/ArchiveModel')->get_by_id($request['_id']);
        $response['data'] = $ret['data'];
    }

    //编辑岗位信息数据处理。
    function jobs_edit(array & $request, array & $response, array & $app) {
        $jobs = get_array_vars($request, array('post_code', 'post_name'));
        $ret = load_model('archives/ArchiveModel')->update($jobs, $request['post_id']);
        exit_json_response($ret);
    }

    //添加岗位信息数据处理。    
    function jobs_add(array & $request, array & $response, array & $app) {
        $jobs = get_array_vars($request, array('post_code', 'post_name', 'post_state'));
        $ret = load_model('archives/ArchiveModel')->insert($jobs);
        exit_json_response($ret);
    }

    //设置岗位状态处理。
    function set_active(array & $request, array & $response, array & $app) {
        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('archives/ArchiveModel')->update_job_active($arr[$request['type']], $request['post_id']);
        exit_json_response($ret);
    }

    //设置岗位状态处理。
    function set_active_enable(array & $request, array & $response, array & $app) {
        $this->set_active($request, $response, $app);
    }

    function set_active_disable(array & $request, array & $response, array & $app) {
        $this->set_active($request, $response, $app);
    }

}
