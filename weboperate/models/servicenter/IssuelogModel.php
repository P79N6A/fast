<?php

/**
 * 服务中心-提单管理-问题提单详情-操作日志
 *
 * @author zyp
 *
 */
require_model('tb/TbModel');
class IssuelogModel extends TbModel {

    function get_table() {
        return 'osp_issue_log';
    }
    
    //获取问题提单对应的日志信息
    function  get_log_info ($filter){
       $sql_main = "FROM {$this->table}  WHERE 1";
       //问题提单对应的日志搜索
        if (isset($filter['log_sue_number']) && $filter['log_sue_number'] != '') {
            $sql_main .= " AND log_sue_number = '". $filter['log_sue_number']."'";
        }
        //构造排序条件
        $sql_main .= " order by log_operate_date desc";
        
        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        //处理关联客户名称、产品等信息
        filter_fk_name($ret_data['data'], array('log_operater|osp_user_id'));   
        return $this->format_ret($ret_status, $ret_data); 
    }
    
    
    
}
