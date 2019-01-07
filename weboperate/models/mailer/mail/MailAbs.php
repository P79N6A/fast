<?php

abstract class MailAbs {

    var $maildata = array();
    var $mailrow = array();
    var $record_data = array();
    var $db;
    var $subject = "系统异常";
    function init(&$db, &$mail_row) {
        $this->db = $db;
        
        $this->mailrow = $mail_row;
        //设置邮件标题
        $this->mailrow['subject'] = $this->subject;
    }

   abstract function get_mail_content($record_time);

    function add_mail_data($content,$subject='') {
		if(!empty($content)){
			$new_mail_row['cont_body'] = $content;
			$new_mail_row['subject'] = empty($subject)?$this->subject:$subject;

			$new_mail_row = array_merge($new_mail_row, $this->mailrow);
			$this->maildata[] = $new_mail_row;
		}
    }

     function get_kh_data() {
        $pra_enddate = date('Y-m-d H:i:s', strtotime("-7 days"));
        $sql = "    select d.rem_db_khid as kh_id  from osp_rdsextmanage_db d
        INNER JOIN osp_productorder_auth p  ON p.pra_kh_id= d.rem_db_khid
        where d.rem_db_is_bindkh=1 AND p.pra_enddate>'{$pra_enddate}'";
        $data = CTX()->db->get_all($sql);
        $kh_data = array();
        foreach ($data as $val) {
            $kh_data[] = $val['kh_id'];
        }
        return $kh_data;
    }

}
