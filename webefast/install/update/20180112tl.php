<?php
//给订单表添加其他优惠金额
$u['2023'] = array(
    "ALTER TABLE oms_sell_record ADD COLUMN other_amount decimal(10,2) DEFAULT '0.00' COMMENT '其他优惠金额（通灵增值）;",
);
//给订单表添加是否打印过质保书字段
$u['2026'] = array(
   "ALTER TABLE oms_sell_record ADD COLUMN is_print_warranty tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否打印质保书';",
);


$u['2024'] = array(
    "ALTER TABLE `js_fapiao`
ADD COLUMN `ghf_sj`  varchar(32) NULL DEFAULT '' COMMENT '购方默认手机号' AFTER `config_type`;",
);

