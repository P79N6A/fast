<?php
require_lib ( 'util/web_util', true );
require_lib ( 'business_util', true );
class purchase_analyse {
	function do_list(array & $request, array & $response, array & $app) {
		
	}

    function view(array & $request, array & $response, array & $app) {
        $request['goods_spec'] = load_model('sys/SysParamsModel')->get_val_by_code(array('goods_spec1', 'goods_spec2'));
        $response['brand'] = load_model('prm/BrandModel')->get_purview_brand_select();
    }

    /**明细按商品维度
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function sku(array & $request, array & $response, array & $app) {
        $response['user_id'] = CTX()->get_session('user_id');
        $response['is_sort'] = $request['is_sort'];
        $app['page'] = 'NULL';
        //扩展属性
        $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('property_power'));
        $property_power = $ret_cfg['property_power'];
        if($property_power){
            $response['proprety'] = load_model('prm/GoodsPropertyModel')->get_property_val('property_code,property_val_title,property_val');
        }
    }

    /**明细按单据维度
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function record(array & $request, array & $response, array & $app) {
        $response['user_id'] = CTX()->get_session('user_id');
        $response['is_sort'] = $request['is_sort'];
        $app['page'] = 'NULL';
        //扩展属性
        $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('property_power'));
        $property_power = $ret_cfg['property_power'];
        if($property_power){
            $response['proprety'] = load_model('prm/GoodsPropertyModel')->get_property_val('property_code,property_val_title,property_val');
        }
    }

    /**
     * 列表汇总
     * @param array $request
     * @param array $response
     * @param array $app
     */
    function report_count(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $ret = load_model('pur/PurReportModel')->report_count($request);
        exit_json_response($ret);
    }
    function detail_count(array & $request, array & $response, array & $app){
        $ret = load_model('pur/PurReportModel')->detail_count($request);
        exit_json_response($ret);
    }
}

