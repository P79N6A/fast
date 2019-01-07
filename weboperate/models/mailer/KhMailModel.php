<?php

/**
 * 异常邮件通知队列
 *
 * @author wsc
 *
 */
require_model('tb/TbModel');
require_model('mailer/MailBaseModel');

class KhMailModel extends TbModel {

    //发送客户邮件
    function kh_mail_send() {
        $cfg = require_conf('mail_type');
        foreach ($cfg as $type => $conf) {
            $this->send_mail_by_type($type);
        }
    }
       //发送客户邮件根据类型
    function send_mail_by_type($type) {
        $mailobj = $this->get_mail_obj($type);
        if ($mailobj === false) {
            return false;
        }
        $kh_data = $mailobj->get_kh_data();
        foreach ($kh_data as $kh_id) {
            $this->send_kh_mail($mailobj, $kh_id, $type);
        }
    }
    
    //执行邮件发送业务
    function send_kh_mail(&$mailobj, $kh_id, $type) {
        static $mail_all = null;
        if (!isset($mail_all[$kh_id])) {
            $mail_all[$kh_id] = new MailBaseModel($kh_id);
        }
        $mail = &$mail_all[$kh_id];
        //发送邮件
        $mail->add_mail($mailobj, $type);
    }

    
    function get_mail_obj($type) {
        $type_arr = explode("_", $type);
        $mod = "";
        foreach ($type_arr as $type_str) {
            $mod.= ucfirst($type_str);
        }
        $mod.="Mail";
        return load_model('mailer/mail/' . $mod);
    }

}
