<?php

$u['745'] = array(
    //订单列表增加操作列，删除历史自定义列数据
    "DELETE FROM sys_user_pref WHERE iid='oms/sell_record_combine_ex_list';",
);

$u['736'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('5020700', '5020000', 'url', '套餐子商品查询', 'prm/goods_combo/detail_list', '3', '1', '0', '1', '0');"
);

$u['735']=array(
    "INSERT INTO `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`) VALUES ('', 'pur_barcode_print', 'pur', 'S004_001 采购入库单支持商品条码打印', 'radio', '[\"关闭\",\"开启\"]', '0', '0.00', '', '2016-10-20 14:16:45', '默认不开启，开启后，采购入库单详情显示‘打印条码’按钮，点击后可直接按照采购数量打印条码');"
);

$u['749']=array(
    "INSERT INTO `base_sale_channel` (`sale_channel_code`,`short_code`,`sale_channel_name`,`is_system`,`is_active`) VALUES('pinduoduo','pdd','拼多多','1','1');"
);

$u['710']=array(
	"insert into sys_action values('1080000','1000000','group','双11工具','op_activity_tool',4,1,0,1,0);",
	"insert into sys_action values('1080100','1080000','url','导入效验商品','op/op_activity_goods/do_list',0,1,0,1,0);",
	"insert into sys_action values('1080200','1080000','url','生成效验任务','op/op_api_activity_check/task_do_list',1,1,0,1,0);",
	"insert into sys_action values('1080300','1080000','url','效验任务结果','op/op_api_activity_check/result',2,1,0,1,0);",
	"insert into sys_action values('1080400','1080000','url','手工同步库存','op/op_activity_goods/inv_check',3,1,0,1,0);"
);

$u['777'] = array("INSERT INTO `sys_params` (`param_code`,`parent_code`,`param_name`,`type`,`form_desc`,`value`,`sort`,`remark`,`memo`) VALUES('order_intercept_and_problem','oms_property','S001_114   平台商家备注更新，同步系统订单','radio','[\"关闭\",\"开启\"]','0','0.00','1-开启 0-关闭','参数默认关闭，请谨慎开启。开启后，针对平台交易无商家备注，订单转入系统且未发货的交易，客服在平台上更新了商家备注，系统订单强制拦截并设为问题单。');");