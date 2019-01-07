<?php

$u['1887'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('5030600', '5030000', 'url', '唯一码库存查询', 'prm/goods_inv_tl/do_list', '5', '1', '0', '1', '0');
",
    //修改唯一码表主石数量和辅石数量属性
    "ALTER TABLE `goods_unique_code_tl` MODIFY COLUMN `pri_diamond_count` decimal(10,0) DEFAULT '0' COMMENT '主石数量';",
    "ALTER TABLE `goods_unique_code_tl` MODIFY COLUMN `ass_diamond_count` decimal(10,0) DEFAULT '0' COMMENT '辅石数量';",
);
//修改库存差异报表描述
$u['1810'] = array(
    "UPDATE `sys_schedule` SET `desc`='启用后，系统分别统计唯一码档案中各仓库商品的数量及库存查询中各仓库商品的可用库存数量，并显示二者的差异数量' WHERE (`code`='inventory_control_compare');
",
);

