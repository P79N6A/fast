<?php

/**
 * 异常邮件通知队列
 *
 * @author wsc
 *
 */
require_model('tb/TbModel');
require_lib("keylock_util");
class MailBaseModel {

    var $send_to_mail;
    var $kh_id;
    var $db;
    var $kh_db = null;
    var $conf = null;
    var $mail_type;
    function __construct($kh_id) {
        $this->kh_id = $kh_id;
        $this->db = CTX()->db;
        $this->set_kh_db($kh_id);//设置数控
        $this->set_kh_mail(); 
    }
    
    


    function add_mail(&$mail_obj, $type) {
        $record_time = $this->get_record_time($type);
        $new_record_time = time();

        $mail_info['kh_id'] = $this->kh_id;
        $mail_info['send_to'] = $this->send_to_mail;  

       $mail_obj->init($this->kh_db,$mail_info);
       $mail_obj->get_mail_content( $record_time);

        
        if (!empty($mail_obj->maildata)) {
            load_model('mailer/QueueModel')->add_mailer_queue($mail_obj->maildata);
        }
        $this->update_record($new_record_time, $type);
    }
    

    //保存时间戳
    function update_record($new_record_time, $type) {
        $data['type'] = $type;
        $data['record_time'] = $new_record_time;
        $data['kh_id'] = $this->kh_id;
        $key_arr = array_keys($data);

        $sql_mx = "'" . implode("','", $data) . "'";

        $sql = 'INSERT  INTO sys_warning_record (`' . implode('`,`', $key_arr) . '`) VALUES ( ' . $sql_mx . ") ";
        $sql .=" ON DUPLICATE KEY UPDATE  ";
        $sql .= " record_time = VALUES(record_time) ";
        return $this->db->query($sql);
    }

    //保存时间戳
    function get_record_time($type) {
        $sql = "select * from sys_warning_record where kh_id=:kh_id AND  type=:type ";
        $row = $this->db->get_row($sql, array(':kh_id' => $this->kh_id, ':type' => $type));
        if (empty($row)) {
            $row['record_time'] = strtotime("-3 days");//最近3天
        } else {
            $row['record_time'] -= 30; //1分钟误差
        }
        return $row['record_time'];
    }

    function set_kh_db($kh_id) {
        static $kh_db_arr = null;
        if (!isset($kh_db_arr[$kh_id])) {
            $sql = "SELECT r1.rem_db_pid as rds_id,r1.rem_db_name as db_name
               FROM osp_rdsextmanage_db r1
               INNER JOIN  osp_kehu  kh ON r1.rem_db_khid=kh.kh_id 
               where  1 AND r1.rem_db_version=28 AND kh.kh_id=:kh_id ";
            $row = $this->db->get_row($sql, array(':kh_id' => $kh_id));
            $kh_db_arr[$kh_id] = $row;
        }

        if (!empty($kh_db_arr[$kh_id])) {
            $rds_id = $kh_db_arr[$kh_id]['rds_id'];
            $db_name = $kh_db_arr[$kh_id]['db_name'];
            $this->kh_db = $this->create_rds_db($rds_id, $db_name);
        }

    }

    function create_rds_db($rds_id, $db_name) {
        static $db_arr = NULL;
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
    function set_kh_mail(){
        if(!empty($this->kh_db)){
            $sql  = "select value from sys_params where param_code='notice_email'";
            $this->send_to_mail =  $this->kh_db->get_value($sql);
            if(empty($this->send_to_mail)){
                $this->send_to_mail = "wqian@baisonmail.com";
            }
        }
    }
}
