<?php
require_lib ( 'util/web_util', true );
require_lib('util/oms_util', true);
class express_strategy {
	function do_list(array & $request, array & $response, array & $app) {
            //判断买家留言匹配是否开启
            $param_code = 'buyer_remark';
            $ret = load_model('sys/SysParamsModel')->get_val_by_code($param_code);
            $response['status'] = $ret['buyer_remark'];
	}
	
	function express_list(array & $request, array & $response, array & $app) {
	
		$response['id'] = $request['policy_express_id'];
		$response['ES_frmId'] = $request['ES_frmId'];
	}
	
    function get_nodes(array & $request, array & $response, array & $app){
		$response = load_model('crm/ExpressStrategyModel')->get_child($request['id'], $request['policy_express_id']);
		//exit_json_response($ret);
	}

	function detail(array & $request, array & $response, array & $app) {
		$ret = array();
		if (isset($request['_id']) && $request['_id'] != '') {
			$ret = load_model('crm/ExpressStrategyModel')->get_by_id($request['_id']);
			$response['id'] = $request['_id'];
		}else{
            $response['id'] = '';
            $ret['data']['policy_express_name'] = load_model('crm/ExpressStrategyModel')->create_name();
			$ret['data']['policy_express_code'] = load_model('crm/ExpressStrategyModel')->create_code();
        }
		$response['data'] = isset($ret['data'])?$ret['data']:'';
		$response['app_scene'] = $_GET['app_scene'];
		$response['form1_data_source'] = json_encode($ret['data']);
		$response['store_code'] = load_model('base/StoreModel')->get_purview_store();
		$response['check_store'] = json_decode($ret['data']['store_code'],true);
	}

	function do_edit(array & $request, array & $response, array & $app) {
		$express_strategy = get_array_vars($request, array('policy_express_code', 'policy_express_name','store_code','is_fee_first'));
		if (!empty($express_strategy['store_code'])){
			$express_strategy['store_code'] = json_encode($express_strategy['store_code']);
		}
		$ret = load_model('crm/ExpressStrategyModel')->update($express_strategy,$request['policy_express_id']);
		CTX()->redirect('crm/express_strategy/do_list');
		return;
	}
	
	
	function do_edit_rule(array & $request, array & $response, array & $app) {
		$express_rule = get_array_vars($request, array('priority','express_name','pid','first_weight','first_weight_price','added_weight','added_weight_price','added_weight_type'));
		$ret = load_model('crm/PolicyExpressRuleModel')->update($express_rule,$request['policy_express_rule_id']);
		return;
	}

	function do_add(array & $request, array & $response, array & $app) {
		$express_strategy = get_array_vars($request, array('policy_express_code', 'policy_express_name','store_code','is_fee_first'));
		if (!empty($express_strategy['store_code'])){
			$express_strategy['store_code'] = json_encode($express_strategy['store_code']);
		}
		$ret = load_model('crm/ExpressStrategyModel')->insert($express_strategy);
		CTX()->redirect('crm/express_strategy/detail&app_scene=edit&_id='.$ret['data']['policy_express_id'].'');

	}
	
	function do_save_area(array & $request, array & $response, array & $app) {
            $app['fmt'] = 'json';
          //  $response = load_model('crm/PolicyExpressAreaModel')->save($request['policy_express_id'], $request['selected_ids']);
            $response = load_model('crm/PolicyExpressAreaModel')->save_area($request);
	}
	
