<?php

$u['1391_4'] = array(
    "ALTER TABLE `api_order`
MODIFY COLUMN `buyer_nick`  varchar(1000)  NULL DEFAULT '' COMMENT '平台买家昵称' AFTER `seller_nick`,
MODIFY COLUMN `receiver_name`  varchar(1000)  NULL DEFAULT '' COMMENT '平台收货人名称' AFTER `buyer_nick`,
MODIFY COLUMN `receiver_mobile`  varchar(1000) NULL DEFAULT '' COMMENT '平台电话' AFTER `receiver_zip_code`,
MODIFY COLUMN `receiver_phone`  varchar(1000)  NULL DEFAULT '' COMMENT '平台固定电话' AFTER `receiver_mobile`,
MODIFY COLUMN `receiver_email`  varchar(1000)  NULL DEFAULT '' COMMENT '平台email' AFTER `receiver_phone`;

",
    "ALTER TABLE `crm_customer`
MODIFY COLUMN `customer_name_encrypt`  varchar(1000)  NULL DEFAULT NULL COMMENT '加密特殊使用' AFTER `customer_id`;

",
    "ALTER TABLE `crm_customer_address_encrypt`
MODIFY COLUMN `name`  varchar(1000)  NOT NULL DEFAULT '' COMMENT '收货人姓名' AFTER `home_tel`,
MODIFY COLUMN `buyer_name`  varchar(1000)  NOT NULL DEFAULT '' COMMENT '买家昵称' AFTER `name`;
",
);

