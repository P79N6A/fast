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
    'Field' => 'order_attr',
    'Type' => 'tinyint(4)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '1',
    'Extra' => '',
  ),
  2 => 
  array (
    'Field' => 'sale_channel_code',
    'Type' => 'varchar(20)',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => NULL,
    'Extra' => '',
  ),
  3 => 
  array (
    'Field' => 'deal_code',
    'Type' => 'varchar(64)',
    'Null' => 'NO',
    'Key' => 'UNI',
    'Default' => '',
    'Extra' => '',
  ),
  4 => 
  array (
    'Field' => 'shop_code',
    'Type' => 'varchar(20)',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => '',
    'Extra' => '',
  ),
  5 => 
  array (
    'Field' => 'alipay_no',
    'Type' => 'varchar(30)',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => '',
    'Extra' => '',
  ),
  6 => 
  array (
    'Field' => 'total_fee',
    'Type' => 'decimal(10,3)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0.000',
    'Extra' => '',
  ),
  7 => 
  array (
    'Field' => 'express_money',
    'Type' => 'decimal(10,3)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0.000',
    'Extra' => '',
  ),
  8 => 
  array (
    'Field' => 'point_fee',
    'Type' => 'decimal(10,3)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  9 => 
  array (
    'Field' => 'ali_in_amount',
    'Type' => 'decimal(10,3)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0.000',
    'Extra' => '',
  ),
  10 => 
  array (
    'Field' => 'ali_out_amount',
    'Type' => 'decimal(10,3)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0.000',
    'Extra' => '',
  ),
  11 => 
  array (
    'Field' => 'commission_fee',
    'Type' => 'decimal(10,3)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0.000',
    'Extra' => '',
  ),
  12 => 
  array (
    'Field' => 'num',
    'Type' => 'int(11)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  13 => 
  array (
    'Field' => 'return_num',
    'Type' => 'int(11)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  14 => 
  array (
    'Field' => 'sell_record_avg_money',
    'Type' => 'decimal(10,3)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0.000',
    'Extra' => '',
  ),
  15 => 
  array (
    'Field' => 'sell_return_avg_money',
    'Type' => 'decimal(10,3)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0.000',
    'Extra' => '',
  ),
  16 => 
  array (
    'Field' => 'compensate_money',
    'Type' => 'decimal(20,3)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0.000',
    'Extra' => '',
  ),
  17 => 
  array (
    'Field' => 'create_time',
    'Type' => 'datetime',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => '0000-00-00 00:00:00',
    'Extra' => '',
  ),
  18 => 
  array (
    'Field' => 'lastchanged',
    'Type' => 'timestamp',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => 'CURRENT_TIMESTAMP',
    'Extra' => 'on update CURRENT_TIMESTAMP',
  ),
  19 => 
  array (
    'Field' => 'account_month',
    'Type' => 'date',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => '0000-00-00',
    'Extra' => '',
  ),
  20 => 
  array (
    'Field' => 'check_accounts_status',
    'Type' => 'tinyint(4)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  21 => 
  array (
    'Field' => 'check_accounts_time',
    'Type' => 'datetime',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => '0000-00-00 00:00:00',
    'Extra' => '',
  ),
  22 => 
  array (
    'Field' => 'check_accounts_user_code',
    'Type' => 'varchar(20)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  23 => 
  array (
    'Field' => 'sell_month',
    'Type' => 'date',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => '0000-00-00',
    'Extra' => '',
  ),
  24 => 
  array (
    'Field' => 'account_month_ym',
    'Type' => 'varchar(7)',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => '0000-00',
    'Extra' => '',
  ),
  25 => 
  array (
    'Field' => 'sell_month_ym',
    'Type' => 'varchar(7)',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => '0000-00',
    'Extra' => '',
  ),
  26 => 
  array (
    'Field' => 'sale_right_fee',
    'Type' => 'decimal(10,3)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0.000',
    'Extra' => '',
  ),
  27 => 
  array (
    'Field' => 'commission_fee2',
    'Type' => 'decimal(10,3)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0.000',
    'Extra' => '',
  ),
  28 => 
  array (
    'Field' => 'credit_code_fee',
    'Type' => 'decimal(10,3)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0.000',
    'Extra' => '',
  ),
  29 => 
  array (
    'Field' => 'ali_trade_je',
    'Type' => 'decimal(10,3)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0.000',
    'Extra' => '',
  ),
  30 => 
  array (
    'Field' => 'adjust_money',
    'Type' => 'decimal(20,3)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0.000',
    'Extra' => '',
  ),
  31 => 
  array (
    'Field' => 'real_point_fee',
    'Type' => 'decimal(10,3)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0.000',
    'Extra' => '',
  ),
  32 => 
  array (
    'Field' => 'fx_refund_money',
    'Type' => 'decimal(10,3)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0.000',
    'Extra' => '',
  ),
  33 => 
  array (
    'Field' => 'record_time',
    'Type' => 'datetime',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => NULL,
    'Extra' => '',
  ),
  34 => 
  array (
    'Field' => 'pay_time',
    'Type' => 'datetime',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => NULL,
    'Extra' => '',
  ),
);
$_the_file_ttl=1522138648;