<?php

/**
 * 产品中心-产品相关业务
 *
 * @author wzd
 *
 */
require_model('tb/TbModel');

//require_lang('sys');

class ProductModel extends TbModel {

    function get_table() {
        return 'osp_chanpin';
    }

    /*
     * 获取产品信息方法
     */
    function get_products_info($filter) {
        $sql_main = "FROM {$this->table}  WHERE 1";
        
        //产品关键字搜索条件
        if (isset($filter['keyword']) && $filter['keyword'] != '') {
            $sql_main .= " AND (cp_code LIKE '%". $filter['keyword'] .
				"%' OR cp_name LIKE '%" . $filter['keyword'] . "%') ";
        }
        //客户名称搜索条件
        if (isset($filter['productname']) && $filter['productname'] != '') {
            $sql_main .= " AND sd_is_adsf='{$filter['isad']}'";
        }

        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        //return $this->get_row(array('cp_id' => $id));
        $params=array('cp_id'=>$id);
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        $ret_status = $data ? 1 : 'op_no_data';
        //处理关联代码表
        filter_fk_name($data, array('cp_createuser|osp_user_id','cp_updateuser|osp_user_id'));

        return $this->format_ret($ret_status, $data);
    }

    /*
     * 添加产品信息
     */
    function insert($product) {
        $status = $this->valid($product);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        $ret = $this->is_exists($product['cp_code']);
        if ($ret['status'] > 0 && !empty($ret['data']))
            return $this->format_ret('code_is_exist');

        $ret = $this->is_exists($product['cp_name'], 'cp_name');
        if ($ret['status'] > 0 && !empty($ret['data']))
            return $this->format_ret('name_is_exist');
        
        //添加创建人和创建日期
        $product['cp_createuser']=CTX()->get_session("user_id");
        $product['cp_createdate']=date("Y-m-d h:i:sa");
        $product['cp_updateuser']=CTX()->get_session("user_id");
        $product['cp_updatedate']=date("Y-m-d h:i:sa");
                
        return parent::insert($product);
    }



    /*
     * 修改客户信息。
     */
    function update($product, $id) {
        $status = $this->valid($product, true);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        $ret = $this->get_row(array('cp_id' => $id));
        if ($product['cp_name'] != $ret['data']['cp_name']) {
            $ret = $this->is_exists($product['cp_name'], 'cp_name');
            if ($ret['status'] > 0 && !empty($ret['data']))
                return $this->format_ret('name_is_exist');
        }
        
        //添加修改人和修改日期
        $product['cp_updateuser']=CTX()->get_session("user_id");
        $product['cp_updatedate']=date("Y-m-d h:i:sa");
        if($ret['data']['cp_createdate']==""){
            $product['cp_createuser']=CTX()->get_session("user_id");
            $product['cp_createdate']=date("Y-m-d h:i:sa");
        }
                
        $ret = parent::update($product, array('cp_id' => $id));
        return $ret;
    }
    function update_maintain($id,$status){
           $product['cp_maintain'] = $status;
         $ret = parent::update($product, array('cp_id' => $id));
         return $ret;
    }

    
    /*
     * 服务器端验证提交的数据是否重复
     */
    private function valid($data, $is_edit = false) {
        if (!$is_edit && (!isset($data['cp_code']) || !valid_input($data['cp_code'], 'required')))
            return CP_ERROR_CODE;
        if (!isset($data['cp_name']) || !valid_input($data['cp_name'], 'required'))
            return CP_ERROR_NAME;
            return 1;
    }

    private function is_exists($value, $field_name = 'cp_code') {
        $ret = parent::get_row(array($field_name => $value));

        return $ret;
    }

   

}
