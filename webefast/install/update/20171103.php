<?php
$u['bug_1724'] = array(
    //赠品策略操作日志增加操作描述
     "ALTER TABLE `op_policy_express_log` ADD COLUMN `desc` varchar(255) DEFAULT '' COMMENT '备注' after `action_name`;"
);

//移仓单
$u['1730'] = array(
    // 确认/取消确认
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('6010601', '6010600', 'act', '确认/取消确认', 'stm/store_shift_record/confirm', '1', '1', '0', '1', '0');",
    //删除
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('6010602', '6010600', 'act', '删除', 'stm/store_shift_record/delete', '2', '1', '0', '1', '0');",
    //出库
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('6010603', '6010600', 'act', '出库', 'stm/store_shift_record/output', '3', '1', '0', '1', '0');",
    //扫描入库
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('6010604', '6010600', 'act', '扫描入库', 'stm/store_shift_record/scan_input', '4', '1', '0', '1', '0');",
    //强制入库
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('6010605', '6010600', 'act', '强制入库', 'stm/store_shift_record/force_input', '5', '1', '0', '1', '0');",
);

$u['1726'] = array(
    //采购入库单 按业务日期验收
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('6030105', '6030100', 'act', '按业务日期验收', 'pur/purchase_record/do_checkin_time', '3', '1', '0', '1', '0');",
    //采购退货单 按业务日期验收
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('6030203', '6030200', 'act', '按业务日期验收', 'pur/return_record/do_checkin_time', '3', '1', '0', '1', '0');",
    //批发销货单 验收
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8020105', '8020100', 'act', '验收', 'wbm/store_out_record/do_checkin', '3', '1', '0', '1', '0');",
    //批发销货单 按业务日期验收
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8020106', '8020100', 'act', '按业务日期验收', 'wbm/store_out_record/do_checkin_time', '4', '1', '0', '1', '0');",
);


$u['1762']=array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('4020506', '4020500', 'act', '生成采购订单', 'oms/sell_record/add_plan_record_check', '6', '1', '0', '1', '0');",
);