<?php
require_lib('util/web_util', true);
require_lib('util/oms_util', true);
require_model('oms/TaobaoRecordModel', true);
require_model('oms/SellRecordFixModel', true);
require_model('oms/SellRecordOptModel', true);

class api_taobao_fx_order {
    //淘宝分销列表
    function td_list(array &$request, array &$response, array &$app) {
    	//$response['source'] = load_model('base/SaleChannelModel')->get_data_code_map();
        $response['change_fail_num'] = load_model('api/FxTaobaoTradeModel')->get_fail_order_num();
    }
    

    //淘宝分销商品列表
    function product_list(array &$request, array &$response, array &$app) {
        $response['source'] = load_model('base/SaleChannelModel')->get_data_code_map();
    }
    function get_product_sku_list_by_pid(array &$request, array &$response, array &$app){
         $data = load_model('api/FxTaoBaoProductModel')->get_product_sku_list_by_pid($request['pid'], array());
		$response = array('rows'=>$data);
    }
    //删除分销商品
    function do_delete(array & $request, array & $response, array & $app) {
        $ret = load_model('api/FxTaoBaoProductModel')->do_delete($request['pid']);
        exit_json_response($ret);
    }
    //分销订单详情
    function td_view(array &$request, array &$response, array &$app) {
    	$ret = load_model('api/FxTaobaoTradeModel')->get_trade_info($request['ttid']);
    	$response['record'] = $ret;
    	if(isset($ret) && !empty($ret)){
    		$detail_list  = load_model('api/FxTaobaoTradeModel')->get_by_field_order_all('ttid',$ret['ttid'], $select = "*");
    		$response['record']['detail_list'] = $detail_list;
    	}
    	//取得国家数据
    	$response['area']['country'] = load_model('base/TaobaoAreaModel')->get_area('0');
    	$response['area']['province'] = array();
    	$area_ids = load_model('base/TaobaoAreaModel')->get_by_field_all($response['record']['receiver_country'],$response['record']['receiver_state'],$response['record']['receiver_city'],$response['record']['receiver_district'],$response['record']['receiver_street']);
    	$response['record']['ids'] = $area_ids;
    	$response['area']['province'] = load_model('base/TaobaoAreaModel')->get_area($area_ids['country_id']);
    	$response['area']['city'] = load_model('base/TaobaoAreaModel')->get_area($area_ids['province']);
    	$response['area']['district'] = load_model('base/TaobaoAreaModel')->get_area($area_ids['city']);
    	$response['area']['street'] = load_model('base/TaobaoAreaModel')->get_area($area_ids['district']);;
    
    }
    
    //转分销订单
    function td_tran(array &$request, array &$response, array &$app) {
    	$sql = "select fenxiao_id from api_taobao_fx_trade where ttid in(".$request['ttid'].")";
    	$tid_arr = ctx()->db->getAll($sql);
    	$tids = array();
    	foreach ($tid_arr as $tid){
    		$tids[] = $tid['fenxiao_id'];
    	}
    	$response = load_model("oms/TranslateOrderModel")->translate_fenxiao_order($tids);
    	if (empty($response['err'])){
    		$response['status'] = 1;
    		$response['message'] = '转单成功';
    	}else{
    		$response['status'] = -1;
    		$response['message'] = '转单失败,'.$response['err'][0]['message'];
    	}
        if ($response['status'] == -1) {
            $response['change_fail_num'] = load_model('api/FxTaobaoTradeModel')->get_fail_order_num();
        } else {
            $response['change_fail_num'] = 0;
        }
    }
    
    //更新允许转单但未转单订单的商品条码操作
    function barcode_update(array &$request, array &$response, array &$app){
    	$response = load_model('api/FxTaobaoTradeModel')->barcode_update($request);
    }
    
    //平台订单标记已转单
    function td_traned(array &$request, array &$response, array &$app) {
    	$app['fmt'] = 'json';
    	$is_change = isset($request['is_change'])?$request['is_change']:1;
    	$response = load_model('api/FxTaobaoTradeModel')->td_traned($request['ttid'],$is_change);
    }
    //批量置为未转单
    function td_no_traned(array &$request, array &$response, array &$app) {
    	$app['fmt'] = 'json';
    	$is_change = isset($request['is_change'])?$request['is_change']:0;
    	$response = load_model('api/FxTaobaoTradeModel')->td_no_traned($request['id'],$is_change);
    }
    
