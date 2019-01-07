<?php
$u = array();
$u['124'] = array(
    "INSERT INTO `sys_action` VALUES ('1030213','1030200','act','删除','sys/user/do_delete','0','1','0','1','0');",
    "INSERT INTO `sys_action` VALUES ('1030214','1030200','act','导出','sys/user/export_list','1','1','0','1','0');"
);

$u['133'] = array(
    "DELETE FROM `sys_user_pref` WHERE iid='oms/sell_record_question_list';"
);
$u['122'] = array(
    "INSERT INTO `sys_action` VALUES ('1030107','1030100','act','删除','sys/role/do_delete','0','1','0','1','0');",
);

$u['115'] = array(
    "ALTER TABLE base_shop MODIFY shop_type tinyint(1) DEFAULT '0' COMMENT '商店性质,0网络店铺、1实体店铺';",
    "ALTER TABLE base_shop ADD entity_type tinyint(1) DEFAULT '0' COMMENT '门店性质,0直营店、1加盟店' AFTER shop_type;",
    "ALTER TABLE base_shop ADD open_time varchar(20) DEFAULT NULL COMMENT '营业时间:9:00-18:00' AFTER entity_type;",
    "ALTER TABLE base_shop ADD shop_desc varchar(255) DEFAULT '' COMMENT '店铺介绍';",
    "ALTER TABLE base_shop ADD create_time datetime NOT NULL COMMENT '创建时间';",
    "ALTER TABLE base_shop ADD create_person varchar(128) DEFAULT '' COMMENT '创建人';",

    "ALTER TABLE base_shop CHANGE province_name province varchar(20) DEFAULT NULL COMMENT '省' AFTER rank;",
    "ALTER TABLE base_shop CHANGE city_name city varchar(20) DEFAULT NULL COMMENT '市' AFTER province;",
    "ALTER TABLE base_shop ADD district VARCHAR(20) DEFAULT NULL COMMENT '区' AFTER city;",
    "ALTER TABLE base_shop ADD street VARCHAR(20) DEFAULT NULL COMMENT '街道' AFTER district;",
    
    "UPDATE `sys_action` SET action_name='网络店铺' WHERE action_id='2050100';",
    "INSERT INTO `sys_action` VALUES ('2050200','2050000','url','实体店铺','base/shop_entity/do_list','1','1','0','1','2');",
    "UPDATE `base_store` set sys=0 where store_code='001';",
    
);

$u['123'] = array(
    "INSERT INTO `sys_params` (`param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`) 
VALUES ('supplier_power', 'sys_set', '供应商权限', 'radio', '[\"关闭\",\"开启\"]', '0', ' 0', '1-开启 0-关闭', NOW(), '适用于负责不同供应商的人员使用系统进行采购业务');"
);

$u['117'] = array(
    "INSERT INTO `sys_params` (`param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`) 
VALUES ('send_check_refund', 'oms_taobao', '发货回写校验退单', 'radio', '[\"关闭\",\"开启\"]', '0', ' 0', '1-开启 0-关闭', NOW(), '开启后，淘宝交易回写时会先校验退单，若有退单则回写失败，若需回写请使用‘强制回写’');"
);

$u['bug_145'] = array(
    "ALTER TABLE base_pay_type MODIFY `is_cod` tinyint(1) DEFAULT '0' COMMENT '货到付款 0：款到发货 1：货到付款';",
);
$u['bug_146'] = array(
    "UPDATE sys_action set sort_order=40 where action_name='待称重订单列表'",
    "UPDATE sys_action set sort_order=41 where action_name='订单称重校验'",
    "UPDATE sys_action set sort_order=42 where action_name='已称重订单列表'",
    
);

$u['140'] = array(
    "CREATE TABLE `sys_role_manage_price` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `role_code` varchar(64) DEFAULT '' COMMENT '角色代码',
        `manage_code` varchar(64) DEFAULT '' COMMENT '价格管控类型',
        `status` tinyint(3) DEFAULT '0' COMMENT '是否启用',
        `desc` varchar(255) DEFAULT '' COMMENT '价格管控类型',
        PRIMARY KEY (`id`),
        UNIQUE KEY `role_sensitive` (`role_code`,`manage_code`) USING BTREE
    ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='角色价格管控';",
    "INSERT INTO `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`) VALUES ( '', 'manage_price', 'sys_set', '价格管控', 'radio', '[\"关闭\",\"开启\"]', '0', '0.00', '1-开启 0-关闭', '', '');"
);