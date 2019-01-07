<?php

/*
 * 产品中心-产品vm管理
 */

class Vhost_manage {

    //产品vm管理列表
    function do_list(array & $request, array & $response, array & $app) {
        
    }

    //新建、编辑产品vm页面显示
    function detail(array & $request, array & $response, array & $app) {
        $title_arr = array('edit' => '编辑主机明细信息', 'add' => '新建主机明细');
        $app['title'] = $title_arr[$app['scene']];
        $ret = load_model('products/VhostModel')->get_vhost_id($request['_id']);
        $response['data'] = $ret['data'];
    }

    //编辑产品vm
    function do_vhost_edit(array & $request, array & $response, array & $app) {
        $vhost = get_array_vars($request, array(
            'vem_vm_id',
            'vem_product_version',
            'vem_cp_version',
            'vem_cp_version_ip',
            'vem_cp_path',
            'vem_cp_id',
            'vem_status',
            'vem_cp_web_path',
        ));
        $ret = load_model('products/VhostModel')->update($vhost, $request['vem_id']);
        if ($ret['status']) {
            $rdsinfo = load_model('products/VhostModel')->getrds_info($request['vem_vm_id']);
            foreach ($rdsinfo as $val) {
                load_model('basedata/RdsDataModel')->update_kh_data(0, $val['asa_rds_id'], 'osp_vmextmanage_ver');
            }
        }
        exit_json_response($ret);
    }

    //添加产品vm管理    
    function do_vhost_add(array & $request, array & $response, array & $app) {
        $vhost = get_array_vars($request, array(
            'vem_vm_id',
            'vem_product_version',
            'vem_cp_version',
            'vem_cp_version_ip',
            'vem_cp_path',
            'vem_cp_id',
            'vem_status',
            'vem_cp_web_path',
        ));
        $vhost['vem_createdate'] = date('Y-m-d H:i:s');
        $ret = load_model('products/VhostModel')->insert($vhost);
        if ($ret['status']) {
            $rdsinfo = load_model('products/VhostModel')->getrds_info($request['vem_vm_id']);
            foreach ($rdsinfo as $val) {
                load_model('basedata/RdsDataModel')->update_kh_data(0, $val['asa_rds_id'], 'osp_vmextmanage_ver');
            }
        }
        exit_json_response($ret);
    }

}
