<?php 
 if (!defined('ROOT_PATH')) die('401,未授权访问 [Unauthorized]');
$data=array (
  0 => 
  array (
    'Field' => 'shop_api_id',
    'Type' => 'int(11) unsigned',
    'Null' => 'NO',
    'Key' => 'PRI',
    'Default' => NULL,
    'Extra' => 'auto_increment',
  ),
  1 => 
  array (
    'Field' => 'shop_code',
    'Type' => 'varchar(40)',
    'Null' => 'NO',
    'Key' => 'UNI',
    'Default' => NULL,
    'Extra' => '',
  ),
  2 => 
  array (
    'Field' => 'source',
    'Type' => 'varchar(20)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  3 => 
  array (
    'Field' => 'api',
    'Type' => 'text',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  4 => 
  array (
    'Field' => 'tb_shop_type',
    'Type' => 'varchar(10)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => 'C',
    'Extra' => '',
  ),
  5 => 
  array (
    'Field' => 'order_update_time',
    'Type' => 'datetime',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  6 => 
  array (
    'Field' => 'order_return_update_time',
    'Type' => 'datetime',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  7 => 
  array (
    'Field' => 'on_sale_goods_update_time',
    'Type' => 'datetime',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  8 => 
  array (
    'Field' => 'inv_goods_update_time',
    'Type' => 'datetime',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  9 => 
  array (
    'Field' => 'remark',
    'Type' => 'varchar(255)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  10 => 
  array (
    'Field' => 'lastchanged',
    'Type' => 'timestamp',
    'Null' => 'NO',
    'Key' => '',
    'Default' => 'CURRENT_TIMESTAMP',
    'Extra' => 'on update CURRENT_TIMESTAMP',
  ),
  11 => 
  array (
    'Field' => 'is_xiaodai',
    'Type' => 'int(4)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  12 => 
  array (
    'Field' => 'nick',
    'Type' => 'varchar(40)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  13 => 
  array (
    'Field' => 'app_key',
    'Type' => 'varchar(200)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  14 => 
  array (
    'Field' => 'app_secret',
    'Type' => 'varchar(200)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  15 => 
  array (
    'Field' => 'session_key',
    'Type' => 'varchar(200)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  16 => 
  array (
    'Field' => 'extra_params',
    'Type' => 'text',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  17 => 
  array (
    'Field' => 'kh_id',
    'Type' => 'int(11)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  18 => 
  array (
    'Field' => 'vender_code',
    'Type' => 'varchar(128)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
);
$_the_file_ttl=1521527510;