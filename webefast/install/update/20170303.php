<?php

$u = array();

$u['1067']=array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('90000000', '0', 'cote', '服务市场', 'server', '71', '1', '0', '1', '0')",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('90010000', '90000000', 'group', '服务首页', 'value_server', '1', '1', '0', '1', '0')",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('90010100', '90010000', 'url', '服务订购', 'value/value_add/server_list', '2', '1', '0', '1', '0')",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('90010200', '90010000', 'url', '我的订单', 'value/server_order/do_list', '3', '1', '0', '1', '0')",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('90010300', '90010000', 'url', '增值服务', 'value/value_add/server_view', '1', '1', '0', '1', '0')"
);

$u['1053'] = array(
    "INSERT INTO `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`) VALUES ( '', 'oms_jingdong', '0', '京东', 'group', '京东平台参数', '', '0.00', '');",
    "INSERT INTO `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`) VALUES ( '', 'is_valuation', 'oms_jingdong', 'JD001 京东保价', 'radio', '[\"关闭\",\"开启\"]', '0', '0.00', '1-开启 0-关闭', '2016-10-24 10:52:47', '开启后，在获取京东电子物流单号时，需要上传保价金额。');"
);

$u['1047'] = array(
    "INSERT INTO op_express_priority (`express_code`) SELECT opg.express_code FROM op_express_by_goods AS opg GROUP BY opg.express_code;"
);



$u['bug_1003'] = array(
    //装箱单删除权限控制
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8050202', '8050200', 'act', '删除', 'common/record_scan_box/cancel_box_record', '2', '1', '0', '1', '0');"
);

$u['bug_998'] = array(
    "UPDATE base_custom SET custom_type = 'pt_fx' WHERE custom_type IS NULL OR custom_type = '';",
    "UPDATE base_custom AS bc,(SELECT distributor_username FROM api_taobao_fx_trade GROUP BY distributor_username) AS du SET bc.custom_type = 'tb_fx' WHERE bc.custom_code = du.distributor_username AND custom_type = 'pt_fx';",
);

$u['1079'] = array(
    "ALTER TABLE oms_sell_return_detail ADD COLUMN `trade_price` decimal(20,3) NOT NULL DEFAULT '0.000' COMMENT '批发价,即分销单价';",
    "ALTER TABLE oms_sell_return_detail ADD COLUMN `fx_amount` decimal(20,3) NOT NULL  DEFAULT '0.000' COMMENT '结算金额（分销单价*num）,用于分销';",
    "ALTER TABLE oms_sell_change_detail ADD COLUMN `fx_amount` decimal(20,3) NOT NULL  DEFAULT '0.000' COMMENT '结算金额（分销单价*num）,用于分销';"
);

