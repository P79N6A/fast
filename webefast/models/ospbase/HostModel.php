<?php

/**
 * 基础档案-云主机相关
 *
 * @author zyp,wkq
 *
 */
require_model('tb/TbModel');
class HostModel extends TbModel {

    function get_table() {
        return 'osp_aliyun_host';
    }
    public function get_host_by_kh_id($kh_id){
        $params = array('kh_id'=>$kh_id);
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        if (isset($data)) {
            return $data;
        } else {
            return "";
        }
    }

    //加密密码
    function encrypt($data) {
        if (isset($data)) {
            $ret = parent::encrypt($data);
            return $ret;
        }
    }

    //解密密码
    function decrypt($data) {
        if (isset($data)) {
            $ret = parent::decrypt($data);
            return $ret;
        }
    }

    //获取web用户密码
    function get_web_pwd($id) {
        $ret = $this->get_row(array('host_id' => $id));
        if (isset($ret)) {
            return $ret['data'];
        } else {
            return "";
        }
    }

    //更新web密码
    function update_web_pass($pwd, $id) {
        $data = array('ali_pass' => $pwd);
        $result = parent::update($data, array('host_id' => $id));
        if ($result) {
            return $this->format_ret("1", "", 'update_success');
        } else {
            return $this->format_ret("-1", '', 'update_error');
        }
    }

    //获取root用户密码
    function get_root_pwd($id) {
        $ret = $this->get_row(array('host_id' => $id));
        if (isset($ret)) {
            return $ret['data'];
        } else {
            return "";
        }
    }

    //更新root密码
    function update_root_pass($pwd, $id)
    {
        $data = array('ali_root' => $pwd);
        $result = parent::update($data, array('host_id' => $id));
        if ($result) {
            return $this->format_ret("1", "", 'update_success');
        } else {
            return $this->format_ret("-1", '', 'update_error');
        }
    }
}
