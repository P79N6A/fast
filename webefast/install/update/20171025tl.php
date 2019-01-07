<?php
$u['1700'] = array(
    "ALTER TABLE `js_fapiao` ADD COLUMN `config_type` tinyint(3) DEFAULT '0' COMMENT '配置类型 1为阿里 2航信';",
        //通灵已发货订单列表增加质保单打印权限控制 
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('4020803', '4020800', 'act', '批量打印质保书', 'oms/sell_record/opt_print_warranty', '0', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('4020804', '4020800', 'act', '打印质保书', 'oms/sell_record/do_print', '0', '1', '0', '1', '0');",
);

$u['1696'] = array(
    //唯一码表增加仓库代码
     "ALTER TABLE goods_unique_code_tl ADD COLUMN store_code varchar(30) DEFAULT '' COMMENT '仓库代码' after unique_code;",
);

//通灵增加唯一码更改仓库
$u['1711'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('5030400', '5030000', 'url', '唯一码仓库更改', 'prm/create_store_tl/do_list', '3', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('5030401', '5030400', 'act', '更改仓库(生成移仓单)', 'prm/create_store/edit_warehouse', '1', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('5030402', '5030400', 'act', '更改仓库(不影响库存)', 'prm/create_store/do_edit', '1', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('5030403', '5030400', 'url', '批量更改仓库(不影响库存)', 'prm/create_store/opt_do_edit', '1', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('5030404', '5030400', 'url', '批量更改仓库(生成移仓单)', 'prm/create_store/edit_warehouse', '1', '1', '0', '1', '0');",
);

$u['1683'] = array(
    //发票表增加销方开户行
     "ALTER TABLE `js_fapiao` ADD COLUMN `xhf_yhmc` varchar(30) DEFAULT '' COMMENT '销货方开户行' after `xhf_dh`;",
);

$u['1741'] = array(
    //发票表增加阿里专用挂靠店铺
     "ALTER TABLE `js_fapiao` ADD COLUMN `gk_dp` varchar(128) DEFAULT '' COMMENT '挂靠店铺';",
);