	function do_add_express(array & $request, array & $response, array & $app) {
		foreach($request['express_code'] as $val){
                    $express_rule['express_code'] = strstr($val, ',', true);
                    $express_rule['express_name'] = ltrim(strstr($val, ','), ',') ;
                    $express_rule['pid'] = $request['policy_express_id'];
                    $ret = load_model('crm/PolicyExpressRuleModel')->insert($express_rule);
		}
		exit_json_response($ret);
	}
	
		
	function addr_do_save(array & $request, array & $response, array & $app) {
            
            $info = array(
			'address'=>$request['address'],
			'country'=>$request['country'],
			'province'=>$request['province'],
			'city'=>$request['city'],
			'district'=>$request['district'],
			'street'=>$request['street'],
			'zipcode'=>$request['zipcode'],
			'tel'=>$request['tel'],
			'home_tel'=>$request['home_tel'],
//			'is_add_time'=>$request['is_add_time'],
			'name'=>$request['name'],
			'is_default'=> isset($request['is_default'])? $request['is_default'] : 0,			
			);
			if($info['is_default'] == 1)
			load_model('crm/CustomerModel')->clear_default($request['customer_code']);
            
                if(!isset($request['customer_address_id'])||empty($request['customer_address_id'])){
                    //get_by_id 
//                    $ret_customer  =   load_model('crm/CustomerModel')->get_by_id($request['customer_id']);
                    $info['customer_code'] = $request['customer_code'];
                    load_model('crm/CustomerModel')->insert_customer_address($info);
                }else{
                    load_model('crm/CustomerModel')->update_customer_address($info,array('customer_address_id'=>$request['customer_address_id']));
                }
                $ret = array('status'=>1);
                exit_json_response($ret);
		
	}

	function express_do_delete(array & $request, array & $response, array & $app) {
		$ret = load_model('crm/PolicyExpressRuleModel')->delete_express($request);
		exit_json_response($ret);
	}

	function get_express_list(array & $request, array & $response, array & $app) {
		$data = load_model('crm/PolicyExpressRuleModel')->get_express_list($request['policy_express_id']);
		$html = '<table class="addr_tbl"><tr><td>操作</td><td>配送方式代码</td><td>配送方式名称</td><td>优先级</td></tr>';
		foreach($data as $sub_data){
			$html .= "<tr><td>&nbsp;<a href='#0' onclick='cg(this)'>编辑</a>&nbsp;<a href='javascript:del_express({$sub_data['policy_express_rule_id']})'>删除</a></td><td>{$sub_data['express_code']}</td><td>{$sub_data['express_code']}</td><td>{$sub_data['priority']}</td></tr>";
		}
		$html .="</table>";
		echo $html;
		die;
	}
        
        function get_default_addr(array & $request, array & $response, array & $app){
                $data = load_model('crm/CustomerModel')->get_default_addr($request['customer_code']);
                return $response = $data;
        }

	function set_default(array & $request, array & $response, array & $app) {
		$ret = load_model('crm/CustomerModel')->set_default($request['customer_address_id']);
		exit_json_response($ret);
	}

	function update_customer_address(array & $request, array & $response, array & $app){
		$info = array(
			'address'=>$request['address'],
			'country'=>$request['country'],
			'province'=>$request['province'],
			'city'=>$request['city'],
			'district'=>$request['district'],
			'street'=>$request['street'],
			'zipcode'=>$request['zipcode'],
			'tel'=>$request['tel'],
			'home_tel'=>$request['home_tel'],
			'is_add_time'=>date(Y-m-d),
			'name'=>$request['name'],
			'is_default'=> isset($request['is_default'])?$request['is_default']: 0,			
		);
		//echo '<hr/>request<xmp>'.var_export($request,true).'</xmp>';
		//echo '<hr/>info<xmp>'.var_export($info,true).'</xmp>';
		//die;
		load_model('crm/CustomerModel')->update_customer_address($info,array('customer_address_id'=>$request['customer_address_id']));
		$ret = array('status'=>1);
		exit_json_response($ret);		
	}

	function edit_addr(array & $request, array & $response, array & $app) {
		$ret = load_model('crm/CustomerModel')->get_addr($request['customer_address_id']);
		exit_json_response($ret);
	}
    function do_delete(array &$request, array &$response, array &$app) {
    	$ret = load_model('crm/ExpressStrategyModel')->get_by_id($request['policy_express_id']);
    	if ($ret['data']['status'] == 1) {
    		exit_json_response(array('status' => -1,'','message' => '已启用的快递策略不能删除'));
    	} else {
    		$ret = load_model('crm/ExpressStrategyModel')->delete_express_strategy($request['policy_express_id']);
    		exit_json_response($ret);
    	}
    }
    
	function update_active(array &$request, array &$response, array &$app) {
        $arr = array('enable' => 1, 'disable' => 2);
        if ($request['type'] == 'enable'){
        	$is_express_rule = load_model('crm/PolicyExpressRuleModel')->get_row(array('pid' => $request['policy_express_id']));
        	if (empty($is_express_rule['data'])){
        		exit_json_response(array('status' => -1,'','message' => '未设置配送方式不能启用'));
        	}
        }
       
        $ret = load_model('crm/ExpressStrategyModel')->update_active($arr[$request['type']], $request['policy_express_id']);
        exit_json_response($ret);
    }
    
