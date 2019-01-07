<?php
require_lib ( 'util/web_util', true );
require_lib('util/oms_util', true);
class Monthly_analysis {
	function do_list(array & $request, array & $response, array & $app) {
            $shop_info = load_model('base/ShopModel')->get_purview_shop();
            $response['shop_info'] = $shop_info;
        $response['well_more'] = array('fullurl'=>get_app_url('crm/monthly_analysis/more_info&type=1'),'_url'=>base64_encode('crm/monthly_analysis/more_info&type=1'));
        $response['unsalable_more'] = array('fullurl'=>get_app_url('crm/monthly_analysis/more_info&type=2'),'_url'=>base64_encode('crm/monthly_analysis/more_info&type=2'));
        //var_dump($shop_info);
		//$response = load_model('crm/OperateModel')->get_report_data();
		//$response['order_sale_money']  = json_encode(array(100,200,300,400,500,600,700));
	}

        function get_monthly_info(array & $request, array & $response, array & $app){
            $app['fmt']="json";
            $response = load_model('crm/MonthlyAnaslysisModel')->get_month_info($request);
        }
    public function more_info(array & $request,array & $response, array & $app){
        $response['goods_spec'] = load_model('sys/SysParamsModel')->get_val_by_code(array('goods_spec1','goods_spec2'));
        $type = isset($request['type']) ? $request['type'] : 0;
        $request['order_by'] = isset($request['order_by']) ? $request['order_by'] : 'num';
        if($type == 1){
            $app['tpl'] = 'crm/monthly_analysis_more_info';
        }elseif($type == 2){
            $app['tpl'] = 'crm/monthly_analysis_more_unsell_info';
        }
    }
}
