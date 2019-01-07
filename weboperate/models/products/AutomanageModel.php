<?php

/**
 * 产品中心-自动服务管理
 *
 *
 */
require_model('tb/TbModel');

class AutomanageModel extends TbModel {

    function get_table() {
        return 'osp_autoservice_acc';
    }

    /*
     * 获取自动服务信息
     *
     */

    function get_autotask_info($filter) {
        $sql_main = "FROM {$this->table} a $sql_join WHERE 1";

        //ip搜索
        if (isset($filter['ipaddr']) && $filter['ipaddr'] != '') {
            $sql_main .= " AND asa_vm_id in (SELECT host_id from osp_aliyun_host WHERE ali_outip like '%" . $filter['ipaddr'] . "%')";
        }
        //构造排序条件
        $sql_main .= " order by asa_id desc";
        $select = 'a.*';
        $data = $this->get_page_from_sql($filter, $sql_main, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        //处理关联
        filter_fk_name($ret_data['data'], 
                array('asa_vm_id|osp_hostinfo', 'asa_cp_id|osp_chanpin',
                    'asa_cp_version_id|osp_chanpin_version',
                    'asa_rds_id|osp_rdsinfo'));
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_autotask_id($id) {
        $params = array('asa_id' => $id);
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        $ret_status = $data ? 1 : 'op_no_data';
        //处理关联代码表
        filter_fk_name($data, array('asa_vm_id|osp_hostinfo','asa_rds_id|osp_rdsinfo'));
        return $this->format_ret($ret_status, $data);
    }

    /*
     * 添加新自动服务
     */

    function insert($atask) {
        if (isset($atask)) {
            return parent::insert($atask);
        }
    }

    /*
     * 修改自动服务
     */

    function update($atask, $id) {
        if (isset($atask)) {
            $ret = parent::update($atask, array('asa_id' => $id));
            return $ret;
        }
    }

    private function is_exists($value, $field_name = 'vem_vm_id') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }

}
