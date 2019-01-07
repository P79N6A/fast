<?php
/**
* 短信模板 相关业务
*
* @author dfr
*/
require_model('tb/TbModel');

require_lang('sys');
class WmsConfigModel extends TbModel {
    
    private $_not_exist_msg = array();

    public function __construct($table = '', $db = '') {
        $table = $this->get_table();
        parent :: __construct($table);
    }

    function get_table() {
        return 'wms_config';
    }

    function get_data_list($fld = 'wms_config_id,wms_config_name') {
        $sql = "select $fld from {$this->table} ";
        $arr = $this->db->get_all($sql);
        return $arr;
    }
    
    function add_info($request){
        $params = require_conf('sys/wms_params');
        $extra_params = array();
        if(isset($params[$request['wms_system_code']])){
            $conf = $params[$request['wms_system_code']];
            for($i=1;$i<20;$i++){
                if(isset($request['param'.$i])){
                    $key = $request['param'.$i];
                    if(isset($conf[$key])){
                       if($request['param'.$i."_val"] <> ''){
                       		$extra_params[$key] = isset($request['param'.$i."_val"])?$request['param'.$i."_val"]:'';
                       }
                    }else{
                        break;
                    }
                }
            }
        }
        $extra_params['notice_iwms'] = isset($request['notice_iwms']) ? $request['notice_iwms'] : 0;
        $extra_params['effect_inv_type'] = isset($request['effect_inv_type'])?$request['effect_inv_type']:0;
        $extra_params['goods_upload_type'] = isset($request['goods_upload_type']) ? $request['goods_upload_type'] : 0;
        $pattern = "/^([0-1][0-9]|[2][0-3])(:)([0-5][0-9])$/";
        if(isset($request['wms_cut_time'])&&$request['wms_cut_time']!=''&&!preg_match($pattern, $request['wms_cut_time'])){
            return $this->format_ret(-1, '', '时间格式为：17:00<br/><span style="color:red">注:（":"为英文字符）</span>');
         }
         if($request['wms_system_code'] == 'jdwms') {
           $api = load_model('base/ShopApiModel')->get_shop_extra_params($extra_params['shop']);
           $extra_params['app_key'] = isset($api['data']['app_key']) ? $api['data']['app_key'] : '';
           $extra_params['app_secret'] = isset($api['data']['app_key']) ? $api['data']['app_secret'] : '';
           $extra_params['access_token'] = isset($api['data']['session']) ? $api['data']['session'] : '';
        }
        $extra_params['wms_cut_time'] = $request['wms_cut_time'];
        $wms_key = json_encode($extra_params);
        $data = get_array_vars($request, array('wms_config_name', 'wms_system_code','item_sync','wms_prefix'));
        $data['wms_address'] = $extra_params['URL'];     
        $data['wms_params'] = $wms_key;
        if($data['wms_system_code'] == 'qimen'){
            $data['wms_system_type'] = isset($request['wms_system_type']) ? $request['wms_system_type'] : '';
        }
        $data['wms_prefix'] = strtoupper(trim($data['wms_prefix']));
        $this->begin_trans();
        $ret = $this->insert($data);
        $store_data = array();
        $store_arr = array();
		foreach ($request['store'] as $key => $value ){
			$data1['outside_code'] = $value['outside_code'];
			$data1['shop_store_code'] =  $value['shop_store_code'];
                        $data1['store_type'] =  isset($value['store_type'])?$value['store_type']:1;
			if(empty($data1['outside_code']) || empty($data1['shop_store_code'])){
                            $this->rollback();
                            return $this->format_ret(-1,'',"仓库不能为空");
                        }
			$data1['shop_store_type'] = 1;
			$data1['outside_type'] = 1;
                        $data1['p_type'] = 1;
			$data1['p_id'] = $ret['data'];
                        
                        
                        
                        $store_data[] = $data1;
                        $store_arr[] = $value['shop_store_code'];
		}
        $ret_check = load_model('sys/ShopStoreModel')->check_store($store_arr);
         
        if(!empty($ret_check['data'])){
            $this->rollback();
            $msg = "";
            foreach($ret_check['data'] as $val){
              $msg .= $val['store_name'].",";
            }
            $msg = substr($msg, 0,-1);
            return $this->format_ret(-1,'',$msg." 仓库已经被使用");
        }
        
        $result = load_model('sys/ShopStoreModel')->insert_multi($store_data);  
        
        if(isset($request['shop']) && !empty($request['shop'])) {
            foreach ($request['shop'] as $key => $value ){
                $data1['outside_code'] = $value['outside_code'];
                $data1['shop_store_code'] =  $value['shop_store_code'];
                $data1['store_type'] =  isset($value['store_type']) ? $value['store_type'] : 1;
                if(empty($data1['outside_code']) || empty($data1['shop_store_code'])){
                    $this->rollback();
                    return $this->format_ret(-1,'',"店铺不能为空");
                }
                $data1['shop_store_type'] = 0;
                $data1['outside_type'] = 0;
                $data1['p_type'] = 1;
                $data1['p_id'] = $ret['data'];
                $shop_data[] = $data1;
                $shop_arr[] = $value['shop_store_code'];
            }
            $ret_check = load_model('sys/ShopStoreModel')->check_shop($shop_arr, 1);
         
            if(!empty($ret_check['data'])){
                $this->rollback();
                $msg = "";
                foreach($ret_check['data'] as $val){
                  $msg .= $val['shop_name'].",";
                }
                $msg = substr($msg, 0,-1);
                return $this->format_ret(-1,'',$msg." 店铺已经被使用");
            }
            $result = load_model('sys/ShopStoreModel')->insert_multi($shop_data);  
        }

        $this->commit();
        
        return $ret;
    }
    
