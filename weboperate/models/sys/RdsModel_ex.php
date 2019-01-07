<?php

/**
 * 企业session相关
 *
 * @author jhua.zuo<jhua.zuo@baisonmail.com>
 * @edit wsc
 */
require_model('tb/TbModel');
require_lang('sys');

class RdsModel_ex extends TbModel {

    function get_table() {
        return 'osp_rds';
    }

    /*
     * 根据条件查询数据,分页列表数据
     */

    function get_by_page($filter=array()) {
        $sql_join = "";
        $sql_main = "FROM {$this->table} rl $sql_join WHERE 1";
        
        //产品
        if (isset($filter['relation_product']) && $filter['relation_product']!='' ) {
                $sql_main .= " AND (rl.relation_product =". $filter['relation_product'].")";
        }
        //平台
        if (isset($filter['relation_platform']) && $filter['relation_platform']!='' ) {
                $sql_main .= " AND (rl.relation_platform =". $filter['relation_platform'].")";
        }
        //app_key
        if (isset($filter['rdsapp_key']) && $filter['rdsapp_key']!='' ) {
                $sql_main .= " AND (rl.app_key =". $filter['rdsapp_key'].")";
        }
                
        $select = 'rl.*';
        $data = $this->get_page_from_sql($filter, $sql_main, "", $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        filter_fk_name($ret_data['data'], array('relation_product|osp_chanpin', 'relation_platform|osp_pt_type')); 
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        $params = array('rds_id' => $id);
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        $ret_status = $data ? 1 : 'op_no_data';
        filter_fk_name($data, array('relation_product|osp_chanpin', 'relation_platform|osp_pt_type'));
        return $this->format_ret($ret_status, $data);
    }

    function insert($rdsinfo) {
        
//        $retex = parent::get_row(array('relation_product' => $rdsinfo['relation_product'],'relation_platform' => $rdsinfo['relation_platform']));
//        if ($retex['status'] > 0 && !empty($retex['data']))
//            return $this->format_ret('rds_is_exist');

        $retappkey = $this->is_exists($rdsinfo['app_key'], 'app_key');
        if ($retappkey['status'] > 0 && !empty($retappkey['data']))
            return $this->format_ret('app_key');
        
        $retappsecret = $this->is_exists($rdsinfo['app_secret'], 'app_secret');
        if ($retappsecret['status'] > 0 && !empty($retappsecret['data']))
            return $this->format_ret('app_secret');

        return parent::insert($rdsinfo);
    }
    
    function update_by_id($rdsinfo, $id) {
        $ret = $this->get_row(array('rds_id' => $id));
        if ($rdsinfo['app_key'] != $ret['data']['app_key']) {
            $retappkey = $this->is_exists($rdsinfo['app_key'], 'app_key');
            if ($retappkey['status'] > 0 && !empty($retappkey['data']))
                return $this->format_ret('app_key');
        }
        if ($rdsinfo['app_secret'] != $ret['data']['app_secret']) {
            $retappsecret = $this->is_exists($rdsinfo['app_secret'], 'app_secret');
            if ($retappsecret['status'] > 0 && !empty($retappsecret['data']))
                return $this->format_ret('app_secret');
        }
        $ret = parent::update($rdsinfo, array('rds_id' => $id));
        return $ret;
    }

    /**
     * 根据回调更新
     * @param $rdsinfo
     * @param $app_key
     * @return array
     */
    function update_by_backurl($rdsinfo, $app_key) {
        $ret = parent::update($rdsinfo, array('app_key'=>$app_key));
        return $ret;
    }

    function is_exists($value, $field_name = 'app_key') {
        $ret = parent::get_row(array($field_name => $value));

        return $ret;
    }
}