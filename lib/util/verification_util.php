<?php 
function set_user_session($user){
    CTX()->set_session("user_id",$user['user_id']);
    CTX()->set_session("user_code",$user['user_code']);
}