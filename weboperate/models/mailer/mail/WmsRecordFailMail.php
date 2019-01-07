<?php

require_model('mailer/mail/MailAbs');

class WmsRecordFailMail extends MailAbs {

    var $type_arr = array('pur_notice' => '采购通知单',
        'sell_record' => '订单', 'sell_return' => '退单',
        'shift_out' => '移仓出库', 'shift_in' => '移仓入库',
        'pur_return_notice' => '采购退货', 'wbm_notice' => '批发通知',
        'wbm_return_notice' => '批发退货', 'adjust' => '调整');
    

    function get_mail_content($record_time) {
        //设置邮件标题
        $this->subject = "仓储单据接口异常"; 
  
        $record_time = date('Y-m-d H:i:s', $record_time);

        $sql_b2b = "select record_code,record_type,action_time,action_msg from wms_b2b_log where (action='wms_response_upload_fail' or  action='wms_response_cancel_fail') and action_time>'{$record_time}'";
        $data_b2b = $this->db->get_all($sql_b2b);
        $mail_content = '';
        foreach ($data_b2b as $val) {
          $this->set_mail_content($val, $mail_content);
        }


        $sql_oms = " select record_code,record_type,action_time,action_msg  from wms_oms_log where (action='wms_response_upload_fail' or  action='wms_response_cancel_fail') and action_time>'{$record_time}'";
        $data_oms = $this->db->get_all($sql_oms);
        foreach ($data_oms as $val) {
                $this->set_mail_content($val, $mail_content);
        }
        //添加邮件
        $this->add_mail_data($mail_content);
        
        
    }

    private function set_mail_content(&$row, &$mail_content) {
       $mail_content .= $this->type_arr[$row['record_type']].$row['record_code']."({$row['action_time']}):".$row['action_msg'];
    }

     function get_kh_data() {
        $pra_enddate = date('Y-m-d H:i:s', strtotime("-7 days"));
        $sql = "select a.vra_kh_id as kh_id from osp_valueorder_auth a
        INNER JOIN osp_valueserver_detail d ON a.vra_server_id=d.value_id
        INNER JOIN osp_productorder_auth pa  ON pa.pra_kh_id=a.vra_kh_id
        where d.vd_busine_type=3 AND pa.pra_enddate>'{$pra_enddate}'";
        $data = CTX()->db->get_all($sql);
        $kh_arr = array();
        foreach ($data as $val) {
            $kh_arr[] = $val['kh_id'];
        }
        return $kh_arr;
    }

}
