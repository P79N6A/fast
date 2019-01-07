<?php
require_model('tb/TbModel');
/**
 * 短信发送
 */
class sendSMS { 
    public function sendSMSTcp(array $data){
        $context = new ZMQContext(1);
        $req = new ZMQSocket($context, ZMQ::SOCKET_REQ);
        $req->connect("tcp://192.168.166.34:60221");
        $r = $req->send(json_encode($data));
        $r1 = $req->recv();

        return $this->object_to_array(json_decode($r1));
    }
    
    function object_to_array($obj){
        $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
        foreach ($_arr as $key => $val)
        {
            $val = (is_array($val) || is_object($val)) ? self::object_to_array($val) : $val;
            $arr[$key] = $val;
        }
        return $arr;
    }
    
    /**
     * 读取表e_message，发送指定数量的短信
     * @param int $num 需要发送的数量
     */
    function send_by_read_message($num,$id="",$type="=") {
        $db = $GLOBALS ['context']->db;
        $sql = "select * from e_message where type = 1";
        if($id != ""){
            $sql .= " and id ".$type." ".$id;
        }
        $sql .= " order by id limit $num";

        $ret_messge = $db->get_all($sql);
        
        //更新表发送状态
        $ids = array();
        foreach($ret_messge as $item) {
            $ids[] = $item['id'];
            $log['tel'] = $item['tel'];
            $log['message'] = $item['message'];
            $log['status'] = 1;
            $log['add_time'] = date('Y-m-d H:i:s');
            $log['type'] = 1;
            
            //记录日志
            $result = $db->insert('e_message_log',$log);
        }
        $ids_str = implode(',', $ids);
        $sql = "update e_message set type = 2 where id in ({$ids_str})";
        $ret_update = $db->query($sql);
        
        $mdl_tb = new TbModel();
        $mdl_tb->delete_exp("e_message","id in ({$ids_str})");
        //原始数据处理为短信需要的格式
        $send_data = $this->createSMSByDB($ret_messge);
        
        //调用接口，发送短信
        return $this->sendSMSTcp($send_data);
    }
    
    /**
     * 构建短信的需要的数组
     * @param string  $message 消息内容
     * @param array   $tels  手机号码
     * @param int   $ownerId  发送者ID
     * @return array
     */
    function createSMSByGeneral($message, $tels, $ownerId = 1){
        $array = array();
        $array["ownerId"] = $ownerId;
        $messageBody = $message;
        $tmp_array = array();
        foreach($tels as $k => $tel) {
            $tmp_array[] = array("ownerId"=>$ownerId,"messageBody"=>$messageBody,"customerName"=>"小李".$k,"tel"=>$tel);
        }
        $array["listCustmerInfos"] = $tmp_array;
        return $array;
    }
    
    /**
     * 通过读取数据库记录，构建短信的需要的数组
     * @param array   $rows  数据库记录
     * array(
     *      array('tel' => '18621778937','message'=> '好消息','gkmc' => '张三'),
     *      array('tel' => '15950597493','message'=> '好消息1','gkmc' => '李四'),
     * )
     * @param int   $ownerId  发送者ID
     * @return array
     */
    function createSMSByDB($rows, $ownerId = 1){
        $array = array();
        $array["ownerId"] = $ownerId;
        $tmp_array = array();
        foreach($rows as $row) {
            $tmp_array[] = array("ownerId"=>$ownerId,"messageBody"=>$row['message'],"customerName"=>$row['gkmc'],"tel"=>$row['tel']);
        }
        $array["listCustmerInfos"] = $tmp_array;
        return $array;
    }
    
    function createMessageSeq($messageList){
        $mdl_tb = new TbModel();
        return $mdl_tb->insert_exp("e_message",$messageList);
    } 
    
    function createMessageSeqAll($messageList){
        $mdl_tb = new TbModel();
        return $mdl_tb->insert_multi_exp("e_message",$messageList,true);
    } 
}