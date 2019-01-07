<?php

$u['1391_2'] = array(
    " 
ALTER TABLE `crm_customer_address_encrypt`
MODIFY COLUMN `address`  varchar(2000)  NOT NULL DEFAULT '' COMMENT '收货地址' ;",
    "ALTER TABLE `api_order`
MODIFY COLUMN `receiver_address`  varchar(2000)  NULL DEFAULT '' COMMENT '地址（含省市区）' AFTER `receiver_street`,
MODIFY COLUMN `receiver_addr`  varchar(2000)  NULL DEFAULT '' COMMENT '地址（不含省市区）' AFTER `receiver_address`;",
);

