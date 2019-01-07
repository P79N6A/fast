<?php
/**
* 短信模板 相关业务
*
* @author dfr
*/
require_model('tb/TbModel');


class SapConfigModel extends TbModel {
    function get_table() {
        return 'sap_config';
    }


    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        $sql_values = array();
        $sql_main = "FROM {$this->table} WHERE 1";

        if (isset($filter['sap_config_name']) && $filter['sap_config_name'] != '') {
            $sql_main .= " AND sap_config_name LIKE :sap_config_name";
            $sql_values[':sap_config_name'] = $filter['sap_config_name'] . '%';
        }

        $select = '*';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        $arr = $this->get_row(array('sap_config_id' => $id));
        return $arr;
    }
    
    function get_by_sap_config() {
        $sql = "SELECT * FROM sap_config where 1";
        $ret = $this->db->get_row($sql);
        return $ret;
    }
     /**
     * 修改纪录
     */
    function update($supplier, $id) {
        $status = $this->check_sap($supplier,$id);
         if ($status['status'] < 1) {
            return $status;
        }
        $this->splicing_data($supplier,$params,$id);
        $ret = parent::update($supplier, array('sap_config_id' => $id));
        if($ret['status'] == 1) {
            foreach ($params as &$val) {
                $val['p_id'] = $id;
            }
            $this->delete_exp('sys_api_shop_store', array('p_id' => $id,'p_type' => 3));
            $this->insert_multi_exp('sys_api_shop_store', $params, true);
        }
        return $ret;
    }

   
    function check_sap($supplier,$id = '') {
        $id_str = '';
        if($id != '') {
            $id_str = " AND sap_config_id != {$id}";
        }
        $sql_value = array();
        $sql = "SELECT * FROM sap_config WHERE sap_config_name=:sap_config_name ";
        $sql_value[':sap_config_name'] = $supplier['sap_config_name'];
        $sql .= $id_str;
        $ret = $this->db->get_row($sql,$sql_value);
        if(!empty($ret)) {
            return $this->format_ret(-1,'','配置名称重复');
        }
        return $this->format_ret(1,'','配置名称重复');
    }
    
    //组合数据
    function splicing_data (&$supplier,&$params,$id = '') {
        $efast_store_code = '';
        $sap_store_code = '';
        $efast_shop_code = '';
        $sap_shop_code = '';
        foreach($supplier['store'] as $val) {
            $efast_store_code[] = $val['efast_store_code'];
            $sap_store_code[] = $val['sap_store_code'];
            $params[] = array(
                'p_type' => 3,
                'shop_store_code' => $val['efast_store_code'],
                'shop_store_type' => 1,
                'outside_type' => 1,
                'outside_code' => $val['sap_store_code']
            ); 
        }
        foreach($supplier['shop'] as $val) {
            $efast_shop_code[] = $val['efast_shop_code'];
            $sap_shop_code[] = $val['sap_shop_code'];
            $params[] = array(
                'p_type' => 3,
                'shop_store_code' => $val['efast_shop_code'],
                'shop_store_type' => 0,
                'outside_type' => 0,
                'outside_code' => $val['sap_shop_code']
            );
        }
        $supplier['efast_store_code'] = implode(",", $efast_store_code);
        $supplier['sap_store_code'] = implode(",", $sap_store_code);
        $supplier['efast_shop_code'] = implode(",", $efast_shop_code);
        $supplier['sap_shop_code'] = implode(",", $sap_shop_code);
        unset($supplier['store']);
        unset($supplier['shop']);
    }
    
 	/**
     * 添加新纪录
     */
    function insert($supplier) {
        $status = $this->check_sap($supplier);
        if ($status['status'] < 1) {
            return $status;
        }
//        var_dump($supplier);die;
        $params = array();
        $this->splicing_data($supplier,$params);
        
        $ret = parent::insert($supplier);
        if($ret['status'] == 1) {
            foreach ($params as &$val) {
                $val['p_id'] = $ret['data'];
            }
            $this->insert_multi_exp('sys_api_shop_store', $params, true);
        }
        
        return $ret;
    }


    /**
    * 删除记录
    */
    function delete($id) {
        $this->delete_exp('sys_api_shop_store', array('p_id' => $id,'p_type' => '3'));
        $ret = parent :: delete(array('sap_config_id' => $id));
        return $ret;
    }
    
    //接口测试
    function test($request){
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
}
    