<?php
$u = array();

$u['1027'] = array(
    "delete from sys_user_pref WHERE iid='prm/goods_inv_do_list/table'",
    "delete from sys_user_pref WHERE iid='rpt/report_jxc_do_list'"
);

$u['1038'] = array("INSERT INTO `base_express` (`company_code`,`express_code`,`express_name`,`sys`) VALUES('SF','SFCR2','顺丰次日','1');",
                   "INSERT INTO `base_express` (`company_code`,`express_code`,`express_name`,`sys`) VALUES('SF','SFGR2','顺丰隔日','1');",
                   "INSERT INTO `base_express` (`company_code`,`express_code`,`express_name`,`sys`) VALUES('SF','SFCC2','顺丰次晨','1');",
                   "INSERT INTO `base_express` (`company_code`,`express_code`,`express_name`,`sys`) VALUES('SF','SFJR2','顺丰即日','1');"
                );

$u['1022'] = array(
		"insert into sys_params(param_id,param_code,parent_code,param_name,type,form_desc,`value`,sort,remark,memo) values ('','spec_power','sys_set',' S006_007      商品套餐/组装定义多规格','radio','[\"关\",\"开启\"]','0',0,'','默认关闭，即系统默认规格，无法定义规格开启后，可以定义多规格套餐/组装');
",
);

$u['980_bug'] = array(
	"update sys_action set action_name='修改快递单号' where action_id = '4020801';",
	"update sys_action set action_name='修改快递单号' where action_id = '7010121';",
);

$i['1022_bug'] = array(
	"update base_spec1 set remark='系统内置，不允许删除' where spec1_code='000';",
	"update base_spec2 set remark='系统内置，不允许删除' where spec2_code='000';",
);
$u['bug_953'] = array(
    "DELETE FROM sys_role_action WHERE role_id = 100 AND action_id = '9060400';",
);

$u['bug_960'] = array(
	"update sys_params set memo='默认关闭，即系统默认规格，无法定义规格，开启后，可以定义多规格套餐/组装' where param_code='spec_power'",
);

$u['bug_955'] = array(
	"delete from base_record_type where record_type_code='inferior_return'",
	"update base_record_type set record_type_name='JIT发货' where record_type_code='200';",
	"update base_record_type set record_type_name='次品退货' where record_type_code='300';",
	"insert into base_record_type(record_type_code,record_type_name,record_type_property,sys,remark) values('301','JIT退货',3,1,'系统内置档案，不允许删除');",
	"update wbm_return_record set record_type_code='300' where record_type_code='';",
	"update wbm_return_notice_record set return_type_code='300' where return_type_code='inferior_return' or return_type_code='';",
);

$u['1065'] = array(
	"update sys_action set sort_order=6,status=0 where action_id='8040700' and action_name='专场商品管理';",

	"update sys_params set parent_code='' where param_code='clodop_print';",

	"update sys_auth set value='v2.0.0' where code='version';",
);