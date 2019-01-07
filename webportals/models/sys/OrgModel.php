<?php

/**
 * 组织机构相关业务
 *
 * @author WangShouChong
 *
 */
require_model('tb/TbModel');
require_lang('sys');

class OrgModel extends TbModel {

    function get_table() {
        return 'osp_organization';
    }

    /*
     * 根据条件查询数据,分页列表数据
     */

    function get_by_page($filter) {
        $sql_join = "";
        $sql_main = "FROM {$this->table} rl $sql_join WHERE 1";
        if (isset($filter['is_buildin']) && $filter['is_buildin'] != '') {
            $sql_main .= " AND is_buildin='{$filter['is_buildin']}'";
        }
        //关键字
        if (isset($filter['keyword']) && $filter['keyword'] != '') {
            $sql_main .= " AND (rl.org_code LIKE '%" . $filter['keyword'] .
                    "%' OR rl.org_name LIKE '%" . $filter['keyword'] . "%') ";
        }


        if (isset($filter['__sort']) && $filter['__sort'] != '') {
            $filter['__sort_order'] = $filter['__sort_order'] == '' ? 'asc' : $filter['__sort_order'];
            $sql_main .= ' order by ' . trim($filter['__sort']) . ' ' . $filter['__sort_order'];
        }
        $select = 'rl.*';

        $data = $this->get_page_from_sql($filter, $sql_main, "", $select);

        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        //return  $this->get_row(array('org_id'=>$id));
        $params = array('org_id' => $id);
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        $ret_status = $data ? 1 : 'op_no_data';
        //处理关联代码表
        filter_fk_name($data, array('org_parent_id|osp_org_id'));

        return $this->format_ret($ret_status, $data);
    }

    function getOrgListById($orgid) {
        $sql_main = "SELECT org_id as id,org_parent_id as pid,org_name as text,org_is_leaf as leaf,org_code as code FROM {$this->table} WHERE org_active='1' and org_parent_id=:orgid ";
        $sql_values[':orgid'] = $orgid;

        $ret = $this->db->get_all($sql_main, $sql_values);

        return $ret;
    }

}
