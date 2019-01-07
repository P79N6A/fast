<?php

$u['bug_544'] = array(
    "UPDATE `sys_params` SET `param_name`='TY001  平台商品初始化，设置允许库存同步', `memo`='默认关闭，即设置为不允许库存同步 开启参数，系统下载商品资料时，设置商品允许库存同步' WHERE (`param_code`='inventory_sync');"
);
$u['bug_590'] = array(
    "alter table api_order  MODIFY column `receiver_phone` varchar(20) DEFAULT '' COMMENT '平台固定电话';",
);

$u['743'] = array(
	"delete from sys_user_pref where iid='oms/sell_record_shipped_list';",
);

$u['734'] = array(
	"insert into sys_action values('6030103','6030100','act','验收后修改价格','pur/purchase_record/do_edit_detail',1,1,0,1,0);",
);

$u['bug_616'] = array (
	"update sys_action set action_code='pur/purchase_record/do_edit_detail_price' where action_name = '验收后修改价格'",
);