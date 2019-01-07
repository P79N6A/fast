<?php 
 if (!defined('ROOT_PATH')) die('401,未授权访问 [Unauthorized]');
$data=array (
  0 => 
  array (
    'Field' => 'id',
    'Type' => 'int(11)',
    'Null' => 'NO',
    'Key' => 'PRI',
    'Default' => NULL,
    'Extra' => 'auto_increment',
  ),
  1 => 
  array (
    'Field' => 'record_type',
    'Type' => 'varchar(50)',
    'Null' => 'YES',
    'Key' => 'MUL',
    'Default' => '',
    'Extra' => '',
  ),
  2 => 
  array (
    'Field' => 'api_product',
    'Type' => 'varchar(128)',
    'Null' => 'YES',
    'Key' => 'MUL',
    'Default' => '',
    'Extra' => '',
  ),
  3 => 
  array (
    'Field' => 'record_mid_type',
    'Type' => 'varchar(128)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  4 => 
  array (
    'Field' => 'check_type',
    'Type' => 'tinyint(3)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '1',
    'Extra' => '',
  ),
);
$_the_file_ttl=1521191176;