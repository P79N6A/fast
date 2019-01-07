<?php

/**
 * 产品中心-产品vm管理
 *
 *
 */
require_model('tb/TbModel');

class VhostModel extends TbModel {

    function get_table() {
        return 'osp_vmextmanage_ver';
    }

    /*
     * 获取vm产品
     *
     */

    function get_vmmanage_info($filter) {
//        $sql_join = "left join osp_kehu kh on sd.sd_kh_id=kh.kh_id  ";
        $sql_main = "FROM {$this->table} sd $sql_join WHERE 1";

        //ip搜索
        if (isset($filter['ipaddr']) && $filter['ipaddr'] != '') {
            $sql_main .= " AND vem_vm_id in (SELECT host_id from osp_aliyun_host WHERE ali_outip like '%" . $filter['ipaddr'] . "%')";
        }
        //构造排序条件
        $sql_main .= " order by vem_id desc";
        $select = 'sd.*';
        $data = $this->get_page_from_sql($filter, $sql_main, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        //处理关联
        filter_fk_name($ret_data['data'], array('vem_vm_id|osp_hostinfo', 'vem_cp_id|osp_chanpin', 'vem_cp_version|osp_chanpin_version'));
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_vhost_id($id) {

        $params = array('vem_id' => $id);
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        $ret_status = $data ? 1 : 'op_no_data';
        //处理关联代码表
        filter_fk_name($data, array('vem_vm_id|osp_hostinfo',));
        return $this->format_ret($ret_status, $data);
    }

    /*
     * 添加新主机
     */

    function insert($vhost) {
        if (isset($vhost)) {
            return parent::insert($vhost);
        }
    }

    /*
     * 修改主机信息
     */

    function update($vhost, $id) {
        if (isset($vhost)) {
            $ret = parent::update($vhost, array('vem_id' => $id));
            return $ret;
        }
    }

    private function is_exists($value, $field_name = 'vem_vm_id') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }

    function getrds_info($vmid) {
        if (isset($vmid)) {
            $rdsdata = $this->db->get_all("SELECT asa_rds_id from osp_autoservice_acc WHERE asa_vm_id ='{$vmid}'");
            return $rdsdata;
        }
    }
    
    function get_version_ip($version){
        $sql = "SELECT vem_cp_version_ip from osp_vmextmanage_ver  where  vem_status=1 AND vem_cp_version =:vem_cp_version ";
        $data = $this->db->get_all($sql,array(':vem_cp_version'=>$version));

        return $this->format_ret(1,$data);
    }

}
