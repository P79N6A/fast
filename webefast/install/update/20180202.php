<?php

$u['2089']=array(
    "ALTER TABLE `oms_sell_return_detail` ADD COLUMN `cost_price`  decimal(20,3) NOT NULL DEFAULT 0.000 COMMENT '商品成本价' AFTER `goods_price`;"
);

$u['bug_2203']=array(
    "ALTER TABLE `wms_oms_trade` MODIFY COLUMN `buyer_name`  varchar(30) NOT NULL DEFAULT '' COMMENT '买家昵称';"
);
//商品税务编码表增加单位字段
$u['2090']=array(
    "ALTER TABLE `goods_tax` ADD COLUMN `unit`  varchar(64)  DEFAULT '' COMMENT '单位';"
);
