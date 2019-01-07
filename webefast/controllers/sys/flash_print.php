<?php
require_lib('util/web_util', true);
require_model('sys/PrintTemplatesModel');

class flash_print
{
    // ?app_act=sys/flash_print/do_print&template_id=5&model=oms/DeliverRecordModel&typ=default&record_ids=18,19,20
    function do_print(array &$request, array &$response, array &$app){

        //&template_id=27&model=oms/WavesRecordModel
        
        
        
        $app['page'] = 'NULL';
        $m = new PrintTemplatesModel();
        if($request['model']=='oms/WavesRecordModel'&&$m->is_oms_waves_goods()){
             //专门的进货单打印
              $url = "tprint/tprint/do_print&print_templates_code=oms_waves_goods&record_ids={$request['record_ids']}&print_type=flash";
              CTX()->redirect($url);
              //
              die;
        }
        
        
        $title_arr = 
        array('wbm/StoreOutRecordModel'=>'批发销货单',
            'oms/DeliverRecordModel'=>'发货单',
        	'oms/InvoiceRecordModel'=>'发票',
            'pur/PurchaseRecordModel'=>'采购入库单',
            'wbm/ReturnRecordModel'=>'批发退货单',
            'wbm/NoticeRecordModel'=>'批发销货通知单',
            );
        $response['title'] = isset($title_arr[$request['model']])?$title_arr[$request['model']]:'单据';
        $mdl = $m->newClass($request['model']);

        // $mdl->print_fields_xxx
        $fieldsKey = 'print_fields_'.$request['typ'];
        $response['fields'] = $mdl->$fieldsKey;
        // $mdl->print_data_xxx()
        $dataKey = 'print_data_'.$request['typ'];

        // ids
        $ids = explode(',', $request['record_ids']);
        $is_lof = 0;
        if(isset($request['template_code'])){
            $ret = load_model('sys/PrintTemplatesModel')->get_template_id($request['template_code']);
            if(!empty($ret['data'])){
                $request['template_id'] = $ret['data']; 
            }
        }
        $model_arr = array('wbm/StoreOutRecordModel');
        if(in_array($request['model'], $model_arr)){
               $arr = array('lof_status');
               $arr_lof = load_model('sys/SysParamsModel')->get_val_by_code($arr);
               if( $arr_lof['lof_status']==1){
                   $ret_body = $m->get_row(array('print_templates_id'=>$request['template_id']));
                   $template_body = $ret_body['data']['template_body'];
                   if(strpos($template_body,'=#批次')!==FALSE){
                       $is_lof = 1;
                   }
               }
        }
        // execute
        foreach($ids as $id) {
            if($is_lof==1){
                $response['data'][] = $mdl->$dataKey($id,1);
            }else{
                $response['data'][] = $mdl->$dataKey($id);
            }
        }
        //预览
        if(isset($request['view'])&&$request['view']==1){
            $app['tpl']='sys/flash_print_do_preview';
        }
        
    }

    
    function do_print_td(array & $request, array & $response, array & $app){
        $app['page'] = 'NULL';
        $m = new PrintTemplatesModel();
        $mdl = $m->newClass($request['model']);

        // $mdl->print_fields_xxx
        $fieldsKey = 'print_fields_'.$request['typ'];
        $response['fields'] = $mdl->$fieldsKey;

        // $mdl->print_data_xxx()
        $dataKey = 'print_data_'.$request['typ'];

        // ids
        if($request['model'] == 'prm/GoodsBarcodeModel') {
            $sku_id_arr = explode(",", $request['record_ids']);
            $arr = array();
            $print_num = array();
            foreach ($sku_id_arr as $sku) {
                $sku_arr = explode("_", $sku);
                $arr[] = $sku_arr[0];
                $print_num[$sku_arr[0]] = $sku_arr[1];
            }
            $ids = $arr;
        } else {
            $ids = explode(',', $request['record_ids']);
        }
        // execute
        foreach($ids as $id) {
        	$data = $mdl->$dataKey($id);
        	if ($request['model'] == 'oms/InvoiceRecordModel') {
        		$response['data'][] = $data['record'];
        		$response['data_goods'][] = $data['detail'];
                } else if ($request['model'] == 'prm/GoodsBarcodeModel') {
                    $print_num[$id] = !empty($print_num[$id]) ? $print_num[$id] : 1;
                    for ($i = 1; $i <= $print_num[$id]; $i++) {
                        $response['data'][] = $data;
                    }
                } else {
        		$response['data'][] = $data;
        	}
        }
    }
    
    /**
     * 采购入库单打印条码
     */
    function do_print_barcode(array & $request, array & $response, array & $app) {
        $app['page'] = 'NULL';
        $m = new PrintTemplatesModel();
        $mdl = $m->newClass($request['model']);

        $fieldsKey = 'print_fields_' . $request['typ'];
        $response['fields'] = $mdl->$fieldsKey;

        $dataKey = 'print_data_' . $request['typ'];
        $ids = array();
        //获取系统sku_id
        if(!empty($request['purchaser_record_id'])){
        $ids = load_model('pur/PurchaseRecordDetailModel')->get_sku_id_by_pid($request['purchaser_record_id']);            
        }else if(!empty($request['store_out_record_id'])){
        $ids = load_model('wbm/StoreOutRecordDetailModel')->get_sku_id_by_pid($request['store_out_record_id']);                     
        }

        // 循环打印
        foreach ($ids as $id) {
            if ($id['num'] == 0) {
                $data = $mdl->$dataKey($id['sku_id']);
                $response['data'][] = $data;
            } else {
                for ($i = 1; $i <= $id['num']; $i++) {
                    $data = $mdl->$dataKey($id['sku_id']);
                        $response['data'][] = $data;
                }
            }
        }
    }

}