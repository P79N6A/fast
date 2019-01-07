<?php 
 if (!defined('ROOT_PATH')) die('401,未授权访问 [Unauthorized]');
$data=array (
  0 => 
  array (
    'Field' => 'aid',
    'Type' => 'int(11) unsigned',
    'Null' => 'NO',
    'Key' => 'PRI',
    'Default' => NULL,
    'Extra' => 'auto_increment',
  ),
  1 => 
  array (
    'Field' => 'shop_code',
    'Type' => 'varchar(50)',
    'Null' => 'YES',
    'Key' => 'MUL',
    'Default' => '',
    'Extra' => '',
  ),
  2 => 
  array (
    'Field' => 'balance',
    'Type' => 'varchar(50)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  3 => 
  array (
    'Field' => 'memo',
    'Type' => 'text',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  4 => 
  array (
    'Field' => 'alipay_order_no',
    'Type' => 'varchar(50)',
    'Null' => 'YES',
    'Key' => 'MUL',
    'Default' => '',
    'Extra' => '',
  ),
  5 => 
  array (
    'Field' => 'opt_user_id',
    'Type' => 'varchar(50)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  6 => 
  array (
    'Field' => 'merchant_order_no',
    'Type' => 'varchar(50)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  7 => 
  array (
    'Field' => 'create_time',
    'Type' => 'timestamp',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0000-00-00 00:00:00',
    'Extra' => '',
  ),
  8 => 
  array (
    'Field' => 'self_user_id',
    'Type' => 'varchar(32)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  9 => 
  array (
    'Field' => 'business_type',
    'Type' => 'varchar(50)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  10 => 
  array (
    'Field' => 'out_amount',
    'Type' => 'decimal(11,3) unsigned',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '0.000',
    'Extra' => '',
  ),
  11 => 
  array (
    'Field' => 'type',
    'Type' => 'varchar(50)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  12 => 
  array (
    'Field' => 'in_amount',
    'Type' => 'decimal(11,3)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '0.000',
    'Extra' => '',
  ),
  13 => 
  array (
    'Field' => 'first_insert_time',
    'Type' => 'datetime',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  14 => 
  array (
    'Field' => 'lastchanged',
    'Type' => 'timestamp',
    'Null' => 'NO',
    'Key' => '',
    'Default' => 'CURRENT_TIMESTAMP',
    'Extra' => 'on update CURRENT_TIMESTAMP',
  ),
  15 => 
  array (
    'Field' => 'deal_code',
    'Type' => 'varchar(50)',
    'Null' => 'YES',
    'Key' => 'MUL',
    'Default' => '',
    'Extra' => '',
  ),
  16 => 
  array (
    'Field' => 'account_item',
    'Type' => 'varchar(20)',
    'Null' => 'YES',
    'Key' => 'MUL',
    'Default' => '',
    'Extra' => '',
  ),
  17 => 
  array (
    'Field' => 'account_month',
    'Type' => 'date',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0000-00-00',
    'Extra' => '',
  ),
  18 => 
  array (
    'Field' => 'check_accounts_status',
    'Type' => 'tinyint(4)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  19 => 
  array (
    'Field' => 'check_accounts_time',
    'Type' => 'datetime',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0000-00-00 00:00:00',
    'Extra' => '',
  ),
  20 => 
  array (
    'Field' => 'check_accounts_user_code',
    'Type' => 'varchar(20)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  21 => 
  array (
    'Field' => 'account_month_ym',
    'Type' => 'varchar(7)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0000-00',
    'Extra' => '',
  ),
  22 => 
  array (
    'Field' => 'sell_month_ym',
    'Type' => 'varchar(7)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0000-00',
    'Extra' => '',
  ),
  23 => 
  array (
    'Field' => 'account_item_code',
    'Type' => 'varchar(20)',
    'Null' => 'YES',
    'Key' => 'MUL',
    'Default' => '',
    'Extra' => '',
  ),
  24 => 
  array (
    'Field' => 'check_accounts_msg',
    'Type' => 'varchar(255)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  25 => 
  array (
    'Field' => 'is_refresh',
    'Type' => 'tinyint(2)',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => '0',
    'Extra' => '',
  ),
);
$_the_file_ttl=1522138648;