    //分销订单保存
    function td_save(array &$request, array &$response, array &$app) {
    	$app['fmt'] = 'json';
//     	$arr_province = load_model('base/TaobaoAreaModel')->get_by_field('id',$request['province']);;
//     	$request['receiver_state'] = isset($arr_province['data']['name'])?$arr_province['data']['name']:'';
    
//     	$arr_city = load_model('base/TaobaoAreaModel')->get_by_field('id',$request['city']);;
//     	$request['receiver_city'] = isset($arr_city['data']['name'])?$arr_city['data']['name']:'';
    
//     	$arr_district = load_model('base/TaobaoAreaModel')->get_by_field('id',$request['district']);;
//     	$request['receiver_district'] = isset($arr_district['data']['name'])?$arr_district['data']['name']:'';
    
//     	$request['receiver_address'] = $request['receiver_province'].$request['receiver_city'].$request['receiver_district'].$request['receiver_street'].$request['receiver_addr'];
    	//修改api_taobao_fx_trade表
//     	$data = get_array_vars($request, array('receiver_phone','receiver_country','receiver_province','receiver_city','receiver_district','receiver_street','receiver_name','receiver_mobile','receiver_mobile','receiver_address','receiver_addr'));
        $arr_province = load_model('base/TaobaoAreaModel')->get_by_field('id', $request['province']);
        $request['receiver_state'] = isset($arr_province['data']['name']) ? $arr_province['data']['name'] : '';
        $arr_city = load_model('base/TaobaoAreaModel')->get_by_field('id', $request['city']);
        $request['receiver_city'] = isset($arr_city['data']['name']) ? $arr_city['data']['name'] : '';
        $arr_district = load_model('base/TaobaoAreaModel')->get_by_field('id', $request['district']);
        $request['receiver_district'] = isset($arr_district['data']['name']) ? $arr_district['data']['name'] : '';
        $request['receiver_address'] = $request['receiver_addr'];
        $ret['status'] = 1;
        if($request['type_name'] != "btn_save"){//'receiver_phone','receiver_name','receiver_mobile_phone',
                	$data = get_array_vars($request, array('receiver_state','receiver_city','receiver_district','receiver_address'));
                        //echo '<hr/>$data<xmp>'.var_export($data,true).'</xmp>';
                         $data['customer_address_id'] = 0;
                        $data['customer_code'] = '';
                        $ret = load_model('api/FxTaobaoTradeModel')->update($data, array('fenxiao_id' => $request['fenxiao_id']));
        }
    	//修改api_taobao_fx_order表
    	if(!empty($request['barcode'])){
    		$ret = load_model('api/FxTaobaoTradeModel')->save($request['barcode']);
    	}
    	$response = $ret;
    
    }
    
    //批量设置是否允许库存同步
    function p_update_active(array &$request, array &$response, array &$app) {
    	$arr = array('enable' => 1, 'disable' => 0);
    	$ret = load_model('api/FxTaoBaoProductModel')->p_update_active($arr[$request['type']], $request['pid']);
    	exit_json_response($ret);
    }
    //单个设置是否允许库存同步
    function update_active_sku(array &$request, array &$response, array &$app) {
    	$ret = load_model('api/FxTaoBaoProductModel')->update_active($request['type'], $request['id']);
    	exit_json_response($ret);
    }
    function down(array &$request, array &$response, array &$app){
        $response['sale_channel']=array(array('taobao','淘宝'),array('fenxiao','淘分销'));
        $sale_channel_code=$response['sale_channel'][0][0];
        $response['shop'] = load_model('base/ShopModel')->get_purview_tbfx_shop_channel('shop_code,shop_name',$sale_channel_code);
    }
        //交易下载
    function down_trade(array &$request, array &$response, array &$app){
    	$ret = load_model('api/FxTaobaoTradeModel')->down_trade($request);
    	 exit_json_response($ret);
    }

     //下载进度
    function down_trade_check(array &$request, array &$response, array &$app){
        $ret = load_model('api/FxTaobaoTradeModel')->down_trade_check($request);
        exit_json_response($ret);
    }
    function down_product(array &$request, array &$response, array &$app){
        $response['sale_channel']=array(array('taobao','淘宝'),array('fenxiao','淘分销'));
        $sale_channel_code=$response['sale_channel'][0][0];
        $response['shop'] = load_model('base/ShopModel')->get_purview_tbfx_shop_channel('shop_code,shop_name',$sale_channel_code);
    }
    //联动效果
    function get_shop_by_sale_channel(array &$request, array &$response, array &$app) {
        $sale_channel_code = $request['sale_channel_code'];
        $ret = load_model('base/ShopModel')->get_purview_tbfx_shop_channel('shop_code,shop_name,shop_id',$sale_channel_code);
        exit_json_response($ret);
    }
    
      //下载商品
    function down_goods(array &$request, array &$response, array &$app){
    	$ret = load_model('api/FxTaoBaoProductModel')->down_goods($request);
    	 exit_json_response($ret);
    }

     //下载商品进度
    function down_goods_check(array &$request, array &$response, array &$app){
        $ret = load_model('api/FxTaoBaoProductModel')->down_goods_check($request);
        exit_json_response($ret);
    }
}