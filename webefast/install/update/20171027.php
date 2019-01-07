<?php
$u['1712'] = array(
    //批量删除
     "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('5010104', '5010100', 'act', '批量删除', 'base/season/opt_delete', '4', '1', '0', '1', '0');",    
     "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('5010204', '5010200', 'act', '批量删除', 'base/year/opt_delete', '4', '1', '0', '1', '0');",
     "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('5010304', '5010300', 'act', '批量删除', 'prm/brand/opt_delete', '4', '1', '0', '1', '0');",   
);

//分销用户订单列表修改返回正常单权限
$u['1700'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8070313', '8070300', 'act', '返回正常单', 'oms/order_opt/fx_unproblem', '5', '1', '0', '1', '0');",
);

$u['1719']=array(
    "INSERT INTO `base_express_company` (`company_code`, `company_name`, `rule`, `sys`, `is_active`, `remark`, `api_content`, `lastchanged`) VALUES ('YTOXG', '圆通速递(国际)', '^[A-Za-z0-9]{2}[0-9]{10}$|^[A-Za-z0-9]{2}[0-9]{8}$|^(8)[0-9]{17}|^(9)[0-9]{17}$', '1', '0', '', NULL, '2017-02-08 18:16:41');",
    "INSERT INTO `base_express_company` (`company_code`, `company_name`, `rule`, `sys`, `is_active`, `remark`, `api_content`, `lastchanged`) VALUES ('YUNDAXG', '韵达快递(国际)', '^(10|11|12|13|14|15|16|17|19|18|50|55|58|80|88|66|31|77|39)[0-9]{11}$|^[0-9]{13}$', '1', '0', '', NULL, '2017-02-08 18:16:41');",
    "INSERT INTO `base_express_company` (`company_code`, `company_name`, `rule`, `sys`, `is_active`, `remark`, `api_content`, `lastchanged`) VALUES ('TTKDEXXG', '天天快递(国际)', '^[0-9]{12}$', '1', '0', '', NULL, '2015-01-28 16:20:45');",
);
//网络订单优化修改发票信息
$u['1737'] = array(
    "ALTER TABLE `oms_sell_invoice` ADD COLUMN `receiver_address` varchar(100) DEFAULT '' COMMENT '寄送地址(包含省市区)';",
    "ALTER TABLE `oms_sell_invoice` ADD COLUMN `receiver_email` varchar(255) DEFAULT '' COMMENT '邮箱地址';",
);






