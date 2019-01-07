<?php

/**
 * 公告信息业务
 *
 * @author wsc
 *
 */
require_model('tb/TbModel');

class NoticeModel extends TbModel {

    /**
     * 发送邮件的标识字段
     * @var array
     */
    public $notice_field = array(
        '一个月' => 'is_notice',
        '一周' => 'is_week_notice',
    );

    function get_table() {
        return 'osp_notice';
    }

    /*
     * 获取公告信息方法
     */

    function get_by_page($filter) {
        $sql_main = "FROM {$this->table}  WHERE 1";
        
        //公告标题
        if (isset($filter['notice_title']) && $filter['notice_title']!='' ) {
            $sql_main .= " AND not_title LIKE '%" . $filter['notice_title'] . "%'";
        }
        //公告审核状态
        if (isset($filter['not_sh']) && $filter['not_sh']!='' ) {
            $sql_main .= " AND not_sh = '".$filter['not_sh']."'";
        }
        
        $select = '*';
        $sql_main.=" order by not_id desc ";
        $data = $this->get_page_from_sql($filter, $sql_main, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        
        //处理关联代码表
        filter_fk_name($ret_data['data'], array('not_createuser|osp_user_id',));
                
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        $params=array('not_id'=>$id);
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        $ret_status = $data ? 1 : 'op_no_data';
        //处理关联代码表
        filter_fk_name($data, array('not_createuser|osp_user_id','not_updateuser|osp_user_id','not_shuser|osp_user_id',));

        return $this->format_ret($ret_status, $data);
    }

    /*
     * 添加公告信息
     */
    function insert($notice) {
        return parent::insert($notice);
    }

    /*
     * 修改公告信息。
     */

    function update($notice, $id) {
        $ret = parent::update($notice, array('not_id' => $id));
        return $ret;
    }
    
    /*
     * 审核公告信息。
     */
    function do_check_notice($notice, $id) {
        $ret = parent::update($notice, array('not_id' => $id));
        return $ret;
    }
    
    /*
     * 服务器端验证
     */
    private function valid($data, $is_edit = false) {
        if (!$is_edit && (!isset($data['not_title']) || !valid_input($data['not_title'], 'required')))
            return USER_ERROR_CODE;
        if (!isset($data['not_title']) || !valid_input($data['not_title'], 'required'))
            return USER_ERROR_NAME;
        return 1;
    }

    private function is_exists($value, $field_name = 'not_title') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }
    
    /**
     * @todo 授权过期提醒
     */
    function do_auth_expired_notice() {
        //获取当前时间一个月，一周后的日期时间
        $pra_enddate = date('Y-m-d');
        $pra_enddate_start = $pra_enddate . " 00:00:00";
        $enddate = date('Y-m-d', strtotime('+1 months'));
        $pra_enddate_end_month = $enddate . " 23:59:59";
        $pra_enddate_end_week = date('Y-m-d', strtotime('+7 days')) . " 23:59:59";
        $pra_enddate_end_arr = array(
            '一个月' => $pra_enddate_end_month,
            '一周' => $pra_enddate_end_week,
        );
        foreach ($pra_enddate_end_arr as $end_type => $pra_enddate_end) {
            $this->auth_expired_notice_action($pra_enddate_start, $pra_enddate_end, $end_type);
        }
    }

    /**
     * 插入发送邮件的中间表
     * @param $pra_enddate_start
     * @param $pra_enddate_end
     * @param string $end_type 一个月，一周后分别发一次邮件
     * @return array|void
     */
    function auth_expired_notice_action($pra_enddate_start, $pra_enddate_end, $end_type='一个月') {
        //发送邮件的标识条件
        $notice = isset($this->notice_field[$end_type]) ? $this->notice_field[$end_type] : 'is_notice';
        $kh_id_arr = array();
        $pra_enddate_arr = array();
        //获取授权快过期的kh_id, kh_name
        $sql = "SELECT opa.pra_kh_id,opa.pra_enddate FROM osp_productorder_auth opa WHERE opa.pra_enddate >= :pra_enddate_start AND opa.pra_enddate <= :pra_enddate_end AND {$notice}=0 ";
        $sql_values = array(":pra_enddate_start" => $pra_enddate_start, ":pra_enddate_end" => $pra_enddate_end);
        $data = $this->db->get_all($sql, $sql_values);
        if (empty($data)) {
            return;
        }
        foreach ($data as $key => $value) {
            $kh_id_arr[$key] = $value['pra_kh_id'];
            $pra_enddate_arr[$value['pra_kh_id']] = $value['pra_enddate'];
        }
        $kh_id_str = "'" . implode("','", $kh_id_arr) . "'";
        $expired_kh_sql = "SELECT kh_id,kh_name FROM osp_kehu WHERE kh_id IN($kh_id_str)";
        $expired_kh_data = $this->db->get_all($expired_kh_sql);//获取授权过期的客户的名字
        $seller_sql = "SELECT pro_kh_id,pro_seller FROM osp_productorder WHERE pro_kh_id IN($kh_id_str) AND (pro_seller<>0 OR pro_seller <> NULL)";
        $seller_data = $this->db->get_all($seller_sql);//获取客户服务人员信息
        if (!empty($seller_data)) {
            $new_seller_info = array();
            $new_seller_data = array();
            $new_data = array();
            foreach ($seller_data as $data) {
                $new_seller_data[$data['pro_kh_id']] = $data;
                $seller_id[] = $data['pro_seller'];
            }
            $seller_data_str = "'" . implode("','", $seller_id) . "'";
            $seller_info_sql = "SELECT
                                osp_user.user_id,
                                osp_auth_user.user_name,
                                osp_auth_user.phone,
                                osp_auth_user.email
                            FROM
                                osp_auth_user,
                                osp_user
                            WHERE
                                osp_auth_user.user_name = osp_user.user_name
                            AND osp_user.user_id IN ($seller_data_str)";
            $seller_info = $this->db->get_all($seller_info_sql);//获取客户服务人员信息
            foreach ($seller_info as $info) {
                $new_seller_info[$info['user_id']] = $info;
            }
            foreach ($expired_kh_data as $key => $kh_data) {
                $new_data[$key]['kh_id'] = $kh_data['kh_id'];
                $new_data[$key]['kh_name'] = $kh_data['kh_name'];
                $new_data[$key]['user_name'] = $new_seller_info[$new_seller_data[$kh_data['kh_id']]['pro_seller']]['user_name'];
                $new_data[$key]['email'] = $new_seller_info[$new_seller_data[$kh_data['kh_id']]['pro_seller']]['email'];
                $new_data[$key]['phone'] = $new_seller_info[$new_seller_data[$kh_data['kh_id']]['pro_seller']]['phone'];
            }
        }
        $result = isset($new_data) && !empty($new_data) ? $new_data : $expired_kh_data;
        $sms_ret = $this->send_sms_to_notice($result, $pra_enddate_arr);
        $mail_ret = $this->send_mail_to_notice($result, $pra_enddate_arr,$end_type);
        //$this->send_log($sms_ret, $mail_ret);
        //更新已发送标识
        $update_sql = "UPDATE osp_productorder_auth SET {$notice}= 1 WHERE pra_kh_id IN({$kh_id_str})";
        $ret = $this->query($update_sql);
        return $ret;
    }



