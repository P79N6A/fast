<?php

/*
 * 
 */
require_lib('util/oms_util', true);

class Valueservice {

    function do_list(array & $request, array & $response, array & $app) {
        
    }

    //新建、编辑增值服务显示页面的方法
    function detail(array & $request, array & $response, array & $app) {
        $title_arr = array('edit' => '编辑增值服务', 'add' => '新建增值服务');
        $app['title'] = $title_arr[$app['scene']];
        if ($app['scene'] == "edit") {
            $app['tpl'] = "market/valueservice_detail_edit";
        }
        if ($app['scene'] == "view") {
            $app['tpl'] = "market/valueservice_detail_show";
        }
        $ret = load_model('market/ValueModel')->get_by_id($request['_id']);
        $response['data'] = $ret['data'];
        //获取efast365增值产品id
        $response['val_cp_id'] = oms_tb_val('osp_chanpin', 'cp_id', array('cp_code' => 'efast365'));
    }

    //编辑增值服务信息数据处理。
    function valueserver_edit(array & $request, array & $response, array & $app) {
        $values = get_array_vars($request, array('value_name',
            'value_cat',
            'value_price',
            'value_cycle',
            'value_cp_id',
            ///         'value_cp_version',
            'value_require_version',
            'value_desc',
            'source_path',
            'value_appl_industry',
            'development_member',
            'develop_cycle',
            'function_application',
            'is_personal',
            'value_publish_status',
            'val_remark',
            'pic_path',
            'value_sort_order'
        ));
        $ret = load_model('market/ValueModel')->update($values, $request['value_id']);
        if ($ret['status']) {
            load_model('basedata/RdsDataModel')->update_rds_all('osp_valueserver');
        }
        exit_json_response($ret);
    }

    //添加增值服务信息数据处理。    
    function valueserver_add(array & $request, array & $response, array & $app) {
        $values = get_array_vars($request, array('value_code',
            'value_name',
            'value_cat',
            'value_price',
            'value_cycle',
            'value_cp_id',
            //   'value_cp_version',
            'value_require_version',
            'value_desc',
            'source_path',
            'value_appl_industry',
            'development_member',
            'develop_cycle',
            'function_application',
            'is_personal',
            'value_publish_status',
            'val_remark',
            'pic_path',
            'value_sort_order'
        ));
        if (empty($values['value_sort_order'])) {
            $values['value_sort_order'] = 1;
        }
        if ($values['value_publish_status'] == 1) {
            $values['value_publish_data'] = date('Y-m-d H:i:s');
        }
        $ret = load_model('market/ValueModel')->insert($values);
        if ($ret['status']) {
            load_model('basedata/RdsDataModel')->update_rds_all('osp_valueserver');
        }
        exit_json_response($ret);
    }

    //
    function set_active(array & $request, array & $response, array & $app) {
        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('market/ValueModel')->update_value_enable($arr[$request['type']], $request['value_id']);
        if ($ret['status']) {
            load_model('basedata/RdsDataModel')->update_rds_all('osp_valueserver');
        }
        exit_json_response($ret);
    }

    //
    function set_active_enable(array & $request, array & $response, array & $app) {
        $this->set_active($request, $response, $app);
    }

    function set_active_disable(array & $request, array & $response, array & $app) {
        $this->set_active($request, $response, $app);
    }

    //
    function do_getvalue_type(array & $request, array & $response, array & $app) {
        $cp_id = $request['cpid'];
        $ret = load_model('market/ValueModel')->getvalue_type($cp_id);
        exit_json_response($ret);
    }

    //显示增值服务明细界面
    function value_func(array & $request, array & $response, array & $app) {
        $title_arr = array('edit' => '编辑增值服务明细', 'add' => '新建增值服务明细');
        $app['title'] = $title_arr[$app['scene']];
        $ret = load_model('market/ValueModel')->get_value_func($request['_id']);
        $response['data'] = $ret['data'];
    }

    function do_vfunc_add(array & $request, array & $response, array & $app) {
        $vfunc = get_array_vars($request, array(
            'value_id',
            'vd_busine_id',
            'vd_busine_code',
            'vd_busine_type',
            'remark'
        ));
        $ret = load_model('market/ValueModel')->insert_vfunc($vfunc);
        if ($ret['status']) {
            load_model('basedata/RdsDataModel')->update_rds_all('osp_valueserver_detail');
        }
        exit_json_response($ret);
    }

    function do_vfunc_edit(array & $request, array & $response, array & $app) {
        $vfunc = get_array_vars($request, array(
            'vd_busine_id',
            'vd_busine_code',
            'vd_busine_type',
            'remark'
        ));
        $ret = load_model('market/ValueModel')->update_vfunc($vfunc, $request['vd_id']);
        if ($ret['status']) {
            load_model('basedata/RdsDataModel')->update_rds_all('osp_valueserver_detail');
        }
        exit_json_response($ret);
    }

    function do_vfunc_delete(array & $request, array & $response, array & $app) {
        $ret = load_model('market/ValueModel')->delete_vfunc($request['vd_id']);
        if ($ret['status']) {
            //  load_model('basedata/RdsDataModel')->update_rds_all('osp_valueserver_detail');
            load_model('basedata/RdsDataModel')->delete_rds_data('osp_valueserver_detail', $request['vd_id']);
        }
        exit_json_response($ret);
    }

    function set_publish(array & $request, array & $response, array & $app) {
        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('market/ValueModel')->update_value_publish($arr[$request['type']], $request['value_id']);
        exit_json_response($ret);
    }

    //增值弹框
    function show_value_serever(array &$request, array &$response, array &$app) {
        $app['page'] = 'NULL';
    }
    
  //查询服务
    function get_service_action(array &$request, array &$response, array &$app){
        $app['fmt'] = 'json';

        $request['page_size'] = $request['limit'];
        $request['page'] = $request['pageIndex'] + 1;
        $result = load_model('market/ValueModel')->get_service_goods($request);

        $response['rows'] = $result['data']['data'];
        $response['results'] = $result['data']['filter']['record_count'];
        $response['hasError'] = false;
        $response['error'] = '';
    }
    
   
}
