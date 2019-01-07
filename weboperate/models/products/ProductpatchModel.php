<?php

/**
 * 产品补丁相关业务
 *
 * @author zyp
 *
 */
require_model('tb/TbModel');

//require_lang('sys');

class ProductpatchModel extends TbModel {

    function get_table() {
        return 'osp_version_patch';
    }

    /*
     * 获取补丁信息方法
     */
    function get_patch_info($filter) {
        $sql_main = "FROM {$this->table}  WHERE 1";
        
        //产品关键字搜索条件
        if (isset($filter['keyword']) && $filter['keyword'] != '') {
            $sql_main .= " AND (version_no LIKE '%". $filter['keyword'] .
				"%' OR version_patch LIKE '%" . $filter['keyword'] . "%') ";
        }
        //客户名称搜索条件
        if (isset($filter['productname']) && $filter['productname'] != '') {
            $sql_main .= " AND sd_is_adsf='{$filter['isad']}'";
        }
        $sql_main .=" order by id desc";
        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
         filter_fk_name($ret_data['data'], array('cp_id|osp_chanpin'));
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        //return $this->get_row(array('cp_id' => $id));
        $params=array('id'=>$id);
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        $ret_status = $data ? 1 : 'op_no_data';
        //处理关联代码表
        filter_fk_name($data, array('version_no|osp_pdt_bh',));
       // var_dump($data);die;
        //version_no_name"]=> string(15) "efast365_v1.0.0" ["version_no_code"]=> string(15) "v1.0.0_20150317" } 
        return $this->format_ret($ret_status, $data);
    }

    /*
     * 添加产品补丁信息
     */
    function insert($product) {
        if(isset($product)){
            return parent::insert($product);
        }
    }

    /*
     * 修改产品补丁信息。
     */
    function update($product, $id) {
        if(isset($product)){     
            $ret = parent::update($product, array('id' => $id));
            return $ret;
        }
    }


}
