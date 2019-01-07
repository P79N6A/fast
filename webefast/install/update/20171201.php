<?php
$u['1837'] = array(
    //外包仓零售单
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('7030112', '7030101', 'act', '批量处理', 'wms/wms_mgr/opt_order_shipping_oms', '3', '1', '0', '1', '0');",
);

$u['1854'] = array(
    'insert into `sys_action` ( `action_code`, `action_name`, `other_priv_type`, `status`, `ui_entrance`, `parent_id`, `sort_order`, `type`, `appid`, `action_id`) values ( \'api/sys/order_refund/down_priv\', \'退单下载\', \'0\', \'1\', \'0\', \'4010400\', \'1\', \'act\', \'1\', \'4010406\');'
);

$u['1893'] = array(
    'insert into `sys_params` ( `param_code`, `parent_code`, `memo`, `type`, `value`, `lastchanged`, `form_desc`, `remark`, `param_name`, `sort`, `param_id`) values ( \'siku_price_type\', \'oms_siku\', \'\', \'radio\', \'0\', \'2017-10-18 17:41:34\', \'[\"结算价\",\"寺库价\"]\', \'1-寺库价 0-结算价\', \'订单中商品单价的取值\', \'1.00\', \'\')',
);

$u['bug_1890'] = array(
    " UPDATE `sys_action` SET `action_code` = 'wms/wms_mgr/opt_upload_oms' WHERE `action_id` = '7030111' ",
);

$u['1891'] = array(
    "UPDATE  sys_params SET  param_name='开启模糊查询',memo='开启后，订单列表和订单查询页面的交易号、订单号、手机号、买家昵称查询支持模糊查询' WHERE  param_code='fuzzy_search';",
);
$u['bug_1691'] = array(
    //唯品会商品管理
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8040801', '8040800', 'act', '批量允许库存同步', 'api/api_weipinhuijit_goods/opt_enable_inv', '1', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8040802', '8040800', 'act', '批量禁止库存同步', 'api/api_weipinhuijit_goods/opt_disable_inv', '2', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8040803', '8040800', 'act', '一键允许库存同步', 'api/api_weipinhuijit_goods/once_enable_inv', '3', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8040804', '8040800', 'act', '一键禁止库存同步', 'api/api_weipinhuijit_goods/once_disable_inv', '4', '1', '0', '1', '0');",
    //平台商品列表
    "UPDATE `sys_action` SET `action_code` = 'api/sys/goods/sync_goods_inv_pt' WHERE `action_id` = '4010301'  ",
    "UPDATE `sys_action` SET `action_code` = 'oms/api_goods/p_update_active_pt' WHERE `action_id` = '4010302'  ",
    "UPDATE `sys_action` SET `action_code` = 'oms/api_goods/p_update_active/ban_pt' WHERE `action_id` = '4010303'  ",
);

$u['bug_1792'] = [
    "ALTER TABLE order_express_dz_detail ADD KEY `ind_dz_code` (`dz_code`) USING BTREE;"
];