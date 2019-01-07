<?php

/**
 * 基础数据-云服务商
 *
 * @author zyp
 *
 */
require_model('tb/TbModel');

class CloudModel extends TbModel {

    function get_table() {
        return 'osp_cloud';
    }

    /*
     * 基础数据-云服务商列表
     */

    function get_could_info($filter) {
        $sql_main = "FROM {$this->table}  WHERE 1";

        //云服务商名称搜索
        if (isset($filter['could_name']) && $filter['could_name'] != '') {
            $sql_main .= " AND cd_name LIKE '%" . $filter['could_name'] . "%'";
        }

        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        $params = array('cd_id' => $id);
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        $ret_status = $data ? 1 : 'op_no_data';

        return $this->format_ret($ret_status, $data);
    }

    /*
     * 添加平台
     */

    function insert($cloud) {
        $status = $this->valid($cloud);
        if ($status < 1) {
            return $this->format_ret($status);
        }
        $ret = $this->is_exists($cloud['cd_name'], 'cd_name');
        if ($ret['status'] > 0 && !empty($ret['data']))
            return $this->format_ret('name_is_exist');
//        //处理LOGO
//        $logofile = json_decode($cloud['pt_logo']);
//        if (!empty($logofile)) {
//            $cloud['pt_logo'] = $logofile[0];
//        }
        return parent::insert($cloud);
    }

    /*
     * 修改平台信息。
     */

    function update($cloud, $id) {
        $status = $this->valid($cloud, true);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        $ret = $this->get_row(array('cd_id' => $id));
        if ($cloud['cd_name'] != $ret['data']['cd_name']) {
            $ret = $this->is_exists($cloud['cd_name'], 'cd_name');
            if ($ret['status'] > 0 && !empty($ret['data']))
                return $this->format_ret(name_is_exist);
        }
        //处理LOGO
        $logofile = json_decode($cloud['pt_logo']);
        if (!empty($logofile)) {
            $cloud['pt_logo'] = $logofile[0][0];
        }
        $ret = parent::update($cloud, array('cd_id' => $id));
        return $ret;
    }

    /*
     * 服务器端验证提交的数据是否重复
     */

    private function valid($data, $is_edit = false) {
        if (!isset($data['cd_name']) || !valid_input($data['cd_name'], 'required'))
            return name_is_exist;
        return 1;
    }

    private function is_exists($value, $field_name = 'cd_name') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }

    function getcloudmd($cdid, $type) {
        $sql_main = "select cm_id,cm_host_type from osp_cloud_module where cm_cd_id=:cm_cd_id and cm_type=:cm_type";
        $sql_values[':cm_cd_id'] = $cdid;
        $sql_values[':cm_type'] = $type;

        $data = $this->db->get_all($sql_main, $sql_values);
        $ret_status = $data ? 1 : 'op_no_data';
        return $this->format_ret($ret_status, $data);
    }

    //基础数据-选择主机类型后自动带出配置信息
    function get_hosts($id) {
        //获取客户联系人和联系方式
        $sql_hmx = "SELECT cm_host_cpu,cm_host_mem,cm_host_net,cm_host_disk FROM osp_cloud_module WHERE cm_id=:cm_id ";
        $sql_mx[':cm_id'] = $id;
        $data = $this->db->get_row($sql_hmx, $sql_mx);
        $ret_status = $data ? 1 : 'op_no_data';
        return $this->format_ret($ret_status, $data);
    }

    function getdbmd($cdid, $type) {
        $sql_main = "select cm_id,cm_db_type from osp_cloud_module where cm_cd_id=:cm_cd_id and cm_type=:cm_type";
        $sql_values[':cm_cd_id'] = $cdid;
        $sql_values[':cm_type'] = $type;

        $data = $this->db->get_all($sql_main, $sql_values);
        $ret_status = $data ? 1 : 'op_no_data';
        return $this->format_ret($ret_status, $data);
    }

    //基础数据-选择主机类型后自动带出配置信息
    function get_dbs($id) {
        //获取客户联系人和联系方式
        $sql_hmx = "SELECT cm_db_mem,cm_db_disk,cm_max_con,cm_max_qps,cm_max_iops FROM osp_cloud_module WHERE cm_id=:cm_id ";
        $sql_mx[':cm_id'] = $id;
        $data = $this->db->get_row($sql_hmx, $sql_mx);
        $ret_status = $data ? 1 : 'op_no_data';
        return $this->format_ret($ret_status, $data);
    }

}