    function get_op_express_by_remark(array &$request, array &$response, array &$app) {
        
    }
    function save_op_express_by_remark(array &$request, array &$response, array &$app) {
        $response =load_model('crm/OpExpressByBuyerRemarkModel')->save_express_by_buyer_remark($request['express_code'],$request['key_word']);
        
    }
    
    /////////////////////
    // 会员指定模块开始 //
    /////////////////////
    
    /**
     * 指定会员指定快递
     */
    public function get_op_express_by_user(array &$request, array &$response, array &$app)
    {
        $response['express_data'] = load_model('crm/OpExpressByGoodsModel')->get_express_list();
        $response['appoint_express'] = isset($request['express_code']) ? $request['express_code'] : '';
    }
    
    /**
     * 会员导入弹窗
     */
    public function customer_import(array &$request, array &$response, array &$app)
    {
    }
    
    /**
     * 导入会员到数据库
     */
    public function import_user(array &$request, array &$response, array &$app)
    {
        $app['fmt'] = 'json';
  	$file = $request['url'];
  	if(empty($file)){
            $response = array(
                'status' => 0,
                'type'   => '',
                'msg'    => "请先上传文件"
            );
            return $response;
  	}
  	$ret = load_model('crm/OpExpressByUserModel')->import_data($file, $request['express_code']);
  	$response = $ret;
    }
    
    /**
     * 上传文件控制器
     */
//    public function import_users(array & $request, array & $response, array & $app) {
//        set_uplaod($request, $response, $app);
//        $ret = check_ext_execl();
//        if ($ret['status'] < 0) {
//            $response = $ret;
//            return;
//        }
//        $ret = $this->import_upload($request, $_FILES);
//        $response = $ret;
//    }
    
    /**
     * 上传文件方法
     */
//    function import_upload($request,$upload_files) {
//		$app['fmt'] = 'json';
//		$files = array();
//		$url = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\')."/";
//		$fileInput = 'fileData';
//		$dir = ROOT_PATH.'webefast/uploads/';
//		$type = $_POST['type'];
//
//		$isExceedSize = false;
//		$files_name_arr = array($fileInput);
//        $is_max = 0;
//        $is_file_type = 0;
//        $file_type = array('csv', 'xlsx', 'xls');
//        $upload_max_filesize = 5242880;
//		foreach($files_name_arr as $k=>$v){
//			$pic = $upload_files[$v];
//            if (!isset($pic['tmp_name']) || empty($pic['tmp_name'])) {
//                $is_max = 1;
//                continue;
//            }
//            $file_ext = get_file_extension($pic['name']);
//            if (!in_array($file_ext, $file_type)) {
//                $is_file_type = 1;
//                continue;
//            }
//			$isExceedSize = $pic['size'] > $upload_max_filesize;
//			if(!$isExceedSize){
//				if(file_exists($dir.$pic['name'])){
//					@unlink($dir.$pic['name']);
//				}
//                $new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;
//                $result = move_uploaded_file($pic['tmp_name'], $dir . $new_file_name);
//                if (true == $result && ('xlsx' == $file_ext || 'xls' == $file_ext)) {
//                    $result = $this->excel2csv($dir . $new_file_name, $file_ext);
//                    $new_file_name = str_replace('.' . $file_ext, '.csv', $new_file_name);
//                }
//			}
//		}
//        if ($is_max){
//            return array(
//					'status' => 0,
//					'type' => $type,
//					'name' => $upload_files[$fileInput]['name'],
//                    'msg' => str_replace('{0}', substr(ini_get('upload_max_filesize'), 0, -1) * 1024, lang('upload_msg_maxSize'))
//			);
//		} else if ($is_file_type){
//            return array(
//					'status' => 0,
//					'type' => $type,
//					'name' => $upload_files[$fileInput]['name'],
//                    'msg' => str_replace('{0}', implode(',', $file_type), lang('upload_msg_ext'))
//			);
//        } else if(!$isExceedSize && $result){
//			return array(
//					'status' => 1,
//					'type' => $type,
//					'name' => $upload_files[$fileInput]['name'],
//					'url' => $dir . $new_file_name
//			);
//		}else if($isExceedSize){
//			return array(
//					'status' => 0,
//					'type' => $type,
//					'msg' => str_replace('{0}', $upload_max_filesize/1024, lang('upload_msg_maxSize'))
//			);
//		}else{
//			return array(
//					'status' => 0,
//					'type' => $type,
//					'msg' => "未知错误！".$result
//			);
//		}
//    }
    
