<?php 
 if (!defined('ROOT_PATH')) die('401,未授权访问 [Unauthorized]');
$data=array (
  0 => 
  array (
    'Field' => 'return_package_id',
    'Type' => 'int(11) unsigned',
    'Null' => 'NO',
    'Key' => 'PRI',
    'Default' => NULL,
    'Extra' => 'auto_increment',
  ),
  1 => 
  array (
    'Field' => 'return_type',
    'Type' => 'tinyint(1)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  2 => 
  array (
    'Field' => 'init_code',
    'Type' => 'varchar(50)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  3 => 
  array (
    'Field' => 'return_package_code',
    'Type' => 'varchar(20)',
    'Null' => 'NO',
    'Key' => 'UNI',
    'Default' => '',
    'Extra' => '',
  ),
  4 => 
  array (
    'Field' => 'sell_return_code',
    'Type' => 'varchar(20)',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => '',
    'Extra' => '',
  ),
  5 => 
  array (
    'Field' => 'sell_record_code',
    'Type' => 'varchar(20)',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => '',
    'Extra' => '',
  ),
  6 => 
  array (
    'Field' => 'deal_code',
    'Type' => 'varchar(100)',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => '',
    'Extra' => '',
  ),
  7 => 
  array (
    'Field' => 'create_time',
    'Type' => 'timestamp',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => '0000-00-00 00:00:00',
    'Extra' => '',
  ),
  8 => 
  array (
    'Field' => 'shop_code',
    'Type' => 'varchar(128)',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => '',
    'Extra' => '',
  ),
  9 => 
  array (
    'Field' => 'store_code',
    'Type' => 'varchar(128)',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => '',
    'Extra' => '',
  ),
  10 => 
  array (
    'Field' => 'stock_date',
    'Type' => 'date',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0000-00-00',
    'Extra' => '',
  ),
  11 => 
  array (
    'Field' => 'return_order_status',
    'Type' => 'int(11)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  12 => 
  array (
    'Field' => 'return_name',
    'Type' => 'varchar(20)',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => '',
    'Extra' => '',
  ),
  13 => 
  array (
    'Field' => 'return_country',
    'Type' => 'bigint(20)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  14 => 
  array (
    'Field' => 'return_province',
    'Type' => 'bigint(20)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  15 => 
  array (
    'Field' => 'return_city',
    'Type' => 'bigint(20)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  16 => 
  array (
    'Field' => 'return_district',
    'Type' => 'bigint(20)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  17 => 
  array (
    'Field' => 'return_street',
    'Type' => 'bigint(20)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  18 => 
  array (
    'Field' => 'return_address',
    'Type' => 'varchar(100)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  19 => 
  array (
    'Field' => 'return_addr',
    'Type' => 'varchar(100)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  20 => 
  array (
    'Field' => 'customer_address_id',
    'Type' => 'int(11)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  21 => 
  array (
    'Field' => 'return_mobile',
    'Type' => 'varchar(32)',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => '',
    'Extra' => '',
  ),
  22 => 
  array (
    'Field' => 'return_phone',
    'Type' => 'varchar(32)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  23 => 
  array (
    'Field' => 'return_express_code',
    'Type' => 'varchar(20)',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => '',
    'Extra' => '',
  ),
  24 => 
  array (
    'Field' => 'return_express_no',
    'Type' => 'varchar(40)',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => '',
    'Extra' => '',
  ),
  25 => 
  array (
    'Field' => 'tag',
    'Type' => 'tinyint(1)',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => '0',
    'Extra' => '',
  ),
  26 => 
  array (
    'Field' => 'remark',
    'Type' => 'varchar(255)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  27 => 
  array (
    'Field' => 'lastchanged',
    'Type' => 'timestamp',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => 'CURRENT_TIMESTAMP',
    'Extra' => 'on update CURRENT_TIMESTAMP',
  ),
  28 => 
  array (
    'Field' => 'buyer_name',
    'Type' => 'varchar(20)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  29 => 
  array (
    'Field' => 'receive_person',
    'Type' => 'varchar(20)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  30 => 
  array (
    'Field' => 'receive_time',
    'Type' => 'datetime',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0000-00-00 00:00:00',
    'Extra' => '',
  ),
  31 => 
  array (
    'Field' => 'return_buyer_memo',
    'Type' => 'varchar(255)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  32 => 
  array (
    'Field' => 'return_remark',
    'Type' => 'varchar(255)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  33 => 
  array (
    'Field' => 'is_exchange_goods',
    'Type' => 'tinyint(1)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  34 => 
  array (
    'Field' => 'customer_code',
    'Type' => 'varchar(30)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
);
$_the_file_ttl=1521700084;