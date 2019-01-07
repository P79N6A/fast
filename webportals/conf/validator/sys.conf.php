<?php

return array(
    //用户编辑
    'user_edit' => array(
        array('user_mobile', 'number'),
        array('user_mobile', 'length', 'value' => 11), //手机号码长度
    ),
    //修改密码
    'user_chgpwd' => array(
        array('old_user_pwd', 'require'),
        array('new_user_pwd', 'require'),
        //array('new_user_pwd','regexp','value'=>'/^[A-Za-z]+[0-9]+[A-Za-z0-9]*|[0-9]+[A-Za-z]+[A-Za-z0-9]*$/g','message'=>'密码必须由6-16个英文字母和数字的字符串组成'),
        array('dnew_user_pwd', 'require'),
        array('new_user_pwd', 'passwordstrength1'),
        array('dnew_user_pwd', 'equalTo', 'value' => 'new_user_pwd'),
    ),
);
