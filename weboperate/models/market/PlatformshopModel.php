<?php

/**
 * 营销中心-报价模板-平台店铺业务
 *
 * @author zyp
 *
 */
require_model('tb/TbModel');

class PlatformshopModel extends TbModel {

    function get_table() {
        return 'osp_plan_detail';
    }

    /*
     * 
     */
    function get_by_page_shop($filter) {
        $sql_main = "FROM {$this->table}  WHERE 1";
        if (isset($filter['price_id']) && $filter['price_id'] != '') {
            $sql_main .= " AND (pd_price_id =" . $filter['price_id'] . ")";
        }
        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        filter_fk_name($ret_data['data'],array('pd_pt_id|osp_pt_type'));
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        $params = array('pd_id' => $id);
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        $ret_status = $data ? 1 : 'op_no_data';
        //处理关联代码表
        //filter_fk_name($data, array('cp_createuser|osp_user_id','cp_updateuser|osp_user_id'));
        return $this->format_ret($ret_status, $data);
    }

    /*
     * 添加
     */
    function insert_platshop($platshop) {
        return parent::insert($platshop);
    }

    /*
     * 修改
     */
    function update_platshop($platshop, $id) {
        $ret = parent::update($platshop, array('pd_id' => $id));
        return $ret;
    }

    /**
     * 根据ID删除行数据
     * 
     */
    function delete($id) {
        $result = parent::delete(array('pd_id' => $id));
        return $result;
    }
    
    
    function get_platshop_byid($price_id, $filter) {
            if (empty($price_id)) {
                    return $this->format_ret(OP_ERROR);
            }
            $select = '*';
            $sql_main = "FROM {$this->table}  WHERE pd_price_id={$price_id}";
            $data =  $this->get_page_from_sql($filter, $sql_main, $select);

            $ret_status = OP_SUCCESS;
            $ret_data = $data;

            filter_fk_name($ret_data['data'], array('pd_pt_id|osp_pt_type'));

            return $this->format_ret($ret_status, $ret_data);
    }
}
