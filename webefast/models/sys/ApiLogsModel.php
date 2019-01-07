<?php
/**
* wms log
*/
require_model('tb/TbModel');
require_lang('sys');
class ApiLogsModel extends TbModel {
    public function __construct($table = '', $db = '') {
        $table = $this->get_table();
        parent :: __construct($table);
    }

    function get_table() {
        return 'api_logs';
    }
    
    function add_logs($log_arr){
        $params['type'] = $log_arr['type'];
        $params['method'] = $log_arr['method'];
        $params['url'] = $log_arr['url'];
        $params['params'] = !empty($log_arr['params'])?json_encode($log_arr['params']):'';
        $params['post_data'] = json_encode($log_arr['post_data']);
        $params['return_data'] = $log_arr['resp'];
        $params['add_time'] = date("Y-m-d H:i:s");
        $this->insert($params);
    }

    /**
     * 添加新纪录
     */
    function insert($params){
        $ret = parent::insert($params);
        return $ret;
    }

}
    