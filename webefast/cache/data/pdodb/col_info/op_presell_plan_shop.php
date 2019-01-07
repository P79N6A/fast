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
    'Key' => 'MUL',
    'Default' => NULL,
    'Extra' => '',
  ),
  2 => 
  array (
    'Field' => 'shop_code',
    'Type' => 'varchar(128)',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => NULL,
    'Extra' => '',
  ),
);
$_the_file_ttl=1520846819;