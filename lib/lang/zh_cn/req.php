<?php
 if (!defined('ROOT_PATH')) die('401,未授权访问 [Unauthorized]');
$req=array(

'req_err_title'=>'应用错误',
'req_err_no'=>'错误码',
'req_err_msg'=>'错误描述',
'req_err_not_found_tpl'=>'找不到主模板文件',
'req_err_500'=>'500，系统调用内部失败',
'req_err_501'=>'501，对应方法未实现',
'req_err_400'=>'400，无效请求参数',
'req_err_401'=>'401，未授权访问',
'req_err_403'=>'403，禁止访问',
'req_err_404'=>'404，未找到',

'req_log_error'=>'错误',
'req_log_warn'=>'警告',
'req_log_debug'=>'调试',
'req_log_info'=>'信息',
'req_log_except'=>'异常',

'req_redirect_msg'=>'页面跳着中，请稍后...',

'db_err_invalid_type'=>'无效数据库类型：',
'db_err_connect'=>'数据库连接错误：',
'db_err_dbname_null'=>'数据库名称为空',
'db_err_prepare'=>'准备SQL错误：',
'db_err_execute'=>'执行SQL错误：',

'date_local_char' => array('零','一','二','三','四','五','六','七','八','九','十'),
'date_local_upper' => array('零','壹','贰','叁','肆','伍','陆','柒','捌','玖','拾'),
'date_local_unit' => array('年','月','日','','时','分','秒')
);