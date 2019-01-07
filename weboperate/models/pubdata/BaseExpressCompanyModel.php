<?php
require_model('tb/TbModel');
class BaseExpressCompanyModel extends TbModel {

    function __construct() {
        parent::__construct('base_express_company');
    }

    function get_by_page($filter) {
        $sql_main = "FROM {$this->table}  WHERE 1 ";
        $sql_value = array();
        
        if(isset($filter['company_code'])&&!empty($filter['company_code'])){
            $sql_main.=" AND company_code=:company_code";
            $sql_value[':company_code'] = $filter['company_code'];
        }
           if(isset($filter['company_name'])&&!empty($filter['company_name'])){
            $sql_main.=" AND company_name like :company_name";
            $sql_value[':company_name'] = '%'.$filter['company_name'].'%';
        }     

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_value, "*");

        $ret_status = "1";
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }


    /**
     * 新增
     * @param $params
     */
    function add_action($params) {
        $ret = $this->get_row(array('company_code' => $params['company_code']));
        if ($ret['status'] == 1) {
            return $this->format_ret('-1', '', '快递编码已存在！');
        }
        $ret = $this->insert($params);
        if ($ret['status'] != 1) {
            return $this->format_ret('-1', '', '添加失败！');
        }
        return $ret;
    }


}
