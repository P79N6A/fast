<?php
require_lib ( 'util/web_util', true );
require_lib('util/oms_util', true);
class Customer {
	function do_list(array & $request, array & $response, array & $app) {
            
	}

	function detail(array & $request, array & $response, array & $app) {
		$ret = array();
		if (isset($request['_id']) && $request['_id'] != '') {
			$ret = load_model('crm/CustomerModel')->get_by_id($request['_id']);
	    	if($ret['data']['birthday'] == "0000-00-00" )
	    	$ret['data']['birthday'] = '';
			$response['form1_data_source'] = json_encode($ret['data']);
			$response['shop_code'] = $ret['data']['shop_code'];
			$result = load_model('base/ShopModel')->get_by_code($ret['data']['shop_code']);
			$response['sale_channel_code'] = $result['data']['sale_channel_code'];
		}
        $response["sale_channel"] = load_model('base/SaleChannelModel')->get_data_code_map();
		$response['data'] = isset($ret['data'])?$ret['data']:'';
		$response['app_scene'] = $_GET['app_scene'];
	}

	function do_edit(array & $request, array & $response, array & $app) {
		$customer = get_array_vars($request, array('customer_code', 'customer_name','customer_sex','tel','home_tel','nickname','type','birthday','email','qq','shop_code'));
		$ret = load_model('crm/CustomerModel')->update($customer, $request['customer_id']);
		CTX()->redirect('crm/customer/do_list');
		return;
		//exit_json_response($ret);
	}

