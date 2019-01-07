<?php 
 if (!defined('ROOT_PATH')) die('401,未授权访问 [Unauthorized]');
$data=array (
  0 => 
  array (
    'Field' => 'detail_id',
    'Type' => 'int(11)',
    'Null' => 'NO',
    'Key' => 'PRI',
    'Default' => NULL,
    'Extra' => 'auto_increment',
  ),
  1 => 
  array (
    'Field' => 'refund_id',
    'Type' => 'varchar(30)',
    'Null' => 'YES',
    'Key' => 'MUL',
    'Default' => NULL,
    'Extra' => '',
  ),
  2 => 
  array (
    'Field' => 'tid',
    'Type' => 'varchar(30)',
    'Null' => 'YES',
    'Key' => 'MUL',
    'Default' => NULL,
    'Extra' => '',
  ),
  3 => 
  array (
    'Field' => 'oid',
    'Type' => 'varchar(30)',
    'Null' => 'YES',
    'Key' => 'MUL',
    'Default' => NULL,
    'Extra' => '',
  ),
  4 => 
  array (
    'Field' => 'goods_code',
    'Type' => 'varchar(30)',
    'Null' => 'YES',
    'Key' => 'MUL',
    'Default' => NULL,
    'Extra' => '',
  ),
  5 => 
  array (
    'Field' => 'title',
    'Type' => 'varchar(100)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  6 => 
  array (
    'Field' => 'price',
    'Type' => 'float(7,2)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  7 => 
  array (
    'Field' => 'num',
    'Type' => 'int(10) unsigned',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  8 => 
  array (
    'Field' => 'refund_price',
    'Type' => 'float(7,2)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  9 => 
  array (
    'Field' => 'sku_properties_name',
    'Type' => 'varchar(50)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  10 => 
  array (
    'Field' => 'goods_barcode',
    'Type' => 'varchar(128)',
    'Null' => 'YES',
    'Key' => 'MUL',
    'Default' => NULL,
    'Extra' => '',
  ),
  11 => 
  array (
    'Field' => 'sku_id',
    'Type' => 'varchar(30)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
);
$_the_file_ttl=1521689256;