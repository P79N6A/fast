<?php 
 if (!defined('ROOT_PATH')) die('401,未授权访问 [Unauthorized]');
$data=array (
  0 => 
  array (
    'Field' => 'id',
    'Type' => 'int(10) unsigned',
    'Null' => 'NO',
    'Key' => 'PRI',
    'Default' => NULL,
    'Extra' => 'auto_increment',
  ),
  1 => 
  array (
    'Field' => 'mid_code',
    'Type' => 'varchar(50)',
    'Null' => 'YES',
    'Key' => 'MUL',
    'Default' => '',
    'Extra' => '',
  ),
  2 => 
  array (
    'Field' => 'join_sys_code',
    'Type' => 'varchar(128)',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => '',
    'Extra' => '',
  ),
  3 => 
  array (
    'Field' => 'join_sys_type',
    'Type' => 'tinyint(3) unsigned',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  4 => 
  array (
    'Field' => 'outside_type',
    'Type' => 'tinyint(3) unsigned',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  5 => 
  array (
    'Field' => 'outside_code',
    'Type' => 'varchar(128)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  6 => 
  array (
    'Field' => 'param_val1',
    'Type' => 'varchar(128)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  7 => 
  array (
    'Field' => 'param_val2',
    'Type' => 'varchar(128)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  8 => 
  array (
    'Field' => 'param_val3',
    'Type' => 'varchar(128)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  9 => 
  array (
    'Field' => 'param_val4',
    'Type' => 'varchar(128)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  10 => 
  array (
    'Field' => 'param_val5',
    'Type' => 'varchar(128)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
);
$_the_file_ttl=1521104813;