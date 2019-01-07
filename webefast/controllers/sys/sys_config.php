<?php
require_model('common/BaseModel');
require_lib('util/web_util', true);
require_model('tb/TbModel');

class sys_config extends BaseModel {

    function get_conf_data($get_kv = 0) {
        $sql = "select id,code,title from sys_config where parent_id = 0 order by sort_order";
        $db_arr = CTX()->db->getAll($sql);
        $parent_id_arr = array();
        foreach ($db_arr as $sub_db) {
            $parent_id_arr[] = $sub_db['id'];
        }
        if (!empty($parent_id_arr)) {
            $parent_id_list = join(',', $parent_id_arr);
            if ($get_kv == 0) {
                $sql = "select parent_id,code,title,type,cid,value from sys_config where parent_id in($parent_id_list) order by sort_order";
                $db_arr2 = CTX()->db->getAll($sql);
                $arr = array();
                foreach ($db_arr2 as $sub_arr2) {
                    $arr[$sub_arr2['parent_id']][] = $sub_arr2;
                }
                foreach ($db_arr as $k => $sub_arr) {
                    $db_arr[$k]['child'] = $arr[$sub_arr['id']];
                }
                return $db_arr;
            } else {
                $sql = "select code,value from sys_config where parent_id in($parent_id_list) order by sort_order";
                $db_arr = CTX()->db->getAll($sql);
                $arr = array();
                foreach ($db_arr as $sub_arr) {
                    $arr[$sub_arr['code']] = $sub_arr['value'];
                }
                return $arr;
            }
        }
    }

    function do_list(array &$request, array &$response, array &$app) {
        $data = $this->get_conf_data();
        $response['sys_conf'] = $data;
    }

    function save(array &$request, array &$response, array &$app) {
        $data = $this->get_conf_data(1);
        $om = new TbModel('sys_config');
        foreach ($data as $k => $v) {
            $new_v = $request[$k];
            if (isset($new_v) && $new_v != $v) {
                $om->update(array('value' => $new_v), " code = '{$k}'");
            }
        }
        CTX()->redirect('sys/SysConfig/do_list');
        return;
    }

}