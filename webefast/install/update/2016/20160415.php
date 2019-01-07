<?php
$u['186'] = array(
    "INSERT INTO `base_sale_channel` VALUES ('38', 'kaola', 'kl', '考拉', '1', '1', '', '2016-04-06 13:57:24');",
);
$u['201'] = array(
    "UPDATE `sys_schedule` SET `loop_time`=300 WHERE `code`='auto_trans_api_order';",
    "UPDATE `sys_schedule` SET `loop_time`=300 WHERE `code`='refund_download_cmd';"
);
$u['bug_152'] = array(
    "ALTER TABLE `oms_sell_return_detail` MODIFY COLUMN `deal_code` VARCHAR (200) NOT NULL DEFAULT '' COMMENT '平台交易号';",
    "ALTER TABLE `oms_sell_return_detail` MODIFY COLUMN `sub_deal_code` VARCHAR (200) NOT NULL DEFAULT '' COMMENT '平台子交易号';",
    "ALTER TABLE `oms_sell_change_detail` MODIFY COLUMN `deal_code` VARCHAR (200) NOT NULL DEFAULT '' COMMENT '平台交易号';"
);
$u['175'] = array(
    "CREATE TABLE `crm_client` (
      `client_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `client_code` varchar(30) DEFAULT '' COMMENT '顾客代码',
      `client_name` varchar(30) DEFAULT '' COMMENT '顾客名称',
      `status` tinyint(1) DEFAULT NULL COMMENT '1：未提交 2：已提交 3：已审核 4：已发卡 5：已领用 6：完成 7：退回',
      `birthday` datetime DEFAULT NULL COMMENT '生日',
      `client_sex` tinyint(1) DEFAULT '3' COMMENT '性别 1:男 2:女 3:保密',
      `client_tel` varchar(20) DEFAULT '' COMMENT '手机号码',
      `email` varchar(128) DEFAULT '' COMMENT '电子邮箱',
      `add_time` datetime DEFAULT NULL COMMENT '加入时间',
      `address` varchar(255) DEFAULT '' COMMENT '详细地址',
      `level` int(4) DEFAULT '0' COMMENT '会员卡等级',
      `level_time` datetime DEFAULT NULL COMMENT '升级时间',
      `last_consume_time` datetime DEFAULT NULL COMMENT '最后一次消费时间',
      `is_vip` tinyint(1) DEFAULT '0' COMMENT '0:不是会员 1:是会员',
      `is_followed_weixin` tinyint(1) DEFAULT '0' COMMENT '0:未关注 1:已关注',
      `marriage` tinyint(1) DEFAULT '0' COMMENT '婚姻状况 1：已婚 2：未婚 0：保密',
      `education` tinyint(1) DEFAULT '0' COMMENT '学历 1：小学及以下，2：初中，3：高中，4：中专，5：大专，6：本科，7：研究生，8：博士及以上，0：保密',
      `consume_money` decimal(20,3) DEFAULT '0.000' COMMENT '累计消费金额',
      `consume_num` varchar(64) DEFAULT '0' COMMENT '累计消费数量',
      `client_balance` int(11) DEFAULT '0' COMMENT '剩余余额',
      `client_integral` int(11) DEFAULT '0' COMMENT '剩余积分',
      `is_black` tinyint(1) DEFAULT '0' COMMENT '0:不在黑名单 1:在黑名单',
      `country` varchar(20) DEFAULT '1' COMMENT '国家',
      `province` varchar(20) DEFAULT '0' COMMENT '省',
      `city` varchar(20) DEFAULT '0' COMMENT '城市',
      `district` varchar(20) DEFAULT '0' COMMENT '地区',
      `street` varchar(20) DEFAULT '0' COMMENT '街道',
      `remark` varchar(255) DEFAULT '' COMMENT '备注',
      `is_add_person` varchar(64) DEFAULT '' COMMENT '添加人',
      `is_add_time` datetime DEFAULT NULL COMMENT '添加时间',
      `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
      `trd_id` varchar(64) DEFAULT '' COMMENT '第三方来源GUID',
      `trd_type` varchar(32) DEFAULT '' COMMENT '第三方来源类型:erp/3000/efast',
      `trd_time` varchar(64) DEFAULT '' COMMENT '第三方导入或者更新的时间',
      PRIMARY KEY (`client_id`),
      UNIQUE KEY `idxu` (`client_code`) USING BTREE,
      UNIQUE KEY `idxu2` (`client_tel`) USING BTREE,
      KEY `client_name` (`client_name`) USING BTREE,
      KEY `status` (`status`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='顾客列表';",
    "INSERT INTO `sys_action` VALUES ('30020000','30000000','group','顾客管理','client_manage','30','1','0','1','2');",
    "INSERT INTO `sys_action` VALUES ('30020100','30020000','url','顾客列表','crm/client/do_list','1','1','0','1','2');",
    "INSERT INTO `sys_action` VALUES ('30020101','30020100','act','编辑','crm/client/detail&app_scene=edit','1','1','0','1','2');"
);