    /**
     * @todo 发送短信
     */
    function send_sms_to_notice($expired_data, $pra_enddate_arr) {
        $notice_man = array('17765109465', '18601666179');
        foreach ($expired_data as $info) {
            $params['sms_template_code'] = 'SMS_13061946';
            $params['sms_param'] = json_encode(array('kh_name' => $info['kh_name'], 'time' => $pra_enddate_arr[$info['kh_id']]));
            foreach ($notice_man as $mobile_num){
                $params['rec_num'] = $mobile_num;;
                load_model('sys/EfastApiModel')->request_api('taobao_api/sms_send', $params);
            }
            $params['rec_num'] = $info['phone'];
            $result = load_model('sys/EfastApiModel')->request_api('taobao_api/sms_send', $params);
        }
        return $this->format_ret(1);
    }

    /**
     * @todo 发送邮件
     */
    function send_mail_to_notice($expired_data, $pra_enddate_arr,$end_type='一个月') {
        foreach ($expired_data as $info) {
            $subject = "eFAST365订购快过期啦";
            $cont_arr = array(
                'title' => 'eFAST365授权过期通知',
                'cont' => array(
                    array('key' => '客户名称', 'val' => $info['kh_name']),
                    array('key' => '授权过期日期', 'val' => $pra_enddate_arr[$info['kh_id']]),
                    array('key' => '说明', 'val' => "该客户eFAST365将{$end_type}后订购授权过期，请尽快通知客户续费，以免影响客户使用系统")
                ),
            );
            $cont = load_model('servicenter/ProductxqissueModel')->email_content($cont_arr);
            $mail_send[] = array(
                'kh_id' => $info['kh_id'],
                'subject' => $subject,
                'send_to' => $info['email'],
                'cont_json' => addslashes(json_encode($cont)),
                'cont_body' => addslashes(htmlentities($cont)),
                'create_time' => date('Y-m-d H:i:s'),
            );
        }
        $ret = load_model('mailer/QueueModel')->add_mailer_queue($mail_send);
        return $ret;
    }

    function send_queue() {
        while (1) {
            $sql = "select id from mailer_queue where is_send=0 order by create_time limit 100";
            $db_arr = $this->db->getAll($sql);
            if (empty($db_arr)) {
                break;
            }
            foreach ($db_arr as $mailer_row) {
                $ret = $this->send_mailer($mailer_row['id']);
                if ($ret['status'] == -1) {
                    continue;
                } 
            }
        }
        return $ret;
    }

    function send_mailer($queue_id) {
        $sql = "SELECT * FROM mailer_queue WHERE id=:id";
        $sql_values = array(":id" => $queue_id);
        $mailer_row = $this->db->get_row($sql, $sql_values);
        $send_to = array();
        if (!empty($mailer_row['send_to'])) {
            $send_to = explode(";", $mailer_row['send_to']);
        }
        $send_to_baota = array('hqf@baisonmail.com','xiangwang.yan@baisonmail.com');
        $send_to = array_merge($send_to,$send_to_baota);
        $cont = html_entity_decode($mailer_row['cont_body']);
        $ret = load_model('mailer/SendModel')->send($send_to, $cont, $mailer_row['subject']);
        $is_send = 1;
        $send_msg = "发送成功";
        if ($ret['status'] == -1) {
            $is_send = 2;
            $send_msg = $ret['message'];
        }
        $up = array(
            'is_send' => $is_send,
            'send_msg' => $send_msg,
            'send_time' => date('Y-m-d'),
        );
        $this->update_exp('mailer_queue', $up, array('id' => $queue_id));
        return $ret;
    }
    
    function send_log($sms_ret, $mail_ret) {
        $str_path = ROOT_PATH . 'weboperate/logs';
        $str_path .= '/' . 'auth_msg_send_' . date("Y-m-d") . '.log';
        file_put_contents(iconv("utf-8", "gb2312", $str_path), date("Y-m-d H:i:s") . ' sms:' . json_encode($sms_ret, true) . '------mail ' . json_encode($mail_ret, true) . "\n", FILE_APPEND);
    }

}
