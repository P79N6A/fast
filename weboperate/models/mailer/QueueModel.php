<?php

/**
 * 异常邮件通知队列
 *
 * @author wsc
 *
 */
require_model('tb/TbModel');

class QueueModel extends TbModel {

    public $send_blacklist = array();//邮件黑名单

    public function __construct() {
        parent:: __construct();
        $this->send_blacklist = $this->get_mailer_blacklist(0);
    }

    function get_table() {
        return 'mailer_queue';
    }
    function send_queue(){
        while (1) {
            $sql = "select id from {$this->table} where is_send=0  order by create_time limit 100";
            $db_arr = $this->db->getAll($sql);
            if (empty($db_arr)){
                break;
            }
            foreach ($db_arr as $mailer_row) {
                
                $ret = $this->send_mailer($mailer_row['id']);
                
                if ($ret['status'] == -1){
                    echo "发送失败".$ret['message']."\n";
                } else {
                    echo "发送成功\n";
                }
            }
        }
        

        
    }
    function send_mailer($queue_id){
        $queue_ret = $this->get_row(array('id' => $queue_id));
        $mailer_row = $queue_ret['data'];
        $send_to = array();
        if (!empty($mailer_row['send_to'])){
            $send_to = explode(";", $mailer_row['send_to']);
        } 
        $send_to_all['kh'] = $send_to;
      //  $send_to_baota = array('yuanfei@baisonmail.com','lin.w@baisonmail.com','wqian@baisonmail.com','zjy@baisonmail.com','dd.zhao@baisonmail.com');
     //   $send_to = array_merge($send_to,$send_to_baota);
        $cont = html_entity_decode($mailer_row['cont_body']);
       //外部人员邮件
        $send_info=load_model('servicenter/ProductxqissueModel')->get_clients($mailer_row['kh_id']);
        if ($send_info['status'] == 1) {
            //客户邮箱
            if (!empty($send_info['data']['kh_email'])) {
                $send_to_all['kh'][] = $send_info['data']['kh_email'];
            }
            //服务工程师邮箱
            if (!empty($send_info['data']['kh_fwuser_email'])) {
                $send_to_all['kh'][] = $send_info['data']['kh_fwuser_email'];
            }
        }
        //内部人员邮件
        switch ($mailer_row['subject']) {
            case '新功能上线啦':
                $send_to_all['baota'] = array('yuanfei@baisonmail.com', 'lin.w@baisonmail.com',);
                break;
            case '库存同步熔断':
                $send_to_all['baota'] = array('lin.w@baisonmail.com', 'dd.zhao@baisonmail.com');
                break;
            case '店铺授权失效':
                $send_to_all['baota'] = array('lin.w@baisonmail.com', 'dd.zhao@baisonmail.com');
                break;
            case '提单审批通过啦':
                $send_to_all['baota'] = array('yuanfei@baisonmail.com', 'lin.w@baisonmail.com',);
                break;
            case 'eFAST365订购快过期啦':
                $send_to_all['baota'] = array('hqf@baisonmail.com', 'xiangwang.yan@baisonmail.com');
                break;
            case '提单审批不通过':
                $send_to_all['baota'] = array('yuanfei@baisonmail.com', 'lin.w@baisonmail.com',);
                break;
            case '产品授权':
                $send_to_all['baota'] = array('lin.w@baisonmail.com', 'xiangwang.yan@baisonmail.com');
                break;
            default:
                $send_to_all['baota'] = array('yuanfei@baisonmail.com', 'lin.w@baisonmail.com', 'wqian@baisonmail.com', 'dd.zhao@baisonmail.com');
                break;
        }

        //内部人员与外部人员分开发送
        $is_black = 0;
        $ret = $this->format_ret('1', '', '');
        foreach ($send_to_all as $key => $send_to_people) {
            if (!empty($send_to_people)) {
                //黑名单
                $send_to_people_finall = array_diff(array_unique($send_to_people), $this->send_blacklist);
                if (empty($send_to_people_finall)) {
                    if ($key == 'kh') {
                        $is_black = 1;
                    }
                    continue;
                }
                $ret = load_model('mailer/SendModel')->send($send_to_people_finall, $cont, $mailer_row['subject']);
            }
        }
        $is_send = 1;
        $send_msg = "发送成功";
        if ($is_black == 1) {
            $ret = $this->format_ret('1', '', '');
            $send_msg .= ',黑名单过滤，邮件改成已发送！';
        }
        if($ret['status'] == -1){
            $is_send = 2;
            $send_msg = $ret['message'];
        }
        $up = array(
            'is_send'=>$is_send,
            'send_msg'=>$send_msg,
            'send_time'=>  date('Y-m-d H:i:s'),
        );
        $this->update($up, array('id'=>$queue_id));
        return $ret;
        
    }
    
    function add_mailer_queue(&$data){
       return $this->insert_multi($data);
        
    }


    /**
     * 邮件过滤
     * @param $mail_type 0：黑名单，1：白名单
     * @return array
     */
    function get_mailer_blacklist($mail_type) {
        $sql_value = array();
        $sql = "SELECT send_mail FROM mailer_blacklist WHERE mail_type=:mail_type";
        $sql_value[':mail_type'] = $mail_type;
        $send_mail = $this->db->get_all_col($sql, $sql_value);
        return $send_mail;
    }

   
}
