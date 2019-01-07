<?php
/**
 * Author:WangShouChong
 * Date: 2015-08-11
 */
require_model('tb/TbModel');
require_lang('sys');
require_lib("keylock_util");

class Ali_ecsModel extends TbModel {
    
    public function __construct() {
            parent::__construct('osp_aliyun_host', 'host_id');
    }
    
    /*
     * 根据条件查询数据,分页列表数据
     */
    function get_by_page($filter) {
        $sql_values = array();
        $sql_join = "";
        $sql_main = "FROM {$this->table} t $sql_join WHERE 1";
        
        //IP地址查询条件
        if (isset($filter['ali_outip']) && $filter['ali_outip'] != '') {
            $sql_main .= " AND (t.ali_outip = :ali_outip)";
            $sql_values[':ali_outip'] = $filter['ali_outip'];
        }
        
        $select = 't.*';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        
        //处理解密密码
        foreach ($data['data'] as $key => $value) {
            $keylock=get_keylock_string($value['ali_createdate']);
            $rootpwd= create_aes_decrypt($value['ali_root'],$keylock);
            $webpwd= create_aes_decrypt($value['ali_pass'],$keylock);
            $ret_data['data'][$key]['ali_root']=$rootpwd;
            $ret_data['data'][$key]['ali_pass']=$webpwd;
        }
        
        return $this->format_ret($ret_status, $ret_data);
    }
}