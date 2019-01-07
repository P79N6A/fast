<?php

require_model('tb/TbModel');
require_lib('util/oms_util', true);

class O2oEntryModel extends TbModel {

    public $o2o_conf = array();

    function __construct() {
        parent::__construct();
        $this->o2o_conf = require_conf('o2o/o2o_param');
    }

    //检查仓库和单据是否支持O2O类型 
    function is_o2o_store_record($store_code, $record_type='sell_record') {
        static $o2o_type_arr = NULL;
        if (!isset($o2o_type_arr[$store_code][$record_type])) {
            $o2o_type = '';
            foreach ($this->o2o_conf as $type => $conf) {
                if (in_array($record_type, $conf['record'])) {
                    
                    $mod = $this->get_base_mod($type);
                    $check = $mod->check_is_o2o_store($store_code);
                    if ($check === TRUE) {
                        $o2o_type = $type;
                        break;
                    }
                }
            }
            $o2o_type_arr[$store_code][$record_type] = $o2o_type;
        }
        if ($o2o_type_arr[$store_code][$record_type] == ""){
            return $this->format_ret(-1,'不支持门店发货');
        }
        return $this->format_ret(1,$o2o_type_arr[$store_code][$record_type]);
    }

    
    
    
    private function get_base_mod($type) {
        static $mod_arr = null;
        if (!isset($mod_arr[$type])) {
            $mod_path = 'o2o/' . $type . '/' . ucfirst($type) . 'BaseModel';
            $mod_arr[$type] = load_model($mod_path);
        }
        return $mod_arr[$type];
    }
    
    function add($record_code,$record_type,$efast_store_code)
    {
        $ret = $this->is_o2o_store_record($efast_store_code,$record_type);
        if ($ret['status']<0){
                return $this->format_ret(10,'',$efast_store_code.'不是门店发货仓库');
        }
        $ret = $this->uploadtask_add($record_code,$record_type);
        return $ret;
    }

    public function uploadtask_add($record_code, $record_type) {


        $ret = $this->get_record_type_mod($record_type)->get_record_info($record_code);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $record_data = $ret['data'];
        $task_info['json_data'] = json_encode($record_data);

        $sys_store_code = (string) $record_data['store_code'];



        $api_product_ret = $this->is_o2o_store_record($sys_store_code, $record_type);
        $api_product = $api_product_ret['data'];

        if ($api_product == '') {
            return $this->format_ret(-1, '', '找不到对接产品');
        }

        $task_info['api_product'] = $api_product;

        foreach ($record_data['goods'] as $code) {
            $deal_code_arr[] = $code['deal_code'];
        }

        $task_info['deal_code'] = (string) implode(',', array_unique($deal_code_arr));

        $task_info['buyer_name'] = isset($record_data['buyer_name']) ? (string) $record_data['buyer_name'] : '';

        $task_info['sale_channel_code'] = isset($record_data['sale_channel_code']) ? (string) $record_data['sale_channel_code'] : '';
        $task_info['shop_code'] = isset($record_data['shop_code']) ? (string) $record_data['shop_code'] : '';
        $task_info['record_code'] = $record_code;
        $task_info['record_type'] = $record_type;
        $task_info['sys_store_code'] = $sys_store_code;
        if ($record_code == '') {
            return $this->format_ret(-1, '', '单据编号不能为空');
        }
        if ($record_type == '') {
            return $this->format_ret(-1, '', '单据类型不能为空');
        }
        if ($sys_store_code == '') {
            return $this->format_ret(-1, '', 'efast仓库代码不能为空');
        }

        $tbl = 'o2o_oms_trade';
        $sql = "SELECT id,upload_request_flag,upload_response_flag,cancel_response_flag,api_order_flow_end_flag,api_product FROM {$tbl} WHERE record_code='{$task_info['record_code']}' AND record_type='{$task_info['record_type']}'";
        $awt_row = $this->db->get_row($sql);

        if (!empty($awt_row)) {
            $upload_request_flag = (int) $awt_row['upload_request_flag'];
            $upload_response_flag = (int) $awt_row['upload_response_flag'];
            $cancel_response_flag = (int) $awt_row['cancel_response_flag'];
            $api_order_flow_end_flag = (int) $awt_row['api_order_flow_end_flag'];
            $awt_id = (int) $awt_row['id'];
            //echo "<xmp>";debug_print_backtrace();echo "</xmp>";
            if ($api_order_flow_end_flag > 0) {
                return $this->format_ret(-1, '', '已收发货的单据不能生成WMS待上传任务');
            }
            if ($upload_response_flag == 10 && $cancel_response_flag <> 10) {
                return $this->format_ret(-1, '', '上传成功的单据不能生成WMS待上传任务');
            }
            if ($upload_request_flag == 10 && $cancel_response_flag <> 10) {
                return $this->format_ret(-1, '', '上传中的单据不能生成WMS待上传任务');
            }
        }

        $task_info['create_time'] = time();
        $task_info['upload_request_flag'] = 0;
        $task_info['cancel_request_flag'] = 0;
        $task_info['upload_response_flag'] = 0;
        $task_info['cancel_response_flag'] = 0;
        $insert_data = array($task_info);
        $this->insert_multi_duplicate($tbl, $insert_data, $insert_data);

        $insert_id = $this->db->insert_id();


        if (!empty($awt_row)) {
            return $this->format_ret(1, $awt_id);
        }
        return $this->format_ret(1, $insert_id);
    }

