<?php

$u['bug_1306'] = array(
    "ALTER TABLE oms_sell_return ADD COLUMN `should_refunds` decimal(20,3) NOT NULL DEFAULT '0.000' COMMENT '退单应退款' AFTER `refund_total_fee`;",
    "UPDATE oms_sell_return SET should_refunds = seller_express_money + compensate_money + adjust_money + return_avg_money WHERE should_refunds = 0;"
);