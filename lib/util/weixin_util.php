<?php 
include_once ROOT_PATH."conf/crm_config.php";
require_lib("util/crm_util");
function get_access_token(){
    $AppId = $GLOBALS['context']->weixin['AppId'];
    $AppSecret = $GLOBALS['context']->weixin['AppSecret'];
    $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$AppId."&secret=".$AppSecret;
    $ret = post_submit($url);

    $ret = object_to_array(json_decode($ret));
    return $ret['access_token'];
}

function get_openid($code){
    $AppId = $GLOBALS['context']->weixin['AppId'];
    $AppSecret = $GLOBALS['context']->weixin['AppSecret'];
    $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$AppId."&secret=".$AppSecret."&code=".$code."&grant_type=authorization_code";
    
    $ret = post_submit($url);
    $ret = object_to_array(json_decode($ret));

    return isset($ret['openid'])?$ret['openid']:"";
}

function send_weixin_message($open_id,$message){
    $access_token = get_access_token();

    $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$access_token;
    $arr = array();
    $arr['touser'] = $open_id;
    $arr['msgtype'] = "text";
    $arr['text']['content'] = $message;

    $ret = post_submit($url,json_encode_zh($arr));
    $ret = object_to_array(json_decode($ret));
    if(isset($ret['errcode']) && $ret['errcode'] == 0){
        return return_value(1,"发送成功");
    }else{
        return return_value(-1,$ret['errmsg'],$ret);
    }
}