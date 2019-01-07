<?php

/**
 * 监控基类
 *
 * @author wq
 *
 */
require_model('tb/TbModel');
require_lib("keylock_util");

class MoBaseModel extends TbModel {

    private $ret_data = array();
    protected $kh_id = 0;
    function __construct($table = '', $pk = '', $db = '') {
        parent::__construct($table, $pk, $db);
    }

    function get_moniter_data($sql, &$ret_data, $func = '') {
        $this->ret_data = array();
        $kh_data = $this->get_kh_info();
        foreach ($kh_data as $kh_info) {
            $this->get_db_data($kh_info, $sql, $func);
        }
        $ret_data = $this->ret_data;
    }
    
    function exec_kh_db($func) {
        $this->ret_data = array();
        $kh_data = $this->get_kh_info();
        foreach ($kh_data as $kh_info) {
            $this->kh_id = $kh_info['kh_id'];
            $db = $this->create_rds_db($kh_info['rds_id'], $kh_info['db_name']);
            $this->$func($db);
        }
    }

    function get_db_data($kh_info, $sql, $func) {
        $db = $this->create_rds_db($kh_info['rds_id'], $kh_info['db_name']);

        $data = $db->get_all($sql);
        foreach ($data as $val) {
            $val['kh_id'] = $kh_info['kh_id'];
            $val['rds_id'] = $kh_info['rds_id'];
            $val['kh_name'] = $kh_info['kh_name'];
            if (!empty($func)) {
                    $val = $this->$func($val);
            }
            if (!empty($val)) {
                $this->ret_data[] = $val;
            }
        }
    }

    function create_rds_db($rds_id, $db_name) {
        static $db_arr;
        if (!isset($db_arr[$rds_id])) {
            $ret_data = $this->db->get_row("select * from osp_aliyun_rds  where rds_id=:rds_id", array(':rds_id' => $rds_id));
            if (empty($ret_data)) {
                return FALSE;
            }

            $keylock = get_keylock_string($ret_data['rds_createdate']);
            $ret_data['rds_pass'] = create_aes_decrypt($ret_data['rds_pass'], $keylock);

            $config = array(
                'name' => 'sysdb',
                'user' => $ret_data['rds_user'],
                'pwd' => $ret_data['rds_pass'],
                'host' => $ret_data['rds_link'],
                'type' => 'mysql',
            );
            if (!class_exists('PDODB')) {
                require_once ROOT_PATH . 'lib/db/PDODB.class.php';
            }
            $db_arr[$rds_id] = new PDODB($config);
        }
        $db_arr[$rds_id]->select_db($db_name);
        return $db_arr[$rds_id];
    }

    function get_kh_info($filer = array()) {
        $sql = "SELECT r1.rem_db_pid as rds_id,r1.rem_db_name as db_name ,kh.kh_id,kh.kh_name "
                . " FROM osp_rdsextmanage_db r1"
                . " INNER JOIN  osp_kehu  kh ON r1.rem_db_khid=kh.kh_id "
                . " where  1 AND r1.rem_db_version=28 ";
        
        
        $sql = "SELECT osp_rdsextmanage_db.rem_db_pid as rds_id,osp_rdsextmanage_db.rem_db_name as db_name ,osp_kehu.kh_id,osp_kehu.kh_name
	FROM osp_rdsextmanage_db,osp_aliyun_rds,osp_kehu,
	(
	SELECT osp_productorder_auth.pra_kh_id FROM osp_productorder_auth , osp_chanpin 
	WHERE osp_productorder_auth.pra_cp_id = osp_chanpin.cp_id AND (osp_chanpin.cp_code = 'eFAST365' OR osp_chanpin.cp_code = 'eFAST5' ) 
	AND osp_productorder_auth.pra_enddate > NOW()
	) AS kh_list 
	WHERE kh_list.pra_kh_id = osp_rdsextmanage_db.rem_db_khid 
	
	AND osp_rdsextmanage_db.rem_db_pid = osp_aliyun_rds.rds_id AND osp_kehu.kh_id = osp_rdsextmanage_db.rem_db_khid AND (osp_aliyun_rds.rds_notes!='备注勿动，有特殊过滤作用，定时服务器不连该服务器' OR osp_aliyun_rds.rds_notes IS NULL)";
        $sql_values = array();
        if (isset($filer['rds_id']) && !empty($filer['rds_id'])) {
            $rds_id_arr = explode(',', $filer['rds_id']);
            $rds_id_str = "," . implode("','", $rds_id_arr) . ",";
            $sql.=" AND r1.rem_db_pid IN ({$rds_id_str})";
        }

        if (isset($filer['kh_id']) && !empty($filer['kh_id'])) {
            $kh_id_arr = explode(',', $filer['kh_id']);
            $kh_id__str = "," . implode("','", $kh_id_arr) . ",";
            $sql.=" AND r1.rem_db_khid IN ({$kh_id__str})";
        }

        return $this->db->get_all($sql, $sql_values);
    }

}