	function do_add(array & $request, array & $response, array & $app) {
		$customer = get_array_vars($request, array('customer_code', 'customer_name','customer_sex','tel','home_tel','nickname','type','birthday','email','qq','shop_code'));
                $customer['source'] = $request['sale_channel'];
                $customer['is_page'] = 1;
		$ret = load_model('crm/CustomerOptModel')->add_customer($customer);
                
		CTX()->redirect('crm/customer/detail&app_scene=edit&_id='.$ret['data']['customer_id'].'');
//		if (isset($ret['status']) && $ret['status']<>1){
//			$ret = array('status'=>-1,'message'=>'添加失败');
//		}else{
//			$ret = array('status'=>1,'message'=>'添加成功');
//		}
//		exit_json_response($ret);
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
             
                    load_model('crm/CustomerOptModel')->create_customer_address($info);
                }else{
                    $info['customer_address_id'] = $request['customer_address_id'];
                     load_model('crm/CustomerOptModel')->create_customer_address($info);
                   // load_model('crm/CustomerModel')->create_customer_address($info,array('customer_address_id'=>$request['customer_address_id']));
                }
                $ret = array('status'=>1);
                exit_json_response($ret);

	}

	function addr_do_delete(array & $request, array & $response, array & $app) {
		$ret = load_model('crm/CustomerModel')->delete_customer_address($request['customer_address_id']);
		exit_json_response($ret);
	}

	function get_addr_list(array & $request, array & $response, array & $app) {
		$data = load_model('crm/CustomerModel')->get_addr_list($request['customer_code']);
                
                $ret = load_model('crm/CustomerModel')->get_by_code($request['customer_code']);
                $customer_data = $ret['data'];
                $ret_encrypt = load_model('sys/security/SysEncrypModel')->get_shop_encrypt($customer_data['shop_code']);
                $is_encrypt = empty($ret_encrypt['data'])?FALSE:TRUE;
   
		$html = '<table class="addr_tbl"><tr><th>操作</th><th>收货人</th><th>地址</th><th>手机</th><th>邮编</th><th>固定电话</th></tr>';
		foreach($data as $sub_data){

			$province = oms_tb_val('base_area', 'name', array('id'=>$sub_data['province']));
			$city = oms_tb_val('base_area', 'name', array('id'=>$sub_data['city']));
			$district = oms_tb_val('base_area', 'name', array('id'=>$sub_data['district']));
			$street = oms_tb_val('base_area', 'name', array('id'=>$sub_data['street']));
			$preAddress = "中国".$province.$city.$district.$street;
			$checked_flag = (int)$sub_data['is_default'] == 0 ? '<span style="cursor:pointer;">设为默认</span>' : '<span style="color:red;">默认</span>';
			$class = (int)$sub_data['is_default'] == 0 ? 'flag' : '';
                        $encrypt_str = "";
                        if($is_encrypt===true){
                            $encrypt_str ="<a href='javascript:show_data({$sub_data['customer_address_id']})'>显示信息</a>";
                        }
			if((int)$sub_data['is_default'] == 0){
			$html .= "<tr id='{$sub_data['customer_address_id']}_row'><td ><span id = '{$sub_data['customer_address_id']}' class = '{$class}' >{$checked_flag}</span>&nbsp;<a href='javascript:edit_addr({$sub_data['customer_address_id']})'>修改</a>&nbsp;<a href='javascript:del_addr({$sub_data['customer_address_id']})'>删除</a> {$encrypt_str}</td><td>{$sub_data['name']}</td><td>".$preAddress."{$sub_data['address']}</td><td>{$sub_data['tel']}</td><td>{$sub_data['zipcode']}</td><td>{$sub_data['home_tel']}</td></tr>";
			}else{
			$html .= "<tr id='{$sub_data['customer_address_id']}_row'><td><span id = '{$sub_data['customer_address_id']}' class = '{$class}' >{$checked_flag}</span>&nbsp;<a href='javascript:edit_addr({$sub_data['customer_address_id']})'>修改</a>&nbsp; {$encrypt_str}</td><td>{$sub_data['name']}</td><td>".$preAddress."{$sub_data['address']}</td><td>{$sub_data['tel']}</td><td>{$sub_data['zipcode']}</td><td>{$sub_data['home_tel']}</td></tr>";
			}
		}
		$html .="</table>";
		echo $html;
		die;
	}
        
        function get_show_addr(array & $request, array & $response, array & $app){

           $data = load_model('crm/CustomerOptModel')->get_customer_address_encrypt($request['customer_address_id']);
           		$province = oms_tb_val('base_area', 'name', array('id'=>$data['province']));
			$city = oms_tb_val('base_area', 'name', array('id'=>$data['city']));
			$district = oms_tb_val('base_area', 'name', array('id'=>$data['district']));
			$street = oms_tb_val('base_area', 'name', array('id'=>$data['street']));
			$preAddress = "中国".$province.$city.$district.$street.$data['address'];
           $response = $data;
           $response['address'] = $preAddress;

        }
       function show_name(array & $request, array & $response, array & $app){

           $response['data'] = load_model('crm/CustomerOptModel')->get_buyer_name_by_code($request['customer_code']);
            $log_data['customer_address_id'] = 0;
            $log_data['customer_code'] = $request['customer_code'];
            $log_data['record_code'] = '无';
            $log_data['record_type'] = '无';
            $log_data['action_note'] = '查看会员名称';
            load_model('sys/security/CustomersSecurityLogModel')->add_log($log_data);
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
        $ret = load_model('crm/CustomerModel')->delete($request['customer_id']);
        exit_json_response($ret);
    }

	function update_active(array &$request, array &$response, array &$app) {
        $arr = array('enable' => 1, 'disable' => 2);
        $ret = load_model('crm/CustomerModel')->update_active($arr[$request['type']], $request['customer_id']);
        exit_json_response($ret);
    }
    function get_by_page_address(array &$request, array &$response, array &$app) {

             $request['page_size'] = $request['limit'];
             $request['page'] = $request['pageIndex']+1;
            $app['fmt'] = 'json';
            $result = load_model('crm/CustomerModel')->get_by_page_address($request);
            $response['rows'] = $result['data']['data'];
            $response['results'] = $result['data']['filter']['record_count'];
            $response['hasError'] =  false;
            $response['error'] =  '';
    }
    
    //导入
        function import(array & $request, array & $response, array & $app) {

         }
    
    function import_customer(array & $request, array & $response, array & $app) {
        $ret = check_ext_execl();
        if ($ret['status'] < 0) {
            $response = $ret;
            return;
        }
        
        $ret = load_model('pur/OrderRecordModel')->import_upload($request, $_FILES);
        $response = $ret;
        set_uplaod($request, $response, $app);
    }
    
    
    function do_import_action(array & $request, array & $response, array & $app) {
        $prive = load_model('sys/PrivilegeModel')->check_priv('crm/customer/import_customer');
        if ($prive) {
          $app['fmt'] = 'json';
            $file = $request['url'];
            if (empty($file)) {
                $response = array(
                    'status' => 0,
                    'type' => '',
                    'msg' => "请先上传文件"
                );
            }
            $ret = load_model('crm/CustomerOptModel')->imoprt_detail($file);
        } else {
            $ret = array(
                'status' => '-1',
                'data' => '',
                'message' => '请先获取权限！'
            );
        }
        
        exit_json_response($ret);
    }
}
