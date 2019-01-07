<?php 
 if (!defined('ROOT_PATH')) die('401,未授权访问 [Unauthorized]');
$data=array (
  0 => 
  array (
    'Field' => 'sell_return_id',
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
    'Field' => 'is_compensate',
    'Type' => 'tinyint(1)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  3 => 
  array (
    'Field' => 'is_packet_out_stock',
    'Type' => 'tinyint(1)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  4 => 
  array (
    'Field' => 'create_time',
    'Type' => 'timestamp',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => '0000-00-00 00:00:00',
    'Extra' => '',
  ),
  5 => 
  array (
    'Field' => 'sell_return_code',
    'Type' => 'varchar(20)',
    'Null' => 'NO',
    'Key' => 'UNI',
    'Default' => '',
    'Extra' => '',
  ),
  6 => 
  array (
    'Field' => 'sell_record_code',
    'Type' => 'varchar(20)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  7 => 
  array (
    'Field' => 'refund_id',
    'Type' => 'varchar(30)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  8 => 
  array (
    'Field' => 'change_record',
    'Type' => 'varchar(20)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  9 => 
  array (
    'Field' => 'deal_code',
    'Type' => 'varchar(200)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  10 => 
  array (
    'Field' => 'deal_code_list',
    'Type' => 'varchar(200)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  11 => 
  array (
    'Field' => 'sell_return_package_code',
    'Type' => 'varchar(20)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  12 => 
  array (
    'Field' => 'stock_date',
    'Type' => 'date',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => '0000-00-00',
    'Extra' => '',
  ),
  13 => 
  array (
    'Field' => 'store_code',
    'Type' => 'varchar(20)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  14 => 
  array (
    'Field' => 'shop_code',
    'Type' => 'varchar(20)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  15 => 
  array (
    'Field' => 'sale_channel_code',
    'Type' => 'varchar(20)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  16 => 
  array (
    'Field' => 'return_pay_code',
    'Type' => 'varchar(32)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  17 => 
  array (
    'Field' => 'relation_shipping_status',
    'Type' => 'tinyint(11)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  18 => 
  array (
    'Field' => 'return_order_status',
    'Type' => 'tinyint(11)',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => '0',
    'Extra' => '',
  ),
  19 => 
  array (
    'Field' => 'finance_check_status',
    'Type' => 'tinyint(11)',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => NULL,
    'Extra' => '',
  ),
  20 => 
  array (
    'Field' => 'return_shipping_status',
    'Type' => 'tinyint(11)',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => '0',
    'Extra' => '',
  ),
  21 => 
  array (
    'Field' => 'is_package_out_stock',
    'Type' => 'tinyint(11)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  22 => 
  array (
    'Field' => 'sell_record_checkpay_status',
    'Type' => 'tinyint(1)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  23 => 
  array (
    'Field' => 'customer_code',
    'Type' => 'varchar(20)',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => '',
    'Extra' => '',
  ),
  24 => 
  array (
    'Field' => 'customer_address_id',
    'Type' => 'int(11)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  25 => 
  array (
    'Field' => 'buyer_name',
    'Type' => 'varchar(20)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  26 => 
  array (
    'Field' => 'return_name',
    'Type' => 'varchar(20)',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => '',
    'Extra' => '',
  ),
  27 => 
  array (
    'Field' => 'return_country',
    'Type' => 'bigint(20)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  28 => 
  array (
    'Field' => 'return_province',
    'Type' => 'bigint(20)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  29 => 
  array (
    'Field' => 'return_city',
    'Type' => 'bigint(20)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  30 => 
  array (
    'Field' => 'return_district',
    'Type' => 'bigint(20)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  31 => 
  array (
    'Field' => 'return_street',
    'Type' => 'bigint(20)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  32 => 
  array (
    'Field' => 'return_address',
    'Type' => 'varchar(100)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  33 => 
  array (
    'Field' => 'return_addr',
    'Type' => 'varchar(100)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  34 => 
  array (
    'Field' => 'return_zip_code',
    'Type' => 'varchar(20)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  35 => 
  array (
    'Field' => 'return_mobile',
    'Type' => 'varchar(32)',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => '',
    'Extra' => '',
  ),
  36 => 
  array (
    'Field' => 'return_phone',
    'Type' => 'varchar(32)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  37 => 
  array (
    'Field' => 'return_email',
    'Type' => 'varchar(40)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  38 => 
  array (
    'Field' => 'return_express_code',
    'Type' => 'varchar(20)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  39 => 
  array (
    'Field' => 'return_express_no',
    'Type' => 'varchar(40)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  40 => 
  array (
    'Field' => 'return_reason_code',
    'Type' => 'varchar(40)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  41 => 
  array (
    'Field' => 'return_buyer_memo',
    'Type' => 'varchar(255)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  42 => 
  array (
    'Field' => 'return_remark',
    'Type' => 'varchar(255)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  43 => 
  array (
    'Field' => 'refund_total_fee',
    'Type' => 'decimal(20,3)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0.000',
    'Extra' => '',
  ),
  44 => 
  array (
    'Field' => 'should_refunds',
    'Type' => 'decimal(20,3)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0.000',
    'Extra' => '',
  ),
  45 => 
  array (
    'Field' => 'compensate_money',
    'Type' => 'decimal(20,3)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0.000',
    'Extra' => '',
  ),
  46 => 
  array (
    'Field' => 'buyer_express_money',
    'Type' => 'decimal(20,3)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0.000',
    'Extra' => '',
  ),
  47 => 
  array (
    'Field' => 'seller_express_money',
    'Type' => 'decimal(20,3)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0.000',
    'Extra' => '',
  ),
  48 => 
  array (
    'Field' => 'adjust_money',
    'Type' => 'decimal(20,3)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0.000',
    'Extra' => '',
  ),
  49 => 
  array (
    'Field' => 'change_express_money',
    'Type' => 'decimal(20,3)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0.000',
    'Extra' => '',
  ),
  50 => 
  array (
    'Field' => 'return_avg_money',
    'Type' => 'decimal(20,3)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0.000',
    'Extra' => '',
  ),
  51 => 
  array (
    'Field' => 'change_avg_money',
    'Type' => 'decimal(20,3)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0.000',
    'Extra' => '',
  ),
  52 => 
  array (
    'Field' => 'is_exchange_goods',
    'Type' => 'tinyint(1)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  53 => 
  array (
    'Field' => 'change_name',
    'Type' => 'varchar(20)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  54 => 
  array (
    'Field' => 'change_country',
    'Type' => 'bigint(11)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  55 => 
  array (
    'Field' => 'change_province',
    'Type' => 'bigint(11)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  56 => 
  array (
    'Field' => 'change_city',
    'Type' => 'bigint(11)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  57 => 
  array (
    'Field' => 'change_district',
    'Type' => 'bigint(11)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  58 => 
  array (
    'Field' => 'change_street',
    'Type' => 'bigint(11)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  59 => 
  array (
    'Field' => 'change_address',
    'Type' => 'varchar(100)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  60 => 
  array (
    'Field' => 'change_addr',
    'Type' => 'varchar(100)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  61 => 
  array (
    'Field' => 'change_mobile',
    'Type' => 'varchar(32)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  62 => 
  array (
    'Field' => 'change_phone',
    'Type' => 'varchar(32)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  63 => 
  array (
    'Field' => 'change_customer_address_id',
    'Type' => 'int(11)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  64 => 
  array (
    'Field' => 'change_express_code',
    'Type' => 'varchar(20)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  65 => 
  array (
    'Field' => 'service_code',
    'Type' => 'varchar(20)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  66 => 
  array (
    'Field' => 'check_time',
    'Type' => 'datetime',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0000-00-00 00:00:00',
    'Extra' => '',
  ),
  67 => 
  array (
    'Field' => 'agreed_refund_time',
    'Type' => 'datetime',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => '0000-00-00 00:00:00',
    'Extra' => '',
  ),
  68 => 
  array (
    'Field' => 'receive_person',
    'Type' => 'varchar(20)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  69 => 
  array (
    'Field' => 'receive_time',
    'Type' => 'datetime',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => '0000-00-00 00:00:00',
    'Extra' => '',
  ),
  70 => 
  array (
    'Field' => 'is_lock',
    'Type' => 'tinyint(4)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  71 => 
  array (
    'Field' => 'is_lock_person',
    'Type' => 'varchar(20)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  72 => 
  array (
    'Field' => 'create_person',
    'Type' => 'varchar(20)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => NULL,
    'Extra' => '',
  ),
  73 => 
  array (
    'Field' => 'confirm_person',
    'Type' => 'varchar(20)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  74 => 
  array (
    'Field' => 'confirm_time',
    'Type' => 'datetime',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0000-00-00 00:00:00',
    'Extra' => '',
  ),
  75 => 
  array (
    'Field' => 'finance_confirm_person',
    'Type' => 'varchar(20)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  76 => 
  array (
    'Field' => 'finance_confirm_time',
    'Type' => 'datetime',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0000-00-00 00:00:00',
    'Extra' => '',
  ),
  77 => 
  array (
    'Field' => 'finance_reject_person',
    'Type' => 'varchar(20)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  78 => 
  array (
    'Field' => 'finance_reject_time',
    'Type' => 'datetime',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0000-00-00 00:00:00',
    'Extra' => '',
  ),
  79 => 
  array (
    'Field' => 'finance_reject_reason',
    'Type' => 'varchar(255)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  80 => 
  array (
    'Field' => 'agree_refund_person',
    'Type' => 'varchar(20)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  81 => 
  array (
    'Field' => 'agree_refund_time',
    'Type' => 'datetime',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0000-00-00 00:00:00',
    'Extra' => '',
  ),
  82 => 
  array (
    'Field' => 'cancel_person',
    'Type' => 'varchar(20)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  83 => 
  array (
    'Field' => 'cancel_time',
    'Type' => 'datetime',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0000-00-00 00:00:00',
    'Extra' => '',
  ),
  84 => 
  array (
    'Field' => 'is_settlement',
    'Type' => 'tinyint(4)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  85 => 
  array (
    'Field' => 'lastchanged',
    'Type' => 'timestamp',
    'Null' => 'NO',
    'Key' => 'MUL',
    'Default' => 'CURRENT_TIMESTAMP',
    'Extra' => 'on update CURRENT_TIMESTAMP',
  ),
  86 => 
  array (
    'Field' => 'buyer_alipay_no',
    'Type' => 'varchar(50)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  87 => 
  array (
    'Field' => 'finsih_status',
    'Type' => 'tinyint(1)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  88 => 
  array (
    'Field' => 'note_num',
    'Type' => 'int(11)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  89 => 
  array (
    'Field' => 'recv_num',
    'Type' => 'int(11)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  90 => 
  array (
    'Field' => 'change_store_code',
    'Type' => 'varchar(20)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  91 => 
  array (
    'Field' => 'is_fenxiao',
    'Type' => 'tinyint(3)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
  92 => 
  array (
    'Field' => 'fenxiao_name',
    'Type' => 'varchar(200)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  93 => 
  array (
    'Field' => 'fenxiao_code',
    'Type' => 'varchar(128)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '',
    'Extra' => '',
  ),
  94 => 
  array (
    'Field' => 'fx_payable_money',
    'Type' => 'decimal(10,2)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0.00',
    'Extra' => '',
  ),
  95 => 
  array (
    'Field' => 'fx_express_money',
    'Type' => 'decimal(10,2)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0.00',
    'Extra' => '',
  ),
  96 => 
  array (
    'Field' => 'change_fx_amount',
    'Type' => 'decimal(10,2)',
    'Null' => 'NO',
    'Key' => '',
    'Default' => '0.00',
    'Extra' => '',
  ),
  97 => 
  array (
    'Field' => 'ag_status',
    'Type' => 'tinyint(1)',
    'Null' => 'YES',
    'Key' => '',
    'Default' => '0',
    'Extra' => '',
  ),
);
$_the_file_ttl=1521700090;