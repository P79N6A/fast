<?php
require_lib('util/web_util', true);
require_model('sys/PrintTemplatesModel');

class flash_templates
{
    // ?app_act=sys/flash_templates/edit&template_id=5&model=oms/DeliverRecordModel&typ=default
    function edit(array &$request, array &$response, array &$app){
        
        if(isset($request['template_code'])){
            $ret = load_model('sys/PrintTemplatesModel')->get_template_id($request['template_code']);
            if(!empty($ret['data'])){
                $request['template_id'] = $ret['data']; 
            }
            
        }
        $templateID = isset($request['template_id']) ? $request['template_id'] : '';
        
        if(empty($templateID)){
            return $response = array('status'=>'-1', 'message'=>'模板不存在');
        }

        $app['page'] = 'null';
        $app['tpl'] = 'sys/flash_templates_edit';

        $m = new PrintTemplatesModel();
        $mdl = $m->newClass($request['model']);
        

        
        
        // print_fields_xxx 模板项
        $fieldsKey = 'print_fields_'.$request['typ'];
        $response['fields'] = $mdl->$fieldsKey;
        
        //批次开启添加批次设置 StoreOutRecordModel
        $model_arr = array('wbm/StoreOutRecordModel');
        if(in_array($request['model'], $model_arr)){
               $arr = array('lof_status');
               $arr_lof = load_model('sys/SysParamsModel')->get_val_by_code($arr);
               if( $arr_lof['lof_status']==0 && $response['fields']['detail'][0]['批次']){
                    unset($response['fields']['detail'][0]['批次']);
               }
        }
        
        
        
        $request['templates_code']='pur_purchaser';
        $tabs_arr['pur'] = array(
            'pur_purchaser'=>array(
                'url'=> 'sys/flash_templates/edit&template_code=pur_purchaser&model=pur/PurchaseRecordModel&typ=default&tabs=pur',
                'title'=>'采购入库单模版'
            ),
             'pur_return'=>array(
                   'url'=>  'sys/flash_templates/edit&template_code=pur_return&model=pur/ReturnRecordModel&typ=default&tabs=pur',
                 'title'=>'采购退货单模版'
                 ),
         
          );
        
        $response['tabs'] = $tabs_arr;
        
    }
    // ?app_act=sys/flash_templates/edit_td&template_id=11&model=prm/GoodsBarcodeModel&typ=default
    function edit_td(array &$request, array &$response, array &$app){
        $templateID = isset($request['template_id']) ? $request['template_id'] : '';
        if(empty($templateID)){
            return $response = array('status'=>'-1', 'message'=>'模板不存在');
        }

        $app['page'] = 'null';
        $app['tpl'] = 'sys/flash_templates_edit_td';

        $m = new PrintTemplatesModel();
        $mdl = $m->newClass($request['model']);

        // print_fields_xxx 模板项
        $fieldsKey = 'print_fields_'.$request['typ'];
        //打印发票
        if ($request['model'] == 'oms/InvoiceRecordModel') {
        	$fields = $mdl->$fieldsKey;
        	$response['fields'] = $fields['record'];
        	foreach($response['fields'] as $k=>&$v){
        		$v = $k;
        	}
        	$response['fields_detail'] = $fields['detail'];
        	foreach($response['fields_detail'] as $k=>&$v){
        		foreach ($v as $kk=>&$vv) {
        			$vv = $kk;
        		}
        	}
        } else {
        	$response['fields'] = $mdl->$fieldsKey;
        	foreach($response['fields'] as $k=>&$v){
        		if($k == '条码') $v = '690123456789';
        		else $v = $k;
        	}
        }
        
    }
    function get_template_body(array &$request, array &$response, array &$app){
        $app['page'] = 'null';
        $app['fmt'] = 'json';
        $templateID = isset($request['template_id']) ? $request['template_id'] : '';
        if(empty($templateID)){
            return $response = array('status'=>'-1', 'message'=>'模块不存在');
        }

        $m = new PrintTemplatesModel();

        //模板
        $response = $m->get_row(array('print_templates_id'=>$templateID));
        echo $response['data']['template_body'];
        exit;
    }
    function save(array &$request, array &$response, array &$app){ 
        $app['fmt'] = 'json';
        $templateID = isset($request['template_id']) ? $request['template_id'] : '';
        if(empty($templateID)){
            return $response = array('status'=>'-1', 'message'=>'模块不存在');
        }

        $m = new PrintTemplatesModel();

        $data = array();
        $data['template_body'] = $request['template_body'];
        $response = $m->update($data, array('print_templates_id'=>$templateID));
    }
}