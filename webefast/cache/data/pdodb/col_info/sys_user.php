<?php 
 if (!defined('ROOT_PATH')) die('401,未授权访问 [Unauthorized]');
$data=array (
  0 => 
  array (
    'Field' => 'user_id',
    'Type' => 'int(11) unsigned',
    'Null' => 'NO',
    'Key' => 'PRI',
    'Default' => NULL,
    'Extra' => 'auto_increment',
  ),
  1 => 
  array (
    'Field' => 'role_id',
    'Type' => 'varchar(128)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  2 => 
  array (
    'Field' => 'user_code',
    'Type' => 'varchar(64)',
    'Null' => 'YES',
    'Key' => 'UNI',
    'Default' => '',
    'Extra' => '',
  ),
  3 => 
  array (
    'Field' => 'login_type',
    'Type' => 'tinyint(3)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  4 => 
  array (
    'Field' => 'p_code',
    'Type' => 'varchar(64)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  5 => 
  array (
    'Field' => 'shop_code',
    'Type' => 'varchar(64)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  6 => 
  array (
    'Field' => 'org_code',
    'Type' => 'varchar(128)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '000',
    'Extra' => '',
  ),
  7 => 
  array (
    'Field' => 'user_name',
    'Type' => 'varchar(128)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  8 => 
  array (
    'Field' => 'password',
    'Type' => 'varchar(128)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  9 => 
  array (
    'Field' => 'style',
    'Type' => 'varchar(128)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  10 => 
  array (
    'Field' => 'status',
    'Type' => 'int(4)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '1',
    'Extra' => '',
  ),
  11 => 
  array (
    'Field' => 'create_mode',
    'Type' => 'int(4)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '1',
    'Extra' => '',
  ),
  12 => 
  array (
    'Field' => 'init_password',
    'Type' => 'varchar(128)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  13 => 
  array (
    'Field' => 'department_code',
    'Type' => 'varchar(64)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  14 => 
  array (
    'Field' => 'position_code',
    'Type' => 'varchar(64)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  15 => 
  array (
    'Field' => 'type',
    'Type' => 'int(4)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  16 => 
  array (
    'Field' => 'relation_shop',
    'Type' => 'varchar(64)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  17 => 
  array (
    'Field' => 'rebate',
    'Type' => 'decimal(4,3)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '1.000',
    'Extra' => '',
  ),
  18 => 
  array (
    'Field' => 'sex',
    'Type' => 'varchar(64)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  19 => 
  array (
    'Field' => 'birthday',
    'Type' => 'date',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  20 => 
  array (
    'Field' => 'phone',
    'Type' => 'varchar(64)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  21 => 
  array (
    'Field' => 'tel',
    'Type' => 'varchar(64)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  22 => 
  array (
    'Field' => 'email',
    'Type' => 'varchar(64)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  23 => 
  array (
    'Field' => 'address',
    'Type' => 'varchar(255)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  24 => 
  array (
    'Field' => 'province',
    'Type' => 'varchar(64)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  25 => 
  array (
    'Field' => 'city',
    'Type' => 'varchar(64)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  26 => 
  array (
    'Field' => 'is_salesman',
    'Type' => 'tinyint(4)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '1',
    'Extra' => '',
  ),
  27 => 
  array (
    'Field' => 'is_customer',
    'Type' => 'tinyint(4)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  28 => 
  array (
    'Field' => 'is_clerk',
    'Type' => 'tinyint(4)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  29 => 
  array (
    'Field' => 'is_manage',
    'Type' => 'int(4)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  30 => 
  array (
    'Field' => 'is_work',
    'Type' => 'int(4)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '1',
    'Extra' => '',
  ),
  31 => 
  array (
    'Field' => 'is_login',
    'Type' => 'int(4)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '1',
    'Extra' => '',
  ),
  32 => 
  array (
    'Field' => 'is_taobao',
    'Type' => 'int(4)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  33 => 
  array (
    'Field' => 'last_login_time',
    'Type' => 'datetime',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  34 => 
  array (
    'Field' => 'last_login_ip',
    'Type' => 'varchar(128)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  35 => 
  array (
    'Field' => 'sys',
    'Type' => 'int(4)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  36 => 
  array (
    'Field' => 'weixin_id',
    'Type' => 'varchar(128)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  37 => 
  array (
    'Field' => 'favorites',
    'Type' => 'varchar(255)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  38 => 
  array (
    'Field' => 'remark',
    'Type' => 'varchar(255)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  39 => 
  array (
    'Field' => 'lastchanged',
    'Type' => 'timestamp',
    'Null' => 'YES',
    'Key' => '',
    'Default' => 'CURRENT_TIMESTAMP',
    'Extra' => 'on update CURRENT_TIMESTAMP',
  ),
  40 => 
  array (
    'Field' => 'trd_id',
    'Type' => 'varchar(64)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  41 => 
  array (
    'Field' => 'trd_type',
    'Type' => 'varchar(32)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  42 => 
  array (
    'Field' => 'trd_time',
    'Type' => 'varchar(64)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  43 => 
  array (
    'Field' => 'is_default_guide',
    'Type' => 'tinyint(4)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  44 => 
  array (
    'Field' => 'is_strong',
    'Type' => 'int(4)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  45 => 
  array (
    'Field' => 'login_fail_num',
    'Type' => 'tinyint(1)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  46 => 
  array (
    'Field' => 'session_id',
    'Type' => 'varchar(64)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  47 => 
  array (
    'Field' => 'create_person',
    'Type' => 'varchar(50)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  48 => 
  array (
    'Field' => 'create_time',
    'Type' => 'datetime',
    'Null' => 'NO',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  49 => 
  array (
    'Field' => 'echarts_index',
    'Type' => 'int(11)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
);
$_the_file_ttl=1546852763;