    /**
     * excel转入csv
     */
//    function excel2csv($file, $extends) {
//        require_lib('PHPExcel', true);
//        try {
//            $time3 = time();
//            $PHPExcel = PHPExcel_IOFactory::load($file);
//            $time4 = time();
//            $objWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'CSV');
//            $objWriter->setUseBOM(true);
//            $objWriter->setPreCalculateFormulas(false);
//            $objWriter->save(str_replace('.' . $extends, '.csv', $file));
//            $time5 = time();
//        } catch (Exception $e) {
//            /*return array(
//                'status' => -1,
//                'data' => array($e->getMessage()),
//                'msg' => lang('op_error')
//            );*/
//            return false;
//        }
//        /*return array(
//            'status' => 1,
//            'data' => array('load_excel' => $time4 - $time3, 'write_csv' => $time5 - $time4, 'excel_to_csv' => $time5 - $time3),
//            'msg' => lang('op_success')
//        );*/
//        return true;
//    }
    
    /////////////////////
    // 会员指定模块结束 //
    /////////////////////
    
    /**
     * 更新用户对应的快递号
     */
    public function do_update_express_user(array &$request, array &$response, array &$app)
    {
        $ret = load_model('crm/OpExpressByUserModel')->do_update_express($request);
        exit_json_response($ret);
    }
    
    /**
     * 一键清空会员
     */
    public function do_delete_all_users(array &$request, array &$response, array &$app)
    {
        $where = empty($request['express_code']) ? array() : array('express_code' => $request['express_code']);
        $ret = load_model('crm/OpExpressByUserModel')->delete_all_users($where);
        exit_json_response($ret);
    }
    
    /**
     * 商品指定快递
     */
    function get_op_express_by_goods(array &$request, array &$response, array &$app) {
        $response['express_data'] = load_model('crm/OpExpressByGoodsModel')->get_express_list('1');
//        $response['appoint_express'] = load_model('crm/OpExpressByGoodsModel')->get_appoint_express();
    }
    
    /**
     * 更新指定快递
     */
    function do_update_express(array &$request, array &$response, array &$app){
        $ret = load_model('crm/OpExpressByGoodsModel')->do_update_express($request['appoint_express']);
        exit_json_response($ret);
    }

    /*
     * 增加指定商品
     */
    function do_add_goods(array & $request, array & $response, array & $app) {
        $ret = load_model('crm/OpExpressByGoodsModel')->add_goods($request);
        exit_json_response($ret);
    }

    /*
     * 根据id删除指定商品
     */
    function do_delete_goods(array &$request, array &$response, array &$app) {
        $ret = load_model('crm/OpExpressByGoodsModel')->delete_goods($request['id']);
        exit_json_response($ret);
    }

    /*
     * 删除全部指定商品
     */
    function do_delete_all_goods(array &$request, array &$response, array &$app) {
        $ret = load_model('crm/OpExpressByGoodsModel')->delete_all_goods($request['express_code']);
        exit_json_response($ret);
    }
    /*
     * 更新优先级
     */
    function do_update_priority(array &$request, array &$response, array &$app) {
        $ret = load_model('crm/OpExpressByGoodsModel')->do_update_priority($request['goods_priority'],$request['express_code']);
        exit_json_response($ret);
    }
    //查询优先级
    function do_goods_priority(array &$request, array &$response, array &$app) {
        $ret = load_model('crm/OpExpressByGoodsModel')->get_by_express_priority($request['express_code']);
        $ret['data'] = empty($ret['data']) ? 1 : $ret['data'];
        exit_json_response($ret);
    }
    //查询快递
    function do_all_express(array &$request, array &$response, array &$app) {
        $ret = load_model('crm/OpExpressByGoodsModel')->get_express_list('1');
        exit_json_response($ret);
    }
     
}
