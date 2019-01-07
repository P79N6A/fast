<?php
/**
* 短信模板 相关业务
*
* @author dfr
*/
require_model('tb/TbModel');


class ErpConfigModel extends TbModel {

    public $erp_type=array(
        '0'=>'直连',
        '1'=>'奇门',
    );

    public function __construct($table = '', $db = '') {
        $table = $this->get_table();
        parent :: __construct($table);
    }

    function get_table() {
        return 'erp_config';
    }

    function get_data_list($fld = 'erp_config_id,erp_config_name') {
        $sql = "select $fld from {$this->table} ";
        $arr = $this->db->get_all($sql);
        return $arr;
    }

    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        $sql_values = array();
        $sql_main = "FROM {$this->table} WHERE 1";

        if (isset($filter['erp_config_name']) && $filter['erp_config_name'] != '') {
            $sql_main .= " AND erp_config_name LIKE :erp_config_name";
            $sql_values[':erp_config_name'] = $filter['erp_config_name'] . '%';
        }

        $select = '*';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$val) {
            $val['erp_type_name'] = $this->erp_type[$val['erp_type']];
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        $arr = $this->get_row(array('erp_config_id' => $id));
        return $arr;
    }
     /**
     * 修改纪录
     */
    function update($supplier, $id) {
        $status = $this->valid($supplier, true);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        $ret = $this->get_row(array('erp_config_id' => $id));
        if ($supplier['erp_config_name'] != $ret['data']['erp_config_name']) {
            $ret = $this->is_exists($supplier['erp_config_name'], 'erp_config_name');
            if ($ret['status'] > 0 && !empty($ret['data'])) {
                return $this->format_ret('erp_config_error_unique_name');
            }
        }

        $ret = parent::update($supplier, array('erp_config_id' => $id));
        return $ret;
    }

   private function is_exists($value, $field_name = 'erp_config_name') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }
    
    
 	/**
     * 添加新纪录
     */
    function insert($supplier) {
        $status = $this->valid($supplier);
        if ($status < 1) {
            return $this->format_ret($status);
        }

//        $ret = $this->is_exists($supplier['supplier_code']);
//        if ($ret['status'] > 0 && !empty($ret['data'])) {
//            return $this->format_ret('sms_supplier_error_unique_code');
//        }

//        $ret = $this->is_exists($supplier['erp_config_name'], 'erp_config_name');
//        if ($ret['status'] > 0 && !empty($ret['data'])) {
//            return $this->format_ret('erp_config_error_unique_name');
//        }

        return parent::insert($supplier);
    }


    /**
    * 删除记录
    */
    function delete($id) {
        $erp = $this->get_row(array('erp_config_id' => $id));
        $erp_info = $erp['data'];
        //删除中间表下载的档案数据 erp_system 0:BSERP2,1:BS3000J,2:BSERP3
        if ($erp_info['erp_system'] == 1) {
            $this->delete_erp3000_relation($id);
        } else {
            $this->delete_erp_relation($id);
        }
        $ret = parent :: delete(array('erp_config_id' => $id));
        $this->delete_exp('sys_api_fx', array('p_id' => $id));
        $this->update_erp_menu();
        return $ret;
    }

    /**
     * 清除缓存表
     * @param $id
     * @return array
     */
    function delete_cache($id) {
        $erp = $this->get_row(array('erp_config_id' => $id));
        $erp_info = $erp['data'];
        //删除中间表下载的档案数据 erp_system 0:BSERP2,1:BS3000J,2:BSERP3
        if ($erp_info['erp_system'] == 1) {
            $this->delete_erp3000_relation($id);
        } else {
            $this->delete_erp_relation($id);
        }

        //添加操作日志
        $module = '系统集成'; //模块名称
        $operate_type = '删除';
        $yw_code = $erp_info['erp_config_name'];
        $operate_xq = "清除ERP配置：{$erp_info['erp_config_name']}的缓存表数据";
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_xq' => $operate_xq, 'operate_type' => $operate_type);
        load_model('sys/OperateLogModel')->insert($log);
        return $this->format_ret(1, '', '清除成功！');
    }



    /**
     * erp2,erp3中间表
     * @param $id
     */
    function delete_erp_relation($id) {
        $parmas = array('barcode', 'brands', 'categories', 'colors', 'item', 'item_color', 'item_quantity', 'item_size', 'seasons', 'sizes');
        foreach ($parmas as $value) {
            $table = "api_bserp_" . $value;
            $ret = $this->delete_exp($table, array('erp_config_id' => $id));
        }
    }

    /**
     * 3000中间表
     * @param $id
     */
    function delete_erp3000_relation($id) {
        $parmas = array('barcode', 'brands', 'categories', 'colors', 'item', 'item_color', 'item_quantity', 'item_size', 'seasons', 'sizes','years');
        foreach ($parmas as $value) {
            $table = "api_bs3000j_" . $value;
            $ret = $this->delete_exp($table, array('erp_config_id' => $id));
        }
    }


    function update_active($active, $id) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('error_params');
        }
        $ret = parent::update(array('is_active' => $active), array('id' => $id));
        return $ret;
    }
    
    function add_info($request){
    	$this->begin_trans();
    	$erp_param['api_key'] = $request['erp_key'];
    	$erp_param['api_url'] = $request['erp_address'];
    	$erp_key = json_encode($erp_param);
    	$data = get_array_vars($request, array('erp_config_name', 'erp_system', 'erp_key','erp_address', 'upload_type', 'manage_stock', 'item_infos_download','online_time','trade_sync','erp_type'));
        $is_exist = $this->is_exists($data['erp_config_name']);
        if(!empty($is_exist['data'])){
            return $this->format_ret(-1, '', $data['erp_config_name'] . '，已存在此配置名');
        }
    	$data['erp_params'] = $erp_key;

        //选择奇门对接方式
        if ($data['erp_type'] == 1) {
            $data['target_key'] = $request['target_key'];
            $data['customer_id'] = $request['customer_id'];
        }
    	$ret = $this->insert($data);
    	
    	$store_data = array();
        $fx_data = array();
    	$store_arr = array();
    	$shop_arr = array();
        $fx_arr = array();
    	foreach ($request['shop'] as $key => $value ){
    		$data1['shop_store_code'] = $value['shop_store_code'];
    		$data1['outside_code'] = $value['outside_code'];
                $data1['o2o_store'] = $value['o2o_store'];
    		$data1['update_stock'] = 1;
    		$data1['shop_store_type'] = 0;
    		$data1['outside_type'] = 0;
    		$data1['p_id'] = $ret['data'];
    		$data1['p_type'] = 0;
    		$store_data[] = $data1;
    		$shop_arr[] = $value['shop_code'];
    	}
    	$ret_check = load_model('sys/ShopStoreModel')->check_shop($shop_arr, 0);
    	 
    	if(!empty($ret_check['data'])){
    		$this->rollback();
    		$msg = "";
    		foreach($ret_check['data'] as $val){
    			$msg .= $val['store_name'].",";
    		}
    		$msg = substr($msg, 0,-1);
    		return $this->format_ret(-1,'',$msg." 店铺已经被使用");
    	}
    	foreach ($request['store'] as $key => $value ){
    		$data1['shop_store_code'] = $value['shop_store_code'];
                $data1['o2o_store'] = $value['o2o_store'];
    		$data1['outside_code'] = $value['outside_code'];
    		$data1['update_stock'] = isset($value['update_stock'])?$value['update_stock']:0;//更新库存
    		$data1['shop_store_type'] = 1;
    		$data1['outside_type'] = 1;
    		$data1['p_type'] = 0;
    		$data1['p_id'] = $ret['data'];
    		$store_data[] = $data1;
    	}
    	$ret_check = load_model('sys/ShopStoreModel')->check_store($store_arr,0);
    	
    	if(!empty($ret_check['data'])){
    		$this->rollback();
    		$msg = "";
    		foreach($ret_check['data'] as $val){
    			$msg .= $val['store_name'].",";
    		}
    		$msg = substr($msg, 0,-1);
    		return $this->format_ret(-1,'',$msg." 仓库已经被使用");
    	}
        //分销商数据写入
    	foreach ($request['fx'] as $key => $value) {
            if(empty($value['custom_code']) || empty($value['outside_code'])){
                continue;
            }
            $data2['custom_code'] = $value['custom_code'];
            $data2['outside_code'] = $value['outside_code'];
            $data2['p_id'] = $ret['data'];
            $fx_data[] = $data2;
            $fx_arr[] = $value['custom_code'];
        }
        if(!empty($fx_arr) && !empty($fx_data)){
            $sql_values = array();
            $fx_str = $this->arr_to_in_sql_value($fx_arr, 'custom_code', $sql_values);
            $sql = "SELECT custom_code FROM  sys_api_fx WHERE custom_code IN($fx_str)";
            $data = $this->db->get_all($sql,$sql_values);
            if (!empty($data['data'])) {
                $this->rollback();
                $msg = "";
                foreach ($data['data'] as $val) {
                    $msg .= $val['custom_name'] . ",";
                }
                $msg = substr($msg, 0, -1);
                return $this->format_ret(-1, '', $msg . " 分销商已存在");
            }
            if(!empty($fx_data)){
                $fx_ret = $this->insert_multi_exp('sys_api_fx', $fx_data);
                if($fx_ret['status'] != 1){
                    $this->rollback();
                    return $this->format_ret(-1, '', "添加分销商数据失败");
                }
            }
        }
    	$result = load_model('sys/ShopStoreModel')->insert_multi($store_data);  
        $this->commit();
    	 $this->update_erp_menu();
    	return $ret;
    	
    	
    }
    function edit_info($request){
    	$this->begin_trans();
    	$erp_param['api_key'] = $request['erp_key'];
    	$erp_param['api_url'] = $request['erp_address'];
    	$erp_key = json_encode($erp_param);
    	$data = get_array_vars($request, array('erp_config_name','erp_key', 'erp_address', 'upload_type', 'manage_stock', 'item_infos_download','online_time','trade_sync','erp_type'));
    	$data['erp_params'] = $erp_key;

    	//选择奇门
        if ($data['erp_type'] == 1) {
            $data['target_key'] = $request['target_key'];
            $data['customer_id'] = $request['customer_id'];
        } else {
            $data['target_key'] = '';
            $data['customer_id'] = '';
        }
    	$ret = $this->update($data, $request['erp_config_id']);
    	$result = load_model('sys/ShopStoreModel')->delete_store_config($request['erp_config_id'],0);
    	$store_data = array();
    	$store_arr = array();
    	$shop_arr = array();
    	foreach ($request['shop'] as $key => $value ){
    		$data1['shop_store_code'] = $value['shop_store_code'];
    		$data1['outside_code'] = $value['outside_code'];
                $data1['o2o_store'] = $value['o2o_store'];
    		$data1['update_stock'] = 1;
    		$data1['shop_store_type'] = 0;
    		$data1['outside_type'] = 0;
    		$data1['p_id'] = $request['erp_config_id'];
    		$data1['p_type'] = 0;
    		$store_data[] = $data1;
    		$shop_arr[] = $value['shop_code'];
    	}
    	$ret_check = load_model('sys/ShopStoreModel')->check_shop($shop_arr);
    
    	if(!empty($ret_check['data'])){
    		$this->rollback();
    		$msg = "";
    		foreach($ret_check['data'] as $val){
    			$msg .= $val['store_name'].",";
    		}
    		$msg = substr($msg, 0,-1);
    		return $this->format_ret(1,'',$msg." 店铺已经被使用");
    	}
    	foreach ($request['store'] as $key => $value ){
    		$data1['shop_store_code'] = $value['shop_store_code'];
    		$data1['outside_code'] = $value['outside_code'];
                $data1['o2o_store'] = $value['o2o_store'];
    		$data1['update_stock'] = isset($value['update_stock'])?$value['update_stock']:0;//更新库存
    		$data1['shop_store_type'] = 1;
    		$data1['outside_type'] = 1;
    		$data1['p_type'] = 0;
    		$data1['p_id'] = $request['erp_config_id'];
    		$store_data[] = $data1;
    	}
    	$ret_check = load_model('sys/ShopStoreModel')->check_store($store_arr,0);
    	if(!empty($ret_check['data'])){
    		$this->rollback();
    		$msg = "";
    		foreach($ret_check['data'] as $val){
    			$msg .= $val['store_name'].",";
    		}
    		$msg = substr($msg, 0,-1);
    		return $this->format_ret(1,'',$msg." 仓库已经被使用");
    	}
        $this->delete_exp('sys_api_fx', array('p_id' => $request['erp_config_id']));
        foreach ($request['fx'] as $key => $value) {
            if(empty($value['custom_code']) || empty($value['outside_code'])){
                continue;
            }
            $data2['custom_code'] = $value['custom_code'];
            $data2['outside_code'] = $value['outside_code'];
            $data2['p_id'] = $request['erp_config_id'];
            $fx_data[] = $data2;
            $fx_arr[] = $value['custom_code'];
        }
        $new_fx_arr = array_unique($fx_arr, SORT_REGULAR);
        if(count($new_fx_arr) != count($fx_arr)){
            $this->rollback();
    		return $this->format_ret(-1,'',"不能填入相同的系统分销商名称");
        }
        if(!empty($fx_data)){
            $update_str = " custom_code = VALUES(custom_code), outside_code = VALUES(outside_code) ";
            $this->insert_multi_duplicate('sys_api_fx', $fx_data, $update_str);
        }
    	$result = load_model('sys/ShopStoreModel')->insert_multi($store_data);
    	$this->commit();
        $this->update_erp_menu();
    	return $ret;
    	 
    	 
    }
    //接口测试
    function test($request){
        set_time_limit(0);
    	$erp_param['api_key'] = $request['erp_key'];
    	$erp_param['api_url'] = $request['erp_address'];
    	$erp_key = json_encode($erp_param);
    	$data = get_array_vars($request, array( 'erp_system', 'erp_key','erp_address'));
    	$data['erp_params'] = $erp_key;
    	$data['erp_config_name'] = 'test';
    	$ret = $this->insert($data);
    	$params = array('erp_config_id'=>$ret['data']);
    	if ($request['erp_system'] == 1){
    		$result = load_model('sys/EfastApiModel')->request_api('bs3000j_api/test', $params);
    	} else {
    		$result = load_model('sys/EfastApiModel')->request_api('bserp_api/test', $params);
    	}
    	
    	if($result['resp_data']['code'] == '0'){
    		$ret['status'] = '1';
    		$ret['message'] = '测试成功';
    	}else{
    		$ret['status'] = '-1';
    		$ret['message'] = '测试失败 '.$result['resp_data']['msg'];
    		
    	}
    	$this->delete($ret['data']);
    	return $ret;
    }
    function update_erp_menu(){
//            "update sys_action set status=0 where    action_id in ('12010000','12030000');",
//    "update sys_action set status=1  where action_id='12010000' AND (select count(1) from erp_config where erp_system=0)>0;",
//    "update sys_action set status=1  where action_id='12030000' AND (select count(1) from erp_config where erp_system=1)>0;",
        $sql = "select erp_system from erp_config ";
        $data = $this->db->get_all($sql);
        $action_arr[0] = array(
         'status'=>0,
         'action_id'=>'12020000',
        );
         $action_arr[1] = array(
           'status'=>0,
            'action_id'=>'12030000'
        );
        $action_arr[2] = array(
            'status'=>0,
            'action_id'=>'12040100'
        );
        foreach($data as $val){
            $erp_system = $val['erp_system'] == 2 ? 0 : $val['erp_system'];
            $action_arr[$erp_system]['status'] = 1;
            if ($val['erp_system'] == 2) {
                $action_arr[2]['status'] = 1;
            }
        }
        
        foreach($action_arr as $action){
            load_model('sys/PrivilegeModel')->update_status($action['action_id'],$action['status']);
        } 
        return $this->format_ret(1);
                    
    }
   
            
    function get_by_pid($pid){
        $sql = "SELECT api_fx_id,custom_code,outside_code FROM `sys_api_fx` WHERE `p_id` = :pid";
        $sql_values = array(':pid' => $pid);
        $data = $this->db->get_all($sql, $sql_values);
       return $data;
    }
}
    