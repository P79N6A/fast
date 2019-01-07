<?php

/**
 * 产品销售渠道相关业务
 *
 * @author zyp
 *
 */
require_model('tb/TbModel');
class SellchannelModel extends TbModel {

    function get_table() {
        return 'osp_sell_channel';
    }

    /*
     * 获取岗位信息方法
     */
    function get_sell_channel($filter) {
        $sql_main = "FROM {$this->table}  WHERE 1";
        
        //名称搜索条件
        if (isset($filter['keyword']) && $filter['keyword'] != '') {
            $sql_main .= " AND channel_name LIKE '%" . $filter['keyword'] . "%'";
        }
        //类型
        if (isset($filter['channel_type']) && $filter['channel_type'] != '') {
            $sql_main .= " AND channel_type = '". $filter['channel_type']."'";
        }
        //模式
        if (isset($filter['channel_mode']) && $filter['channel_mode'] != '') {
            $sql_main .= " AND channel_mode = '". $filter['channel_mode']."'";
        }
        
        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data); 
    }
    
    
    function get_by_id($id) {
        return $this->get_row(array('channel_id' => $id));
    }
    
     /*
     * 添加销售渠道
     */
    function insert($channel) {
        $status = $this->valid($channel);
        if ($status < 1) {
            return $this->format_ret($status);
        }
        $ret = $this->is_exists($channel['channel_name'], 'channel_name');
        if ($ret['status'] > 0 && !empty($ret['data']))
            return $this->format_ret(name_is_exist);
            return parent::insert($channel);
    }


    /*
     * 修改销售渠道信息。
     */
    function update($channel, $id) {
        $status = $this->valid($channel, true);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        $ret = $this->get_row(array('channel_id' => $id));
        if ($channel['channel_name'] != $ret['data']['channel_name']) {
            $ret = $this->is_exists($channel['channel_name'], 'channel_name');
            if ($ret['status'] > 0 && !empty($ret['data']))
                return $this->format_ret(name_is_exist);
        }
        $ret = parent::update($channel, array('channel_id' => $id));
        return $ret;
    }

    
    /*
     * 服务器端验证提交的数据是否重复
     */
    private function valid($data, $is_edit = false) {
//        if (!$is_edit && (!isset($data['channel_name']) || !valid_input($data['channel_name'], 'required')))
//            return VL_ERROR_CODE;
        if (!isset($data['channel_name']) || !valid_input($data['channel_name'], 'required'))
            return name_is_exist;
            return 1;
    }

    private function is_exists($value, $field_name = 'value_num') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }
    
    
    
    
    
        function getSellCListById($cid){
            $sql_main="SELECT channel_name as id,channel_id as sid,channel_mode as pid,channel_name as text,'true' as leaf FROM {$this->table} WHERE channel_mode=:cid "; 
            $sql_values[':cid'] = $cid;
            
            $ret=$this->db->get_all($sql_main, $sql_values);
            
            return $ret;
        }
    
}