    function get_record_type_mod($record_type) {
        static $mod_arr = null;
        if (!isset($mod_arr[$record_type])) {
            $record_arr = explode("_", $record_type);
            $mod_str = '';
            foreach ($record_arr as $val) {
                $mod_str.=ucfirst($val);
            }
            $mod_path = 'o2o/O2o' . $mod_str . 'Model';
            $mod_arr[$record_type] = load_model($mod_path);
        }
        return $mod_arr[$record_type];
    }

    function cancel($record_code, $record_type, $sys_store_code, $is_cancel_tag = array('act' => 'unnotice_shipping')) {

        $tbl = "o2o_oms_trade";
        $type = 'oms';

        $sql = "select id,cancel_response_flag,api_order_flow_end_flag from {$tbl} where record_code = :record_code and record_type = :record_type";
        $row = ctx()->db->get_row($sql, array(':record_code' => $record_code, ':record_type' => $record_type));
        if (empty($row)) {
            return $this->format_ret(100, '', '不是对外接口类型单据');
        }
        if ($row['api_order_flow_end_flag'] > 0) {
            return $this->format_ret(-1, '', '已经收发货完成');
        }
        if ($row['cancel_response_flag'] == 10) {
            return $this->format_ret(100, '', '单据已取消成功');
        }


        $ret = load_model('o2o/O2oMgrModel')->cancel($row['id'], $type, $is_cancel_tag);


        if ($ret['status'] == 2) {
            //用于异步模式的wms
            return $this->format_ret(-2, '', '单据取消中，请等待取消成功，再进行操作');
        }
        if ($ret['status'] == 10) {
            return $this->format_ret(10, '', '单据未上传,取消单据成功');
        }
        if ($ret['status'] > 0 && $ret['status'] < 10) {
            return $this->format_ret(10, '', '单据已上传,调用取消接口成功');
        }
        return $ret;
    }
    
    function get_o2o_store_all(){
        $sql = "select s.shop_store_code from erp_config e 
            INNER JOIN sys_api_shop_store s ON e.erp_config_id=s.p_id AND s.p_type=0
            where s.shop_store_type=1 AND s.o2o_store=1";
        $data = $this->db->get_all($sql);
        $store_arr = array();
        foreach($data as $val){
            $store_arr[] = $val['shop_store_code'];
        }
        return $store_arr;
    }
}
