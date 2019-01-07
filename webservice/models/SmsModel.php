<?php
require_lib('Sms/AbsSmsClient');
require_lib('Sms/YunRongSmsClient');
require_lib('db/PDODB.class');
require_model('common/SmsTaskModel');
class SmsModel extends SmsTaskModel {

    protected $smsclient;

    function __construct() {
        set_time_limit(0);
        parent::__construct();
        $this->setSmsClient();
    }
    function setSmsClient() {
        $api_param = array();
        $this->smsclient = new YunRongSmsClient($api_param);
    }
    
    /**
     * 批量发送短信 (一条条发送)
     */
    function sendBatchSmsOneByOne() {
        //获取中间表短信 (时效性: 超过三天不发送)
        $limit = 500;
        $sql = "SELECT id,phone,content FROM `sms_task` WHERE `status` = 0 AND `create_time` >= :create_time LIMIT {$limit}";
        $sql_values = array();
        $do_flag = true;
        $ret = $this->format_ret(1);
        do{
            $sql_values[':create_time'] = date('Y-m-d H:i:s', strtotime('-3 day')); //3天前
            $data = $this->db->get_all($sql, $sql_values);
            if (!empty($data)){ //有数据
                $other = array();
                foreach ($data as $val){
                    //单条发送
                    $ret = $this->smsclient->sendSms($val['phone'], $val['content'], $other);
                    if (0 < $ret['status']){ //发送成功
                        //修改短信状态
                        $cur_time = date('Y-m-d H:i:s');
                        $up_data = array(
                            'send_channel' => $this->smsclient->send_channel,
                            'send_channel_code' => $ret['data'],
                            'status' => 3,
                            'send_time' => $cur_time,
                        );
                        $up_where = array('id' => $val['id']);
                        $ret = $this->update($up_data, $up_where);
                        if (0 > $ret['status']){ //修改状态失败
                            $do_flag = false;
                            break;
                        }
                    }else{ //发送失败
                        $do_flag = false;
                        break;
                    }
                }
            }else{ //数据为空
                $do_flag = false;
            }
        }while($do_flag);
        
        return $ret;
    }
    /**
     * 批量发送短信 (一批批发送)
     */
    function sendBatchSms() {
        //获取中间表短信 (时效性: 超过三天不发送)
        $limit = 500;
        $sql = "SELECT id,phone,content FROM `sms_task` WHERE `status` = 0 AND `create_time` >= :create_time LIMIT {$limit}";
        $sql_values = array();
        $do_flag = true;
        $ret = $this->format_ret(1);
        do{
            $cur_time = date('Y-m-d H:i:s');
            $sql_values[':create_time'] = date('Y-m-d H:i:s', strtotime('-3 day')); //3天前
            $data = $this->db->get_all($sql, $sql_values);
            if (!empty($data)){ //有数据
                //批量发送
                $other = array();
                $ret = $this->smsclient->sendBatchSms($data, $other);
                if (0 < $ret['status']){ //发送成功
                    //批量修改短信状态
                    $task_id = array_column($data, 'id');
                    $task_id_str = implode(',', $task_id);
                    $up_data = array(
                        'send_channel' => $this->smsclient->send_channel,
                        'send_channel_code' => $ret['data'],
                        'status' => 3,
                        'send_time' => $cur_time,
                    );
                    $up_where = " `id` IN ({$task_id_str})";
                    $ret = $this->update($up_data, $up_where);
                    if (0 > $ret['status']){ //修改状态失败
                        $do_flag = false;
                    }
                }else{ //发送失败
                    $do_flag = false;
                }
            }else{ //数据为空
                $do_flag = false;
            }
        }while($do_flag);
        
        return $ret;
    }
    
    /**
     * 获取发送结果（有可能是被动接口）
     */
    function getSmsResult($params) {
        $ret = $this->smsclient->getSmsResult($params);
        return $ret;
    }
    /**
     * 推送短信报告到客户表
     */
    function pushSmsReport() {
        //获取需要推送报告的kh_id
        $sql = "SELECT DISTINCT `kh_id` FROM `sms_task` WHERE `status` IN (1,2) AND `is_push_report` = 0";
        $kh_id_arr = $this->db->get_all_col($sql);
        if(empty($kh_id_arr)){
            return $this->format_ret(-1, '', '没有新的短信报告');
        }
        foreach ($kh_id_arr as $kh_id) {
            //批量推送客户短信报告
            $ret = $this->__pushReport($kh_id);
        }
        return $this->format_ret(1);
    }
    /**
     * 批量推送客户短信报告
     * @param type $kh_id
     * @return type
     */
    private function __pushReport($kh_id) {
        $limit = 500;
        $sql = "SELECT `id`,`kh_id`,`sys_sms_id`,`status` FROM `sms_task` WHERE `status` IN (1,2) AND `is_push_report` = 0 AND `kh_id` = :kh_id LIMIT {$limit}";
        $sql_values = array(':kh_id' => $kh_id);
        try {
            $do_flag = true;
            do {
                $data = $this->db->get_all($sql, $sql_values);
                if (!empty($data)){
                    $this->begin_trans();
                    $success_sms_id = array();
                    $fail_sms_id = array();
                    foreach ($data as $val) {
                        if ($val['status'] == 1){
                            $success_sms_id[] = $val['sys_sms_id'];
                        }else if ($val['status'] == 2){
                            $fail_sms_id[] = $val['sys_sms_id'];
                        }
                    }
                    //更新中间表状态
                    $task_id = array_column($data, 'id');
                    $task_id_str = implode(',', $task_id);
                    $up_data = array('is_push_report' => 1);
                    $up_where = " id IN ({$task_id_str})";
                    $ret = $this->update($up_data, $up_where);
                    if ($ret['status'] < 0){
                        $this->rollback();
                        return $this->format_ret(-1, '', '短信中间表状态更新失败');
                    }
                    //创建客户数据库实例
                    $kh_id = isset($data[0]['kh_id']) ? $data[0]['kh_id'] : '';
                    $kh_db = load_model('common/KhRdsModel')->create_kh_db($kh_id);
                    if (empty($kh_db)){
                        $this->rollback();
                        return $this->format_ret(-1, '', '客户数据库异常');
                    }
                    //更新客户表成功状态
                    if (!empty($success_sms_id)){
                        $success_data = array('status' => 1);
                        $success_sms_id_str = implode(',', $success_sms_id);
                        $success_where = " `id` IN ({$success_sms_id_str}) AND `status` = 4";
                        $ret = $kh_db->update('op_sms_queue', $success_data, $success_where);
                        if (!$ret){
                            $this->rollback();
                            return $this->format_ret(-1, '', '客户短信表更新失败');
                        }
                    }
                    //更新客户表失败状态
                    if (!empty($fail_sms_id)){
                        $fail_data = array('status' => 2);
                        $fail_sms_id_str = implode(',', $fail_sms_id);
                        $fail_where = " `id` IN ({$fail_sms_id_str}) AND `status` = 4";
                        $ret = $kh_db->update('op_sms_queue', $fail_data, $fail_where);
                        if (!$ret){
                            $this->rollback();
                            return $this->format_ret(-1, '', '客户短信表更新失败');
                        }
                    }
                    $this->commit();
                }else{
                    $do_flag = false;
                }
            }while ($do_flag);
        } catch (Exception $e) {
            return $this->format_ret(-1, '', $e->getMessage());
        }
        return $this->format_ret(1);
    }
}
