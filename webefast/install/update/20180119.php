<?php
$u['2052']=array(
    "INSERT INTO `alipay_account_item` (`code`, `account_item`, `in_out_flag`, `lastchanged`) VALUES ('115', '村淘平台服务费扣款', '2', '2018-1-8 15:28:21'),('116', '公益宝贝捐款', '2', '2018-1-8 15:28:21'),('117', '保险承保-卖家版运费险保费', '2', '2018-1-8 15:28:21'),('118', '天猫提现', '2', '2018-1-8 15:28:21');"
);

$u['2049']=array(
    "UPDATE sys_params SET memo='默认关闭，开启后，商品列表/商品库存查询/销售商品分析/销售数据分析/批发统计分析的查看商品明细/采购统计分析的查看商品明细，显示扩展属性并支持导出；且批发销货通知单/批发退货通知单/采购通知单/采购退货通知单，导出明细时导出扩展属性' WHERE param_code='property_power' "

);
$u['2041'] = array(
    "UPDATE sys_schedule SET `desc` = '从唯品会平台定时获取已销售订单占用库存数，在唯品会库存同步时预扣除已销售库存（使用唯品会jit库存同步业务时，此自动服务必须开启）' WHERE `name` = '唯品会jit已成交销售订单查询';"
);

$u['2006'] = array(
    "UPDATE sys_action SET action_code='sys/params/industry' WHERE action_id='1010400';",
    "INSERT INTO sys_params (param_id, param_code, parent_code, param_name, type, form_desc, value, sort, remark, lastchanged, memo) VALUES ('', 'industry_clothing', 'industry', '服装', 'group', '', '', 0.00, '', now(), '服装行业特性');",
    "INSERT INTO sys_params (param_id, param_code, parent_code, param_name, type, form_desc, value, sort, remark, lastchanged, memo) VALUES ('', 'size_layer', 'industry_clothing', '颜色、尺码层商品展示', 'radio', '[\"关闭\",\"开启\"]', '0', '2.00', '1-开启 0-关闭', now(), '单据添加商品时，界面以颜色、尺码层商品展示');",
    "ALTER TABLE sys_params ADD `data` varchar(1000) DEFAULT NULL COMMENT '参数特殊配置值';"
);
$u['bug_2218']=array(
    "ALTER TABLE api_weipinhuijit_return_record ADD INDEX return_sn (`return_sn`) USING BTREE;"
);

$u['2045']=array(
    "UPDATE `sys_role` SET `role_desc`='该角色为系统内置，不可删除，拥有订单查询/售后服务单/会员列表/缺货订单列表导出明文权限' WHERE (`role_code`='security');",
);
