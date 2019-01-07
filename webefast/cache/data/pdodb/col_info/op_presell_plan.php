<?php 
 if (!defined('ROOT_PATH')) die('401,未授权访问 [Unauthorized]');
$data=array (
  0 => 
  array (
    'Field' => 'id',
    'Type' => 'int(11) unsigned',
    'Null' => 'NO',
    'Key' => 'PRI',
    'Default' => NULL,
    'Extra' => 'auto_increment',
  ),
  1 => 
  array (
    'Field' => 'plan_code',
    'Type' => 'varchar(64)',
    'Null' => 'NO',
    'Key' => 'UNI',
    'Default' => NULL,
    'Extra' => '',
  ),
  2 => 
  array (
    'Field' => 'plan_name',
    'Type' => 'varchar(64)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  3 => 
  array (
    'Field' => 'start_time',
    'Type' => 'int(11)',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => '0',
    'Extra' => '',
  ),
  4 => 
  array (
    'Field' => 'end_time',
    'Type' => 'int(11)',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => '0',
    'Extra' => '',
  ),
  5 => 
  array (
    'Field' => 'create_person',
    'Type' => 'varchar(20)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  6 => 
  array (
    'Field' => 'create_time',
    'Type' => 'int(11)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  7 => 
  array (
    'Field' => 'lastchanged',
    'Type' => 'timestamp',
    'Null' => 'NO',
    'Key' => '',
    'Default' => 'CURRENT_TIMESTAMP',
    'Extra' => 'on update CURRENT_TIMESTAMP',
  ),
  8 => 
  array (
    'Field' => 'sync_num',
    'Type' => 'tinyint(3)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  9 => 
  array (
    'Field' => 'last_sync_time',
    'Type' => 'int(11)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  10 => 
  array (
    'Field' => 'exit_status',
    'Type' => 'int(2)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
);
$_the_file_ttl=1520846819;