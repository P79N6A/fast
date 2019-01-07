<?php
/**
* 短信队列 相关业务
*
* @author dfr
*/
require_model('tb/TbModel');


class SmsQueueModel extends TbModel {
    public function __construct($table = '', $db = '') {
        $table = $this->get_table();
        parent :: __construct($table);
    }

    function get_table() {
        return 'sys_sms_queue';
    }

    /**
    * 根据条件查询数据
    */
    function get_by_page($filter) {
        $sql_main = "FROM {$this->table} WHERE 1";

        if (!empty($filter['user_nick'])) {
            $sql_main .= " AND user_nick = :user_nick ";
            $sql_values[':user_nick'] = $filter['user_nick'];
        }

        if (!empty($filter['tel'])) {
            $sql_main .= " AND tel = :tel ";
            $sql_values[':tel'] = $filter['tel'];
        }

        if (!empty($filter['send_time_start'])) {
            $sql_main .= " AND send_time >= :send_time_start ";
            $sql_values[':send_time_start'] = $filter['send_time_start'];
        }

        if (isset($filter['send_time_end']) && $filter['send_time_end'] != '') {
            $sql_main .= " AND send_time <= :send_time_end ";
            $sql_values[':send_time_end'] = $filter['send_time_end'];
        }

        if (isset($filter['msg_content']) && $filter['msg_content'] != '') {
            $sql_main .= " AND msg_content like :msg_content";
            $sql_values[':msg_content'] = $filter['msg_content'];
        }
        
    	if (isset($filter['status']) && $filter['status'] != '') {
            $sql_main .= " AND status = :status";
            $sql_values[':status'] = $filter['status'];
        }

        $select = 'id,user_nick,tel,msg_content,send_time,status';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        $sms_conf = require_conf('sys/sms');
        foreach($ret_data['data'] as $k => $row) {
            $ret_data['data'][$k]['status_exp'] = $sms_conf['status'][$row['status']];
        }

        return $this->format_ret($ret_status, $ret_data);
    }


    /**
    * 删除记录
    */
    function delete($id) {
        $ret = parent :: delete(array('id' => $id));
        return $ret;
    }
 	/**
    * 查询未发送的消息队列
    */
    function select() {
        $ret = parent :: get_all(array('status' => 0),'id');
        return $ret;
    }
    
	/**
    * 通过id 查询数据
    */
    function query_by_id($id) {
        $ret = parent :: get_row(array('id' => $id));
        return $ret;
    }
	/**
    * 更新发送状态
    */
    function update($data,$where) {
        $ret = parent :: update($data,$where);
        return $ret;
    }
    /**
    * 客户自定义要发送什么信息
    */
    function do_batch_send($tel_arr,$msg_content){
        //先验证手机号
        $succ_tel_arr = array();
        $fail_tel_arr = array();
        foreach($tel_arr as $t_tel){
            if (preg_match('/1[3458]{1}\d{9}$/', $t_tel)){
                $succ_tel_arr[] = $t_tel;
            }else{
                $fail_tel_arr[] = $t_tel;
            }
        }

        $ins_arr = array();
        //开始添加队列 tel,msg_content,msg_content_hash,add_time
        foreach($succ_tel_arr as $tel){
            $msg_content_hash = md5($msg_content);
            $add_time = date('Y-m-d H:i:s');
            $row = array('tel'=>addslashes($tel),'msg_content'=>addslashes($msg_content),'msg_content_hash'=>$msg_content_hash,'add_time'=>$add_time);
            $ins_arr[] = "('".join("','",$row)."')";
        }
        if (empty($ins_arr)){
            $result = array('fail'=>array(),'success'=>array());
            return $result;
        }
        $field_str = join(',',array_keys($row));
        $sql = "insert ignore into sys_sms_queue({$field_str}) values".join(",",$ins_arr);
        
        CTX()->db->query($sql);
        $result = array('fail'=>$fail_tel_arr,'success'=>$succ_tel_arr);
        
        return $result;
    }

}
