<?php

require_model('tb/TbModel');
require_lib("keylock_util");
class BasePubModel extends TbModel {

    function create_kh_db($kh_id) {
        $kh_db_info = $this->get_kh_rds($kh_id);
        $rds_id = $kh_db_info['rem_db_pid'];
        $rds_name = $kh_db_info['rem_db_name'];
        $sql = "select * from osp_aliyun_rds where rds_id=:rds_id";
        $ret['data'] = $this->db->get_row($sql,array(':rds_id' => $rds_id));
        if (empty($ret['data'])) {
            return FALSE;
        }

        $keylock = get_keylock_string($ret['data']['rds_createdate']);
        $ret['data']['rds_pass'] = create_aes_decrypt($ret['data']['rds_pass'], $keylock);

        $config = array(
            'name' => $rds_name,
            'user' => $ret['data']['rds_user'],
            'pwd' => $ret['data']['rds_pass'],
            'host' => $ret['data']['rds_link'],
            'type' => 'mysql',
        );

        return create_db($config);
    }
  function create_rds_db($rds_id,$rds_name) {

        $sql = "select * from osp_aliyun_rds where rds_id=:rds_id";
        $ret['data'] = $this->db->get_row($sql,array(':rds_id' => $rds_id));
        if (empty($ret['data'])) {
            return FALSE;
        }

        $keylock = get_keylock_string($ret['data']['rds_createdate']);
        $ret['data']['rds_pass'] = create_aes_decrypt($ret['data']['rds_pass'], $keylock);

        $config = array(
            'name' => $rds_name,
            'user' => $ret['data']['rds_user'],
            'pwd' => $ret['data']['rds_pass'],
            'host' => $ret['data']['rds_link'],
            'type' => 'mysql',
        );

        return create_db($config);
    }

    function get_all_kh() {
        $sql = "select rem_db_khid from osp_rdsextmanage_db where  rem_db_is_bindkh=1 ";
        $data = $this->db->get_all($sql);
        $kh_data = array();
        foreach ($data as $val) {
            $kh_data[] = $val['rem_db_khid'];
        }
        return $kh_data;
    }

    function get_kh_rds($kh_id) {
        $row = $this->db->get_row("select rem_db_pid,rem_db_name from osp_rdsextmanage_db where rem_db_khid='{$kh_id}' AND rem_db_is_bindkh=1 ");
        if (empty($row)) {
            return FALSE;
        }
        return $row;
    }

}
