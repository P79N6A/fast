<?php

require_lib('util/web_util', true);

class oms_month_analysis {

    function get_month_all(array & $request, array & $response, array & $app) {
        $mod = load_model("rpt/OmsMonthAnalysisModel");
        $shop_code = $request['shop_code'];
        $year_month = $request['year_month'];


        $ret = $mod->get_sale_all($shop_code, $year_month);
        $response['sale_all'] = $ret['data'];



        $ret = $mod->get_sale_data($shop_code, $year_month);
        $response['sale_data'] = $ret['data'];
//              'month_data'=> $month_arr,
//             'sale_data'=>$sale_data,
//             'sale_money_data'=>$sale_money_data,
//             'refund_data'=>$refund_data,
//             'refund_money_data'=>$refund_money_data,  
        $ret = $mod->get_sale_cat_data($shop_code, $year_month, 0);
        $response['cat_data'] = $ret['data'];
        $ret = $mod->get_sale_cat_data($shop_code, $year_month, 1);
        $response['brand_data'] = $ret['data'];
        

        $ret = $mod->get_sale_goods_data($shop_code, $year_month, 0);
        $response['goods_num_data'] = $ret['data'];
        
        $ret =$mod->get_sale_goods_data($shop_code, $year_month, 1);
        $response['gooods_money_data'] = $ret['data'];
        
        $ret =$mod->get_sale_goods_data($shop_code, $year_month, 2);
        $response['gooods_unsalable_data'] = $ret['data'];
        
    }
    

    

}
