<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LogConsumeModel
 *
 * @author wq
 */
set_time_limit(0);
require_model('apilog/ApiLogBaseModel');

class LogConsumeModel extends ApiLogBaseModel {

    private $consumer = null;
    private $read_log_data = array();
    private $group_name = null;
   
    function __construct() {
        parent::__construct();
    }

    function set_group_name($group_name) {
        if (!empty($group_name)) {
            $this->group_name = $group_name;
        }
    }

    function consume_log($partition_id = 0, $partition_max = -1) {
        $topic_name_all = $this->log_config['topic_name'];


        $partition_id_max = ($partition_max < 0) ? $this->log_config['partition_id_max'] : $partition_max;

        $kafka_conf = require_conf('apilog/kafka');
        //     host
        //   $host_arr = $this->log_config['host'];
        // $host_str = implode(",", $host_arr);
        $host_str = $kafka_conf['host'] . ":" . $kafka_conf['port'];
        // $group = "apilogConsumerGroup";
        $group = empty($this->group_name) ? "logConsumerGroup" : $this->group_name;
        foreach ($topic_name_all as $topic_name) {

            $topic = $this->create_consume_topic($host_str, $group, $topic_name);
            $i = 1;
            while ($i>0) {
                $status = $this->read_topic_partition_message($topic, $topic_name, $partition_id_max, $partition_id);
                $i = $status===true?0:1;
            }
        }



        //create_consume_topic($host_str, $group)
//        foreach ($topic_name_all as $topic_name) {
//            $partition_id = 0;
//            while ($partition_id_max > $partition_id) {
//                $read_num = $this->read_topic_partition_log($topic_name, $partition_id, $read_max);
//                $this->save_log($topic_name);
//                $partition_id +=$read_num >= $read_max ? 0 : 1;
//            }
//        }
    }

    function create_consume($host_str, $group) {

        $consumer = \Kafka\Consumer::getInstance($host_str);
        $consumer->setGroup($group);

        return $consumer;
    }

    function create_consume_topic($host_str, $group, $topic_name) {

        $conf = new RdKafka\Conf();
        $conf->set('group.id', $group);
        $rk = new RdKafka\Consumer($conf);
        $rk->addBrokers($host_str);
        $topicConf = new RdKafka\TopicConf();
        $topicConf->set('auto.commit.interval.ms', 100);
        $topicConf->set('auto.commit.enable', 'false');
            $topicConf->set('auto.offset.reset', 'smallest');
        $topic = $rk->newTopic($topic_name, $topicConf);

        return $topic;
    }

    function read_topic_partition_message(&$topic, $topic_name, $partition_id_max, $partition_id = 0) {
        $read_max = 1000;
        $is_error = 0;
        try{
        while ($partition_id_max > $partition_id) {
            $read_num = 0;
            $topic->consumeStart($partition_id, RD_KAFKA_OFFSET_STORED);
            while (true) {
                $message = $topic->consume($partition_id, 120 * 10000);
                $is_next = true;

                switch ($message->err) {
                    case RD_KAFKA_RESP_ERR_NO_ERROR:
                        $this->set_log($message->payload);
                        $is_next = FALSE;
                        $read_num++;
                        break;
                    case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                        echo $partition_id . ":No more messages; will wait for more\n";

                        break;
                    case RD_KAFKA_RESP_ERR__TIMED_OUT:
                        echo "Timed out\n";
                        break;
                    default:
                        //错误日志
                        echo "error({$message->err}):" . $message->errstr();
                        //throw new \Exception($message->errstr(), $message->err);
                        break;
                }
                //read_max to save
                if ($read_num >= $read_max || $is_next === TRUE) {
                    break;
                }
            }

            if (!empty($this->read_log_data)) {
                $this->save_log($topic_name);
                $topic->offsetStore($message->partition, $message->offset);
            }

            $read_max > $read_num ? $partition_id++ : false;
        }
        }catch(Exception $ex){
            echo "error:".$ex->getMessage()."\n";
           $is_error++;
        }
        
            if (!empty($this->read_log_data)) {
                $this->save_log($topic_name);
                $topic->offsetStore($message->partition, $message->offset);
            } 
            if($is_error>0){
                return false;
            }
            return true;
        
    }

    function read_topic_partition_log($topic_name, $partition_id, $read_max = 1000) {



        $this->read_log_data = array();
        $this->consumer->setPartition($topic_name, $partition_id);
        $result = $this->consumer->fetch();
        $read_i = 0;
        foreach ($result as $topicName => $topic) {
            foreach ($topic as $partId => $partition) {
                foreach ($partition as $message) {
                    $this->set_log((string) $message);
                    $read_i++;
                    if ($read_i > $read_max) { //读取条数限制
                        break;
                    }
                }
            }
        }
        return $read_i;
    }

    function set_log($message) {
        $msg_data = json_decode($message, true);
        $m_id = md5($message);
        $kh_id = 0;
        $date_key = date('Ymd');
        if (isset($msg_data['kh_id']) && isset($msg_data['add_time'])) {
            $kh_id = $msg_data['kh_id'];
            $date_key = $this->get_date_key($msg_data['add_time']);
        }
        load_model('apilog/ExplainModel')->get_explain_data($msg_data);
        //增加唯一标识
        $msg_data['index_key3'] = $m_id;
        $this->read_log_data[$kh_id . "," . $date_key][] = $msg_data;
    }

    function save_log($topic_name) {

        if (!empty($this->read_log_data)) {
            foreach ($this->read_log_data as $key => $log_data) {
                list($kh_id, $date_key) = explode(",", $key);
                $db_name = '';
                $db = $this->get_kh_db($kh_id, $db_name);
                $tb_name = $this->get_tb_name($topic_name, $kh_id, $date_key);
                $this->save_to_log($db, $tb_name, $log_data);
            }
            $this->read_log_data = array();
        }
    }

    function save_to_log(&$db, $tb_name, $data) {
        $tb = new TbModel($tb_name, '', $db);
        $tb->insert_multi($data, TRUE);
    }

}
