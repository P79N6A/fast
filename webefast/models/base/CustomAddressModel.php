<?php

/**
 * 供应商类型相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lib('util/oms_util', true);
require_lang('base');

class CustomAddressModel extends TbModel {

    function get_table() {
        return 'base_custom_address';
    }

    function get_by_page($filter) {
        $sql_values = array();
        $sql_main = " FROM {$this->table} AS r1 WHERE 1 AND custom_code = :custom_code ";
        $sql_values[':custom_code'] = $filter['custom_code'];
        $select = 'r1.*';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $tbl_cfg = array(
            'base_area' => array('fld' => 'name as province_txt', 'relation_fld' => 'id+province'),
            'base_area#1' => array('fld' => 'name as city_txt', 'relation_fld' => 'id+city'),
            'base_area#2' => array('fld' => 'name as district_txt', 'relation_fld' => 'id+district'),
        );
        require_model('util/GetDataBySqlRelModel');
        $obj = new GetDataBySqlRelModel();
        $obj->tbl_cfg = $tbl_cfg;
        $data['data'] = $obj->get_data_by_cfg(null, $data['data']);
        foreach ($data['data'] as &$value) {
            $country = $value['country'] == 1 ? '中国' : '海外';

            $value['address_str'] = $country . $value['province_txt'] . $value['city_txt'] . $value['district_txt'] . $value['address'];
        }

        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    //添加地址
    function do_add_addr($params, $custom_id, $custom_address_id = '') {
        $custom_data = load_model('base/CustomModel')->get_by_id($custom_id);
        if (empty($custom_data['data'])) {
            return $this->format_ret(-1, '', '分销商不存在');
        }
        $default_addr = array();
        if(!empty($custom_address_id)) {
            $default_addr = $this->get_by_data($custom_address_id);
        }
        $params['custom_code'] = $custom_data['data']['custom_code'];
        $this->begin_trans();
        if (!empty($params['is_default']) && $params['is_default'] == 1 || (!empty($custom_address_id) && !empty($default_addr) && $default_addr['is_default'] == 1)) { //启用默认地址 ，回写分销商档案
            $custom_params = array(
                'country' => $params['country'],
                'province' => $params['province'],
                'city' => $params['city'],
                'district' => $params['district'],
                'address' => $params['address'],
                'mobile' => $params['tel'],
                'tel' => $params['home_tel'],
                'contact_person' => $params['name']
            );
            $ret = $this->update_exp('base_custom', $custom_params, array('custom_id' => $custom_id));
            if ($ret['status'] < 0) {
                $this->rollback();
                return $this->format_ret(-1, '', '回写分销商档案失败');
            }
            //将当前默认地址去除
            $ret = $this->del_default($custom_data['data']['custom_code']);
            if ($ret['status'] < 0) {
                $this->rollback();
                return $this->format_ret(-1, '', '设置默认地址失败');
            }
            $params['is_default'] = 1;
        }
        if(empty($custom_address_id)) {
            $ret = $this->insert_dup($params);
            $params = $this->get_by_addr_name($params);
            $addr_str = $params['country_name'] . $params['province_name'] . $params['city_name'] . $params['district_name'] . $params['address'];
            $note_str = $params['is_default'] == 1 ? ' 并设为默认地址' : '';
            $action_note = '地址添加为：' . $addr_str . ';联系人为：' . $params['name'] . ';手机号：' . $params['tel'] . ';电话：' . $params['home_tel'] . $note_str;
            $action_name = '添加地址';
        } else {
            $ret = $this->update($params, array('custom_address_id' => $custom_address_id));
            $params = $this->get_by_addr_name($params);
            $addr_str = $params['country_name'] . $params['province_name'] . $params['city_name'] . $params['district_name'] . $params['address'];
            $action_note = '地址修改为：' . $addr_str  . ';联系人为：' . $params['name'] . ';手机号：' . $params['tel'] . ';电话：' . $params['home_tel'] . $note_str;
            $action_name = '修改地址';
        }
        if ($ret['status'] < 0) {
            $this->rollback();
            return $this->format_ret(-1, '', '回写分销商档案失败');
        }
        $ret = $this->add_addr_log($params['custom_code'], $action_name, $action_note);
        if ($ret['status'] < 0) {
            $this->rollback();
            return $this->format_ret(-1, '', '添加日志失败');
        }
        
        $this->commit();
        return $ret;
    }
    
    //移除默认地址
    function del_default($custom_code) {
        $ret = $this->update(array('is_default' => 0), array('custom_code' => $custom_code));
        return $ret;
    }
    
    //查找地址
    function get_by_data($filter, $where = 'custom_address_id', $select = '*') {
        $sql = "SELECT {$select} FROM base_custom_address WHERE {$where} = :{$where} ";
        $ret = $this->db->get_row($sql, array(':' . $where => $filter));
        $ret = $this->get_by_addr_name($ret);
        return $ret;
    }
    
    //删除地址
    function do_delete_address($addr_id) {
        $addr_data = $this->get_by_data($addr_id);
        if($addr_data['is_default'] == 1) {
            return $this->format_ret(-1, '', '此地址是默认地址不能删除');
        }
        $this->begin_trans();
        $ret = $this->delete(array('custom_address_id' => $addr_id));
        if($ret['status'] < 0) {
            $this->rollback();
            return $ret;
        }
        $addr_str = $addr_data['country_name'] . $addr_data['province_name'] . $addr_data['city_name'] . $addr_data['district_name'] . $addr_data['address'];
        $ret = $this->add_addr_log($addr_data['custom_code'], '删除地址', '将：' . $addr_str . ' 地址删除');
        if ($ret['status'] < 0) {
            $this->rollback();
            return $this->format_ret(-1, '', '添加日志失败');
        }
        $this->commit();
        return $ret;
    }
    
    //设为默认地址
    function do_set_default ($addr_id) {
        $addr_data = $this->get_by_data($addr_id);
        if(empty($addr_data)) {
            return $this->format_ret(-1, '', '此地址不存在');
        }
        $this->begin_trans();
        // 回写分销商档案
        $custom_params = array(
            'country' => $addr_data['country'],
            'province' => $addr_data['province'],
            'city' => $addr_data['city'],
            'district' => $addr_data['district'],
            'address' => $addr_data['address'],
            'mobile' => $addr_data['tel'],
            'tel' => $addr_data['home_tel'],
            'contact_person' => $addr_data['name']
        );
        $ret = $this->update_exp('base_custom', $custom_params, array('custom_code' => $addr_data['custom_code']));
        if ($ret['status'] < 0) {
            $this->rollback();
            return $this->format_ret(-1, '', '回写分销商档案失败');
        }

        //移除默认地址
        $ret = $this->del_default($addr_data['custom_code']);
        if($ret['status'] < 0) {
            $this->rollback();
            return $this->format_ret(-1, '', '设为默认地址失败');
        }
        //设为默认地址
        $ret = $this->update(array('is_default' => 1), array('custom_address_id' => $addr_id));
        if($ret['status'] < 0) {
            $this->rollback();
            return $this->format_ret(-1, '', '设为默认地址失败');
        }
        $addr_str = $addr_data['country_name'] . $addr_data['province_name'] . $addr_data['city_name'] . $addr_data['district_name'] . $addr_data['address'];
        $ret = $this->add_addr_log($addr_data['custom_code'], '设为默认', '将：' . $addr_str . ' 地址设为默认地址');
        if ($ret['status'] < 0) {
            $this->rollback();
            return $this->format_ret(-1, '', '添加日志失败');
        }
        $this->commit();
        return $ret;
    }
    
    function add_addr_log($custom_code, $action_name, $action_note) {
        $user_name = CTX()->get_session('user_name');
        $user_code = CTX()->get_session('user_code');
        $data = array(
            'custom_code' => $custom_code,
            'user_code' => $user_code,
            'user_name' => $user_name,
            'action_name' => $action_name,
            'action_note' => $action_note
        );
        $ret = $this->insert_exp('base_custom_address_log', $data);
        return $ret;
    }
    
    function get_by_addr_name ($data) {
        $data['country_name'] = oms_tb_val('base_area', 'name', array('id' => $data['country']));
        $data['province_name'] = oms_tb_val('base_area', 'name', array('id' => $data['province']));
        $data['city_name'] = oms_tb_val('base_area', 'name', array('id' => $data['city']));
        $data['district_name'] = oms_tb_val('base_area', 'name', array('id' => $data['district']));
        return $data;
   } 
   
   function get_by_log($filter) {
        $sql_values = array();
        $sql_main = " FROM base_custom_address_log WHERE custom_code = :custom_code ";
        $sql_values[':custom_code'] = $filter['custom_code'];
        $sql_main .= ' ORDER BY lastchanged DESC';
        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

}
