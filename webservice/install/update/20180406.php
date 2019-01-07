<?php

$u["2097"] = array( //短信模块
    //短信中间表
    "CREATE TABLE `sms_task` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `sms_type` varchar(50) NOT NULL COMMENT '短信类型:deliver=发货通知',
        `kh_id` int(11) NOT NULL COMMENT '客户ID',
        `sys_sms_id` int(11) NOT NULL COMMENT '系统短信ID',
        `send_channel` varchar(20) NOT NULL DEFAULT '' COMMENT '发送通道:yunrong=云融正通',
        `send_channel_code` varchar(100) NOT NULL DEFAULT '' COMMENT '发送通道短信ID',
        `phone` varchar(20) NOT NULL COMMENT '手机号',
        `content` text NOT NULL DEFAULT '' COMMENT '短信内容',
        `num` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '短信条数',
        `status` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '发送状态:0=未发送,1=发送成功,2=发送失败,3=发送中',
        `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
        `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
        `send_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '发送时间',
        `report_content` varchar(255) NOT NULL DEFAULT '' COMMENT '报告内容',
        `report_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '报告时间',
        `is_push_report` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否推送短信报告到客户表',
        PRIMARY KEY (`id`),
        UNIQUE KEY `index_1` (`kh_id`,`sys_sms_id`) USING BTREE,
        KEY `index_2` (`create_time`,`status`) USING BTREE,
        KEY `index_3` (`status`,`is_push_report`) USING BTREE
    ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='短信任务中间表';",
);