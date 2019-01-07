<?php

require_model('tb/TbModel');

class SfcModel extends TbModel {

    protected $table = "sfc_rm_config";

    function get_by_id($id) {
        return $this->get_row(array('pid' => $id));
    }

    /**
     * 根据条件查询数据
     */

    function get_api_list($filter) {
        $sql = "SELECT * FROM  {$this->table} WHERE 1 ";
        $sql_values = array();
        if (isset($filter['express_id']) && !empty($filter['express_id'])) {
            $sql .= " AND express_id=:express_id";
            $sql_values[':express_id'] = $filter['express_id'];
        }
        $data = $this->db->get_all($sql, $sql_values);
        $ret_data['data'] = $data;
        return $this->format_ret(1, $ret_data);
    }

    /**
     * 删除记录 
     */

    function delete($id) {
        $ret = parent::delete(array('pid' => $id));
        return $ret;
    }

    function check_exists($appKey, $token, $express_code) {
        $sql_values = array();
        $sql = "SELECT COUNT(1) FROM  sfc_rm_config WHERE sfckey=:key AND token=:token AND express_code = :express_code";
        $sql_values[':key'] = $appKey;
        $sql_values[':token'] = $token;
        $sql_values[':express_code'] = $express_code;
        $ret = $this->db->get_value($sql, $sql_values);
        return $ret;
    }

    /**
     * 新增账号
     */
    function add_config($request) {
        $r = $this->check_exists($request['sfckey'], $request['token'] , $request['express_code']);
        if ($r > 0) {
            return $this->format_ret(-1, '', '同appKey不能重复添加');
        }
        $data = array(
            'express_id' => $request['express_id'],
            'express_code' => $request['express_code'],
            'company_code' => 'SFC',
            'sfckey' => trim($request['sfckey']),
            'token' => trim($request['token']),
            'sfcid' => trim($request['sfcid']),
        );
        $ret = $this->insert($data);
        if ($ret['status'] < 1) {
            return $this->format_ret(-1, '', '新增失败');
        }
        return $ret;
    }

    /**
     * 更新账号
     */
    function update_config($request) {
        $data = array(
            'sfckey' => trim($request['sfckey']),
            'token' => trim($request['token']),
            'sfcid' => trim($request['sfcid']),
        );
        $ret = $this->check_update_exists($request);
        if ($ret > 0) {
            return $this->format_ret(-1, '', '同appKey不能重复添加');
        }
        return $this->update($data, array('pid' => $request['pid']));
    }
    
    function check_update_exists($request) {
        $sql_values = array();
        $sql = "SELECT COUNT(1) FROM  sfc_rm_config WHERE sfckey=:key AND token=:token AND pid <> :pid AND express_code=:express_code";
        $sql_values[':key'] = $request['sfckey'];
        $sql_values[':token'] = $request['token'];
        $sql_values[':pid'] = $request['pid'];
        $sql_values[':express_code'] = $request['express_code'];
        $ret = $this->db->get_value($sql, $sql_values);
        return $ret;
    }
}
