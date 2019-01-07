<?php

/**
 * 平台列表相关业务
 *
 * @author zyp
 *
 */
require_model('tb/TbModel');

class PlatformModel extends TbModel {

    function get_table() {
        return 'osp_platform';
    }

    /*
     * 获取平台列表方法
     */

    function get_by_page($filter) {
        $sql_main = "FROM {$this->table}  WHERE 1";

        //代码名称搜索条件
        if (isset($filter['keyword']) && $filter['keyword'] != '') {
            $sql_main .= " AND (pt_code LIKE '%" . $filter['keyword'] .
                    "%' or pt_name LIKE '%" . $filter['keyword'] . "%')";
        }

        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        $path = CTX()->get_app_conf('img_show_path');
        foreach ($ret_data['data'] as & $datalist) {
            $newpath = $path . $datalist['pt_logo'];
            $datalist['pt_logo'] = '<div align="center"><img src="' . $newpath . '" width="50px" height="50px" /></div>';
        }
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        $params = array('pt_id' => $id);
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        $ret_status = $data ? 1 : 'op_no_data';
        return $this->format_ret($ret_status, $data);
    }

    /*
     * 添加平台
     */

    function insert($platform) {
        $status = $this->valid($platform);
        if ($status < 1) {
            return $this->format_ret($status);
        }
        $ret = $this->is_exists($platform['ode']);
        if ($ret['status'] > 0 && !empty($ret['data']))
            return $this->format_ret('code_is_exist');
        $ret = $this->is_exists($platform['pt_name'], 'pt_name');
        if ($ret['status'] > 0 && !empty($ret['data']))
            return $this->format_ret('name_is_exist');

        //处理LOGO
        $logofile = json_decode($platform['pt_logo']);
        if (!empty($logofile)) {
            $platform['pt_logo'] = $logofile[0];
        }
        return parent::insert($platform);
    }

    /*
     * 修改平台信息。
     */

    function update($platform, $id) {
        $status = $this->valid($platform, true);
        if ($status < 1) {
            return $this->format_ret($status);
        }
        $ret = $this->get_row(array('pt_id' => $id));
        if ($platform['pt_name'] != $ret['data']['pt_name']) {
            $ret = $this->is_exists($platform['pt_name'], 'pt_name');
            if ($ret['status'] > 0 && !empty($ret['data']))
                return $this->format_ret(name_is_exist);
        }
        //处理LOGO
        $logofile = json_decode($platform['pt_logo']);
        if (!empty($logofile)) {
            $platform['pt_logo'] = $logofile[0][0];
        }
        $ret = parent::update($platform, array('pt_id' => $id));
        return $ret;
    }

    /*
     * 服务器端验证提交的数据是否重复
     */

    private function valid($data, $is_edit = false) {
        if (!isset($data['pt_name']) || !valid_input($data['pt_name'], 'required'))
            return name_is_exist;
        return 1;
    }

    private function is_exists($value, $field_name = 'pt_code') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }

    function get_platform_shop($id) {
        $sql_main = "FROM osp_platform_detail  WHERE 1";
        if (isset($id['pd_pt_id']) && $id['pd_pt_id'] != '') {
            $sql_main .= " AND pd_pt_id = '" . $id['pd_pt_id'] . "'";
        }
        //构造排序条件
        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        filter_fk_name($ret_data['data'], array('pd_pt_id|osp_pt_type'));   
        return $this->format_ret($ret_status, $ret_data);
    }
    
    function get_platshop_type($id) {
        $params = array('pd_id' => $id);
        $data = $this->db->create_mapper('osp_platform_detail')->where($params)->find_by();
        $ret_status = $data ? 1 : 'op_no_data';
        filter_fk_name($ret_data['data'], array('pd_pt_id|osp_pt_type')); 
        return $this->format_ret($ret_status, $data);
    }
    
    //基础数据-平台列表-编辑平台店铺类型
    function update_platshop_type($shop, $id) {
        if (isset($shop)){
                $sql_main="update osp_platform_detail set pd_shop_type='".$shop['pd_shop_type']."' WHERE pd_id=:id" ; 
                $sql_values[':id'] = $id;
                $this->db->query($sql_main, $sql_values);
                return $this->format_ret("1", $data, '更新成功');
            }else {
                return $this->format_ret("-1", '', '更新失败');
        }
    }
    //基础数据-平台列表-添加平台店铺类型
    function insert_platshop_type($shop) {
        if (isset($shop)){
                $sql_main="insert into osp_platform_detail (pd_pt_id,pd_shop_type) value  ('{$shop['pd_pt_id']}','{$shop['pd_shop_type']}')" ; 
                $this->db->query($sql_main);
                return $this->format_ret("1", $data, '更新成功');
            }else {
                return $this->format_ret("-1", '', '更新失败');
        }
    }
    
    function delete_platshop_type($id) {
        if (isset($id)){
                $sql_main="delete from osp_platform_detail  WHERE pd_id=:id" ; 
                $sql_values[':id'] = $id;
                $this->db->query($sql_main, $sql_values);
                return $this->format_ret("1", $data, '删除成功');
            }else {
                return $this->format_ret("-1", '', '删除失败');
        }
    }

}
