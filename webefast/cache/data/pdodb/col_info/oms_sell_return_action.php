<?php 
 if (!defined('ROOT_PATH')) die('401,未授权访问 [Unauthorized]');
$data=array (
  0 => 
  array (
    'Field' => 'sell_return_action_id',
    'Type' => 'int(11) unsigned',
    'Null' => 'NO',
    'Key' => 'PRI',
    'Default' => NULL,
    'Extra' => 'auto_increment',
  ),
  1 => 
  array (
    'Field' => 'sell_return_code',
    'Type' => 'varchar(30)',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => NULL,
    'Extra' => '',
  ),
  2 => 
  array (
    'Field' => 'user_code',
    'Type' => 'varchar(30)',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => NULL,
    'Extra' => '',
  ),
  3 => 
  array (
    'Field' => 'user_name',
    'Type' => 'varchar(30)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  4 => 
  array (
    'Field' => 'return_order_status',
    'Type' => 'tinyint(1) unsigned',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  5 => 
  array (
    'Field' => 'return_shipping_status',
    'Type' => 'tinyint(1) unsigned',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  6 => 
  array (
    'Field' => 'finance_check_status',
    'Type' => 'tinyint(1)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  7 => 
  array (
    'Field' => 'action_name',
    'Type' => 'varchar(50)',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => '',
    'Extra' => '',
  ),
  8 => 
  array (
    'Field' => 'action_note',
    'Type' => 'varchar(255)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  9 => 
  array (
    'Field' => 'create_time',
    'Type' => 'timestamp',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => 'CURRENT_TIMESTAMP',
    'Extra' => '',
  ),
);
$_the_file_ttl=1521700084;