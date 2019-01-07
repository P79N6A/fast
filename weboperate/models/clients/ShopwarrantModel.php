<?php

/**
 * 客户中心-店铺授权列表
 *
 * @author zyp
 */
require_model('tb/TbModel');
//require_lang('sys');
class ShopwarrantModel extends TbModel {

    function get_table() {
        return 'osp_shop_warrant';
    }

    /*
     * 根据条件查询数据,分页列表数据
     */

    function get_by_page($filter=array()) {
        $sql_main = "FROM {$this->table}  WHERE 1";
        
        //客户
        if (isset($filter['client_name']) && $filter['client_name']!='' ) {
            $sql_main .= " AND sw_kh_id in (SELECT kh_id from osp_kehu WHERE kh_name like '%" . $filter['client_name']."%')";
        }
        //平台
        if (isset($filter['product']) && $filter['product']!='' ) {
            $sql_main .= " AND (sw_cp_id =". $filter['product'].")";
        }
        //产品
        if (isset($filter['platform']) && $filter['platform']!='' ) {
            $sql_main .= " AND (sw_pt_id =". $filter['platform'].")";
        }
        //商店
        if (isset($filter['shop_name']) && $filter['shop_name']!='' ) {
            $sql_main .= " AND sw_sd_id in (SELECT sd_id from osp_shangdian WHERE sd_name like '%" . $filter['shop_name']."%')";
        }
                
        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main, "", $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        filter_fk_name($ret_data['data'], array('sw_cp_id|osp_chanpin', 'sw_pt_id|osp_pt_type','sw_kh_id|osp_kh','sw_sd_id|client_shop')); 
        return $this->format_ret($ret_status, $ret_data);
    }

    
    function get_by_id($id) {
        $params = array('sw_id' => $id);
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        $ret_status = $data ? 1 : 'op_no_data';
        //处理关联代码表
        filter_fk_name($data, array('sw_cp_id|osp_chanpin', 'sw_pt_id|osp_pt_type','sw_kh_id|osp_kh','sw_sd_id|client_shop'));
        return $this->format_ret($ret_status, $data);
//        return $this->get_row(array('rds_id' => $id));
    }
    
    /*
     * 添加店铺授权
     */
    function insert($shop_warrant) {
        
        $ret = $this->is_exists($shop_warrant['sw_sd_id'], 'sw_sd_id');
        if ($ret['status'] > 0 && !empty($ret['data']))
            return $this->format_ret('店铺授权记录已存在');
        
        $result= parent::insert($shop_warrant);
        if ($result) {
            //更新商店表的session
            $sql_main="update osp_shangdian set sd_top_session=:sd_top_session,sd_end_time=:sd_end_time where sd_id=:sd_id "; 
            $sql_values[':sd_top_session'] = $shop_warrant['sw_shop_session'];
            $sql_values[':sd_end_time'] = $shop_warrant['sw_valid_date'];
            $sql_values[':sd_id'] = $shop_warrant['sw_sd_id'];
            $this->db->query($sql_main, $sql_values);
        }
        return $result;
    }



    /*
     * 修改店铺授权信息。
     */
    function update($shop_warrant, $id) {
        $ret = parent::update($shop_warrant, array('sw_id' => $id));
        if ($ret) {
            $retvalue = parent::get_row(array('sw_id' => $id));
            //更新商店表的session
            $sql_main="update osp_shangdian set sd_top_session=:sd_top_session,sd_end_time=:sd_end_time where sd_id=:sd_id "; 
            $sql_values[':sd_top_session'] = $retvalue['data']['sw_shop_session'];
            $sql_values[':sd_end_time'] = $retvalue['data']['sw_valid_date'];
            $sql_values[':sd_id'] = $retvalue['data']['sw_sd_id'];
            $this->db->query($sql_main, $sql_values);
        }
        return $ret;
    }
    
    private function is_exists($value, $field_name = 'sw_sd_id') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }
        
       function get_shop_warrant_log($id) {
            $sql_main = "FROM osp_spwarrant_log  WHERE 1";
            if (isset($id['id']) && $id['id'] != '') {
                $sql_main .= " AND sl_sw_id = '" . $id['id'] . "'";
            }
            //构造排序条件
            $sql_main .= " order by sw_update_date desc";
            $select = '*';
            $data = $this->get_page_from_sql($filter, $sql_main, $select);
            $ret_status = OP_SUCCESS;
            $ret_data = $data;
            return $this->format_ret($ret_status, $ret_data);
        }
        
        
        /*保存店铺授权日志
        * pid 为店铺授权表osp_shop_warrant里面的id
        * session为osp_shop_warrant里面的sw_shop_session值。
        */
        function save_shop_warrant_log($pid, $session) {
        $loginfo = array();
        if (isset($pid) && isset($session)) {
            $loginfo['sl_sw_id'] = $pid;
            $loginfo['sw_update_date'] = date('Y-m-d H:i:s');
            $loginfo['sl_shop_session'] = $session;
            $logdata = $this->db->create_mapper('osp_spwarrant_log')->insert($loginfo);
            return $logdata;
        } else {
            return false;
        }
    }


}