<?php 
 if (!defined('ROOT_PATH')) die('401,未授权访问 [Unauthorized]');
$data=array (
  0 => 
  array (
    'Field' => 'action_id',
    'Type' => 'int(11) unsigned',
    'Null' => 'NO',
    'Key' => 'PRI',
    'Default' => NULL,
    'Extra' => 'auto_increment',
  ),
  1 => 
  array (
    'Field' => 'parent_id',
    'Type' => 'int(11) unsigned',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => '0',
    'Extra' => '',
  ),
  2 => 
  array (
    'Field' => 'type',
    'Type' => 'varchar(10)',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => '',
    'Extra' => '',
  ),
  3 => 
  array (
    'Field' => 'action_name',
    'Type' => 'varchar(100)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  4 => 
  array (
    'Field' => 'action_code',
    'Type' => 'varchar(255)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  5 => 
  array (
    'Field' => 'sort_order',
    'Type' => 'int(11) unsigned',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  6 => 
  array (
    'Field' => 'appid',
    'Type' => 'int(11)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  7 => 
  array (
    'Field' => 'other_priv_type',
    'Type' => 'int(11)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  8 => 
  array (
    'Field' => 'status',
    'Type' => 'tinyint(3) unsigned',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '1',
    'Extra' => '',
  ),
  9 => 
  array (
    'Field' => 'ui_entrance',
    'Type' => 'tinyint(3)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
);
$_the_file_ttl=1521106985;