    function edit_info($request){
    	$params = require_conf('sys/wms_params');
        $extra_params = array();
        if(isset($params[$request['wms_system_code']])){
            $conf = $params[$request['wms_system_code']];
            for($i=1;$i<20;$i++){
                if(isset($request['param'.$i])){
                    $key = $request['param'.$i];
                    if(isset($conf[$key])){
                       if($request['param'.$i."_val"] <> ''){
                       		$extra_params[$key] = isset($request['param'.$i."_val"])?$request['param'.$i."_val"]:'';
                       }
                    }else{
                        break;
                    }
                }
            }
        }
        $pre_config = $this->db->get_value("SELECT wms_params FROM wms_config WHERE wms_config_id = :wms_config_id", array(":wms_config_id" => $request['wms_config_id']));
        $pre_config_arr = json_decode($pre_config, 512, JSON_BIGINT_AS_STRING);
        $extra_params['notice_iwms'] = isset($request['notice_iwms']) ? $request['notice_iwms'] : 0;
        $extra_params['effect_inv_type'] = isset($request['effect_inv_type'])?$request['effect_inv_type']:0;
        $extra_params['wms_cut_time']=isset($request['wms_cut_time'])?$request['wms_cut_time']:'';
        $extra_params['goods_upload_type'] = isset($pre_config_arr['goods_upload_type']) ? $pre_config_arr['goods_upload_type'] : 0;
        if($request['wms_system_code'] == 'jdwms') {
           $api = load_model('base/ShopApiModel')->get_shop_extra_params($extra_params['shop']);
           $extra_params['app_key'] = isset($api['data']['app_key']) ? $api['data']['app_key'] : '';
           $extra_params['app_secret'] = isset($api['data']['app_key']) ? $api['data']['app_secret'] : '';
           $extra_params['access_token'] = isset($api['data']['session']) ? $api['data']['session'] : '';
        }
        $wms_key = json_encode($extra_params);
        $data = get_array_vars($request, array('wms_config_name', 'wms_system_code','item_sync','wms_prefix'));
        $data['wms_address'] = $extra_params['URL'];
        $data['wms_params'] = $wms_key;
        if($data['wms_system_code'] == 'qimen'){
            $data['wms_system_type'] = isset($request['wms_system_type']) ? $request['wms_system_type'] : '';
        }
        $data['wms_prefix'] = strtoupper(trim($data['wms_prefix']));
        $this->begin_trans();
        $ret = $this->update($data, $request['wms_config_id']);
        $store_data = array();
        $store_arr = array();
        $result = load_model('sys/ShopStoreModel')->delete_store_config($request['wms_config_id'],1);
       
		foreach ($request['store'] as $key => $value ){
			$data1['outside_code'] = $value['outside_code'];
			$data1['shop_store_code'] =  $value['shop_store_code'];
                        $data1['store_type'] =  isset($value['store_type'])?$value['store_type']:1;
			if(empty($data1['outside_code']) || empty($data1['shop_store_code'])){
                $this->rollback();
                return $this->format_ret(-1,'',"仓库不能为空");
            }
			$data1['shop_store_type'] = 1;
			$data1['outside_type'] = 1;
                        $data1['p_type'] = 1;
			$data1['p_id'] = $request['wms_config_id'];
                        $store_data[] = $data1;
                        $store_arr[] = $value['shop_store_code'];
		}
                 $ret_check = load_model('sys/ShopStoreModel')->check_store($store_arr);
         
             if(!empty($ret_check['data'])){
                  $this->rollback();
                  $msg = "";
                  foreach($ret_check['data'] as $val){
                      $msg .= $val['store_name'].",";
                  }
                  $msg = substr($msg, 0,-1);
                  return $this->format_ret(-1,'',$msg." 仓库已经被使用");
             }

            $result = load_model('sys/ShopStoreModel')->insert_multi($store_data);
            
        if(isset($request['shop']) && !empty($request['shop'])) {
            foreach ($request['shop'] as $key => $value ){
                $data1['outside_code'] = $value['outside_code'];
                $data1['shop_store_code'] =  $value['shop_store_code'];
                $data1['store_type'] =  isset($value['store_type']) ? $value['store_type'] : 1;
                if(empty($data1['outside_code']) || empty($data1['shop_store_code'])){
                    $this->rollback();
                    return $this->format_ret(-1,'',"店铺不能为空");
                }
                $data1['shop_store_type'] = 0;
                $data1['outside_type'] = 0;
                $data1['p_type'] = 1;
                $data1['p_id'] = $request['wms_config_id'];
                $shop_data[] = $data1;
                $shop_arr[] = $value['shop_store_code'];
            }
            $ret_check = load_model('sys/ShopStoreModel')->check_shop($shop_arr, 1);
         
            if(!empty($ret_check['data'])){
                $this->rollback();
                $msg = "";
                foreach($ret_check['data'] as $val){
                  $msg .= $val['shop_name'].",";
                }
                $msg = substr($msg, 0,-1);
                return $this->format_ret(-1,'',$msg." 店铺已经被使用");
            }
            $result = load_model('sys/ShopStoreModel')->insert_multi($shop_data);  
        }
            $this->commit();
                 
        return $ret;
    }
    
    
    
    
    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        $sql_values = array();
        $sql_main = "FROM {$this->table} WHERE 1";

