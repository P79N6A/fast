<?php
// require_lib('util/web_util', true);
// require_lib('util/oms_util', true);
// require_model('oms/TaobaoRecordModel', true);

class shop_report {
    //put your code here
    function data_analyse(array & $request, array & $response, array & $app){
//     	$response['category'] = load_model('prm/CategoryModel')->get_category_trees();
// 		$response['brand'] = $this->get_purview_brand();
    }

    function view(array &$request, array &$response, array &$app){
      $response = load_model('wms/WmsMgrModel')->wms_record_info($request['task_id'],$request['type'], 1);
      $m = new WmsTradeModel();
      if ($request['type'] == 'oms') {
        $wms_info = $m->getInfoById($request['task_id'], 'wms_oms_trade');
      } else {
        $wms_info = $m->getInfoById($request['task_id'], 'wms_b2b_trade');
      }
      $record_order_type = isset($m->order_type[$wms_info['record_type']]) ? $m->order_type[$wms_info['record_type']] : '';
      $response['data']['record_order_type'] = $record_order_type;
      $response['data']['wms_info'] = $wms_info;

      if ($response['status'] > 0 && $wms_info['wms_order_flow_end_flag'] == 1) {
        $obj = load_model('util/ViewUtilModel');
        $mx = $obj->append_mx_info_by_barcode($response['data']['goods']);
        $goods_info = $obj->record_detail_append_goods_info($mx['data']);
        $total_efast_sl = '';
        $total_wms_sl = '';
        foreach ($response['data']['goods'] as $key1 => $good) {
          foreach ($goods_info as $key2 => $good_info) {
            if ($good_info['barcode'] == $good['barcode']) {
              $response['data']['goods'][$key1]['goods_code'] = $good_info['goods_code'];
              $response['data']['goods'][$key1]['spec1_code'] = $good_info['spec1_code'];
              $response['data']['goods'][$key1]['spec2_code'] = $good_info['spec2_code'];
              $response['data']['goods'][$key1]['sku'] = $good_info['sku'];
              $response['data']['goods'][$key1]['goods_name'] = $good_info['goods_name'];
              $response['data']['goods'][$key1]['spec1_name'] = $good_info['spec1_name'];
              $response['data']['goods'][$key1]['spec2_name'] = $good_info['spec2_name'];
            }
            $total_efast_sl += $good['efast_sl'];
            $total_wms_sl += $good['wms_sl'];
          }
          
        }
        $response['data']['total_efast_sl'] = ($total_efast_sl >= 0) ? $total_efast_sl : '';
        $response['data']['total_wms_sl'] = ($total_wms_sl >= 0) ? $total_wms_sl : '';
        $response['data']['wms_order_time'] = (strtotime($response['data']['wms_order_time']) > 0) ? $response['data']['wms_order_time'] : '';
      }
      
   		
      if ($request['type'] == 'oms') {
        $wms_oms_log = $m->get_wms_oms_log($wms_info['record_code'], 'wms_oms_log');
      } else {
        $wms_oms_log = $m->get_wms_oms_log($wms_info['record_code'], 'wms_b2b_log');
      }
      $response['data']['log_info'] = $wms_oms_log;
      $response['type'] = $request['type'];
    }


    //外包仓库存
    function inv_list(array &$request, array &$response, array &$app) {

    }

    function update_sku(array &$request, array &$response, array &$app){
  
      $data = load_model('wms/WmsTradeModel')->get_wms_store();
      
      $html_tpl = '<option value="" >--请选择--</option>';
      foreach($data as $sub_data){
          $html_tpl.="<option value='{$sub_data['store_code']}'>{$sub_data['store_name']}</option>";
      }

      $response['tpl_html'] = $html_tpl;
    }

}