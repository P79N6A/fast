<?php

$u['1391'] = array(
    "CREATE TABLE `sys_encrypt` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `shop_code` varchar(50) NOT NULL DEFAULT '' COMMENT '店铺代码',
  `randomNum` varchar(128) NOT NULL DEFAULT '' COMMENT '安全码',
  `type` varchar(30) NOT NULL DEFAULT '' COMMENT '加密类型',
  `start_time` int (10) NOT NULL DEFAULT '0' COMMENT '加密开始时间',
  `end_time` int (10) NOT NULL DEFAULT '0' COMMENT '加密结束',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '1启用，0停用',
  `param` text NOT NULL DEFAULT '' COMMENT '其他参数',
  `new_encrypt_id` int(11) NOT NULL DEFAULT '0' COMMENT '新的加密算法id',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  KEY `shop_code` (`shop_code`,`status`) USING BTREE,
  KEY `type` (`type`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='加密授权表';",
    "
CREATE TABLE `crm_customer_address_encrypt` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `customer_address_id` int(11) NOT NULL DEFAULT '0' COMMENT '地址ID',
  `customer_code` varchar(30) NOT NULL DEFAULT '' COMMENT '顾客',
  `shop_code` varchar(30) NOT NULL DEFAULT '' COMMENT '店铺代码',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '收货地址',
  `tel` varchar(128) NOT NULL DEFAULT '' COMMENT '电话',
  `home_tel` varchar(128) NOT NULL DEFAULT '' COMMENT '座机',
  `name` varchar(128) NOT NULL DEFAULT '' COMMENT '收货人姓名',
  `buyer_name` varchar(128) NOT NULL DEFAULT '' COMMENT '买家昵称',
  `encryp_other` text NOT NULL COMMENT '其他加密信息',
  `only_code` varchar(128) NOT NULL DEFAULT '' COMMENT '明文唯一码',
  `encrypt_id` int(11) NOT NULL DEFAULT '0' COMMENT '加密规则ID',
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_address_id` (`customer_address_id`) USING BTREE,
  UNIQUE KEY `only_code` (`only_code`, `customer_code`) USING BTREE,
  KEY `buyer_name` (`buyer_name`) USING BTREE,
  KEY `customer_shop` (`customer_code`,`shop_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='会员地址加密信息';

",
    "
CREATE TABLE `crm_customer_address_encrypt_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `customer_code` varchar(128) DEFAULT NULL,
  `customer_address_id` int(11) NOT NULL DEFAULT '0' COMMENT '地址ID',
  `user_code` varchar(64) NOT NULL DEFAULT '' COMMENT '用户代码',
  `action_time` int(11) NOT NULL DEFAULT '0' COMMENT '操作时间',
  `action_note` varchar(128)  NOT NULL DEFAULT '' COMMENT '操作内容',
  `record_type` varchar(64)  NOT NULL DEFAULT '' COMMENT '单据类型',
  `record_code` varchar(128)  NOT NULL DEFAULT '' COMMENT '单据编号',
  `action_desc` varchar(128) NOT NULL DEFAULT '' COMMENT '描述',
  PRIMARY KEY (`id`),
  KEY `customer_address_id` (`customer_address_id`) USING BTREE,
  KEY `user_code` (`user_code`) USING BTREE,
  KEY `record_code` (`record_code`) USING BTREE,
  KEY `action_time` (`action_time`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='查看加密日志';
",
    "
ALTER TABLE `api_order`
ADD COLUMN `customer_address_id`  int(11) NOT NULL DEFAULT 0 COMMENT '关联界面地址' AFTER `is_daixiao`,
ADD COLUMN  `customer_code` varchar(30) NOT NULL DEFAULT '' COMMENT '会员代码' AFTER `customer_address_id`,
MODIFY COLUMN `seller_nick`  varchar(128)  NULL DEFAULT '' COMMENT '平台卖家昵称' AFTER `pay_time`,
MODIFY COLUMN `buyer_nick`  varchar(128)  NULL DEFAULT '' COMMENT '平台买家昵称' AFTER `seller_nick`,
MODIFY COLUMN `receiver_name`  varchar(128)  NULL DEFAULT '' COMMENT '平台收货人名称' AFTER `buyer_nick`,
MODIFY COLUMN `receiver_address`  varchar(255) NULL DEFAULT '' COMMENT '地址（含省市区）' AFTER `receiver_street`,
MODIFY COLUMN `receiver_addr`  varchar(255)  NULL DEFAULT '' COMMENT '地址（不含省市区）' AFTER `receiver_address`,
MODIFY COLUMN `receiver_mobile`  varchar(128) NULL DEFAULT '' COMMENT '平台电话' AFTER `receiver_zip_code`,
MODIFY COLUMN `receiver_phone`  varchar(128)  NULL DEFAULT '' COMMENT '平台固定电话' AFTER `receiver_mobile`,
MODIFY COLUMN `receiver_email`  varchar(128)  NULL DEFAULT '' COMMENT '平台email' AFTER `receiver_phone`,
ADD INDEX `customer_address_id` (`customer_address_id`) ;
",
    "

ALTER TABLE `api_refund`
MODIFY COLUMN `buyer_nick`  varchar(255)  NULL DEFAULT NULL COMMENT '平台买家昵称' AFTER `seller_nick`;
",
    "

ALTER TABLE `oms_sell_record`
ADD COLUMN `customer_address_id`  int(11) NOT NULL DEFAULT 0 COMMENT '会员地址ID' AFTER `customer_code`,
MODIFY COLUMN `receiver_email`  varchar(255)  NOT NULL DEFAULT '' COMMENT '收货人email' AFTER `receiver_phone`,
ADD INDEX `customer_address_id` (`customer_address_id`) ;
",
    
    
    
    "

ALTER TABLE `oms_sell_return`
ADD COLUMN `customer_address_id`  int(11) NULL DEFAULT 0 COMMENT '退货地址id' AFTER `customer_code`,
ADD COLUMN `change_customer_address_id`  int(11) NULL DEFAULT 0 COMMENT '换货地址信息' AFTER `change_phone`;
",
    "

ALTER TABLE `oms_return_package`
ADD COLUMN `customer_address_id`  int(11) NULL DEFAULT 0 COMMENT '退货地址id' AFTER `return_addr`,
ADD COLUMN   `customer_code` varchar(30) NOT NULL DEFAULT '' COMMENT '顾客代码';
",
    
        "
  ALTER TABLE `oms_sell_record_notice`
ADD COLUMN `customer_address_id`  int(11) NULL DEFAULT 0 COMMENT '会员地址ID' AFTER `customer_code`;
",
            "
  ALTER TABLE `oms_deliver_record`
  ADD COLUMN   `customer_code` varchar(30) NOT NULL DEFAULT '' COMMENT '顾客代码',
ADD COLUMN `customer_address_id`  int(11) NULL DEFAULT 0 COMMENT '会员地址ID' AFTER `customer_code`;
",
          "
  ALTER TABLE `oms_sell_record_combine`
ADD COLUMN `customer_address_id`  int(11) NULL DEFAULT 0 COMMENT '会员地址ID' ;
",  
            "
  ALTER TABLE `oms_sell_record_cz`
ADD COLUMN `customer_address_id`  int(11) NULL DEFAULT 0 COMMENT '会员地址ID' ;
",  
  
    
    
    
    "
ALTER TABLE `crm_customer`
MODIFY COLUMN `customer_name`  varchar(255)  DEFAULT '' COMMENT '顾客名称' AFTER `customer_code`,
ADD COLUMN `customer_name_encrypt`  varchar(255) NULL COMMENT '加密特殊使用' AFTER `customer_id`,
ADD COLUMN `customer_name_code`  varchar(64) NULL ;


",
    "
ALTER TABLE `crm_customer_address`
ADD COLUMN `address_detail`  varchar(255) NOT NULL AFTER `address`,
ADD COLUMN  `only_code` varchar(128) NOT NULL DEFAULT '' COMMENT '明文唯一码',
ADD COLUMN `tel_code`  varchar(128) NOT NULL DEFAULT '',
ADD COLUMN `home_tel_code`  varchar(128) NOT NULL DEFAULT '',
ADD COLUMN `name_code`  varchar(128) NOT NULL DEFAULT '',
ADD INDEX  `name` (`name_code`) USING BTREE,
ADD INDEX `tel` (`tel_code`) USING BTREE,
ADD INDEX `home_tel` (`home_tel_code`) USING BTREE;
",
    "

update 
oms_return_package,
oms_sell_return set oms_return_package.customer_code=oms_sell_return.customer_code
where oms_return_package.sell_return_code=oms_sell_return.sell_return_code;
",
    "

update crm_customer set customer_name_code = MD5(customer_name)
where customer_name_code='' or customer_name_code is null;

",
    "
ALTER TABLE `crm_customer`
ADD COLUMN `source`  varchar(64) NULL AFTER `customer_level`;
",
    "
update crm_customer,base_shop set crm_customer.source=base_shop.sale_channel_code
where crm_customer.shop_code=base_shop.shop_code;
",
    "


ALTER TABLE `crm_customer`
ADD UNIQUE INDEX `idx3` (`customer_name_code`, `source`) USING BTREE ;
",//索引有可能加不上，需要完善
    "
ALTER TABLE `crm_customer`
DROP INDEX `idxu2`;
",
    "
CREATE TABLE `crm_customer_seq` (
  `seq` bigint(20) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`seq`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='单据自增序列表';

",
    "

ALTER TABLE `api_taobao_trade`
MODIFY COLUMN `buyer_nick`  varchar(255)  NULL DEFAULT '' COMMENT '买家昵称' AFTER `seller_nick`,
MODIFY COLUMN `receiver_name`  varchar(128)  NULL DEFAULT NULL COMMENT '收货人的姓名' AFTER `buyer_alipay_no`,
MODIFY COLUMN `receiver_address`  varchar(255)  NULL DEFAULT NULL COMMENT '收货人的详细地址' AFTER `receiver_district`,
MODIFY COLUMN `receiver_mobile`  varchar(128) NULL DEFAULT NULL COMMENT '收货人的手机号码' AFTER `receiver_zip`,
MODIFY COLUMN `receiver_phone`  varchar(128)  NULL DEFAULT NULL COMMENT '收货人的电话号码' AFTER `receiver_mobile`,
MODIFY COLUMN `buyer_email`  varchar(255)  NULL DEFAULT NULL COMMENT '买家邮件地址' AFTER `consign_time`,
MODIFY COLUMN `seller_phone`  varchar(255)  NULL DEFAULT NULL COMMENT '卖家电话' AFTER `seller_mobile`,
MODIFY COLUMN `seller_name`  varchar(255) NULL DEFAULT NULL COMMENT '卖家姓名' AFTER `seller_phone`,
MODIFY COLUMN `seller_email`  varchar(255)  NULL DEFAULT NULL COMMENT '卖家邮件地址' AFTER `seller_name`;

",

    "

ALTER TABLE `api_taobao_refund`
MODIFY COLUMN `buyer_nick`  varchar(255) NULL DEFAULT NULL COMMENT '买家昵称' AFTER `total_fee`;
",
    "
ALTER TABLE `api_taobao_fx_trade`
MODIFY COLUMN `buyer_nick`  varchar(255) NULL DEFAULT '' COMMENT '买家nick，供应商查询不会返回买家昵称，分销商查询才会返回' AFTER `buyer_payment`,
MODIFY COLUMN `receiver_phone`  varchar(255)  NULL DEFAULT '' COMMENT '固定电话' AFTER `receiver_city`,
MODIFY COLUMN `receiver_name`  varchar(255)  NULL DEFAULT '' COMMENT '收货人全名' AFTER `receiver_phone`,
MODIFY COLUMN `receiver_mobile_phone`  varchar(255)  NULL DEFAULT '' COMMENT '移动电话' AFTER `receiver_name`,
ADD COLUMN `customer_address_id`  int(11) NOT NULL DEFAULT 0 COMMENT '关联界面地址' ,
ADD COLUMN  `customer_code` varchar(30) NOT NULL DEFAULT '' COMMENT '会员代码' ,
ADD INDEX `customer_address_id` (`customer_address_id`) ;
",

    "INSERT INTO `sys_schedule` (`code`, `name`, `task_type_code`, `sale_channel_code`, `status`, `type`, `desc`, `request`, `path`, `max_num`, `add_time`, `last_time`, `loop_time`, `task_type`, `task_module`, `exec_ip`, `plan_exec_time`, `plan_exec_data`, `update_time`) 
    VALUES ('cli_decrypt_api_order', '订单解密服务', '', '', '0', '0', '', '{\"app_act\":\"cli/cli_decrypt_api_order\",\"app_fmt\":\"json\"}', 'webefast/web/index.php', '0', '0', '0', '60', '0', 'sys', '', '0', NULL, '0');",
);



//foreach($u as $arr){
//    foreach ($arr as $sql){
//        echo $sql."\n";
//    }
//}