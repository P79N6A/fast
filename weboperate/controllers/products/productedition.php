<?php
/**
 *产品版本信息
 * @author WangShouChong
 */
require_lib ( 'util/web_util', true );
class productedition {
    
    //版本列表
    function do_list(array & $request, array & $response, array & $app) {
        
    }
    
    function detail(array & $request, array & $response, array & $app) {
            $title_arr = array('edit'=>'编辑产品系统版本', 'add'=>'新建产品系统版本', 'view'=>'查看产品系统版本');
            $app['title'] = $title_arr[$app['scene']];
            $ret = load_model('products/ProductEditionModel')->get_by_id($request['_id']);
            $response['data'] = $ret['data'];
    }
    
    function do_add(array & $request, array & $response, array & $app){
        //获取主表数据
        $prouctversion = get_array_vars($request, 
            array('pv_code', 
                'pv_name',
                'pv_bh',
                'pv_rq',
                'pv_fbr',
                'pv_cp_id',
                'pv_type',
                'pv_path',
                'pv_js',
                ));
        //附件明细
        $ret = load_model('products/ProductEditionModel')->insert($prouctversion,$request["file"]);
        exit_json_response($ret);
    }
    
    function do_edit(array & $request, array & $response, array & $app){
        //获取主表数据
        $prouctversion = get_array_vars($request, 
            array('pv_code', 
                'pv_name',
                'pv_bh',
                'pv_rq',
                'pv_fbr',
                'pv_cp_id',
                'pv_type',
                'pv_path',
                'pv_js',
                ));
        //附件暂时不考虑
        $ret = load_model('products/ProductEditionModel')->update($prouctversion, $request['pv_id']);
        exit_json_response($ret);
    }
}