        if (isset($filter['wms_config_name']) && $filter['wms_config_name'] != '') {
            $sql_main .= " AND wms_config_name LIKE :wms_config_name";
            $sql_values[':wms_config_name'] = $filter['wms_config_name'] . '%';
        }

        $select = '*';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }
        
    function get_by_id($id) {
        $arr = $this->get_row(array('wms_config_id' => $id));
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

        $ret = $this->get_row(array('wms_config_id' => $id));
        if ($supplier['wms_config_name'] != $ret['data']['wms_config_name']) {
            $ret = $this->is_exists($supplier['wms_config_name'], 'wms_config_name');
            if ($ret['status'] > 0 && !empty($ret['data'])) {
                return $this->format_ret('wms_config_error_unique_name');
            }
        }

        $ret = parent::update($supplier, array('wms_config_id' => $id));
        return $ret;
    }

   private function is_exists($value, $field_name = 'wms_config_name') {
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

        $ret = $this->is_exists($supplier['wms_config_name'], 'wms_config_name');
        if ($ret['status'] > 0 && !empty($ret['data'])) {
            return $this->format_ret('wms_config_error_unique_name');
        }

        $ret = parent::insert($supplier);
        return $ret;
    }


    /**
    * 删除记录
    */
    function delete($id) {
        $ret = parent :: delete(array('wms_config_id' => $id));
        return $ret;
    }
    
    
    function get_wms_type(){
       $conf = require_conf('sys/wms');
       $value_arr = $this->get_no_values();
       foreach($conf as $key=>$val){
           if(in_array($key, $value_arr)){
               unset($conf[$key]);
           }
       }
       return $conf;
    }
    function get_select(){
        $conf = $this->get_wms_type();
        $arr = array();
        foreach($conf as $key=>$val){
          $arr[] = array($key,$val['name']);  
        }
        return $arr;
    }
    
    
    function get_wms_conf($wms_type){
        $conf = $this->get_wms_type();
         if(isset($conf[$wms_type])){
             return $conf[$wms_type];
         }else{
             return array();
         }
    }
    
    function get_no_values(){
            require_model('common/ServiceModel');//正在服务
            $serviceModel = new ServiceModel();
            return $serviceModel->get_value_no_auth(3);
    }
    
    public function get_wms_system($wms_config_id){
    	$sql = "select wms_params from {$this->table} where wms_config_id='{$wms_config_id}'";
    	$wms_params = $this->db->getOne($sql);
    	$wms_params = json_decode($wms_params,true);
    	return $this->format_ret(1,$wms_params);
    }
    
    function get_wms_store_all(){
        $sql = "select shop_store_code from wms_config w 
            INNER JOIN sys_api_shop_store s ON w.wms_config_id=s.p_id
           where s.p_type=1 ";
          $data = $this->db->get_all($sql);
          $store_arr = array();
             foreach($data as $val){
                 $store_arr[]= $val['shop_store_code'];
             }   
             return $store_arr;
    }
    
    function get_store_code_by_out_code($wms_code, $outside_code) {
        $sql = "SELECT shop_store_code from wms_config w
            INNER JOIN sys_api_shop_store s ON w.wms_config_id = s.p_id
            where w.wms_system_code=:wms_system_code AND s.outside_code=:outside_code AND  s.p_type=1";
        return $this->db->get_value($sql, array(':wms_system_code' => $wms_code, ':outside_code' => $outside_code));
    }
    
    /**
     * 商品上传
     * @param int $wms_config_id wms配置id
     * @param source $file 上传的文件
     */
    function do_imoprt_goods($wms_config_id, $file){
        $data = $this->read_csv($file);
        $chunk_arr = array_chunk($data, 3000);
        foreach ($chunk_arr as $chunk) {
            $this->do_imoprt_goods_action($wms_config_id, $chunk);
        }
        $wrong_barcode = $this->_not_exist_msg;
        if(empty($wrong_barcode)) {
            return $this->format_ret(1);
        } else {
            $top=array('商品条形码','导入失败原因');
            $message = '导入成功：' . (count($data) - count($wrong_barcode));
            $message .= '；' . '失败数量：' . count($wrong_barcode);
            $message .=$this->create_fail_file($wrong_barcode, $top, 'import_failed_barcode');
            return $this->format_ret(-1, '', $message);
        }
        
    }
    
    function do_imoprt_goods_action($wms_config_id, $data){
        $i= 0;
        $sql_values = array();
        $barcode_str = $this->arr_to_in_sql_value($data, 'barcode', $sql_values);
        //获取goods_sku表存在的barcode
        $sql = "SELECT sku, barcode FROM goods_sku WHERE barcode IN({$barcode_str})";
        $exist_sku  = $this->db->get_all($sql, $sql_values);
        $exist_barcode_arr = array_column($exist_sku, 'barcode');

        foreach ($data as $import_barcode) {
            if(!in_array($import_barcode, $exist_barcode_arr)) {
                $this->_not_exist_msg[$i]['barcode'] = $import_barcode;
                $this->_not_exist_msg[$i]['error_msg'] = '商品条码不存在';
                $i++;
            }
        }
        
        if(!empty($exist_barcode_arr)) {
            $this->import_goods($wms_config_id, $exist_sku);
        }
           
    }
    
    /**
     * @todo 读取文件
     */
    function read_csv($file) {
        $open_file = fopen($file, "r");
        $i = 0;
        $start_line = 1;
        $total_barcode = array();
        while (!feof($open_file)) {
            $row = fgetcsv($open_file);
            if($i >= $start_line) {//第二行读起
                if (!empty($row)) {
                    $total_barcode[] = $row[0];
                }
            }
            $i++;
        }
        return $total_barcode;
    }
    
    /**
     * @todo 导入商品
     */
    function import_goods($wms_config_id, $barcode_arr){
        $config_arr = array();
        foreach ($barcode_arr as $key =>$value) {
            $config_arr[$key]['barcode'] =  $value['barcode'];
            $config_arr[$key]['sku'] =  $value['sku'];
            $config_arr[$key]['wms_config_id'] = $wms_config_id;
        }
        $update_str = " barcode=VALUES(barcode), sku=VALUES(sku)";
        $ret = $this->insert_multi_duplicate('wms_custom_goods_sku', $config_arr, $update_str);
        return $ret;
    }
    
    /**
     * @todo 下发商品页检索
     */
    function get_goods_config($filter) {
        $sql_values = array();
        $sql_main = "FROM wms_custom_goods_sku AS wms,goods_sku AS g WHERE 1 AND wms.barcode=g.barcode";

        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sql_main .= " AND wms.barcode LIKE '%{$filter['barcode']}%'";
        }
        if (isset($filter['wms_config_id']) && $filter['wms_config_id'] != '') {
            $sql_main .= " AND wms.wms_config_id = :wms_config_id";
            $sql_values['wms_config_id'] = $filter['wms_config_id'];
        }

        $select = 'wms.*, g.sku';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$sub_data) {
            $key_arr = array('goods_code', 'goods_name');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($sub_data['sku'], $key_arr);

            $sub_data = array_merge($sku_info, $sub_data);
        }

        $ret_data = $data;
        return $this->format_ret(1, $ret_data);
    }   
    
    function do_delete_goods($param) {
        return $this->delete_exp('wms_custom_goods_sku', $param);   
    }
    
     /**
     * 创建导出信息csv文件
     * @param array $msg 信息数组
     * @return string 文件地址
     */
    private function create_fail_file($msg, $fail_top, $name) {
        require_lib('csv_util');
        $csv_obj = new execl_csv();
        $file_name = $csv_obj->create_fail_csv_files($fail_top, $msg, $name);
//        $message = "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name={$name}\" >下载</a>";
        $url = set_download_csv_url($file_name,array('export_name'=>'error'));
        $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        return $message;
    }

    /**
     * 根据wms类型获取wms配置
     * @param array $wms_system_code
     * @return array
     */
    public function get_wms_config_select($wms_system_code) {
        if (!is_array($wms_system_code)) {
            $wms_system_code = [$wms_system_code];
        }
        $sql_values = [];
        $wms_system_code_str = $this->arr_to_in_sql_value($wms_system_code, 'wms_system_code', $sql_values);
        $sql = "SELECT wms_config_id,wms_config_name FROM {$this->table} WHERE wms_system_code IN({$wms_system_code_str})";
        $wms_config_data = $this->db->get_all($sql, $sql_values);
        $wms_config_data = array_column($wms_config_data, 'wms_config_name','wms_config_id');
        return $wms_config_data;
    }
    public function do_new_delete($param) {
        if (!isset($param['id']) || empty($param['id'])){
            return $this->format_ret(-1, '', '参数缺失');
        }
        //修改最大执行时间
        set_time_limit(0);
        //修改此次最大运行内存
        ini_set('memory_limit','1024M');
        $wms_config_id = $param['id'];
        try{
            //获取wms配置信息
            $ret = $this->get_by_id($wms_config_id);
            if($ret['status'] != 1){
                return $ret;
            }
            $wms_config_info = $ret['data'];
            //获取wms关联店铺仓库
            $ret = load_model('sys/ShopStoreModel')->get_all(array('p_id' => $wms_config_id, 'p_type'=>1));
            $shop_store_code_arr = array();//关联店铺仓库code
            if($ret['status'] == 1 && !empty($ret['data'])){
                $shop_store_code_arr = array_column($ret['data'], 'shop_store_code');
            }
            //系统单据拦截和外包仓中间表单据清空（暂时只支持百胜iWMS、胜境WMS365）
            if (in_array($wms_config_info['wms_system_code'], array('iwms','iwmscloud'))){
                $ret = $this->empty_wms_middle_table($shop_store_code_arr, $wms_config_info['wms_system_code']);
                if($ret['status'] < 0){
                    return $ret;
                }
            }
            //删除WMS配置
            $ret = $this->delete($wms_config_id);
            if($ret['status'] < 0){
                return $this->format_ret(-1, '', '删除wms配置失败');
            }
            //删除wms关联店铺仓库关系
            $ret = load_model('sys/ShopStoreModel')->delete_store_config($wms_config_id,1);
            if($ret['status'] < 0){
                return $this->format_ret(-1, '', '删除wms关联店铺仓库关系失败');
            }
        } catch (Exception $e) {
            return $this->format_ret(-1, '', '删除失败:' . $e->getMessage());
        }
        return $this->format_ret(1);
    }
    //系统单据拦截和外包仓中间表单据清空（暂时只支持奇门）
    private function empty_wms_middle_table($shop_store_code_arr, $wms_system_code) {
        if (empty($shop_store_code_arr) || empty($wms_system_code)){
           return $this->format_ret(1);
        }
        //拦截外包仓零售
        $sql_values = array();
        $sql_values[':api_product'] = $wms_system_code;
        $efast_store_code_str = $this->arr_to_in_sql_value($shop_store_code_arr, 'efast_store_code', $sql_values);
        $sql = "SELECT record_code,record_type FROM wms_oms_trade"
                . " WHERE efast_store_code IN ({$efast_store_code_str}) AND api_product = :api_product AND process_flag = 0 AND wms_order_flow_end_flag = 0"
                . " AND ((upload_response_flag in(0,20) AND cancel_request_flag=0 AND cancel_response_flag=0) OR (upload_response_flag = 10 AND cancel_response_flag<>10))";
        $wms_oms_trade_arr = $this->db->get_all($sql, $sql_values);//仅需要拦截订单
        if (!empty($wms_oms_trade_arr)){
            $record_code_arr = array();//订单
            $return_code_arr = array();//退单
            foreach ($wms_oms_trade_arr as $val) {
                if($val['record_type'] == 'sell_record'){
                    $record_code_arr[] = $val['record_code'];
                }else if($val['record_type'] == 'sell_return'){
                    $return_code_arr[] = $val['record_code'];
                }
            }
            //拦截订单
            if(!empty($record_code_arr)){
                foreach ($record_code_arr as $sell_record_code) {
                    $ret = load_model('oms/SellRecordOptModel')->opt_intercept($sell_record_code, 0, '', 1);//强制
                    if($ret['status'] < 0){
                        return $ret;
                    }
                }
            }
            //拦截售后单 ( 取消确认 )
            if(!empty($return_code_arr)){
                foreach ($return_code_arr as $sell_return_code) {
               
                    $record = load_model('oms/SellReturnModel')->get_return_by_return_code($sell_return_code);
                    if($record['finance_check_status']==1){
                        $sys_user = load_model('oms/SellRecordOptModel')->sys_user();
                        $sql = "update oms_sell_return set finance_check_status = 0, finance_reject_time=:finance_reject_time, finance_reject_person=:finance_reject_person where sell_return_code = :sell_return_code";
                        $record['finance_check_status'] = 0;
                        CTX()->db->query($sql, array(':sell_return_code' => $sell_return_code, ':finance_reject_person' => $sys_user['user_name'], ':finance_reject_time' => date('Y-m-d H:i:s')));
                        load_model('oms/SellReturnModel')->add_action($record, '财务退回');
                    }
                    $ret = load_model('oms/SellReturnOptModel')->opt_unconfirm($sell_return_code, array(), '', 1);//强制
                    if($ret['status'] < 0){
                        return $ret;
                    }
                }
            }
        }
            
        //拦截外包仓进销存 (采购单、批发单、移仓单)
        $sql_values = array();
        $sql_values[':api_product'] = $wms_system_code;
        $efast_store_code_str = $this->arr_to_in_sql_value($shop_store_code_arr, 'efast_store_code', $sql_values);
        $sql = "SELECT record_code,record_type FROM wms_b2b_trade"
                . " WHERE efast_store_code IN ({$efast_store_code_str}) AND api_product = :api_product AND process_flag = 0 AND wms_order_flow_end_flag = 0"
                . " AND ((upload_response_flag in(0,20) AND cancel_request_flag=0 AND cancel_response_flag=0) OR (upload_response_flag = 10 AND cancel_response_flag<>10))";
        $wms_b2b_trade_arr = $this->db->get_all($sql, $sql_values);//仅需要拦截进销存单据
        if (!empty($wms_b2b_trade_arr)){
            $wbm_notice_arr = array();//批发通知单
            $wbm_return_notice_arr = array();//批发退货通知单
            $pur_notice_arr = array();//采购通知单
            $pur_return_notice_arr = array();//采购退货通知单
            $shift_out_arr = array();//移仓单
            foreach ($wms_b2b_trade_arr as $val) {
                $record_code = $val['record_code'];
                switch ($val['record_type']) {
                    case 'wbm_notice':
                        $wbm_notice_arr[] = $record_code;
                        break;
                    case 'wbm_return_notice':
                        $wbm_return_notice_arr[] = $record_code;
                        break;
                    case 'pur_notice':
                        $pur_notice_arr[] = $record_code;
                        break;
                    case 'pur_return_notice':
                        $pur_return_notice_arr[] = $record_code;
                        break;
                    case 'shift_out':
                        $shift_out_arr[] = $record_code;
                        break;
                    default:
                        break;
                }
            }
            //拦截批发通知单
            if(!empty($wbm_notice_arr)){
                $sql_values_1 = array();
                $notice_record_code_str = $this->arr_to_in_sql_value($wbm_notice_arr, 'record_code', $sql_values_1);
                $sql = "SELECT notice_record_id FROM wbm_notice_record WHERE record_code IN ({$notice_record_code_str});";
                $notice_record_id_arr = $this->db->get_all_col($sql, $sql_values_1);
                if (!empty($notice_record_id_arr)){
                    foreach ($notice_record_id_arr as $notice_record_id) {
                        $ret = load_model('wbm/NoticeRecordModel')->update_sure(0, 'is_sure', $notice_record_id);
                        if($ret['status'] < 0){
                            return $ret;
                        }
                    }
                }
            }
            //拦截批发退货通知单
            if(!empty($wbm_return_notice_arr)){
                foreach ($wbm_return_notice_arr as $return_notice_code) {
                    $ret = load_model('wbm/ReturnNoticeRecordModel')->update_check(0, 'is_check', $return_notice_code);
                    if($ret['status'] < 0){
                        return $ret;
                    }
                }
            }
            //拦截采购通知单
            if(!empty($pur_notice_arr)){

                $sql_values_1 = array();
                $notice_record_code_str = $this->arr_to_in_sql_value($pur_notice_arr, 'record_code', $sql_values_1);
                $sql = "SELECT order_record_id FROM pur_order_record WHERE record_code IN ({$notice_record_code_str});";
                $notice_record_id_arr = $this->db->get_all_col($sql, $sql_values_1);
                if (!empty($notice_record_id_arr)){
                    foreach ($notice_record_id_arr as $notice_record_id) {
                        $ret = load_model('pur/OrderRecordModel')->update_check(0, 'is_check', $notice_record_id);
                        if($ret['status'] < 0){
                            return $ret;
                        }
                    }
                }

            }
            //拦截采购退货通知单
            if(!empty($pur_return_notice_arr)){
                $sql_values_1 = array();
                $notice_record_code_str = $this->arr_to_in_sql_value($pur_return_notice_arr, 'record_code', $sql_values_1);
                $sql = "SELECT return_notice_record_id FROM pur_return_notice_record WHERE record_code IN ({$notice_record_code_str});";
                $notice_record_ids_arr = $this->db->get_all_col($sql, $sql_values_1);
                if (!empty($notice_record_ids_arr)){
                    foreach ($notice_record_ids_arr as $notice_record_id) {
                        $ret = load_model('pur/ReturnNoticeRecordModel')->update_sure(0, 'is_sure', $notice_record_id);
                        if($ret['status'] < 0){
                            return $ret;
                        }
                    }
                }
            }
            //拦截移仓单
            if(!empty($shift_out_arr)){
                $sql_values_1 = array();
                $notice_record_code_str = $this->arr_to_in_sql_value($shift_out_arr, 'record_code', $sql_values_1);
                $sql = "SELECT shift_record_id FROM stm_store_shift_record WHERE record_code IN ({$notice_record_code_str});";
                $notice_record_id_arr = $this->db->get_all_col($sql, $sql_values_1);
                if (!empty($notice_record_id_arr)){
                    foreach ($notice_record_id_arr as $notice_record_id) {
                        $ret = load_model('stm/StoreShiftRecordModel')->update_sure(0, 'is_sure', $notice_record_id);
                        if($ret['status'] < 0){
                            return $ret;
                        }
                    }
                }
            }
        }
        //清空外包仓零售中间表
        $flag = true;
        $limit = 2000;
        do{
            $sql_values = array();
            $sql_values[':api_product'] = $wms_system_code;
            $efast_store_code_str = $this->arr_to_in_sql_value($shop_store_code_arr, 'efast_store_code', $sql_values);
            $sql = "SELECT record_code FROM wms_oms_trade WHERE efast_store_code IN ({$efast_store_code_str}) AND api_product = :api_product ORDER BY id LIMIT {$limit}";
            $wms_oms_trade_arr = $this->db->get_all_col($sql, $sql_values);//所有订单
            if(!empty($wms_oms_trade_arr)){
                $sql_values_1 = array();
                $record_code_str = $this->arr_to_in_sql_value($wms_oms_trade_arr, 'record_code', $sql_values_1);
                $sql = "DELETE FROM wms_oms_order WHERE record_code IN  ({$record_code_str})";
                $ret = $this->query($sql, $sql_values_1);
                if($ret['status'] < 0){
                    return $this->format_ret(-1, '', '清空外包仓零售中间表失败');
                }
                $sql = "DELETE FROM wms_oms_order_lof WHERE record_code IN  ({$record_code_str})";
                $ret = $this->query($sql, $sql_values_1);
                if($ret['status'] < 0){
                    return $this->format_ret(-1, '', '清空外包仓零售中间表失败');
                }
                $sql = "DELETE FROM wms_oms_trade WHERE efast_store_code IN ({$efast_store_code_str}) AND api_product = :api_product ORDER BY id LIMIT {$limit}";
                $ret = $this->query($sql, $sql_values);
                if($ret['status'] < 0){
                    return $this->format_ret(-1, '', '清空外包仓零售中间表失败');
                }
            }else{
                $flag = false;
            }
        }while ($flag);
        
        //清空外包仓进销存中间表
        $flag = true;
        do{
            $sql_values = array();
            $sql_values[':api_product'] = $wms_system_code;
            $efast_store_code_str = $this->arr_to_in_sql_value($shop_store_code_arr, 'efast_store_code', $sql_values);
            $sql = "SELECT record_code FROM wms_b2b_trade WHERE efast_store_code IN ({$efast_store_code_str}) AND api_product = :api_product ORDER BY id LIMIT {$limit}";
            $wms_b2b_trade_arr = $this->db->get_all_col($sql, $sql_values);//所有单据
            if(!empty($wms_b2b_trade_arr)){
                $sql_values_1 = array();
                $record_code_str = $this->arr_to_in_sql_value($wms_b2b_trade_arr, 'record_code', $sql_values_1);
                $sql = "DELETE FROM wms_b2b_order WHERE record_code IN  ({$record_code_str})";
                $ret = $this->query($sql, $sql_values_1);
                if($ret['status'] < 0){
                    return $this->format_ret(-1, '', '清空外包仓进销存中间表失败');
                }
                $sql = "DELETE FROM wms_b2b_order_detail WHERE record_code IN  ({$record_code_str})";
                $ret = $this->query($sql, $sql_values_1);
                if($ret['status'] < 0){
                    return $this->format_ret(-1, '', '清空外包仓进销存中间表失败');
                }
                $sql = "DELETE FROM wms_b2b_order_lof WHERE record_code IN  ({$record_code_str})";
                $ret = $this->query($sql, $sql_values_1);
                if($ret['status'] < 0){
                    return $this->format_ret(-1, '', '清空外包仓进销存中间表失败');
                }
                $sql = "DELETE FROM wms_b2b_trade WHERE efast_store_code IN ({$efast_store_code_str}) AND api_product = :api_product ORDER BY id LIMIT {$limit}";
                $ret = $this->query($sql, $sql_values);
                if($ret['status'] < 0){
                    return $this->format_ret(-1, '', '清空外包仓进销存中间表失败');
                }
            }else{
                $flag = false;
            }
        }while ($flag);
        
        return $this->format_ret(1);
    }

}
    