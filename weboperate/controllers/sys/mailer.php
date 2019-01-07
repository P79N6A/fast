<?php

/*
 * 邮件通知
 */

class Mailer {

    //发送邮件
    function send(array & $request, array & $response, array & $app) {
           load_model('mailer/QueueModel')->send_queue();
           $response['status'] = 1;
    }
    
    //发送邮件
    function send_monitor_mail(array & $request, array & $response, array & $app){
          load_model('mailer/KhMailModel')->kh_mail_send();
             $response['status'] = 1;
    }
    
}